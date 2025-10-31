<?php

namespace App\Domains\Payment\Services;

class DepositService
{
    /**
     * Calculate the deposit - WIP.
     *
     * @param float $totalPrice
     * @return float
     */
    public function calculateDeposit(float $totalPrice): float
    {
        if ($totalPrice == 0) {
            return 0.00;
        }

        // @todo - deposit config!
        $percentDeposit = 15;
        $maxDeposit = 59.00;            /// TODO - move to config
        $minDeposit = 20.00;

        $deposit = $totalPrice * ($percentDeposit / 100);
        $deposit = round($deposit);
        $deposit = $deposit > $maxDeposit ? $maxDeposit : $deposit;

        return $deposit < $minDeposit ? $minDeposit : $deposit;
    }
}
