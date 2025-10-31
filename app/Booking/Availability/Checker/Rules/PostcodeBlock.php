<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Address;
use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;
use App\Booking\Contracts\AvailabilityManager;
use App\Booking\Services\DistanceService;
use Illuminate\Support\Carbon;

class PostcodeBlock extends RuleParent
{
    /**
     * @var AvailabilityData
     */
    private $availabilityData;

    /** @var DistanceService */
    private $distanceService;

    public function __construct(AvailabilityManager $availabilityManager)
    {
        parent::__construct($availabilityManager);

        $this->distanceService = new DistanceService();
    }

    public function handle(AvailabilityData $availabilityData, \Closure $next)
    {
        $this->availabilityData = $availabilityData;

        if ($availabilityData->request->location === null) {
            $availabilityData->response->log[__CLASS__] =
                'Missing location so can\'t check postcode blocking';
            return $next($availabilityData);
        }

        $blockedPostcodes = explode(',', $availabilityData->request->product->settings->get('blocked_postcodes'));
        if(!$blockedPostcodes) {
            $availabilityData->response->log[__CLASS__] =
                'No blocked postcodes, continuing';
            return $next($availabilityData);
        }

        /**
         * @var Address $origin
         */
        $origin = $availabilityData->request->product->business->address;

        $distanceInfo = $this->distanceService->getDistance(
            $origin->postalcode,
            $origin->formatSingleLine(),
            $availabilityData->request->location
        );

        foreach($blockedPostcodes as $postcode) {
            $postcode = trim($postcode);
            if($postcode) {
                if (str_contains($distanceInfo->destination_address, $postcode)) {
                    $availabilityData->response->log[__CLASS__] = "The postcode is blocked.";
                    $availabilityData->response->info['reason'] = 'location';
                    $availabilityData->response->info['reason_message'] = 'Please try a different location';
                    return;
                }
            }
        }

        return $next($availabilityData);
    }
}
