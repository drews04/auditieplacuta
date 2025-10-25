<?php $__env->startSection('title', 'Editează thread - Forum'); ?>
<?php $__env->startSection('body_class', 'page-forum'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/forum.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="forum-container">
  <div class="container">

    
    <div class="forum-header">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1>Editează thread</h1>
          <div class="forum-thread-meta mt-2">
            <span class="forum-thread-author">
              <i class="fas fa-user me-2"></i><?php echo e($thread->user->name ?? 'Utilizator'); ?>

            </span>
            <span class="forum-thread-time">
              <i class="fas fa-clock me-2"></i><?php echo e($thread->created_at->diffForHumans()); ?>

            </span>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="<?php echo e(route('forum.threads.show', $thread->slug)); ?>" class="btn btn-secondary">← Înapoi la thread</a>
        </div>
      </div>
    </div>

    
    <?php if($errors->any()): ?>
      <div class="alert alert-danger mb-3">
        <ul class="mb-0">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <li><?php echo e($e); ?></li> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div>
    <?php endif; ?>

    
    <div class="forum-actions">
      <form method="POST" action="<?php echo e(route('forum.threads.update', $thread->slug)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
          <label for="title" class="form-label forum-label">Titlu</label>
          <input id="title" name="title" type="text"
                 class="form-control forum-input"
                 required minlength="3" maxlength="140"
                 value="<?php echo e(old('title', $thread->title)); ?>">
        </div>

        <div class="mb-3">
          <label for="category_id" class="form-label forum-label">Categorie</label>
          <select id="category_id" name="category_id" class="form-control forum-select">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($cat->id); ?>"
                <?php echo e((int) old('category_id', $thread->category_id) === (int) $cat->id ? 'selected' : ''); ?>>
                <?php echo e($cat->name); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="body" class="form-label forum-label">Conținut</label>
          <textarea id="body" name="body" rows="10"
                    class="form-control forum-textarea"
                    required minlength="5"><?php echo e(old('body', $thread->body)); ?></textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="<?php echo e(route('forum.threads.show', $thread->slug)); ?>" class="btn btn-secondary">Anulează</a>
          <button type="submit" class="btn btn-new-thread">
            <i class="fas fa-save me-2"></i>Salvează
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\forum\threads\edit.blade.php ENDPATH**/ ?>