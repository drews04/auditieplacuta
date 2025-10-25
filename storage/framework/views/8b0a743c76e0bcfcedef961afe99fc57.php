<?php $__env->startSection('title', 'Adaugă eveniment'); ?>
<?php $__env->startSection('body_class', 'page-events-create'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/events.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="events-page-wrap">
  <div class="container py-5">
    <div class="neon-card p-4">
      <h1 class="h4 fw-bold mb-4">Adaugă eveniment</h1>
      <form method="POST" action="<?php echo e(route('events.store')); ?>" enctype="multipart/form-data" class="event-form">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label">Titlu</label>
          <input type="text" name="title" class="form-control" value="<?php echo e(old('title')); ?>" required maxlength="160">
          <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Data evenimentului (opțional)</label>
          <input type="date" name="event_date" class="form-control" value="<?php echo e(old('event_date')); ?>">
          <?php $__errorArgs = ['event_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Poster (JPG/PNG/WebP, max 8MB)</label>
          <input type="file" name="poster" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
          <?php $__errorArgs = ['poster'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-4">
          <label class="form-label">Descriere (opțional)</label>
          <textarea name="body" rows="6" class="form-control" maxlength="50000" placeholder="Detalii: locație, oră, preț bilet etc."><?php echo e(old('body')); ?></textarea>
          <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button class="btn btn-neon">Salvează</button>
      </form>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\evenimente\create.blade.php ENDPATH**/ ?>