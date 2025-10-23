<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

  <title><?php echo $__env->yieldContent('title', 'Auditie Placuta'); ?></title>

  <?php
    // Helper: build asset URL with cache-busting based on filemtime, safely.
    $cssv = function (string $rel) {
        $full = public_path($rel);
        $ver  = file_exists($full) ? filemtime($full) : time();
        return asset($rel) . '?v=' . $ver;
    };
  ?>

  <!-- Bootstrap (core) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />

  <!-- ===== Global CSS (always) ===== -->
  <link rel="stylesheet" href="<?php echo e($cssv('assets/css/ico-moon-fonts.css')); ?>" />
  <link rel="stylesheet" href="<?php echo e($cssv('assets/css/all.min.css')); ?>" />
  <link rel="stylesheet" href="<?php echo e($cssv('assets/css/nav-new.css')); ?>" />      
  <link rel="stylesheet" href="<?php echo e($cssv('assets/css/style.css')); ?>" />
  <link rel="stylesheet" href="<?php echo e($cssv('assets/css/responsive.css')); ?>" />
  <link rel="stylesheet" href="<?php echo e($cssv('logo.css')); ?>" />
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/modal-neon.css')); ?>?v=<?php echo e(file_exists(public_path('assets/css/modal-neon.css')) ? filemtime(public_path('assets/css/modal-neon.css')) : time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/youtube-modal.css')); ?>">

  <!-- Our tiny core (last so it normalizes sizes & neon look) -->
  <link rel="stylesheet" href="<?php echo e($cssv('assets/css/ap-core.css')); ?>" />

  
  <?php
    $isHome    = request()->is('/');
    $isConcurs = request()->is('concurs*');
    $isForum   = request()->is('forum*');
    $isMuzica  = request()->is('muzica*');
    $isArena   = request()->is('arena*');
    $isMagazin = request()->is('magazin*');
    $isRegul   = request()->is('regulament*') || request()->is('regulament');
    $isAbout   = request()->is('despre*') || request()->is('about*');
    $isEvents  = request()->is('evenimente*') || request()->is('events*');
  ?>

  
  <?php if($isHome): ?>
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/rotating-banner.css')); ?>">
    <?php if(file_exists(public_path('assets/css/slick.css'))): ?>
      <link rel="stylesheet" href="<?php echo e($cssv('assets/css/slick.css')); ?>">
    <?php endif; ?>
    <?php if(file_exists(public_path('assets/css/slick-theme.min.css'))): ?>
      <link rel="stylesheet" href="<?php echo e($cssv('assets/css/slick-theme.min.css')); ?>">
    <?php endif; ?>
  <?php endif; ?>

  
  <?php if($isConcurs): ?>
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/tema-lunii.css')); ?>">
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/theme-like.css')); ?>">
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/concurs.css')); ?>">
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/winner.css')); ?>">
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/leaderboard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/pagination-neon.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/neon-bg.css')); ?>?v=<?php echo e(file_exists(public_path('assets/css/neon-bg.css')) ? filemtime(public_path('assets/css/neon-bg.css')) : time()); ?>">
    <?php if(file_exists(public_path('assets/css/vote-btn.css'))): ?>
      <link rel="stylesheet" href="<?php echo e($cssv('assets/css/vote-btn.css')); ?>">
    <?php endif; ?>
    <?php if(file_exists(public_path('assets/css/alege-tema.css'))): ?>
      <link rel="stylesheet" href="<?php echo e($cssv('assets/css/alege-tema.css')); ?>">
    <?php endif; ?>
  <?php endif; ?>

  
  <?php if($isForum): ?>
    <?php if(file_exists(public_path('assets/css/forum.css'))): ?>
      <link rel="stylesheet" href="<?php echo e($cssv('assets/css/forum.css')); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/pagination-neon.css')); ?>">
  <?php endif; ?>

  
  <?php if($isMuzica): ?>
    
  <?php endif; ?>
  <?php if($isArena): ?>
    
  <?php endif; ?>
  <?php if($isMagazin): ?>
    
  <?php endif; ?>

  
  <?php if($isRegul && file_exists(public_path('assets/css/regulament.css'))): ?>
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/regulament.css')); ?>">
  <?php endif; ?>
  <?php if($isAbout && file_exists(public_path('assets/css/about.css'))): ?>
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/about.css')); ?>">
  <?php endif; ?>
  <?php if($isEvents && file_exists(public_path('assets/css/events.css'))): ?>
    <link rel="stylesheet" href="<?php echo e($cssv('assets/css/events.css')); ?>">
  <?php endif; ?>

  
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="site <?php echo $__env->yieldContent('body_class'); ?>">
  <?php echo $__env->make('partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('partials.mobile_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 

  
  <?php echo $__env->make('concurs.partials.youtube_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  
  <script defer src="<?php echo e(asset('assets/js/youtube-modal.js')); ?>?v=<?php echo e(file_exists(public_path('assets/js/youtube-modal.js')) ? filemtime(public_path('assets/js/youtube-modal.js')) : time()); ?>"></script>

  
  <main class="site-main">
    <?php echo $__env->yieldContent('content'); ?>
  </main>

  <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <!-- JS: load once, at the bottom -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="<?php echo e(asset('assets/js/jquery.min.js')); ?>"></script>
  <script src="<?php echo e(asset('assets/js/plugins.js')); ?>"></script>

  
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="loginModalLabel">Autentificare</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php if(session('success')): ?>
            <div class="custom-alert-success">
              <div class="checkmark-icon">✅</div>
              <div class="success-text"><?php echo e(str_replace(['✓','✅','☑','☐'], '', session('success'))); ?></div>
            </div>
          <?php endif; ?>

          <?php if($errors->has('email')): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-exclamation-circle me-2" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14z"/>
                <path d="M7.002 11a1 1 0 1 0 2 0 1 1 0 0 0-2 0zm.1-5.995a.905.905 0 0 1 1.8 0l-.35 3.5a.552.552 0 0 1-1.1 0l-.35-3.5z"/>
              </svg>
              <div><?php echo e($errors->first('email')); ?></div>
            </div>
          <?php endif; ?>

          <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Parola</label>
              <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 text-end">
              <a href="<?php echo e(route('password.request')); ?>" target="_blank" rel="noopener noreferrer">Ai uitat parola?</a>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Autentifică-te</button>
            </div>
          </form>

          <hr class="my-4">
          <p class="text-center">Nu ai cont? <a href="<?php echo e(route('register')); ?>" target="_blank" rel="noopener noreferrer">Creează unul aici</a></p>
        </div>
      </div>
    </div>
  </div>

  <?php if(session('show_login_modal') || $errors->has('email')): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('loginModal')).show();
      });
    </script>
  <?php endif; ?>

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
      const candidates = document.querySelectorAll('.alert-success, .alert[data-auto-dismiss="true"]');
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

  <script src="<?php echo e(asset('js/mobile-login.js')); ?>?v=<?php echo e(file_exists(public_path('js/mobile-login.js')) ? filemtime(public_path('js/mobile-login.js')) : time()); ?>"></script>

  <?php if(auth()->guard()->check()): ?>
    <?php $isForum = request()->is('forum*'); ?>
    <?php if(!$isForum): ?>
      <div id="reply-pill-root" class="reply-pill-root" aria-live="polite"></div>
      <link rel="stylesheet" href="<?php echo e(asset('assets/css/pill-alert.css')); ?>">
      <script defer src="<?php echo e(asset('js/pill-alert.js')); ?>?v=<?php echo e(file_exists(public_path('js/pill-alert.js')) ? filemtime(public_path('js/pill-alert.js')) : time()); ?>"></script>
    <?php endif; ?>
  <?php endif; ?>

  <script src="<?php echo e(asset('js/user-menu.js')); ?>?v=<?php echo e(file_exists(public_path('js/user-menu.js')) ? filemtime(public_path('js/user-menu.js')) : time()); ?>"></script>

  
  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/layouts/app.blade.php ENDPATH**/ ?>