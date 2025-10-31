<?php

namespace App\Booking\Contracts;

use App\Booking\Availability\Schedule;
use App\Booking\Business;
use Carbon\Carbon;

interface AvailabilityManager
{
    /**
     * Check if user has availability for a given date.
     *
     * Checks both schedule and existing bookings
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     * @param $staff
     * @return mixed
     */
    public function staffHasAvailability(Carbon $startDateTime, Carbon $finishDateTime, $staff);

    /**
     * Check if a business has staff availability for a given datetime period.
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     * @param Business $business
     * @return mixed
     */
    public function businessHasStaffAvailable(Carbon $startDateTime, Carbon $finishDateTime, Business $business);

    /**
     * Check user's __schedule__ to see if they are available.
     *
     * @param $startDateTime
     * @param $finishDateTime
     * @param $staff
     * @return Schedule
     */
    public function staffAvailable(Carbon $startDateTime, Carbon $finishDateTime, $staff): ?Schedule;
}
