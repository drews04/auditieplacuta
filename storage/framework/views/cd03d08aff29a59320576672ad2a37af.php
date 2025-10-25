

<?php $__env->startSection('title', 'Rezultate Concurs'); ?>
<?php $__env->startSection('body_class', 'page-concurs-archive-show'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0 fw-bold">
      Rezultate — <?php echo e($cycle->vote_end_at->timezone(config('app.timezone'))->format('D, d M Y')); ?>

    </h1>
    <div class="d-flex gap-2">
      <?php if($prev): ?>
        <a class="btn btn-outline-secondary"
           href="<?php echo e(route('concurs.arhiva.show', $prev->vote_end_at->toDateString())); ?>">← Anterior</a>
      <?php endif; ?>
      <a class="btn btn-outline-secondary" href="<?php echo e(route('concurs.arhiva')); ?>">Arhivă</a>
      <?php if($next): ?>
        <a class="btn btn-outline-secondary"
           href="<?php echo e(route('concurs.arhiva.show', $next->vote_end_at->toDateString())); ?>">Următor →</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="small text-muted mb-1">
        Vot închis la <?php echo e($cycle->vote_end_at->timezone(config('app.timezone'))->format('H:i, d M Y')); ?>

      </div>
      <?php if($cycle->theme_text): ?>
        <div class="fw-semibold">Tema: <?php echo e($cycle->theme_text); ?></div>
      <?php endif; ?>
      <?php if($winner): ?>
        <div class="mt-2 p-3 rounded-3" style="background:#0b1b1f;">
          <div class="fw-bold">
            🏆 Câștigător: <?php echo e($winner->song->title ?? 'Melodie'); ?>

            <span class="text-muted">de</span>
            <span class="fw-semibold"><?php echo e($winner->user->name ?? 'necunoscut'); ?></span>
            <span class="ms-2 badge bg-success"><?php echo e($winner->vote_count); ?> voturi</span>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h5 class="card-title fw-bold mb-3">Clasament complet</h5>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Melodie</th>
              <th>Utilizator</th>
              <th class="text-center">Voturi</th>
              <th style="width:180px;"></th>
            </tr>
          </thead>
          <tbody>
          <?php $pos = 1; ?>
          <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $song): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td class="fw-bold"><?php echo e($pos++); ?></td>
              <td><?php echo e($song->title ?? 'Melodie'); ?></td>
              <td><?php echo e(optional($song->user)->name ?? 'necunoscut'); ?></td>
              <td class="text-center">
                <span class="badge bg-success"><?php echo e($song->vote_count); ?></span>
              </td>
              <td>
                <?php if($song->vote_count > 0): ?>
                    <button
                    class="btn btn-sm btn-outline-info voters-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#voters-<?php echo e($song->id); ?>"
                    aria-expanded="false"
                    data-url="<?php echo e(route('concurs.arhiva.voters', ['date' => $cycle->vote_end_at->toDateString(), 'song' => $song->id])); ?>"
                    data-target="#voters-<?php echo e($song->id); ?>"
                    >
                    Cine a votat
                    </button>
                <?php endif; ?>
                </td>

            </tr>
            <?php if($song->vote_count > 0): ?>
            <tr class="collapse" id="voters-<?php echo e($song->id); ?>">
                <td colspan="5">
                    <div class="voters-panel" data-loaded="0">
                    
                    <div class="text-muted small">Se încarcă…</div>
                    </div>
                </td>
                </tr>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.voters-btn');
  if (!btn) return;

  const url = btn.getAttribute('data-url');
  const targetSel = btn.getAttribute('data-target');
  const row = document.querySelector(targetSel);
  if (!row) return;

  const panel = row.querySelector('.voters-panel');
  if (!panel) return;

  // Only load once per song
  if (panel.getAttribute('data-loaded') === '1') return;

  panel.innerHTML = '<div class="text-muted small">Se încarcă…</div>';

  fetch(url, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin'
  })
  .then(r => { if (!r.ok) throw r; return r.json(); })
  .then(data => {
    const voters = data.voters || [];

    if (voters.length === 0) {
      panel.innerHTML = '<div class="text-muted small">Nu există voturi pentru această melodie.</div>';
      panel.setAttribute('data-loaded', '1');
      return;
    }

    const header = document.createElement('div');
    header.className = 'small text-muted mb-1';
    header.textContent = 'Voturi pentru: ' + (data.song?.title || '');

    const list = document.createElement('div');
    list.className = 'd-flex flex-wrap gap-2';

    voters.forEach(v => {
      const pill = document.createElement('span');
      pill.className = 'badge bg-secondary';
      pill.textContent = v.name || '—';
      list.appendChild(pill);
    });

    panel.innerHTML = '';
    panel.appendChild(header);
    panel.appendChild(list);
    panel.setAttribute('data-loaded', '1');
  })
  .catch(async err => {
    let msg = 'Eroare la încărcarea listei de votanți.';
    try { const j = await err.json(); if (j.message) msg = j.message; } catch(_){}
    panel.innerHTML = '<div class="text-danger small">'+ msg +'</div>';
  });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\concurs\archive\show.blade.php ENDPATH**/ ?>