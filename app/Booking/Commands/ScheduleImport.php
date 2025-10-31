<?php

namespace App\Booking\Commands;

use App\Booking\Availability\Schedule;
use App\Booking\Contracts\ScheduleManager;
use App\Domains\Auth\Models\User;
use Carbon\Carbon;
use Exception;
use ICal\ICal;
use Illuminate\Console\Command;

/**                     STAFF PRICE IMPORT (Not used yet), only product price import is used.
 *
 * Simple schedule import by storing event with r-rules into database.
 *
 * If you need to search peoples schedules then you will need to pull out schedules for limiting by start and end date
 * You can also reduce the number of staff prior via geographical restriction, then using IN subquery
 *
 * Class ScheduleImportRrule
 */
//  php artisan larabook:schedule-import 3 https://calendar.google.com/calendar/ical/7q272ck9gop6c9norpjp87d9mg%40group.calendar.google.com/private-c48c4d8714fd753e6044e5c187b20edc/basic.ics
class ScheduleImport extends Command
{
    /**
     * @var ScheduleManager
     */
    private $scheduleManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabook:schedule-import {userid : The user id to import the schedule into} {icsurl : The ICS URL} {--tag=[schedule]}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an ICS calendar into a users schedule';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ScheduleManager $scheduleManager)
    {
        $this->scheduleManager = $scheduleManager;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('icsurl');
        $userId = $this->argument('userid');
        $tag = $this->option('tag');

        $staff = User::findOrFail($userId);

        try {
            $ical = new ICal(false, [
                'skipRecurrence' => true, /* Do NOT expand repeating events. */
            ]);
            $ical->initUrl($url, $username = null, $password = null, $userAgent = null);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 1; // General error
        }

        $this->info('Calendar url: '.$url);
        $this->info('Name: '.$ical->calendarName());
        $this->info('Description: '.$ical->calendarDescription());
        $this->info('User ID: '.$userId);
        $this->info('Tag: '.$tag);
        $this->info('User name "'.$staff->name).'"';

        $newItems = [];
        $existingItems = [];
        foreach ($this->getEvents($ical) as $event) {

            // Could check $event->status==CONFIRMED

            // Check calendar entry contains tag.
            if (stripos($event->description, $tag) === false) {
                continue;
            }

            // Get the r-rule if there is one.
            $rrule = null;
            if (property_exists($event, 'rrule_array') && ! empty($event->rrule_array[1])) {
                $rrule = $event->rrule_array[1];
            }

            $dtstart = $ical->iCalDateToDateTime($event->dtstart);
            $dtend = $ical->iCalDateToDateTime($event->dtend);

            $item = [
                'start_datetime' => $dtstart->format(Carbon::DEFAULT_TO_STRING_FORMAT),
                'end_datetime' => $dtend->format(Carbon::DEFAULT_TO_STRING_FORMAT),
                'summary' => $event->summary,
                'rrule' => $rrule,
                'extuid' => $event->uid,
                'model_type' => $staff->getMorphClass(),
                'model_id' => $staff->id,
                'properties' => $this->parseCustomParamsFromDescription($event->description),
            ];

            $existing =
                Schedule::where('extuid', '=', $event->uid)
                ->where('model_type', '=', $staff->getMorphClass())
                ->where('model_id', '=', $staff->id)->first();

            // During testing we import the same calendar ID for the same user.
            // @TODO - sort out this clusterfuck.
            $testing = config('testing.allow_duplicate_events_to_be_added', false);

            if ($existing && !$testing) {
                $existingItems[] = $item;
                $existing->update($item);
            } else {
                $newItems[] = $item;
            }
        }

        /**
         *  This is run every 5 minutes, so is creating a LOT of trashed entries.
         *  These are ALL staff schedules and have nothing tied to them. model=staff and delete_at !=null.
         *  They are simply referenced during booking to check the staffs availability prior to booking
         *  All the other schedules are valid and don't seem to be problematic.
         *
         *  For the same of the primary id, I think it would be best to update on extuid
         *  Another cron will delete where deleted_at and model type=user.
         *
         */
        // Clear staff schedule - Will end up black if there are on [schedule] tags.
        // $this->scheduleManager->clearSchedule($staff);

        // Delete any old schedules that have been removed from the imported calendar.
        Schedule::where([
                'model_type' => $staff->getMorphClass(),
                'model_id' => $staff->id]
        )
            ->whereNotIn('extuid', collect($existingItems)->pluck('extuid')->toArray())
            ->delete();

        if (count($newItems)) {

            // Insert don't work due to 'properties' and schemaless attibutes.
            // Would need to json_encode properties. Then there seems to be issues decoding later.
            //Schedule::insert($newItems);

            foreach($newItems as $item) {
                $newSchedule = new Schedule($item);
                $newSchedule->save();
            }
        }
    }

    /**
     * get events from calendar.
     *
     * @param Ical $ical
     * @return array
     */
    protected function getEvents(ICal $ical)
    {
        return $ical->sortEventsWithOrder($ical->events());
    }

    /**
     * extract custom properties from a given calendar description
     * For example: [price=500]
     *
     * @param $description
     * @return array
     */
    private function parseCustomParamsFromDescription(string $description): array
    {
        // Look for text enclosed within [square brackets]
        preg_match_all('/\[(.*?)\]/', $description, $matches);
        $params = $matches[1];
        $processed = [];
        foreach ($params as $param) {
            // Look for X=Y
            $parts = explode('=', $param);
            if (count($parts) > 1) {
                //Save them as key value pairs
                $processed[$parts[0]] = trim(strip_tags($parts[1]));
            }
        }

        return $processed;
    }
}
