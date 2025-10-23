<?php if($paginator->hasPages()): ?>
<nav class="ap-pagination" role="navigation" aria-label="Pagination">
  <ul class="pagination neon-pagination">

    
    <?php if($paginator->onFirstPage()): ?>
      <li class="page-item prev disabled" aria-disabled="true" aria-label="<?php echo app('translator')->get('pagination.previous'); ?>">
        <span class="page-link neon-page">&lsaquo;</span>
      </li>
    <?php else: ?>
      <li class="page-item prev">
        <a class="page-link neon-page" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="<?php echo app('translator')->get('pagination.previous'); ?>">&lsaquo;</a>
      </li>
    <?php endif; ?>

    
    <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if(is_string($element)): ?>
        <li class="page-item disabled"><span class="page-link neon-page"><?php echo e($element); ?></span></li>
      <?php endif; ?>
      <?php if(is_array($element)): ?>
        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php if($page == $paginator->currentPage()): ?>
            <li class="page-item active"><span class="page-link neon-page is-active"><?php echo e($page); ?></span></li>
          <?php else: ?>
            <li class="page-item"><a class="page-link neon-page" href="<?php echo e($url); ?>"><?php echo e($page); ?></a></li>
          <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <?php if($paginator->hasMorePages()): ?>
      <li class="page-item next">
        <a class="page-link neon-page" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="<?php echo app('translator')->get('pagination.next'); ?>">&rsaquo;</a>
      </li>
    <?php else: ?>
      <li class="page-item next disabled" aria-disabled="true" aria-label="<?php echo app('translator')->get('pagination.next'); ?>">
        <span class="page-link neon-page">&rsaquo;</span>
      </li>
    <?php endif; ?>

  </ul>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/vendor/pagination/neon.blade.php ENDPATH**/ ?>