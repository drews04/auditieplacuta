<?php $__env->startSection('title', 'Despre noi'); ?>
<?php $__env->startSection('body_class', 'page-about'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/about.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5 about-container">

  <div class="neon-card about-hero mb-5">
    <h1 class="display-5 fw-extrabold mb-3 text-neon">Noi suntem Audiție Plăcută</h1>
    <p class="lead mb-0 opacity-90">
      Din dragoste pentru muzică, am transformat un mic grup de prieteni într-o comunitate vie, unde
      descoperim piese noi, votăm preferatele și ne bucurăm împreună de energia pe care ne-o dă muzica bună.
    </p>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="neon-card p-4 h-100">
        <h2 class="h4 fw-bold mb-3">Povestea noastră</h2>
        <p class="mb-2">
          În 2021 am început pe WhatsApp, trimițându-ne melodii „de suflet". Apoi am vrut ceva mai mare:
          o casă pentru toți cei care simt muzica la fel de intens. Așa s-a născut <strong>Audiție Plăcută</strong>.
        </p>
        <p class="mb-0">
          Aici vorbim pe limba universală a notelor, descoperim artiști, ne provocăm în concursul zilnic
          și celebrăm emoțiile pe care doar muzica le știe stârni.
        </p>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="neon-card p-4 h-100">
        <h2 class="h4 fw-bold mb-3">Ce facem aici</h2>
        <ul class="about-list mb-0">
          <li><span>Concurs zilnic</span> – propunem piese, votăm anonim, alegem câștigătorul.</li>
          <li><span>Teme muzicale</span> – în fiecare zi alt vibe, ales de câștigător.</li>
          <li><span>Forum</span> – discutăm idei, recomandări, feedback.</li>
          <li><span>Evenimente</span> – ne vedem la party-uri sau concerte, în lumea reală.</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="neon-divider my-5"></div>

  <div class="neon-card p-4 mb-4">
    <h2 class="h4 fw-bold mb-3">Manifest</h2>
    <p class="mb-2">
      Muzica ne apropie. Fără bariere, fără „gusturi greșite". Doar emoție bună, respect și vibe.
    </p>
    <p class="mb-0">
      Vrem să ne distrăm, să descoperim și să redescoperim. Vrem mai multă muzică bună în fiecare zi — împreună.
    </p>
  </div>

  <div class="text-center">
    <a href="<?php echo e(route('concurs') ?? url('/concurs')); ?>" class="btn btn-neon btn-lg px-4">
      Descoperă concursul
    </a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\pages\about.blade.php ENDPATH**/ ?>