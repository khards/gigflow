<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;
use App\Booking\Business;
use App\Booking\Models\Calendar;
use App\Booking\Product;
use App\Booking\Services\DistanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StaffAvailable extends RuleParent
{
    public function handle(AvailabilityData $availabilityData, \Closure $next)
    {
        // If no staff required for service, then continue
        if ($availabilityData->request->product->staff_quantity == 0) {
            $availabilityData->response->log[__CLASS__] =
                'The requested product/service does not require any staff - all ok.';
            return $next($availabilityData);
        }

        if ($availabilityData->request->start === null || $availabilityData->request->end === null) {
            $availabilityData->response->log[__CLASS__] =
                'Missing start, or end datetime so can\'t check staff availability';
            return $next($availabilityData);
        }

        if (!$availabilityData->request->product->isDelivered()) {
            $availabilityData->response->log[__CLASS__] =
                'Product is NOT delivered (staffed service), so staff availability will check be skipped';
            return $next($availabilityData);
        }

        if (!$availabilityData->request->location) {
            $availabilityData->response->log[__CLASS__] =
                'No location has been provided, staff availability will check be skipped as we can\'t
                    calculate travelling time';
            return $next($availabilityData);
        }

        // Check start and end times against previous and later gig star and end times
        $staffAtStartOfDay = $this->checkFirstGigOfTheDay($availabilityData);
        if (!$staffAtStartOfDay->count()) {

            $availabilityData->response->log[__CLASS__] =
                "Time we leave home cannot be met, blocking this gig - " .
                "Try a later start time {$availabilityData->request->start} and {$availabilityData->request->end}";

            $availabilityData->response->info['reason'] = 'times';
            $availabilityData->response->info['reason_message'] = 'Unavailable at these times. Please try a later time';

            return $availabilityData;
        }
        $info = [];
        $staffThatDontClashWithEarlyLateGigs = $this->checkEarlyLateGigs(
            $availabilityData, $staffAtStartOfDay, $info);

        if ($staffThatDontClashWithEarlyLateGigs->count() < $availabilityData->request->product->staff_quantity) {
            $availabilityData->response->log[__CLASS__] =
                "Clash with earlier or later gigs, blocking this gig - " .
                "Try a earlier/later start time {$availabilityData->request->start} and {$availabilityData->request->end}";

            $availabilityData->response->info['product_id'] = $availabilityData->request->product->id;
            $availabilityData->response->info['reason'] = 'times';
            $availabilityData->response->info['reason_message'] = 'Unavailable at this time. Please try a different date';
            $availabilityData->response->info['reason_messages'] = implode(', ', $info);

            return $availabilityData;
        }

        // All is good!
        $availabilityData->response->log[__CLASS__]
            = "OK. have staff available between {$availabilityData->request->start} and {$availabilityData->request->end}";

        return $next($availabilityData);
    }


    // Valid if no previous bookings finishing on that day.
    // If there is a disco finishing say 6am and booking a kids disco for 9am, it may not work.
    protected function getTimeWeMustLeaveTheHouse(AvailabilityData $availabilityData): Carbon|false
    {
        $product = $availabilityData->request->product;
        $timeWeMustLeaveTheHouse = Carbon::createFromFormat('Y-m-d H:i:s', $availabilityData->request->start);

        // Subtract the setup time from the start time as we'll need to leave the house early to get setup!
        $setupTime = $product->totalSetupTime();
        if ($setupTime) {
            $timeWeMustLeaveTheHouse->subMinutes($setupTime);
        }

        $origin = $availabilityData->request->product->business->address;
        $response = $this->getDistanceInfo($origin->postalcode, $origin->formatSingleLine(), $availabilityData);
        if ($response->status !== 'OK') {
            return false;
        }

        $timeWeMustLeaveTheHouse->subMinutes($response->getMinutes());

        return $timeWeMustLeaveTheHouse;
    }

    /**
     * Check start and end times against previous and later gig star and end times
     *
     * @param AvailabilityData $availabilityData
     * @param Product $product
     * @return bool
     */
    private function checkEarlyLateGigs(AvailabilityData $availabilityData, Collection $staff, &$info)
    {

        $product = $availabilityData->request->product;
        $requestedGigStart = Carbon::createFromFormat('Y-m-d H:i:s', $availabilityData->request->start);
        $requestedGigEnd = Carbon::createFromFormat('Y-m-d H:i:s', $availabilityData->request->end);
        $setupTime = $product->totalSetupTime();

        foreach($staff as $key => $user) {
            $gigsFinishingEarlier = new Collection();
            $gigsStartingLater = new Collection();

            // Find gigs finishing earlier that same day
            $this->findGigsFinishingEarlierThatSameDay($user, $requestedGigStart, $gigsFinishingEarlier);

            // Find gigs starting later that same day
            $this->findGigsStartingLaterThatSameDay($user, $requestedGigStart, $gigsStartingLater);

            if ($gigsStartingLater->count()) {

                /** @var Calendar $nextDisco */
                $nextDisco = $gigsStartingLater->sortBy('start')->first();
                $nextBooking = $nextDisco->booking()->first();
                $nextLocation = $nextBooking->location;
                $nextDiscoStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $nextDisco->start);
                if($nextLocation) {
                    $response = $this->getDistanceInfo($nextLocation, $nextLocation, $availabilityData);
                }
                if (!$nextLocation || $response->status !== 'OK') {
                    unset($staff[$key]);
                    continue;
                    //return false; // Terminate Error
                }

                if ($requestedGigEnd->addMinutes($setupTime + $response->getMinutes()) >= $nextDiscoStartTime) {
                    unset($staff[$key]);
                    $info[] = 'Event must finish earlier than ' . $requestedGigEnd->format('l jS \\of F Y h:i');
                    continue; // Not available
                }
            }

            if ($gigsFinishingEarlier->count()) {
                /** @var Calendar $nextDisco */
                $previousDisco = $gigsFinishingEarlier->sortByDesc('end')->first();
                $previousBooking = $previousDisco->booking()->first();
                $previousLocation = $previousBooking->location;
                $previousDiscoEnd = Carbon::createFromFormat('Y-m-d H:i:s', $previousDisco->end);

                if($previousLocation) {
                    $response = $this->getDistanceInfo($previousLocation, $previousLocation, $availabilityData);
                }
                if (!$previousLocation || $response->status !== 'OK') {
                    unset($staff[$key]);
                    continue;
                    //return false; // Terminate Error
                }

                $st = $requestedGigStart->subMinutes($setupTime + $response->getMinutes());
                if ($previousDiscoEnd > $st) {
                    $info[] = 'Event must start later than ' . $st->format('l jS \\of F Y h:i');
                    unset($staff[$key]);
                    continue; // Not available
                }
            }
        }

        return collect($staff); // Staff available
    }

    private function findGigsFinishingEarlierThatSameDay($user, Carbon $requestedGigStart, Collection $gigsFinishingEarlier): void
    {
        $startOfDay = $requestedGigStart->format('Y-m-d') . ' 00:00:00';

        $user
            ->calendar()
            ->where('end', '>=', $startOfDay)
            ->where('end', '<=', $requestedGigStart->format('Y-m-d H:i:s'))
            ->get()
            ->each(function ($model) use ($gigsFinishingEarlier) {
                $gigsFinishingEarlier->add($model);
            });
    }

    private function findGigsStartingLaterThatSameDay($user, Carbon $requestedGigStart, Collection $gigsStartingLater): void
    {
        $end = clone($requestedGigStart);
        $midnight = $end->addDay()->format('Y-m-d') . ' 00:00:00';

        $user
            ->calendar()
            ->where('start', '>=', $requestedGigStart->format('Y-m-d H:i:s'))
            ->where('start', '<=', $midnight)
            ->get()
            ->each(function ($model) use ($gigsStartingLater) {
                $gigsStartingLater->add($model);
            });
    }

    private function getDistanceInfo($startPostcode, $startAddress, AvailabilityData $availabilityData): \Pnlinh\GoogleDistance\Response
    {
        $distanceService = new DistanceService();
        $response = $distanceService->getDistance(
            $startPostcode,
            $startAddress,
            $availabilityData->request->location
        );
        if ($response->status !== 'OK') {
            //Don't var_export response as you get an infinate loop!
            $availabilityData->response->log[__CLASS__] = 'Error ('.__METHOD__.'), google matrix API returned an error ' . $response->exception->getMessage();
        }
        return $response;
    }

    private function checkFirstGigOfTheDay(AvailabilityData $availabilityData)
    {
        $business = $availabilityData->request->business;

        // Get the number of staff (when leaving home and no earlier or later gigs)
        $timeWeMustLeaveIncDrivingAndSetup = $this->getTimeWeMustLeaveTheHouse($availabilityData);
        $timeWeWillFinishIncDrivingAndPackup = Carbon::createFromFormat('Y-m-d H:i:s', $availabilityData->request->end);

        return $business->staffAvailableDuring(
            $timeWeMustLeaveIncDrivingAndSetup,
            $timeWeWillFinishIncDrivingAndPackup
        );
    }

}
