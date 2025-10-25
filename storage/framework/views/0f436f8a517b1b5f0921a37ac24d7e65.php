<?php $__env->startSection('title', 'Editează răspunsul - Forum'); ?>
<?php $__env->startSection('body_class', 'page-forum'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/forum.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="forum-container">
  <div class="container">

    <!-- Header -->
    <div class="forum-header">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1>Editează răspunsul</h1>
          <div class="forum-thread-meta mt-2">
            <span class="forum-thread-author">
              <i class="fas fa-user me-2"></i><?php echo e($post->user->name ?? 'Utilizator'); ?>

            </span>
            <span class="forum-thread-time">
              în thread: <strong><?php echo e($post->thread->title); ?></strong>
            </span>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="<?php echo e(route('forum.threads.show', $post->thread->slug)); ?>#post-<?php echo e($post->id); ?>" class="btn btn-new-thread">
            <i class="fas fa-arrow-left me-2"></i>Înapoi la thread
          </a>
        </div>
      </div>
    </div>

    <!-- Errors -->
    <?php if($errors->any()): ?>
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($e); ?></li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Edit form -->
    <div class="forum-actions">
      <form method="POST" action="<?php echo e(route('forum.posts.update', $post)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
          <label class="form-label forum-label">Conținut</label>
          <textarea
            name="body"
            class="form-control forum-textarea"
            rows="6"
            required
            minlength="2"
          ><?php echo e(old('body', $post->body)); ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
          <a href="<?php echo e(route('forum.threads.show', $post->thread->slug)); ?>#post-<?php echo e($post->id); ?>" class="btn btn-secondary">
            Anulează
          </a>
          <button type="submit" class="btn btn-new-thread">
            <i class="fas fa-save me-2"></i>Salvează
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\forum\posts\edit.blade.php ENDPATH**/ ?>