<?php

//@TODO - this class is a mess!
//@TODO - it contains staff schedules and business schedules
//@TODO - Either make it more generic or break into the single classes responsible
// 1st October 2020
//

namespace App\Booking\Services;

use App\Booking\Availability\Schedule;
use App\Booking\Business;
use App\Booking\Contracts\ScheduleManager as ScheduleManagerContract;
use App\Booking\Holiday;
use App\Booking\Models\Calendar;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ScheduleManager implements ScheduleManagerContract
{
    /**
     * Create single schedule entry.
     *
     * @param $staff
     * @param $eventDetails
     */
    public function createSingleEvent($staff, array $eventDetails)
    {
        Schedule::create([
            'model_type' => $staff->getMorphClass(),
            'model_id' => $staff->id,
            'is_recurring' => false,
            'properties' => $eventDetails['properties'],
            'summary' => $eventDetails['summary'],
            'start_datetime' => $eventDetails['start_datetime'],
            'end_datetime' => $eventDetails['end_datetime'],
        ]);
    }

    /**
     * Clear staff's schedule.
     *
     * @param $staff
     */
    public function clearSchedule($staff)
    {
        Schedule::where(['model_type' => $staff->getMorphClass(), 'model_id' => $staff->id])->
        delete();
    }

    public function getHoliday(User $user)
    {
        return $user->calendar()->hols()->with('booked_by')->get();
    }

    /**
     * Create staff's holiday booking entry.
     *
     * @param User $staff
     * @param array $eventDetails
     * @return mixed
     */
    public function createHoliday(User $staff, array $eventDetails)
    {
        // I'm sure there is a better way with attach, associate or sync but I don't want to spend another 8 hours figuring it out!!!!
        // August 2020! Need to crack on!!
        // @TODO - LOL
        // @1st October 2020 - Hmmmmm. Perhaps it can be nested in the Holiday::create?
        //
        // November 2022: Haha, still at it! Nearly there though... :-)
        //

        // With a holiday
        $holiday = Holiday::create([
            'title' => Str::limit($eventDetails['summary'], 1024),
            'description' => Str::limit($eventDetails['description'], 4096)
        ]);

        // When the staff has a booking...
        $entry = Calendar::create([
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
            'booked_by_type' => $holiday->getMorphClass(),
            'booked_by_id' => $holiday->id,
            'start' => $eventDetails['startDateTime'],
            'end' => $eventDetails['endDateTime'],
            'timezone' => $eventDetails['timeZone'],
        ]);

        return $entry;
    }

    /**
     * Clear staff's Holiday.
     *
     * @param $staff
     */
    public function clearHoliday(User $staff)
    {
        $staff->calendar()->hols()->with('booked_by')
            ->each(function ($item) {
                //delete the Holiday
                $item->booked_by()->delete();
                //delete the Calendar entry
                $item->delete();
            });
    }

    //// ------ crud

    /**
     * View all schedules for a given business.
     *
     * @param Business|id $business
     * @return mixed
     */
    public function all($business)
    {
        if (! is_object($business)) {
            $business = Business::findOrFail($business);
        }

        return $business->schedule()->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        return Schedule::findOrFail($id);
    }

    /**
     * @param Product $product
     * @param array $productDetails
     * @return Product
     */
    public function update(Schedule $schedule, array $scheduleDetails): Schedule
    {
        if (! is_object($schedule)) {
            $schedule = Product::findOrFail($schedule);
        }

        $schedule->start_datetime = $scheduleDetails['start_datetime'];
        $schedule->end_datetime = $scheduleDetails['end_datetime'];

        //Automagically convert incoming time to UTC
        if ($scheduleDetails['timezone']) {
            $local = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $scheduleDetails['start_datetime'], $scheduleDetails['timezone']);
            $local->setTimezone('UTC');
            $schedule->start_datetime = $local->format(Carbon::DEFAULT_TO_STRING_FORMAT);

            $local = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $scheduleDetails['end_datetime'], $scheduleDetails['timezone']);
            $local->setTimezone('UTC');
            $schedule->end_datetime = $local->format(Carbon::DEFAULT_TO_STRING_FORMAT);
        }

        $schedule->summary = $scheduleDetails['summary'];
        $schedule->state = $scheduleDetails['state'];
        $schedule->rrule = $scheduleDetails['rrule'];
        $schedule->is_recurring = ! empty($scheduleDetails['rrule']);
        $schedule->timezone = $scheduleDetails['timezone'];
        $schedule->save();

        return $schedule;
    }

    /**
     * Create a schedule.
     *
     * @param Business $business
     * @param array $scheduleDetails
     * @return Schedule
     */
    public function create(Business $business, array $scheduleDetails): Schedule
    {
        if (! $scheduleDetails['start_datetime']) {
            $scheduleDetails['start_datetime'] = Carbon::now()->tz('UTC')->format('Y-m-d 00:00:00');
        }

        if (! $scheduleDetails['end_datetime']) {
            $scheduleDetails['end_datetime'] = Carbon::now()->tz('UTC')->format('Y-m-d 00:00:00');
        }

        return Schedule::create([
            'model_type' => $business->getMorphClass(),
            'model_id' => $business->id,
            'start_datetime' => $scheduleDetails['start_datetime'],
            'end_datetime' => $scheduleDetails['end_datetime'],
            'summary' => $scheduleDetails['summary'],
            'state' => $scheduleDetails['state'] ?? 'draft',
            'rrule' => $scheduleDetails['rrule'] ?? '',
            'is_recurring' => false,
            'timezone' => $scheduleDetails['timezone'] ?? 'etc/UTC',
            'properties' => [],
        ]);
    }

    /**
     * @param $schedule
     * @return mixed|void
     */
    public function delete($schedule)
    {
        if (! is_object($schedule)) {
            $schedule = Schedule::findOrFail($schedule);
        }
        $schedule->delete();
    }
}
