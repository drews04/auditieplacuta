// public/js/theme-like.js
(function () {
  if (window.__apThemeLikeInitDone) return;
  window.__apThemeLikeInitDone = true;

  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $all(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  // Sync initial UI
  function initButtons() {
    $all('.theme-like').forEach(btn => {
      const liked = btn.getAttribute('data-liked') === '1';
      btn.classList.toggle('is-liked', liked);

      const cntEl = btn.querySelector('.like-count');
      const cnt = parseInt(btn.getAttribute('data-count') || '0', 10);
      if (cntEl) cntEl.textContent = String(isNaN(cnt) ? 0 : cnt);

      btn.removeAttribute('data-bs-toggle'); // avoid dropdown stealing click
      btn.removeAttribute('aria-expanded');
      btn.dataset.busy = '0';                // request guard
    });
  }

  // Single delegated handler
  async function handleClick(e) {
    const btn = e.target.closest('.theme-like');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    // Busy guard (prevents double clicks/twitch)
    if (btn.dataset.busy === '1') return;

    // Guests
    if (btn.getAttribute('data-auth') === '0') {
      window.location.href = '/login';
      return;
    }

    const type  = btn.getAttribute('data-likeable-type'); // 'contest'|'pool'
    const id    = parseInt(btn.getAttribute('data-likeable-id') || '0', 10);
    const cntEl = btn.querySelector('.like-count');
    if (!type || !id || !cntEl) return;

    // Optimistic flip (clamped)
    const wasLiked  = btn.getAttribute('data-liked') === '1';
    const prevCount = parseInt(cntEl.textContent || '0', 10) || 0;
    const nextCount = Math.max(0, prevCount + (wasLiked ? -1 : 1));

    btn.dataset.busy = '1';
    btn.classList.toggle('is-liked', !wasLiked);
    btn.setAttribute('data-liked', wasLiked ? '0' : '1');
    cntEl.textContent = String(nextCount);

    try { btn.animate([{transform:'scale(1)'},{transform:'scale(1.08)'},{transform:'scale(1)'}],{duration:160}); } catch(_) {}

    const url  = window.routeThemesLikeToggle || '/themes/like/toggle';
    const csrf = ($('meta[name="csrf-token"]')?.getAttribute('content')) || (window.csrfToken || '');

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ likeable_type: type, likeable_id: id }),
        credentials: 'same-origin'
      });

      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      if (!data.ok) throw new Error(data.message || 'Server error');

      // Server truth
      btn.classList.toggle('is-liked', !!data.liked);
      btn.setAttribute('data-liked', data.liked ? '1' : '0');
      cntEl.textContent = String(data.count ?? 0);
    } catch (err) {
      // Revert on fail
      btn.classList.toggle('is-liked', wasLiked);
      btn.setAttribute('data-liked', wasLiked ? '1' : '0');
      cntEl.textContent = String(prevCount);
      console.error('[theme-like] toggle failed:', err);
      alert('Nu s-a putut înregistra like-ul. Încearcă din nou.');
    } finally {
      btn.dataset.busy = '0';
    }
  }

  document.addEventListener('DOMContentLoaded', initButtons, { once: true });
  document.addEventListener('click', handleClick, false);
})();
