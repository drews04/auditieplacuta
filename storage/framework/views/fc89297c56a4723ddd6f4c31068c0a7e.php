<?php $__env->startSection('title', 'Evenimente – Auditie Placuta'); ?>
<?php $__env->startSection('body_class', 'page-evenimente'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/evenimente.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/evenimente.css'))); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.in-constructie', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\acasa\evenimente.blade.php ENDPATH**/ ?>