@extends('frontend.layouts.app')

@section('title', $business->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>
                    <x-slot name="header">
                        {{ $business->name }}
                    </x-slot>

                    <x-slot name="body">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <x-utils.link
                                    :text="__('Details')"
                                    class="nav-link active"
                                    id="my-profile-tab"
                                    data-toggle="pill"
                                    href="#my-profile"
                                    role="tab"
                                    aria-controls="my-profile"
                                    aria-selected="true" />

                                <x-utils.link
                                    :text="__('Edit Information')"
                                    class="nav-link"
                                    id="information-tab"
                                    data-toggle="pill"
                                    href="#information"
                                    role="tab"
                                    aria-controls="information"
                                    aria-selected="false"/>

                                <x-utils.link
                                    :text="__('Edit Payments')"
                                    class="nav-link"
                                    id="payments-tab"
                                    data-toggle="pill"
                                    href="#payments"
                                    role="tab"
                                    aria-controls="payments"
                                    aria-selected="false"/>

                            </div>
                        </nav>

                        <div class="tab-content" id="my-profile-tabsContent">
                            <div class="tab-pane fade pt-3 show active" id="my-profile" role="tabpanel" aria-labelledby="my-profile-tab">
                                @include('frontend.user.business.tabs.details')
                            </div><!--tab-profile-->

                            <div class="tab-pane fade pt-3" id="information" role="tabpanel" aria-labelledby="information-tab">
                                @include('frontend.user.business.tabs.information')
                            </div><!--tab-information-->

                            <div class="tab-pane fade pt-3" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                                @include('frontend.user.business.tabs.payments')
                            </div><!--tab-information-->

                        </div><!--tab-content-->
                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection
