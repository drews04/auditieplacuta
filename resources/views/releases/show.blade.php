@extends('layouts.app')
@section('title', $release->title.' – Noutăți în muzică')
@section('body_class', 'page-release-show')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/releases.css') }}">
@endpush

@section('content')
<div class="container py-5">

  <a href="{{ route('releases.week', $release->week_key) }}" class="btn btn-neon mb-4">&laquo; Înapoi la săptămâna {{ $release->week_key }}</a>

  <article class="neon-card hero-release d-flex flex-wrap">
    <div class="hero-poster flex-shrink-0">
      <img src="{{ asset('storage/'.$release->cover_path) }}" alt="{{ $release->title }}">
    </div>

    <div class="hero-body p-3 p-md-4 flex-grow-1">
      <h1 class="fw-bold text-neon mb-1">{{ $release->artists->pluck('name')->join(', ') }}</h1>
      <h2 class="h4 mb-2">{{ $release->title }}</h2>

      <div class="small text-accent mb-2">
        {{ $release->categories->pluck('name')->join(' | ') }}
        @if($release->release_date) • {{ $release->release_date->isoFormat('D MMM YYYY') }} @endif
      </div>

      @if($release->description)
        <div class="release-body">{!! nl2br(e($release->description)) !!}</div>
      @else
        <p class="opacity-75">Fără descriere.</p>
      @endif
    </div>
  </article>

</div>
@endsection
