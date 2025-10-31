<?php

namespace App\Domains\Payment\Listeners\Payment;

use App\Domains\Email\Jobs\Orders\SendConfirmation;
use App\Domains\Order\Events\OrderReady;
use App\Domains\Order\Order;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Events\PaymentTransaction;

class UpdateStatus
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
     * @param  object  $event
     * @return void
     */
    public function handle(PaymentTransaction $event)
    {
        $eventData = $event->getData();

        if (! $order = Order::find($eventData['orderId'])) {
            throw new \Exception('Error, UpdateStatus order '.$eventData['orderId'].' was not found. Unable to process payment event');
        }

        if ($order->status->equals(OrderStatus::NEW())) {
            // Set to ready if not a refund.
            //
            $thisPaymentAmount = $eventData['amount'];
            if ($thisPaymentAmount > 0) {
                $order->update(['status' => OrderStatus::BOOKED()]);
//TODO
                // Events:
                OrderReady::dispatch($eventData);

                // Jobs:
                SendConfirmation::dispatch($eventData);
            }

        }
    }

}
