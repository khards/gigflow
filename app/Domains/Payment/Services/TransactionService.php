<?php

namespace App\Domains\Payment\Services;

use App\Domains\Payment\Contracts\TransactionService as TransactionServiceContract;
use App\Domains\Payment\Events\PaymentTransaction as PaymentTransactionEvent;
use App\Domains\Payment\Models\Transaction;

class TransactionService implements TransactionServiceContract
{
    public function create(int $orderId, string $method, float $amount, string $currency, array $data, string $note, string $transactionType = '')
    {
        $transaction = Transaction::create([
            'order_id' => $orderId,
            'method' => $method,
            'amount' => $amount,
            'note' => $note,
            'details' => $data,
            'currency' => $currency,
        ]);

        // Moved PaymentTransactionEvent to an observer so it's always triggered
        // even if not using the service! Kind of makes the service pattern a little redundant.

        return $transaction;
    }

    /**
     * Read.
     *
     * @param int $transactionId
     * @return void
     */
    public function read(int $transactionId): Transaction
    {
        return Transaction::find($transactionId);
    }
}
