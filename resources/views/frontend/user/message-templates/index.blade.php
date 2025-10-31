@extends('frontend.layouts.app')

@section('title', $business->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>

                    <x-slot name="header">
                        {{ $business->name }} - Message Templates
                    </x-slot>

                    <x-slot name="body">

                        <div class="mb-2 container">
                            <div class="row">
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col"></div>
                            </div>
                        </div>

                        <livewire:message-templates.table :business="$business"/>
                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection
