<?php

namespace App\Booking;

use App\Booking\Availability\Schedule;
use App\Domains\Auth\Models\User;
use App\Domains\Email\Models\MailTemplate;
use App\Domains\Form\Models\Form;
use App\Domains\Order\Order;
use App\Domains\Payment\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;

/**
 * @property int id
 * @property Address address
 */
class Business extends Model
{
    protected $fillable = ['name', 'address_id', 'timezone', 'url', 'email', 'phone', 'currency'];

    /**
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

    /**
     * The users that belong to the business.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user', 'business_id', 'user_id');
    }

    /**
     * The customers that belong to the business.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }

    /**
     * The products that belong to the business.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'owner_id');
    }

    public function mailables(): HasMany
    {
        return $this->hasMany(MailTemplate::class, 'owner_id');
    }

    /**
     * All the orders that belong to the business.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * The business has many Payment Methods.
     *
     * @return HasMany
     */
    public function paymentMethod(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * The business Bank Payment Method.
     */
    public function paymentMethodBank()
    {
        return $this->paymentMethod()->where('type', 'bank')->first();
    }


    /**
     * The forms that belong to the business.
     *
     * @return MorphMany
     */
    public function forms(): MorphMany
    {
        return $this->morphMany(Form::class, 'owner');
    }

    /**
     * The schedules that belong to this business.
     *
     * @return MorphMany
     */
    public function schedule(): MorphMany
    {
        return $this->morphMany(Schedule::class, 'model');
    }

    /**
     *  Does the business have any staff availability on this date?
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     *
     * @return bool
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function hasStaffAvailableDuring(Carbon $startDateTime, Carbon $finishDateTime): bool
    {
        return $this->quantityStaffAvailableDuring($startDateTime, $finishDateTime) > 0;
    }

    /**
     *  Does the business have any staff availability on this date?
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     *
     * @return int
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function quantityStaffAvailableDuring(Carbon $startDateTime, Carbon $finishDateTime): int
    {
        $count = 0;
        foreach ($this->users()->get() as $staff) {
            /** @var User $staff */
            if ($staff->isAvailableDuring($startDateTime, $finishDateTime)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     *  Get the staff the businesshas available on this date
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     *
     */
    public function staffAvailableDuring(Carbon $startDateTime, Carbon $finishDateTime)
    {
        $results = collect();
        foreach ($this->users()->get() as $staff) {
            /** @var User $staff */
            if ($staff->isAvailableDuring($startDateTime, $finishDateTime)) {
                $results[] = $staff;
            }
        }

        return $results;
    }
}
