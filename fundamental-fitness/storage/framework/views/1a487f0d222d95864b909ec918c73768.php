<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Reset Password'); ?>

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"></div>
    </div>
    <div class="col-lg-6  col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="<?php echo e(asset('assets/images/logo-login.svg')); ?>"></div>
                <form method="post" action="<?php echo e(route('admin.resetPassword', $token)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="whiteBg">
                        <h4>Reset password</h4>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg" placeholder="Enter new password"
                                    name="new_password" value="">
                            </div>
                            <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm password<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg"
                                    placeholder="Enter confirm password" name="new_password_confirmation"
                                    value="">
                            </div>
                            <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="btn-block">
                            <button type="submit" class="btn btn-lg btn-primary w-100">
                                Continue
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.auth.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/auth/reset-password.blade.php ENDPATH**/ ?>