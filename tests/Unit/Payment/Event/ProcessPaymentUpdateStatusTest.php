<?php

namespace Tests\Unit\Payment;

use App\Domains\Email\Jobs\Orders\SendConfirmation;
use App\Domains\Order\Events\OrderReady;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Events\PaymentTransaction;
use App\Domains\Payment\Listeners\Payment\UpdateStatus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\Unit\Payment\Event\BaseEvent;

class ProcessPaymentUpdateStatusTest extends BaseEvent
{
    /**
     * Test that refunding doesn't set the order back to completed
     *
     * @test
     * @return void
     */
    public function updateStatusNotDoneRefund() {
        $this->setUpOrder('product', ['staff_quantity' => 4, 'skip-transactions' => true]);

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => 0 - 100,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireUpdateStatusEvent($eventData);

        //assert status has changed.
        $this->order->refresh();

        $this->assertTrue($this->order->status->equals(OrderStatus::NEW()));
    }

    /**
     * Test that paying deposit changes the order status.
     *
     * @test
     * @return void
     */
    public function updateStatusOrderUpdated() {
        Event::fake();
        Queue::fake();

        $this->setUpOrder('product', ['staff_quantity' => 4]);

        // Given an event.
        $eventData = [
            'orderId' => $this->order->id,
            'amount' => $this->order->deposit,
            'type' => 'cash',
        ];

        // When, we fire the booking event.
        $this->fireUpdateStatusEvent($eventData);

        //assert status has changed.
        $this->order->refresh();
        $this->assertTrue($this->order->status->equals(OrderStatus::BOOKED()));

        // Check events and jobs were dispatched
        Event::assertDispatched(OrderReady::class);
        Queue::assertPushed(SendConfirmation::class);
    }

    /**
     * Test invalid order, throws an error.
     *
     * @test
     */
    public function updateStatusInvalidOrder()
    {
        $this->setUpOrder('service', ['staff_quantity' => 4]);

        // Given an event.
        $eventData = [
            'orderId' => 9898979798976979,
            'amount' => 0.01,
            'type' => 'cash',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error, UpdateStatus order 9898979798976979 was not found. Unable to process payment event');

        // When, we fire the booking event.
        $this->fireUpdateStatusEvent($eventData);
    }

    private function fireUpdateStatusEvent($eventData)
    {
        $event = new PaymentTransaction($eventData);
        $listener = app()->make(UpdateStatus::class);

        return $listener->handle($event);
    }
}
