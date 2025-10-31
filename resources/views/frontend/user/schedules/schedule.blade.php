@extends('frontend.layouts.app')

@section('title', $schedule->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>

                    <x-slot name="header">
                        Edit schedule
                    </x-slot>

                    <x-slot name="body">

                        <?php
                        /*
                         * Form css documentation:
                         *      https://coreui.io/docs/components/bootstrap/forms/
                        */
                        ?>
                        <form action="{{ route('frontend.user.schedule.update', $schedule->id) }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="mb-2 container">
                                <div class="row">
                                    <div class="col"></div>
                                    <div class="col"></div>
                                    <div class="col"></div>
                                    <div class="col">
                                        <button
                                            type="submit"
                                            class="float-right btn btn-pill btn-success">
                                            <span class="cil-save btn-icon mr-2"></span>Save</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="schedule_summary">@lang('Schedule summary')</label>
                                <input
                                    type="text"
                                    name="schedule_summary"
                                    class="form-control @error('schedule_summary') is-invalid @enderror"
                                    id="schedule_name" placeholder="Schedule summary" value="{{ $schedule->summary }}">
                            </div>

                            <div class="form-group">
                                <label for="schedule_state">@lang('Schedule state')</label>
                                <select name="schedule_state"
                                        class="form-control @error('schedule_state') is-invalid @enderror"
                                        id="schedule_state">
                                    <option value="draft" {{ $schedule->state === 'draft' ? ' selected="selected" ' : '' }} >Inactive</option>
                                    <option value="active" {{ $schedule->state === 'active' ? ' selected="selected" ' : '' }} >Active</option>
                                </select>
                            </div>

                            <div>
                                <Recurrence
                                    timezone="{{ $schedule->timezone }}"
                                    rrule="{{ e($schedule->rrule) }}"
                                    dtstart="{{ DateTime::createFromFormat('Y-m-d H:i:s', $schedule->start_datetime)->format('Y-m-d\TH:i:s\Z') }}"
                                    dtend="{{ DateTime::createFromFormat('Y-m-d H:i:s', $schedule->end_datetime)->format('Y-m-d\TH:i:s\Z') }}" />
                            </div>

                        </form>

                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection
