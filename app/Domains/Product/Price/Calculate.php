<?php

namespace App\Domains\Product\Price;

use App\Booking\Availability\Schedule;
use App\Booking\Product;
use App\Booking\Product\Exceptions\ProductPriceNoncalculable;
use App\Booking\Product\Exceptions\ProductPriceNotAvailable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;

class Calculate
{
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @throws ProductPriceNotAvailable
     */
    public function getPriceSchedule(string $start, string $end, array $request, bool $withRecurring = true): ?Schedule
    {
        try {
            $schedule = $this->product->scheduledStarting(toCarbon($start), toCarbon($end), $withRecurring);
        } catch (InvalidRRule) {
            Log::debug('Invalid rrule', [$request, $this]);
            throw (new ProductPriceNotAvailable('Product price is not currently available'));
        } catch (InvalidWeekday) {
            Log::debug('Invalid weekday in rrule', [$request, $this]);
            throw (new ProductPriceNotAvailable('Product price is not currently available'));
        }
        return $schedule;
    }

    public function getCalculatedPrice(Schedule $schedule, string $end, string $start): ?float
    {
        $calculatedPrice = null;
        $price = $schedule->pivot->value;

        switch ($schedule->pivot->key) {

            //Per hour or part of, minimum 1 hour? Nah. Could be something booked for 1/2 hour slot (restaurant table?)
            case 'hour':
                $scaledPrice = (toCarbon($end)->diffInMinutes(toCarbon($start)) / 60) * $price;

                $calculatedPrice = (float)($scaledPrice / 100);
                break;

            /*
             * Per day or part of a day? Per 24 hours? Is there a minimum period?
             * I have to make a call here, so I am going to make minimum of 24 hours + part of there on.
             * That would likely cover room hire + tool hire
             * Potentially can add an option for 'minimum day' or 'allow part day' etc. 'round up to nearest day'
             * We wait for the requirements to come back from customers.
             * K.Hards 12/2020
             */
            case 'day':
                $hours = (toCarbon($end)->diffInHours(toCarbon($start)));
                if ($hours < 24) {
                    $hours = 24;
                }

                $calculatedPrice = (float)((($hours / 24) * $price) / 100);
                break;

            // Flat rate, time does not come into it.
            case 'booking':
            case 'special':
                $calculatedPrice = (float)($price / 100);
                break;
        }
        return $calculatedPrice;
    }

    /**
     * Gets the products price in floating point format.
     * Reason it's floating point is due to Vanillo's product that we use
     *      Example: (1023 / 100) = £10.23
     * Internally prices  are stored as ints. All prices should be converted to floating point here.
     *
     *
     * @param array $request
     * @return float
     * @throws ProductPriceNotAvailable|ProductPriceNoncalculable
     */
    public function getPrice(array $request = []): float
    {
        $price = $this->getBasePrice($request);
        $price = $this->extraHoursChargeForOverMaxHours($price, $request);
        $price = $this->addChargeAndDiscountsForAdvanceBookingCharges($price, $request);

        return round($price);
    }

    /**
     * @throws ProductPriceNotAvailable
     */
    public function calculatedFixedPrice(string $start, string $end, array $request): float
    {
        // special fixed pricing
        if (($schedule = $this->getPriceSchedule($start, $end, $request, false))) {
            if ($schedule->pivot->key === 'special') {
                if (($calculatedPrice = $this->getCalculatedPrice($schedule, $end, $start)) !== null) {
                    return $calculatedPrice;
                }
            }
        }

        //std. fixed
        return (float)($this->product->price_fixed_price / 100);
    }

    private function getBasePrice(array $request): float
    {
        // Default and original method - unused by booking system
        // (Used by vanillo add to cart, cart item attributes)
        if (empty($request)) {
            return (float)($this->product->price / 100) ?? 0;
        }

        $start = $request['start'] ?? '';
        $end = $request['end'] ?? '';

        // Fixed price, regardless of dates etc.
        if ($this->product->price_type == 'fixed' && $start && $end) {
            return $this->calculatedFixedPrice($start, $end, $request);
        }

        if (!$start || !$end) {
            throw (new ProductPriceNotAvailable('For pricing, please enter start and end dates'));
        }

        // Calculate the scheduled & special prices
        $schedule = $this->getPriceSchedule($start, $end, $request);

        if ($this->product->price_type === 'scheduled' && $schedule === null) {
            Log::debug('No scheduled price available for this product', [$request, $this]);
            throw (new ProductPriceNotAvailable('Product price is not currently available'));
        }

        if (($calculatedPrice = $this->getCalculatedPrice($schedule, $end, $start)) !== null) {
            return $calculatedPrice;
        }

        Log::debug("schedule=", ['schedule' => $schedule]);//null
        Log::debug('Unavailable to calculate the scheduled price', [$request, $schedule]);

        throw (new ProductPriceNoncalculable('Unable to calculate the scheduled price'));
    }

    /**
     * Option to charge extra for bookings over x hours long.
     *
     * @param float $price
     * @param array $request
     * @return float
     */
    private function extraHoursChargeForOverMaxHours(float $price, array $request): float
    {
        if (!($maxHours = $this->product->settings->get('extra_hours_charge_max_hours'))) {
            return $price;
        }

        $start = $request['start'] ?? '';
        $end = $request['end'] ?? '';
        if (!$start || !$end) {
            return $price;
        }

        $diffInHours = (toCarbon($start)->diffInMinutes(toCarbon($end))) / 60;
        if ($diffInHours <= $maxHours) {
            return $price;
        }

        $pricePerHour = (1 / $maxHours) * $price;
        $extraHours = $diffInHours - $maxHours;

        return $price + ($extraHours * $pricePerHour);
    }

    /**
     * @param float $price
     * @param array $request
     * @return float
     */
    private function addChargeAndDiscountsForAdvanceBookingCharges(float $price, array $request): float
    {
        $start = $request['start'] ?? '';
        if (!$start) {
            return $price;
        }

        if (!($advanceCharges = $this->product->settings->get('advance_charges'))) {
            return $price;
        }

        $diffInDays = toCarbon($start)->diffInDays(Carbon::now());

        foreach ($advanceCharges as $charge) {
            if ($diffInDays >= $charge['from'] && $diffInDays <= $charge['to']) {
                $price += $charge['value'];
            }
        }

        return $price;
    }

}
