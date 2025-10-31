<?php

namespace App\Booking\Availability\Checker;

class AvailabilityData
{
    /**
     * @var AvailabilityRequest
     */
    public $request;

    /**
     * @var AvailabilityResponse
     */
    public $response;

    /**
     * AvailabilityData constructor.
     *
     * @param AvailabilityRequest $request
     * @param AvailabilityResponse $response
     */
    public function __construct(AvailabilityRequest $request, AvailabilityResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
