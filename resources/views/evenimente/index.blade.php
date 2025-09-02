@extends('layouts.app')
@section('title', 'Evenimente')
@section('body_class', 'page-events')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/events.css') }}">
@endpush

@section('content')
<div class="events-page-wrap">
  <div class="container py-5">
    <div class="events-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h1 class="mb-0 fw-bold text-neon">Evenimente</h1>
      @auth
        <a href="{{ route('events.create') }}" class="btn btn-neon events-add-btn">+ Adaugă eveniment</a>
      @endauth
    </div>

  @if(session('success'))
    <div class="alert alert-neon mb-4">{{ session('success') }}</div>
  @endif

  @forelse($events as $ev)
    <article class="event-card neon-card mb-5">
      <div class="event-poster-wrap">
        <img class="event-poster" src="{{ asset('storage/'.$ev->poster_path) }}" alt="{{ $ev->title }}">
      </div>
      <div class="p-3 p-md-4">
        <h2 class="h4 fw-bold mb-2">{{ $ev->title }}</h2>
        @if($ev->event_date)
          <div class="small text-muted mb-2">Data: {{ \Illuminate\Support\Carbon::parse($ev->event_date)->isoFormat('D MMM YYYY') }}</div>
        @endif
        @if($ev->body)
          <div class="event-body">{!! nl2br(e($ev->body)) !!}</div>
        @endif
        <div class="small text-muted mt-3">Adăugat de {{ $ev->user->name ?? 'utilizator' }} • {{ $ev->created_at->diffForHumans() }}</div>
      </div>
    </article>
  @empty
    <div class="neon-card p-4 text-center opacity-75">Niciun eveniment încă.</div>
  @endforelse

  <div class="mt-4">{{ $events->links() }}</div>
  </div>
</div>
@endsection
