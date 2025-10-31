<?php

namespace App\Booking\Availability;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\Constraint\AfterConstraint;

class Rrule
{
    /**
     * Transfer an Rrule string into a collection of datetimes to use by search.
     *
     * The generated collection is currently limited to 30 days back and 1 day after the search period
     *
     * There is also a limit of $virtualLimit = 732 recurrences set in ArrayTransformerConfig
     *
     * If a schedule repeats
     *      once per month, it's past the end of my life!
     *      once per week, then this is 14 years
     *      once per day, then it's 2 years.       __DANGER__
     *
     *          into the future at that point it will be slow and fail
     * It is possible to get the last repeating date and store that as the start of the schedule, but we will leave this until the tool becomes popular
     * and it's an issue as lots to do.
     *
     * @param $eventRrule
     * @param $eventDtstart
     * @param $eventDtend
     * @param DateTime $searchStartDate
     * @param DateTime $searchEndDate
     * @return Recurrence[]|RecurrenceCollection
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public static function transformRrule($eventRrule, $eventDtstart, $eventDtend, DateTime $searchStartDate, DateTime $searchEndDate)
    {
        $eventRrule = self::tidyEventRrule($eventRrule);

        $rule = new Rule($eventRrule, $eventDtstart, $eventDtend);

        // Limit the forward time UNTIL the day after the event ends.
        $searchForwardInterval = new DateInterval('P1D');

        $generateMaxDate = (clone $searchEndDate)->add($searchForwardInterval);
        $rule->setUntil($generateMaxDate);

        // Generate date from 30 days back to handle events that repeat every 30 days and started some time ago.
        // For example you may have repeat for a week, every month. If you started generating events from today then
        // you would miss a repeating event that triggered a week ago for a week!
        //
        // Should this period be longer? We'll see..
        // $searchBackInterval = new DateInterval('P30D');
        $searchBackInterval = new DateInterval('P1Y');

        $generateFromDate = (clone $searchStartDate)->sub($searchBackInterval);
        $transformer = new ArrayTransformer();
        $afterConstraint = new AfterConstraint($generateFromDate, true);

        return $transformer->transform($rule, $afterConstraint);
    }

    /**
     * Search for an event starting or ending within the search period.
     **
     * @param $transformed
     * @param Carbon $searchStartDate
     * @param Carbon $searchEndDate
     *
     * @return array [
     *      bool startFound
     *      bool endFound
     * ]
     */
    public static function search(RecurrenceCollection $transformed, DateTime $searchStartDate, DateTime $searchEndDate)
    {
        $startFound = false;
        $endFound = false;
        foreach ($transformed as $recurrence) {
            /** @var $recurrence Recurrence */
            $recurringStart = $recurrence->getStart();
            $recurringEnd = $recurrence->getEnd();
            if (! $startFound) {
                $startFound = ($recurringStart <= $searchStartDate) && ($recurringEnd >= $searchStartDate);
            }
            if (! $endFound) {
                $endFound = ($recurringStart <= $searchEndDate) && ($recurringEnd >= $searchEndDate);
            }
            if ($startFound && $endFound) {
                break;
            }
        }

        return [
            'startFound' => $startFound,
            'endFound' => $endFound,
        ];
    }

    /**
     * Search for an event starting within the search period.
     *
     * @param $transformed
     * @param Carbon $searchStartDate
     * @param Carbon $searchEndDate
     *
     * @return array [
     *      bool startFound
     *      bool endFound
     * ]
     */
//    public static function searchStartsWithin(RecurrenceCollection $transformed, DateTime $searchStartDate, DateTime $searchEndDate)
//    {
//        foreach ($transformed as $recurrence) {
//            /** @var $recurrence Recurrence */
//            $recurringStart = $recurrence->getStart();
//            $recurringEnd = $recurrence->getEnd();
//
//            // 1-1-22 ($recurringStart) - 1-3-22 {$recurringEnd}
//            // is 31-12-22 ($searchStartDate) within those?
//            if ($searchStartDate >= $recurringStart && $searchStartDate <= $recurringEnd) {
//                return true;
//            }
//        }
//        return false;
//    }

    // Strip out DTSTART for DTSTART:20220101T000000Z\r\nRRULE:FREQ=YEARLY;INTERVAL=1
    public static function tidyEventRrule(string $eventRrule): string
    {
        if (str_contains($eventRrule, 'DTSTART')) {
            $rruleTmp = strtoupper($eventRrule);
            $rruleTmp = trim($rruleTmp, ';');
            $rruleTmp = trim($rruleTmp, "\n");
            $rows = explode("\n", $rruleTmp);

            $rruleArray = array();
            foreach ($rows as $rruleForRow) {
                if (!str_contains($rruleForRow, 'DTSTART')) {
                    $rruleArray[] = $rruleForRow;
                }
            }
            $eventRrule = implode("\r\n", $rruleArray);
        }
        return $eventRrule;
    }

}
