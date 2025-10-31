@extends('frontend.layouts.public')

@section('title', __('Order Payment'))
<?php
    /* @var \App\Domains\Order\Order $order */
    /* @var \Illuminate\Support\Collection $paypal */
    /* @var \Illuminate\Support\Collection $bank */
    /* @var \App\Booking\Business $business */

    $symbol = Symfony\Component\Intl\Currencies::getSymbol($paypal->get('currency', 'GBP'));
    $paypal = [
        'business_id' => $business->id,
        'account' => $paypal->get('account'),
        'clientId' => $paypal->get('clientId'),
        'currency' => $paypal->get('currency'),
        'currency_symbol_html' => $symbol,
        'descriptor' => $paypal->get('descriptor'),
        'bank' => $bank->toArray(),
        'description' => "Order #{$order->id}",
        'url' => 'https://www.paypal.com/sdk/js?client-id=',
        'order_id' => $order->id,
        'amount' => $order->totalPrice,
        'deposit' => $order->deposit,
        'reference' => ('B'.$business->id.'K'.$order->id),
    ];
?>
@section('meta')
    <script>
        window.payment_config = <?php echo json_encode($paypal) ?>;
    </script>
@endsection

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>
                    <x-slot name="header">
                        <h3>Order #{{ $order->id }}</h3>
                    </x-slot>

                    <x-slot name="body">
                        <paypal-buttons />
                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection

@section('before-scripts')

@endsection
