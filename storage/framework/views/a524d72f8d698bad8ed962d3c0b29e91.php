

<?php
  // Optional debug: /?new=1 forces the badge
  $forceNew = request('new') === '1';

  // Latest event id (cache first, DB fallback so it works even if CACHE_DRIVER=array)
  $latestEventId = cache()->get('events_latest_id') ?: \App\Models\Events\Event::max('id');

  // What the user has seen (session OR cookie; cookie works for guests too)
  $seenId = session('events_last_seen_id') ?? request()->cookie('events_last_seen_id');

  $hasNewEvents = $forceNew || ($latestEventId && (!$seenId || $latestEventId > (int) $seenId));
?>

<?php if(request('dbg')==='1'): ?>
  <!-- events_latest_id=<?php echo e($latestEventId ?? 'null'); ?>, events_last_seen_id=<?php echo e($seenId ?? 'null'); ?>, hasNewEvents=<?php echo e($hasNewEvents ? '1':'0'); ?> -->
<?php endif; ?>

<link rel="stylesheet" href="<?php echo e(asset('assets/css/nav-new.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/nav-new.css'))); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/css/user-dropdown.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/user-dropdown.css'))); ?>">

<header id="gamfi-header" class="gamfi-header-section transparent-header">
  <div class="menu-area menu-sticky">
    <div class="container">
      <div class="heaader-inner-area d-flex align-items-center justify-content-between">

        
        <div class="gamfi-logo-area d-flex align-items-center">
          <div class="logo">
            <a href="<?php echo e(route('home')); ?>">
              <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="logo">
            </a>
          </div>
        </div>

        
        <div class="header-menu">
          <ul class="nav-menu d-flex align-items-center">
            
            <li class="position-relative">
              <a href="<?php echo e(route('home')); ?>" class="nav-new-anchor"
                 style="--new-top:-10px; --new-right:-22px;">
                Acasa
                <?php if($hasNewEvents): ?>
                  <span class="nav-new-badge">NEW</span>
                <?php endif; ?>
              </a>
              <ul class="sub-menu">
                <li class="position-relative">
                  <a class="dropdown-item nav-new-anchor pe-4" href="<?php echo e(route('events.index')); ?>"
                     style="--new-top:-6px; --new-right:-14px;">
                    Evenimente
                    <?php if($hasNewEvents): ?>
                      <span class="nav-new-badge">NEW</span>
                    <?php endif; ?>
                  </a>
                </li>
                <li><a href="<?php echo e(route('about')); ?>">Despre noi</a></li>
              </ul>
            </li>

            
            <li>
              <a href="<?php echo e(route('concurs')); ?>">Concurs</a>
              <ul class="sub-menu">
                <li><a href="<?php echo e(route('leaderboard.monthly')); ?>">Clasament</a></li>
                <li><a href="<?php echo e(route('winners.index')); ?>">🎖️ Melodii câștigătoare</a></li>
                <li><a href="<?php echo e(route('concurs')); ?>">Rezultate (Arhivă)</a></li>
                <li><a href="<?php echo e(route('concurs.arhiva-teme')); ?>">Arhivă teme</a></li>
                <li><a href="<?php echo e(route('regulament')); ?>">Regulament</a></li>
              </ul>
            </li>

            
            <li class="position-relative">
              <a href="<?php echo e(route('muzica')); ?>" class="nav-new-anchor"
                 style="--new-top:-10px; --new-right:-22px;">
                Muzică
                <?php if($hasNewEvents): ?>
                  <span class="nav-new-badge">NEW</span>
                <?php endif; ?>
              </a>
              <ul class="sub-menu">
                <li class="position-relative">
                  <a class="dropdown-item nav-new-anchor pe-4" href="<?php echo e(route('releases.index')); ?>"
                     style="--new-top:-6px; --new-right:-14px;">
                    Noutăți In muzica
                    <?php if($hasNewEvents): ?>
                      <span class="nav-new-badge">NEW</span>
                    <?php endif; ?>
                  </a>
                </li>
                <li><a href="<?php echo e(route('muzica.artisti')); ?>">Artiști</a></li>
                <li><a href="<?php echo e(route('muzica.genuri')); ?>">Genuri muzicale</a></li>
                <li><a href="<?php echo e(route('muzica.playlists')); ?>">Playlists</a></li>
              </ul>
            </li>

            
            <li class="mega_menu_hov">
              <a href="#">Arena</a>
              <div class="gamfi_mega_menu_sect">
                <div class="gamfi_mega_menu">
                  <div class="container">
                    <div class="mega_menu_content">
                      <div class="menu_column">
                        <h2><a href="<?php echo e(route('abilities.index')); ?>">Abilități</a></h2>
                        <ul>
                          <li><a href="<?php echo e(route('abilitati-disponibile')); ?>">Disponibile</a></li>
                          <li><a href="<?php echo e(route('foloseste-abilitate')); ?>">Folosește abilitate</a></li>
                          <li><a href="<?php echo e(route('cooldown')); ?>">Timp rămas</a></li>
                        </ul>
                      </div>
                      <div class="menu_column">
                        <h2><a href="<?php echo e(route('arena.trivia.joaca-trivia')); ?>">Joacă Trivia</a></h2>
                        <ul>
                          <li><a href="<?php echo e(route('arena.trivia.regulament-trivia')); ?>">Regulament Trivia</a></li>
                          <li><a href="<?php echo e(route('arena.trivia.istoric-trivia')); ?>">Istoric Trivia</a></li>
                        </ul>
                      </div>
                      <div class="menu_column">
                        <h2><a href="<?php echo e(route('arena.misiuni.index')); ?>">Misiuni</a></h2>
                        <ul>
                          <li><a href="<?php echo e(route('arena.misiuni.ghiceste-melodia')); ?>">Ghiceste Melodia</a></li>
                          <li><a href="<?php echo e(route('arena.misiuni.misiuni-zilnice')); ?>">Misiuni Zilnice</a></li>
                          <li><a href="<?php echo e(route('arena.misiuni.provocari')); ?>">Provocări</a></li>
                          <li><a href="<?php echo e(route('arena.misiuni.recompense')); ?>">Recompense</a></li>
                        </ul>
                      </div>
                      <div class="menu_column">
                        <h2><a href="<?php echo e(route('clasamente.index')); ?>">Clasamente</a></h2>
                        <ul>
                          <li><a href="<?php echo e(route('arena.clasamente.clasament-general')); ?>">General</a></li>
                          <li><a href="<?php echo e(route('arena.clasamente.jucatori-de-top')); ?>">Jucători top</a></li>
                          <li><a href="<?php echo e(route('arena.clasamente.jucatori-trivia')); ?>">Trivia top</a></li>
                          <li><a href="<?php echo e(route('arena.clasamente.tema-lunii')); ?>">Tema lunii</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>

            
            <li>
              <a href="<?php echo e(route('forum.home')); ?>">Forum</a>
            </li>

            
            <li>
              <a href="<?php echo e(route('magazin.index')); ?>">Magazin</a>
              <ul class="sub-menu">
                <li><a href="<?php echo e(route('magazin.premium')); ?>">Premium</a></li>
                <li><a href="<?php echo e(route('magazin.produse-disponibile')); ?>">Produse Disponibile</a></li>
                <li><a href="<?php echo e(route('magazin.cumpara-apbucksi')); ?>">Cumpără APbucksi</a></li>
              </ul>
            </li>

            
            <?php if(auth()->guard()->guest()): ?>
              <li class="connect-btn-wrapper">
                <button type="button" class="connect-btn cyberpunk-pass"
                        data-bs-toggle="modal" data-bs-target="#loginModal">
                  <span>Conectează-te</span>
                </button>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        
        <div class="user-menu-container d-flex align-items-center">
          <?php echo $__env->make('user.user-menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        
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
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/partials/header.blade.php ENDPATH**/ ?>