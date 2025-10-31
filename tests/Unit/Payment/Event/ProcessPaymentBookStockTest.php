<?php

namespace Tests\Unit\Payment;

use App\Booking\Models\Calendar;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Events\PaymentTransaction;
use App\Domains\Payment\Listeners\Payment\BookStock;
use Tests\Unit\Payment\Event\BaseEvent;

class ProcessPaymentBookStockTest extends BaseEvent
{
    /**
     * Test that processing a payment BookStock event for a PRODUCT.
     *      decrements stock
     *
     * @test
     */
    public function bookProductRefundNoStockDecrement()
    {
        $this->setUpOrder('product');

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => -12.11,
            'type' => 'cash',
        ];
        $this->product->refresh();
        $originalNumInStock = $this->product->getAvailableQuantity();

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);
        $this->product->refresh();

        // Then, Assert that staff was not booked
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was NOT made
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);


        $this->product->refresh();

        // Then ensure stock wasn't decremented.
        $this->assertEquals($originalNumInStock, $this->product->getAvailableQuantity());

        $this->assertEquals($this->order->status, OrderStatus::NEW());
    }

   /**
     * Test that processing a payment for an invalid product ID throws an exception
     *
     * @test
     */
    public function bookServiceInvalidProductId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error, product 9999901 was not found. Unable to process payment event');

        $this->setUpOrder('invalid-product');

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);
    }

    /**
     * Test that processing a payment BookStock event for a PRODUCT.
     *      decrements stock
     *
     * @test
     */
    public function bookServiceParentProductStock()
    {
        $this->setUpOrder('parent-product', ['skip-transactions' => true]);

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        $originalNumInStock = $this->productChild->getAvailableQuantity();

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was NOT booked for each service order item.
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was NOT made for each bookable productChild
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->productChild->id,
            'model_type' => $this->productChild->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);


        $this->productChild->refresh();
        $this->assertEquals($originalNumInStock - 1, $this->productChild->getAvailableQuantity());
    }

    /**
     * Test that processing a payment BookStock event for a PRODUCT.
     *      decrements stock
     *
     * @test
     */
    public function BookServiceProductStock()
    {
        $this->setUpOrder('product');

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        $originalNumInStock = $this->product->getAvailableQuantity();

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was booked correctly for each service order item.
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was made for each bookable product
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);


        $this->product->refresh();
        $this->assertEquals($originalNumInStock - 1, $this->product->getAvailableQuantity());
    }

    /**
     * Test that processing a payment BookStock event for a PRODUCT.
     *      decrements stock
     *
     * @test
     */
    public function BookServiceMixedOrder()
    {
        $this->setUpOrder('mixed-service-product');

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        $originalNumInStock = $this->productProduct->getAvailableQuantity();

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was booked correctly for each service order item.
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was made for each bookable product
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Ensure stock was decremented for saleable item
        $this->productProduct->refresh();
        $this->assertEquals($originalNumInStock - 1, $this->productProduct->getAvailableQuantity());
    }

    /**
     * @test
     */
    public function bookServiceUnknownOrderId()
    {
        $eventData = [
            'orderId' => 123123,
            'amount' => 12.11,
            'type' => 'bitcoin',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error, order 123123 was not found. Unable to process payment event');
        $this->fireBookStockEvent($eventData);
    }

    /**
     * Test that processing a payment BookStock event for a bookable service.
     *      books the products in calendar
     *      books staff in calendar
     *      creates booking.
     *
     * @test
     */
    public function BookServiceSingleStaffTest()
    {
        $this->setUpOrder('service');

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was booked correctly for each service order item.
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was made for each bookable product
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);
    }

    /**
     * Refund, no booking activated
     *
     * @test
     */
    public function NoBookingOnRefundTest()
    {
        $this->setUpOrder('service', ['skip-transactions' => true]);

        // Given an REFUND event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => -12.11,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was NOT booked  for each service order item.
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => (string)$this->order->id
        ]);

        // Then, Assert that calendar entry was NOT made for each bookable product
        $this->assertDatabaseMissing(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => (string)$this->order->id
        ]);
    }

    /**
     * Test that processing a payment BookStock event for a bookable service.
     *      books the products in calendar
     *      books staff in calendar
     *
     * @test
     */
    public function BookServiceTwoStaffTest()
    {
        $this->setUpOrder('service', ['staff_quantity' => 2]);

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was booked correctly for each service order item.
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->staff2->id,
            'model_type' => $this->staff2->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was made for each bookable product
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);
    }

    /**
     * Test that processing a payment BookStock event for an event without enough staff.
     *
     * @test
     */
    public function BookServiceTwoStaffErrorTest()
    {
        $this->setUpOrder('service', ['staff_quantity' => 4]);

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 12.11,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireBookStockEvent($eventData);

        // Then, Assert that staff was booked correctly for each service order item.
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->staff->id,
            'model_type' => $this->staff->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->staff2->id,
            'model_type' => $this->staff2->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);

        // Then, Assert that calendar entry was made for each bookable product
        $this->assertDatabaseHas(Calendar::class, [
            'model_id' => $this->product->id,
            'model_type' => $this->product->getMorphClass(),
            'start' => $this->order->start,
            'end' => $this->order->end,
            "booked_by_type" => $this->order->getMorphClass(),
            "booked_by_id" => $this->order->id
        ]);
    }


    private function fireBookStockEvent($eventData)
    {
        $event = new PaymentTransaction($eventData);
        $listener = app()->make(BookStock::class);

        return $listener->handle($event);
    }
}
