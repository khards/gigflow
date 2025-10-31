<?php

namespace App\Booking\Contracts;

use App\Booking\Customer;
use Konekt\Address\Models\Address;
use Vanilo\Cart\Contracts\CartManager as CartManagerContract;
use Vanilo\Order\Models\Billpayer;
use Vanilo\Order\Models\Order;

interface OrderProcessor
{
    /**
     * Create an order with items.
     */
    public function createOrderWithItems(
        Customer $customer,
        Billpayer $billPayer,
        Address $shippingAddress,
        CartManagerContract $cart,
        array $attributes
    ): Order;
}
