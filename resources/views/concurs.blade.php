@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/concurs-winner.css') }}?v={{ filemtime(public_path('assets/css/concurs-winner.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/vote-btn.css') }}?v={{ filemtime(public_path('assets/css/vote-btn.css')) }}">
@endpush
{{-- main site styles --}}
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

@section('title', 'Concursul de Azi')
@section('body_class', 'page-concurs')
@includeWhen(isset($lastFinishedCycle, $lastWinner) && $lastFinishedCycle && $lastWinner, 'partials.winner_recap')


@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/alege-tema.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/theme-like.css') }}?v={{ time() }}">
@endpush


@section('content')


{{-- Admin-only Start (manual categories + free text themes + hard reset) --}}
@auth
@if((auth()->user()->is_admin ?? false) || auth()->id() === 1)
  <div class="mb-2 d-flex justify-content-end">
    <button type="button"
            class="btn btn-primary btn-sm w-auto d-inline-flex align-items-center px-2 py-1"
            style="line-height:1; font-size:12px;"
            data-bs-toggle="modal"
            data-bs-target="#startConcursModal">
      ▶ Start
    </button>
  </div>

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
              Alege <strong>categoria</strong> (CSD/ITC/Artisti/Genuri) + scrie <strong>numele temei</strong> exact cum vrei să apară.
              Dacă lași gol, alegem aleator din <code>theme_pools</code>.
            </div>

            {{-- TEMA A (începe acum; înscrieri până la 20:00, sau imediat→mâine 20:00 dacă e după 20:00) --}}
            <h6 class="fw-bold mb-2">Tema A (pornește ACUM)</h6>

            <div class="mb-3">
              <label class="form-label">Categoria</label>
              <select name="theme_a_category" class="form-select">
                <option value="">— Alege categoria (opțional) —</option>
                <option value="csd">CSD</option>
                <option value="itc">ITC</option>
                <option value="artisti">Artisti</option>
                <option value="genuri">Genuri</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Numele temei</label>
              <input name="theme_a_name" type="text" class="form-control" placeholder="ex: The Beatles / Dragoste / 90s">
              <small class="text-muted">Afișare: <em>CSD — Dragoste</em>, <em>ITC — 90s</em>, <em>Artisti — The Beatles</em> etc.</small>
            </div>

            <hr class="my-3">

            {{-- TEMA B (mâine 00:00→20:00 în paralel cu votul pentru A) --}}
            <h6 class="fw-bold mb-2">Tema B (mâine — înscrieri 00:00 → 20:00)</h6>

            <div class="mb-3">
              <label class="form-label">Categoria</label>
              <select name="theme_b_category" class="form-select">
                <option value="">— opțional (dacă lași gol, alegem aleator) —</option>
                <option value="csd">CSD</option>
                <option value="itc">ITC</option>
                <option value="artisti">Artisti</option>
                <option value="genuri">Genuri</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Numele temei</label>
              <input name="theme_b_name" type="text" class="form-control" placeholder="ex: Rock / 2000s / ABBA">
              <small class="text-muted">Rulăm Tema B în paralel cu <strong>votul pentru A</strong> (00:00 → 20:00).</small>
            </div>

            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" name="force_reset_today" id="force_reset_today" value="1">
              <label class="form-check-label" for="force_reset_today">
                Reset de azi (șterge înscrieri, voturi, câștigători și runde care ating ziua curentă) înainte de Start
              </label>
            </div>

            <small class="text-muted d-block mt-3">
              La <strong>00:00</strong>: vot pentru Tema A (00:00 → 20:00) + înscrieri pentru Tema B (00:00 → 20:00).
              Apoi, a doua zi: vot pentru Tema B (00:00 → 20:00).
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
    <div class="alert alert-success mb-3">
      {{ session('status') }}
    </div>
  @endif

  @php
      // Winner?
      $isWinner = auth()->check() && $todayWinner && auth()->id() === $todayWinner->user_id;

      // tomorrow theme exists?
      $tomorrowPicked = isset($tomorrowTheme) && $tomorrowTheme;

      // Allow upload when submissions are open OR we’re in the “upload for tomorrow” window
      $canUpload = ($submissionsOpen || ($uploadForTomorrow ?? false));
  @endphp

  <div class="container py-5">

    {{-- Success popup after choosing theme --}}
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

    {{-- Title + quick winner action --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
      <h1 class="mb-2 mb-sm-0 text-center w-100" style="font-weight:800; letter-spacing:1px;">
        🎧 CONCURSUL DE AZI
      </h1>

      {{-- /concurs quick actions (safe links to dedicated pages) --}}
      <div class="container my-3" id="concurs-quick-ctas">
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('concurs.vote.page') }}" class="btn btn-primary">
            🔊 Votează melodiile de ieri
          </a>
          <a href="{{ route('concurs.upload.page') }}" class="btn btn-outline-info">
            ⬆️ Încarcă melodia pentru azi
          </a>
        </div>
      </div>

      {{-- Posters row — completely hidden if no poster_url on that cycle --}}
        @php
            // These two vars should already be available or can be fetched via helpers on the model.
            // Use data_get(...) so we don't error if the fields don't exist yet.
            $voteCycle      = isset($voteCycle) ? $voteCycle : ($cycles['vote'] ?? null);
            $submitCycle    = isset($submitCycle) ? $submitCycle : ($cycles['submit'] ?? null);

            $votePosterUrl   = data_get($voteCycle, 'poster_url');    // string|null
            $submitPosterUrl = data_get($submitCycle, 'poster_url');  // string|null
        @endphp

        @if($votePosterUrl || $submitPosterUrl)
          <div class="container my-3" id="concurs-posters">
            <div class="row g-3">
              @if($votePosterUrl)
                <div class="col-12 col-md-6">
                  <a href="{{ route('concurs.vote.page') }}" class="d-block" aria-label="Deschide pagina de vot">
                    <img src="{{ $votePosterUrl }}" alt="Poster vot" class="img-fluid rounded-3 w-100" style="display:block;">
                  </a>
                </div>
              @endif

              @if($submitPosterUrl)
                <div class="col-12 col-md-6">
                  <a href="{{ route('concurs.upload.page') }}" class="d-block" aria-label="Deschide pagina de upload">
                    <img src="{{ $submitPosterUrl }}" alt="Poster înscrieri" class="img-fluid rounded-3 w-100" style="display:block;">
                  </a>
                </div>
              @endif
            </div>
          </div>
        @endif


      @if(($isWinner && !$tomorrowPicked) || (session('force_theme_modal') && session('ap_test_mode')))
        <a href="{{ route('concurs.alege-tema.create') }}" class="btn btn-neon">
          🎯 Alege tema pentru mâine
          @if(session('ap_test_mode'))
            <span class="badge bg-warning ms-2">TEST</span>
          @endif
        </a>
      @endif
    </div>
    <p class="text-center mb-4">Ascultă melodiile participante și votează-ți favorita!</p>

    {{-- ===== WINNER STRIP (last finished round) ===== --}}
