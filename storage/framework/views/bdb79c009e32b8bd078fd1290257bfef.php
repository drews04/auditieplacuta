
<?php $__env->startSection('title', 'Tema lunii'); ?>
<?php $__env->startSection('body_class', 'page-tema-lunii'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/concurs.css')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/tema-lunii.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
  <h1 class="mb-3">🏅 Tema lunii — <?php echo e(\Illuminate\Support\Carbon::create($year, $month, 1)->isoFormat('MMMM YYYY')); ?></h1>

  <p class="text-muted small mb-3">
    În caz de egalitate la like-uri: câștigă tema mai veche; dacă e tot egal, câștigă tema cu ID mai mic.
  </p>

  <?php
    $currentMonth = \Carbon\Carbon::create($year, $month, 1);
    $prevMonth = $currentMonth->copy()->subMonth();
    $nextMonth = $currentMonth->copy()->addMonth();
  ?>

  
  <div class="tema-lunii-nav d-flex justify-content-between align-items-center">
    <a href="<?php echo e(route('arena.clasamente.tema-lunii', ['y' => $prevMonth->year, 'm' => $prevMonth->month])); ?>"
       class="ap-btn-neon">
      ← <?php echo e($prevMonth->isoFormat('MMMM YYYY')); ?>

    </a>

    <a href="<?php echo e(route('arena.clasamente.tema-lunii', ['y' => $nextMonth->year, 'm' => $nextMonth->month])); ?>"
       class="ap-btn-neon">
      <?php echo e($nextMonth->isoFormat('MMMM YYYY')); ?> →
    </a>
  </div>
  
<?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <div>
      <span class="ap-badge ap-badge-soft me-2"><?php echo e($row->category ?? '—'); ?></span>
      <strong><?php echo e($row->name); ?></strong>
      <span class="ap-muted ms-2">• Aleasă de: <strong><?php echo e($row->chooser_name); ?></strong></span>
    </div>
    <span class="ap-badge ap-badge-dark">❤️ <?php echo e($row->likes_count); ?></span>
  </li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  <?php if($rows->count() > 1): ?>
    <h5 class="mt-4 mb-2">Top 10 teme ale lunii</h5>
    <ol class="list-group list-group-numbered">
      <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <span class="ap-badge ap-badge-soft me-2"><?php echo e($row->category ?? '—'); ?></span>
            <strong><?php echo e($row->name); ?></strong>
          </div>
          <span class="ap-badge ap-badge-dark">❤️ <?php echo e($row->likes_count); ?></span>
        </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\clasamente\tema-lunii.blade.php ENDPATH**/ ?>