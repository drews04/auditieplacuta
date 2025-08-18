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

        {{-- CENTER: Menu --}}
        <div class="header-menu">
          <ul class="nav-menu d-flex align-items-center">
            {{-- Acasa --}}
            <li>
              <a href="{{ route('home') }}">Acasa</a>
              <ul class="sub-menu">
                
                <li><a href="{{ route('evenimente') }}">Evenimente</a></li>
                
              </ul>
            </li>

            {{-- Concurs --}}
            <li>
              <a href="{{ route('concurs') }}">Concurs</a>
              <ul class="sub-menu">
              <li><a href="{{ route('leaderboard.monthly') }}">Clasament</a></li>
                <li><a href="{{ route('concurs.incarca-melodie') }}">Încarcă melodie</a></li>
                <li><a href="{{ route('concurs.melodiile-zilei') }}">Melodiile zilei</a></li>
                <li><a href="{{ route('concurs.voteaza') }}">Votează</a></li>
                <li><a href="{{ route('concurs.rezultate') }}">Rezultate (Arhivă)</a></li>
                <li><a href="{{ route('concurs.arhiva-teme') }}">Arhivă teme</a></li>
                <li><a href="{{ route('regulament') }}">Regulament</a></li>
              </ul>
            </li>

            {{-- Muzica --}}
            <li>
              <a href="{{ route('muzica') }}">Muzică</a>
              <ul class="sub-menu">
                <li><a href="{{ route('muzica.noutati') }}">Noutăți în muzică</a></li>
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

            {{-- Conectare --}}
            @guest
            <li class="connect-btn-wrapper">
              <button type="button" class="connect-btn cyberpunk-pass" data-bs-toggle="modal" data-bs-target="#loginModal">
                <span>Conectează-te</span>
              </button>
            </li>
              @endguest
            </ul>
        </div>

        {{-- User menu shown if authenticated --}}
        {{-- TEMP: Always show user menu (for styling) --}}
          <div class="user-menu-container d-flex align-items-center">
            @include('user.user-menu')
          </div>

      </div>
    </div>
  </div>
</header>
