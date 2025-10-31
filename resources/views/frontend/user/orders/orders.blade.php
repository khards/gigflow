@extends('frontend.layouts.app')

@section('title', $business->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>

                    <x-slot name="header">
                        {{ $business->name }} - Orders
                    </x-slot>

                    <x-slot name="body">
                        <livewire:orders.orders-table :business="$business"/>

                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->

    <!-- Modal -->
    <div wire:ignore.self class="modal fade"
         id="exampleModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Confirm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true close-btn">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure want to delete?</p>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary close-btn" data-dismiss="modal">Close</button>

                    <button type="button"
                            wire:click.prevent="delete()"
                            class="btn btn-danger close-modal"
                            data-dismiss="modal">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

@endsection
