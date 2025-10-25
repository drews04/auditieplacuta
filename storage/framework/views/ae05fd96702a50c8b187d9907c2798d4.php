

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/register.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="guest-container">
  <div class="register-container">

    <div class="logo">
      <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Auditie Placuta">
    </div>

    <h2>Resetează parola</h2>

    <?php if($errors->any()): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
      <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('status')): ?>
      <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('password.update')); ?>">
      <?php echo csrf_field(); ?>

      <div class="form-group">
        <label for="code">Cod primit prin email</label>
        <input
          type="text"
          id="code"
          name="code"
          required
          placeholder="Introdu codul primit pe email">
      </div>

      <div class="form-group">
        <label for="new_password">Parolă nouă</label>
        <input
          type="password"
          id="new_password"
          name="new_password"
          required
          placeholder="Alege o parolă sigură">
      </div>

      <div class="form-group">
        <label for="new_password_confirmation">Confirmă parola</label>
        <input
          type="password"
          id="new_password_confirmation"
          name="new_password_confirmation"
          required
          placeholder="Rescrie parola">
      </div>

      <button type="submit" class="register-btn">
        Resetează parola
      </button>
    </form>

    <div class="resend-link">
      <a href="<?php echo e(route('password.request')); ?>">Trimite alt cod</a>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>
    <?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>