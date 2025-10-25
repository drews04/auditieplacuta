<?php $__env->startPush('styles'); ?>
  
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-winner.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/concurs-winner.css'))); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/vote-btn.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/vote-btn.css'))); ?>">
  
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/theme-like.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/theme-like.css'))); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-override.css')); ?>?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-mobile.css')); ?>?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/song-disqualified.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>


<link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=<?php echo e(filemtime(public_path('css/style.css'))); ?>">

<?php $__env->startSection('title', 'Încarcă — Concurs'); ?>
<?php $__env->startSection('body_class', 'page-concurs page-neon'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">

  
  <?php if(session('error')): ?>
    <div id="ap-toast" class="alert alert-danger text-center fw-semibold mb-4">
      <?php echo e(session('error')); ?>

    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const t = document.getElementById('ap-toast');
        if (t) { setTimeout(()=>t.classList.add('fade'),4500); setTimeout(()=>t.remove(),5000); }
      });
    </script>
  <?php endif; ?>
  <?php if(session('status')): ?>
    <div id="ap-toast-ok" class="alert alert-success text-center fw-semibold mb-4">
      <?php echo e(session('status')); ?>

    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const t = document.getElementById('ap-toast-ok');
        if (t) { setTimeout(()=>t.classList.add('fade'),4500); setTimeout(()=>t.remove(),5000); }
      });
    </script>
  <?php endif; ?>

  <h1 class="mb-3 text-center fw-bold" style="letter-spacing:1px;">⬆️ Încarcă melodia pentru azi</h1>
  <p class="text-center mb-4">Adaugă linkul YouTube și intră în concursul de azi.</p>

  
  <?php
    $tz = config('app.timezone', 'Europe/Bucharest');
    $submitClosesAt = ($cycleSubmit && $cycleSubmit->submit_end_at) ? \Carbon\Carbon::parse($cycleSubmit->submit_end_at)->timezone($tz) : null;
  ?>

  <div class="card border-0 shadow-sm mb-4 ap-neon">
    <div class="card-body">
      <h5 class="card-title mb-2 d-flex align-items-center gap-2">
        ★ Încarcă
        <?php if(!empty($submissionsOpen) && $submissionsOpen): ?>
          <span class="badge text-bg-success ap-badge-clear">Deschis până la <?php echo e($submitClosesAt?->format('H:i') ?? '20:00'); ?></span>
        <?php else: ?>
          <span class="badge text-bg-secondary">Închis</span>
        <?php endif; ?>
      </h5>

      
      <?php if($cycleSubmit && $cycleSubmit->theme_text): ?>
    <?php
      // Parse "Category - Theme Name" from theme_text (hyphen, not em dash)
      $parts = preg_split('/\s*[-—]\s*/u', (string)$cycleSubmit->theme_text, 2);
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? ($cycleSubmit->theme_text ?? ''));

      // Normalize badge label
      $catDisp = [
        'csd' => 'CSD', 'it' => 'ITC', 'itc' => 'ITC',
        'artisti' => 'Artiști', 'genuri' => 'Genuri',
      ][mb_strtolower($cat)] ?? mb_strtoupper($cat);

      // Theme likes (now queried from database)
      $submitThemeId    = $submitTheme->id ?? 0;
      $submitLikesCount = $submitTheme->likes_count ?? 0;
      $submitLiked      = $submitTheme->liked_by_me ?? false;
    ?>
    <div class="ap-theme-row ap-theme-section">
      <div class="ap-left">
        <?php if($cat !== ''): ?> <span class="ap-cat-badge"><?php echo e($catDisp); ?></span><span class="ap-dot">🎯</span> <?php endif; ?>
        <span class="ap-label">Tema:</span>
        <span class="ap-title"><?php echo e($title); ?></span>

        
        <?php if($submitThemeId): ?>
          <div class="dropdown d-inline-block theme-like-wrap ms-2">
            <button type="button"
                    class="btn btn-sm theme-like <?php echo e($submitLiked ? 'is-liked' : ''); ?>"
                    data-likeable-type="contest"
                    data-likeable-id="<?php echo e($submitThemeId); ?>"
                    data-liked="<?php echo e($submitLiked ? 1 : 0); ?>"
                    data-count="<?php echo e((int)$submitLikesCount); ?>"
                    <?php if(auth()->guard()->guest()): ?> data-auth="0" <?php endif; ?>>
              <i class="heart-icon"></i>
              <span class="like-count"><?php echo e((int)$submitLikesCount); ?></span>
            </button>
            <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
              <?php $__empty_1 = true; $__currentLoopData = ($submitTheme->likes ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $like): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="small text-muted">❤️ <?php echo e($like->user->name); ?></li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="small text-muted">Niciun like încă</li>
              <?php endif; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

      
      <?php if(auth()->guard()->check()): ?>
        <?php $allowUploadNow = $submissionsOpen && !$userHasUploadedToday; ?>
        <?php if($allowUploadNow): ?>
          <div class="mb-3 ap-upload-form-wrapper">
            <h6 class="fw-bold mb-2">📤 Înscrie-ți melodia (YouTube URL)</h6>
            <form id="song-upload-form" action="<?php echo e(route('concurs.upload')); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <div class="row g-2">
                <div class="col-md-9">
                  <input type="url" name="youtube_url" id="youtube_url"
                         class="form-control" placeholder="https://www.youtube.com/watch?v=…" required>
                </div>
                <div class="col-md-3 d-grid">
                  <button type="submit" class="btn btn-success">Trimite</button>
                </div>
              </div>
            </form>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      
      <?php echo $__env->make('partials.songs_list', [
        'songs' => $songsSubmit,
        'userHasVotedToday'=>true,
        'showVoteButtons'=>false,
        'hideVoteStatus'=>true,
        'hideDisabledButtons'=>true,
      ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
  </div>
</div>


<div class="modal fade" id="youtubeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header border-0">
        <h5 class="modal-title">Redă melodia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="ratio ratio-16x9">
          <iframe id="ytFrame" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
      <div class="modal-footer border-0">
        <a id="ytOpenLink" href="#" target="_blank" rel="noopener" class="btn btn-outline-info">Vezi pe YouTube</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script>
    window.skipInitialLoad = true;
    // window.songListRoute not needed - JS has fallback
    window.uploadRoute   = "<?php echo e(route('concurs.upload')); ?>";
    window.voteRoute     = "<?php echo e(route('concurs.vote')); ?>";
    window.csrfToken     = "<?php echo e(csrf_token()); ?>";
    // endpoint used by theme-like.js
    window.routeThemesLikeToggle = "<?php echo e(route('themes.like.toggle')); ?>";
  </script>

  
  <script src="<?php echo e(asset('js/concurs.js')); ?>" defer></script>
  
  <script src="<?php echo e(asset('js/theme-like.js')); ?>?v=<?php echo e(filemtime(public_path('js/theme-like.js'))); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/concurs/upload.blade.php ENDPATH**/ ?>