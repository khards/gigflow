@extends('frontend.layouts.app')

@section('title', $business->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="text-right">
                    <form action="{{ route('frontend.user.schedule.create', ['businessId' => $business->id]) }}" method="post">
                        {{ csrf_field() }}
                        <button
                            type="submit"
                            class="float-right btn btn-pill btn-success">
                            <span class="cil-plus btn-icon mr-2"></span>Add</button>
                    </form>
                    <div class="clearfix mb-2"></div>
                </div>

                <livewire:schedules.schedules-table :business="$business"/>

{{--                <x-frontend.card>--}}

{{--                    <x-slot name="header">--}}
{{--                        {{ $business->name }} - Schedules--}}
{{--                    </x-slot>--}}

{{--                    <x-slot name="body">--}}

{{--                        <div class="mb-2 container">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col"></div>--}}
{{--                                <div class="col"></div>--}}
{{--                                <div class="col"></div>--}}
{{--                                <div class="col">--}}
{{--                                    <form action="{{ route('frontend.user.schedule.create', ['businessId' => $business->id]) }}" method="post">--}}
{{--                                        {{ csrf_field() }}--}}
{{--                                    <button--}}
{{--                                        type="submit"--}}
{{--                                        class="float-right btn btn-pill btn-success">--}}
{{--                                        <span class="cil-plus btn-icon mr-2"></span>Add</button>--}}
{{--                                    </form>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <table class="table">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th scope="col"></th>--}}
{{--                                <th scope="col">Id</th>--}}
{{--                                <th scope="col">State</th>--}}
{{--                                <th scope="col">Summary</th>--}}
{{--                                <th scope="col">Category</th>--}}
{{--                                <th scope="col">Actions</th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            @foreach ($schedules as $schedule)--}}
{{--                                <tr>--}}
{{--                                    <td>@todo</td>--}}
{{--                                    <th scope="row">{{ $schedule->id }}</th>--}}
{{--                                    <td> TODO </td>--}}
{{--                                    <td>{{ $schedule->summary }}</td>--}}
{{--                                    <td>@todo</td>--}}
{{--                                    <td>--}}

{{--                                        <form style="display: inline;" action="{{ route('frontend.user.schedule.delete', ['scheduleId' => $schedule->id]) }}" method="post">--}}
{{--                                            {{ csrf_field() }}--}}
{{--                                            {{ method_field('DELETE') }}--}}
{{--                                            <button--}}
{{--                                                type="submit"--}}
{{--                                                class="btn btn-pill btn-danger">--}}
{{--                                                <span class="cil-trash btn-icon mr-2"></span>Delete</button>--}}

{{--                                        </form>--}}

{{--                                        <a  href="{{ route('frontend.user.schedule.edit', ['scheduleId' => $schedule->id]) }}"--}}
{{--                                            class="btn btn-pill btn-warning">--}}
{{--                                            <span class="cil-pencil btn-icon mr-2"></span>Edit</a>--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </x-slot>--}}
{{--                </x-frontend.card>--}}
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection
