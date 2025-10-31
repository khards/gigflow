<?php

namespace App\Domains\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// The order is ready
// Either someone has paid a deposit, or the staff has manually moved the order into
// this state as either payment is not needed or the customer paid in some other way
//
class OrderReady
{
    use Dispatchable; /*InteractsWithSockets,*/use SerializesModels;

    private array $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the event data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
