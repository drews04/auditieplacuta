@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
@section('title', 'Concursul de Azi')
@section('body_class', 'page-concurs')

@section('content')

{{-- ====================================================================== --}}
{{-- Admin TEST MODE toggles (force weekday bypass for weekend testing)     --}}
{{-- ====================================================================== --}}
@auth
  @if((auth()->user()->is_admin ?? false) || auth()->id() === 1)
    {{-- status flash (so you see ON/OFF worked) --}}
    @if(session('status'))
      <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    {{-- yellow banner when test mode is active --}}
    @if(session()->get('ap_force_weekday') === true)
      <div class="alert alert-warning mb-3">
        🔧 <strong>TEST MODE</strong> — Weekday is forced for this session.
      </div>
    @endif

    <div class="d-flex gap-2 mb-4">
      <a href="{{ route('admin.test.forceWeekdayOn') }}" class="btn btn-warning btn-sm">
        🔧 Force Weekday (ON)
      </a>
      <a href="{{ route('admin.test.forceWeekdayOff') }}" class="btn btn-secondary btn-sm">
        ❌ Force Weekday (OFF)
      </a>
    </div>
  @endif
@endauth

@if(session('status'))
  <div class="alert alert-success mb-3">
    {{ session('status') }}
  </div>
@endif

{{-- Admin-only Declare Winner button (runs the cron logic + flags modal) --}}
@auth
  @if((auth()->user()->is_admin ?? false) || auth()->id() === 1)
    <div class="mb-3">
      <a href="{{ route('admin.concurs.declare-now') }}"
         class="btn btn-danger"
         onclick="return confirm('Declare today\'s winner NOW? This will close voting immediately.');">
        Declare Winner Now
      </a>
    </div>
  @endif
@endauth

@php
    // winner?
    $isWinner = auth()->check() && $todayWinner && auth()->id() === $todayWinner->user_id;

    // tomorrow theme exists?
    $tomorrowPicked = isset($tomorrowTheme) && $tomorrowTheme;

    // ✅ Correct rule:
    // Show upload when we are within TODAY's submission window,
    // OR when tomorrow's theme exists and we’re prepping tomorrow.
    $canUpload = ($submissionsOpen || ($uploadForTomorrow ?? false));
@endphp


