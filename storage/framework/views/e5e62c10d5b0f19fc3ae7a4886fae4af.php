<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/register.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="guest-container">
  <div class="register-container">
    <div class="logo">
      <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Auditie Placuta">
    </div>

    <h2>Resetare parolă</h2>

    <?php if($errors->any()): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if(session('status')): ?>
      <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if(session('success')): ?>
      <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('password.email')); ?>">
      <?php echo csrf_field(); ?>

      <div class="form-group">
        <label for="email">Adresă de email</label>
        <input
          type="email"
          id="email"
          name="email"
          required
          placeholder="exemplu@email.com"
          value="<?php echo e(old('email')); ?>"
        >
      </div>

      <button type="submit" class="register-btn">
        Trimite codul de resetare
      </button>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>