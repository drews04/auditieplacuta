<?php $__env->startSection('title','Adaugă lansare'); ?>
<?php $__env->startSection('body_class','page-releases'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/releases.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
  <h1 class="fw-bold text-neon text-center mb-4">Adaugă lansare nouă</h1>

  <form class="neon-card p-4" action="<?php echo e(route('admin.releases.store')); ?>" method="post" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <?php if($errors->any()): ?>
      <div class="alert alert-danger"><ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <li><?php echo e($e); ?></li> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label text-accent">Titlu</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="col-md-3">
        <label class="form-label text-accent">Data lansării</label>
        <input type="date" name="release_date" class="form-control" required>
      </div>

      <div class="col-md-3">
        <label class="form-label text-accent">Tip</label>
        <select name="type" class="form-select">
          <option value="Album">Album</option>
          <option value="Single">Single</option>
          <option value="EP">EP</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label text-accent">Artiști (virgulă între nume)</label>
        <input type="text" name="artists" class="form-control" placeholder="ex: TEST ARTIST, Alt Artist">
      </div>

      <div class="col-md-6">
        <label class="form-label text-accent">Categorii (virgulă între nume)</label>
        <input type="text" name="categories" class="form-control" placeholder="ex: Pop, Rock">
      </div>

      <div class="col-md-6">
        <label class="form-label text-accent">Poster / Copertă</label>
        <input type="file" name="cover" class="form-control" accept="image/*">
        <div class="form-text">PNG/JPG/WEBP, max 4MB. Recomandat ≥ 800×800.</div>
      </div>

      <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_highlight" id="is_highlight" value="1">
          <label class="form-check-label" for="is_highlight">Setează ca “Hero” al săptămânii</label>
        </div>
      </div>

      <div class="col-12">
        <label class="form-label text-accent">Descriere</label>
        <textarea name="description" class="form-control" rows="6" placeholder="Scrie descrierea…"></textarea>
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-neon" type="submit">Salvează</button>
      <a class="btn btn-outline-light" href="<?php echo e(route('releases.index')); ?>">Anulează</a>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\releases\create.blade.php ENDPATH**/ ?>