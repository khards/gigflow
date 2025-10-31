<?php

namespace Tests\Feature\Payments;

use App\Booking\Business;
use App\Domains\Auth\Models\User;
use App\Domains\Order\Order;
use App\Domains\Order\OrderStatus;
use App\Domains\Payment\Events\PaymentTransaction;
use App\Domains\Payment\Webhooks\PayPal as PayPalWebhook;
use Mockery\MockInterface;
use Tests\TestCase;
use Vanilo\Order\Models\Billpayer as ModelsBillpayer;

class PayPalWebhookTest extends TestCase
{
    private Order $order;

    public function setUp(): void
    {
        parent::setUp();

        $this->paypalWebhook = app()->make(PayPalWebhook::class);
        $this->order = $this->createOrder();

        $testData = [
            'business_id' => $this->order->business->id,
            'type' => 'paypal', //bank
            'data' => [
                'bank' => [
                    'bank_name' => 'Barclays',
                    'account_name' => 'Joseppy blogger',
                ],

                'clientId' => 'AZPciSUdiT_hkuh1jhJNl3gZvkYwpYePTbG1VdjZJ5rr7hgCxzV2ALSsM7QHUXqj0WR0-Hn1so7fz02n',
                'secret' => 'EGArZ2ZViB8tyH3Y6fYTYoy_zdPZQzrcVKisM46r8HkAgyUdV5BoH-cf8ER0J40-WxdWIcOLKDdf0iK3',
                'account' => 'sb-voclm13447435@business.example.com',
                'app_id' => 'APP-80W284485P519543T',
                'webhook_id' => '8PV16905TY216884F',

                'currency' => 'GBP',
                'descriptor' => 'DJ Keith Hards',
                'currency_symbol_html' => '£',
            ],
        ];
        \App\Domains\Payment\Models\PaymentMethod::create($testData);
    }

    /**
     * @test
     */
    public function webhookPaymentCaptureRefund()
    {
        $this->partialMock(\Srmklive\PayPal\Services\PayPal::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAccessToken')->once()->andReturnNull();
            $mock->shouldReceive('verifyWebHook')->once()->andReturn([
                'verification_status' => 'SUCCESS', //'FAILURE',
            ]);
        });

        $this->expectsEvents(PaymentTransaction::class);

        // When we make a payment webhook request.
        $response = $this->json('POST', route('webhook.paypal'), $this->transactionPaymentCaptureRefund(), $this->headerSample());

        // The the request is successful.
        $response->assertSuccessful();

        // Then ensure that the payment was recorded against the order.
        $this->assertCount(1, $this->order->transactions);

