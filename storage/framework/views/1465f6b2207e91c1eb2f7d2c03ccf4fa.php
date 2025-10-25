<div class="forum-thread-card" data-category="<?php echo e($thread->category->name); ?>">
    <!-- Category Badge -->
    <div class="forum-category-badge">
        <?php echo e($thread->category->name); ?>

    </div>

    <!-- Thread Title -->
    <div class="forum-thread-title">
        <a href="<?php echo e(route('forum.threads.show', ['thread' => $thread->slug])); ?>">
            <?php echo e($thread->title); ?>

        </a>
    </div>

    <!-- Thread Excerpt -->
    <div class="forum-thread-excerpt">
        <?php echo e(Str::limit($thread->body, 150)); ?>

    </div>

    <!-- Thread Meta -->
    <div class="forum-thread-meta">
        <div class="forum-thread-author">
            <span class="forum-username">
                <i class="fas fa-user forum-username__icon"></i>
                <?php echo e($thread->user->name ?? 'Utilizator'); ?>

            </span>
        </div>
        <div class="forum-thread-time">
            <i class="fas fa-clock me-2"></i>
            <?php echo e($thread->created_at->diffForHumans()); ?>

        </div>
    </div>

    <!-- Thread Stats -->
    <div class="forum-thread-stats">
        <div class="forum-stat" data-stat="replies">
            <i class="fas fa-comments me-2"></i>
            <span class="forum-stat-value"><?php echo e($thread->replies_count); ?></span>
            <span>răspunsuri</span>
        </div>
        <div class="forum-stat" data-stat="views">
            <i class="fas fa-eye me-2"></i>
            <span class="forum-stat-value"><?php echo e($thread->views_count); ?></span>
            <span>vizualizări</span>
        </div>
    </div>

    <!-- Last Activity -->
    <?php if($thread->last_posted_at): ?>
        <div class="forum-thread-activity">
            <i class="fas fa-history me-2"></i>
            Ultima activitate <?php echo e($thread->last_posted_at->diffForHumans()); ?>

            <?php if($thread->lastPostUser): ?>
                de <span class="forum-username">
                    <i class="fas fa-user forum-username__icon"></i>
                    <?php echo e($thread->lastPostUser->name); ?>

                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\forum\partials\thread_card.blade.php ENDPATH**/ ?>