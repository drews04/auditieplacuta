@extends('layouts.app')

@push('styles')
  <link rel="stylesheet"
        href="{{ asset('assets/css/concurs.css') }}?v={{ @filemtime(public_path('assets/css/concurs.css')) }}">
@endpush

@section('title', 'Concurs')

@section('content')
<div class="container py-5">

  {{-- Phase switch pill (enabled/disabled by server flags) --}}
  <div class="concurs-hub">
    <div class="hub-actions">
      {{-- LEFT: Vote --}}
      @php $voteHref = route('concurs.vote.page'); @endphp
      <a href="{{ $votingOpen ? $voteHref : '#' }}"
         class="hub-btn hub-btn--vote {{ $votingOpen ? '' : 'is-disabled' }}"
         aria-disabled="{{ $votingOpen ? 'false' : 'true' }}">
        ★ Votează
      </a>

      {{-- RIGHT: Upload --}}
      @php $uploadHref = route('concurs.upload.page'); @endphp
      <a href="{{ $submissionsOpen ? $uploadHref : '#' }}"
         class="hub-btn hub-btn--upload {{ $submissionsOpen ? '' : 'is-disabled' }}"
         aria-disabled="{{ $submissionsOpen ? 'false' : 'true' }}">
        ⬆️ Încarcă melodia
      </a>
    </div>

    {{-- Hint strip (weekend / nothing open) --}}
    @if(!$submissionsOpen && !$votingOpen)
      <div class="hub-hint">
        <span class="hub-hint__label">Weekend — concursul este în pauză.</span>
        @if($upcomingCycle)
          <span class="hub-hint__next">
            Următoarea rundă: {{ $upcomingCycle->start_at->timezone(config('app.timezone'))->isoFormat('dddd, D MMMM YYYY') }}
          </span>
        @endif
      </div>
    @endif
  </div>

</div>
@endsection
