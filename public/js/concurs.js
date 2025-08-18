/* ==========================================================================
   auditieplacuta.ro — Concurs page JS
   - Winner popup logic (+ localStorage tracking)
   - Upload form (AJAX) + dust vanish animation on success
   - Toast helper
   - Voting (AJAX) + STAGGERED VANISH EFFECT top→bottom
   - YouTube modal wiring (play buttons)
   - 3D play button micro-press animation
   ========================================================================== */

/* --------------------------------------------------------------------------
   WINNER POPUP (unchanged logic, just formatted)
   -------------------------------------------------------------------------- */
   (function () {
    const qs = new URLSearchParams(window.location.search);
    const force = qs.get('force_popup') === '1';
    const reset = qs.get('reset_popup') === '1';
  
    const popup = document.getElementById('winnerReminder');
    if (!popup) return;
  
    const btnClose = document.getElementById('btn-close-winner');
    const btnOpen = document.getElementById('btn-open-theme');
    const btnReopen = document.getElementById('btn-winner-reopen');
    const deadlineEl = document.getElementById('winner-deadline');
  
    const KEY_SHOWN = 'ap_winner_shown_at';
    const KEY_CLOSED = 'ap_winner_closed_without_save';
  
    function ymdKey(d) {
      return [
        d.getFullYear(),
        String(d.getMonth() + 1).padStart(2, '0'),
        String(d.getDate()).padStart(2, '0')
      ].join('-');
    }
    function lsGet(k) { try { return localStorage.getItem(k) } catch (_) { return null } }
    function lsSet(k, v) { try { localStorage.setItem(k, v) } catch (_) { } }
    function lsDel(k) { try { localStorage.removeItem(k) } catch (_) { } }
  
    if (reset) { lsDel(KEY_SHOWN); lsDel(KEY_CLOSED); }
  
    function isWeekday(d) { const n = d.getDay(); return n >= 1 && n <= 5; }
    function inWinnerWindow(d) { const h = d.getHours(); return h >= 20 && h < 21; }
    function alreadyShownToday() { return lsGet(KEY_SHOWN) === ymdKey(new Date()); }
    function markShownToday() { lsSet(KEY_SHOWN, ymdKey(new Date())); }
  
    let autoCloseTimer = null;
  
    function showPopup() {
      if (deadlineEl) {
        const now = new Date();
        deadlineEl.textContent = `Până la 21:00, ${String(now.getDate()).padStart(2, '0')}.${String(now.getMonth() + 1).padStart(2, '0')}.${now.getFullYear()}`;
      }
      popup.style.display = 'flex';
      markShownToday();
      lsDel(KEY_CLOSED);
  
      clearTimeout(autoCloseTimer);
      autoCloseTimer = setTimeout(hidePopup, 10000); // auto-close after 10s
    }
  
    function hidePopup() {
      popup.style.display = 'none';
      if (btnReopen) {
        btnReopen.style.display = 'inline-block';
        lsSet(KEY_CLOSED, '1');
      }
    }
  
    if (btnClose) btnClose.addEventListener('click', hidePopup);
    if (btnOpen) {
      btnOpen.addEventListener('click', function () {
        window.location.href = '/concurs/alege-tema';
      });
    }
    if (btnReopen) {
      btnReopen.addEventListener('click', function () {
        showPopup();
        btnReopen.style.display = 'none';
      });
    }
  
    const now = new Date();
    if (force) { showPopup(); return; }
    if (isWeekday(now) && inWinnerWindow(now) && !alreadyShownToday()) { showPopup(); return; }
    if (isWeekday(now) && now.getHours() < 20) {
      const at = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 20, 0, 0, 0);
      const delay = at.getTime() - now.getTime();
      if (delay > 0) {
        setTimeout(function () { if (!alreadyShownToday()) showPopup(); }, delay);
      }
    }
  
    // ping every 10 minutes within 20:00–20:50 (max 5 pings)
    (function () {
      if (!isWeekday(now)) return;
      let count = 0;
      function ping() {
        const t = new Date();
        if (t.getHours() !== 20 || count >= 5) return;
        if (!alreadyShownToday()) showPopup();
        count++;
        setTimeout(ping, 10 * 60 * 1000);
      }
      if (inWinnerWindow(now)) setTimeout(ping, 10 * 60 * 1000);
    })();
  
    if (lsGet(KEY_CLOSED) === '1' && btnReopen) {
      btnReopen.style.display = 'inline-block';
    }
  })();
  
  /* --------------------------------------------------------------------------
     UPLOAD FORM (AJAX) — dust vanish animation on success
     -------------------------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('song-upload-form');
    if (!form) return;
  
    const submitBtn = form.querySelector('button[type="submit"]');
  
    form.addEventListener('submit', function (e) {
      e.preventDefault();
  
      // lock button to avoid double submits
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.dataset._originalText = submitBtn.textContent;
        submitBtn.textContent = 'Se încarcă…';
      }
  
      const formData = new FormData(form);
  
      fetch(uploadRoute, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: formData
      })
        .then(res => {
          if (!res.ok) throw res;
          return res.json();
        })
        .then(data => {
          // SUCCESS: toast + animate the upload card with apDustVanish, then remove
          showToast(data.message || 'Melodia a fost încărcată cu succes.', 'success');
  
          const card = form.closest('.card');
          if (card) {
            // If you already map .vanish -> apDustVanish in CSS, use the class instead:
            // card.classList.add('vanish');
  
            // Inline animation using your @keyframes apDustVanish
            card.style.animation = 'apDustVanish 0.65s ease forwards';
  
            const removeCard = () => card.remove();
            card.addEventListener('animationend', removeCard, { once: true });
  
            // Safety net (just in case animationend doesn’t fire)
            setTimeout(() => {
              if (document.body.contains(card)) removeCard();
            }, 900);
          }
  
          // Refresh today’s song list after a tiny delay so the fade is visible
          return new Promise(res => setTimeout(res, 160)).then(() =>
            fetch(songListRoute, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
              .then(r => r.text())
              .then(html => {
                const list = document.getElementById('song-list');
                if (list) list.innerHTML = html;
              })
          );
        })
        .catch(async err => {
          // ERROR: toast + restore button
          let msg = 'Eroare la încărcare.';
          try {
            const e = await err.json();
            if (e.message) msg = e.message;
          } catch (_) { }
          showToast(msg, 'danger');
        })
        .finally(() => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset._originalText || 'Trimite';
          }
        });
    });
  });
  
  /* --------------------------------------------------------------------------
     TOAST (unchanged)
     -------------------------------------------------------------------------- */
  function showToast(message, type) {
    const toast = document.createElement('div');
    toast.textContent = message;
  
    toast.style.position = 'fixed';
    toast.style.top = '50%';
    toast.style.left = '50%';
    toast.style.transform = 'translate(-50%, -50%)';
    toast.style.padding = '15px 25px';
    toast.style.fontSize = '18px';
    toast.style.fontWeight = '600';
    toast.style.borderRadius = '8px';
    toast.style.zIndex = '5000';
    toast.style.textAlign = 'center';
    toast.style.maxWidth = '80%';
    toast.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
  
    if (type === 'success') {
      toast.style.backgroundColor = '#28a745';
      toast.style.color = '#fff';
    } else {
      toast.style.backgroundColor = '#dc3545';
      toast.style.color = '#fff';
    }
  
    document.body.appendChild(toast);
  
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.5s ease';
      setTimeout(() => toast.remove(), 500);
    }, 3000);
  }
  
  /* --------------------------------------------------------------------------
     STAGGERED VANISH (NEW) — helper used by voting success
     - Reads per-button delay from CSS custom property --vanish-delay
     - 0.5s between each button, in DOM (top→bottom) order
     - Requires CSS block "Vote button — sand/dust vanish (stagger-ready)"
     -------------------------------------------------------------------------- */
  function apStaggerVanishVoteButtons() {
    const buttons = Array.from(document.querySelectorAll('.vote-btn'));
    buttons.forEach((btn, i) => {
      btn.disabled = true;                                // stop more clicks
      btn.style.setProperty('--vanish-delay', `${i * 0.5}s`);
      btn.classList.add('vanish');                        // triggers CSS animation
    });
  }
  
  /* --------------------------------------------------------------------------
     VOTING (AJAX) — modified to use STAGGERED VANISH (replaces “instant fade”)
     -------------------------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.vote-btn');
    if (!btn) return;
  
    e.preventDefault();
    const songId = btn.dataset.songId;
  
    fetch(voteRoute.replace(':songId', songId), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })
      .then(res => {
        if (!res.ok) throw res;
        return res.json();
      })
      .then(data => {
        showToast(data.message || 'Votul tău a fost înregistrat cu succes.', 'success');
  
        // NEW: top→bottom staggered vanish using CSS animation
        apStaggerVanishVoteButtons();
      })
      .catch(async err => {
        let msg = 'Eroare la vot.';
        try {
          const j = await err.json();
          if (j.message) msg = j.message;
        } catch (_) { }
        showToast(msg, 'danger');
      });
  });
  
  /* --------------------------------------------------------------------------
     YOUTUBE MODAL WIRING (NEW)
     - Fills iframe with the clicked song URL (embed with autoplay)
     - Clears iframe when modal hides (stops audio)
     - Relies on markup in Blade: #youtubeModal, #ytFrame, #ytOpenLink
     -------------------------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    const ytFrame = document.getElementById('ytFrame');
    const openLink = document.getElementById('ytOpenLink');
    const modalEl = document.getElementById('youtubeModal');
  
    if (!ytFrame || !openLink || !modalEl) return;
  
    function ytId(url) {
      if (!url) return null;
      const m1 = url.match(/youtu\.be\/([0-9A-Za-z_-]{11})/);
      if (m1) return m1[1];
      const m2 = url.match(/(?:v=|\/embed\/|\/v\/)([0-9A-Za-z_-]{11})/);
      if (m2) return m2[1];
      const m3 = url.match(/([0-9A-Za-z_-]{11})/);
      return m3 ? m3[1] : null;
    }
  
    function toEmbed(url) {
      const id = ytId(url);
      return id ? `https://www.youtube.com/embed/${id}?autoplay=1&rel=0` : '';
    }
  
    document.body.addEventListener('click', function (e) {
      const btn = e.target.closest('.play3d');
      if (!btn) return;
  
      const url = btn.getAttribute('data-youtube-url') || '';
      const embed = toEmbed(url);
  
      if (embed) {
        ytFrame.src = embed;
        openLink.href = url;
      } else {
        ytFrame.src = '';
        openLink.href = url || '#';
      }
    });
  
    // Clear video when the modal is hidden (Bootstrap event)
    modalEl.addEventListener('hidden.bs.modal', function () {
      ytFrame.src = '';
    });
  });
  
  /* --------------------------------------------------------------------------
     3D PLAY BUTTON MICRO PRESS (unchanged)
     -------------------------------------------------------------------------- */
  document.addEventListener('click', (e) => {
    const p = e.target.closest('.play3d');
    if (!p) return;
    p.animate(
      [{ transform: 'translateY(1px) scale(0.98)' }, { transform: 'translateY(0) scale(1)' }],
      { duration: 140, easing: 'ease-out' }
    );
  });
    