<?php
/**
 * Calendar import used for Holidays, NOT schedules.
 */

namespace App\Booking\Commands;

use App\Booking\Contracts\ScheduleManager;
use App\Domains\Auth\Models\User;
use Carbon\Carbon;
use Exception;
use ICal\Event;
use ICal\ICal;
use Illuminate\Console\Command;

// HOLIDAY IMPORT -> todo rename this class and command name
class CalendarImport extends Command
{
    /**
     * @var ScheduleManager
     */
    private ScheduleManager $scheduleManager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabook:calendar-import {userid : The user id to import the calendar into} {icsurl : The ICS URL} {--tag=[holiday]}';

    // Test URL = https://calendar.google.com/calendar/ical/keithhards%40gmail.com/private-fa1038ee56ff0f6cdd8b754cdb7b8664/basic.ics

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an ICS calendar into a users holiday';

    /**
     * CalendarImport constructor.
     * @param ScheduleManager $scheduleManager
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

        $this->info('Calendar url: '.$url);
        $this->info('User ID: '.$userId);
        $this->info('Tag: '.$tag);

        $this->info('User name '.$staff->name);

        try {
            $ical = new ICal(false, ['defaultSpan' => 5 /* years for repeating events */]);
            $ical->initUrl($url, $username = null, $password = null, $userAgent = null);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->info('The number of events: '.$ical->eventCount);
        $this->info('The number of free/busy time slots: '.$ical->freeBusyCount);
        $this->info('The number of todos: '.$ical->todoCount);
        $this->info('The number of alarms: '.$ical->alarmCount);

        $now = Carbon::now()->subtract('day', 1)->format(Carbon::DEFAULT_TO_STRING_FORMAT);
        $end = Carbon::now()->addCentury()->format(Carbon::DEFAULT_TO_STRING_FORMAT);

        $this->info("From: {$now} End: {$end}");

        $events = $ical->eventsFromRange($now, $end);

        $this->scheduleManager->clearHoliday($staff);

        foreach ($events as $event) {
            if (stripos($event->description, $tag) === false) {
                continue;
            }

            $this->createHoliday($staff, $event, $ical);

            $dtstart = $ical->iCalDateToDateTime($event->dtstart);
            $dtend = $ical->iCalDateToDateTime($event->dtend);

            $this->info('');
            $this->info('');
            $this->info(' ('.$dtstart->format('d-m-Y H:i').' - '.$dtend->format('d-m-Y H:i').')');
            $this->info($event->summary);
        }
    }

    /**
     * Create a holiday entry.
     *
     * @param $staff
     * @param Event $event
     * @param $ical
     *
     * @return mixed
     */
    private function createHoliday($staff, Event $event, $ical)
    {
        $startDateTime = $ical->iCalDateToDateTime($event->dtstart);
        $endDateTime = $ical->iCalDateToDateTime($event->dtend);
        $timeZone = $ical->calendarTimeZone();
        $eventDetails = [
            'summary' => $event->summary,
            'description' => $event->description,
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'timeZone' => $timeZone,
        ];

        return $this->scheduleManager->createHoliday($staff, $eventDetails);
    }
}
