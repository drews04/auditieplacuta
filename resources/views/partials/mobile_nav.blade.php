{{-- resources/views/partials/mobile_nav.blade.php --}}
<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="apMobileNav" aria-labelledby="apMobileNavLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title ap-menu-title" id="apMobileNavLabel">Meniu</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Închide"></button>
  </div>

  <div class="offcanvas-body">
    <ul class="nav flex-column gap-2 fs-6" id="mobileNavList">
      @if(View::exists('partials.nav.mobile'))
        @include('partials.nav.mobile')
      @else
        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Acasă</a></li>
        <li class="nav-item">
          <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('concurs') ? route('concurs') : url('/concurs') }}">
            Concurs
          </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="{{ url('/muzica') }}">Muzică</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ url('/arena') }}">Arena</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ url('/magazin') }}">Magazin</a></li>
      @endif
    </ul>

    <div class="mt-3 border-top pt-3">
      @auth
        <div class="ap-greet">Salut, {{ auth()->user()->name }}</div>

        <a href="{{ route('logout') }}" class="nav-link logout-link text-danger fw-semibold"
           onclick="event.preventDefault(); document.getElementById('logout-form-main').submit();">
          Deconectează-te
        </a>
        <form id="logout-form-main" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      @else
        <button type="button"
                class="ap-btn-neon js-open-login"
                data-bs-dismiss="offcanvas"
                aria-controls="apMobileNav">
          Autentificare
        </button>
      @endauth
    </div>
  </div>
</div>
