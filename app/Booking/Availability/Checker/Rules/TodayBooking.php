<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TodayBooking extends RuleParent
{
    /**
     * @var AvailabilityData
     */
    private $availabilityData;

    public function handle(AvailabilityData $availabilityData, \Closure $next)
    {
        $this->availabilityData = $availabilityData;

        if ($availabilityData->request->start === null || $availabilityData->request->end === null) {
            $availabilityData->response->log[__CLASS__] =
                'Missing start, or end datetime so can\'t check staff availability';
            return $next($availabilityData);
        }

        $blockSameDay = $availabilityData->request->product->settings->get('block_same_day_bookings');
        $bookingDate = toCarbon($availabilityData->request->start);
        $isToday = $bookingDate->toDateString() === Carbon::now()->toDateString();

        if ($blockSameDay === 'yes' && $isToday) {
            $availabilityData->response->log[__CLASS__] = "The product is being booked for today so blocked.";
            $availabilityData->response->info['reason'] = 'times';
            $availabilityData->response->info['reason_message'] = 'Unavailable to book for today. Please call to book';
            return;
        }

        // testing.allow_past_bookings is a bodge as the tests use past dates.
        // All tests need to be updated to use carbon::now()->Add days etc./
        // Will be a little tricky as we are testing schedules with saturdays and weekdays etr.

        if(!config('testing.allow_past_bookings', false)) {
            if ($bookingDate <= Carbon::now()) {
                $availabilityData->response->log[__CLASS__] = "The product is being booked in the past, so is blocked.";
                $availabilityData->response->info['reason'] = 'times';
                $availabilityData->response->info['reason_message'] = 'Unavailable to book in the past.';
                return;
            }
        }

        $blockFutureDays = $availabilityData->request->product->settings->get('block_number_days_future');
        $diffInDays = $bookingDate->diffInDays(\Carbon\Carbon::now());
        if(($blockFutureDays > 0) && ($diffInDays >= $blockFutureDays)) {
            $availabilityData->response->log[__CLASS__] = "The product is being booked too far into the future.";
            $availabilityData->response->info['reason'] = 'times';
            $availabilityData->response->info['reason_message'] = 'Unavailable to book over ' . $blockFutureDays . ' days into the future';
            return;
        }

        return $next($availabilityData);
    }
}
