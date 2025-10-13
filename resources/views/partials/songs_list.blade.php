{{-- resources/views/partials/songs_list.blade.php --}}

@php
    // Safe fallbacks (parent view may omit any of these)
    $userHasVotedToday    = $userHasVotedToday    ?? false;
    $showVoteButtons      = $showVoteButtons      ?? false;   // render ACTIVE buttons (only on vote page)
    $hideDisabledButtons  = $hideDisabledButtons  ?? false;   // when true: render NOTHING on the right side
    $disabledVoteText     = $disabledVoteText     ?? null;    // custom disabled label
    $hideVoteStatus       = $hideVoteStatus       ?? false;   // hide entire right-side status area
@endphp

<div class="list-group">
@forelse ($songs as $song)
    @php
        $title  = trim($song->title ?? '');
        $label  = $title !== '' ? $title : 'Melodie YouTube';
        $yt     = trim($song->youtube_url ?? '');
        $isMine = auth()->check() && (int) ($song->user_id ?? 0) === (int) auth()->id();

        $canVote = $showVoteButtons && !$userHasVotedToday && !$isMine && auth()->check();
    @endphp

    <div class="list-group-item d-flex justify-content-between align-items-center song-item {{ $isMine ? 'my-song' : '' }}">
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
        </div>

        {{-- Right side (vote button / status) --}}
        @unless ($hideVoteStatus)
            <div class="d-flex align-items-center">
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
            </div>
        @endunless
    </div>
@empty
    <div class="alert alert-info mb-0">Nu au fost încă adăugate melodii.</div>
@endforelse
</div>
