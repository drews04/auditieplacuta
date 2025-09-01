@extends('layouts.app')
@section('title', 'Tema lunii')
@section('body_class', 'page-tema-lunii')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/concurs.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/tema-lunii.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="container py-4">
  <h1 class="mb-3">🏅 Tema lunii — {{ \Illuminate\Support\Carbon::create($year, $month, 1)->isoFormat('MMMM YYYY') }}</h1>

  <p class="text-muted small mb-3">
    În caz de egalitate la like-uri: câștigă tema mai veche; dacă e tot egal, câștigă tema cu ID mai mic.
  </p>

  @php
    $currentMonth = \Carbon\Carbon::create($year, $month, 1);
    $prevMonth = $currentMonth->copy()->subMonth();
    $nextMonth = $currentMonth->copy()->addMonth();
  @endphp

  {{-- Navigation buttons --}}
  <div class="tema-lunii-nav d-flex justify-content-between align-items-center">
    <a href="{{ route('arena.clasamente.tema-lunii', ['y' => $prevMonth->year, 'm' => $prevMonth->month]) }}"
       class="ap-btn-neon">
      ← {{ $prevMonth->isoFormat('MMMM YYYY') }}
    </a>

    <a href="{{ route('arena.clasamente.tema-lunii', ['y' => $nextMonth->year, 'm' => $nextMonth->month]) }}"
       class="ap-btn-neon">
      {{ $nextMonth->isoFormat('MMMM YYYY') }} →
    </a>
  </div>
  {{-- TOP 10 LIST ITEMS --}}
@foreach($rows as $row)
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <div>
      <span class="ap-badge ap-badge-soft me-2">{{ $row->category ?? '—' }}</span>
      <strong>{{ $row->name }}</strong>
      <span class="ap-muted ms-2">• Aleasă de: <strong>{{ $row->chooser_name }}</strong></span>
    </div>
    <span class="ap-badge ap-badge-dark">❤️ {{ $row->likes_count }}</span>
  </li>
@endforeach

  @if($rows->count() > 1)
    <h5 class="mt-4 mb-2">Top 10 teme ale lunii</h5>
    <ol class="list-group list-group-numbered">
      @foreach($rows as $row)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <span class="ap-badge ap-badge-soft me-2">{{ $row->category ?? '—' }}</span>
            <strong>{{ $row->name }}</strong>
          </div>
          <span class="ap-badge ap-badge-dark">❤️ {{ $row->likes_count }}</span>
        </li>
      @endforeach
    </ol>
  @endif
</div>
@endsection
