<?php

namespace App\Booking\Availability\Checker;

use App\Booking\Availability\Checker\Rules\PostcodeBlock;
use App\Booking\Availability\Checker\Rules\ProductActive;
use App\Booking\Availability\Checker\Rules\ProductCanBeDelivered;
use App\Booking\Availability\Checker\Rules\ProductScheduleAvailable;
use App\Booking\Availability\Checker\Rules\StaffAvailable;
use App\Booking\Availability\Checker\Rules\StockAvailable;
use App\Booking\Availability\Checker\Rules\TodayBooking;
use Illuminate\Pipeline\Pipeline;

class Checker
{
    /**
     * The process order function is responsible for accepting details and
     * processing the required steps to satisfy the requirements of
     * the request.
     *
     * @param AvailabilityData $availabilityData
     * @return AvailabilityData
     */
    public function checkAvailability(AvailabilityData $availabilityData)
    {
        // now get the tasks that we need to run for this order
        $productAvailabilityTasks = [
            TodayBooking::class,
            PostcodeBlock::class,
            ProductActive::class,
            StockAvailable::class,
            ProductScheduleAvailable::class,
            ProductCanBeDelivered::class,
//@TODO     ProductBeShipped::class,
            StaffAvailable::class,
        ];

        // now create a new pipeline sending the order data object through
        $pipeline = app(Pipeline::class)->
            send($availabilityData)->
            through($productAvailabilityTasks)->
            then(function (AvailabilityData $availabilityData) {
                //If we got to this which is the last thing, then all is good.
                $availabilityData->response->status = AvailabilityResponse::STATUS_AVAILABLE;

                return $availabilityData;
            });

        return $availabilityData;
    }
}
