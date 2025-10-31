<?php
namespace App\Domains\Payment\Observers;

use App\Domains\Payment\Events\PaymentTransaction as PaymentTransactionEvent;
use App\Domains\Payment\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction)
    {
        PaymentTransactionEvent::dispatch([
            'orderId' => $transaction->order_id,
            'amount' => $transaction->amount,
            'type' => $transaction->method, //, //Type is currently unused, so replaced it for method.!
            'transaction_id' => $transaction->id
        ]);
    }
}
