// public/assets/js/youtube-modal.js
document.addEventListener('DOMContentLoaded', function () {
    const ytFrame  = document.getElementById('ytFrame');
    const openLink = document.getElementById('ytOpenLink');
    const modalEl  = document.getElementById('youtubeModal');
    if (!ytFrame || !openLink || !modalEl) return;
  
    function ytId(url){
      if (!url) return null;
      // supports watch?v=, youtu.be, /embed/, /shorts/, /live/
      const m = url.match(/(?:youtu\.be\/|v=|\/embed\/|\/shorts\/|\/live\/)([A-Za-z0-9_-]{11})/);
      if (m && m[1]) return m[1];
      try { return new URL(url).searchParams.get('v'); } catch { return null; }
    }
    const toEmbed = (u) => {
      const id = ytId(u);
      return id ? `https://www.youtube.com/embed/${id}?autoplay=1&rel=0` : '';
    };
  
    // any .play3d with data-youtube-url opens the modal player
    document.body.addEventListener('click', function (e) {
      const btn = e.target.closest('.play3d');
      if (!btn) return;
      const url = btn.getAttribute('data-youtube-url') || '';
      const embed = toEmbed(url);
      ytFrame.src   = embed || '';
      openLink.href = url   || '#';
    });
  
    // stop playback on close
    modalEl.addEventListener('hidden.bs.modal', () => { ytFrame.src = ''; });
  });
  