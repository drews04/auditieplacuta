

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/register.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="guest-container">
  <div class="register-container text-center">

    <div class="logo mb-3">
      <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Auditie Placuta">
    </div>

    <h2>Cont creat cu succes!</h2>

    <div class="alert alert-success mt-3">
      ✅ Contul tău a fost creat! Te poți autentifica acum.
    </div>

  </div>
</div>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('loginModal');
    if (modal) {
      new bootstrap.Modal(modal).show();
    }
  });
</script>
<?php $__env->stopSection(); ?>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\auth\register-succes.blade.php ENDPATH**/ ?>