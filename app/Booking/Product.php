<?php

namespace App\Booking;

use App\Booking\Availability\Rrule;
use App\Booking\Availability\Schedule;
use App\Booking\Availability\Traits\ScheduledAvailable;
use App\Booking\Models\Calendar;
use App\Booking\Product\Exceptions\ProductPriceNotAvailable;
use App\Booking\Services\AvailabilityManager;
use App\Domains\Form\Models\Form;
use App\Domains\Product\Price\Calculate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;
use Spatie\SchemalessAttributes\SchemalessAttributes;
use Vanilo\Framework\Models\Product as VaniloProduct;

/**
 * Class Product.
 *
 * @property int $price - Basic price
 * @property int $staff_quantity - Quantity of staff required with booking product
 * @property string $availability_type - Type of availability always available / scheduled
 * @property int $availability_schedule - Id of the schedule associated with product availability
 * @property int $available_quantity - Quantity available for hire/for sale
 * @property string $travelling_limit - Limit travelling yes/no
 * @property int $travelling_value - The limit value
 * @property string $travelling_type - The travel limit type
 * @property string $name - The product name
 * @property string $description - The product description
 * @property string $state - The product state - active / draft
 * @property bool $is_addon - The product cann't be booked by it's self
 * @property bool $is_required - The product is required when booking
 * @property string $sku - The product sku (unused)
 * @property int $delivery_method - Bit value of delivery methods.
 * @property string type - Product or service
 * @property string price_type - fixed or scheduled
 * @property int price_fixed_price - Fixed price in pennies
 * @property Business business
 *
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $settings - Product attributes (non filterable)
 */
class Product extends VaniloProduct
{
    use ScheduledAvailable;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DRAFT = 'draft';

    public const TYPE_SERVICE = 'service';

    public const TYPE_PRODUCT = 'product';

    public const DELIVERY_METHODS = [
        'delivered' => 1,
        'collected' => 2,
        'shipped' => 4,
    ];

