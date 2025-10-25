<?php $__env->startPush('styles'); ?>
  
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-winner.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/concurs-winner.css'))); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/vote-btn.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/vote-btn.css'))); ?>">
  
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/theme-like.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/theme-like.css'))); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-override.css')); ?>?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-mobile.css')); ?>?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/song-disqualified.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>


<link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=<?php echo e(filemtime(public_path('css/style.css'))); ?>">

<?php $__env->startSection('title', 'Votează — Concurs'); ?>
<?php $__env->startSection('body_class', 'page-concurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">

  <h1 class="mb-3 text-center" style="font-weight:800; letter-spacing:1px;">🔊 Votează melodiile de ieri</h1>
  <p class="text-center mb-4">Ascultă melodiile participante și votează-ți favorita!</p>

  
  <?php if($cycleVote && isset($songsVote)): ?>
    <?php
      $tz            = config('app.timezone', 'Europe/Bucharest');
      $voteOpensAtTZ = $voteOpensAt ? \Carbon\Carbon::parse($voteOpensAt)->timezone($tz) : null;
      $voteClosesAt  = ($cycleVote && $cycleVote->vote_end_at) ? \Carbon\Carbon::parse($cycleVote->vote_end_at)->timezone($tz) : null;

      // Parse "Category - Theme Name" from theme_text (hyphen, not em dash)
      $parts = $cycleVote->theme_text ? preg_split('/\s*[-—]\s*/u', (string)$cycleVote->theme_text, 2) : [];
      $cat   = trim($parts[0] ?? '');
      $title = trim($parts[1] ?? ($cycleVote->theme_text ?? '-'));

      // Normalize badge text
      $catDisp = [
        'csd' => 'CSD', 'it' => 'ITC', 'itc' => 'ITC',
        'artisti' => 'Artiști', 'genuri' => 'Genuri',
      ][mb_strtolower($cat)] ?? mb_strtoupper($cat);

      // Theme likes (now queried from database)
      $voteThemeId    = $voteTheme->id ?? 0;
      $voteLikesCount = $voteTheme->likes_count ?? 0;
      $voteLiked      = $voteTheme->liked_by_me ?? false;

      // Preview flag — when theme chosen but voting not yet opened (legacy, kept for safety)
      $isPreVote = !empty($preVote) && $preVote;
    ?>

    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <h5 class="card-title mb-2 d-flex align-items-center gap-2">
          ★ Votează
          <?php if(!empty($votingOpen) && $votingOpen): ?>
            <span class="badge text-bg-success ap-badge-clear">Deschis până la <?php echo e($voteClosesAt?->format('H:i') ?? '20:00'); ?></span>
          <?php elseif($isPreVote): ?>
            <span class="badge text-bg-secondary">
              Previzualizare — începe la <?php echo e($voteOpensAtTZ?->format('H:i') ?? '00:00'); ?>

            </span>
          <?php else: ?>
            <span class="badge text-bg-secondary">Vot închis</span>
          <?php endif; ?>

          <?php if(auth()->guard()->check()): ?>
            <?php if(!empty($submissionsOpen) && $submissionsOpen): ?>
              <a href="<?php echo e(route('concurs.upload.page')); ?>" class="btn btn-outline-primary btn-sm ms-auto">⬆️ Încarcă</a>
            <?php endif; ?>
          <?php endif; ?>
        </h5>

        
        <?php if($cycleVote->theme_text): ?>
          <div class="ap-theme-row ap-theme-section">
            <div class="ap-left">
              <?php if($cat !== ''): ?> <span class="ap-cat-badge"><?php echo e($catDisp); ?></span><span class="ap-dot">🎯</span> <?php endif; ?>
              <span class="ap-label">Tema:</span>
              <span class="ap-title"><?php echo e($title); ?></span>

              
              <?php if($voteThemeId): ?>
                <div class="dropdown d-inline-block theme-like-wrap ms-2">
                  <button type="button"
                          class="btn btn-sm theme-like <?php echo e($voteLiked ? 'is-liked' : ''); ?>"
                          data-likeable-type="contest"
                          data-likeable-id="<?php echo e($voteThemeId); ?>"
                          data-liked="<?php echo e($voteLiked ? 1 : 0); ?>"
                          data-count="<?php echo e((int)$voteLikesCount); ?>"
                          <?php if(auth()->guard()->guest()): ?> data-auth="0" <?php endif; ?>>
                    <i class="heart-icon"></i>
                    <span class="like-count"><?php echo e((int)$voteLikesCount); ?></span>
                  </button>
                  <ul class="dropdown-menu theme-like-dropdown p-2 shadow-sm" style="min-width:180px;">
                    <?php $__empty_1 = true; $__currentLoopData = $voteTheme->likes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $like): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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

        
        <?php if($isPreVote): ?>
          <div class="alert alert-info text-center fw-semibold mb-3">
            Votul începe la <strong><?php echo e($voteOpensAtTZ ? $voteOpensAtTZ->format('H:i') : '00:00'); ?></strong>.
          </div>
        <?php endif; ?>

        
        <?php echo $__env->make('partials.songs_list', [
          'songs'               => $songsVote,
          'showVoteButtons'     => (!empty($votingOpen) && $votingOpen) && !($userHasVotedToday ?? false),
          'hideDisabledButtons' => $isPreVote ? true : false,
          'disabledVoteText'    => $isPreVote
                                    ? 'Votul începe la ' . ($voteOpensAtTZ ? $voteOpensAtTZ->format('H:i') : '00:00')
                                    : ((!empty($votingOpen) && $votingOpen) ? 'Ai votat deja' : 'Vot închis'),
          'userHasVotedToday'   => $userHasVotedToday ?? false,
          'votedSongId'         => $votedSongId ?? null,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(!( !empty($votingOpen) && $votingOpen ) && !$isPreVote): ?>
          <div class="small text-muted mt-2">Votul s-a închis la <?php echo e($voteClosesAt?->format('H:i') ?? '20:00'); ?>.</div>
        <?php endif; ?>
      </div>
    </div>

  <?php else: ?>
    <div class="card border-0 shadow-sm mb-4 ap-neon">
      <div class="card-body">
        <h5 class="card-title mb-2">★ Votează</h5>
        <div class="text-muted">Nu există melodii de votat pentru această rundă.</div>
      </div>
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script>
    window.voteRoute = "<?php echo e(route('concurs.vote')); ?>";
    window.csrfToken = "<?php echo e(csrf_token()); ?>";
    window.routeThemesLikeToggle = "<?php echo e(route('themes.like.toggle')); ?>";
    window.concursFlags = {
      votingOpen: <?php echo (!empty($votingOpen) && $votingOpen) ? 'true' : 'false'; ?>,
      isPreVote:  <?php echo (!empty($preVote) && $preVote) ? 'true' : 'false'; ?>

    };
  </script>

  <script src="<?php echo e(asset('js/concurs.js')); ?>" defer></script>
  <script src="<?php echo e(asset('js/theme-like.js')); ?>?v=<?php echo e(filemtime(public_path('js/theme-like.js'))); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\concurs\vote.blade.php ENDPATH**/ ?>