        // Ensure that refund has been recorded.
        $this->assertEquals(-59.00, $this->order->transactions->sum('amount'));
    }

    /**
     * @test
     */
    public function webhookPaymentApproved()
    {
        $this->mockPayPal('SUCCESS', 'APPROVED');

        $this->expectsEvents(PaymentTransaction::class);

        // When we make a payment webhook request.
        $response = $this->json('POST', route('webhook.paypal'), $this->transactionApprovedBody(), $this->headerSample());

        // The the request is successful.
        $response->assertSuccessful();

        // Then ensure that the payment was recorded against the order.
        $this->assertCount(1, $this->order->transactions);

        // Ensure that payment hasn't yet been received.
        $this->assertEquals(0, $this->order->transactions->sum('amount'));
    }

    /**
     * @test
     */
    public function webhookPaymentCaptured()
    {
        $data = $this->transactionCaptureComplete();
        $headers = $this->headerSample();
        $uri = route('webhook.paypal');

        $this->expectsEvents(PaymentTransaction::class);

        $this->mockPayPal('SUCCESS', 'CAPTURE');
        $response = $this->json('POST', $uri, $data, $headers);
        $response->assertSuccessful();

        // Ensure that captured payment has been recorded
        $this->assertEquals(59.00, $this->order->transactions->sum('amount'));
    }

    /**
     * Mock paypal verify.
     *
     * @param $status
     */
    private function mockPayPal($status, $type)
    {
        // Mock the PayPal methods and verify response.
        $this->partialMock(\Srmklive\PayPal\Services\PayPal::class, function (MockInterface $mock) use ($status, $type) {
            $mock->shouldReceive('getAccessToken')->once()->andReturnNull();
            $mock->shouldReceive('verifyWebHook')->once()->andReturn([
                'verification_status' => $status,
            ]);
        });
    }

    /**
     * Create order for testing.
     *
     * @return Order
     */
    private function createOrder(): Order
    {
        $address = $this->getBusinessAddress();

        $business = factory(Business::class)->create([
            'name' => 'Night marer pt.1',
            'address_id' => $address->id,
            'timezone' => \DateTimeZone::UTC,
        ]);

        $staff = User::factory()->create();
        $business->users()->attach($staff);

        $billpayer = factory(ModelsBillpayer::class)->create();

        return Order::create([
            'number' => 1231,
            'status' => OrderStatus::NEW(),
            'user_id' => $staff->id,
            'billpayer_id' => $billpayer->id,
            'shipping_address_id' => $address->id,
            'dispatchPrice' => 12.11,
            'totalProductPrice' => 11.00,
            'adjustments' => -1.00,
            'totalPrice' => 454.22,
            'deposit' => 3.21,
            'start' => '2021-10-11 15:00:00',
            'end' => '2021-10-12 15:00:00',
            'business_id' => $business->id,
            'location' => 'Here and there',
        ]);
    }

    private function headerSample(): array
    {
        return [
            'host' => [0 => 'elitebookingsystem.com'],
            'accept' => [0 => '*/*'],
            'paypal-transmission-time' => [0 => '2022-02-24T22:01:03Z'],
            'paypal-auth-version' => [0 => 'v2'],
            'paypal-cert-url' => [0 => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-360caa42-fca2a594-7a8abba8'],
            'paypal-auth-algo' => [0 => 'SHA256withRSA'],
            'paypal-transmission-sig' => [
                0 => 'TNboUsy18fzRs5eTizBNO5hKbQcWzE81Si52DxMwjsH912CgaGYi8rT7jZK0JDd/pyyMf1MCwUOrjxMuWb/HGoKpfvYd9/1DTCjO66eGXCze9+5ORvlvKwWXYHAW02Eu6j2gJT0p0w+fVlF9JQU2TSHY/nAbq6eRoORW5/OxNGpIztQ518/hIY4vSe1P2LIjhJDLg9aRPLGpC2wTIOH2RDfZfksaZbYMq7yhYKgeiZRiqErDsXCnv8Xon7280qn4IgSsJvb2JDatIZ2tyE4kFUQxVzGwqVxseH3H4Ir+FE7v0f0hlyFohsMGNuctB6IIEuvFRV8UcKcX53PzrSdjUA==',
            ],
            'paypal-transmission-id' => [0 => '42182940-95bd-11ec-8889-914733057ff4'],
            'content-type' => [0 => 'application/json'],
            'user-agent' => [0 => 'PayPal/AUHD-214.0-56138150'],
            'correlation-id' => [0 => 'c576619f60c52'],
            'x-b3-spanid' => [0 => '31530b4fad9d05db'],
            'content-length' => [0 => '2348'],
        ];
    }

    private function transactionPaymentCaptureRefund()
    {
        $json = <<<'EOF'
        {
            "id": "WH-43R55384LP412101P-38S808231J2425126",
            "create_time": "2022-03-03T14:21:08.736Z",
            "resource_type": "refund",
            "event_type": "PAYMENT.CAPTURE.REFUNDED",
            "summary": "A GBP 59.0 GBP capture payment was refunded",
            "resource": {
                "seller_payable_breakdown": {
                    "total_refunded_amount": {
                        "value": "59.00",
                        "currency_code": "GBP"
                    },
                    "paypal_fee": {
                        "value": "0.00",
                        "currency_code": "GBP"
                    },
                    "gross_amount": {
                        "value": "59.00",
                        "currency_code": "GBP"
                    },
                    "net_amount": {
                        "value": "59.00",
                        "currency_code": "GBP"
                    }
                },
                "amount": {
                    "value": "59.00",
                    "currency_code": "GBP"
                },
                "update_time": "2022-03-03T06:21:05-08:00",
                "create_time": "2022-03-03T06:21:05-08:00",
                "custom_id": "1",
                "links": [
                    {
                        "method": "GET",
                        "rel": "self",
                        "href": "https://api.sandbox.paypal.com/v2/payments/refunds/62632988YT8069440"
                    },
                    {
                        "method": "GET",
                        "rel": "up",
                        "href": "https://api.sandbox.paypal.com/v2/payments/captures/5R796252C1471823C"
                    }
                ],
                "id": "62632988YT8069440",
                "status": "COMPLETED"
            },
            "status": "PENDING",
            "transmissions": [
                {
                    "webhook_url": "https://elitebookingsystem.com/webhook/paypal",
                    "http_status": 500,
                    "reason_phrase": "HTTP/1.1 200 Connection established",
                    "response_headers": {
                        "Transfer-Encoding": "chunked",
                        "Server": "Apache/2.4.46 (Ubuntu)",
                        "Cache-Control": "no-cache, private",
                        "Connection": "close",
                        "Date": "Thu, 03 Mar 2022 14:21:45 GMT",
                        "Content-Type": "text/html; charset=UTF-8"
                    },
                    "transmission_id": "3a17bb80-9afd-11ec-b5ae-8b1f6f9741a9",
                    "status": "PENDING",
                    "timestamp": "2022-03-03T14:21:33Z"
                }
            ],
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-43R55384LP412101P-38S808231J2425126",
                    "rel": "self",
                    "method": "GET",
                    "encType": "application/json"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-43R55384LP412101P-38S808231J2425126/resend",
                    "rel": "resend",
                    "method": "POST",
                    "encType": "application/json"
                }
            ],
            "event_version": "1.0",
            "resource_version": "2.0"
        }
        EOF;

        return json_decode($json, true);
    }

    private function transactionCaptureComplete()
    {
        return [
            'id' => 'WH-6GB0505332174213J-23542143VC2777914',
            'event_version' => '1.0',
            'create_time' => '2022-02-24T22:00:33.294Z',
            'resource_type' => 'capture',
            'resource_version' => '2.0',
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'summary' => 'Payment completed for GBP 59.0 GBP',
            'resource' => [
                    'amount' => [
                            'value' => '59.00',
                            'currency_code' => 'GBP',
                        ],
                    'seller_protection' => [
                            'dispute_categories' => [
                                    0 => 'ITEM_NOT_RECEIVED',
                                    1 => 'UNAUTHORIZED_TRANSACTION',
                                ],
                            'status' => 'ELIGIBLE',
                        ],
                    'supplementary_data' => [
                            'related_ids' => [
                                    'order_id' => '70V2887344993644B',
                                ],
                        ],
                    'update_time' => '2022-02-24T22:00:29Z',
                    'create_time' => '2022-02-24T22:00:29Z',
                    'final_capture' => true,
                    'seller_receivable_breakdown' => [
                            'paypal_fee' => [
                                    'value' => '2.21',
                                    'currency_code' => 'GBP',
                                ],
                            'gross_amount' => [
                                    'value' => '59.00',
                                    'currency_code' => 'GBP',
                                ],
                            'net_amount' => [
                                    'value' => '56.79',
                                    'currency_code' => 'GBP',
                                ],
                        ],
                    'custom_id' => $this->order->id,
                    'links' => [
                            0 => [
                                    'method' => 'GET',
                                    'rel' => 'self',
                                    'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/39W39499W4454115Y',
                                ],
                            1 => [
                                    'method' => 'POST',
                                    'rel' => 'refund',
                                    'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/39W39499W4454115Y/refund',
                                ],
                            2 => [
                                    'method' => 'GET',
                                    'rel' => 'up',
                                    'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/70V2887344993644B',
                                ],
                        ],
                    'id' => '39W39499W4454115Y',
                    'status' => 'COMPLETED',
                ],
            'links' => [
                    0 => [
                            'href' => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-6GB0505332174213J-23542143VC2777914',
                            'rel' => 'self',
                            'method' => 'GET',
                        ],
                    1 => [
                            'href' => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-6GB0505332174213J-23542143VC2777914/resend',
                            'rel' => 'resend',
                            'method' => 'POST',
                        ],
                ],
        ];
    }

    private function transactionApprovedBody()
    {
        $order = $this->order;

        return [
            'id' => 'WH-1U846850L9521810W-14A00367GK2126045',
            'event_version' => '1.0',
            'create_time' => '2022-02-24T22:00:33.226Z',
            'resource_type' => 'checkout-order',
            'resource_version' => '2.0',
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'summary' => 'An order has been approved by buyer',
            'resource' => [
                'update_time' => '2022-02-24T22:00:29Z',
                'create_time' => '2022-02-24T22:00:10Z',
                'purchase_units' => [
                    0 => [
                        'reference_id' => $order->id,
                        'amount' => [
                            'currency_code' => 'GBP',
                            'value' => '59.00',
                        ],
                        'payee' => [
                            'email_address' => 'sb-voclm13447435@business.example.com',
                            'merchant_id' => 'N4DVDLGAQGPEE',
                        ],
                        'description' => 'Order #4',
                        'custom_id' => $order->id,
                        'soft_descriptor' => 'DJ Keith Hards',
                        'shipping' => [
                            'name' => ['full_name' => 'John Doe'],
                            'address' => [
                                'address_line_1' => 'Whittaker House',
                                'address_line_2' => '2 Whittaker Avenue',
                                'admin_area_2' => 'Richmond',
                                'admin_area_1' => 'Surrey',
                                'postal_code' => 'TW9 1EH',
                                'country_code' => 'GB',
                            ],
                        ],
                        'payments' => [
                            'captures' => [
                                0 => [
                                    'id' => '39W39499W4454115Y',
                                    'status' => 'COMPLETED',
                                    'amount' => [
                                        'currency_code' => 'GBP',
                                        'value' => '59.00',
                                    ],
                                    'final_capture' => true,
                                    'seller_protection' => [
                                        'status' => 'ELIGIBLE',
                                        'dispute_categories' => [
                                            0 => 'ITEM_NOT_RECEIVED',
                                            1 => 'UNAUTHORIZED_TRANSACTION',
                                        ],
                                    ],
                                    'seller_receivable_breakdown' => [
                                        'gross_amount' => [
                                            'currency_code' => 'GBP',
                                            'value' => '59.00',
                                        ],
                                        'paypal_fee' => [
                                            'currency_code' => 'GBP',
                                            'value' => '2.21',
                                        ],
                                        'net_amount' => [
                                            'currency_code' => 'GBP',
                                            'value' => '56.79',
                                        ],
                                    ],
                                    'links' => [
                                        0 => [
                                            'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/39W39499W4454115Y',
                                            'rel' => 'self',
                                            'method' => 'GET',
                                        ],
                                        1 => [
                                            'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/39W39499W4454115Y/refund',
                                            'rel' => 'refund',
                                            'method' => 'POST',
                                        ],
                                        2 => [
                                            'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/70V2887344993644B',
                                            'rel' => 'up',
                                            'method' => 'GET',
                                        ],
                                    ],
                                    'create_time' => '2022-02-24T22:00:29Z',
                                    'update_time' => '2022-02-24T22:00:29Z',
                                ],
                            ],
                        ],
                    ],
                ],
                'links' => [
                    0 => [
                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/70V2887344993644B',
                        'rel' => 'self',
                        'method' => 'GET',
                    ],
                ],
                'id' => '70V2887344993644B',
                'intent' => 'CAPTURE',
                'payer' => [
                    'name' => [
                        'given_name' => 'John',
                        'surname' => 'Doe',
                    ],
                    'email_address' => 'sb-umi6h13447069@personal.example.com',
                    'payer_id' => 'YJ4S8P2CNT5QW',
                    'address' => [
                        'country_code' => 'GB',
                    ],
                ],
                'status' => 'COMPLETED',
            ],
            'links' => [
                0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-1U846850L9521810W-14A00367GK2126045',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                1 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-1U846850L9521810W-14A00367GK2126045/resend',
                    'rel' => 'resend',
                    'method' => 'POST',
                ],
            ],
        ];
    }
}
