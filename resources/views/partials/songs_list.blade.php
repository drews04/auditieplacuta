{{-- resources/views/partials/songs_list.blade.php --}}

@php
    // Defaults
    $userHasVotedToday   = $userHasVotedToday   ?? false;
    $showVoteButtons     = $showVoteButtons     ?? false;
    $hideDisabledButtons = $hideDisabledButtons ?? false;
    $disabledVoteText    = $disabledVoteText    ?? null;
@endphp

<div class="list-group">
@forelse ($songs as $song)
    @php
        $label  = ($song->title && trim($song->title) !== '') ? $song->title : 'Melodie YouTube';
        $isMine = auth()->check() && (int) $song->user_id === (int) auth()->id();
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

            {{-- Title --}}
            <span class="fw-semibold song-title">{{ $label }}</span>
        </div>

        <div class="d-flex align-items-center">
            {{-- ACTIVE vote button (only when voting is open) --}}
            @if ($showVoteButtons && !$userHasVotedToday && !$isMine && auth()->check())
                <button type="button"
                        class="btn btn-sm btn-success vote-btn"
                        data-song-id="{{ $song->id }}">
                    Votează
                </button>
            @endif

            {{-- DISABLED/TEASER vote button (when voting not open yet) --}}
            @if (
                !$showVoteButtons               &&  {{-- voting not open --}}
                !$hideDisabledButtons           &&  {{-- don't hide teaser --}}
                !empty($disabledVoteText)       &&  {{-- we have a label --}}
                !$isMine                            {{-- not my own song --}}
            )
                <button type="button"
                        class="btn btn-sm btn-success opacity-60 ms-2 vote-btn"
                        disabled>
                    {{ $disabledVoteText }}
                </button>
            @endif

            {{-- Already voted OR own song --}}
            @if ($showVoteButtons && ($userHasVotedToday || $isMine))
                <button type="button"
                        class="btn btn-sm btn-outline-secondary vote-btn"
                        disabled
                        title="{{ $isMine ? 'Nu poți vota propria melodie' : 'Ai votat deja în această rundă' }}">
                    Votează
                </button>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info mb-0">Nu au fost încă adăugate melodii.</div>
@endforelse
</div>
