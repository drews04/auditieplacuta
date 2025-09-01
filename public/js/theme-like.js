// Theme Like — initialize state + optimistic toggle + server sync (CSRF fallback)
(function () {
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $all(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  // 1) On load, sync CSS with data-liked and show the starting count
  function initButtons() {
    $all('.theme-like').forEach(btn => {
      const liked = btn.getAttribute('data-liked') === '1';
      btn.classList.toggle('is-liked', liked);

      const cntEl = btn.querySelector('.like-count');
      const cnt = parseInt(btn.getAttribute('data-count') || '0', 10);
      if (cntEl) cntEl.textContent = String(isNaN(cnt) ? 0 : cnt);
    });
  }

  // 2) Toggle handler
  async function handleClick(e) {
    const btn = e.target.closest('.theme-like');
    if (!btn) return;

    // Guests → login
    if (btn.hasAttribute('data-auth') && btn.getAttribute('data-auth') === '0') {
      window.location.href = '/login';
      return;
    }

    const type  = btn.getAttribute('data-likeable-type'); // 'contest' | 'pool'
    const idStr = btn.getAttribute('data-likeable-id') || '0';
    const liked = (btn.getAttribute('data-liked') === '1');
    const cntEl = btn.querySelector('.like-count');

    const id = parseInt(idStr, 10);
    if (!type || !id || id === 0) {
      console.warn('[theme-like] missing type/id', { type, id });
      return;
    }

    // Optimistic update
    const prevLiked = liked;
    const prevCount = cntEl ? parseInt(cntEl.textContent || '0', 10) : 0;

    btn.classList.toggle('is-liked', !prevLiked);
    btn.setAttribute('data-liked', prevLiked ? '0' : '1');
    if (cntEl) cntEl.textContent = String(prevCount + (prevLiked ? -1 : 1));

    try { btn.animate([{transform:'scale(1)'},{transform:'scale(1.12)'},{transform:'scale(1)'}],{duration:180}); } catch (_) {}

    // Build request
    const url = (typeof window.routeThemesLikeToggle === 'string' && window.routeThemesLikeToggle)
      ? window.routeThemesLikeToggle
      : '/themes/like/toggle';

    // CSRF: meta tag OR window.csrfToken fallback
    const metaToken = $('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const csrf = metaToken || (typeof window.csrfToken === 'string' ? window.csrfToken : '');

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',        // <— IMPORTANT: forces JSON (no 302 HTML)
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ likeable_type: type, likeable_id: id }),
        credentials: 'same-origin'
      });

      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      if (!data.ok) throw new Error(data.message || 'Server error');

      // debug network body if not ok
      if (!res.ok) {
        const txt = await res.text().catch(() => '');
        console.error('[theme-like] HTTP error', res.status, txt);
      }
      if (!data.ok) {
        console.error('[theme-like] API error', data);
      }

      // Trust server response
      btn.classList.toggle('is-liked', !!data.liked);
      btn.setAttribute('data-liked', data.liked ? '1' : '0');
      if (cntEl) cntEl.textContent = String(data.count ?? 0);
    } catch (err) {
      // Revert on failure
      btn.classList.toggle('is-liked', prevLiked);
      btn.setAttribute('data-liked', prevLiked ? '1' : '0');
      if (cntEl) cntEl.textContent = String(prevCount);
      console.error('[theme-like] toggle failed:', err);
      alert('Nu s-a putut înregistra like-ul. Încearcă din nou.');
    }
  }

  document.addEventListener('DOMContentLoaded', initButtons, false);
  document.addEventListener('click', handleClick, false);
})();
