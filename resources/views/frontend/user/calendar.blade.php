@extends('frontend.layouts.app')

@section('title', __('My Calendar'))

@section('content')
    <div class="container py-4">
        <div class="row justify-content-right ">
            <div class="col-md-12 mb-3">
                <div class="float-right">
                    <button @click="$refs.mycalendar.sync()" type="button" class="btn btn-pill btn-secondary">
                        <span id="calendar-sync-button-icon" class="fas fa-sync btn-icon mr-2"></span> Sync</button>

                    <a href="{{ route('frontend.user.account') }}#calendar-integration" type="button" class="btn btn-pill btn-secondary"><span class="cil-settings btn-icon mr-2"></span> Settings</a>
                </div>
            </div><!--col-md-12-->
        </div><!--row-->

        <div class="row justify-content-center">
            <h3>Availability for user: {{ \Illuminate\Support\Facades\Auth::user()->name }}</h3>
            <div class="col-md-12">
                <schedule-component ref="mycalendar"></schedule-component>
            </div><!--col-md-12-->
        </div><!--row-->
    </div><!--container-->
@endsection
