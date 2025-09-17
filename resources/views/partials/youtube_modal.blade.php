{{-- resources/views/partials/youtube_modal.blade.php --}}
<div class="modal fade" id="youtubeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header border-0">
        <h5 class="modal-title">Redă melodia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Închide"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="ratio ratio-16x9">
          <iframe id="ytFrame" src="" title="YouTube player"
                  allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
      <div class="modal-footer border-0">
        <a id="ytOpenLink" href="#" target="_blank" rel="noopener" class="btn btn-outline-info">Vezi pe YouTube</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
      </div>
    </div>
  </div>
</div>
