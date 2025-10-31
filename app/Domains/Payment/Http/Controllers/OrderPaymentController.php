<?php

namespace App\Domains\Payment\Http\Controllers;

use App\Domains\Order\Order;
use App\Domains\Payment\Contracts\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory as FactoryAlias;
use Illuminate\Contracts\View\View;

class OrderPaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    /**
     * View the payment form for this order.
     *
     * @param \App\Domains\Order\Order $order
     * @return \Illuminate\Contracts\Foundation\Application|FactoryAlias|View
     */
    public function show(Order $order)
    {
        $paypal = collect($this->paymentService->get($order->business, 'paypal'));
        $bank = collect($this->paymentService->get($order->business, 'bank'));
        $business = $order->business;

        return view('frontend.pages.order-payment', compact(['order', 'paypal', 'bank', 'business']));
    }
}
