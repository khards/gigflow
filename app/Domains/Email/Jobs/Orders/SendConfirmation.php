<?php

namespace App\Domains\Email\Jobs\Orders;

use App\Domains\Email\Mailables\Booking\BookingConfirmation;
use App\Domains\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;


// Send a payment booking confirmation email to the customer.

class SendConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $eventData;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;
    }

    /**
     * Handle the event.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (! $order = Order::find($this->eventData['orderId'])) {
            throw new \Exception(__METHOD__ . ' is not a valid order ID' . $this->eventData['orderId'] . ' was not found.');
        }

        try {
            Mail::to($order->user->email)->send(
                new BookingConfirmation(['user' => $order->user, 'order' => $order])
            );
        } catch (\Exception $e) {
            report("Failed to send BookingConfirmation email for order {$order->id}", $e);
        }
    }


}
