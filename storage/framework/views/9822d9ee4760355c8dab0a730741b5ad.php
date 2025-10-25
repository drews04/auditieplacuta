

<?php $__env->startSection('content'); ?>
    <div class="login-container">
        <h2>Autentificare</h2>

        
        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <span class="checkmark-icon">✅</span>
                <span><?php echo e(session('success')); ?></span>
            </div>
        <?php endif; ?>

        
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <form method="POST" action="<?php echo e(route('login.attempt')); ?>">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email">Adresă de email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    required
                    placeholder="exemplu@email.com"
                >
            </div>

            <div class="form-group">
                <label for="password">Parolă</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Parola ta"
                >
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember">
                    Ține-mă minte
                </label>
            </div>

            <button type="submit">Autentifică-te</button>
        </form>
    </div>
<?php $__env->stopSection(); ?><?php /**PATH C:\xampp\htdocs\auditieplacuta\resources\views\auth\login.blade.php ENDPATH**/ ?>