<?php if($lastFinishedCycle && $lastWinner): ?>
  <?php
    $tz = config('app.timezone');
    $voteEndAt = $lastFinishedCycle->vote_end_at ?? null;
    if ($voteEndAt && !($voteEndAt instanceof \Carbon\Carbon)) {
      $voteEndAt = \Carbon\Carbon::parse($voteEndAt);
    }
  ?>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center justify-content-between">
      <div class="d-flex flex-column">
        <div class="small text-muted mb-1">
          Rezultatele de ieri — <?php echo e($voteEndAt ? $voteEndAt->timezone($tz)->format('D, d M Y') : '—'); ?>

        </div>
        <div class="fw-bold">
          🏆 <?php echo e($lastWinner->song->title ?? 'Melodie'); ?>

          <span class="text-muted">de</span>
          <span class="fw-semibold"><?php echo e($lastWinner->user->name ?? 'necunoscut'); ?></span>
          <span class="ms-2 badge bg-success"><?php echo e($lastWinner->vote_count); ?> voturi</span>
        </div>
        <?php if($lastFinishedCycle->theme_text): ?>
          <div class="small text-muted mt-1">Tema: <?php echo e($lastFinishedCycle->theme_text); ?></div>
        <?php endif; ?>
      </div>

      <a class="btn btn-outline-info"
         href="<?php echo e(route('concurs.arhiva.show', $voteEndAt ? $voteEndAt->toDateString() : now()->toDateString())); ?>">
        Vezi rezultatele complete
      </a>
    </div>
  </div>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/concurs/partials/winner_recap.blade.php ENDPATH**/ ?>