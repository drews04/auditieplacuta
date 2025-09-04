(function () {
    'use strict';
  
    /* ================== CONSTANTS ================== */
    const CHECK_INTERVAL_MS = 60 * 1000;     // poll backend every 1 min
    const PILL_MIN_INTERVAL_MS = 0;          // dev: no cooldown
    const TYPE_SPEED_MS = 28;                // typewriter speed (ms/char)
    const CURTAIN_MS = 1000;                 // wait 1s for curtain to open
    const HOLD_AFTER_TYPING_MS = 10000;      // keep pill 10s after typing ends
  
    // LocalStorage keys
    const STORAGE_LAST_SEEN  = 'ap_forum_last_seen_at';
    const STORAGE_LAST_SHOWN = 'ap_forum_pill_last_shown_at';
    const STORAGE_SOUND_MUTED = 'ap_forum_sound_muted';
  
    /* ================== STATE ================== */
    let checkInterval = null;
    let currentPill = null;
    let audio = null;
  
    /* ================== INIT ================== */
    function init() {
      // If we're on a forum page, mark last seen and stop
      if (window.location.pathname.startsWith('/forum')) {
        localStorage.setItem(STORAGE_LAST_SEEN, Date.now().toString());
        return;
      }
  
      // Require auth (Blade injects CSRF meta only for logged-in layout)
      if (!document.querySelector('meta[name="csrf-token"]')) return;
  
      startChecking();
    }
  
    function startChecking() {
      if (checkInterval) clearInterval(checkInterval);
      checkInterval = setInterval(checkForNewReplies, CHECK_INTERVAL_MS);
      // fire soon after load
      setTimeout(checkForNewReplies, 1000);
    }
  
    /* ================== POLL BACKEND ================== */
    async function checkForNewReplies() {
      if (document.visibilityState !== 'visible') return; // only when visible
      if (!document.querySelector('meta[name="csrf-token"]')) return;
  
      // (Cooldown removed for dev; keep key for future use)
      // const lastShown = Number(localStorage.getItem(STORAGE_LAST_SHOWN) || 0);
      // if (Date.now() - lastShown < PILL_MIN_INTERVAL_MS) return;
  
      try {
        const lastSeen = localStorage.getItem(STORAGE_LAST_SEEN)
          || (Date.now() - 12 * 60 * 60 * 1000).toString(); // fallback 12h
  
        const resp = await fetch(`/forum/alerts/unread-summary?since=${lastSeen}`, {
          method: 'GET',
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        if (!resp.ok) return;
  
        const data = await resp.json();
        if (data && data.has_new) showPill(data);
      } catch (err) {
        console.warn('Forum alert check failed:', err);
      }
    }
  
    /* ================== RENDER PILL ================== */
    function showPill(data) {
      // Remove any existing pill
      if (currentPill) currentPill.remove();
  
      // Build message
      let pillText = '';
      switch (data.type) {
        case 'single':
          pillText = `@${data.latest_user.name} replied on "${data.thread.title}"`;
          break;
        case 'multi_in_thread':
          pillText = `${data.count} new replies on "${data.thread.title}"`;
          break;
        case 'multi_threads':
          pillText = `${data.count} new replies across your threads`;
          break;
        default:
          pillText = 'New forum activity';
      }
  
      // Create + mount
      currentPill = createPillElement(data);
      const root = document.getElementById('reply-pill-root');
      if (!root) return;
      root.appendChild(currentPill);
  
      // Trigger curtain reveal (CSS handles animation)
      requestAnimationFrame(() => currentPill.classList.add('pill-visible'));
  
      // Start typewriter only AFTER the curtain is fully open
      setTimeout(() => startTypewriter(currentPill, pillText), CURTAIN_MS);
  
      // Track last shown time (kept for future cooldown)
      localStorage.setItem(STORAGE_LAST_SHOWN, Date.now().toString());
    }
  
    function createPillElement(data) {
      const pill = document.createElement('div');
      pill.className = 'reply-pill';
  
      const isSoundMuted = localStorage.getItem(STORAGE_SOUND_MUTED) === 'true';
      const targetUrl = data.thread && data.thread.url ? data.thread.url : '/forum';
  
      pill.innerHTML = `
        <div class="pill-content">
          <div class="pill-typing"></div>
          <div class="pill-buttons">
            <button class="pill-btn pill-open" data-url="${targetUrl}">Open</button>
            <button class="pill-btn pill-mute" data-muted="${isSoundMuted}">${isSoundMuted ? '🔊' : '🔕'}</button>
            <button class="pill-btn pill-close" aria-label="Close">×</button>
          </div>
        </div>
      `;
  
      // Actions
      pill.querySelector('.pill-open').addEventListener('click', () => {
        const url = pill.querySelector('.pill-open').getAttribute('data-url');
        window.location.href = url;
      });
  
      pill.querySelector('.pill-mute').addEventListener('click', () => {
        const muted = localStorage.getItem(STORAGE_SOUND_MUTED) === 'true';
        const next = !muted;
        localStorage.setItem(STORAGE_SOUND_MUTED, next.toString());
        pill.querySelector('.pill-mute').setAttribute('data-muted', next);
        pill.querySelector('.pill-mute').textContent = next ? '🔊' : '🔕';
      });
  
      pill.querySelector('.pill-close').addEventListener('click', () => dismissPill(pill));
  
      return pill;
    }
  
    /* ================== TYPEWRITER ================== */
    function startTypewriter(pill, text) {
      const typingEl = pill.querySelector('.pill-typing');
      typingEl.textContent = ''; // reset
      let i = 0;
  
      // Play sound once at start (if not muted)
      const muted = localStorage.getItem(STORAGE_SOUND_MUTED) === 'true';
      if (!muted && document.visibilityState === 'visible') playSound();
  
      function tick() {
        if (i < text.length) {
          typingEl.textContent += text.charAt(i++);
          setTimeout(tick, TYPE_SPEED_MS);
        } else {
          typingEl.classList.add('typing-complete');
          // keep pill for 10s after typing finishes
          setTimeout(() => dismissPill(pill), HOLD_AFTER_TYPING_MS);
        }
      }
  
      tick();
    }
  
    /* ================== SOUND ================== */
    function playSound() {
      try {
        if (audio) { audio.pause(); audio.currentTime = 0; }
        audio = new Audio('/assets/sounds/reply.mp3');
        audio.onerror = () => {
          const alt = new Audio('/assets/sounds/reply.ogg');
          alt.onerror = () => console.warn('Reply sound file not found');
          alt.volume = 0.35;
          alt.play().catch(() => {});
        };
        audio.volume = 0.35;
        audio.play().catch(() => {}); // ignore autoplay restrictions
      } catch (_) {
        // no-op
      }
    }
  
    /* ================== DISMISS ================== */
    function dismissPill(pill) {
      if (!pill || !pill.parentNode) return;
      pill.classList.add('pill-exiting');
      setTimeout(() => {
        if (pill.parentNode) pill.parentNode.removeChild(pill);
        if (currentPill === pill) currentPill = null;
      }, 300);
    }
  
    /* ================== BOOT ================== */
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }
  })();
  