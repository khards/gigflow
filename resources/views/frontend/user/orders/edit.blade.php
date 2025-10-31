@extends('frontend.layouts.app')

@section('title', $order->location)

@section('content')
    <div>
        <livewire:orders.order-edit :order="$order"/>
    </div>
@endsection

