<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>@yield('title', 'Auditie Placuta')</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/off-canvas.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/ico-moon-fonts.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/sc-spacing.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}" />
  <link rel="stylesheet" href="{{ asset('logo.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/winner.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/leaderboard.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/rotating-banner.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/theme-like.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/tema-lunii.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/pagination-neon.css') }}">

  {{-- Page-level style injections --}}
  @stack('styles')

  <!-- Tiny layout skeleton -->
  <style>
    html, body { height: 100%; margin: 0; }
    body.site { min-height: 100%; display: flex; flex-direction: column; }
    main.site-main { flex: 1 0 auto; }
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

      // YouTube modal wiring (kept from your push-block; in layout now)
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
</body>
</html>
