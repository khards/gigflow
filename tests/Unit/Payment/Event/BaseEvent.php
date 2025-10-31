<?php

namespace Tests\Unit\Payment\Event;

use App\Booking\Contracts\AvailabilityManager;
use App\Booking\Product;
use App\Domains\Auth\Models\User;
use App\Domains\Order\Order;
use App\Domains\Order\OrderStatus;
use Konekt\Address\Models\Address;
use Konekt\Address\Models\AddressType;
use Mockery\MockInterface;
use Tests\TestCase;
use Vanilo\Order\Models\Billpayer;

class BaseEvent extends TestCase
{
    public function setUpOrder(string $type = 'service', $options = []): void
    {

        // Given, staff & business,
        $this->staff = User::factory()->create();
        $this->business = $this->getBusiness();
        $this->business->users()->attach($this->staff);

        $this->staff2 = User::factory()->create();
        $this->business->users()->attach($this->staff2);

        $this->staff3 = User::factory()->create();
        $this->business->users()->attach($this->staff3);

        // Given, Has availability
        $this->partialMock(AvailabilityManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('staffHasAvailability')->andReturn(true);//once()->
        });

        // Given billpayer and order address
        $this->billpayer = factory(Billpayer::class)->create();
        $this->address = factory(Address::class)->create([
            'type' => AddressType::SHIPPING,
            'name' => 'Shipping address',
            'address' => '14 Grove Road, west huntspill',
            'city' => 'somerset',
            'postalcode' => 'TA93RS',
            'country_id' => 'GB',
        ]);

        if ($type === 'service' || $type === 'mixed-service-product') {
            $this->product = Product::create([
                'owner_type' => $this->business->getMorphClass(),
                'owner_id' => $this->business->id,

                'name' => 'Mobile DJ Service Standard',
                'description' => 'Hire of DJ and equipment',
                'state' => 'active',
                'sku' => 'dj-service',
                'slug' => 'dj-service',
                'image' => null,
                'url' => 'https://elitebookingsystem.com',

                'type' =>'service',
                'price_type' => 'fixed',//scheduled
                'price_fixed_price' => 225.00,
                'staff_quantity' => $options['staff_quantity'] ?? 1,
                'availability_type' => 'scheduled',
                'availability_schedule' => 1,
                'available_quantity' => 1,
                'travelling_limit' => 'yes',
                'travelling_value' => 10.50,
                'travelling_type' => 'miles',
                'form' => 231,
                'delivery' => [
                    'delivered' => [
                        'charge' => 0.12,
                        'per' => 'mile',
                    ],
                    'shipped' => [
                        'price' => 10.00,
                        'per' => 'order',
                    ],
                ],
            ]);
        }

        if ($type === 'product' || $type === 'mixed-service-product') {
            $product = Product::create([
                'owner_type' => $this->business->getMorphClass(),
                'owner_id' => $this->business->id,

                'name' => 'Mobile DJ Service Standard',
                'description' => 'Hire of DJ and equipment',
                'state' => 'active',
                'sku' => 'dj-service',
                'slug' => 'dj-service',
                'image' => null,
                'url' => 'https://elitebookingsystem.com',

                'type' =>'product',
                'price_type' => 'fixed',//scheduled
                'price_fixed_price' => 25.00,
                'staff_quantity' => $options['staff_quantity'] ?? 1,
                'availability_type' => 'scheduled',
                'availability_schedule' => 1,
                'available_quantity' => 7,
                'travelling_limit' => 'yes',
                'travelling_value' => 10.50,
                'travelling_type' => 'miles',
                'form' => 231,
                'delivery' => [
                    'delivered' => [
                        'charge' => 0.12,
                        'per' => 'mile',
                    ],
                    'shipped' => [
                        'price' => 4.00,
                        'per' => 'order',
                    ],
                ],
            ]);

            if($type === 'product') {
                $this->product = $product;
            }
            if($type === 'mixed-service-product') {
                $this->productProduct = $product;
            }
        }

