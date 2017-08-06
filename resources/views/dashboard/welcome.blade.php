@extends('dashboard.layouts.master')
@section('content')
    <h3>Welcome, </h3>
    <h1>{{ Auth::user()->name }}</h1>
@endsection