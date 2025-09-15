@extends('layouts.app')

@push('styles')
  {{-- keep SAME css includes so styling + vanish stay identical --}}
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
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
  @if($cycleVote && $songsVote->isNotEmpty())
    @php
      $voteOpensAt  = optional($cycleVote->vote_start_at)->timezone(config('app.timezone'));
      $voteClosesAt = optional($cycleVote->vote_end_at)->timezone(config('app.timezone'));

      // theme likes (controller will usually pass $voteTheme; these guards keep the page safe)
      $parts = $cycleVote->theme_text
        ? preg_split('/\s*—\s*/u', $cycleVote->theme_text, 2)
        : [];
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? ($cycleVote->theme_text ?? '—'));

      $voteTheme      = $voteTheme ?? ($cycleVote->contestTheme ?? null);
      $voteThemeId    = $voteTheme->id ?? ($cycleVote->contest_theme_id ?? 0);
      $voteLikesCount = $voteTheme->likes_count ?? ($voteTheme ? $voteTheme->likes->count() : 0);
      $voteLiked      = auth()->check() && $voteTheme
                        ? $voteTheme->likes->where('user_id', auth()->id())->isNotEmpty()
                        : false;
    @endphp

    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <h5 class="card-title mb-2 d-flex align-items-center gap-2">
          ★ Votează
          @if($votingOpen)
            <span class="badge text-bg-success">Deschis până la {{ $voteClosesAt?->format('H:i') ?? '20:00' }}</span>
          @else
            <span class="badge text-bg-secondary">Vot închis</span>
          @endif
        </h5>

        @if($cycleVote->theme_text)
          <div class="ap-theme-row mb-3">
            <div class="ap-left">
              @if($cat !== '') <span class="ap-cat-badge">{{ $cat }}</span><span class="ap-dot">🎯</span> @endif
              <span class="ap-label">Tema:</span>
              <span class="ap-title">{{ $title }}</span>
            </div>

            <div class="dropdown d-inline-block theme-like-wrap">
              <button type="button"
                      class="btn btn-sm theme-like"
                      data-likeable-type="contest"
                      data-likeable-id="{{ $voteThemeId }}"
                      data-liked="{{ $voteLiked ? 1 : 0 }}"
                      data-count="{{ $voteLikesCount }}"
                      @guest data-auth="0" @endguest
                      data-bs-toggle="dropdown"
                      aria-expanded="false">
                <i class="heart-icon"></i>
                <span class="like-count">{{ $voteLikesCount }}</span>
              </button>
              <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
                @forelse($voteTheme->likes ?? [] as $like)
                  <li class="small text-muted">❤️ {{ $like->user->name }}</li>
                @empty
                  <li class="small text-muted">Niciun like încă</li>
                @endforelse
              </ul>
            </div>
          </div>
        @endif

        {{-- SAME list partial + SAME classes so the vanish .vote-btn works --}}
        @include('partials.songs_list', [
          'songs'               => $songsVote,
          'userHasVotedToday'   => $userHasVotedToday,
          'showVoteButtons'     => $votingOpen && !$userHasVotedToday,
          'hideDisabledButtons' => false,
          'disabledVoteText'    => $votingOpen ? 'Ai votat deja' : 'Vot închis',
        ])

        @if(!$votingOpen)
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
  {{-- keep the same JS so vanish + actions work --}}
  <script>
    window.voteRoute   = "{{ route('concurs.vote') }}";
    window.csrfToken   = "{{ csrf_token() }}";
  </script>
  <script src="{{ asset('js/concurs.js') }}"></script>
  <script src="{{ asset('js/theme-like.js') }}"></script>
@endpush
