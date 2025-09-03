// public/js/forum.js

// ---------- helpers ----------
async function postJSON(url, data = {}) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
      credentials: 'same-origin',
    });
    if (res.status === 401) { window.location.href = '/login'; return null; }
    return await res.json();
  }
  
  function buildLikeUrl(type, id) {
    // Support both naming schemes from Blade
    const threadBase = window.forumLikeThreadBase || window.forumLikeThreadRoute || '/forum/like/thread';
    const postBase   = window.forumLikePostBase   || window.forumLikePostRoute   || '/forum/like/post';
    return (type === 'thread' ? threadBase : postBase).replace(/\/$/, '') + '/' + id;
  }
  
  // ---------- like toggle (delegated so it works for children too) ----------
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.forum-like-btn');
    if (!btn) return;
  
    const type = btn.dataset.type;           // "thread" | "post"
    const id   = btn.dataset.id;
    const url  = buildLikeUrl(type, id);
  
    const icon  = btn.querySelector('i');
    const count = btn.querySelector('.forum-like-count');
  
    btn.disabled = true;
    try {
      const data = await postJSON(url, {});
      if (!data) return;
  
      // toggle UI
      if (data.liked) {
        icon.classList.add('is-liked');
      } else {
        icon.classList.remove('is-liked');
      }
      if (typeof data.count !== 'undefined') {
        count.textContent = data.count;
      }
    } catch (err) {
      console.error(err);
    } finally {
      btn.disabled = false;
    }
  });
  
  // ---------- reply targetting (sets parent_id, shows pill, scrolls) ----------
  document.addEventListener('click', (e) => {
    const replyBtn = e.target.closest('.forum-reply-btn');
    if (!replyBtn) return;
  
    const parentId = replyBtn.dataset.postId;
    const userName = replyBtn.dataset.userName || 'Utilizator';
    const input    = document.getElementById('parent_id');
    const pill     = document.getElementById('replying-pill');
    const form     = document.getElementById('reply-form');
  
    if (input) input.value = parentId;
    if (pill) {
      pill.innerHTML = 'Răspunzi lui <strong>@' + userName + '</strong> <span id="reply-cancel" aria-label="Renunță" style="cursor:pointer;margin-left:.5rem;opacity:.7;">×</span>';
      pill.classList.remove('d-none');
    }
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
  
  document.addEventListener('click', (e) => {
    if (e.target && e.target.id === 'reply-cancel') {
      const input = document.getElementById('parent_id');
      const pill  = document.getElementById('replying-pill');
      if (input) input.value = '';
      if (pill) { pill.classList.add('d-none'); pill.innerHTML = ''; }
    }
  });
  