<div class="container py-5">

  {{-- Admin-only Start Concurs Test button --}}
  @auth
    @if(auth()->user()->is_admin ?? auth()->id() === 1)
      <a href="{{ route('admin.concurs.start') }}"
         class="btn btn-danger"
         style="position:absolute; right:20px; top:120px; z-index:9999;"
         onclick="return confirm('Pornești testul concursului și resetezi datele?')">
        🚀 Start Concurs Test
      </a>
    @endif
  @endauth

  {{-- success popup after choosing theme --}}
  @if(session('tema_success'))
    <div id="temaChosenPopup" class="ap-popup ap-popup--success" role="dialog" aria-live="assertive">
      <div class="ap-popup__content">
        <div class="ap-popup__icon">✅</div>
        <div class="ap-popup__title">Tema a fost aleasă cu succes</div>
        <div class="ap-popup__text">Mulțumim! Tema pentru mâine este setată.</div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const p = document.getElementById('temaChosenPopup');
        if (!p) return;
        p.classList.add('is-visible');
        setTimeout(() => p.classList.remove('is-visible'), 3000);
        setTimeout(() => p.remove(), 3400);
      });
    </script>
  @endif

  {{-- title + winner action --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
    <h1 class="mb-2 mb-sm-0 text-center w-100" style="font-weight:800; letter-spacing:1px;">
      🎧 CONCURSUL DE AZI
    </h1>
    @if($isWinner && !$tomorrowPicked)
      <a href="{{ route('concurs.alege-tema.create') }}" class="btn btn-neon">🎯 Alege tema pentru mâine</a>
    @endif
  </div>
  <p class="text-center mb-4">Ascultă melodiile participante și votează-ți favorita!</p>

  @if($todayWinner)
  <div class="ap-winner-banner">
    <div class="ap-winner-inner">
      <span class="cup">🏆</span>
      <span class="label">Câștigător azi:</span>
      <span class="song">{{ $todayWinner->song->title }}</span>
      <span class="by">de</span>
      <span class="user">
        {{ $todayWinner->user->name ?? ($todayWinner->song->user->name ?? 'necunoscut') }}
      </span>
    </div>
  </div>
  @endif

  {{-- tomorrow theme row (simple, no box) --}}
  @if($tomorrowPicked)
    <div class="ap-theme-row">
      <div class="ap-left">
        <span class="ap-cat-badge">{{ $tomorrowTheme->category_code }}</span>
        <span class="ap-dot">🎯</span>
        <span class="ap-label">Tema pentru mâine:</span>
        <span class="ap-title">{{ $tomorrowTheme->title }}</span>
      </div>
      <div class="ap-right">
        aleasă de <strong>{{ $tomorrowTheme->chooser->name ?? 'necunoscut' }}</strong>
      </div>
    </div>
  @endif

  {{-- weekend info --}}
  @unless($isWeekday)
    <div class="alert alert-info mb-3">
      🗓️ Nu se ține concurs în weekend (Luni–Vineri).
    </div>
  @endunless

  {{-- upload form --}}
@auth
@php
  $allowUploadNow = ($submissionsOpen || ($uploadForTomorrow ?? false))
                    && ( !$userHasUploadedToday || ($uploadForTomorrow ?? false) );
@endphp

  @if($allowUploadNow)
    <div class="card border-0 shadow-sm mb-4">
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

          @if(!empty($uploadForTomorrow) && $uploadForTomorrow)
            <small class="d-block mt-2 text-success">
              🚀 Acestă melodie va intra în <strong>concursul de mâine</strong>
              (tema: <strong>{{ $tomorrowTheme->title }}</strong>).
            </small>
          @else
            <small class="text-muted d-block mt-2">
              Înscrierile sunt deschise până la <strong>19:30</strong>.
            </small>
          @endif
        </form>
      </div>
    </div>
  @endif
@else
  <div class="alert alert-dark mb-4">🔒 Autentifică-te pentru a-ți înscrie melodia.</div>
@endauth

  {{-- songs list --}}
  <div id="song-list">
    @if($songs->isEmpty())
      <div class="alert alert-info mb-0">Nu au fost încă adăugate melodii azi.</div>
    @else
      @include('partials.songs_list', [
        'songs' => $songs,
        'userHasVotedToday' => $userHasVotedToday
      ])
    @endif
  </div>
</div>

{{-- YouTube Modal --}}
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

{{-- winner reminder overlay --}}
@if( ($isWinner && !$tomorrowPicked) || $showWinnerPopup || session('ap_show_theme_modal') === true )

  <div id="winnerReminder" style="display:none;">
    <canvas id="confetti-bg"></canvas>
    <div class="winner-box">
      <h3 class="w-title">Felicitări, {{ Auth::user()->name ?? 'campion' }}, ai câștigat!</h3>
      <div class="w-sub">Alege tema pentru concursul de mâine</div>
      <p class="w-lead">Setează tema până la ora 21:00. Dacă nu alegi, vom porni fallback-ul automat.</p>
      <div class="w-actions">
        <a href="{{ route('concurs.alege-tema.create') }}" id="btn-open-theme" class="btn-neon">Alege tema</a>
        <button id="btn-close-winner" class="btn-ghost" type="button">Închide</button>
      </div>
      <div class="w-pill mt-3">
        <span class="me-1">🗓</span>
        <span id="winner-deadline">Până la 21:00, azi</span>
      </div>
    </div>
  </div>

  @if($isWinner && !$tomorrowPicked)
    <button id="btn-winner-reopen"
            onclick="window.location='{{ route('concurs.alege-tema.create') }}'"
            class="btn btn-neon"
            style="display:none; position:fixed; right:16px; bottom:16px; z-index:2100;">
      Alege tema
    </button>
  @endif
@endif
@endsection

@push('scripts')
<script>
  const songListRoute = "{{ route('concurs.songs.today') }}";
  const uploadRoute   = "{{ route('concurs.upload') }}";
  const voteRoute     = "{{ route('vote.song', ['songId' => ':songId']) }}";
  const csrfToken     = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/concurs.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js" defer></script>

{{-- YouTube modal wiring --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const ytFrame = document.getElementById('ytFrame');
  const openLink = document.getElementById('ytOpenLink');
  const modalEl = document.getElementById('youtubeModal');

  function ytId(url) {
    if (!url) return null;
    const m1 = url.match(/youtu\.be\/([0-9A-Za-z_-]{11})/);
    if (m1) return m1[1];
    const m2 = url.match(/(?:v=|\/embed\/|\/v\/)([0-9A-Za-z_-]{11})/);
    if (m2) return m2[1];
    const m3 = url.match(/([0-9A-Za-z_-]{11})/);
    return m3 ? m3[1] : null;
  }

  function toEmbed(url) {
    const id = ytId(url);
    return id ? `https://www.youtube.com/embed/${id}?autoplay=1&rel=0` : '';
  }

  // click handler for any .play3d button (comes from partial)
  document.body.addEventListener('click', function (e) {
    const btn = e.target.closest('.play3d');
    if (!btn) return;

    const url = btn.getAttribute('data-youtube-url') || '';
    const embed = toEmbed(url);

    if (embed) {
      ytFrame.src = embed;
      openLink.href = url;
    } else {
      ytFrame.src = '';
      openLink.href = url || '#';
    }
  });

  // clear video when modal hides (stop audio)
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function () {
      ytFrame.src = '';
    });
  }
});
</script>

