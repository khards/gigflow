<?php

namespace App\Domains\Email\Jobs\Payments;

use App\Domains\Email\Mailables\Payments\PaymentReceivedReceipt;
use App\Domains\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

// Send a payment receipt email to the customer / payee.

class SendReceipt implements ShouldQueue
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
     */
    public function handle()
    {
        // no receipt cash
        if (isset($this->eventData['type']) && ($this->eventData['type'] === 'cash' || $this->eventData['type'] === 'other')) {
            return;
        }

        if (! $order = Order::find($this->eventData['orderId'])) {
            throw new \Exception(__METHOD__ . ' is not a valid order ID. Unable to process job');
        }

        try {
            // @todo - This isn't quite correct.
            // transaction_id is passed in the eventData that transports the currency code from PayPal etc.
            // Should use that currency code instead
            $amount = $this->getFormattedAmount($order);
            $users = [$order->billpayer->getEmail()];
            Mail::to($users)->send(new PaymentReceivedReceipt([
                'order' => $order,
                'amount' => $amount
            ]));
        } catch (\Exception $e) {
            report("Failed to send PaymentReceivedReceipt email for order {$order->id} and amount {$this->eventData['amount']}", $e);
        }
    }

    /**
     * @param $order
     * @return string
     */
    private function getFormattedAmount($order): string
    {
        // @todo - This isn't quite correct.
        // transaction_id is passed in the eventData that transports the currency code from PayPal etc.
        // Should use that currency code instead of $order->business->currency

        $currencies = collect(config('currencies'));
        $currency = $currencies->where('code', $order->business->currency)->first();
        return $currency['symbol'] . $this->eventData['amount'];
    }

}
