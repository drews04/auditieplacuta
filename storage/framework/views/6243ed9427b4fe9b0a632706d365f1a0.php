<?php $__env->startSection('title', 'Forum - Auditie Placuta'); ?>

<?php $__env->startSection('body_class', 'page-forum'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/forum.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="forum-container">
    <div class="container">
        <!-- Forum Header -->
        <div class="forum-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>
                        <?php if(isset($currentCategory) && $currentCategory): ?>
                            <?php echo e($currentCategory->name); ?>

                        <?php else: ?>
                            Forum
                        <?php endif; ?>
                    </h1>
                    <?php if(isset($currentCategory) && $currentCategory): ?>
                        <p class="text-muted mb-0"><?php echo e($currentCategory->description); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?php echo e(route('forum.threads.create')); ?>" class="btn btn-new-thread">
                        <i class="fas fa-plus me-2"></i>Thread Nou
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Messages -->
        <?php if(session('success')): ?>
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Forum Actions -->
        <div class="forum-actions">
            <!-- Search and Sort Row -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="forum-search">
                        <input type="text" placeholder="Caută în thread-uri..." aria-label="Caută thread-uri">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="forum-sort">
                        <select aria-label="Sortează thread-urile">
                            <option value="latest">Ultima Activitate</option>
                            <option value="replies">Cele Mai Multe Răspunsuri</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Categories Row -->
            <div class="forum-categories">
                <a href="<?php echo e(route('forum.home')); ?>" 
                   class="forum-category-pill <?php echo e(!request()->route('category') ? 'active' : ''); ?>">
                    Toate
                </a>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('forum.categories.show', $category->slug)); ?>" 
                       class="forum-category-pill <?php echo e(isset($currentCategory) && $currentCategory && $currentCategory->id === $category->id ? 'active' : ''); ?>">
                        <?php echo e($category->name); ?>

                        <span class="badge bg-secondary ms-1"><?php echo e($category->threads_count); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Threads List -->
        <div class="forum-threads">
            <?php $__empty_1 = true; $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thread): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('forum.partials.thread_card', ['thread' => $thread], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="forum-thread-card text-center">
                    <div class="text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h4>Nu există thread-uri încă</h4>
                        <p>Fii primul care creează un thread în această categorie!</p>
                        <?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('forum.threads.create')); ?>" class="btn btn-new-thread">
                                <i class="fas fa-plus me-2"></i>Creează Thread
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="forum-pagination">
            <?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $threads]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($threads)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $attributes = $__attributesOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $component = $__componentOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__componentOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/forum.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\forum\home.blade.php ENDPATH**/ ?>