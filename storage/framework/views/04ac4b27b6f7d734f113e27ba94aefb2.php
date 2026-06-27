<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'info',
    'message',
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
    'type' => 'info',
    'message',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $icon = match ($type) {
        'success' => 'M20 6 9 17l-5-5',
        'error' => 'M18 6 6 18M6 6l12 12',
        'warning' => 'M12 9v4m0 4h.01',
        default => 'M12 16v-4m0-4h.01',
    };
?>

<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, 5200)"
    x-show="show"
    x-transition:enter="toast-enter"
    x-transition:leave="toast-leave"
    class="toast toast-<?php echo e($type); ?>"
    role="status"
>
    <span class="toast-icon" aria-hidden="true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <?php if($type === 'warning'): ?>
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <path d="<?php echo e($icon); ?>"/>
            <?php elseif($type === 'info'): ?>
                <circle cx="12" cy="12" r="10"/>
                <path d="<?php echo e($icon); ?>"/>
            <?php else: ?>
                <path d="<?php echo e($icon); ?>"/>
            <?php endif; ?>
        </svg>
    </span>
    <span class="toast-message"><?php echo e($message); ?></span>
    <button type="button" class="toast-close" @click="show = false" aria-label="Dismiss notification">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
    </button>
</div>
<?php /**PATH /home/tatsuya/Projects/AMIS/amis_payment/resources/views/components/toast.blade.php ENDPATH**/ ?>