@if(isset($winnerStripCycle) && $winnerStripCycle)
  @php
    $d  = optional($winnerStripCycle->vote_end_at)->timezone(config('app.timezone'));
    $ds = $d ? $d->isoFormat('dddd, D MMMM YYYY') : '';
  @endphp

  <div class="ap-winner-strip card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
      <div class="ap-winner-cup">🏆</div>

      <div class="flex-grow-1">
        @if(isset($winnerStripWinner) && $winnerStripWinner)
          <div class="ap-winner-topline">
            <span class="ap-winner-label">Ultima rundă încheiată</span>
            @if(!empty($ds))
              <span class="ap-winner-date">• {{ $ds }}</span>
            @endif
          </div>
          <div class="ap-winner-title">
            {{ $winnerStripWinner->song->title ?? 'Melodie' }}
            <span class="ap-winner-by">de</span>
            <a href="{{ route('users.wins', ['userId' => $winnerStripWinner->user->id ?? 0]) }}"
               class="ap-winner-user">
              {{ $winnerStripWinner->user->name ?? 'utilizator' }}
            </a>
          </div>

          <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
            {{-- theme pill (comes from that cycle’s theme_text) --}}
            @if($winnerStripCycle->theme_text)
              @php
                $parts = preg_split('/\s*—\s*/u', $winnerStripCycle->theme_text, 2);
                $cat   = trim($parts[0] ?? '');
                $title = trim($parts[1] ?? $winnerStripCycle->theme_text);
                $catDisp = ['csd'=>'CSD','it'=>'ITC','itc'=>'ITC','artisti'=>'Artisti','genuri'=>'Genuri'][strtolower($cat)] ?? $cat;
              @endphp
              <span class="ap-theme-pill">
                @if($catDisp !== '')<span class="ap-theme-cat">{{ $catDisp }}</span>@endif
                <span class="ap-theme-sep">—</span>
                <span class="ap-theme-title">{{ $title }}</span>
              </span>
            @endif

            {{-- votes badge --}}
            @if(isset($winnerStripWinner->vote_count))
              <span class="badge bg-dark-subtle text-dark-emphasis ap-votes-badge">
                {{ $winnerStripWinner->vote_count }} vot{{ $winnerStripWinner->vote_count === 1 ? '' : 'uri' }}
              </span>
            @endif
          </div>
        @else
          {{-- tie/unresolved or not declared yet --}}
          <div class="ap-winner-title mb-1">
            ⚖️ Egalitate la vârf — decizie în curs
          </div>
          @if(!empty($ds))
            <div class="text-muted small">{{ $ds }}</div>
          @endif
        @endif
      </div>

      <div class="d-flex gap-2">
        @if(isset($winnerStripWinner) && $winnerStripWinner && !empty($winnerStripWinner->song?->youtube_url))
          <a href="{{ $winnerStripWinner->song->youtube_url }}" target="_blank" rel="noopener"
             class="btn btn-outline-primary btn-sm">
            Ascultă pe YouTube
          </a>
        @endif
        {{-- Rezultatele complete → we’ll wire a dedicated recap page later; for now, stash date as a query --}}
        @if($d)
          <a href="{{ route('concurs') }}?rezultate={{ $d->toDateString() }}"
             class="btn btn-primary btn-sm">
            Rezultatele complete
          </a>
        @endif
      </div>
    </div>
  </div>
