<?php

namespace Tests\Unit\Order;

use App\Domains\Order\Order;
use App\Domains\Order\Models\OrderNotes;
use Tests\TestCase;

class OrderNotesTest extends TestCase {

    public function testAddOrderNoteTest() {

        $order = Order::create([
            'number' => '23t2333',
        ]);

        $order->notes()->save(OrderNotes::make(['value' => 'smej']));

        $this->assertCount(1, $order->notes()->get());
    }

    public function testNoteOrderTest() {

        $order = Order::create([
            'number' => '23t2333',
        ]);
        $note = OrderNotes::make(['value' => 'smej']);
        $order->notes()->save($note);

        $assOrder = $note->order()->first();

        $this->assertInstanceOf(Order::class, $assOrder);
    }

    public function testSoftDeleteNote() {
        $order = Order::create([
            'number' => '23t2333',
        ]);

        $order->notes()->save(OrderNotes::make(['value' => 'smej']));
        $this->assertCount(1, $order->notes()->get());

        $order->notes()->get()->each(function ($note) {
            $note->delete();
        });

        $this->assertCount(0, $order->notes()->get());
    }

}
