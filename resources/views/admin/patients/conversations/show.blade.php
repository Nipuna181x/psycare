@extends('layouts.admin')

@php
    $title = 'Conversation — '.$patient->name;
    $subtitle = $session->created_at->format('j M Y, g:i A');
@endphp

@section('content')
    @include('patients.conversation-show-content')
@endsection
