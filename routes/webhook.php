<?php

use App\Domains\Payment\Webhooks\PayPal;

Route::post('webhook/paypal/', function (Illuminate\Http\Request $request) {
    $paypal = app()->make(PayPal::class);
    $paypal->processRequest($request);

    return response('OK', 200);
})->name('webhook.paypal');
