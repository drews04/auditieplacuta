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
 



  {{-- ===== THEME PILL (today or next scheduled) ===== --}}
  @if($cycleSubmit && $cycleSubmit->theme_text)
    @php
      $parts = preg_split('/\s*—\s*/u', $cycleSubmit->theme_text, 2);
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? $cycleSubmit->theme_text);

      // normalize category label (no CSS changes)
      $catDisp = [
        'csd'     => 'CSD',
        'it'      => 'ITC',
        'itc'     => 'ITC',
        'artisti' => 'Artisti',
        'genuri'  => 'Genuri',
      ][strtolower($cat)] ?? strtoupper($cat);

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
            @if($cat !== '') <span class="ap-cat-badge">{{ $catDisp }}</span><span class="ap-dot">🎯</span> @endif
            <span class="ap-label">Tema:</span>
            <span class="ap-title">{{ $title }}</span>

            {{-- HEART: right next to the title (same placement as on /concurs) --}}
            @if($submitThemeId)
              <div class="dropdown d-inline-block theme-like-wrap ms-2">
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
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- 19:30–20:00 cooldown banner (today closed, voting soon) --}}
  @if(
    empty($preSubmit) &&
    $cycleSubmit &&
    now()->between(
      optional($cycleSubmit->submit_end_at)->timezone(config('app.timezone')),
      optional($cycleSubmit->vote_start_at)->timezone(config('app.timezone'))
    )
  )
    <div class="alert alert-warning text-center fw-semibold mb-4">
      Înscrierile s-au închise pentru azi. Votul începe la
      <strong>{{ optional($cycleSubmit->vote_start_at)->timezone(config('app.timezone'))->format('H:i') ?? '20:00' }}</strong>.
    </div>
  @endif

  {{-- ===== PRE-SUBMIT BANNER (winner picked; next cycle scheduled; before 00:00) ===== --}}
  @if(!empty($preSubmit) && $preSubmit)
    @php
      $opensAt = optional($submissionsOpensAt)->timezone(config('app.timezone'));
    @endphp
    <div class="alert alert-info text-center fw-semibold mb-4">
      Înscrierile pentru tema curentă se deschid la
      <strong>{{ $opensAt ? $opensAt->format('H:i') : '00:00' }}</strong>.
    </div>
  @endif

  {{-- ===== UPLOAD FORM (hidden during pre-submit; shows from 00:00) ===== --}}
  @auth
    @php
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
    @elseif(empty($preSubmit) || !$preSubmit)
      <div class="alert alert-dark mb-4">🕒 Înscrierile sunt închise sau ai încărcat deja o melodie.</div>
    @endif
  @else
    <div class="alert alert-dark mb-4">🔒 Autentifică-te pentru a-ți înscrie melodia.</div>
  @endauth

  {{-- ===== TODAY/NEXT SONG LIST (read-only during pre-submit) ===== --}}
  <div class="card border-0 shadow-sm mb-4 ap-neon">
    <div class="card-body">
      <div id="song-list">
        @include('partials.songs_list', [
          'songs'               => $songsSubmit,
          'userHasVotedToday'   => true,   // no voting on upload page
          'showVoteButtons'     => false,  // keep buttons hidden here
          'disabledVoteText'    => $votingOpensAt
                                    ? 'Votează (se activează la ' . $votingOpensAt->timezone(config('app.timezone'))->format('H:i') . ')'
                                    : 'Votează (se activează curând)',
          'hideVoteStatus'      => true,   // hide status column entirely
          'hideDisabledButtons' => true,   // render nothing on the right side
        ])
      </div>
    </div>
  </div>

</div>

{{-- YouTube Modal (same behavior as /concurs) --}}
<div class="modal fade" id="youtubeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header border-0">
        <h5 class="modal-title">Redă melodia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Închide"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="ratio ratio-16x9">
          <iframe id="ytFrame" src="" title="YouTube player" allow="autoplay; encrypted-media" allowfullscreen></iframe>
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
  {{-- IMPORTANT: prevent concurs.js from auto-fetching the list on this page --}}
  <script>window.skipInitialLoad = true;</script>

  {{-- same JS so AJAX upload + list refresh + YT modal behavior are identical --}}
  <script>
    window.songListRoute = "{{ route('concurs.songs.today') }}";
    window.uploadRoute   = "{{ route('concurs.upload') }}";
    window.voteRoute     = "{{ route('concurs.vote') }}"; // harmless here; used by shared JS
    window.csrfToken     = "{{ csrf_token() }}";
    window.routeThemesLikeToggle = "{{ route('themes.like.toggle') }}";
  </script>
  <script src="{{ asset('js/concurs.js') }}"></script>
  <script src="{{ asset('js/theme-like.js') }}"></script>

  {{-- YouTube modal wiring (same as hub) --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const ytFrame  = document.getElementById('ytFrame');
      const openLink = document.getElementById('ytOpenLink');
      const modalEl  = document.getElementById('youtubeModal');

      function ytId(url) {
        if (!url) return null;
        const m1 = url.match(/youtu\.be\/([0-9A-Za-z_-]{11})/); if (m1) return m1[1];
        const m2 = url.match(/(?:v=|\/embed\/|\/v\/)([0-9A-Za-z_-]{11})/); if (m2) return m2[1];
        const m3 = url.match(/([0-9A-Za-z_-]{11})/); return m3 ? m3[1] : null;
      }
      function toEmbed(url){ const id = ytId(url); return id ? `https://www.youtube.com/embed/${id}?autoplay=1&rel=0` : ''; }

      document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.play3d'); if (!btn) return;
        const url = btn.getAttribute('data-youtube-url') || '';
        const theEmbed = toEmbed(url);
        ytFrame.src = theEmbed || '';
        openLink.href = url || '#';
      });

      modalEl?.addEventListener('hidden.bs.modal', () => { ytFrame.src = ''; });
    });
  </script>
@endpush
