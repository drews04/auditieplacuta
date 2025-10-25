<?php $__env->startSection('content'); ?>
<div class="container py-5 user-page-wrapper">
    <h1 class="mb-4">Victoriile lui <?php echo e($user->name ?? 'utilizator'); ?></h1>

    <div class="card user-card shadow-sm">
        <h4 class="mb-3 px-3 pt-3">Toate victoriile</h4>

        <?php if(isset($wins) && $wins->count()): ?>
            <div class="table-responsive px-3 pb-3">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width:40%">Melodie</th>
                            <th style="width:40%">Tema</th>
                            <th style="width:20%">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $wins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($row->song_title ?? '—'); ?></td>
                                <td><?php echo e($row->theme_title ?: '—'); ?></td>
                                <td><?php echo e(\Illuminate\Support\Carbon::parse($row->won_on)->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="px-3 pb-3">
                <?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $wins]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($wins)]); ?>
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
        <?php else: ?>
            <div class="px-3 pb-3 text-muted">Nicio victorie încă.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\user\wins.blade.php ENDPATH**/ ?>