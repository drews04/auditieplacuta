@extends('layouts.app')

@push('styles')
  {{-- SAME css includes so styling matches hub --}}
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
@endpush
{{-- main site styles (same include as /concurs) --}}
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

@section('title', 'Încarcă — Concurs')
@section('body_class', 'page-concurs')

@section('content')
<div class="container py-5">

  {{-- ===== System toast (auto-hide in 5s) ===== --}}
  @if(session('error'))
    <div id="ap-toast" class="alert alert-danger text-center fw-semibold mb-4">
      {{ session('error') }}
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const t = document.getElementById('ap-toast');
        if (!t) return;
        setTimeout(()=>{ t.classList.add('fade'); }, 4500);
        setTimeout(()=>{ t.remove(); }, 5000);
      });
    </script>
  @endif
  @if(session('status'))
    <div id="ap-toast-ok" class="alert alert-success text-center fw-semibold mb-4">
      {{ session('status') }}
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const t = document.getElementById('ap-toast-ok');
        if (!t) return;
        setTimeout(()=>{ t.classList.add('fade'); }, 4500);
        setTimeout(()=>{ t.remove(); }, 5000);
      });
    </script>
  @endif

  <h1 class="mb-3 text-center" style="font-weight:800; letter-spacing:1px;">⬆️ Încarcă melodia pentru azi</h1>
  <p class="text-center mb-4">Adaugă linkul YouTube și intră în concursul de azi.</p>

  {{-- ===== THEME PILL (today) ===== --}}
  @if($cycleSubmit && $cycleSubmit->theme_text)
    @php
      $parts = preg_split('/\s*—\s*/u', $cycleSubmit->theme_text, 2);
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? $cycleSubmit->theme_text);

      $submitTheme      = $submitTheme ?? ($cycleSubmit->contestTheme ?? null);
      $submitThemeId    = $submitTheme->id ?? ($cycleSubmit->contest_theme_id ?? 0);
      $submitLikesCount = $submitTheme->likes_count ?? ($submitTheme ? $submitTheme->likes->count() : 0);
      $submitLiked      = auth()->check() && $submitTheme
                          ? $submitTheme->likes->where('user_id', auth()->id())->isNotEmpty()
                          : false;
    @endphp
    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <div class="ap-theme-row">
          <div class="ap-left">
            @if($cat !== '') <span class="ap-cat-badge">{{ $cat }}</span><span class="ap-dot">🎯</span> @endif
            <span class="ap-label">Tema:</span>
            <span class="ap-title">{{ $title }}</span>
          </div>

          <div class="dropdown d-inline-block theme-like-wrap">
            <button type="button"
                    class="btn btn-sm theme-like"
                    data-likeable-type="contest"
                    data-likeable-id="{{ $submitThemeId }}"
                    data-liked="{{ $submitLiked ? 1 : 0 }}"
                    data-count="{{ $submitLikesCount }}"
                    @guest data-auth="0" @endguest
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
              <i class="heart-icon"></i>
              <span class="like-count">{{ $submitLikesCount }}</span>
            </button>
            <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
              @forelse($submitTheme->likes ?? [] as $like)
                <li class="small text-muted">❤️ {{ $like->user->name }}</li>
              @empty
                <li class="small text-muted">Niciun like încă</li>
              @endforelse
            </ul>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ===== UPLOAD FORM (identical classes/structure) ===== --}}
  @auth
    @php
      // identical allow rule as hub
      $allowUploadNow = $submissionsOpen && !$userHasUploadedToday;
    @endphp

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
              <strong>{{ optional($cycleSubmit?->submit_end_at)->timezone(config('app.timezone'))->format('H:i') }}</strong>.
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

  {{-- ===== TODAY'S SONG LIST (under upload) ===== --}}
  @if($songsSubmit->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        @include('partials.songs_list', [
          'songs'              => $songsSubmit,
          'userHasVotedToday'  => true,   // no voting on upload page
          'showVoteButtons'    => false,  // keep buttons hidden here
          'disabledVoteText'   => $votingOpensAt
                                  ? 'Votează (se activează la ' . $votingOpensAt->timezone(config('app.timezone'))->format('H:i') . ')'
                                  : 'Votează (se activează curând)',
        ])
      </div>
    </div>
  @endif

</div>
@endsection

@push('scripts')
  {{-- same JS so AJAX upload + YT modal behavior are identical --}}
  <script>
    window.songListRoute = "{{ route('concurs.songs.today') }}";
    window.uploadRoute   = "{{ route('concurs.upload') }}";
    window.csrfToken     = "{{ csrf_token() }}";
  </script>
  <script src="{{ asset('js/concurs.js') }}"></script>
  <script src="{{ asset('js/theme-like.js') }}"></script>
@endpush
