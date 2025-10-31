<?php

namespace Tests\Feature\Booking\Booking;

use App\Booking\Cart\CartManager;
use App\Booking\Cart\ProductUnavailableException;
use App\Booking\Contracts\OrderProcessor as OrderProcessorContract;
use App\Booking\OrderProcessor\OrderProcessor;
use Tests\Feature\Booking\BookingTestDataGenerator;
use Vanilo\Checkout\Facades\Checkout;
use Vanilo\Order\Contracts\Order as OrderContract;
use Vanilo\Order\Models\Billpayer;

class CreateBookingTest extends BookingTestDataGenerator
{
    /**
     * 1. Setup data
     * 2. Add booking product to cart
     * 3. Checkout to state
     * 4. Validate that booking date is still available
     * 5. Take payment
     * 6. Create order with order items (from cart) including booking prodcut and its attributes.
     * @test
     */
    public function order_can_be_created_with_minimal_data()
    {
        $cart = new CartManager();

        //Setup test data.
        $business = $this->createBusinessWithData();
        $this->createCustomerData();
        $this->createFixedPriceServiceProduct($business);

        //Date unavailable in test schedule
        $orderAttributes = [
            'attributes' => [
                'start' => '2020-04-17 12:00:00',
                'end' => '2020-04-19 19:00:00',
                'businessId' => $this->business->id,
                'location' => 'TA93RS',
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
        }

        //Verify that item would not add
        Checkout::setCart($cart);
        $this->assertCount(0, Checkout::getCart()->getItems());

        //Date available in schedule
        $orderAttributes = [
            'attributes' => [
                'start' => '2020-08-22 19:30:00',
                'end' => '2020-08-23 01:00:00',
                'businessId' => $this->business->id,
                'location' => 'TA93RS',
            ],
        ];

        //Add to cart - ok
        $cart->addItem($this->product, 1, $orderAttributes);
        Checkout::setCart($cart);
        $this->assertCount(1, Checkout::getCart()->getItems());

        //Checkout - verify order page
        $cart = new CartManager();
        $cart->setUser($this->customer->id);
        Checkout::setCart($cart);
        $billpayer = factory(Billpayer::class)->create();
        Checkout::setBillpayer($billpayer);
        $this->assertEquals(round(17.95), Checkout::total());

        //Create order
        /** @var $orderProcessor OrderProcessor */
        $orderProcessor = resolve(OrderProcessorContract::class);
        $orderAttributes = [
            'start' => '2020-01-01 17:30:00',
            'end' => '2020-01-02 00:00:00',
            'businessId' => 1,
            'location' => 'TA9 3RS',
        ];
        $order = $orderProcessor->createOrderWithItems(
            $this->customer,
            $billpayer,
            $this->address,
            $cart,
            $orderAttributes
        );
        //Then check the order is returned
        $this->assertInstanceOf(OrderContract::class, $order);

        //Test order was created correctly.
        $orders = $this->customer->orders()->get();
        $this->assertCount(1, $orders);
    }
}
