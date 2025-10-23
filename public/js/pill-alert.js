// public/js/pill-alert.js — simple: only NEW since last /forum visit
(function () {
  const ROOT_ID = 'reply-pill-root';
  const ENDPOINT = '/forum/alerts/unread-count';
  const IDLE_MS = 30_000; // poll every 30s

  const root = document.getElementById(ROOT_ID);
  if (!root) return;

  let timer = null;

  // ---------- SOUND (best-effort) ----------
  const soundUrls = (() => {
    const base = window.location.origin;
    return [
      `${base}/assets/sounds/reply.mp3`,
      `${base}/assets/sounds/reply.ogg`,
      `${base}/assets/sounds/reply1.mp3`,
    ];
  })();

  function beepFallback() {
    try {
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return;
      const ctx = new AC();
      if (ctx.state === 'suspended') ctx.resume().catch(() => {});
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = 880;
      gain.gain.value = 0.04;
      osc.connect(gain).connect(ctx.destination);
      osc.start();
      setTimeout(() => { try { osc.stop(); ctx.close(); } catch (_) {} }, 160);
    } catch (_) {}
  }

  function tryPlay(index = 0) {
    if (index >= soundUrls.length) { beepFallback(); return; }
    try {
      const a = new Audio(soundUrls[index]);
      a.volume = 0.6;
      const p = a.play();
      if (p && typeof p.then === 'function') p.catch(() => tryPlay(index + 1));
    } catch (_) { tryPlay(index + 1); }
  }

  function playChime() {
    tryPlay(0);
  }

  // ---------- HELPERS ----------
  function schedule(ms) {
    if (timer) clearTimeout(timer);
    timer = setTimeout(check, ms);
  }

  function removePill() {
    const el = root.querySelector('.reply-pill');
    if (el) el.remove();
  }

  // ---------- UI ----------
  function renderPill(count) {
    removePill();

    const pill = document.createElement('div');
    pill.className = 'reply-pill pill-visible';
    pill.setAttribute('role', 'status');
    pill.innerHTML = `
      <div class="pill-content">
        <div class="pill-text">Ai ${count} răspuns${count === 1 ? '' : 'uri'} noi pe Forum</div>
        <div class="pill-buttons">
          <button class="pill-btn" data-action="view">Vezi</button>
          <button class="pill-btn" data-action="close">Închide</button>
        </div>
      </div>
    `;
    root.appendChild(pill);

    playChime();

    pill.addEventListener('click', (e) => {
      const btn = e.target.closest('.pill-btn');
      if (!btn) return;
      const action = btn.getAttribute('data-action');
      if (action === 'view') {
        // Visiting /forum resets forum_seen_at; count becomes 0 after
        window.location.href = '/forum';
        return;
      }
      if (action === 'close') {
        pill.classList.add('pill-exiting');
        pill.addEventListener('transitionend', () => pill.remove());
      }
    });

    // Auto-hide after 10s
    setTimeout(() => {
      pill.classList.add('pill-exiting');
      pill.addEventListener('transitionend', () => pill.remove());
    }, 10_000);

    schedule(IDLE_MS);
  }

  // ---------- SERVER CHECK ----------
  function check() {
    fetch(`${ENDPOINT}?_=${Date.now()}`, {
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => r.ok ? r.json() : Promise.reject(r))
      .then(data => {
        const count = Number(data?.count ?? 0);
        if (count > 0) renderPill(count);
        else { removePill(); schedule(IDLE_MS); }
      })
      .catch(() => schedule(IDLE_MS));
  }

  document.addEventListener('DOMContentLoaded', () => setTimeout(check, 600));
})();
