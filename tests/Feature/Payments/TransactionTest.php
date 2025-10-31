<?php

namespace Tests\Feature\Payments;

use App\Domains\Auth\Models\User;
use App\Domains\Email\Mailables\Payments\PaymentReceivedReceipt;
use App\Domains\Payment\Contracts\TransactionService as TransactionServiceContract;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Vanilo\Order\Models\Billpayer;

class TransactionTest extends TestCase
{
    private TransactionServiceContract $transactionService;

    public function setUp(): void
    {
        parent::setUp();
        $this->transactionService = app()->make(TransactionServiceContract::class);
    }

    /**
     * @test
     */
    public function insertCashTransactionPayment()
    {
        Mail::fake();

        //$this->createCountries();

        //$order = factory(Order::class);
        // Create business and it's related emails.
        $owner = $this->getBusiness();

        // Factory up and order to this business.
        $order = \App\Domains\Order\Order::factory()->withBusiness($owner)->create();

        $billPayer = factory(Billpayer::class)->create();

        $user = User::first();

        $order->create([
            'location' => 'a wooden shack in the woods',
            'start' => '2020-01-01 00:00:00',
            'end' => '2020-01-01 00:00:00',
            'business_id' => 1,
            'user_id' => $user->id,
            'billpayer_id' => $billPayer->id,
            'number' => time()
        ]);

        $trans = $this->transactionService->create(
            $order->id,
            'cash',
            12.00,
            'GBP',
            ['testId' => 'ahaaaa'],
            'Paid with thanks.'
        );

        $this->assertEquals('cash', $trans->method);
        $this->assertEquals(12.00, $trans->amount);

        $trans2 = $this->transactionService->read($trans->id);
        $this->assertEquals('cash', $trans2->method);
        $this->assertEquals(12.00, $trans2->amount);

        Mail::assertNotSent(PaymentReceivedReceipt::class);
    }
}
