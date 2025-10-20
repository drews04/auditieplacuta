{{-- resources/views/concurs/upload.blade.php --}}
@extends('layouts.app')

@push('styles')
  {{-- SAME css includes so styling matches hub --}}
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
  {{-- Heart/likes styles (same as /concurs) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/theme-like.css') }}?v={{ filemtime(public_path('assets/css/theme-like.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-override.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-mobile.css') }}?v={{ time() }}">
@endpush

{{-- main site styles (same include as /concurs) --}}
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

@section('title', 'Încarcă — Concurs')
@section('body_class', 'page-concurs page-neon')

@section('content')
<div class="container py-5">

  {{-- ===== System toast (auto-hide in 5s) ===== --}}
  @if(session('error'))
    <div id="ap-toast" class="alert alert-danger text-center fw-semibold mb-4">
      {{ session('error') }}
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const t = document.getElementById('ap-toast');
        if (t) { setTimeout(()=>t.classList.add('fade'),4500); setTimeout(()=>t.remove(),5000); }
      });
    </script>
  @endif
  @if(session('status'))
    <div id="ap-toast-ok" class="alert alert-success text-center fw-semibold mb-4">
      {{ session('status') }}
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const t = document.getElementById('ap-toast-ok');
        if (t) { setTimeout(()=>t.classList.add('fade'),4500); setTimeout(()=>t.remove(),5000); }
      });
    </script>
  @endif

  <h1 class="mb-3 text-center fw-bold" style="letter-spacing:1px;">⬆️ Încarcă melodia pentru azi</h1>
  <p class="text-center mb-4">Adaugă linkul YouTube și intră în concursul de azi.</p>

  {{-- ===== THEME PILL (today or next scheduled) ===== --}}
  @if($cycleSubmit && $cycleSubmit->theme_text)
    @php
      // Parse "Category - Theme Name" from theme_text (hyphen, not em dash)
      $parts = preg_split('/\s*[-—]\s*/u', (string)$cycleSubmit->theme_text, 2);
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? ($cycleSubmit->theme_text ?? ''));

      // Normalize badge label
      $catDisp = [
        'csd' => 'CSD', 'it' => 'ITC', 'itc' => 'ITC',
        'artisti' => 'Artiști', 'genuri' => 'Genuri',
      ][mb_strtolower($cat)] ?? mb_strtoupper($cat);

      // Theme likes (now queried from database)
      $submitThemeId    = $submitTheme->id ?? 0;
      $submitLikesCount = $submitTheme->likes_count ?? 0;
      $submitLiked      = $submitTheme->liked_by_me ?? false;
    @endphp
    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <div class="ap-theme-row">
          <div class="ap-left">
            @if($cat !== '') <span class="ap-cat-badge">{{ $catDisp }}</span><span class="ap-dot">🎯</span> @endif
            <span class="ap-label">Tema:</span>
            <span class="ap-title">{{ $title }}</span>

            {{-- HEART button --}}
            @if($submitThemeId)
              <div class="dropdown d-inline-block theme-like-wrap ms-2">
                <button type="button"
                        class="btn btn-sm theme-like {{ $submitLiked ? 'is-liked' : '' }}"
                        data-likeable-type="contest"
                        data-likeable-id="{{ $submitThemeId }}"
                        data-liked="{{ $submitLiked ? 1 : 0 }}"
                        data-count="{{ (int)$submitLikesCount }}"
                        @guest data-auth="0" @endguest>
                  <i class="heart-icon"></i>
                  <span class="like-count">{{ (int)$submitLikesCount }}</span>
                </button>
                <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
                  @forelse(($submitTheme->likes ?? []) as $like)
                    <li class="small text-muted">❤️ {{ $like->user->name }}</li>
                  @empty
                    <li class="small text-muted">Niciun like încă</li>
                  @endforelse
                </ul>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ===== UPLOAD FORM / CLOSE STATES ===== --}}
  @auth
    @php $allowUploadNow = $submissionsOpen && !$userHasUploadedToday; @endphp
    @if($allowUploadNow)
      <div class="card border-0 shadow-sm mb-4 ap-neon">
        <div class="card-body">
          <h5 class="card-title mb-3">📤 Înscrie-ți melodia (YouTube URL)</h5>
          <form id="song-upload-form" action="{{ route('concurs.upload') }}" method="POST">
            @csrf
            <div class="row g-2">
              <div class="col-md-9">
                <input type="url" name="youtube_url" id="youtube_url"
                       class="form-control" placeholder="https://www.youtube.com/watch?v=…" required>
              </div>
              <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-success">Trimite</button>
              </div>
            </div>
            <small class="text-muted d-block mt-2">
              Înscrierile se închid la
              <strong>{{ $cycleSubmit && $cycleSubmit->submit_end_at ? \Carbon\Carbon::parse($cycleSubmit->submit_end_at)->timezone(config('app.timezone'))->format('H:i') : '20:00' }}</strong>.
            </small>
          </form>
        </div>
      </div>
    @else
      <div class="alert alert-dark mb-4">🕒 Înscrierile sunt închise sau ai încărcat deja o melodie.</div>
    @endif
  @else
    <div class="alert alert-dark mb-4">🔒 Autentifică-te pentru a-ți înscrie melodia.</div>
  @endauth

  {{-- SONG LIST --}}
  <div class="card border-0 shadow-sm mb-4 ap-neon">
    <div class="card-body">
      <div id="song-list">
        @include('partials.songs_list', [
          'songs' => $songsSubmit,
          'userHasVotedToday'=>true,
          'showVoteButtons'=>false,
          'hideVoteStatus'=>true,
          'hideDisabledButtons'=>true,
        ])
      </div>
    </div>
  </div>
</div>

{{-- YouTube modal --}}
<div class="modal fade" id="youtubeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header border-0">
        <h5 class="modal-title">Redă melodia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="ratio ratio-16x9">
          <iframe id="ytFrame" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
      <div class="modal-footer border-0">
        <a id="ytOpenLink" href="#" target="_blank" rel="noopener" class="btn btn-outline-info">Vezi pe YouTube</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script>
    window.skipInitialLoad = true;
    // window.songListRoute not needed - JS has fallback
    window.uploadRoute   = "{{ route('concurs.upload') }}";
    window.voteRoute     = "{{ route('concurs.vote') }}";
    window.csrfToken     = "{{ csrf_token() }}";
    // endpoint used by theme-like.js
    window.routeThemesLikeToggle = "{{ route('themes.like.toggle') }}";
  </script>

  {{-- core page helpers first --}}
  <script src="{{ asset('js/concurs.js') }}" defer></script>
  {{-- heart toggle (guards against double-init) --}}
  <script src="{{ asset('js/theme-like.js') }}?v={{ filemtime(public_path('js/theme-like.js')) }}" defer></script>
@endpush
