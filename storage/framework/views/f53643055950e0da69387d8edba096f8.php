<script src="<?php echo e(asset('js/user-menu.js')); ?>?v=<?php echo e(filemtime(public_path('js/user-menu.js'))); ?>"></script>

<div class="user-menu-container" id="ap-user-menu">
    <?php if(auth()->guard()->check()): ?>
        <span id="user-name" class="user-dropdown-toggle">Salut, <?php echo e(Auth::user()->name); ?></span>

        <ul id="user-dropdown" class="user-dropdown-list hidden">
            <li><a href="<?php echo e(route('user.user_profile')); ?>">Profilul meu</a></li>
            <li><a href="<?php echo e(route('user.statistics')); ?>">Statistici personale</a></li>
            <li><a href="<?php echo e(route('abilities.index')); ?>">Abilitățile mele</a></li>
            <li><a href="<?php echo e(route('user.settings')); ?>">Setări cont</a></li>
            <li class="logout">
                <a href="<?php echo e(route('logout.get')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Deconectare</a>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display:none;"><?php echo csrf_field(); ?></form>
            </li>
        </ul>
    <?php else: ?>
        <span class="username">Salut, vizitator</span>
    <?php endif; ?>
</div>

<?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views/user/user-menu.blade.php ENDPATH**/ ?>