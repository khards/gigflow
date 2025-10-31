<?php

namespace Tests\Unit\Email;

use App\Domains\Auth\Models\User;
use App\Domains\Email\Jobs\Payments\SendReceipt;
use App\Domains\Email\Mailables\Payments\PaymentReceivedReceipt;
use App\Domains\Order\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailReceiptTest extends TestCase {

    public function testPaymentReceipt() {

        Mail::fake();
        Mail::assertNothingSent();
        Mail::assertNothingQueued();

        // Create business and it's related emails.
        $owner = $this->getBusiness();

        // Factory up and order to this business.
        $order = Order::factory()->withBusiness($owner)->create();

        $user = User::first();
        $order->user_id = $user->id;
        $order->save();

        // Create the mailable
        SendReceipt::dispatch(['orderId' => $order->id, 'amount' => '77.77']);

        // Then check the email was sent!
        Mail::assertSent(PaymentReceivedReceipt::class, 1);
    }

    public function testSendRealEmailReceipt() {
        Config::set('mail.default', 'smtp');

        // Create business and it's related emails.
        $owner = $this->getBusiness();

        // Factory up and order to this business.
        $order = Order::factory()->withBusiness($owner)->create();

        $user = User::first();
        $order->user_id = $user->id;
        $order->save();

        $user->email = 'contact@keithhards.co.uk';
        $user->save();

        // Actually send the email
        SendReceipt::dispatch(['orderId' => $order->id, 'amount' => 77.77]);

        // No assert needed, will fail if email can't be sent via smtp.
        $this->assertTrue(true);
    }
}
