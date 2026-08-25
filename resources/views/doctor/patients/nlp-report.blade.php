@extends('layouts.doctor')

@php
    $title = 'NLP Report — '.$patient->name;
    $subtitle = 'Patient conversation classification history';
@endphp

@section('content')
    @include('patients.nlp-report-content')
@endsection
