<?php

namespace Tests\Unit\Email;

use App\Domains\Auth\Models\User;
use App\Domains\Email\Jobs\Orders\SendConfirmation;
use App\Domains\Email\Mailables\Booking\BookingConfirmation;
use App\Domains\Order\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailBookingConfirmationTest extends TestCase {

    public function testBookingConfirmation() {

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
        SendConfirmation::dispatch(['orderId' => $order->id]);

        // Then check the email was sent!
        Mail::assertSent(BookingConfirmation::class, 1);
    }

    public function testSendRealEmailBookingConfirmationPdfAttachment() {
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
        SendConfirmation::dispatch(['orderId' => $order->id]);

        // No assert needed, will fail if email can't be sent via smtp.
        $this->assertTrue(true);
    }
}
