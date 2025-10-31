@extends('frontend.layouts.app')

@section('title', $form->name)

@section('content')
    <div>
        <livewire:forms.form-edit :form="$form"/>
    </div>
@endsection

