{{-- resources/views/partials/header.blade.php --}}

@php
  // Optional debug: /?new=1 forces the badge
  $forceNew = request('new') === '1';

  // Latest event id (cache first, DB fallback so it works even if CACHE_DRIVER=array)
  $latestEventId = cache()->get('events_latest_id') ?: \App\Models\Events\Event::max('id');

  // What the user has seen (session OR cookie; cookie works for guests too)
  $seenId = session('events_last_seen_id') ?? request()->cookie('events_last_seen_id');

  $hasNewEvents = $forceNew || ($latestEventId && (!$seenId || $latestEventId > (int) $seenId));
@endphp

@if(request('dbg')==='1')
  <!-- events_latest_id={{ $latestEventId ?? 'null' }}, events_last_seen_id={{ $seenId ?? 'null' }}, hasNewEvents={{ $hasNewEvents ? '1':'0' }} -->
@endif

<link rel="stylesheet" href="{{ asset('assets/css/nav-new.css') }}?v={{ filemtime(public_path('assets/css/nav-new.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/user-dropdown.css') }}?v={{ filemtime(public_path('assets/css/user-dropdown.css')) }}">

<header id="gamfi-header" class="gamfi-header-section transparent-header">
  <div class="menu-area menu-sticky">
    <div class="container">
      <div class="heaader-inner-area d-flex align-items-center justify-content-between">

        {{-- LEFT: Logo --}}
        <div class="gamfi-logo-area d-flex align-items-center">
          <div class="logo">
            <a href="{{ route('home') }}">
              <img src="{{ asset('assets/images/logo.png') }}" alt="logo">
            </a>
          </div>
        </div>

        {{-- CENTER: Desktop Menu (unchanged) --}}
        <div class="header-menu">
          <ul class="nav-menu d-flex align-items-center">
            {{-- Acasa --}}
            <li class="position-relative">
              <a href="{{ route('home') }}" class="nav-new-anchor"
                 style="--new-top:-10px; --new-right:-22px;">
                Acasa
                @if($hasNewEvents)
                  <span class="nav-new-badge">NEW</span>
                @endif
              </a>
              <ul class="sub-menu">
                <li class="position-relative">
                  <a class="dropdown-item nav-new-anchor pe-4" href="{{ route('events.index') }}"
                     style="--new-top:-6px; --new-right:-14px;">
                    Evenimente
                    @if($hasNewEvents)
                      <span class="nav-new-badge">NEW</span>
                    @endif
                  </a>
                </li>
                <li><a href="{{ route('forum.home') }}">Forum</a></li>
                <li><a href="{{ route('about') }}">Despre noi</a></li>
              </ul>
            </li>

            {{-- Concurs --}}
            <li>
              <a href="{{ route('concurs') }}">Concurs</a>
              <ul class="sub-menu">
                <li><a href="{{ route('leaderboard.monthly') }}">Clasament</a></li>
                <li><a href="{{ route('winners.index') }}">🎖️ Melodii câștigătoare</a></li>
                <li><a href="{{ route('concurs') }}">Rezultate (Arhivă)</a></li>
                <li><a href="{{ route('concurs.arhiva-teme') }}">Arhivă teme</a></li>
                <li><a href="{{ route('regulament') }}">Regulament</a></li>
              </ul>
            </li>

            {{-- Muzica --}}
            <li>
              <a href="{{ route('muzica') }}">Muzică</a>
              <ul class="sub-menu">
                <li><a href="{{ route('releases.index') }}" class="dropdown-item">Noutăți</a></li>
                <li><a href="{{ route('muzica.artisti') }}">Artiști</a></li>
                <li><a href="{{ route('muzica.genuri') }}">Genuri muzicale</a></li>
                <li><a href="{{ route('muzica.playlists') }}">Playlists</a></li>
              </ul>
            </li>

            {{-- Arena --}}
            <li class="mega_menu_hov">
              <a href="#">Arena</a>
              <div class="gamfi_mega_menu_sect">
                <div class="gamfi_mega_menu">
                  <div class="container">
                    <div class="mega_menu_content">
                      <div class="menu_column">
                        <h2><a href="{{ route('abilities.index') }}">Abilități</a></h2>
                        <ul>
                          <li><a href="{{ route('abilitati-disponibile') }}">Disponibile</a></li>
                          <li><a href="{{ route('foloseste-abilitate') }}">Folosește abilitate</a></li>
                          <li><a href="{{ route('cooldown') }}">Timp rămas</a></li>
                        </ul>
                      </div>
                      <div class="menu_column">
                        <h2><a href="{{ route('arena.trivia.joaca-trivia') }}">Joacă Trivia</a></h2>
                        <ul>
                          <li><a href="{{ route('arena.trivia.regulament-trivia') }}">Regulament Trivia</a></li>
                          <li><a href="{{ route('arena.trivia.istoric-trivia') }}">Istoric Trivia</a></li>
                        </ul>
                      </div>
                      <div class="menu_column">
                        <h2><a href="{{ route('arena.misiuni.index') }}">Misiuni</a></h2>
                        <ul>
                          <li><a href="{{ route('arena.misiuni.ghiceste-melodia') }}">Ghiceste Melodia</a></li>
                          <li><a href="{{ route('arena.misiuni.misiuni-zilnice') }}">Misiuni Zilnice</a></li>
                          <li><a href="{{ route('arena.misiuni.provocari') }}">Provocări</a></li>
                          <li><a href="{{ route('arena.misiuni.recompense') }}">Recompense</a></li>
                        </ul>
                      </div>
                      <div class="menu_column">
                        <h2><a href="{{ route('clasamente.index') }}">Clasamente</a></h2>
                        <ul>
                          <li><a href="{{ route('arena.clasamente.clasament-general') }}">General</a></li>
                          <li><a href="{{ route('arena.clasamente.jucatori-de-top') }}">Jucători top</a></li>
                          <li><a href="{{ route('arena.clasamente.jucatori-trivia') }}">Trivia top</a></li>
                          <li><a href="{{ route('arena.clasamente.tema-lunii') }}">Tema lunii</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>

            {{-- Magazin --}}
            <li>
              <a href="{{ route('magazin.index') }}">Magazin</a>
              <ul class="sub-menu">
                <li><a href="{{ route('magazin.premium') }}">Premium</a></li>
                <li><a href="{{ route('magazin.produse-disponibile') }}">Produse Disponibile</a></li>
                <li><a href="{{ route('magazin.cumpara-apbucksi') }}">Cumpără APbucksi</a></li>
              </ul>
            </li>

            {{-- Conectare (desktop) --}}
            @guest
              <li class="connect-btn-wrapper">
                <button type="button" class="connect-btn cyberpunk-pass"
                        data-bs-toggle="modal" data-bs-target="#loginModal">
                  <span>Conectează-te</span>
                </button>
              </li>
            @endguest
          </ul>
        </div>

        {{-- RIGHT: Desktop User Menu (unchanged) --}}
        <div class="user-menu-container d-flex align-items-center">
          @include('user.user-menu')
        </div>

        {{-- RIGHT: Mobile/Tablet burger (in header row; desktop hidden via CSS) --}}
        <button class="ap-hamburger d-xl-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#apMobileNav"
                aria-controls="apMobileNav"
                aria-label="Deschide meniul">
          <span class="bars"></span>
        </button>

      </div>
    </div>
  </div>
</header>
