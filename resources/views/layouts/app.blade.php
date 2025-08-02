<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Auditie Placuta')</title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-..."
    crossorigin="anonymous"
  />

  <!-- Your other CSS -->
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
</head>
<body>

  @include('partials.header')

  <main>
    @yield('content')
  </main>

  @include('partials.footer')

  <!-- Bootstrap JS Bundle -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-..."
    crossorigin="anonymous"
  ></script>

  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins.js') }}"></script>

  @stack('scripts')

  <!-- Login Modal -->
  <div
    class="modal fade"
    id="loginModal"
    tabindex="-1"
    aria-labelledby="loginModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="loginModalLabel">Autentificare</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <div class="modal-body">

          {{-- ✅ Success after registration --}}
            @if(session('success'))
              <div class="custom-alert-success">
                <div class="icon-wrapper">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708L6.707 11.5 2.146 6.854a.5.5 0 1 1 .708-.708L6.707 10.293l6.439-6.439a.5.5 0 0 1 .708 0z"/>
                  </svg>
                </div>
                <div>{{ session('success') }}</div>
              </div>
            @endif

          {{-- ❌ Login error --}}
          @if ($errors->has('email'))
            <div class="alert alert-danger d-flex align-items-center" role="alert">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-exclamation-circle me-2" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0-1A6 6 0 1 1 8 2a6 6 0 0 1 0 12z"/>
                <path d="M7.002 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0zm.1-5.995a.905.905 0 0 1 1.8 0l-.35 3.5a.552.552 0 0 1-1.1 0l-.35-3.5z"/>
              </svg>
              <div>{{ $errors->first('email') }}</div>
            </div>
          @endif

          <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="Email"
                required
              >
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Parola</label>
              <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Parola"
                required
              >
            </div>
            <div class="mb-3 text-end">
              <a href="{{ route('password.request') }}" target="_blank" rel="noopener noreferrer">
                Ai uitat parola?
              </a>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">
                Autentifică-te
              </button>
            </div>
          </form>

          <hr class="my-4">
          <p class="text-center">
            Nu ai cont?
            <a href="{{ route('register') }}" target="_blank" rel="noopener noreferrer">Creează unul aici</a>
          </p>
        </div>

      </div>
    </div>
  </div>
  <!-- /Login Modal -->

  {{-- ✅ Auto open modal if success or login error --}}
  @if(session('show_login_modal') || $errors->has('email'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
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
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
  </script>

</body>
</html>
