<?php

namespace App\Booking\Availability\Checker;

class AvailabilityResponse
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * @var string
     */
    public $status = self::STATUS_UNAVAILABLE;

    /**
     * @var int
     */
    public $quantityAvailable;

    /**
     * @var array
     */
    public array $log = [];

    /**
     * The idea of info is it will return help messages, suggested start and end times etc.
     * It will help people booking, this data will be used by the frontend booking widget.
     *
     * @var array
     */
    public array $info = [];
}
