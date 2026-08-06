@extends('layouts.doctor')

@section('title', 'Dashboard')

@section('content')
    <p class="text-gray-600">Welcome back, Dr. {{ auth('doctor')->user()->name }}.</p>
@endsection
