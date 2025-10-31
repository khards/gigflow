<?php

namespace App\Domains\Payment\Contracts;

use App\Domains\Payment\Models\Transaction;

interface TransactionService
{
    /**
     * create a transaction record.
     *
     * @param  int  $orderId
     * @param  string  $method
     * @param  float  $amount
     * @param  string  $currency
     * @param  array  $data
     * @param  string  $note
     * @param  string $transactionType
     * @return Transaction
     */
    public function create(int $orderId, string $method, float $amount, string $currency, array $data, string $note, string $transactionType = '');

    /**
     * Read a transaction.
     *
     * @param int $transactionId
     * @return Transaction
     */
    public function read(int $transactionId): Transaction;
}
