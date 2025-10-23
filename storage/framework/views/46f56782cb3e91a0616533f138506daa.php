

<?php
    // Safe fallbacks (parent view may omit any of these)
    $userHasVotedToday    = $userHasVotedToday    ?? false;
    $showVoteButtons      = $showVoteButtons      ?? false;   // render ACTIVE buttons (only on vote page)
    $hideDisabledButtons  = $hideDisabledButtons  ?? false;   // when true: render NOTHING on the right side
    $disabledVoteText     = $disabledVoteText     ?? null;    // custom disabled label
    $hideVoteStatus       = $hideVoteStatus       ?? false;   // hide entire right-side status area
    $votedSongId          = $votedSongId          ?? null;    // ID of song user voted for (purple glow)
?>

<div class="list-group">
<?php $__empty_1 = true; $__currentLoopData = $songs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $song): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $title     = trim($song->title ?? '');
        $label     = $title !== '' ? $title : 'Melodie YouTube';
        $yt        = trim($song->youtube_url ?? '');
        $isMine    = auth()->check() && (int) ($song->user_id ?? 0) === (int) auth()->id();
        $isVoted   = $votedSongId && (int) ($song->id ?? 0) === (int) $votedSongId;

        $canVote = $showVoteButtons && !$userHasVotedToday && !$isMine && auth()->check();
        
        $itemClass = $isMine ? 'my-song' : ($isVoted ? 'voted-song' : '');
    ?>

    <div class="list-group-item d-flex justify-content-between align-items-center song-item <?php echo e($itemClass); ?>">
        <div class="d-flex align-items-center gap-3">
            
            <button
                type="button"
                class="play3d"
                aria-label="Redă"
                <?php if($yt !== ''): ?>
                    data-youtube-url="<?php echo e($yt); ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#youtubeModal"
                <?php else: ?>
                    disabled
                    title="Nu există link YouTube"
                <?php endif; ?>
            >
                <span class="play3d-core">
                    <span class="play3d-triangle"></span>
                </span>
            </button>

            <span class="fw-semibold song-title"><?php echo e($label); ?></span>
        </div>

        
        <?php if (! ($hideVoteStatus)): ?>
            <div class="d-flex align-items-center">
                <?php if($hideDisabledButtons): ?>
                    
                <?php else: ?>
                    <?php if($canVote): ?>
                        
                        <button type="button"
                                class="btn btn-sm btn-success vote-btn ms-2"
                                data-song-id="<?php echo e($song->id); ?>"
                                data-cycle-id="<?php echo e($song->cycle_id ?? ($song->contest_cycle_id ?? 0)); ?>">
                            Votează
                        </button>
                    <?php else: ?>
                        
                        <?php
                            $why = $userHasVotedToday
                                    ? 'Ai votat deja'
                                    : ($isMine ? 'Nu poți vota propria melodie' : ($disabledVoteText ?? 'Vot închis'));
                        ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary vote-btn ms-2"
                                disabled
                                title="<?php echo e($why); ?>">
                            <?php echo e($why); ?>

                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="alert alert-info mb-0">Nu au fost încă adăugate melodii.</div>
<?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/partials/songs_list.blade.php ENDPATH**/ ?>