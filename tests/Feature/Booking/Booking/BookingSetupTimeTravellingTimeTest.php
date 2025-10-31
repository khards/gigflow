<?php

namespace Tests\Feature\Booking\Booking;

use App\Booking\Business;
use App\Booking\Cart\CartManager;
use App\Booking\Cart\ProductUnavailableException;
use App\Booking\Product;
use Tests\Feature\Booking\BookingTestDataGenerator;

class BookingSetupTimeTravellingTimeTest extends BookingTestDataGenerator
{
    public function testProductVariationSetupTimeAvailability()
    {
        $cart = new CartManager();

        //Setup test data.
        $business = $this->createBusinessWithData();
        $this->createCustomerData();
        $this->createFixedPriceServiceProduct($business);

        // Will NOT be available with 0 travelling time & 0 setup time
        $this->addToCartRequest('2022-12-02 17:29:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(0, $cart->getItems());

        $cart->clear();

        // Will be available with 0 travelling time & 0 setup time
        $this->addToCartRequest('2022-12-02 17:30:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(1, $cart->getItems());

        $cart->clear();

        // Not available due to 45 mins setup time.
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest('2022-12-02 17:30:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(0, $cart->getItems());

        // Available after setup time.
        $this->addToCartRequest('2022-12-02 18:15:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(1, $cart->getItems());

        $cart->clear();
//
//        // Add child product variation with 15 mins setup
//        {
//            $childProduct = Product::create([
//                'name' => 'Gold package upgrade',
//                'description' => 'Extra - Gold package upgrade',
//                'is_addon' => true,
//                'state' => 'active',
//                'sku' => 'GOLD-1',
//                'owner_type' => $this->business->getMorphClass(),
//                'owner_id' => $this->business->id,
//                'setup_time' => 15,
//                'slug' => 'gold-package',
//                'url' => 'https://elitebookingsystem.com/gold',
//                'type' =>'service',
//                'price_type' => 'fixed',
//                'price_fixed_price' => 35.00,
//                'staff_quantity' => 1,
//                'availability_type' => 'scheduled',
//                'availability_schedule' => 1,
//                'available_quantity' => 1,
//                'travelling_limit' => 'yes',
//                'travelling_value' => 12.65,
//                'travelling_type' => 'miles',
//                'delivery_method' => Product::DELIVERY_METHODS['delivered'] | Product::DELIVERY_METHODS['collected'],
//                'delivery' => [
//                    'delivered' => [
//                        'charge' => 0.12,
//                        'per' => 'mile',
//                    ],
//                    'shipped' => [
//                        'price' => 10.00,
//                        'per' => 'order',
//                    ],
//                ],
//            ]);
//
//            // Add it to the variations table
//            $this->product->variations()->save($childProduct, [
//                'stock_from_parent' => true,
//            ]);
//        }
//
//        // No longer Available after due to additional setup time.
//        //$this->addToCartRequest('2022-12-02 18:15:00', '2022-12-03 01:00:00', $cart);
//        {
//            $start = '2022-12-02 18:15:00';
//            $end = '2022-12-03 01:00:00';
//            $location = 'TA93RS';
//            $this->dateAvailableInTestSchedule($start, $end, $location, $childProduct, $cart);
//        }
//
//        $this->assertCount(0, $cart->getItems());
//
//        $cart->clear();
//
//        // now available later
//        //$this->addToCartRequest('2022-12-02 18:30:00', '2022-12-03 01:00:00', $cart);
//        {
//            $start = '2022-12-02 19:00:00';
//            $end = '2022-12-03 01:00:00';
//            $location = 'TA93RS';
//            $this->dateAvailableInTestSchedule($start, $end, $location, $childProduct, $cart);
//        }
//        $this->assertCount(1, $cart->getItems());
//
//        $cart->clear();

    }
    /**
     * Testing of Product->start_time when adding items to the cart items
     *
     * @tests \App\Booking\Availability\Checker\Rules\StaffAvailable
     *
     * @return void
     */
    public function testProductSetupTimeAvailability()
    {
        $cart = new CartManager();

        //Setup test data.
        $business = $this->createBusinessWithData();
        $this->createCustomerData();
        $this->createFixedPriceServiceProduct($business);

        // Will NOT be available with 0 travelling time & 0 setup time
        $this->addToCartRequest('2022-12-02 17:29:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(0, $cart->getItems());

        $cart->clear();

        // Will be available with 0 travelling time & 0 setup time
        $this->addToCartRequest('2022-12-02 17:30:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(1, $cart->getItems());

        $cart->clear();

        // Not available due to 45 mins setup time.
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest('2022-12-02 17:30:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(0, $cart->getItems());

        $cart->clear();

        // Available a little later due to 45 mins setup time.
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest('2022-12-02 18:15:00', '2022-12-03 01:00:00', $cart);
        $this->assertCount(1, $cart->getItems());

        $cart->clear();

        // NOT Available 1 second too earlier due to 45 mins setup time.
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest('2022-12-02 18:14:59', '2022-12-03 01:00:00', $cart);
        $this->assertCount(0, $cart->getItems());

        $cart->clear();

        // Not Available due to 45 mins setup time + 10 min travelling.
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest('2022-12-02 18:15:00', '2022-12-03 01:00:00', $cart, 'TA64RN');
        $this->assertCount(0, $cart->getItems());

        $cart->clear();

        // IS Available:
        //      45 mins setup time
        //      10 mins travelling allowance.
        //      Start: 5:30pm + 55 mins
        $this->product->setup_time = 45;
        $this->product->save();
        $this->addToCartRequest('2022-12-02 18:25:00', '2022-12-03 01:00:00', $cart, 'TA64RN');
        $this->assertCount(1, $cart->getItems());

        $cart->clear();
    }

    protected function addToCartRequest(string $start, string $end, CartManager $cart, $location = 'TA93RS'): void
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

    private function dateAvailableInTestSchedule(string $start, string $end, string $location, $childProduct, CartManager $cart): void
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

        $childProduct->price = 1795;
        $childProduct->save();

        // Add item to cart - with unavailable date
        try {
            $cart->addItem($childProduct, 1, $orderAttributes);
        } catch (ProductUnavailableException $e) {
            //ok.
            //dd($e);
        }
    }
}
