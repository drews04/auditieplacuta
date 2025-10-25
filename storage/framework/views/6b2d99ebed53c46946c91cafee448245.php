<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    // Pass any paginator: LengthAwarePaginator or Paginator
    'paginator',
    // Use the compact previous/next only template if true
    'simple' => false,
    // Optional anchor (e.g. "replies" → links end with #replies)
    'fragment' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    // Pass any paginator: LengthAwarePaginator or Paginator
    'paginator',
    // Use the compact previous/next only template if true
    'simple' => false,
    // Optional anchor (e.g. "replies" → links end with #replies)
    'fragment' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($paginator): ?>
    <?php
        if ($fragment) {
            // add #fragment to all links
            $paginator->fragment($fragment);
        }
    ?>

    <?php if($paginator->hasPages()): ?>
        <?php if($simple): ?>
            <?php echo e($paginator->links('vendor.pagination.neon-simple')); ?>

        <?php else: ?>
            <?php echo e($paginator->links('vendor.pagination.neon')); ?>

        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\components\pagination.blade.php ENDPATH**/ ?>