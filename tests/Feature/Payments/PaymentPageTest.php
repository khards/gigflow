<?php
namespace Tests\Feature\Payments;

use App\Booking\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPageTest extends  TestCase
{
    use RefreshDatabase;

    private Business $business;

    public function setUp(): void
    {
        parent::setUp();
        $this->business = $this->getBusiness('Hards independent traders international. Dover, West Huntspill, Galway, Basildon');
    }

    public function testGetPaymentPage() {
        // Factory up and order to this business.
        $order = \App\Domains\Order\Order::factory()->withBusiness($this->business)->create();
        $response = $this->get(route('frontend.order.payment', $order->id));
        $response->assertSuccessful();
    }
}
