@extends('layouts.app')

@section('title', 'Evenimente – Auditie Placuta')
@section('body_class', 'page-evenimente')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/evenimente.css') }}?v={{ filemtime(public_path('assets/css/evenimente.css')) }}">
@endpush

@section('content')
    @include('components.in-constructie')
@endsection