@extends('frontend.layouts.public')

@section('title', __('Order'))

@section('meta')
    <script>
        window.config = {!! $js_config !!};
    </script>
@endsection

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>
                    <x-slot name="header">

                    </x-slot>

                    <x-slot name="body">
                        <order-widget></order-widget>
                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection

@push(('after-scripts'))
<style>

</style>
@endpush
