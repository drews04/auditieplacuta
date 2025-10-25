<?php $__env->startSection('content'); ?>
<div class="user-page-wrapper container py-5">
    <h1>Profilul meu</h1>

    
    <div class="card user-card mb-4 shadow-sm">
        <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
                <img src="<?php echo e($user->profile_photo_url ?? asset('assets/images/default-user.png')); ?>" 
                     alt="Profile Photo" 
                     class="rounded-circle" 
                     width="150" 
                     height="150"
                     style="object-fit: cover; border: 3px solid #16f1d3;">
                
                
                <button type="button" 
                        class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                        style="width: 40px; height: 40px; padding: 0;"
                        data-bs-toggle="modal" 
                        data-bs-target="#uploadPhotoModal">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>

        <ul class="list-group list-group-flush text-white">
            <li class="list-group-item bg-transparent"><strong>Nume:</strong> <?php echo e($user->name); ?></li>
            <li class="list-group-item bg-transparent"><strong>Email:</strong> <?php echo e($user->email); ?></li>
            <li class="list-group-item bg-transparent"><strong>Data înscrierii:</strong> <?php echo e($user->created_at ? $user->created_at->format('d M Y') : '–'); ?></li>
            <li class="list-group-item bg-transparent"><strong>Participări totale:</strong> <?php echo e($stats->participations ?? 0); ?></li>
            <li class="list-group-item bg-transparent"><strong>Victorii totale:</strong> <?php echo e($stats->wins ?? 0); ?></li>
            <li class="list-group-item bg-transparent"><strong>Jucătorul lunii:</strong> <?php echo e($user->player_of_the_month ? '🏅 Da' : '–'); ?></li>
            <li class="list-group-item bg-transparent"><strong>Jucătorul anului:</strong> <?php echo e($user->player_of_the_year ? '🏆 Da' : '–'); ?></li>
            <li class="list-group-item bg-transparent">
                <strong>Melodie activă:</strong>
                <?php if($activeSong): ?>
                    <?php echo e($activeSong->title); ?>

                    <?php if($activeSong->youtube_url): ?>
                        – <a href="<?php echo e($activeSong->youtube_url); ?>" target="_blank" rel="noopener">Vezi</a>
                    <?php endif; ?>
                <?php else: ?>
                    – niciuna –
                <?php endif; ?>
            </li>
        </ul>
    </div>

    
    <div class="card user-card shadow-sm">
        <h4 class="mb-4">🧠 Informatii Generale</h4>
        <div class="row row-cols-1 row-cols-md-2 g-3">
        
<div class="col d-flex align-items-center justify-content-between p-3 rounded bg-dark border border-info">
    <div class="d-flex align-items-center">
        <svg class="me-2" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2l3 6 6 .5-4.5 4L18 22l-6-3.5L6 22l1.5-9L3 8.5 9 8z"/>
        </svg>
        <span>Puncte All-Time:</span>
    </div>
    <strong class="ms-2"><?php echo e(number_format($allTimePoints)); ?></strong>
</div>


<div class="col d-flex align-items-center justify-content-between p-3 rounded bg-dark border border-info">
    <div class="d-flex align-items-center">
        <svg class="me-2" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5h-2v6h6v-2h-4V7z"/>
        </svg>
        <span>Puncte Anuale:</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <strong><?php echo e(number_format($yearPoints)); ?></strong>
        <a href="<?php echo e(route('leaderboard.index', ['scope' => 'positions'])); ?>" class="btn btn-sm btn-outline-info">Vezi clasamentul</a>
    </div>
