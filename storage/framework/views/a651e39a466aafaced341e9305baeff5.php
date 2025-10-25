<?php $__env->startSection('title', 'Acasă – Auditie Placuta'); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startSection('body_class', 'page-home'); ?>

<?php $__env->startPush('styles'); ?>
  <link rel="stylesheet" href="<?php echo e(asset('assets/css/home.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/home.css'))); ?>">
<?php $__env->stopPush(); ?>

<div id="sc-banner" class="sc-banner banner-bg position-relative">
  <div class="container">
      <div class="banner-content text-center">
          <img class="banner-icon wow fadeInUp"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s"
               src="<?php echo e(asset('assets/images/icons/icon1.png')); ?>"
               alt="icon-image"/>

          <h1 class="banner-title wow fadeInUp"
              data-wow-delay="0.4s"
              data-wow-duration="0.7s">
              Descoperă Muzica Împreună Cu Noi
          </h1>

          <div class="description wow fadeInUp"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s">
              Te așteptăm să îți arăți talentul
          </div>

          
          <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('concurs')); ?>"
               class="banner-btn wow fadeInUp black-shape-big"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s">
                <span class="btn-text">Intră în Concurs</span>
                <span class="hover-shape1"></span>
                <span class="hover-shape2"></span>
                <span class="hover-shape3"></span>
            </a>
          <?php else: ?>
            <a href="<?php echo e(route('register')); ?>"
               class="banner-btn wow fadeInUp black-shape-big"
               data-wow-delay="0.4s"
               data-wow-duration="0.7s">
                <span class="btn-text">Înscrie-te acum</span>
                <span class="hover-shape1"></span>
                <span class="hover-shape2"></span>
                <span class="hover-shape3"></span>
            </a>
          <?php endif; ?>
      </div>
  </div>
</div>

<!-- Banner Section End -->
<?php echo $__env->make('partials.home_leaderboards', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/home.blade.php ENDPATH**/ ?>