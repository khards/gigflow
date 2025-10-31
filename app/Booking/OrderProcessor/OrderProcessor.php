<?php

namespace App\Booking\OrderProcessor;

use App\Booking\Cart\CartManager;
use App\Booking\Contracts\AvailabilityManager;
use App\Booking\Contracts\OrderProcessor as OrderProcessorContract;
use App\Booking\Customer;
use App\Domains\Auth\Models\User;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Services\DepositService;
use Konekt\Address\Models\Address;
use Vanilo\Cart\Contracts\CartManager as CartManagerContract;
use Vanilo\Order\Generators\SequentialNumberGenerator;
use Vanilo\Order\Models\Billpayer;
use Vanilo\Order\Models\Order;

/**
 * Class OrderProcessor.
 */
class OrderProcessor implements OrderProcessorContract
{
    /**
     * @var User
     */
    // private $user;

    /**
     * @var Billpayer
     */
    private $billpayer;

    /**
     * @var Address
     */
    private $shippingAddress;

    /**
     * @var CartManager
     */
    private $cart;

    private Customer $customer;

    public function __construct(
        private AvailabilityManager $availabilityManager,
        private DepositService $depositService
    ) {
    }

    /**
     * Create an order with items and attributes.
     */
    public function createOrderWithItems(
        Customer $customer,
        Billpayer $billPayer,
        Address $shippingAddress,
        CartManagerContract $cart,
        array $attributes
    ): Order {
        $this->customer = $customer;
        $this->billpayer = $billPayer;
        $this->shippingAddress = $shippingAddress;
        $this->cart = $cart;

        $order = $this->createOrder(
            $attributes['start'] ?? '',
            $attributes['end'] ?? '',
            $attributes['location'] ?? '',
            $attributes['businessId'] ?? 0,
        );
        $this->createOrderItems($order);

        return $order;
    }

    /**
     * Create the order @TODO - unit test.
     *
     * @param string $start
     * @param string $end
     * @param string $location
     * @param int $businessId
     * @return Order
     */
    protected function createOrder(string $start, string $end, string $location, int $businessId): Order
    {
        $numberGenerator = app(SequentialNumberGenerator::class);
        $orderNumber = $numberGenerator->generateNumber();

        $required = [
            'start' => $start,
            'end' => $end,
            'location' => $location,
        ];
        //Currently untested
        $dispatchPrice = $this->cart->getDeliveryCost($required);
        $totalProductPrice = $this->cart->getTotalProductPrices($required);
        $adjustments = $this->cart->getAdjustments();
        $totalPrice = ($dispatchPrice + $totalProductPrice + $adjustments);
        $deposit = $this->depositService->calculateDeposit($totalPrice);

        return Order::create([
            'number'              => $orderNumber,
            'status'              => OrderStatus::NEW(),
            'user_id'             => $this->customer->id,
            'billpayer_id'        => $this->billpayer->id,
            'shipping_address_id' => $this->shippingAddress->id,

//Currently untested
            'dispatchPrice'       => $dispatchPrice,
            'totalProductPrice'   => $totalProductPrice,
            'adjustments'         => $adjustments,
            'totalPrice'          => $totalPrice,
            'deposit'             => $deposit,
//Currently untested
            'start'               => $start,
            'end'                 => $end,
            'business_id'         => $businessId,
            'location'            => $location,
        ]);
    }

    /**
     * Create the order items.
     *
     * @param Order $order
     */
    protected function createOrderItems(Order $order)
    {
        $this->cart->getItems()->each(function ($cartItem) use ($order) {
            $product = $cartItem->product;
            $order->items()->create([
                'product_type' => $product->type,
                'product_id'   => $product->id,
                'name'         => $product->name,
                'price'        => $product->price ?? 0,
                'quantity'     => $cartItem->quantity ?? 0,
            ]);
        });
    }
}
