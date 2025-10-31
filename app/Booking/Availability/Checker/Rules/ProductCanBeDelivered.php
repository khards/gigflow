<?php

namespace App\Booking\Availability\Checker\Rules;

use App\Booking\Address;
use App\Booking\Availability\Checker\AvailabilityData;
use App\Booking\Availability\Checker\RuleParent;
use App\Booking\Contracts\AvailabilityManager;
use App\Booking\Services\DistanceService;

class ProductCanBeDelivered extends RuleParent
{
    /** @var AvailabilityData */
    private $data;

    /** @var DistanceService */
    private $distanceService;

    public function __construct(AvailabilityManager $availabilityManager)
    {
        parent::__construct($availabilityManager);

        $this->distanceService = new DistanceService();
    }

    public function handle(AvailabilityData $data, \Closure $next)
    {
        $this->data = $data;

        if (empty($this->data->request->location)) {
            $data->response->log[__CLASS__][] = "The product CAN be delivered no NO LOCATION okay.";
            return $next($data);
        }

        $withinTravellingLimit = $this->withinTravellingLimit();
        if ($this->data->request->product->isDelivered()) {
            if ($withinTravellingLimit) {
                $data->response->log[__CLASS__][] = "The product CAN be delivered WITHIN DISTANCE okay.";
                return $next($data);
            }
        }

        if ($this->data->request->product->isShipped()) {
            $data->response->log[__CLASS__][] = "The product CAN be shipped okay.";
            return $next($data);
        }

        $data->response->log[__CLASS__][] = "The product can't be delivered or shipped.";

        if($this->data->request->product->isDelivered() && !$withinTravellingLimit) {
            $data->response->info['reason'] = 'distance';
            $data->response->info['reason_message'] = 'Not available in your area.';
        }

        return false;
    }

    /**
     * Are we within the given travelling time/distance ?
     *
     * @param string $origin
     * @param string $destination
     * @return bool
     */
    private function withinTravellingLimit()
    {
        if ($this->data->request->product->travelling_limit == 'no') {
            return true;
        }

        /**
         * @var Address $origin
         */
        $origin = $this->data->request->product->business->address;

        $response = $this->distanceService->getDistance(
            $origin->postalcode,
            $origin->formatSingleLine(),
            $this->data->request->location
        );

        if ($response->status !== 'OK') {
            //Don't var_export response as you get an infinate loop!
            $this->data->response->log[__CLASS__][] = 'Error, google matrix API returned an error '.var_export($response, true);
            return false;
        }

        if ($this->data->request->product->travelling_type == 'miles') {
            $this->data->response->log[__CLASS__][] = "Travelling distance in miles - stats:\n".var_export($response, true);

            return $response->getMiles() <= $this->data->request->product->travelling_value;
        } elseif ($this->data->request->product->travelling_type == 'minutes') {
            $this->data->response->log[__CLASS__][] = "Ok, within travelling distance in minutes:\n".var_export($response, true);

            return $response->getMinutes() <= $this->data->request->product->travelling_value;
        } elseif ($this->data->request->product->travelling_type == 'kilometers') {
            $this->data->response->log[__CLASS__][] = "Ok, within travelling distance in kilometers:\n".var_export($response, true);

            return $response->getKilometers() <= $this->data->request->product->travelling_value;
        }

        $this->data->response->log[__CLASS__][] = "Default to can't be delievered. Is product setup for delivery?";

        return false;
    }
}
