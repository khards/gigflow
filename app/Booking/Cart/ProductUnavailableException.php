<?php

namespace App\Booking\Cart;

use App\Booking\Availability\Checker\AvailabilityData;
use Exception;
use Throwable;

class ProductUnavailableException extends Exception
{
    /**
     * @var AvailabilityData
     */
    public $availabilityData;

    public function __construct(AvailabilityData $availabilityData)
    {
        $this->result = $availabilityData;

        parent::__construct();
    }

    protected $message = 'Product is unavailable';
}
