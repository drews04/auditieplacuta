<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/register.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="guest-container">
  <div class="register-container">

    <div class="logo">
      <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Auditie Placuta">
    </div>

    <h2>Verificare Email</h2>

    <p class="text-center" style="margin-top:-.3rem; margin-bottom:1rem;">
      Ți-am trimis un cod de verificare pe email. Introdu-l mai jos pentru a finaliza înregistrarea.
    </p>

    <?php if($errors->has('verification_code')): ?>
      <div class="alert alert-danger">
        <?php echo e($errors->first('verification_code')); ?>

      </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
      <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('verify.code')); ?>">
      <?php echo csrf_field(); ?>

      <div class="form-group">
        <label for="verification_code">Cod de verificare</label>
        <input
          type="text"
          id="verification_code"
          name="verification_code"
          required
          placeholder="Introdu codul primit pe email">
      </div>

      <button type="submit" class="register-btn">
        Confirmă Codul
      </button>
    </form>

    <div class="resend-link">
      <a href="<?php echo e(route('password.request')); ?>">
        Trimite din nou codul
      </a>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\auth\verify.blade.php ENDPATH**/ ?>