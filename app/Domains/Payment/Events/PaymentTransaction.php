<?php

namespace App\Domains\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentTransaction
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
