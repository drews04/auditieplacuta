


<style>
  #youtubeModal ~ .modal-backdrop {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
  }
  body.modal-open:has(#youtubeModal.show) {
    overflow: auto !important;
    padding-right: 0 !important;
  }
</style>

<div class="modal fade" id="youtubeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-neon fw-bold">Redă melodia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
      </div>

      <div class="modal-body pt-2">
        <div class="ap-player-frame ratio ratio-16x9">
          <iframe id="ytFrame" src="" title="YouTube player"
                  allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>

      <div class="modal-footer d-flex flex-column gap-2 border-0">
        <a id="ytOpenLink" href="#" target="_blank" rel="noopener" class="btn btn-neon w-100">
          Vezi pe YouTube
        </a>
        <button type="button" class="btn btn-neon-ghost w-100" data-bs-dismiss="modal">
          Închide
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Clean up YouTube modal backdrop on close
  (function() {
    const ytModal = document.getElementById('youtubeModal');
    if (!ytModal) return;
    
    ytModal.addEventListener('hidden.bs.modal', function() {
      // Remove any leftover backdrops
      document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
      
      // Clear iframe src to stop video
      const iframe = document.getElementById('ytFrame');
      if (iframe) iframe.src = '';
    });
    
    // Also clean up when closing button is clicked
    const closeBtn = ytModal.querySelector('[data-bs-dismiss="modal"]');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        setTimeout(() => {
          document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
        }, 100);
      });
    }
  })();
</script>

<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\concurs\partials\youtube_modal.blade.php ENDPATH**/ ?>