    protected $enums = [
        //'state' => 'ProductStateProxy@enumClass'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_addon' => 'bool',
        'is_required' => 'bool',
    ];
    private Calculate $calculate;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->calculate = new Calculate($this);
    }

    /**
     * @return BelongsToMany
     */
    public function variations(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_variations', 'parent_product_id', 'product_id')
            ->withPivot(['stock_from_parent', 'is_default']);
    }

    // Setup time with variation times.
    public function totalSetupTime() {
        if($this->isParent()) {
            return $this->setup_time;
        }

        return $this->setup_time + $this->parent()->first()->setup_time;// in Mins.
    }

    /**
     * @return BelongsToMany
     */
    public function parent(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_variations', 'product_id', 'parent_product_id')
            ->withPivot(['stock_from_parent', 'is_default']);
    }

    public function isParent(): bool
    {
        return ($this->parent()->count() === 0);
    }

    /**
     * Calculate the delivery method value.
     *
     * @param array $deliveryMethods
     * @return int
     */
    public static function calculateDeliveryMethodValue(array $deliveryMethods)
    {
        $deliveryMethod = 0;
        foreach ($deliveryMethods as $method) {
            $deliveryMethod += self::DELIVERY_METHODS[$method] ?? 0;
        }

        return $deliveryMethod;
    }

    /**
     * Calculate the delivery methods from a value.
     *
     * @param int $deliveryMethod
     * @return array
     */
    public static function calculateDeliveryMethodsFromValue($deliveryMethod): array
    {
        $methods = [];
        foreach (self::DELIVERY_METHODS as $methodKey => $methodValue) {
            if ($deliveryMethod & $methodValue) {
                $methods[] = $methodKey;
            }
        }

        return $methods;
    }

    /**
     * Product attributes.
     *
     * @return SchemalessAttributes
     */
    public function getSettingsAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'settings');
    }

    /**
     * Settings scope.
     *
     * @return Builder
     */
    public function scopeWithSettings(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('settings');
    }

    /**
     * Get the owning model.
     */
    public function business()
    {
        // (new Business)->getMorphClass()
        return $this->morphTo(Business::class, 'owner_type', 'owner_id');
    }

    public function form()
    {
        return $this->hasOne(Form::class, 'id', 'form_id');
    }

    /**
     * @return bool
     */
    public function isProduct(): bool
    {
        return $this->type == self::TYPE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isService(): bool
    {
        return $this->type == self::TYPE_SERVICE;
    }

    /**
     * Collected = Dry hire, person collects. Staff not needed to attend booking.
     *
     * @return bool
     */
    public function isCollected(): bool
    {
        return (bool) ($this->delivery_method & self::DELIVERY_METHODS['collected']);
    }

    /**
     * Shipped = The item will be posted, staff will not be attending
     *
     * @return bool
     */
    public function isShipped(): bool
    {
        return ($this->delivery_method & self::DELIVERY_METHODS['shipped']) > 0;
    }

    /**
     * Delivered = A member of staff will need to be available to attend the event in person
     *
     * @return bool
     */
    public function isDelivered(): bool
    {
        return ($this->delivery_method & self::DELIVERY_METHODS['delivered']) > 0;
    }

    /**
     * Decrement the stock quantity
     *
     * @param integer $quantity
     * @return void
     */
    public function decrementStock(int $quantity)
    {
        if (!$this->isProduct()) {
            return;
        }

        //Handle parent stock product
        if ($pivot = DB::table('product_variations')->where('product_id', $this->id)->first()) {
            if ($pivot->stock_from_parent) {
                $parentProduct = $this->parent->first();
                $parentProduct->available_quantity = $parentProduct->available_quantity - $quantity;
                $parentProduct->save();
                return;
            }
        }

        // Normal stock decrement - allowing negative.
        $this->available_quantity = $this->available_quantity - $quantity;
        $this->save();
    }

    /**
     * get the number of items that are not hires or available in stock.
     *
     * @param Carbon|null $start
     * @param Carbon|null $end
     * @return bool|int
     */
    public function getAvailableQuantity(Carbon $start = null, Carbon $end = null)
    {
        $quantityInStock = $this->available_quantity;

        if ($this->isProduct()) {
            if ($pivot = DB::table('product_variations')->where('product_id', $this->id)->first()) {
                if ($pivot->stock_from_parent) {
                    return $this->parent->first()->available_quantity;
                }
            }

            return $quantityInStock;
        }

        if ($this->isService() && $start && $end) {
            return $quantityInStock - $this->quantityBooked($start, $end);
        }

        return 0;
    }

    /**
     * Get quantity booked between dates.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return bool
     */
    public function quantityBooked(Carbon $start, Carbon $end)
    {
        $query = Calendar::quantityBookedBetween($this, $start, $end);
        $quantityBooked = $query->get()->count();

        //Assumption is that staff can only handle a single booking at the time!
        return $quantityBooked > 0;
    }

    /**
     * Product has staff availability.
     *
     * @param array $attributes
     * @return bool
     * @todo - remove - now using pipeline..
     *
     * @deprecated
     */
    public function isAvailable(array $attributes)
    {
        $this->availabilityManager = new AvailabilityManager();
        $business = Business::query()->find($attributes['businessId']);

        $available = $this->availabilityManager->businessHasStaffAvailable(
            Carbon::createFromFormat('Y-m-d H:i:s', $attributes['start']),
            Carbon::createFromFormat('Y-m-d H:i:s', $attributes['end']),
            $business
        );

        return $available;
    }

    /**
     * Price schedules.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function schedules()
    {
        // Just to be clear this currently only supports price scheduling, it does NOT support availability schedules
        // To make that happen we would need to add another value to the pivot, such as type which could then be set to 'price' or 'availability'
        //

        // Include key and value from schedule_maps
        return $this->morphToMany(Schedule::class, 'schedule_map')->withPivot(['key', 'value']);
    }

    protected function availabilitySchedules()
    {
        return $this->schedules();
        //return Schedule::where('id', $this->availability_schedule);
    }

    /**
     * Currently availability schedule is one field in the product editor that allows you to set 1 schedule.
     * TODO: Allow multiple schedules.
     *
     * @param Carbon $searchStartDate
     * @param Carbon $searchEndDate
     * @return Schedule|null
     */
    public function isAllowedByAvailabilityScheduled(Carbon $searchStartDate, Carbon $searchEndDate): ?Schedule
    {
        $theSchedule = Schedule::where('id', $this->availability_schedule)->first();

        // The following is a copy of scheduledAvailableDuring, reason is it's a last minute fix.
        // TODO rewrite this crap.

        $startFound = $endFound = false;

        $dtstart = $theSchedule->start_datetime;
        $dtend = $theSchedule->end_datetime;
        $rrule = $theSchedule->rrule;

        if ($rrule) {
            $transformed = Rrule::transformRrule($rrule, $dtstart, $dtend, $searchStartDate->toDateTime(), $searchEndDate->toDateTime());
            $searchResults = Rrule::search($transformed, $searchStartDate->toDateTime(), $searchEndDate->toDateTime());

            if ($searchResults['startFound']) {
                $startFound = true;
            }
            if ($searchResults['endFound']) {
                $endFound = true;
            }

            if ($startFound && $endFound) {
                return $theSchedule;
            }
        } else {
            $scheduleStart = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $dtstart);
            $scheduleEnd = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $dtend);

            $startFound = ($scheduleStart <= $searchStartDate) && ($scheduleEnd >= $searchStartDate);
            $endFound = ($scheduleStart <= $searchEndDate) && ($scheduleEnd >= $searchEndDate);
            if ($startFound && $endFound) {
                return $theSchedule;
            }
        }

        return null;
    }

    /**
     * Get's the products price in floating point format.
     * Reason it's floating point is due to Vanillo's product that we use
     *      Example: (1023 / 100) = £10.23
     * Internally prices  are stored as int's. All prices should be converted to floating point here.
     *
     *
     * @param array $request
     * @return float
     * @throws ProductPriceNotAvailable
     */
    public function getPrice($request = []): float
    {
        return $this->calculate->getPrice($request);
    }
}
