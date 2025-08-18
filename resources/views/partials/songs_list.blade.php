@php
    // Prevent crash if variable is not passed from controller
    $userHasVotedToday = $userHasVotedToday ?? false;
@endphp

<div class="list-group">
@forelse($songs as $song)
    @php
        $label  = $song->title && trim($song->title) !== '' ? $song->title : 'Melodie YouTube';
        $isMine = auth()->check() && $song->user_id === auth()->id();
    @endphp

    <div class="list-group-item d-flex justify-content-between align-items-center song-item {{ $isMine ? 'my-song' : '' }}">
        <div class="d-flex align-items-center gap-3">
            {{-- 3D metallic play button --}}
            <button
                class="play3d"
                aria-label="Redă"
                data-youtube-url="{{ $song->youtube_url }}"
                data-bs-toggle="modal"
                data-bs-target="#youtubeModal">
                <span class="play3d-core">
                    <span class="play3d-triangle"></span>
                </span>
            </button>

            {{-- Exact title from YouTube --}}
            <span class="fw-semibold song-title">{{ $label }}</span>
        </div>

        <div class="d-flex align-items-center">
            @if(!$userHasVotedToday && !$isMine)
                <button 
                    class="btn btn-sm btn-success vote-btn" 
                    data-song-id="{{ $song->id }}">
                    Votează
                </button>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info mb-0">Nu au fost încă adăugate melodii azi.</div>
@endforelse
</div>

