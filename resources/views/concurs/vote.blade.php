{{-- resources/views/concurs/vote.blade.php --}}
@extends('layouts.app')

@push('styles')
  {{-- keep SAME css includes so styling + vanish stay identical --}}
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
  {{-- Heart/likes styles (match /concurs + upload) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/theme-like.css') }}?v={{ filemtime(public_path('assets/css/theme-like.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-override.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-mobile.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/song-disqualified.css') }}?v={{ time() }}">
@endpush

{{-- main site styles (same include as /concurs) --}}
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

@section('title', 'Votează — Concurs')
@section('body_class', 'page-concurs')

@section('content')
<div class="container py-5">

  <h1 class="mb-3 text-center" style="font-weight:800; letter-spacing:1px;">🔊 Votează melodiile de ieri</h1>
  <p class="text-center mb-4">Ascultă melodiile participante și votează-ți favorita!</p>

  {{-- ===== VOTE BLOCK (same look/classes as on /concurs) ===== --}}
  @if($cycleVote && isset($songsVote))
    @php
      $tz            = config('app.timezone', 'Europe/Bucharest');
      $voteOpensAtTZ = $voteOpensAt ? \Carbon\Carbon::parse($voteOpensAt)->timezone($tz) : null;
      $voteClosesAt  = ($cycleVote && $cycleVote->vote_end_at) ? \Carbon\Carbon::parse($cycleVote->vote_end_at)->timezone($tz) : null;

      // Parse "Category - Theme Name" from theme_text (hyphen, not em dash)
      $parts = $cycleVote->theme_text ? preg_split('/\s*[-—]\s*/u', (string)$cycleVote->theme_text, 2) : [];
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? ($cycleVote->theme_text ?? '-'));

      // Normalize badge text
      $catDisp = [
        'csd' => 'CSD', 'it' => 'ITC', 'itc' => 'ITC',
        'artisti' => 'Artiști', 'genuri' => 'Genuri',
      ][mb_strtolower($cat)] ?? mb_strtoupper($cat);

      // Theme likes (now queried from database)
      $voteThemeId    = $voteTheme->id ?? 0;
      $voteLikesCount = $voteTheme->likes_count ?? 0;
      $voteLiked      = $voteTheme->liked_by_me ?? false;

      // Preview flag — when theme chosen but voting not yet opened (legacy, kept for safety)
      $isPreVote = !empty($preVote) && $preVote;
    @endphp

    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <h5 class="card-title mb-2 d-flex align-items-center gap-2">
          ★ Votează
          @if(!empty($votingOpen) && $votingOpen)
            <span class="badge text-bg-success ap-badge-clear">Deschis până la {{ $voteClosesAt?->format('H:i') ?? '20:00' }}</span>
          @elseif($isPreVote)
            <span class="badge text-bg-secondary">
              Previzualizare — începe la {{ $voteOpensAtTZ?->format('H:i') ?? '00:00' }}
            </span>
          @else
            <span class="badge text-bg-secondary">Vot închis</span>
          @endif

          @auth
            @if(!empty($submissionsOpen) && $submissionsOpen)
              <a href="{{ route('concurs.upload.page') }}" class="btn btn-outline-primary btn-sm ms-auto">⬆️ Încarcă</a>
            @endif
          @endauth
        </h5>

        {{-- THEME ROW --}}
        @if($cycleVote->theme_text)
          <div class="ap-theme-row ap-theme-section">
            <div class="ap-left">
              @if($cat !== '') <span class="ap-cat-badge">{{ $catDisp }}</span><span class="ap-dot">🎯</span> @endif
              <span class="ap-label">Tema:</span>
              <span class="ap-title">{{ $title }}</span>

              {{-- HEART next to the title --}}
              @if($voteThemeId)
                <div class="dropdown d-inline-block theme-like-wrap ms-2">
                  <button type="button"
                          class="btn btn-sm theme-like {{ $voteLiked ? 'is-liked' : '' }}"
                          data-likeable-type="contest"
                          data-likeable-id="{{ $voteThemeId }}"
                          data-liked="{{ $voteLiked ? 1 : 0 }}"
                          data-count="{{ (int)$voteLikesCount }}"
                          @guest data-auth="0" @endguest>
                    <i class="heart-icon"></i>
                    <span class="like-count">{{ (int)$voteLikesCount }}</span>
                  </button>
                  <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
                    @forelse($voteTheme->likes ?? [] as $like)
                      <li class="small text-muted">❤️ {{ $like->user->name }}</li>
                    @empty
                      <li class="small text-muted">Niciun like încă</li>
                    @endforelse
                  </ul>
                </div>
              @endif
            </div>
          </div>
        @endif

        {{-- PRE-VOTE BANNER --}}
        @if($isPreVote)
          <div class="alert alert-info text-center fw-semibold mb-3">
            Votul începe la <strong>{{ $voteOpensAtTZ ? $voteOpensAtTZ->format('H:i') : '00:00' }}</strong>.
          </div>
        @endif

        {{-- SONGS LIST --}}
        @include('partials.songs_list', [
          'songs'               => $songsVote,
          'showVoteButtons'     => (!empty($votingOpen) && $votingOpen) && !($userHasVotedToday ?? false),
          'hideDisabledButtons' => $isPreVote ? true : false,
          'disabledVoteText'    => $isPreVote
                                    ? 'Votul începe la ' . ($voteOpensAtTZ ? $voteOpensAtTZ->format('H:i') : '00:00')
                                    : ((!empty($votingOpen) && $votingOpen) ? 'Ai votat deja' : 'Vot închis'),
          'userHasVotedToday'   => $userHasVotedToday ?? false,
          'votedSongId'         => $votedSongId ?? null,
        ])

        @if(!( !empty($votingOpen) && $votingOpen ) && !$isPreVote)
          <div class="small text-muted mt-2">Votul s-a închis la {{ $voteClosesAt?->format('H:i') ?? '20:00' }}.</div>
        @endif
      </div>
    </div>

  @else
    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <h5 class="card-title mb-2">★ Votează</h5>
        <div class="text-muted">Nu există melodii de votat pentru această rundă.</div>
      </div>
    </div>
  @endif

</div>
@endsection

@push('scripts')
  <script>
    window.voteRoute = "{{ route('concurs.vote') }}";
    window.csrfToken = "{{ csrf_token() }}";
    window.routeThemesLikeToggle = "{{ route('themes.like.toggle') }}";
    window.concursFlags = {
      votingOpen: {!! (!empty($votingOpen) && $votingOpen) ? 'true' : 'false' !!},
      isPreVote:  {!! (!empty($preVote) && $preVote) ? 'true' : 'false' !!}
    };
  </script>

  <script src="{{ asset('js/concurs.js') }}" defer></script>
  <script src="{{ asset('js/theme-like.js') }}?v={{ filemtime(public_path('js/theme-like.js')) }}" defer></script>
@endpush
