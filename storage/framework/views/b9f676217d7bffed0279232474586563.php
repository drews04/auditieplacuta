<?php $__env->startSection('title', 'Arhivă Concurs'); ?>
<?php $__env->startSection('body_class', 'page-concurs-archive'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
  <h1 class="mb-4 fw-bold">Arhivă Concurs</h1>

  <?php $__empty_1 = true; $__currentLoopData = $cycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
      $w = $c->winner_snapshot ?? null;
    ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div class="mb-2">
          <div class="small text-muted">
            Încheiat: <?php echo e($c->vote_end_at->timezone(config('app.timezone'))->format('D, d M Y H:i')); ?>

          </div>
          <div class="fw-semibold">Tema: <?php echo e($c->theme_text ?? '—'); ?></div>
          <?php if($w): ?>
            <div class="mt-1">
              🏆 <?php echo e($w->song->title ?? 'Melodie'); ?>

              <span class="text-muted">de</span>
              <span class="fw-semibold"><?php echo e($w->user->name ?? 'necunoscut'); ?></span>
              <span class="ms-2 badge bg-success"><?php echo e($w->vote_count); ?> voturi</span>
            </div>
          <?php else: ?>
            <div class="mt-1 text-muted">Rezultate în curs de validare…</div>
          <?php endif; ?>
        </div>

        <a class="btn btn-outline-info"
           href="<?php echo e(route('concurs.arhiva.show', $c->vote_end_at->toDateString())); ?>">
          Detalii & clasament
        </a>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="alert alert-info">Nu există încă runde încheiate.</div>
  <?php endif; ?>

  <div class="mt-3">
    <?php echo e($cycles->links()); ?>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\concurs\archive\index.blade.php ENDPATH**/ ?>