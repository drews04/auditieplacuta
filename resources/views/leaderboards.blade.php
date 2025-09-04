@extends('layouts.app')

@section('content')
@php
  // Scope flags (now includes POSITIONS)
  $scope   = $scope ?? request('scope', 'alltime'); // default ALL-TIME
  $isPos   = $scope === 'positions';
  $isAll   = $scope === 'alltime';
  $isMonth = $scope === 'monthly';
  $isYear  = $scope === 'yearly';

  // Filters (controller may pass them; otherwise read from query)
  $ym = $ym ?? request('ym', now()->format('Y-m'));
  $y  = $y  ?? request('y',  now()->format('Y'));

  // Fallback weights for points display (used only by legacy tabs)
  $W_WINS  = 10;
  $W_VOTES = 1;
  $W_PARTS = 2;
@endphp

{{-- keep tabs above any header dropdowns / fixed nav --}}
<style>
  .ap-tabs { position: relative; z-index: 2001; }
</style>

<div class="container py-4 py-md-5">

  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 mb-md-4">
    <div class="d-flex align-items-center gap-2">
      <h1 class="h3 m-0">Clasament</h1>
      <span class="badge bg-secondary">
        {{ $isPos ? 'Poziții' : ($isAll ? 'All-Time' : ($isMonth ? 'Lunar' : 'Anual')) }}
      </span>
    </div>

    {{-- Tabs --}}
    <div class="d-flex align-items-center gap-2 ap-tabs">
      <ul class="nav nav-pills">
        <li class="nav-item">
          <a class="nav-link {{ $isPos ? 'active' : '' }}"
             href="{{ route('leaderboard.positions') }}">
            POZIȚII
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $isAll ? 'active' : '' }}"
             href="{{ route('leaderboard.alltime') }}">
            ALL-TIME
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $isMonth ? 'active' : '' }}"
             href="{{ route('leaderboard.monthly') }}">
            MONTHLY
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $isYear ? 'active' : '' }}"
             href="{{ route('leaderboard.yearly') }}">
            YEARLY
          </a>
        </li>
      </ul>

      {{-- Filters (only visible when needed) --}}
      @if($isMonth)
        <form method="GET" action="{{ route('leaderboard.monthly') }}" class="ms-2">
          <input type="month" name="ym" value="{{ $ym }}"
                 class="form-control form-control-sm" onchange="this.form.submit()"/>
        </form>
      @elseif($isYear)
        <form method="GET" action="{{ route('leaderboard.yearly') }}" class="ms-2">
          <input type="number" name="y" value="{{ $y }}" min="2000" max="2100"
                 class="form-control form-control-sm" onchange="this.form.submit()"/>
        </form>
      @endif
    </div>
  </div>

  {{-- Info line --}}
  <div class="text-muted small mb-3">
    @if($isPos)
      Clasament POZIȚII (puncte agregate din pozițiile zilnice). Ordinea: puncte ↓, apoi ID ↑.
    @elseif($isAll)
      Clasament All-Time. Ordinea: victorii ↓, voturi primite ↓, participări ↓, nume ↑.
    @elseif($isMonth)
      Clasament pentru luna
      <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') }}</strong>.
      <em>(Puncte din ledger pentru luna selectată.)</em>
      Ordinea: puncte ↓, nume ↑.
    @else
      Clasament pentru anul <strong>{{ $y }}</strong>.
      Ordinea: victorii ↓, voturi primite ↓, participări ↓, nume ↑.
    @endif
  </div>

  {{-- Table --}}
  <div class="ap-card ap-card-hover rounded-3 p-3 p-md-4">
    <div class="table-responsive">
      <table class="ap-table table table-dark table-borderless align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th class="text-center">Participări</th>
            <th class="text-center">Victorii</th>
            <th class="text-center">Voturi primite</th>
            <th class="text-center">Voturi date</th>
            <th class="text-center">Win Rate</th>
            <th class="text-end">Puncte</th>
          </tr>
        </thead>
        <tbody>
          @php $start = $page_start ?? 0; @endphp

          @forelse($rows as $i => $r)
            @php
              $rank   = $start + $i + 1;
              $isTop3 = $rank <= 3;
              $badgeBg = $rank==1 ? 'linear-gradient(135deg,#ffd700,#ffb700)'
                        : ($rank==2 ? 'linear-gradient(135deg,#c0c0c0,#a9a9a9)'
                                    : 'linear-gradient(135deg,#cd7f32,#a46a29)');
              $points = $r->points
                ?? ($r->wins * $W_WINS) + ($r->votes_received * $W_VOTES) + ($r->participations * $W_PARTS);
              $winRate = ($r->participations ?? 0) > 0
                ? round(($r->wins / max($r->participations,1)) * 100) . '%'
                : '—';
            @endphp
            <tr>
              <td class="fw-semibold">
                @if($isTop3)
                  <span class="ap-rank-badge" style="background: {{ $badgeBg }};">{{ $rank }}</span>
                @else
                  {{ $rank }}
                @endif
              </td>
              <td>{{ $r->name }}</td>
              <td class="text-center">{{ $r->participations ?? '—' }}</td>
              <td class="text-center fw-semibold">{{ $r->wins ?? '—' }}</td>
              <td class="text-center">{{ $r->votes_received ?? '—' }}</td>
              <td class="text-center">{{ $r->votes_given ?? '—' }}</td>
              <td class="text-center">{{ $winRate }}</td>
              <td class="text-end fw-bold">{{ $points }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                Nu există date pentru această perioadă.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3"><x-pagination :paginator="$rows" /></div>
  </div>
</div>
@endsection