@if($showWinnerPopup)
<script>
document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('winnerReminder');
  if (!overlay) return;

  function boom(){
    if (typeof confetti !== "function") return;
    confetti({particleCount:300, spread:120, startVelocity:50, gravity:0.9, ticks:200, origin:{ y:0.6 }, zIndex:3000});
  }

  let hideTimer = null;
  function showReminder(){
    overlay.classList.remove('d-none');
    overlay.style.display = 'block';
    boom();
    clearTimeout(hideTimer);
    hideTimer = setTimeout(hideReminder, 10000);
  }
  function hideReminder(){ overlay.classList.add('d-none'); overlay.style.display='none'; }

  const triggerTimes = ['20:00','20:10','20:20','20:30','20:40'];
  function parseToday(hhmm){ const [h,m] = hhmm.split(':').map(Number); const d = new Date(); d.setHours(h,m,0,0); return d; }
  const triggers = triggerTimes.map(parseToday);
  const endWindow = parseToday('20:50');

  const now = new Date();
  if (now >= triggers[0] && now < endWindow) showReminder();
  triggers.forEach(t => {
    const delay = t.getTime() - now.getTime();
    if (delay > 0) setTimeout(showReminder, delay);
  });

  document.getElementById('btn-close-winner')?.addEventListener('click', hideReminder);
});
</script>
@endif

{{-- Auto-open winner overlay immediately when controller asked for it --}}
@if (session('ap_show_theme_modal') === true)
<script>
document.addEventListener('DOMContentLoaded', function () {
  var overlay = document.getElementById('winnerReminder');
  if (overlay) {
    overlay.classList.remove('d-none');
    overlay.style.display = 'block';
    try { if (typeof confetti === 'function') confetti({ particleCount: 250, spread: 100 }); } catch(e){}
  } else {
    // Fallback: if overlay not present, go straight to theme picker
    window.location.href = "{{ route('concurs.alege-tema.create') }}";
  }
});
</script>
@endif
@endpush
