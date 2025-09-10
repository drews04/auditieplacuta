<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Auditie Placuta')</title>

  @php
    // Helper: build asset URL with cache-busting based on filemtime, safely.
    $cssv = function (string $rel) {
        $full = public_path($rel);
        $ver  = file_exists($full) ? filemtime($full) : time();
        return asset($rel) . '?v=' . $ver;
    };
  @endphp

  <!-- Bootstrap (core) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />

  <!-- ===== Global CSS (always) ===== -->
  <link rel="stylesheet" href="{{ $cssv('assets/css/ico-moon-fonts.css') }}" />
  <link rel="stylesheet" href="{{ $cssv('assets/css/all.min.css') }}" />
  <link rel="stylesheet" href="{{ $cssv('assets/css/nav-new.css') }}" />      {{-- header/nav tweaks --}}
  <link rel="stylesheet" href="{{ $cssv('assets/css/style.css') }}" />
  <link rel="stylesheet" href="{{ $cssv('assets/css/responsive.css') }}" />
  <link rel="stylesheet" href="{{ $cssv('logo.css') }}" />

  <!-- Our tiny core (last so it normalizes sizes & neon look) -->
  <link rel="stylesheet" href="{{ $cssv('assets/css/ap-core.css') }}" />

  {{-- ===== Route-based CSS (automatic) ===== --}}
  @php
    $isHome    = request()->is('/');
    $isConcurs = request()->is('concurs*');
    $isForum   = request()->is('forum*');
    $isMuzica  = request()->is('muzica*');
    $isArena   = request()->is('arena*');
    $isMagazin = request()->is('magazin*');
    $isRegul   = request()->is('regulament*') || request()->is('regulament');
    $isAbout   = request()->is('despre*') || request()->is('about*');
    $isEvents  = request()->is('evenimente*') || request()->is('events*');
  @endphp

  {{-- Home --}}
  @if($isHome)
    <link rel="stylesheet" href="{{ $cssv('assets/css/rotating-banner.css') }}">
    @if(file_exists(public_path('assets/css/slick.css')))
      <link rel="stylesheet" href="{{ $cssv('assets/css/slick.css') }}">
    @endif
    @if(file_exists(public_path('assets/css/slick-theme.min.css')))
      <link rel="stylesheet" href="{{ $cssv('assets/css/slick-theme.min.css') }}">
    @endif
  @endif

  {{-- Concurs --}}
  @if($isConcurs)
    <link rel="stylesheet" href="{{ $cssv('assets/css/tema-lunii.css') }}">
    <link rel="stylesheet" href="{{ $cssv('assets/css/theme-like.css') }}">
    <link rel="stylesheet" href="{{ $cssv('assets/css/concurs.css') }}">
    <link rel="stylesheet" href="{{ $cssv('assets/css/winner.css') }}">
    <link rel="stylesheet" href="{{ $cssv('assets/css/leaderboard.css') }}">
    <link rel="stylesheet" href="{{ $cssv('assets/css/pagination-neon.css') }}">
    @if(file_exists(public_path('assets/css/vote-btn.css')))
      <link rel="stylesheet" href="{{ $cssv('assets/css/vote-btn.css') }}">
    @endif
    @if(file_exists(public_path('assets/css/alege-tema.css')))
      <link rel="stylesheet" href="{{ $cssv('assets/css/alege-tema.css') }}">
    @endif
  @endif

  {{-- Forum --}}
  @if($isForum)
    @if(file_exists(public_path('assets/css/forum.css')))
      <link rel="stylesheet" href="{{ $cssv('assets/css/forum.css') }}">
    @endif
    <link rel="stylesheet" href="{{ $cssv('assets/css/pagination-neon.css') }}">
  @endif

  {{-- Muzica / Arena / Magazin (add only if you truly need per-page CSS) --}}
  @if($isMuzica)
    {{-- add per-page CSS here if muzica has its own file --}}
  @endif

  @if($isArena)
    {{-- add per-page CSS here if arena has its own file --}}
  @endif

  @if($isMagazin)
    {{-- add per-page CSS here if magazin has its own file --}}
  @endif

  {{-- Static pages (about/regulament/events) --}}
  @if($isRegul && file_exists(public_path('assets/css/regulament.css')))
    <link rel="stylesheet" href="{{ $cssv('assets/css/regulament.css') }}">
  @endif
  @if($isAbout && file_exists(public_path('assets/css/about.css')))
    <link rel="stylesheet" href="{{ $cssv('assets/css/about.css') }}">
  @endif
  @if($isEvents && file_exists(public_path('assets/css/events.css')))
    <link rel="stylesheet" href="{{ $cssv('assets/css/events.css') }}">
  @endif

  {{-- Page-level CSS (for one-off needs: magnific-popup, owl.carousel, etc.) --}}
  @stack('styles')

  <!-- Tiny layout skeleton -->
  <style>
    html, body { height: 100%; margin: 0; }
    body.site { min-height: 100%; display: flex; flex-direction: column; }
    main.site-main { flex: 1 0 auto; }

    /* smooth fade-out for flash alerts (used globally below) */
    .alert.fade-out{
      opacity: 0;
      transform: translateY(-4px);
      transition: opacity .6s ease, transform .6s ease;
    }
  </style>
