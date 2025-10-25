<?php if($paginator->hasPages()): ?>
<nav class="ap-pagination" role="navigation" aria-label="Pagination">
    <ul class="pagination neon-pagination">
        
        <?php if($paginator->onFirstPage()): ?>
            <li class="page-item disabled" aria-disabled="true"><span class="page-link neon-page">&lsaquo;</span></li>
        <?php else: ?>
            <li class="page-item"><a class="page-link neon-page" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">&lsaquo;</a></li>
        <?php endif; ?>

        
        <li class="page-item disabled">
            <span class="page-link neon-page"><?php echo e($paginator->currentPage()); ?> / <?php echo e($paginator->lastPage()); ?></span>
        </li>

        
        <?php if($paginator->hasMorePages()): ?>
            <li class="page-item"><a class="page-link neon-page" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">&rsaquo;</a></li>
        <?php else: ?>
            <li class="page-item disabled" aria-disabled="true"><span class="page-link neon-page">&rsaquo;</span></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\vendor\pagination\neon-simple.blade.php ENDPATH**/ ?>