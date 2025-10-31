<?php

namespace App\Domains\Payment\Webhooks;

use App\Booking\Business;
use App\Domains\Order\Order;
use App\Domains\Payment\Contracts\PaymentService;
use App\Domains\Payment\Contracts\TransactionService;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

class PayPal
{
    public function __construct(private TransactionService $transactionService, private PaymentService $paymentService)
    {
    }

    public function processRequest($request)
    {
        Log::debug('Request received on webhook/paypal/');
        Log::debug('request->toArray() = ', $request->toArray());

        // https://paypal.github.io/PayPal-PHP-SDK/
        // When your app receives a notification message, it must:
        //     Verify that the notification message came from PayPal
        //     Was not altered or corrupted during transmission
        //     Was targeted for you
        //     Contains a valid signature
        //     Respond with an HTTP 200 code
        //     Note: If your app responds with any other status code, PayPal tries to resend the notification message 25 times over the course of three days.

        $resource = $request->get('resource');

        $orderId = 0;

        // 1. Initial Auth.
        if ($request->get('event_type') === 'CHECKOUT.ORDER.APPROVED') {
            $orderId = (int) $resource['purchase_units'][0]['reference_id'] ?? 0;
        }

        // 2. Capture
        elseif ($request->get('event_type') === 'PAYMENT.CAPTURE.COMPLETED') {
            $orderId = isset($resource['custom_id']) ? (int) $resource['custom_id'] : 0; //Will not exist in test simulator
        }

        // 3/4 Refunds
        // PAYMENT.CAPTURE.REFUNDED	A merchant refunds a payment capture.
        // PAYMENT.CAPTURE.REVERSED	PayPal reverses a payment capture.
        elseif ($request->get('event_type') === 'PAYMENT.CAPTURE.REFUNDED' || $request->get('event_type') === 'PAYMENT.CAPTURE.REVERSED') {
            $orderId = isset($resource['custom_id']) ? (int) $resource['custom_id'] : 0; //Will not exist in test simulator
        }

        $order = Order::find($orderId);

        if(!$order) {
            Log::warning(__METHOD__ . ' Error, IPN not processed due to order not found ' . $orderId);
            return;
        }
        $this->setConfig($order->business);

        $status = $this->paypalVerifyStatus($request);

        if ($status === 'SUCCESS') {
            // Log::debug("Verified webhook SUCCESS!!!");

            $resource = $request->get('resource');

            // 1. Initial Auth.
            if ($request->get('event_type') === 'CHECKOUT.ORDER.APPROVED') {
                $captures = collect($resource['purchase_units'][0]['payments']['captures'][0] ?? null);
                $paypalStatus = $captures->get('status');
                $orderId = (int) $resource['purchase_units'][0]['reference_id'] ?? 0;

                $amount = 0;
                $currency = '';
                $this->addTransaction($orderId, $amount, $currency, $resource, $paypalStatus, 'paypal:approved');
            }

            // 2. Capture
            elseif ($request->get('event_type') === 'PAYMENT.CAPTURE.COMPLETED') {
                $paypalStatus = $resource['status'];
                $amount = (float) $resource['amount']['value'];
                $currency = $resource['amount']['currency_code'];
                $orderId = (int) $resource['custom_id'];

                $this->addTransaction($orderId, $amount, $currency, $resource, $paypalStatus, 'paypal:capture');
            }

            // 3/4 Refunds
            // PAYMENT.CAPTURE.REFUNDED	A merchant refunds a payment capture.
            // PAYMENT.CAPTURE.REVERSED	PayPal reverses a payment capture.
            elseif ($request->get('event_type') === 'PAYMENT.CAPTURE.REFUNDED' || $request->get('event_type') === 'PAYMENT.CAPTURE.REVERSED') {
                $paypalStatus = $resource['status'];
                $amount = (float) 0 - $resource['amount']['value'];
                $currency = $resource['amount']['currency_code'];
                $orderId = (int) $resource['custom_id'];

                $this->addTransaction($orderId, $amount, $currency, $resource, $paypalStatus, 'paypal:refund');
            }
        } else {
            Log::debug('Verified webhook, failed ', $status);
        }
    }

    /**
     * Store transaction.
     *
     * @param  int  $orderId
     * @param  float  $amount
     * @param  string  $currency
     * @param  array  $details
     * @param  string  $note
     * @param  string  $transactionType
     * @throws Exception
     */
    private function addTransaction(int $orderId, float $amount, string $currency, array $details, string $note, string $transactionType)
    {
        if (! Order::find($orderId)) {
            throw new Exception('Invalid order ID');
        }

        $this->transactionService->create($orderId, 'paypal', $amount, $currency, $details, $note, $transactionType);
    }

    private function setConfig(Business $business): void
    {
        $config = $this->paymentService->get($business, 'paypal');

        $config = [

            'mode' => env('PAYPAL_MODE', 'sandbox'),

            'webhook_id' => $config->get('webhook_id'),

            'sandbox' => [
                'client_id' => $config->get('clientId'),
                'client_secret' => $config->get('secret'),
                'app_id' => $config->get('app_id'),
            ],
            'live' => [
                'client_id' => $config->get('clientId'),
                'client_secret' => $config->get('secret'),
                'app_id' => $config->get('app_id'),
            ],

            'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Can only be 'Sale', 'Authorization' or 'Order'
            'currency' => env('PAYPAL_CURRENCY', 'GBP'),
            'notify_url' => env('PAYPAL_NOTIFY_URL', 'https://elitebookingsystem.com/webhook/paypal/'), // Change this accordingly for your application.
            'locale' => env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
            'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.
        ];

        Config::set('paypal', $config);
    }

    /**
     * @param $request
     * @return string
     * @throws BindingResolutionException
     * @throws Throwable
     */
    private function paypalVerifyStatus($request): string
    {
        $paypal = app()->make(PayPalClient::class);
        $paypal->getAccessToken();

        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);
        $responseBody = $request->all();

        $wdata = [
            'auth_algo' => $headers['PAYPAL-AUTH-ALGO'][0], // $request->headers->get('PAYPAL-AUTH-ALGO')
            'cert_url' => $headers['PAYPAL-CERT-URL'][0],
            'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'][0],
            'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'][0],
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'][0],
            'webhook_id' => Config::get('paypal.webhook_id'),
            'webhook_event' => $responseBody,
        ];

        $response = $paypal->verifyWebHook($wdata);

        if (isset($response['verification_status']) && $response['verification_status'] === 'FAILURE') {
            Log::debug('PayPal Webhook validation status = FAILURE!', $response);
            throw new Exception('PayPal Webhook validation status = FAILURE!');
        }

        if (isset($response['type']) && $response['type'] === 'error') {
            Log::debug('PayPal Webhook validation error!', $response);
            throw new Exception('PayPal Webhook Validation Error');
        }

        return $response['verification_status'];
    }
}
