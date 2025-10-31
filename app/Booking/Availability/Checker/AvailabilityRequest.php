<?php

namespace App\Booking\Availability\Checker;

use App\Booking\Business;
use App\Booking\Product;

class AvailabilityRequest
{
    /**
     * @var string
     */
    public ?string $timezone = null;

    /**
     * @var string
     */
    public ?string $start = null;

    /**
     * @var string
     */
    public ?string $end = null;

    /**
     * @var string
     */
    public ?string $location = null;

    /**
     * @var Business
     */
    public Business $business;

    /**
     * @var Product
     */
    public Product $product;

    /**
     * @var int
     */
    public int $quantity = 1;

    /**
     * @var bool
     */
    public bool $active = true;

    /**
     * AvailabilityData constructor.
     *
     * @param array $requestData
     */
    public function __construct(array $requestData = [])
    {
        foreach ($requestData as $key => $value) {
            $this->$key = $value;
        }
    }
}
