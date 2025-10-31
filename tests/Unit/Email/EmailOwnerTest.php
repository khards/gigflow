<?php

namespace Tests\Unit\Email;

use App\Domains\Email\Mailables\Payments\PaymentReceivedReceipt;
use App\Domains\Order\Order;
use Tests\TestCase;

class EmailOwnerTest extends TestCase {

    public function testEmailOwner() {

        // Create business and it's related emails.
        $owner = $this->getBusiness();

        // Factory up and order to this business.
        $order = Order::factory()->withBusiness($owner)->create();

        // Create the mailable
        $mailable = new PaymentReceivedReceipt([
            'order' => $order,
            'amount' => 11.13
        ]);

        //Check the relationship and all is good!
        $this->assertEquals($owner->id, $mailable->owner()->id);
        $this->assertEquals('business', $mailable->owner()->getMorphClass());
        $this->assertEquals($order->billpayer->getEmail(), $mailable->customerEmail);
        $this->assertEquals('11.13', $mailable->amount);

    }
}
