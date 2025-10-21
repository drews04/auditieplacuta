{{-- resources/views/concurs/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/alege-tema.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/theme-like.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-mobile.css') }}?v={{ time() }}">
  
  {{-- NUCLEAR OPTION: Force hide any modal backdrop that appears --}}
  <style>
    .modal-backdrop {
      display: none !important;
      opacity: 0 !important;
      visibility: hidden !important;
    }
    body.modal-open {
      overflow: auto !important;
      padding-right: 0 !important;
    }
  </style>
@endpush

{{-- main site styles --}}
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

@section('title', 'Concursul de Azi')
@section('body_class', 'page-concurs')

{{-- Winner recap banner (safe) --}}
@includeWhen(isset($winnerStripCycle, $winnerStripWinner) && $winnerStripCycle && $winnerStripWinner, 'concurs.partials.winner_recap', [
    'lastFinishedCycle' => $winnerStripCycle,
    'lastWinner' => $winnerStripWinner
])

{{-- Winner pick-theme button --}}
@if($isWinner ?? false)
    @if($window === 'waiting_theme')
        <div class="text-center mt-4">
            <button class="btn btn-neon px-4 py-2" id="openPickThemeModal">
                <i class="fas fa-magic me-2"></i> Alege tema
            </button>
            <p class="mt-3" style="color:#16f1d3;font-weight:500;">
                Ai timp până la <strong>21:00</strong> să alegi tema.
            </p>
        </div>
    @endif
@endif

@section('content')
  {{-- Admin-only Start modal trigger --}}
  @auth
    @if((auth()->user()->is_admin ?? false) || auth()->id() === 1)
      {{-- Start modal --}}
      <div class="modal fade" id="startConcursModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form action="{{ route('concurs.start') }}" method="POST">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title">Pornește o rundă nouă</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
              </div>

              <div class="modal-body">
                <div class="mb-2 small text-muted">
                  Alege <strong>categoria</strong> + scrie <strong>numele temei</strong>. Dacă lași gol, alegem aleator din <code>theme_pools</code>.
                </div>

                {{-- TEMA A --}}
                <h6 class="fw-bold mb-2">Tema A (pornește ACUM)</h6>
                <div class="mb-3">
                  <label class="form-label">Categoria</label>
                  <select name="theme_a_category" class="form-select">
                    <option value="">— Alege categoria (opțional) —</option>
                    <option value="csd">CSD</option>
                    <option value="itc">ITC</option>
                    <option value="artisti">Artiști</option>
                    <option value="genuri">Genuri</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Numele temei</label>
                  <input name="theme_a_name" type="text" class="form-control" placeholder="ex: The Beatles / Dragoste / 90s">
                </div>

                <hr class="my-3">

                {{-- TEMA B (va fi folosită la 20:00) --}}
                <h6 class="fw-bold mb-2">Tema B (mâine după 20:00)</h6>
                <div class="mb-3">
                  <label class="form-label">Categoria</label>
                  <select name="theme_b_category" class="form-select">
                    <option value="">— Alege categoria —</option>
                    <option value="csd">CSD</option>
                    <option value="itc">ITC</option>
                    <option value="artisti">Artiști</option>
                    <option value="genuri">Genuri</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Numele temei</label>
                  <input name="theme_b_name" type="text" class="form-control" placeholder="ex: Rock / 2000s / ABBA">
                </div>

                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" name="force_reset_today" id="force_reset_today" value="1" checked>
                  <label class="form-check-label" for="force_reset_today">
                    Reset complet (șterge tot)
                  </label>
                </div>

                <small class="text-muted d-block mt-3">
                  <strong>Tema A</strong>: upload ACUM → 20:00.<br>
                  <strong>Tema B</strong>: se activează la 20:00 (songs A → vote, Tema B → upload).
                </small>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                <button type="submit" class="btn btn-primary" id="btnStartConcurs">▶ Start</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Close modal BEFORE form submit to prevent backdrop issue --}}
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const form = document.querySelector('#startConcursModal form');
          const modal = document.getElementById('startConcursModal');
          
          if (form && modal) {
            form.addEventListener('submit', function(e) {
              e.preventDefault();
              
              // Close modal using Bootstrap API
              const bsModal = bootstrap.Modal.getInstance(modal);
              if (bsModal) {
                bsModal.hide();
              }
              
              // Wait for modal to close, then submit
              setTimeout(() => {
                // Remove any leftover backdrops
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                
                // Now submit the form
                form.submit();
              }, 300);
            });
          }
        });
      </script>
    @endif
  @endauth

  @if(session('status'))
    <div class="alert alert-success mb-3">{{ session('status') }}</div>
    <script>
      // Remove modal backdrop if it's stuck after form submit
      document.addEventListener('DOMContentLoaded', function() {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
      });
    </script>
  @endif

  @php
    // $isWinner already calculated in controller
    // Normalize cycles for posters
    $voteCycle       = $cycleVote   ?? null;
    $submitCycle     = $cycleSubmit ?? null;
    $votePosterUrl   = data_get($voteCycle, 'poster_url');
    $submitPosterUrl = data_get($submitCycle, 'poster_url');
  @endphp

  <div class="container py-5">
    {{-- success toast after choosing theme --}}
    @if(session('tema_success'))
      <div id="temaChosenPopup" class="ap-popup ap-popup--success" role="dialog" aria-live="assertive">
        <div class="ap-popup__content">
          <div class="ap-popup__icon">✅</div>
          <div class="ap-popup__title">Tema a fost aleasă cu succes</div>
          <div class="ap-popup__text">Mulțumim! Tema pentru mâine este setată.</div>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded',function(){
          const p=document.getElementById('temaChosenPopup');if(!p)return;
          p.classList.add('is-visible');setTimeout(()=>p.classList.remove('is-visible'),3000);
          setTimeout(()=>p.remove(),3400);
        });
      </script>
    @endif

    {{-- Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
      <h1 class="mb-2 mb-sm-0 text-center w-100" style="font-weight:800; letter-spacing:1px;">🎧 CONCURSUL DE AZI</h1>
    </div>
    <p class="text-center mb-3">Alege acțiunea de azi:</p>

    {{-- Admin toolbar (mobile-safe, no overlap) --}}
    @auth
      @if((auth()->user()->is_admin ?? false) || auth()->id() === 1)
        <div class="admin-toolbar mb-3">
          <button type="button" class="neon-start-btn" data-bs-toggle="modal" data-bs-target="#startConcursModal">
            <i class="fas fa-power-off me-2"></i> Pornire Concurs
          </button>
        </div>
      @endif
    @endauth

    {{-- ===================== HERO: Posters grid (Vote | Upload) ===================== --}}
    <div class="container my-3 posters-grid" id="concurs-hero">
      {{-- LEFT: VOTE --}}
      <div class="poster-slot">
        @if($votePosterUrl)
          <div class="poster-wrap">
            <span class="poster-label">🔊 Votează</span>
            <a href="{{ route('concurs.vote.page') }}" class="poster-card" aria-label="Votează melodiile de ieri">
              <img class="poster-img"
                   src="{{ $votePosterUrl }}?v={{ optional($voteCycle)->updated_at?->timestamp ?? time() }}"
                   alt="Votează melodiile de ieri">
            </a>

            {{-- Admin replace/remove --}}
            @if(auth()->check() && data_get(auth()->user(),'is_admin') && data_get($voteCycle,'id'))
              <form class="poster-admin-overlay" method="POST" action="{{ route('admin.concurs.poster.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cycle_id" value="{{ data_get($voteCycle,'id') }}">
                <label class="ap-mini-upload ap-mini-upload--ghost">
                  <input type="file" name="poster" accept="image/*" class="d-none" onchange="this.form.submit()">Replace
                </label>
              </form>
              <form class="poster-admin-overlay poster-admin-overlay--right" method="POST" action="{{ route('admin.concurs.poster.destroy') }}"
                    onsubmit="return confirm('Ștergi acest poster?');">
                @csrf @method('DELETE')
                <input type="hidden" name="cycle_id" value="{{ data_get($voteCycle,'id') }}">
                <button type="submit" class="ap-mini-upload ap-mini-upload--danger">Remove</button>
              </form>
            @endif
          </div>
        @else
          <div class="hero-placeholder">
            <a href="{{ route('concurs.vote.page') }}" class="btn-hero-link">🔊 Votează melodiile de ieri</a>
            @if(auth()->check() && data_get(auth()->user(),'is_admin') && data_get($voteCycle,'id'))
              <form class="ap-admin-tools hero-upload" method="POST" action="{{ route('admin.concurs.poster.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cycle_id" value="{{ data_get($voteCycle,'id') }}">
                <label class="ap-mini-upload">
                  <input type="file" name="poster" accept="image/*" class="d-none" onchange="this.form.submit()">Upload
                </label>
                @error('poster')   <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                @error('cycle_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
              </form>
            @endif
          </div>
        @endif
      </div>

      {{-- RIGHT: UPLOAD --}}
      <div class="poster-slot">
        @if($submitPosterUrl)
          <div class="poster-wrap">
            <span class="poster-label">⬆️ Încarcă</span>
            <a href="{{ route('concurs.upload.page') }}" class="poster-card" aria-label="Încarcă melodia pentru azi">
              <img class="poster-img"
                   src="{{ $submitPosterUrl }}?v={{ optional($submitCycle)->updated_at?->timestamp ?? time() }}"
                   alt="Încarcă melodia pentru azi">
            </a>

            {{-- Admin replace/remove --}}
            @if(auth()->check() && data_get(auth()->user(),'is_admin') && data_get($submitCycle,'id'))
              <form class="poster-admin-overlay" method="POST" action="{{ route('admin.concurs.poster.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cycle_id" value="{{ data_get($submitCycle,'id') }}">
                <label class="ap-mini-upload ap-mini-upload--ghost">
                  <input type="file" name="poster" accept="image/*" class="d-none" onchange="this.form.submit()">Replace
                </label>
              </form>
              <form class="poster-admin-overlay poster-admin-overlay--right" method="POST" action="{{ route('admin.concurs.poster.destroy') }}"
                    onsubmit="return confirm('Ștergi acest poster?');">
                @csrf @method('DELETE')
                <input type="hidden" name="cycle_id" value="{{ data_get($submitCycle,'id') }}">
                <button type="submit" class="ap-mini-upload ap-mini-upload--danger">Remove</button>
              </form>
            @endif
          </div>
        @else
          <div class="hero-placeholder">
            <a href="{{ route('concurs.upload.page') }}" class="btn-hero-link">⬆️ Încarcă melodia pentru azi</a>
            @if(auth()->check() && data_get(auth()->user(),'is_admin') && data_get($submitCycle,'id'))
              <form class="ap-admin-tools hero-upload" method="POST" action="{{ route('admin.concurs.poster.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="cycle_id" value="{{ data_get($submitCycle,'id') }}">
                <label class="ap-mini-upload">
                  <input type="file" name="poster" accept="image/*" class="d-none" onchange="this.form.submit()">Upload
                </label>
                @error('poster')   <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                @error('cycle_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
              </form>
            @endif
          </div>
        @endif
      </div>
    </div>
    {{-- ===================== /HERO ===================== --}}

    {{-- ===== WINNER STRIP (last finished round) ===== --}}
    @if(isset($winnerStripCycle) && $winnerStripCycle)
      @php
        $d  = $winnerStripCycle->vote_end_at ? \Carbon\Carbon::parse($winnerStripCycle->vote_end_at)->timezone(config('app.timezone')) : null;
        $ds = $d ? $d->isoFormat('dddd, D MMMM YYYY') : '';
      @endphp

      <div class="ap-winner-strip ap-neon-card p-3 mb-4 d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
        <div class="ap-winner-cup">🏆</div>

        <div class="flex-grow-1">
          @if(isset($winnerStripWinner) && $winnerStripWinner)
            <div class="ap-winner-topline mb-1">
              <span class="ap-winner-label">Ultima rundă încheiată</span>
              @if(!empty($ds))
                <span class="ap-winner-date">• {{ $ds }}</span>
              @endif
            </div>

            <div class="ap-winner-title">
              {{ $winnerStripWinner->song->title ?? 'Melodie' }}
              <span class="ap-winner-by">de</span>
              <a href="{{ route('users.wins', ['userId' => $winnerStripWinner->user->id ?? 0]) }}" class="ap-winner-user">
                {{ $winnerStripWinner->user->name ?? 'utilizator' }}
              </a>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
              @if($winnerStripCycle->theme_text)
                @php
                  $parts  = preg_split('/\s*—\s*/u', $winnerStripCycle->theme_text, 2);
                  $cat    = trim($parts[0] ?? '');
                  $titleT = trim($parts[1] ?? $winnerStripCycle->theme_text);
                  $catDisp= ['csd'=>'CSD','it'=>'ITC','itc'=>'ITC','artisti'=>'Artiști','genuri'=>'Genuri'][strtolower($cat)] ?? $cat;
                @endphp
                <span class="ap-theme-pill">
                  @if($catDisp !== '')<span class="ap-theme-cat">{{ $catDisp }}</span>@endif
                  <span class="ap-theme-sep">—</span>
                  <span class="ap-theme-title">{{ $titleT }}</span>
                </span>
              @endif

              @if(isset($winnerStripWinner->vote_count))
                <span class="badge bg-dark-subtle text-dark-emphasis ap-votes-badge">
                  {{ $winnerStripWinner->vote_count }} vot{{ $winnerStripWinner->vote_count === 1 ? '' : 'uri' }}
                </span>
              @endif
            </div>
          @else
            {{-- No winner case (no entries / no valid votes) --}}
            <div class="ap-winner-topline mb-1">
              <span class="ap-winner-label">Ultima rundă</span>
              @if(!empty($ds))
                <span class="ap-winner-date">• {{ $ds }}</span>
              @endif
            </div>
            <div class="ap-winner-title">Nu avem un câștigător pentru runda trecută.</div>
          @endif
        </div>

        <div class="d-flex gap-2 mt-3 mt-md-0">
          @if(isset($winnerStripWinner) && $winnerStripWinner && !empty($winnerStripWinner->song?->youtube_url))
            <a href="{{ $winnerStripWinner->song->youtube_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Ascultă pe YouTube</a>
          @endif
          @if($d)
            <a href="{{ route('concurs') }}?rezultate={{ $d->toDateString() }}" class="ap-btn-neon">Rezultatele complete</a>
          @endif
        </div>
      </div>
    @endif

    {{-- ===== Tema lunii (DISABLED - broken schema, not in Compendium v2) ===== --}}
    {{--
    @php
      $yr = now()->year; $mo = now()->month;
      $start = \Carbon\Carbon::create($yr,$mo,1)->startOfDay();
      $end   = $start->copy()->endOfMonth();
      $monthClosed = now()->greaterThan($end);
      $temaLunii = null; // DISABLED
    @endphp

    @if($monthClosed && $temaLunii)
      <div class="ap-neon-card p-3 mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <span class="fs-5">🏅 <strong>Tema lunii</strong></span>
          <span class="fw-bold fs-5 text-light">{{ $temaLunii->name }}</span>
        </div>
      </div>
    @endif
    --}}
  </div>

  {{-- Winner reminder overlay (no inline JS; handled by concurs.js) --}}
  @if( ($isWinner && !$tomorrowPicked) || $showWinnerModal || $showWinnerPopup || session('ap_show_theme_modal') === true || session('force_theme_modal') === true )
    <div id="winnerReminder" style="display:none;">
      <canvas id="confetti-bg" style="pointer-events:none"></canvas>
      <div class="winner-box">
        <h3 class="w-title">Felicitări, {{ Auth::user()->name ?? 'campion' }}, ai câștigat!</h3>
        <div class="w-sub">Alege tema pentru concursul de mâine</div>
        <p class="w-lead">Setează tema până la ora 21:00. Dacă nu alegi, vom porni fallback-ul automat.</p>
        <div class="w-actions">
          <a href="{{ route('concurs.alege-tema.create') }}" id="btn-open-theme" class="btn-neon">Alege tema</a>
          <button id="btn-close-winner" class="btn-ghost" type="button">Închide</button>
        </div>
        <div class="w-pill mt-3"><span class="me-1">🗓</span><span id="winner-deadline">Până la 21:00, azi</span></div>
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
  {{-- NUCLEAR OPTION: Kill any stuck modal backdrop --}}
  <script>
    (function() {
      function killBackdrop() {
        // Remove ALL backdrops
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Also hide any open modals
        document.querySelectorAll('.modal.show').forEach(modal => {
          modal.classList.remove('show');
          modal.style.display = 'none';
          modal.setAttribute('aria-hidden', 'true');
        });
      }
      
      // Run immediately
      killBackdrop();
      
      // Run after DOM loads
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', killBackdrop);
      } else {
        killBackdrop();
      }
      
      // Run after a delay (catch any late-loading modals)
      setTimeout(killBackdrop, 100);
      setTimeout(killBackdrop, 300);
      setTimeout(killBackdrop, 500);
    })();
  </script>

  {{-- Expose tokens, routes & flags for public/js/concurs.js --}}
  <script>
    window.csrfToken = "{{ csrf_token() }}";
    window.uploadRoute   = "{{ route('concurs.upload') }}";
    // window.songListRoute not needed - JS has fallback
    window.voteRoute     = "{{ route('concurs.vote') }}";

    window.concursFlags = {
      // state for voting / preview guards
      votingOpen: {{ $votingOpen ? 'true' : 'false' }},
      isPreVote:  {{ isset($votingOpensAt) && $votingOpensAt ? 'true' : 'false' }},
      // winner modal control (JS decides when to show; respects snooze)
      showWinnerModal: {{ ($showWinnerModal ?? false) ? 'true' : 'false' }},
      forceThemeModal: {{ (session('ap_show_theme_modal') === true || session('force_theme_modal') === true) ? 'true' : 'false' }},
      isWinner: {{ $isWinner ? 'true' : 'false' }},
      tomorrowPicked: {{ $tomorrowPicked ? 'true' : 'false' }}
    };
  </script>

  {{-- Confetti (used by JS when opening the modal) --}}
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js" defer></script>

  {{-- Core page JS (winner modal, uploads, voting, list loading) --}}
  <script src="{{ asset('js/concurs.js') }}"></script>
@endpush

