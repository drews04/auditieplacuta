@extends('layouts.app')

@section('title', 'Melodii câștigătoare')
@section('body_class', 'page-winners')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
@endpush

@section('content')
<div class="container py-5">

  {{-- Header / hero --}}
  <div class="winners-hero">
    <h1>🎖️ Melodii câștigătoare</h1>
  </div>

  {{-- Toolbar (search + per-page) --}}
  <form method="GET" class="winners-toolbar">
    <input
      class="form-control"
      name="q"
      value="{{ $q }}"
      placeholder="Caută după melodie, câștigător, temă…"
      aria-label="Căutare"
    />
    <div class="d-flex gap-2">
      <select name="per" class="form-select" onchange="this.form.submit()" aria-label="Rezultate pe pagină">
        @foreach([10,20,30,50,100] as $opt)
          <option value="{{ $opt }}" @selected($per === $opt)>{{ $opt }} / pagină</option>
        @endforeach
      </select>
      <button class="btn btn-primary fw-bold" type="submit">Caută</button>
    </div>
  </form>

  @if($winners->count() === 0)
    <div class="alert alert-secondary mt-2">Nu am găsit rezultate.</div>
  @else
    <div class="winners-card">
      <table class="winners-table">
        <thead>
          <tr>
            <th style="width:120px;">Data</th>
            <th>Tema</th>
            <th>Melodie</th>
            <th style="width:220px;">Câștigător</th>
            <th style="width:90px;">Voturi</th>
            <th style="width:88px;">Link</th>
          </tr>
        </thead>
        <tbody>
          @foreach($winners as $w)
            @php
              $when = $w->contest_date ?? optional($w->cycle)->vote_end_at;
              $date = $when ? $when->timezone(config('app.timezone'))->format('Y-m-d') : '—';

              $themeRaw = $w->theme->title ?? ($w->cycle->theme_text ?? '—');
              $parts    = preg_split('/\s*—\s*/u', (string)$themeRaw, 2);
              $cat      = trim($parts[0] ?? '');
              $title    = trim($parts[1] ?? $themeRaw);
            @endphp
            <tr>
              <td class="text-nowrap">{{ $date }}</td>

              <td>
                <span class="ap-theme-chip">
                  @if($cat !== '')<span>{{ $cat }}</span>@endif
                  <span>🎯</span>
                  <span>{{ $title }}</span>
                </span>
              </td>

              <td>{{ $w->song->title ?? 'Melodie' }}</td>

              <td>
                @if($w->user)
                  <a class="link-light text-decoration-underline"
                     href="{{ route('users.wins', ['userId' => $w->user->id]) }}">
                    {{ $w->user->name }}
                  </a>
                @else
                  —
                @endif
              </td>

              <td><span class="ap-votes-badge">{{ (int) $w->vote_count }}</span></td>

              <td>
                @if(!empty($w->song?->youtube_url))
                  <a class="ap-yt-link" href="{{ $w->song->youtube_url }}" target="_blank" rel="noopener">
                    YouTube
                  </a>
                @else
                  —
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Pagination --}}
      <div class="winners-pagination">
        {{ $winners->onEachSide(1)->links('vendor.pagination.simple-bootstrap-5') }}
      </div>
    </div>
  @endif
</div>
@endsection
