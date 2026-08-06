@extends('layouts.medical-center')

@section('title', 'Dashboard')

@section('content')
    <p class="text-gray-600">Welcome back, {{ auth('medical_center')->user()->name }}.</p>
@endsection
