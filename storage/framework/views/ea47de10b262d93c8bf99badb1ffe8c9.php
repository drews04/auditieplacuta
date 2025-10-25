<link rel="stylesheet" href="<?php echo e(asset('assets/css/winners.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/winners.css'))); ?>">

<?php $__env->startSection('title', 'Melodii câștigătoare'); ?>
<?php $__env->startSection('body_class', 'page-winners'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs-winner.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/concurs-winner.css'))); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4 mb-4">

  
  <div class="winners-hero">
    <h1>🎖️ Melodii câștigătoare</h1>
  </div>

  
  
<form method="GET" class="winners-toolbar d-flex align-items-center flex-wrap gap-2">
  <input
    class="form-control form-control-sm winners-input"
    name="q"
    value="<?php echo e($q); ?>"
    placeholder="Caută după melodie, câștigător, temă…"
    aria-label="Căutare"
  />
  <button class="btn btn-success btn-sm fw-bold" type="submit">Caută</button>
  <select name="per" class="form-select form-select-sm">
    <?php $__currentLoopData = [20,30,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option value="<?php echo e($opt); ?>" <?php if($per === $opt): echo 'selected'; endif; ?>><?php echo e($opt); ?> / pagină</option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
</form>



  <?php if($winners->count() === 0): ?>
    <div class="alert alert-secondary mt-2">Nu am găsit rezultate.</div>
  <?php else: ?>
    <div class="winners-card">
      <table class="winners-table">
        <thead>
          <tr>
            <th style="width:120px;">Data</th>
            <th>Tema</th>
            <th>Melodie</th>
            <th style="width:220px;">Câștigător</th>
            <th style="width:90px;">Voturi</th>
            <th style="width:88px;">Link</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $winners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $when = $w->contest_date ?? optional($w->cycle)->vote_end_at;
              $date = $when ? $when->timezone(config('app.timezone'))->format('Y-m-d') : '—';

              $themeRaw = $w->theme->title ?? ($w->cycle->theme_text ?? '—');
              $parts    = preg_split('/\s*—\s*/u', (string)$themeRaw, 2);
              $cat      = trim($parts[0] ?? '');
              $title    = trim($parts[1] ?? $themeRaw);
            ?>
            <tr>
              <td class="text-nowrap"><?php echo e($date); ?></td>

              <td>
                <span class="ap-theme-chip">
                  <?php if($cat !== ''): ?><span><?php echo e($cat); ?></span><?php endif; ?>
                  <span>🎯</span>
                  <span><?php echo e($title); ?></span>
                </span>
              </td>

              <td><?php echo e($w->song->title ?? 'Melodie'); ?></td>

              <td>
                <?php if($w->user): ?>
                  <a class="link-light text-decoration-underline"
                     href="<?php echo e(route('users.wins', ['userId' => $w->user->id])); ?>">
                    <?php echo e($w->user->name); ?>

                  </a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>

              <td><span class="ap-votes-badge"><?php echo e((int) $w->vote_count); ?></span></td>

              <td>
                <?php if(!empty($w->song?->youtube_url)): ?>
                  <a class="ap-yt-link" href="<?php echo e($w->song->youtube_url); ?>" target="_blank" rel="noopener">
                    YouTube
                  </a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>

      
      <div class="winners-pagination">
        <?php echo e($winners->onEachSide(1)->links('vendor.pagination.simple-bootstrap-5')); ?>

      </div>
    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\concurs\winners.blade.php ENDPATH**/ ?>