
<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="apMobileNav" aria-labelledby="apMobileNavLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title ap-menu-title" id="apMobileNavLabel">Meniu</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Închide"></button>
  </div>

  <div class="offcanvas-body">
    <ul class="nav flex-column gap-2 fs-6" id="mobileNavList">
      <?php if(View::exists('partials.nav.mobile')): ?>
        <?php echo $__env->make('partials.nav.mobile', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/')); ?>">Acasă</a></li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(\Illuminate\Support\Facades\Route::has('concurs') ? route('concurs') : url('/concurs')); ?>">
            Concurs
          </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/muzica')); ?>">Muzică</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/arena')); ?>">Arena</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/magazin')); ?>">Magazin</a></li>
      <?php endif; ?>
    </ul>

    <div class="mt-3 border-top pt-3">
      <?php if(auth()->guard()->check()): ?>
        <div class="ap-greet">Salut, <?php echo e(auth()->user()->name); ?></div>

        <a href="<?php echo e(route('logout.get')); ?>" class="nav-link logout-link text-danger fw-semibold"
           onclick="event.preventDefault(); document.getElementById('logout-form-main').submit();">
          Deconectează-te
        </a>
        <form id="logout-form-main" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none"><?php echo csrf_field(); ?></form>
      <?php else: ?>
        <button type="button"
                class="ap-btn-neon js-open-login"
                data-bs-dismiss="offcanvas"
                aria-controls="apMobileNav">
          Autentificare
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\partials\mobile_nav.blade.php ENDPATH**/ ?>