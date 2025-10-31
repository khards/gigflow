<?php

namespace App\Booking\Contracts;

use App\Domains\Order\Order;

interface OrderManager
{
    /**
     * @param Order $order
     * @param $status
     */
    public function updateStatus(Order $order, $status): void;

    /**
     * Delete an order with it's associated items, bookings.
     */
    public function delete(Order $order): void;
}
