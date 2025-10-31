<?php

namespace Tests\Feature\Payments;

use App\Booking\Business;
use App\Domains\Payment\Models\PaymentMethod;
use App\Domains\Payment\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    public function setUp(): void
    {
        parent::setUp();
        $this->business = $this->getBusiness('Trotters independents');
    }

    public function testCanSetGetPaymentMethod()
    {
        $this->assertCount(0, PaymentMethod::all());

        $testData = [
            'business_id' => $this->business->id,
            'type' => 'bank',
            'data' => ['bank_name' => 'Barclays', 'account_name' => 'Joseppy blogger'],
        ];

        PaymentMethod::create($testData);

        $entry = PaymentMethod::business($this->business->id)->first();
        $this->assertEquals($testData['business_id'], $entry->id);
        $this->assertEquals($testData['type'], $entry->type);
        $this->assertEquals($testData['data']['bank_name'], $entry->data['bank_name']);
        $this->assertEquals($testData['data']['account_name'], $entry->data['account_name']);
    }

    public function testBusinessPaymentMethodRelationship()
    {
        $fields = ['account' => '5678 motorway', 'clientId' => 'Papa Smurf', 'descriptor' => 'HiedHi', 'currency' => 'AUD'];

        $this->business->paymentMethod('paypal')->create([
            'type' => 'bank',
            'business_id' => $this->business->id,
            'data' => $fields,
        ]);

        $this->assertNotNull($this->business->paymentMethod('paypal')->get());
    }

    public function testBusinessPaymentServiceCreateUpdate()
    {
        $paymentService = app()->make(PaymentService::class);

        $fields = ['account' => '5678 motorway', 'clientId' => 'New Papa Smurf', 'descriptor' => 'HiedHi', 'currency' => 'AUD'];
        $paymentService->createUpdate($this->business, 'paypal', $fields);

        $fields = ['account' => 'UP 5678 motorway', 'clientId' => 'UP Papa Smurf', 'descriptor' => 'UP HiedHi', 'currency' => 'UPD'];
        $paymentService->createUpdate($this->business, 'paypal', $fields);

        $updated = $this->business->paymentMethod('paypal')->get();
        $this->assertNotNull($updated);
        $this->assertEquals($fields['account'], $updated->first()->data['account']);
    }
}