@endif

{{-- ===== Tema lunii (under winner strip) ===== --}}
@php
  $yr = now()->year; $mo = now()->month;

  $start = \Carbon\Carbon::create($yr,$mo,1)->startOfDay();
  $end   = $start->copy()->endOfMonth();
  $monthClosed = now()->greaterThan($end); // only reveal winner after month closes

  $temaLunii = \Illuminate\Support\Facades\Cache::remember("tema_lunii_{$yr}_{$mo}_v2", 300, function () use ($start,$end) {
    return \DB::table('contest_themes as ct')
      ->leftJoin('users as u', 'u.id', '=', 'ct.chosen_by_user_id')
      ->leftJoin('theme_likes as tl', function ($j) {
        $j->on('tl.likeable_id','=','ct.id')
          ->where('tl.likeable_type','=', \App\Models\ContestTheme::class);
      })
      ->selectRaw('
        ct.id, ct.name, ct.category, ct.contest_date, ct.created_at,
        ct.chosen_by_user_id, COALESCE(u.name, "—") as chooser_name,
        COUNT(tl.id) as likes_count
      ')
      ->whereBetween(\DB::raw('COALESCE(ct.contest_date, DATE(ct.created_at))'), [$start->toDateString(), $end->toDateString()])
      ->groupBy('ct.id','ct.name','ct.category','ct.contest_date','ct.created_at','ct.chosen_by_user_id','u.name')
      ->orderByDesc('likes_count')
      ->orderByRaw('COALESCE(ct.contest_date, DATE(ct.created_at)) ASC')
      ->orderBy('ct.id','ASC')
      ->first();
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





    {{-- Test Mode Winner Banner --}}
    @if(session('test_winner_banner'))
      <div class="alert alert-info mb-3">
        <div class="d-flex align-items-center">
          <span class="me-2">🧪</span>
          <strong>TEST MODE:</strong> {{ session('test_winner_banner') }}
        </div>
      </div>
    @endif

    {{-- Tomorrow theme pill --}}
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

    {{-- Weekend info label (soft) --}}
    @unless($isWeekday)
      <div class="alert alert-info mb-3">
        🗓️ Nu se ține concurs în weekend (Luni–Vineri).
      </div>
    @endunless


    {{-- ===== 1) THEME PILL (today's submission theme) ===== --}}
@if($submissionsOpen)
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title mb-2">
        <a id="concurs-submit"></a>
        📤 Înscrieri pentru tema de azi
      </h5>

      @if($cycleSubmit && $cycleSubmit->theme_text)
        @php
          // Parse category + title from theme_text
          $parts = preg_split('/\s*—\s*/u', $cycleSubmit->theme_text, 2);
          $cat   = trim($parts[0] ?? '');
          $title = trim($parts[1] ?? $cycleSubmit->theme_text);
          $catDisp = ['csd'=>'CSD','it'=>'ITC','itc'=>'ITC','artisti'=>'Artisti','genuri'=>'Genuri'][strtolower($cat)] ?? $cat;

          // Force a concrete ContestTheme id straight from the cycle
          $submitThemeId = (int)($cycleSubmit->contest_theme_id ?? 0);

          // Server counts (prefer eager data, fallback to DB)
          $submitLikesCount = 0;
          $submitLiked      = false;

          if ($submitThemeId > 0) {
              if (isset($submitTheme) && ($submitTheme->id ?? 0) === $submitThemeId) {
                  // From controller eager load
                  $submitLikesCount = $submitTheme->likes_count
                      ?? ($submitTheme->relationLoaded('likes')
                          ? $submitTheme->likes->count()
                          : $submitTheme->likes()->count());

                  if (auth()->check()) {
                      $submitLiked = $submitTheme->relationLoaded('likes')
                          ? $submitTheme->likes->where('user_id', auth()->id())->isNotEmpty()
                          : $submitTheme->likes()->where('user_id', auth()->id())->exists();
                  }
              } else {
                  // No eager model → compute from DB
                  $submitLikesCount = \App\Models\ThemeLike::where('likeable_type', \App\Models\ContestTheme::class)
                      ->where('likeable_id', $submitThemeId)->count();

                  if (auth()->check()) {
                      $submitLiked = \App\Models\ThemeLike::where('likeable_type', \App\Models\ContestTheme::class)
                          ->where('likeable_id', $submitThemeId)
                          ->where('user_id', auth()->id())->exists();
                  }
              }
          }
        @endphp

        <div class="ap-theme-row">
          <div class="ap-left">
            @if($catDisp !== '')
              <span class="ap-cat-badge">{{ $catDisp }}</span>
              <span class="ap-dot">🎯</span>
              <span class="ap-label">Tema:</span>
            @else
              <span class="ap-label">Tema:</span>
            @endif

            <span class="ap-title">{{ $title }}</span>

            {{-- Heart LIKE pill (neon style; no Bootstrap btn classes) --}}
            @if($submitThemeId > 0)
              <button
                type="button"
                class="theme-like ms-2"
                data-likeable-type="contest"
                data-likeable-id="{{ $submitThemeId }}"
                data-liked="{{ $submitLiked ? 1 : 0 }}"
                data-count="{{ $submitLikesCount }}"
                @guest data-auth="0" @endguest>
                <i class="heart-icon"></i>
                <span class="like-count">{{ $submitLikesCount }}</span>
              </button>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
@endif


    {{-- ===== 2) UPLOAD FORM (BETWEEN THEME & SONG LIST) ===== --}}
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
                  🚀 Această melodie va intra în <strong>concursul de mâine</strong>
                  (tema: <strong>{{ $tomorrowTheme->title }}</strong>).
                </small>
              @else
                <small class="text-muted d-block mt-2">
                  Înscrierile sunt deschise până la
                  <strong>{{ optional($cycleSubmit?->submit_end_at)->timezone(config('app.timezone'))->format('H:i') }}</strong>.
                </small>
              @endif
            </form>
          </div>
        </div>
      @endif
    @else
      {{-- not logged in --}}
      <div class="alert alert-dark mb-4">🔒 Autentifică-te pentru a-ți înscrie melodia.</div>
    @endauth


    {{-- ===== 3) TODAY'S SONG LIST (AFTER UPLOAD) ===== --}}
    @if($submissionsOpen)
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div id="song-list">
            @include('partials.songs_list', [
              'songs'              => $songsSubmit,
              'userHasVotedToday'  => true,   // no real voting here
              'showVoteButtons'    => false,  // hide active buttons
              'disabledVoteText'   => $votingOpensAt
                                      ? 'Votează (se activează la ' . $votingOpensAt->timezone(config('app.timezone'))->format('H:i') . ')'
                                      : 'Votează (se activează curând)',
            ])
          </div>
        </div>
      </div>
    @endif


    {{-- Yesterday's list — the VOTE section --}}
@if($songsVote->isNotEmpty())
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3">
        <a id="concurs-vote"></a>
        ★ Votează
      </h5>

      @if($cycleVote && $cycleVote->theme_text)
        @php
          $parts = preg_split('/\s*—\s*/u', $cycleVote->theme_text, 2);
          $cat   = trim($parts[0] ?? '');
          $title = trim($parts[1] ?? $cycleVote->theme_text);
          $catDisp = ['csd'=>'CSD','it'=>'ITC','itc'=>'ITC','artisti'=>'Artisti','genuri'=>'Genuri'][strtolower($cat)] ?? $cat;

          $voteTheme      = $voteTheme ?? ($cycleVote->contestTheme ?? null);
          $voteThemeId    = $voteTheme->id ?? ($cycleVote->contest_theme_id ?? 0);
          $voteLikesCount = $voteTheme->likes_count ?? ($voteTheme ? $voteTheme->likes->count() : 0);
          $voteLiked      = auth()->check() && $voteTheme
                            ? $voteTheme->likes->where('user_id', auth()->id())->isNotEmpty()
                            : false;
        @endphp

        <div class="ap-theme-row mb-3">
          <div class="ap-left">
            @if($catDisp !== '')
              <span class="ap-cat-badge">{{ $catDisp }}</span>
              <span class="ap-dot">🎯</span>
              <span class="ap-label">Tema:</span>
            @else
              <span class="ap-label">Tema:</span>
            @endif
            <span class="ap-title">{{ $title }}</span>
          </div>

          <div class="dropdown d-inline-block theme-like-wrap">
            <button type="button"
                    class="btn btn-sm theme-like"
                    data-likeable-type="contest"
                    data-likeable-id="{{ $voteThemeId }}"
                    data-liked="{{ $voteLiked ? 1 : 0 }}"
                    data-count="{{ $voteLikesCount }}"
                    @guest data-auth="0" @endguest
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
              <i class="heart-icon"></i>
              <span class="like-count">{{ $voteLikesCount }}</span>
            </button>

            <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
              @forelse($voteTheme->likes ?? [] as $like)
                <li class="small text-muted">❤️ {{ $like->user->name }}</li>
              @empty
                <li class="small text-muted">Niciun like încă</li>
              @endforelse
            </ul>
          </div>
        </div>
      @endif

      {{-- THE ACTUAL SONG LIST FOR VOTING --}}
      @include('partials.songs_list', [
        'songs'               => $songsVote,
        'userHasVotedToday'   => $userHasVotedToday,
        'showVoteButtons'     => true,
        'hideDisabledButtons' => false,
        'disabledVoteText'    => null,
      ])
    </div>
  </div>
@endif





    {{-- Empty / Weekend view --}}
    @if(!$submissionsOpen && !$votingOpen)
      @if(!empty($isWeekendView) && $isWeekendView)
        <div class="card border-0 shadow-sm mb-0">
          <div class="card-body">
            <h5 class="card-title mb-3">🗓️ Weekend — concursul este în pauză</h5>

            {{-- Monday theme pill (from next scheduled cycle) --}}
            @if($upcomingCycle && $upcomingCycle->theme_text)
              @php
                $parts = preg_split('/\s*—\s*/u', $upcomingCycle->theme_text, 2);
                $cat   = trim($parts[0] ?? '');
                $title = trim($parts[1] ?? $upcomingCycle->theme_text);
                $catDisp = ['csd'=>'CSD','it'=>'ITC','itc'=>'ITC','artisti'=>'Artisti','genuri'=>'Genuri'][strtolower($cat)] ?? $cat;
              @endphp
              <div class="ap-theme-row">
                <div class="ap-left">
                  @if($catDisp !== '')
                    <span class="ap-cat-badge">{{ $catDisp }}</span>
                    <span class="ap-dot">🎯</span>
                    <span class="ap-label">Tema de luni:</span>
                  @else
                    <span class="ap-label">Tema de luni:</span>
                  @endif
                  <span class="ap-title">{{ $title }}</span>
                </div>
                <div class="ap-right"></div>
              </div>
            @else
              <div class="ap-theme-row">
                <div class="ap-left">
                  <span class="ap-label">Tema de luni:</span>
                  <span class="ap-title">— (încă nu a fost aleasă)</span>
                </div>
              </div>
            @endif

            {{-- Weekend notice with the fixed schedule --}}
            <div class="alert alert-info mb-4">
              Înscrierile pentru tema de <strong>luni</strong> se deschid <strong>luni 00:00</strong> și se închid la <strong>19:30</strong>.
              Votul are loc <strong>luni 20:00 → marți 20:00</strong>.
            </div>

            {{-- Friday recap list (read-only, no vote buttons) --}}
            <h6 class="fw-bold mb-2">Recapitulare ultima rundă</h6>
            @if($lastSongs && $lastSongs->isNotEmpty())
              @include('partials.songs_list', [
                'songs'             => $lastSongs,
                'userHasVotedToday' => true,  // hides buttons
                'showVoteButtons'   => false
              ])
            @else
              <div class="alert alert-secondary mb-0">Nu avem încă o rundă încheiată de afișat.</div>
            @endif
          </div>
        </div>
      @else
        <div class="alert alert-info mb-0">
          Nu există concurs activ în acest moment.
          @if(!$isWeekday)
            Nu se ține concurs în weekend (Luni–Vineri).
          @else
            Așteaptă să înceapă un nou ciclu de concurs.
          @endif
        </div>
      @endif
    @endif

    {{-- Sticky “Votează până la …” bar — only if voting open AND user hasn’t voted --}}
    @if($votingOpen && !$userHasVotedToday)
      <a href="#concurs-vote"
         class="btn btn-secondary shadow-lg rounded-full px-4 py-2"
         title="Votează pentru tema de ieri">
        ★ Votează
        @if($cycleVote)
          <span class="ml-2 text-xs opacity-80">
            până la {{ $cycleVote->vote_end_at->timezone(config('app.timezone'))->format('H:i') }}
          </span>
        @endif
      </a>
    @endif
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

  {{-- Winner reminder overlay (only if you’re the winner / test flags) --}}
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
  {{-- Globals used by page JS and theme-like.js --}}
  <script>
    window.songListRoute = "{{ route('concurs.songs.today') }}";
    window.uploadRoute   = "{{ route('concurs.upload') }}";
    window.voteRoute     = "{{ route('concurs.vote') }}";

    // CSRF + endpoint for like toggle
    window.csrfToken = "{{ csrf_token() }}";
    window.routeThemesLikeToggle = "{{ route('themes.like.toggle') }}";
  </script>

  {{-- Core page JS first --}}
  <script src="{{ asset('js/concurs.js') }}"></script>

  {{-- Theme Like (optimistic + server sync) --}}
  <script src="{{ asset('js/theme-like.js') }}"></script>

  {{-- Confetti lib (used by winner modal; guarded in code) --}}
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js" defer></script>

  {{-- YouTube modal wiring --}}
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

  {{-- Winner modal (optional) --}}
  @if($showWinnerModal)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('winnerReminder'); if (!overlay) return;
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
        var overlay = document.getElementById('winnerReminder');
        if (overlay) { overlay.classList.remove('d-none'); overlay.style.display='block'; try{ if(typeof confetti==='function') confetti({ particleCount:250, spread:100 }); }catch(e){} }
        else { window.location.href = "{{ route('concurs.alege-tema.create') }}"; }
      });
    </script>
  @endif
@endpush
