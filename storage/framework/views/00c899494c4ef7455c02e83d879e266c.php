
<?php
  $flat = $flat ?? false;

  // Simple linkifier: turns http(s):// or www. into clickable links, keeps newlines
  $linkify = function (string $text): string {
      $escaped = e($text);

      // Autolink http(s) and www.
      $linked = preg_replace(
          '~(?i)\b((?:https?://|www\.)[^\s<]+)~',
          '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-neon">$1</a>',
          $escaped
      );

      // Ensure www.* gets https://
      $linked = preg_replace('~href="www\.~i', 'href="https://www.', $linked);

      // Preserve line breaks
      return nl2br($linked);
  };
?>

<div class="forum-thread-card forum-post" id="post-<?php echo e($post->id); ?>">
    <div class="forum-post-header mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="forum-thread-author">
                <i class="fas fa-user me-2"></i>
                <strong><?php echo e($post->user->name ?? 'Utilizator'); ?></strong>
            </div>
            <div class="forum-thread-time">
                <i class="fas fa-clock me-2"></i>
                <?php echo e($post->created_at->diffForHumans()); ?>

                <?php if($post->isEdited()): ?>
                    <small class="text-muted ms-2">(editat)</small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="forum-post-body post-body">
        <?php echo $linkify($post->body); ?>

    </div>

    <div class="forum-post-actions mt-3 d-flex align-items-center gap-2">
        <button class="forum-like-btn" data-type="post" data-id="<?php echo e($post->id); ?>">
            <i class="far fa-heart <?php echo e($post->likedBy(auth()->id()) ? 'is-liked' : ''); ?>"></i>
            <span class="forum-like-count"><?php echo e($post->likes()->count()); ?></span>
        </button>

        <?php if(auth()->guard()->check()): ?>
        <button class="forum-reply-btn"
            data-post-id="<?php echo e($post->parent_id ? $post->parent_id : $post->id); ?>"
            data-user-name="<?php echo e($post->user->name ?? 'Utilizator'); ?>">↩ Răspunde</button>
        <?php endif; ?>

        <div class="ms-auto d-flex gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $post)): ?>
                <a href="<?php echo e(route('forum.posts.edit', $post)); ?>" class="btn btn-secondary btn-sm">Editează</a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $post)): ?>
                <form action="<?php echo e(route('forum.posts.destroy', $post)); ?>" method="POST"
                      onsubmit="return confirm('Ștergi acest răspuns?');" class="d-inline">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-secondary btn-sm">Șterge</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if($post->children && $post->children->count() > 0): ?>
        <div class="forum-post-children mt-3">
            <?php $__currentLoopData = $post->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="forum-thread-card forum-post forum-post-reply" id="post-<?php echo e($child->id); ?>">
                    <div class="forum-post-header mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="forum-thread-author">
                                <i class="fas fa-user me-2"></i>
                                <strong><?php echo e($child->user->name ?? 'Utilizator'); ?></strong>
                            </div>
                            <div class="forum-thread-time">
                                <i class="fas fa-clock me-2"></i>
                                <?php echo e($child->created_at->diffForHumans()); ?>

                                <?php if($child->isEdited()): ?>
                                    <small class="text-muted ms-2">(editat)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="forum-post-body post-body">
                        <?php if($child->parent && $child->parent->user): ?>
                            <div class="replying-to-pill mb-2">
                                Răspunzi lui <strong>&#64;<?php echo e($child->parent->user->name); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php echo $linkify($child->body); ?>

                    </div>

                    <div class="forum-post-actions mt-2 d-flex align-items-center gap-2">
                        <button class="forum-like-btn" data-type="post" data-id="<?php echo e($child->id); ?>">
                            <i class="far fa-heart <?php echo e($child->likedBy(auth()->id()) ? 'is-liked' : ''); ?>"></i>
                            <span class="forum-like-count"><?php echo e($child->likes()->count()); ?></span>
                        </button>
                        <?php if(auth()->guard()->check()): ?>
                        <button class="forum-reply-btn ms-2"
                                data-post-id="<?php echo e($child->parent_id ?: $child->id); ?>"
                                data-user-name="<?php echo e($child->user->name ?? 'Utilizator'); ?>">↩ Răspunde</button>
                        <?php endif; ?>

                        <div class="ms-auto d-flex gap-2">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $child)): ?>
                                <a href="<?php echo e(route('forum.posts.edit', $child)); ?>" class="btn btn-secondary btn-sm">Editează</a>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $child)): ?>
                                <form action="<?php echo e(route('forum.posts.destroy', $child)); ?>" method="POST"
                                      onsubmit="return confirm('Ștergi acest răspuns?');" class="d-inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-secondary btn-sm">Șterge</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\forum\partials\post.blade.php ENDPATH**/ ?>