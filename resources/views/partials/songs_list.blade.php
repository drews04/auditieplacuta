{{-- resources/views/partials/songs_list.blade.php --}}

@php
    // Safe fallbacks (parent view may omit any of these)
    $userHasVotedToday    = $userHasVotedToday    ?? false;
    $showVoteButtons      = $showVoteButtons      ?? false;   // render ACTIVE buttons (only on vote page)
    $hideDisabledButtons  = $hideDisabledButtons  ?? false;   // when true: render NOTHING on the right side
    $disabledVoteText     = $disabledVoteText     ?? null;    // custom disabled label
    $hideVoteStatus       = $hideVoteStatus       ?? false;   // hide entire right-side status area
    $votedSongId          = $votedSongId          ?? null;    // ID of song user voted for (purple glow)
    $isAdmin              = auth()->check() && auth()->user()->is_admin;
@endphp

<div class="list-group">
@forelse ($songs as $song)
    @php
        $title     = trim($song->title ?? '');
        $label     = $title !== '' ? $title : 'Melodie YouTube';
        $yt        = trim($song->youtube_url ?? '');
        $isMine    = auth()->check() && (int) ($song->user_id ?? 0) === (int) auth()->id();
        $isVoted   = $votedSongId && (int) ($song->id ?? 0) === (int) $votedSongId;
        $isDisqualified = (bool) ($song->is_disqualified ?? false);
        $disqReason = $song->disqualification_reason ?? 'Descalificat';

        $canVote = $showVoteButtons && !$userHasVotedToday && !$isMine && !$isDisqualified && auth()->check();
        
        $itemClass = $isMine ? 'my-song' : ($isVoted ? 'voted-song' : ($isDisqualified ? 'disqualified-song' : ''));
    @endphp

    <div class="list-group-item d-flex justify-content-between align-items-center song-item {{ $itemClass }}">
        <div class="d-flex align-items-center gap-3">
            {{-- Play button (keeps your 3D styles) --}}
            <button
                type="button"
                class="play3d"
                aria-label="Redă"
                @if($yt !== '')
                    data-youtube-url="{{ $yt }}"
                    data-bs-toggle="modal"
                    data-bs-target="#youtubeModal"
                @else
                    disabled
                    title="Nu există link YouTube"
                @endif
            >
                <span class="play3d-core">
                    <span class="play3d-triangle"></span>
                </span>
            </button>

            <span class="fw-semibold song-title">{{ $label }}</span>
            
            @if($isDisqualified)
                <span class="badge bg-danger ms-2" title="{{ $disqReason }}">❌ Descalificat</span>
            @endif
        </div>

        {{-- Right side (vote button / status + admin controls) --}}
        <div class="d-flex align-items-center">
            @unless ($hideVoteStatus)
                @if ($hideDisabledButtons)
                    {{-- Explicitly hide anything on the right (used by Upload page / preview states) --}}
                @else
                    @if ($canVote)
                        {{-- ACTIVE vote button (keep .vote-btn for JS + vanish) --}}
                        <button type="button"
                                class="btn btn-sm btn-success vote-btn ms-2"
                                data-song-id="{{ $song->id }}"
                                data-cycle-id="{{ $song->cycle_id ?? ($song->contest_cycle_id ?? 0) }}">
                            Votează
                        </button>
                    @else
                        {{-- Disabled/teaser states (layout preserved on vote page) --}}
                        @php
                            $why = $userHasVotedToday
                                    ? 'Ai votat deja'
                                    : ($isMine ? 'Nu poți vota propria melodie' : ($disabledVoteText ?? 'Vot închis'));
                        @endphp
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary vote-btn ms-2"
                                disabled
                                title="{{ $why }}">
                            {{ $why }}
                        </button>
                    @endif
                @endif
            @endunless
            
            {{-- ADMIN DISQUALIFY BUTTONS (always visible for admin, regardless of hideVoteStatus) --}}
            @if($isAdmin)
                @if($isDisqualified)
                    <button type="button" 
                            class="btn btn-sm btn-outline-success admin-disqualify-toggle ms-2"
                            data-song-id="{{ $song->id }}"
                            data-action="enable"
                            title="Re-activează melodia">
                        ✓ Re-activează
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger admin-disqualify-toggle ms-2"
                            data-song-id="{{ $song->id }}"
                            data-action="disqualify"
                            title="Descalifică melodia">
                        ✗ Descalifică
                    </button>
                @endif
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info mb-0">Nu au fost încă adăugate melodii.</div>
@endforelse
</div>
