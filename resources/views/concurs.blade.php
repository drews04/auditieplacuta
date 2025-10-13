{{-- resources/views/concurs.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/alege-tema.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/theme-like.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/concurs-mobile.css') }}?v={{ time() }}">
@endpush

{{-- main site styles --}}
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

@section('title', 'Concursul de Azi')
@section('body_class', 'page-concurs')

{{-- Winner recap banner (safe) --}}
@includeWhen(isset($lastFinishedCycle, $lastWinner) && $lastFinishedCycle && $lastWinner, 'partials.winner_recap')

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

                {{-- TEMA B --}}
                <h6 class="fw-bold mb-2">Tema B (mâine — înscrieri 00:00 → 20:00)</h6>
                <div class="mb-3">
                  <label class="form-label">Categoria</label>
                  <select name="theme_b_category" class="form-select">
                    <option value="">— opțional —</option>
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
                  <input class="form-check-input" type="checkbox" name="force_reset_today" id="force_reset_today" value="1">
                  <label class="form-check-label" for="force_reset_today">
                    Reset de azi (șterge datele curente) înainte de Start
                  </label>
                </div>

                <small class="text-muted d-block mt-3">
                  La <strong>00:00</strong>: vot A (00:00→20:00) + înscrieri B (00:00→20:00). Apoi vot pentru B a doua zi.
                </small>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                <button type="submit" class="btn btn-primary">▶ Start</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endif
  @endauth

  @if(session('status'))
    <div class="alert alert-success mb-3">{{ session('status') }}</div>
  @endif

  @php
    // Winner?
    $isWinner       = auth()->check() && $todayWinner && auth()->id() === $todayWinner->user_id;
    $tomorrowPicked = isset($tomorrowTheme) && $tomorrowTheme;
    // Normalize cycles for posters
    $voteCycle       = $cycleVote   ?? ($voteCycle   ?? null);
    $submitCycle     = $cycleSubmit ?? ($submitCycle ?? null);
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
    $d  = optional($winnerStripCycle->vote_end_at)->timezone(config('app.timezone'));
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

    {{-- ===== Tema lunii (under winner strip) ===== --}}
    @php
      $yr = now()->year; $mo = now()->month;
      $start = \Carbon\Carbon::create($yr,$mo,1)->startOfDay();
      $end   = $start->copy()->endOfMonth();
      $monthClosed = now()->greaterThan($end);
      $temaLunii = \Illuminate\Support\Facades\Cache::remember("tema_lunii_{$yr}_{$mo}_v2", 300, function () use ($start,$end) {
        return \DB::table('contest_themes as ct')
          ->leftJoin('users as u', 'u.id', '=', 'ct.chosen_by_user_id')
          ->leftJoin('theme_likes as tl', function ($j) { $j->on('tl.likeable_id','=','ct.id')->where('tl.likeable_type','=', \App\Models\ContestTheme::class); })
          ->selectRaw('ct.id, ct.name, ct.category, ct.contest_date, ct.created_at, ct.chosen_by_user_id, COALESCE(u.name, "—") as chooser_name, COUNT(tl.id) as likes_count')
          ->whereBetween(\DB::raw('COALESCE(ct.contest_date, DATE(ct.created_at))'), [$start->toDateString(), $end->toDateString()])
          ->groupBy('ct.id','ct.name','ct.category','ct.contest_date','ct.created_at','ct.chosen_by_user_id','u.name')
          ->orderByDesc('likes_count')->orderByRaw('COALESCE(ct.contest_date, DATE(ct.created_at)) ASC')->orderBy('ct.id','ASC')->first();
      });
    @endphp

    @if($monthClosed && $temaLunii)
      <div class="ap-neon-card p-3 mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <span class="fs-5">🏅 <strong>Tema lunii</strong></span>
          @if(!empty($temaLunii->category))
            <span class="ap-badge ap-badge-soft">{{ $temaLunii->category }}</span>
          @endif
          <span class="fw-bold fs-5 text-light">{{ $temaLunii->name }}</span>
          <span class="ap-muted ms-2">Aleasă de: <strong>{{ $temaLunii->chooser_name }}</strong></span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="ap-badge ap-badge-dark">❤️ {{ $temaLunii->likes_count }}</span>
          <a href="{{ route('arena.clasamente.tema-lunii') }}" class="ap-btn-neon">Vezi topul</a>
        </div>
      </div>
    @else
      <div class="ap-neon-card p-3 mb-4 d-flex align-items-center justify-content-between">
        <div class="fs-5">🏅 <strong>Tema lunii</strong></div>
        <em class="ap-muted">Tema lunii nu a fost decisă încă.</em>
        <a href="{{ route('arena.clasamente.tema-lunii') }}" class="ap-btn-neon">Vezi topul</a>
      </div>
    @endif
  </div>

  {{-- Winner reminder overlay --}}
  @if( ($isWinner && !$tomorrowPicked) || $showWinnerModal || $showWinnerPopup || session('ap_show_theme_modal') === true || session('force_theme_modal') === true )
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
  <script>
    window.csrfToken = "{{ csrf_token() }}";
  </script>

  {{-- Core page JS (posters/admin helpers) --}}
  <script src="{{ asset('js/concurs.js') }}"></script>

  {{-- Confetti --}}
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js" defer></script>

  {{-- Winner modal (optional) --}}
  @if($showWinnerModal)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const overlay=document.getElementById('winnerReminder'); if(!overlay) return;
        function boom(){ if(typeof confetti==="function"){ confetti({ particleCount:300, spread:120, startVelocity:50, gravity:0.9, ticks:200, origin:{ y:0.6 }, zIndex:3000 }); } }
        overlay.classList.remove('d-none'); overlay.style.display='block'; boom();
        setTimeout(()=>{ overlay.classList.add('d-none'); overlay.style.display='none'; }, 30000);
        document.getElementById('btn-close-winner')?.addEventListener('click', ()=>{ overlay.classList.add('d-none'); overlay.style.display='none'; });
      });
    </script>
  @endif

  {{-- Force theme modal (optional) --}}
  @if (session('ap_show_theme_modal') === true)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var overlay=document.getElementById('winnerReminder');
        if(overlay){ overlay.classList.remove('d-none'); overlay.style.display='block'; try{ if(typeof confetti==='function') confetti({ particleCount:250, spread:100 }); }catch(e){} }
        else{ window.location.href="{{ route('concurs.alege-tema.create') }}"; }
      });
    </script>
  @endif
@endpush
