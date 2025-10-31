<?php

namespace App\Booking\Services;

use App\Booking\Availability\Rrule;
use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Booking\Contracts\AvailabilityManager as AvailabilityManagerContract;
use App\Booking\Models\Calendar;
use App\Domains\Auth\Models\User;
use Carbon\Carbon;

class AvailabilityManager implements AvailabilityManagerContract
{
    /**
     *  Does the business have availability on this date?
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     * @param Business $business
     * @return bool
     */
    public function businessHasStaffAvailable(Carbon $startDateTime, Carbon $finishDateTime, Business $business)
    {
        return $business->hasStaffAvailableDuring($startDateTime, $finishDateTime);
    }

    /**
     * Check if a member of staff has availability for that period.
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     * @param User $staff
     * @return bool
     */
    public function staffHasAvailability(Carbon $startDateTime, Carbon $finishDateTime, $staff)
    {
        return $staff->isAvailableDuring($startDateTime, $finishDateTime);
    }

    /**
     * Check a staff members schedule for availability?
     *
     * @return Schedule
     */
    public function staffAvailable(Carbon $searchStartDate, Carbon $searchEndDate, $staff): ?Schedule
    {
        return $staff->scheduledAvailableDuring($searchStartDate, $searchEndDate);
    }
}
