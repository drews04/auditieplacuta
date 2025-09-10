// public/js/pill-alert.js — ultra-simple forum pill + chime
(function () {
  const root = document.getElementById('reply-pill-root');
  if (!root) return;

  const COOLDOWN_MS = 30 * 1000;      // 30 seconds (test)
  const IDLE_MS     = 2 * 60 * 1000;  // 2 minutes when there's nothing to show

  let timer = null;

  // ---------- SOUND (best-effort, no UI prompts) ----------
  const soundUrls = (function () {
    // Absolute URLs (works without Blade vars)
    const base = window.location.origin;
    return [
      `${base}/assets/sounds/reply.mp3`,
      `${base}/assets/sounds/reply.ogg`,
      `${base}/assets/sounds/reply1.mp3`
    ];
  })();

  function beepFallback() {
    try {
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return;
      const ctx = new AC();
      if (ctx.state === 'suspended') { ctx.resume().catch(() => {}); }
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
      // low volume, non-blocking
      a.volume = 0.6;
      const p = a.play();
      if (p && typeof p.then === 'function') {
        p.catch(() => tryPlay(index + 1));
      }
    } catch (_) {
      tryPlay(index + 1);
    }
  }

  function playChime() {
    // Autoplay can fail if there was no prior gesture — we just try silently.
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

  function postAckShown() {
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      fetch('/forum/alerts/ack-shown', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token
        },
        credentials: 'same-origin',
        cache: 'no-store',
        body: JSON.stringify({ shown: true })
      }).catch(() => {});
    } catch (_) {}
  }

  // ---------- UI ----------
  function renderPill(count) {
    removePill();

    const pill = document.createElement('div');
    pill.className = 'reply-pill pill-visible';
    pill.setAttribute('role', 'status');
    pill.innerHTML = `
      <div class="pill-content">
        <div class="pill-text">Ai ${count} răspuns${count===1 ? '' : 'uri'} noi pe Forum</div>
        <div class="pill-buttons">
          <button class="pill-btn" data-action="view">Vezi</button>
          <button class="pill-btn" data-action="close">Închide</button>
        </div>
      </div>
    `;
    root.appendChild(pill);

    // start cooldown on server immediately
    postAckShown();
    // play chime (best-effort)
    playChime();

    pill.addEventListener('click', (e) => {
      const btn = e.target.closest('.pill-btn');
      if (!btn) return;
      const action = btn.getAttribute('data-action');

      if (action === 'view') {
        removePill();
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
    }, 10000);

    // Next check strictly after cooldown window
    schedule(COOLDOWN_MS);
  }

  // ---------- SERVER CHECK ----------
  function check() {
    fetch(`/forum/alerts/unread-count?_=${Date.now()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      cache: 'no-store'
    })
      .then(r => r.ok ? r.json() : Promise.reject(r))
      .then(data => {
        const count = Number(data?.count ?? 0);

        if (count > 0) {
          renderPill(count);               // schedules 30s on its own
        } else {
          removePill();
          schedule(IDLE_MS);               // check again in 2 min
        }
      })
      .catch(() => {
        // On error, try again later
        schedule(IDLE_MS);
      });
  }

  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(check, 600); // first check shortly after load
  });
})();
