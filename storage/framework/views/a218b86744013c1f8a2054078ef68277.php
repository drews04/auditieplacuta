<?php $__env->startSection('content'); ?>
<?php
  // Scope flags (now includes POSITIONS)
  $scope   = $scope ?? request('scope', 'alltime'); // default ALL-TIME
  $isPos   = $scope === 'positions';
  $isAll   = $scope === 'alltime';
  $isMonth = $scope === 'monthly';
  $isYear  = $scope === 'yearly';

  // Filters (controller may pass them; otherwise read from query)
  $ym = $ym ?? request('ym', now()->format('Y-m'));
  $y  = $y  ?? request('y',  now()->format('Y'));

  // Fallback weights for points display (used only by legacy tabs)
  $W_WINS  = 10;
  $W_VOTES = 1;
  $W_PARTS = 2;
?>


<style>
  .ap-tabs { position: relative; z-index: 2001; }
</style>

<div class="container py-4 py-md-5">

  
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 mb-md-4">
    <div class="d-flex align-items-center gap-2">
      <h1 class="h3 m-0">Clasament</h1>
      <span class="badge bg-secondary">
        <?php echo e($isPos ? 'Poziții' : ($isAll ? 'All-Time' : ($isMonth ? 'Lunar' : 'Anual'))); ?>

      </span>
    </div>

    
    <div class="d-flex align-items-center gap-2 ap-tabs">
      <ul class="nav nav-pills">
        <li class="nav-item">
          <a class="nav-link <?php echo e($isPos ? 'active' : ''); ?>"
             href="<?php echo e(route('leaderboard.positions')); ?>">
            POZIȚII
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo e($isAll ? 'active' : ''); ?>"
             href="<?php echo e(route('leaderboard.alltime')); ?>">
            ALL-TIME
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo e($isMonth ? 'active' : ''); ?>"
             href="<?php echo e(route('leaderboard.monthly')); ?>">
            MONTHLY
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo e($isYear ? 'active' : ''); ?>"
             href="<?php echo e(route('leaderboard.yearly')); ?>">
            YEARLY
          </a>
        </li>
      </ul>

      
      <?php if($isMonth): ?>
        <form method="GET" action="<?php echo e(route('leaderboard.monthly')); ?>" class="ms-2">
          <input type="month" name="ym" value="<?php echo e($ym); ?>"
                 class="form-control form-control-sm" onchange="this.form.submit()"/>
        </form>
      <?php elseif($isYear): ?>
        <form method="GET" action="<?php echo e(route('leaderboard.yearly')); ?>" class="ms-2">
          <input type="number" name="y" value="<?php echo e($y); ?>" min="2000" max="2100"
                 class="form-control form-control-sm" onchange="this.form.submit()"/>
        </form>
      <?php endif; ?>
    </div>
  </div>

  
  <div class="text-muted small mb-3">
    <?php if($isPos): ?>
      Clasament POZIȚII (puncte agregate din pozițiile zilnice). Ordinea: puncte ↓, apoi ID ↑.
    <?php elseif($isAll): ?>
      Clasament All-Time. Ordinea: victorii ↓, voturi primite ↓, participări ↓, nume ↑.
    <?php elseif($isMonth): ?>
      Clasament pentru luna
      <strong><?php echo e(\Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y')); ?></strong>.
      <em>(Puncte din ledger pentru luna selectată.)</em>
      Ordinea: puncte ↓, nume ↑.
    <?php else: ?>
      Clasament pentru anul <strong><?php echo e($y); ?></strong>.
      Ordinea: victorii ↓, voturi primite ↓, participări ↓, nume ↑.
    <?php endif; ?>
  </div>

  
  <div class="ap-card ap-card-hover rounded-3 p-3 p-md-4">
    <div class="table-responsive">
      <table class="ap-table table table-dark table-borderless align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th class="text-center">Participări</th>
            <th class="text-center">Victorii</th>
            <th class="text-center">Voturi primite</th>
            <th class="text-center">Voturi date</th>
            <th class="text-center">Win Rate</th>
            <th class="text-end">Puncte</th>
          </tr>
        </thead>
        <tbody>
          <?php $start = $page_start ?? 0; ?>

          <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
              $rank   = $start + $i + 1;
              $isTop3 = $rank <= 3;
              $badgeBg = $rank==1 ? 'linear-gradient(135deg,#ffd700,#ffb700)'
                        : ($rank==2 ? 'linear-gradient(135deg,#c0c0c0,#a9a9a9)'
                                    : 'linear-gradient(135deg,#cd7f32,#a46a29)');
              $points = $r->points
                ?? ($r->wins * $W_WINS) + ($r->votes_received * $W_VOTES) + ($r->participations * $W_PARTS);
              $winRate = ($r->participations ?? 0) > 0
                ? round(($r->wins / max($r->participations,1)) * 100) . '%'
                : '—';
            ?>
            <tr>
              <td class="fw-semibold">
                <?php if($isTop3): ?>
                  <span class="ap-rank-badge" style="background: <?php echo e($badgeBg); ?>;"><?php echo e($rank); ?></span>
                <?php else: ?>
                  <?php echo e($rank); ?>

                <?php endif; ?>
              </td>
              <td><?php echo e($r->name); ?></td>
              <td class="text-center"><?php echo e($r->participations ?? '—'); ?></td>
              <td class="text-center fw-semibold"><?php echo e($r->wins ?? '—'); ?></td>
              <td class="text-center"><?php echo e($r->votes_received ?? '—'); ?></td>
              <td class="text-center"><?php echo e($r->votes_given ?? '—'); ?></td>
              <td class="text-center"><?php echo e($winRate); ?></td>
              <td class="text-end fw-bold"><?php echo e($points); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                Nu există date pentru această perioadă.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    
    <div class="mt-3"><?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $rows]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rows)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $attributes = $__attributesOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $component = $__componentOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__componentOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?></div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\leaderboards.blade.php ENDPATH**/ ?>