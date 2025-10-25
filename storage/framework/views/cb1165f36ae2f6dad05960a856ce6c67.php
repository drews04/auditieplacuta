<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-winner.css')); ?>?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/song-disqualified.css')); ?>?v=<?php echo e(time()); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/archive.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('title', $archive->theme_category . ' — ' . $archive->theme_name . ' | Arhivă'); ?>
<?php $__env->startSection('body_class', 'page-concurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">

  
  <div class="archive-nav d-flex justify-content-between align-items-center mb-4">
    <?php if($prevArchive): ?>
      <a href="<?php echo e(route('concurs.arhiva.show', ['cycleId' => $prevArchive->cycle_id])); ?>" 
         class="btn btn-outline-primary archive-nav-btn">
        ← Anterior
      </a>
    <?php else: ?>
      <div></div>
    <?php endif; ?>

    <a href="<?php echo e(route('concurs')); ?>" class="btn btn-outline-secondary">
      🏠 Concurs Actual
    </a>

    <?php if($nextArchive): ?>
      <a href="<?php echo e(route('concurs.arhiva.show', ['cycleId' => $nextArchive->cycle_id])); ?>" 
         class="btn btn-outline-primary archive-nav-btn">
        Următor →
      </a>
    <?php else: ?>
      <div></div>
    <?php endif; ?>
  </div>

  
  <div class="text-center mb-5">
    <h1 class="archive-theme-title fw-bold mb-3">
      <?php echo e($archive->theme_category); ?> — <?php echo e($archive->theme_name); ?>

    </h1>
    <p class="archive-date"><?php echo e($formattedDate); ?></p>
    <?php if($archive->theme_likes_count > 0): ?>
      <div class="mt-3">
        <span class="badge" style="background: rgba(255, 0, 100, 0.2); border: 2px solid #ff0064; color: #ff0064; font-size: 16px; padding: 8px 16px;">
          ❤️ <?php echo e($archive->theme_likes_count); ?>

        </span>
      </div>
    <?php endif; ?>
  </div>

  
  <div class="card border-0 mb-4 archive-winner-card">
    <div class="card-body p-4">
      <div class="d-flex align-items-center gap-4">
        <div class="text-center">
          <div class="fs-1 mb-2">🏆</div>
          <?php if($archive->winner_photo_url): ?>
            <img src="<?php echo e($archive->winner_photo_url); ?>" 
                 alt="<?php echo e($archive->winner_name); ?>" 
                 class="archive-winner-photo">
          <?php endif; ?>
        </div>
        <div class="flex-grow-1">
          <h3 class="mb-2 fw-bold" style="color: #16f1d3;"><?php echo e($archive->winner_name); ?></h3>
          <p class="mb-3 fs-5" style="color: rgba(255,255,255,0.8);"><?php echo e($archive->winner_song_title); ?></p>
          <div class="d-flex gap-3 flex-wrap">
            <span class="badge" style="background: rgba(22, 241, 211, 0.2); border: 2px solid #16f1d3; color: #16f1d3; font-size: 14px; padding: 8px 16px;">
              🗳️ <?php echo e($archive->winner_votes); ?> <?php echo e($archive->winner_votes === 1 ? 'vot' : 'voturi'); ?>

            </span>
            <span class="badge" style="background: rgba(255, 215, 0, 0.2); border: 2px solid #ffd700; color: #ffd700; font-size: 14px; padding: 8px 16px;">
              ⭐ <?php echo e($archive->winner_points); ?> puncte
            </span>
          </div>
        </div>
        <div class="d-flex flex-column gap-2">
          <a href="<?php echo e($archive->winner_song_url); ?>" target="_blank" rel="noopener" 
             class="btn" style="background: rgba(22, 241, 211, 0.1); border: 2px solid #16f1d3; color: #16f1d3; font-weight: 600;">
            ▶️ Ascultă pe YouTube
          </a>
          <a href="<?php echo e(route('concurs.arhiva.show', ['cycleId' => $archive->cycle_id])); ?>" 
             class="btn btn-sm" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7);">
             Vezi clasament complet
          </a>
        </div>
      </div>
    </div>
  </div>

  
  <div class="card border-0 mb-4">
    <div class="card-body">
      <h5 class="card-title mb-4" style="color: #16f1d3; font-size: 24px; font-weight: 700;">📊 Clasament Final</h5>
      
      <div class="table-responsive">
        <table class="table table-hover archive-ranking-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Jucător</th>
              <th>Melodie</th>
              <th class="text-center">Voturi</th>
              <th class="text-center">Puncte</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="archiveRankings">
            <?php $__currentLoopData = $rankings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ranking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr class="archive-ranking-row <?php echo e($index >= 10 ? 'archive-hidden' : ''); ?>" data-rank="<?php echo e($ranking['rank']); ?>">
                <td class="fw-bold"><?php echo e($ranking['rank']); ?></td>
                <td><?php echo e($ranking['user_name']); ?></td>
                <td><?php echo e($ranking['song_title']); ?></td>
                <td class="text-center"><?php echo e($ranking['votes']); ?></td>
                <td class="text-center fw-bold"><?php echo e($ranking['points']); ?></td>
                <td>
                  <a href="<?php echo e($ranking['youtube_url']); ?>" target="_blank" rel="noopener" 
                     class="btn btn-sm btn-outline-primary">
                    ▶️
                  </a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>

      <?php if(count($rankings) > 10): ?>
        <div class="text-center mt-3">
          <button type="button" id="showMoreRankings" class="btn btn-outline-secondary">
            Mai mult ▼
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  
  <?php if($winnerPosters->count() > 0): ?>
    <div class="card border-0 mb-4">
      <div class="card-body">
        <h5 class="card-title mb-4" style="color: #16f1d3; font-size: 20px; font-weight: 700;">🎨 Alte teme câștigate de <?php echo e($archive->winner_name); ?></h5>
        
        <div class="archive-poster-carousel-container">
          <div class="archive-poster-carousel" id="posterCarousel">
            <?php $__currentLoopData = $winnerPosters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poster): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="archive-poster-item">
                <a href="<?php echo e(route('concurs.arhiva.show', ['cycleId' => $poster->cycle_id])); ?>" 
                   class="archive-poster-link">
                  <?php if($poster->poster_url): ?>
                    <img src="<?php echo e($poster->poster_url); ?>" 
                         alt="<?php echo e($poster->theme_name); ?>" 
                         class="archive-poster-img">
                  <?php else: ?>
                    <div class="archive-poster-placeholder">
                      <span><?php echo e($poster->theme_name); ?></span>
                    </div>
                  <?php endif; ?>
                  <div class="archive-poster-overlay">
                    <small><?php echo e($poster->theme_name); ?></small>
                  </div>
                </a>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Show more rankings
document.getElementById('showMoreRankings')?.addEventListener('click', function() {
  const hiddenRows = document.querySelectorAll('.archive-ranking-row.archive-hidden');
  hiddenRows.forEach(row => row.classList.remove('archive-hidden'));
  this.style.display = 'none';
});

// Horizontal scroll for poster carousel (drag to scroll)
const carousel = document.getElementById('posterCarousel');
if (carousel) {
  let isDown = false;
  let startX;
  let scrollLeft;

  carousel.addEventListener('mousedown', (e) => {
    isDown = true;
    carousel.classList.add('active');
    startX = e.pageX - carousel.offsetLeft;
    scrollLeft = carousel.scrollLeft;
  });

  carousel.addEventListener('mouseleave', () => {
    isDown = false;
    carousel.classList.remove('active');
  });

  carousel.addEventListener('mouseup', () => {
    isDown = false;
    carousel.classList.remove('active');
  });

  carousel.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - carousel.offsetLeft;
    const walk = (x - startX) * 2;
    carousel.scrollLeft = scrollLeft - walk;
  });
}
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\concurs\arhiva.blade.php ENDPATH**/ ?>