</head>

<body class="site @yield('body_class')">
  @include('partials.header')

  {{-- Pages decide if they want container or full-width --}}
  <main class="site-main">
    @yield('content')
  </main>

  @include('partials.footer')

  <!-- JS: load once, at the bottom -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins.js') }}"></script>

  {{-- Page-level scripts --}}
  @stack('scripts')

  {{-- Login Modal (unchanged) --}}
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="loginModalLabel">Autentificare</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if(session('success'))
            <div class="custom-alert-success">
              <div class="checkmark-icon">✅</div>
              <div class="success-text">{{ str_replace(['✓','✅','☑','☐'], '', session('success')) }}</div>
            </div>
          @endif

          @if ($errors->has('email'))
            <div class="alert alert-danger d-flex align-items-center" role="alert">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-exclamation-circle me-2" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14z"/>
                <path d="M7.002 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0zm.1-5.995a.905.905 0 0 1 1.8 0l-.35 3.5a.552.552 0 0 1-1.1 0l-.35-3.5z"/>
              </svg>
              <div>{{ $errors->first('email') }}</div>
            </div>
          @endif

          <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Parola</label>
              <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 text-end">
              <a href="{{ route('password.request') }}" target="_blank" rel="noopener noreferrer">Ai uitat parola?</a>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Autentifică-te</button>
            </div>
          </form>

          <hr class="my-4">
          <p class="text-center">Nu ai cont? <a href="{{ route('register') }}" target="_blank" rel="noopener noreferrer">Creează unul aici</a></p>
        </div>
      </div>
    </div>
  </div>

  @if(session('show_login_modal') || $errors->has('email'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('loginModal')).show();
      });
    </script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const userName = document.getElementById('user-name');
      const dropdown = document.getElementById('user-dropdown');
      if (userName && dropdown) {
        userName.addEventListener('click', function (e) {
          e.stopPropagation();
          dropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function (e) {
          if (!dropdown.contains(e.target)) dropdown.classList.add('hidden');
        });
      }

      // YouTube modal wiring (Bootstrap modal)
      const youtubeModal = document.getElementById('youtubeModal');
      const youtubeIframe = document.getElementById('youtubeIframe');
      if (youtubeModal && youtubeIframe) {
        youtubeModal.addEventListener('show.bs.modal', function (event) {
          const btn = event.relatedTarget;
          const id = btn?.getAttribute('data-video-id');
          youtubeIframe.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1';
        });
        youtubeModal.addEventListener('hidden.bs.modal', function () {
          youtubeIframe.src = '';
        });
      }
    });
  </script>

  <!-- GLOBAL: auto-dismiss all success alerts after 5s. -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const candidates = document.querySelectorAll(
        '.alert-success, .alert[data-auto-dismiss="true"]'
      );
      candidates.forEach(el => {
        const msAttr = el.getAttribute('data-dismiss-ms');
        const delay = Number.isFinite(parseInt(msAttr, 10)) ? parseInt(msAttr, 10) : 5000;
        setTimeout(() => {
          el.classList.add('fade-out');
          el.addEventListener('transitionend', () => el.remove());
        }, delay);
      });
    });
  </script>

@auth
  @php $isForum = request()->is('forum*'); @endphp
  @if(!$isForum)
    <div id="reply-pill-root" class="reply-pill-root" aria-live="polite"></div>
    <link rel="stylesheet" href="{{ asset('assets/css/pill-alert.css') }}">
    <script defer src="{{ asset('js/pill-alert.js') }}?v={{ file_exists(public_path('js/pill-alert.js')) ? filemtime(public_path('js/pill-alert.js')) : time() }}"></script>
  @endif
@endauth
</body>
</html>
