

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/register.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/register.css'))); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="guest-container">
  <div class="register-container">

    <div class="logo">
      <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Auditie Placuta">
    </div>

    <h2>Înregistrare</h2>

    <?php if($errors->any()): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if(session('verification_message')): ?>
      <div class="alert alert-success">
        <?php echo e(session('verification_message')); ?>

      </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('register')); ?>">
      <?php echo csrf_field(); ?>

      <div class="form-group">
        <label for="name">Nume</label>
        <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" required placeholder="Acesta va fi numele tău afișat pe site">
      </div>

      <div class="form-group">
        <label for="email">Adresă de email</label>
        <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required placeholder="exemplu@email.com">
      </div>

      <div class="form-group">
        <label for="password">Parolă</label>
        <input type="password" id="password" name="password" required placeholder="Alege o parolă sigură">
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirmă parola</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Rescrie parola">
      </div>

      <button type="submit" class="register-btn">Înregistrează-te</button>
    </form>

  </div>
</div>
<?php $__env->stopSection(); ?>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\auth\register.blade.php ENDPATH**/ ?>