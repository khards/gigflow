<?php

namespace App\Domains\Payment\Listeners\Payment;

use App\Domains\Email\Jobs\Payments\SendReceipt;
use App\Domains\Order\Order;
use App\Domains\Payment\Events\PaymentTransaction;


class CreateReceiptEmailJob
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * This event is responsible for sending a payment receipt to the customer.
     *
     * @param PaymentTransaction $event
     * @return void
     * @throws \Exception
     */
    public function handle(PaymentTransaction $event)
    {
        $eventData = $event->getData();

        $exists = Order::where('id', $eventData['orderId'])->exists();

        if (! $exists) {
            throw new \Exception('Error, order '.$eventData['orderId'].' was not found. Unable to process payment event');
        }

        SendReceipt::dispatch($eventData);
    }

}