</div>


            
            <div class="col d-flex align-items-center">
                <svg class="me-2 text-success" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L15 8l6 .5-4.5 4L18 20l-6-3.5L6 20l1.5-7.5L3 8.5 9 8z"/>
                </svg>
                Victorii Trivia: <strong class="ms-1"><?php echo e($user->trivia_wins); ?></strong>
            </div>

            
            <div class="col d-flex align-items-center">
                <svg class="me-2 text-primary" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3 7h7l-5.5 4.5L18 22l-6-4-6 4 2.5-8.5L2 9h7z"/>
                </svg>
                Misiuni Câștigate: <strong class="ms-1"><?php echo e($user->missions_won); ?></strong>
            </div>

            
            <div class="col d-flex align-items-center">
                <svg class="me-2 text-purple" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
                    2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 
                    14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 
                    6.86-8.55 11.54L12 21.35z"/>
                </svg>
                Voturi primite: <strong class="ms-1"><?php echo e($stats->votes_received ?? 0); ?></strong>
            </div>

            
            <div class="col d-flex align-items-center">
                <svg class="me-2 text-info" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 12l18-12v24L3 12z"/>
                </svg>
                Voturi oferite: <strong class="ms-1"><?php echo e($stats->votes_made ?? 0); ?></strong>
            </div>

            
            <div class="col d-flex align-items-center">
                <svg class="me-2 text-secondary" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 3h18v2H3V3zm0 4h18v2H3V7zm0 4h18v2H3v-2zm0 4h12v2H3v-2zm0 4h12v2H3v-2z"/>
                </svg>
                Participări Concurs: <strong class="ms-1"><?php echo e($stats->participations ?? 0); ?></strong>
            </div>

            
            <div class="col d-flex align-items-center">
                <svg class="me-2 text-pink" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21 16.54 14 
                    22 9.24l-7.19-.61L12 2 9.19 8.63 
                    2 9.24 7.46 14 5.82 21z"/>
                </svg>
                Concursuri câștigate: <strong class="ms-1"><?php echo e($stats->wins ?? 0); ?></strong>
            </div>
        </div>

        
        <div class="mt-3 px-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div><strong>Ultima victorie:</strong> <?php echo e(optional($stats)->last_win_date ? $stats->last_win_date->format('d M Y') : '—'); ?></div>
                <div><strong>Melodie:</strong> <?php echo e(optional($stats)->last_win_song_title ?? '—'); ?></div>
                <div>
                    <a href="<?php echo e(route('me.wins')); ?>" class="btn btn-sm btn-outline-light">Vezi toate victoriile</a>
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="uploadPhotoModalLabel">Schimbă poza de profil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo e(route('user.profile.uploadPhoto')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Alege o poză</label>
                        <input type="file" 
                               class="form-control" 
                               id="profile_photo" 
                               name="profile_photo" 
                               accept="image/jpeg,image/png,image/jpg,image/webp" 
                               required>
                        <div class="form-text text-muted">
                            Max 2MB. Format: JPG, PNG, WEBP
                        </div>
                    </div>
                    
                    
                    <div id="imagePreview" class="text-center mb-3" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #16f1d3;">
                    </div>
                    
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <?php echo e($errors->first()); ?>

                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="submit" class="btn btn-primary">Salvează poza</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Image preview
document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>

    
    <div class="card user-card mt-4 shadow-sm">
        <h4 class="mb-4">🧙‍♂️ Abilități active</h4>
        <div class="row row-cols-2 row-cols-md-4 g-3 text-center px-3 pb-3">
        <?php $__empty_1 = true; $__currentLoopData = $activeAbilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ability): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col">
                <div class="p-3 rounded bg-dark text-white border border-info text-center">
                    
                    <div class="mb-2">
                        <?php switch($ability->code):
                            case ('steal'): ?>
                                <svg width="28" height="28" fill="currentColor" class="text-danger" viewBox="0 0 24 24">
                                    <path d="M12 2c-2 0-4 2-4 4v2H6c-1.1 0-2 .9-2 2v1h16v-1c0-1.1-.9-2-2-2h-2V6c0-2-2-4-4-4zM4 12v8c0 1.1.9 2 2 2h2v-4h8v4h2c1.1 0 2-.9 2-2v-8H4z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('shield'): ?>
                                <svg width="28" height="28" fill="currentColor" class="text-success" viewBox="0 0 24 24">
                                    <path d="M12 2l8 4v6c0 5.25-3.25 10-8 12-4.75-2-8-6.75-8-12V6l8-4z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('switch'): ?>
                                <svg width="28" height="28" fill="currentColor" class="text-warning" viewBox="0 0 24 24">
                                    <path d="M4 7h12v2H4v3L0 8l4-4v3zm16 10H8v-2h12v-3l4 4-4 4v-3z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('2m'): ?>
                                <svg width="28" height="28" fill="currentColor" class="text-info" viewBox="0 0 24 24">
                                    <path d="M12 2l4 9H8l4-9zm0 20c-4.42 0-8-3.58-8-8h2a6 6 0 0012 0h2c0 4.42-3.58 8-8 8z"/>
                                </svg>
                                <?php break; ?>
                            <?php default: ?>
                                <svg width="28" height="28" fill="currentColor" class="text-muted" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                </svg>
                        <?php endswitch; ?>
                    </div>

                    <div>
                        <strong><?php echo e($ability->name); ?></strong><br>
                        <small>
                            <?php echo e($ability->cooldown_remaining > 0 
                                ? 'Cooldown: '.$ability->cooldown_remaining.'z' 
                                : 'Disponibilă'); ?>

                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="row row-cols-2 row-cols-md-4 g-3 text-center px-3 pb-3 w-100">
                <div class="col">
                    <div class="text-light-emphasis small fw-bold">
                        Abilități active: <span class="text-muted">– nicio abilitate –</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\user\user_profile.blade.php ENDPATH**/ ?>