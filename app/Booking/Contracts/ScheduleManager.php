<?php

namespace App\Booking\Contracts;

use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Domains\Auth\Models\User;

interface ScheduleManager
{
    /**
     * Create single schedule entry.
     *
     * @param $staff
     * @param $eventDetails
     */
    public function createSingleEvent($staff, array $eventDetails);

    /**
     * Clear staff's schedule.
     *
     * @param $staff
     */
    public function clearSchedule($staff);

    /**
     * Clear staff's holiday bookings.
     *
     * @param User $staff
     */
    public function clearHoliday(User $staff);

    /**
     * Create staff's holiday booking entry.
     *
     * @param User $staff
     * @param array $eventDetails
     * @return mixed
     */
    public function createHoliday(User $staff, array $eventDetails);

    /**
     * Get staff's holiday booking entrys.
     *
     * @param User $staff
     * @return mixed
     */
    public function getHoliday(User $staff);

    /**
     * View all schedules for a given business.
     *
     * @param Business|id $business
     * @return mixed
     */
    public function all($business);

    /**
     * @param $id
     * @return mixed
     */
    public function read($id);

    /**
     * Update a schedule.
     *
     * @param Schedule $schedule
     * @param array $scheduleDetails
     * @return Schedule
     */
    public function update(Schedule $schedule, array $scheduleDetails): Schedule;

    /**
     * Create a schedule.
     *
     * @param Business $business
     * @param array $scheduleDetails
     * @return Schedule
     */
    public function create(Business $business, array $scheduleDetails): Schedule;

    /**
     * Delete a schedule.
     *
     * @param $schedule
     * @return mixed
     */
    public function delete($schedule);
}
