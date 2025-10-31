<?php

namespace App\Booking\Availability;

use App\Domains\Auth\Models\User;
use RRule\RfcParser;

/**
 * This is to convert "schedule to fullcalendar".
 *
 * Class AvailabilityToFullCalendar
 */
class AvailabilityToFullCalendar
{
    public const transfom = [
        'FREQ' => 'freq',
        'INTERVAL' => 'interval',
        'WKST' => 'wkst',
        'COUNT' => 'count',
        'UNTIL' => 'until',
        'BYSETPOS' => 'bysetpos',
        'BYMONTH' => 'bymonth',
        'BYMONTHDAY' => 'bymonthday',
        'BYYEARDAY' => 'byyearday',
        'BYWEEKNO' => 'byweekno',
        'BYDAY' => 'byweekday',
        'BYHOUR' => 'byhour',
        'BYMINUTE' => 'byminute',
        'BYSECOND' => 'bysecond',
    ];

    public function convert($userId)
    {
        $user = User::findOrFail($userId);
        $calendarItems = [];
        foreach ($user->availability()->get() as $entry) {
            /** @var Schedule $entry */
            $data = $entry->toArray();

            $calItem = new \stdClass();
            if ($entry->rrule) {
                $calItem->title = $entry->summary;

                $rruleParts = RfcParser::parseRRule($entry->rrule, $entry->start_datetime);
                $rruleParts = array_change_key_case($rruleParts, CASE_UPPER);
                $calItem->rrule = new \stdClass();
                foreach ($rruleParts as $ruleKey => $ruleValue) {
                    if (isset(self::transfom[$ruleKey])) {
                        $calItem->rrule->{self::transfom[$ruleKey]} = $ruleValue;
                    }
                    if ($ruleKey === 'DTSTART') {
                        $calItem->rrule->tzid = $ruleValue->getTimezone()->getName();
                        $calItem->rrule->dtstart = $ruleValue->format('Y-m-d\TH:i:s');
                    }
                }

                $date1 = new \DateTime($entry->end_datetime);
                $date2 = new \DateTime($entry->start_datetime);
                $seconds = ($date1->getTimestamp() - $date1->getOffset()) - ($date2->getTimestamp() - $date2->getOffset());
                $calItem->duration = [
                    'seconds' => $seconds,
                ];
            } else {
                $calItem->title = $data['summary'];
                $calItem->start = $data['start_datetime'];
                $calItem->end = $data['end_datetime'];
            }
            $calendarItems[] = $calItem;
        }

        return $calendarItems;
    }
}
