<?php $__env->startSection('title', $release->title.' – Noutăți în muzică'); ?>
<?php $__env->startSection('body_class', 'page-release-show'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/releases.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">

  <a href="<?php echo e(route('releases.week', $release->week_key)); ?>" class="btn btn-neon mb-4">&laquo; Înapoi la săptămâna <?php echo e($release->week_key); ?></a>

  <article class="neon-card hero-release d-flex flex-wrap">
    <div class="hero-poster flex-shrink-0">
      <img src="<?php echo e(asset('storage/'.$release->cover_path)); ?>" alt="<?php echo e($release->title); ?>">
    </div>

    <div class="hero-body p-3 p-md-4 flex-grow-1">
      <h1 class="fw-bold text-neon mb-1"><?php echo e($release->artists->pluck('name')->join(', ')); ?></h1>
      <h2 class="h4 mb-2"><?php echo e($release->title); ?></h2>

      <div class="small text-accent mb-2">
        <?php echo e($release->categories->pluck('name')->join(' | ')); ?>

        <?php if($release->release_date): ?> • <?php echo e($release->release_date->isoFormat('D MMM YYYY')); ?> <?php endif; ?>
      </div>

      <?php if($release->description): ?>
        <div class="release-body"><?php echo nl2br(e($release->description)); ?></div>
      <?php else: ?>
        <p class="opacity-75">Fără descriere.</p>
      <?php endif; ?>
    </div>
  </article>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\releases\show.blade.php ENDPATH**/ ?>