
<li class="nav-item"><a class="nav-link text-white" href="<?php echo e(url('/')); ?>">Acasă</a></li>

<li class="nav-item">
  <a class="nav-link text-white"
     href="<?php echo e(\Illuminate\Support\Facades\Route::has('concurs') ? route('concurs') : url('/concurs')); ?>">
    Concurs
  </a>
</li>

<li class="nav-item"><a class="nav-link text-white" href="<?php echo e(url('/muzica')); ?>">Muzică</a></li>
<li class="nav-item"><a class="nav-link text-white" href="<?php echo e(url('/arena')); ?>">Arena</a></li>
<li class="nav-item"><a class="nav-link text-white" href="<?php echo e(url('/forum')); ?>">Forum</a></li>
<li class="nav-item"><a class="nav-link text-white" href="<?php echo e(url('/magazin')); ?>">Magazin</a></li>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\partials\nav\mobile.blade.php ENDPATH**/ ?>