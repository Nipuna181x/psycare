@extends('layouts.doctor')

@php
    $title = 'Conversations — '.$patient->name;
    $subtitle = 'AI companion conversation history';
@endphp

@section('content')
    @include('patients.conversations-index-content')
@endsection
