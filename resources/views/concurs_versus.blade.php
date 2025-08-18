<div class="vs-container mx-auto">
  @if($songs->count() === 2)
    <div class="vs-grid">
      @foreach($songs as $song)
        @php
          $label  = $song->title && trim($song->title) !== '' ? $song->title : 'Melodie YouTube';
          $isMine = auth()->check() && $song->user_id === auth()->id();
        @endphp

        <div class="list-group-item song-item vs-item d-flex flex-column align-items-center">
          <button
            class="play3d mb-3"
            aria-label="Redă"
            data-youtube-url="{{ $song->youtube_url }}"
            data-bs-toggle="modal"
            data-bs-target="#youtubeModal">
            <span class="play3d-core"><span class="play3d-triangle"></span></span>
          </button>

          <span class="fw-semibold song-title text-center">{{ $label }}</span>

          @unless($isMine)
            <button class="btn btn-success vote-btn mt-3" data-song-id="{{ $song->id }}">Votează</button>
          @endunless
        </div>
      @endforeach

      <div class="vs-badge">VS</div>
    </div>
  @else
    <div class="vs-stack">
      @foreach($songs as $song)
        @php
          $label  = $song->title && trim($song->title) !== '' ? $song->title : 'Melodie YouTube';
          $isMine = auth()->check() && $song->user_id === auth()->id();
        @endphp

        <div class="list-group-item song-item vs-item d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-3">
            <button
              class="play3d"
              aria-label="Redă"
              data-youtube-url="{{ $song->youtube_url }}"
              data-bs-toggle="modal"
              data-bs-target="#youtubeModal">
              <span class="play3d-core"><span class="play3d-triangle"></span></span>
            </button>
            <span class="fw-semibold song-title">{{ $label }}</span>
          </div>

          @unless($isMine)
            <button class="btn btn-success vote-btn" data-song-id="{{ $song->id }}">Votează</button>
          @endunless
        </div>
      @endforeach
    </div>
  @endif
</div>
@push('scripts')
