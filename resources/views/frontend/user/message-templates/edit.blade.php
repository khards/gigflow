@extends('frontend.layouts.app')

@section('title', $mailTemplate->subject)

@section('content')
    <div>
        <livewire:message-templates.edit :mailTemplate="$mailTemplate"/>
    </div>
@endsection

