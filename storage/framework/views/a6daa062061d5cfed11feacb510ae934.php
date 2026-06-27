<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['showLoader' => true]));

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

foreach (array_filter((['showLoader' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? config('app.name', 'AMIS Payment')); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/AMIS_Logo.png')); ?>">
    <link rel="shortcut icon" href="<?php echo e(asset('images/AMIS_Logo.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Inter:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

    <!-- Prevent FOUC -->
    <style>
        [x-cloak] { display: none !important; }
        .page-content { opacity: 0; transition: opacity 0.2s; }
        .page-content.show { opacity: 1; }
    </style>

    <!-- Scripts & Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="font-sans antialiased" x-data="{ pageLoaded: <?php echo e($showLoader ? 'false' : 'true'); ?> }" x-init="
    if (!<?php echo e($showLoader ? 'true' : 'false'); ?>) {
        document.querySelector('.page-content').classList.add('show');
    } else {
        const shown = sessionStorage.getItem('amis_loaded');
        if (shown) {
            pageLoaded = true;
            document.querySelector('.page-content').classList.add('show');
        } else {
            setTimeout(() => {
                pageLoaded = true;
                sessionStorage.setItem('amis_loaded', '1');
                document.querySelector('.page-content').classList.add('show');
            }, 800);
        }
    }
">
    <!-- Initial Loading Screen (only on F5 / first visit) -->
    <?php if($showLoader): ?>
        <?php if (isset($component)) { $__componentOriginal4d6bed2ebceb29e0a9932fbda627422a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-loader','data' => ['xShow' => '!pageLoaded','xCloak' => true,'xTransition:leave' => 'transition ease-in duration-300','xTransition:leaveStart' => 'opacity-100','xTransition:leaveEnd' => 'opacity-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => '!pageLoaded','x-cloak' => true,'x-transition:leave' => 'transition ease-in duration-300','x-transition:leave-start' => 'opacity-100','x-transition:leave-end' => 'opacity-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a)): ?>
<?php $attributes = $__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a; ?>
<?php unset($__attributesOriginal4d6bed2ebceb29e0a9932fbda627422a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4d6bed2ebceb29e0a9932fbda627422a)): ?>
<?php $component = $__componentOriginal4d6bed2ebceb29e0a9932fbda627422a; ?>
<?php unset($__componentOriginal4d6bed2ebceb29e0a9932fbda627422a); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php
        $toastError = session('error') ?: ($errors->any() ? $errors->first() : null);
    ?>
    <?php if(session('success') || session('info') || session('warning') || $toastError): ?>
        <div class="toast-stack">
            <?php if(session('success')): ?>
                <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'success','message' => session('success')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('success'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if(session('info')): ?>
                <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'info','message' => session('info')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'info','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('info'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if(session('warning')): ?>
                <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'warning','message' => session('warning')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('warning'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if($toastError): ?>
                <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'error','message' => $toastError]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'error','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($toastError)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Page Content -->
    <div class="page-content" x-show="pageLoaded" x-cloak 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100">
        <?php echo e($slot); ?>

    </div>

    <script>
        document.addEventListener('click', function (event) {
            if (event.defaultPrevented) return;

            var clickable = event.target.closest('a, button');
            if (!clickable) return;
            if (clickable.closest('.enrollment-page')) return; // Let Alpine handle enrollment form loading

            if (clickable.tagName === 'A' && clickable.getAttribute('href') && !clickable.getAttribute('href').startsWith('#') && !clickable.getAttribute('target')) {
                if (
                    clickable.classList.contains('btn-primary') ||
                    clickable.classList.contains('family-action-primary') ||
                    clickable.classList.contains('family-finalize') ||
                    clickable.classList.contains('family-action-payment') ||
                    clickable.classList.contains('family-add-yes') ||
                    clickable.classList.contains('logout-btn') ||
                    clickable.getAttribute('href').includes('/enroll') ||
                    clickable.getAttribute('href').includes('/payment') ||
                    clickable.getAttribute('href').includes('/finalize')
                ) {
                    // Show page load loader overlay instead of button spinner
                    var loader = document.querySelector('.initial-loading-screen');
                    if (loader) {
                        loader.style.setProperty('display', 'flex', 'important');
                        loader.style.opacity = '1';
                    }
                }
            }
        });
    </script>

    <script>
        (function () {
            var userId = <?php echo json_encode(auth()->id(), 15, 512) ?>;
            var shouldClearDraftCache = <?php echo json_encode((bool) session('clear_draft_cache'), 15, 512) ?>;
            var discardedDraftApplicantId = <?php echo json_encode(session('discarded_draft_applicant_id'), 15, 512) ?>;

            function draftKeys(applicantId) {
                var keys = [];

                if (userId) {
                    keys.push('amis_enrollment_draft_user_' + userId + '_applicant_' + (applicantId || 'new'));
                }

                keys.push('amis_enrollment_draft');

                return keys;
            }

            window.amisEnrollmentDraftCache = {
                clear: function (applicantId) {
                    // A discarded child draft has two copies: the backend row and this browser cache.
                    // Clear only the matching child key so sibling drafts stay intact and cannot rehydrate stale data.
                    draftKeys(applicantId).forEach(function (key) {
                        try { localStorage.removeItem(key); } catch (_) {}
                        try { sessionStorage.removeItem(key); } catch (_) {}
                    });
                }
            };

            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (!(form instanceof HTMLFormElement)) return;

                if (form.dataset.confirmMessage && !window.confirm(form.dataset.confirmMessage)) {
                    event.preventDefault();
                    return;
                }

                if (form.matches('[data-clear-draft-form]')) {
                    var applicantInput = form.querySelector('input[name="applicant_id"]');
                    window.amisEnrollmentDraftCache.clear(applicantInput ? applicantInput.value : null);
                }

                // Show page load loader overlay instead of button spinner (unless handled by Alpine inside enrollment-page)
                if (!form.closest('.enrollment-page')) {
                    var loader = document.querySelector('.initial-loading-screen');
                    if (loader) {
                        loader.style.setProperty('display', 'flex', 'important');
                        loader.style.opacity = '1';
                    }
                }
            });

            if (shouldClearDraftCache && discardedDraftApplicantId) {
                window.amisEnrollmentDraftCache.clear(discardedDraftApplicantId);
            }
        })();
    </script>

    
    <?php if(auth()->guard()->check()): ?>
    <form id="idle-logout-form" method="POST" action="<?php echo e(route('logout')); ?>" style="display:none;">
        <?php echo csrf_field(); ?>
    </form>
    <div id="idle-warning" style="display:none; position:fixed; bottom:1.5rem; right:1.5rem; z-index:99999; background:#fef2f2; border:1.5px solid #fca5a5; border-radius:14px; padding:1rem 1.25rem; box-shadow:0 8px 30px rgba(0,0,0,0.12); max-width:340px; font-family:'Inter',sans-serif; animation: idleSlideIn 0.3s ease-out;">
        <div style="display:flex; align-items:flex-start; gap:0.75rem;">
            <svg style="width:22px;height:22px;flex-shrink:0;margin-top:2px;" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <div>
                <div style="font-weight:700; font-size:0.875rem; color:#991b1b; margin-bottom:0.25rem;">Session Expiring</div>
                <div style="font-size:0.8125rem; color:#b91c1c; line-height:1.4;">
                    You'll be logged out in <strong id="idle-countdown" style="font-family:monospace;font-size:0.9rem;">2:00</strong> due to inactivity.
                </div>
                <button onclick="window.amisIdleReset()" style="margin-top:0.65rem; background:#dc2626; color:#fff; border:none; border-radius:8px; padding:0.4rem 1rem; font-size:0.8rem; font-weight:700; cursor:pointer; font-family:inherit;">
                    Stay Logged In
                </button>
            </div>
        </div>
    </div>
    <style>
        @keyframes idleSlideIn {
            from { opacity:0; transform:translateY(20px); }
            to { opacity:1; transform:translateY(0); }
        }
    </style>
    <script>
        (function () {
            var IDLE_MINUTES = <?php echo e((int) config('session.idle_timeout', 30)); ?>;
            var WARNING_SECONDS = 120; // Show warning 2 minutes before logout
            var IDLE_MS = IDLE_MINUTES * 60 * 1000;
            var WARNING_MS = IDLE_MS - (WARNING_SECONDS * 1000);
            var STORAGE_KEY = 'amis_last_active';

            var idleTimer = null;
            var warningTimer = null;
            var countdownInterval = null;
            var warningEl = document.getElementById('idle-warning');
            var countdownEl = document.getElementById('idle-countdown');

            function now() { return Date.now(); }

            function updateStorage() {
                try { localStorage.setItem(STORAGE_KEY, String(now())); } catch (_) {}
            }

            function resetTimers() {
                clearTimeout(idleTimer);
                clearTimeout(warningTimer);
                clearInterval(countdownInterval);
                if (warningEl) warningEl.style.display = 'none';

                updateStorage();

                warningTimer = setTimeout(showWarning, WARNING_MS);
                idleTimer = setTimeout(doLogout, IDLE_MS);
            }

            function showWarning() {
                if (!warningEl || !countdownEl) return;
                warningEl.style.display = 'block';
                warningEl.style.animation = 'none';
                void warningEl.offsetHeight; // trigger reflow
                warningEl.style.animation = 'idleSlideIn 0.3s ease-out';

                var remaining = WARNING_SECONDS;
                countdownEl.textContent = formatTime(remaining);
                countdownInterval = setInterval(function () {
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(countdownInterval);
                        doLogout();
                    } else {
                        countdownEl.textContent = formatTime(remaining);
                    }
                }, 1000);
            }

            function formatTime(secs) {
                var m = Math.floor(secs / 60);
                var s = secs % 60;
                return m + ':' + String(s).padStart(2, '0');
            }

            function doLogout() {
                clearTimeout(idleTimer);
                clearTimeout(warningTimer);
                clearInterval(countdownInterval);
                try { localStorage.removeItem(STORAGE_KEY); } catch (_) {}
                var form = document.getElementById('idle-logout-form');
                if (form) form.submit();
            }

            // Expose reset for the "Stay Logged In" button
            window.amisIdleReset = resetTimers;

            // Track user activity
            var events = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
            var throttled = false;
            function onActivity() {
                if (throttled) return;
                throttled = true;
                setTimeout(function () { throttled = false; }, 5000); // Throttle to once per 5s
                resetTimers();
            }
            events.forEach(function (ev) {
                document.addEventListener(ev, onActivity, { passive: true });
            });

            // Sync across tabs — if another tab logs out or resets, follow suit
            window.addEventListener('storage', function (e) {
                if (e.key === STORAGE_KEY) {
                    if (e.newValue === null) {
                        // Another tab logged out
                        doLogout();
                    } else {
                        // Another tab was active — reset our timers
                        resetTimers();
                    }
                }
            });



            // Start
            resetTimers();
        })();
    </script>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/tatsuya/Projects/AMIS/amis_payment/resources/views/layouts/guest.blade.php ENDPATH**/ ?>