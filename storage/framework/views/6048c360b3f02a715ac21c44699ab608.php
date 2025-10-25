<?php $__env->startSection('title', 'Noutăți în muzică'); ?>
<?php $__env->startSection('body_class', 'page-releases'); ?>

<?php use Illuminate\Support\Str; ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/releases.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">

  
  <?php $isAdmin = auth()->check() && (auth()->user()->is_admin ?? false); ?>
  <div class="title-bar">
    <h1 class="fw-bold text-neon">Noutăți în muzică</h1>
    <?php if($isAdmin): ?>
      <a href="<?php echo e(route('admin.releases.create')); ?>" class="btn btn-neon btn-sm">+ Adaugă lansare</a>
    <?php endif; ?>
  </div>

  
  <?php if($hero): ?>
    <article class="neon-card hero-release d-flex flex-wrap mb-5">
      <div class="hero-poster flex-shrink-0">
        <img src="<?php echo e($hero->cover_path ? asset('storage/'.$hero->cover_path) : asset('assets/img/placeholder-cover.jpg')); ?>"
             alt="<?php echo e($hero->title); ?>">
      </div>
      <div class="hero-body p-3 p-md-4 flex-grow-1">
       <div class="artist-ribbon"><span><?php echo e($hero->artists->pluck('name')->join(', ')); ?></span></div>

        <h3 class="mb-2"><?php echo e($hero->title); ?></h3>
        <div class="mb-2 text-accent small">
          <?php echo e($hero->categories->pluck('name')->join(' | ')); ?>

        </div>
        <p><?php echo e(Str::limit($hero->description, 250)); ?></p>
        <a href="<?php echo e(route('releases.show',$hero->slug)); ?>" class="btn btn-neon mt-2">Citește mai mult</a>
      </div>
    </article>
  <?php endif; ?>

  
  <?php if($releases->count()): ?>
    <h4 class="text-neon mb-3">Alte lansări din săptămâna <?php echo e($weekKey); ?></h4>

    <?php $__currentLoopData = $releases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <article class="neon-card hero-release d-flex flex-wrap mb-5">
        <div class="hero-poster flex-shrink-0">
          <img src="<?php echo e($r->cover_path ? asset('storage/'.$r->cover_path) : asset('assets/img/placeholder-cover.jpg')); ?>"
               alt="<?php echo e($r->title); ?>">
        </div>
        <div class="hero-body p-3 p-md-4 flex-grow-1">
        <div class="artist-ribbon"><span><?php echo e($hero->artists->pluck('name')->join(', ')); ?></span></div>
          <h3 class="mb-2"><?php echo e($r->title); ?></h3>
          <div class="mb-2 text-accent small">
            <?php echo e($r->categories->pluck('name')->join(' | ')); ?>

          </div>
          <p><?php echo e(Str::limit($r->description, 250)); ?></p>
          <a href="<?php echo e(route('releases.show',$r->slug)); ?>" class="btn btn-neon mt-2">Citește mai mult</a>
        </div>
      </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php else: ?>
    <div class="neon-card p-4 text-center opacity-75">Nicio lansare găsită pentru săptămâna <?php echo e($weekKey); ?></div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\releases\index.blade.php ENDPATH**/ ?>