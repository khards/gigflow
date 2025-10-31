<?php

namespace App\Booking\Availability\Traits;

use App\Booking\Availability\Rrule;
use App\Booking\Availability\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;

trait ScheduledAvailable
{
    protected function availabilitySchedules()
    {
        return Schedule::where('model_id', $this->id)->where('model_type', $this->getMorphClass());
    }

    /**
     * Check something's schedule for availability.
     *
     * @param Carbon $searchStartDate
     * @param Carbon $searchEndDate
     * @return Schedule|null
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function scheduledAvailableDuring(Carbon $searchStartDate, Carbon $searchEndDate): ?Schedule
    {
        $schedules = $this->availabilitySchedules()->get();

        $startFound = $endFound = false;
        foreach ($schedules as $entry) {
            $dtstart = $entry->start_datetime;
            $dtend = $entry->end_datetime;
            $rrule = $entry->rrule;

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
                    return $entry;
                }
            } else {
                $start = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $dtstart);
                $end = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $dtend);

                $startFound = ($start <= $searchStartDate) && ($end >= $searchStartDate);
                $endFound = ($start <= $searchEndDate) && ($end >= $searchEndDate);
                if ($startFound && $endFound) {
                    return $entry;
                }
            }
        }

        return null;
    }

    /**
     * Is the search start time within the schedule?
     *
     * @param Carbon $searchStartDate
     * @param Carbon $searchEndDate
     * @return Schedule|null
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function scheduledStarting(Carbon $searchStartDate, Carbon $searchEndDate, bool $withRecurring = true): ?Schedule
    {
        $startingSchedule = null;

        foreach ($this->availabilitySchedules()->get() as $schedule) {
            $dtStart = $schedule->start_datetime;
            $dtEnd = $schedule->end_datetime;
            $rrule = $schedule->rrule;

            if(!$withRecurring && $rrule) {
                continue;
            }

            if ($rrule) {
                $transformed = Rrule::transformRrule($rrule, $dtStart, $dtEnd, $searchStartDate->toDateTime(), $searchEndDate->toDateTime());
                foreach ($transformed as $recurrence) {
                    // 1-12-22 ($recurringStart) - 31-12-22 {$recurringEnd}
                    // is 31-12-22 ($searchStartDate) within those?
                    if (
                        ($searchStartDate->toDateTime() >= $recurrence->getStart()) &&
                        ($searchStartDate->toDateTime() <= $recurrence->getEnd())
                    ) {
                        $startingSchedule = $schedule;
                        break;
                    }
                }

                if ($startingSchedule) {
                    return $schedule;
                }
            } else {
                $start = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $dtStart);
                $end = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $dtEnd);
                $startFound = ($start <= $searchStartDate) && ($end >= $searchStartDate);

                if ($startFound) {
                    return $schedule;
                }
            }
        }

        return null;
    }

}
