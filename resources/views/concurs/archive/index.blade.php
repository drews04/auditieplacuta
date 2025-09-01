@extends('layouts.app')

@section('title', 'Arhivă Concurs')
@section('body_class', 'page-concurs-archive')

@section('content')
<div class="container py-5">
  <h1 class="mb-4 fw-bold">Arhivă Concurs</h1>

  @forelse($cycles as $c)
    @php
      $w = $c->winner_snapshot ?? null;
    @endphp
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div class="mb-2">
          <div class="small text-muted">
            Încheiat: {{ $c->vote_end_at->timezone(config('app.timezone'))->format('D, d M Y H:i') }}
          </div>
          <div class="fw-semibold">Tema: {{ $c->theme_text ?? '—' }}</div>
          @if($w)
            <div class="mt-1">
              🏆 {{ $w->song->title ?? 'Melodie' }}
              <span class="text-muted">de</span>
              <span class="fw-semibold">{{ $w->user->name ?? 'necunoscut' }}</span>
              <span class="ms-2 badge bg-success">{{ $w->vote_count }} voturi</span>
            </div>
          @else
            <div class="mt-1 text-muted">Rezultate în curs de validare…</div>
          @endif
        </div>

        <a class="btn btn-outline-info"
           href="{{ route('concurs.arhiva.show', $c->vote_end_at->toDateString()) }}">
          Detalii & clasament
        </a>
      </div>
    </div>
  @empty
    <div class="alert alert-info">Nu există încă runde încheiate.</div>
  @endforelse

  <div class="mt-3">
    {{ $cycles->links() }}
  </div>
</div>
@endsection
