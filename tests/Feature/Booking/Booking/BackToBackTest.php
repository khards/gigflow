<?php

namespace Tests\Feature\Booking\Booking;

use App\Booking\Cart\CartManager;
use App\Booking\Cart\ProductUnavailableException;
use App\Booking\Models\Calendar;
use App\Domains\Order\OrderStatus;
use Tests\Feature\Booking\BookingTestDataGenerator;

class BackToBackTest extends BookingTestDataGenerator
{
    public function testKidsEarlyDiscoBookingEveningBookingExists()
    {
        $cart = new CartManager();

        //Setup test data.
        $business = $this->createBusinessWithData();
        $this->createCustomerData();
        $this->createFixedPriceServiceProduct($business);

        // Add 45 mins setup time.
        $this->product->setup_time = 45;
        $this->product->save();

        // Fake an evening disco
        $staff1 = $this->business->users()->where('name', '=', 'staff1')->first();
        $staff2 = $this->business->users()->where('name', '=', 'staff2')->first();

        //Staff 1 (3) available before 19:45
        $this->fakeBooking($staff1,'2022-12-10 19:45:00', '2022-12-11 01:00:00');

        // Staff 2 (4) not available all day!
        $this->fakeBooking($staff2,'2022-12-10 00:45:00', '2022-12-11 01:00:00');

        // Back to back booking will be okay! Is > 55 mins earlier.
        $this->product->setup_time = 45;
        $this->product->save();

        // ---------------------

        $this->addToCartRequest(
            '2022-12-10 14:30:00',
            '2022-12-10 18:49:59',//18:49:59 + 45:00 + 10:00  = 1s < 19:45
            $cart,
            'TA64RN'
        );
        $this->assertCount(1, $cart->getItems());

        $cart->clear();

        // ---------------------

        // Back to bak booking will fail! Needs to be 55 mins earlier.
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest(
            '2022-12-10 14:30:00',
            '2022-12-10 19:44:59', // 19:44:59 + 45 + 10 = 20:45 > 16:49:59
            $cart,
             'TA64RN'
        );

        $this->assertCount(0, $cart->getItems());

        $cart->clear();
        // ---------------------

    }

    public function testEarlyDiscoExistsCheckBookingEvening()
    {
        $cart = new CartManager();

        //Setup test data.
        $business = $this->createBusinessWithData();
        $this->createCustomerData();
        $this->createFixedPriceServiceProduct($business);

        // Add 45 mins setup time.
        $this->product->setup_time = 45;
        $this->product->save();

        // Fake an morning disco 1pm to 3pm
        {
            $order = \App\Domains\Order\Order::factory()->withBusiness($this->business)->create();

            $order->status = OrderStatus::BOOKED();
            $order->save();

            $staff = $this->business->users()->first();
            Calendar::create([
                'model_id' => $staff->id,
                'model_type' => $staff->getMorphClass(),
                'booked_by_type' => $order->getMorphClass(),
                'booked_by_id' => $order->id,
                'start' => '2022-12-10 13:00:00',
                'end' => '2022-12-10 15:00:00',
            ]);

            $order->items()->create([
                'product_type' => $this->product->type,
                'product_id'   => $this->product->id,
                'name'         => $this->product->name,
                'price'        => '77777',
                'quantity'     => 1,
            ]);
        }

        // Back to bak booking will be okay! Needs to be > 55 mins after. (> 13:55pm)
        $this->product->setup_time = 45;
        $this->product->save();

        // ---------------------

        $this->addToCartRequest(
            '2022-12-10 18:55:00',
            '2022-12-10 19:44:59',
            $cart,
            'TA64RN'
        );
        $this->assertCount(1, $cart->getItems());

        $cart->clear();
        // ---------------------

        // Back to bak booking will fail! Needs to be > 55 mins later than 15:00:00

        $this->addToCartRequest(
            '2022-12-10 15:55:00',
            '2022-12-10 19:44:59',
            $cart,
            'TA64RN'
        );
        $this->assertCount(1, $cart->getItems());

        $cart->clear();
        // ---------------------

        $this->product->save();
        $this->addToCartRequest(
            '2022-12-10 15:54:00',
            '2022-12-10 19:44:59',
            $cart,
            'TA64RN'
        );
        $this->assertCount(0, $cart->getItems());

        $cart->clear();
        // ---------------------
    }

    protected function addToCartRequest(string $start, string $end, CartManager $cart, $location = null): void
    {
        //Date available in test schedule
        $orderAttributes = [
            'attributes' => [
                'start' => $start,
                'end' => $end,
                'businessId' => $this->business->id,
                'location' => $location,
            ],
        ];

        //Customize base product.
        $this->product->update([
            // Product is always available this test, just checks staff availability.
            'availability_type' => 'available',

            // Just required 1 staff.
            'staff_quantity' => 1,
        ]);

        $this->product->price = 1795;
        $this->product->save();

        // Add item to cart - with unavailable date
        try {
            $cart->addItem($this->product, 1, $orderAttributes);
        } catch (ProductUnavailableException $e) {
            //ok.
            //dd($e);
        }
    }

    private function fakeBooking(mixed $staff, $start, $end)
    {
        {
            $order = \App\Domains\Order\Order::factory()->withBusiness($this->business)->create();

            $order->status = OrderStatus::BOOKED();
            $order->save();

            Calendar::create([
                'model_id' => $staff->id,
                'model_type' => $staff->getMorphClass(),
                'booked_by_type' => $order->getMorphClass(),
                'booked_by_id' => $order->id,
                'start' => $start,
                'end' => $end,
            ]);

            $order->items()->create([
                'product_type' => $this->product->type,
                'product_id'   => $this->product->id,
                'name'         => $this->product->name,
                'price'        => '77777',
                'quantity'     => 1,
            ]);
        }
    }
}
