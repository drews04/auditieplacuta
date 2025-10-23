document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-open-login').forEach(btn => {
      btn.addEventListener('click', () => {
        const off = document.getElementById('apMobileNav');
        if (off) bootstrap.Offcanvas.getOrCreateInstance(off).hide();
        const modal = document.getElementById('loginModal');
        if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
      });
    });
  });
  