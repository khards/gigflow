<?php

namespace Tests\Unit\Transaction;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Order\Order;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Models\Transaction;
use Tests\TestCase;
use Vanilo\Order\Models\Billpayer;

class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function insertTransactionPayment()
    {
        $address = $this->getBusinessAddress();

        $business = factory(Business::class)->create([
            'name' => 'Night marer pt.1',
            'address_id' => $address->id,
            'timezone' => \DateTimeZone::UTC,
        ]);

        $staff = User::factory()->create();
        $business->users()->attach($staff);

        $billpayer = factory(Billpayer::class)->create();

        $order = Order::create([
            'number'              => 1231,
            'status'              => OrderStatus::NEW(),
            'user_id'             => $staff->id,
            'billpayer_id'        => $billpayer->id,
            'shipping_address_id' => $address->id,
            'dispatchPrice'       => 12.11,
            'totalProductPrice'   => 11.00,
            'adjustments'         => -1.00,
            'totalPrice'          => 454.22,
            'deposit'             => 3.21,
            'start'               => '2021-10-11 15:00:00',
            'end'                 => '2021-10-12 15:00:00',
            'business_id'         => $business->id,
            'location'            => 'Here and there',
        ]);

        $transaction = Transaction::create([
            'order_id' => $order->id,
            'currency' => 'GBP',
            'method' => 'cash',
            'amount' => 123.21,
            'note' => 'Payment ref#123 date 2011:12:12 23:22:11. Payment in cash from Jon.',
            'details' => ['Thanks for your payment'],
        ]);

        $this->assertTrue($transaction instanceof Transaction);
    }

    /**
     * @test
     */
    public function attachPaymentTransactionToOrder()
    {
        $address = $this->getBusinessAddress();

        $business = factory(Business::class)->create([
            'name' => 'Night marer pt.1',
            'address_id' => $address->id,
            'timezone' => \DateTimeZone::UTC,
        ]);

        $staff = User::factory()->create();
        $business->users()->attach($staff);

        $billpayer = factory(Billpayer::class)->create();

        $order = Order::create([
            'number'              => 1231,
            'status'              => OrderStatus::NEW(),
            'user_id'             => $staff->id,
            'billpayer_id'        => $billpayer->id,
            'shipping_address_id' => $address->id,
            'dispatchPrice'       => 12.11,
            'totalProductPrice'   => 11.00,
            'adjustments'         => -1.00,
            'totalPrice'          => 454.22,
            'deposit'             => 3.21,
            'start'               => '2021-10-11 15:00:00',
            'end'                 => '2021-10-12 15:00:00',
            'business_id'         => $business->id,
            'location'            => 'Here and there',
        ]);

        $order2 = Order::create([
            'number'              => '23423525ss',
            'status'              => OrderStatus::NEW(),
            'user_id'             => $staff->id,
            'billpayer_id'        => $billpayer->id,
            'shipping_address_id' => $address->id,
            'dispatchPrice'       => 55.55,
            'totalProductPrice'   => 55.55,
            'adjustments'         => -5.00,
            'totalPrice'          => 555.55,
            'deposit'             => 3.55,
            'start'               => '2023-10-11 15:00:00',
            'end'                 => '2025-10-12 15:00:00',
            'business_id'         => $business->id,
            'location'            => 'and there',
        ]);

        $order->transactions()->create([
            'currency' => 'GBP',
            'method' => 'cash',
            'amount' => 123.21,
            'note' => 'Payment ref#123 date 2011:12:12 23:22:11. Payment in cash from Jon.',
            'details' => ['Thanks for your payment'],
        ]);

        $order->transactions()->create([
            'currency' => 'GBP',
            'method' => 'paypal',
            'amount' => 111.11,
            'note' => 'Payment ref#123 date 2011:12:12 23:22:11. Payment in cash from Jon.',
            'details' => ['Thanks for your payment'],
        ]);

        $this->assertCount(2, $order->transactions);
        $this->assertCount(0, $order2->transactions);
    }
}
