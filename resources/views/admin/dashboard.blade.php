@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <p class="text-gray-600">Welcome back, {{ auth('admin')->user()->name }}.</p>
@endsection