        if ($type === 'parent-product') {
            $this->product = Product::create([
                'owner_type' => $this->business->getMorphClass(),
                'owner_id' => $this->business->id,

                'name' => 'Parent',
                'description' => 'Parent',
                'state' => 'active',
                'sku' => 'dj-service',
                'slug' => 'dj-service',
                'image' => null,
                'url' => 'https://elitebookingsystem.com',

                'type' =>'product',
                'price_type' => 'fixed',
                'price_fixed_price' => 25.00,
                'staff_quantity' => 0,
                'availability_type' => 'scheduled',
                'availability_schedule' => 1,
                'available_quantity' => 7,
                'travelling_limit' => 'yes',
                'travelling_value' => 10.50,
                'travelling_type' => 'miles',
                'form' => 231,
                'delivery' => [
                    'delivered' => [
                        'charge' => 0.12,
                        'per' => 'mile',
                    ],
                    'shipped' => [
                        'price' => 4.00,
                        'per' => 'order',
                    ],
                ],
            ]);

            $this->productChild = Product::create([
                'owner_type' => $this->business->getMorphClass(),
                'owner_id' => $this->business->id,

                'name' => 'Child',
                'description' => 'Child',
                'state' => 'active',
                'sku' => 'dj-service-child',
                'slug' => 'dj-service-child',
                'image' => null,
                'url' => 'https://elitebookingsystem.com',

                'type' =>'product',
                'price_type' => 'fixed',
                'price_fixed_price' => 25.00,
                'staff_quantity' => 0,
                'availability_type' => 'scheduled',
                'availability_schedule' => 1,
                'available_quantity' => 7,
                'travelling_limit' => 'yes',
                'travelling_value' => 10.50,
                'travelling_type' => 'miles',
                'form' => 231,
                'delivery' => [
                    'delivered' => [
                        'charge' => 0.12,
                        'per' => 'mile',
                    ],
                    'shipped' => [
                        'price' => 4.00,
                        'per' => 'order',
                    ],
                ],
            ]);

            $this->product->variations()->save($this->productChild, [
                'stock_from_parent' => true,
            ]);
        }

        // Given an order
        $this->order = Order::create([
            'number'              => 1231,
            'status'              => OrderStatus::NEW(),
            'user_id'             => $this->staff->id,
            'billpayer_id'        => $this->billpayer->id,
            'shipping_address_id' => $this->address->id,
            'dispatchPrice'       => 12.11,
            'totalProductPrice'   => 11.00,
            'adjustments'         => -1.00,
            'totalPrice'          => 454.22,
            'deposit'             => 13.77,
            'start'               => '2021-10-11 15:00:00',
            'end'                 => '2021-10-12 15:00:00',
            'business_id'         => $this->business->id,
            'location'            => 'Here and there',
        ]);

        if ($type === 'parent-product') {
            // Given ordered items.
            $this->order->items()->create([
                'product_type' => $this->productChild->type,
                'product_id'   => $this->productChild->id,
                'name'         => $this->productChild->name,
                'price'        => 225.00*100,
                'quantity'     => 1,
            ]);
        } else {
            // Given ordered items.
            $this->order->items()->create([
                'product_type' => $this->product->type ?? 'product',
                'product_id'   => $this->product->id ?? '9999901',
                'name'         => $this->product->name ?? 'INVALID PRODUCT',
                'price'        => 225.00*100,
                'quantity'     => 1,
            ]);

            if($type === 'mixed-service-product') {
                $this->order->items()->create([
                    'product_type' => $this->productProduct->type,
                    'product_id'   => $this->productProduct->id,
                    'name'         => $this->productProduct->name,
                    'price'        => 1225.00*100,
                    'quantity'     => 1,
                ]);
            }
        }

        // Given payment transactions.
        if (!isset($options['skip-transactions'])) {
            $this->order->transactions()->createMany([
                [
                    'currency' => 'GBP',
                    'method' => 'cash',
                    'amount' => 12.00,
                    'note' => 'Payment ref#123 date 2011:12:12 23:22:11. Payment in cash from Jon.',
                    'details' => ['Thanks for your payment'],
                ],
                [
                    'currency' => 'GBP',
                    'method' => 'cash',
                    'amount' => 1.77,
                    'note' => 'Payment ref#123 date 2011:12:12 23:22:11. Payment in cash from Jon.',
                    'details' => ['Thanks for your payment'],
                ],
            ]);
        }
    }
}
