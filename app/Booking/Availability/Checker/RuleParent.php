<?php

namespace App\Booking\Availability\Checker;

use App\Booking\Contracts\AvailabilityManager;

abstract class RuleParent
{
    /**
     * @var AvailabilityManager
     */
    protected $availabilityManager;

    public function __construct(AvailabilityManager $availabilityManager)
    {
        $this->availabilityManager = $availabilityManager;
    }

    abstract public function handle(AvailabilityData $data, \Closure $next);
}
