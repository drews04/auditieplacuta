<?php $__env->startSection('title', 'Evenimente'); ?>
<?php $__env->startSection('body_class', 'page-events'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/events.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="events-page-wrap">
  <div class="container py-5">
    <div class="events-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h1 class="mb-0 fw-bold text-neon">Evenimente</h1>
      <?php if(auth()->guard()->check()): ?>
        <a href="<?php echo e(route('events.create')); ?>" class="btn btn-neon events-add-btn">+ Adaugă eveniment</a>
      <?php endif; ?>
    </div>

  <?php if(session('success')): ?>
    <div class="alert alert-neon mb-4"><?php echo e(session('success')); ?></div>
  <?php endif; ?>

  <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <article class="event-card neon-card mb-5">
      <div class="event-poster-wrap">
      <img class="event-poster" src="<?php echo e(Storage::url($ev->poster_path)); ?>" alt="<?php echo e($ev->title); ?>">

      </div>
      <div class="p-3 p-md-4">
        <h2 class="h4 fw-bold mb-2"><?php echo e($ev->title); ?></h2>
        <?php if($ev->event_date): ?>
          <div class="small text-muted mb-2">Data: <?php echo e(\Illuminate\Support\Carbon::parse($ev->event_date)->isoFormat('D MMM YYYY')); ?></div>
        <?php endif; ?>
        <?php if($ev->body): ?>
          <div class="event-body"><?php echo nl2br(e($ev->body)); ?></div>
        <?php endif; ?>
        <div class="small text-muted mt-3">Adăugat de <?php echo e($ev->user->name ?? 'utilizator'); ?> • <?php echo e($ev->created_at->diffForHumans()); ?></div>
      </div>
    </article>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="neon-card p-4 text-center opacity-75">Niciun eveniment încă.</div>
  <?php endif; ?>

  <div class="mt-4"><?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $events]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($events)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $attributes = $__attributesOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $component = $__componentOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__componentOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?></div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\evenimente\index.blade.php ENDPATH**/ ?>