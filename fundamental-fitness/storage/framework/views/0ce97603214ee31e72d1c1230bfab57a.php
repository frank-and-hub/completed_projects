<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Login'); ?>

<div class="row g-0 login-wrapper">
    <div class="col-lg-6 login-left">
        <div class="text-center loginLeftImg"></div>
    </div>
    <div class="col-lg-6  col-md-12 login-right">
        <div class="loginRightImg">
            <div class="login-container">
                <div class="text-center mb-5"><img src="<?php echo e(asset('assets/images/logo-login.svg')); ?>"></div>
                <form method="post" action="<?php echo e(route('admin.loginAuth')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="whiteBg">
                        <h4>Sign In</h4>
                        <?php if(session('status')): ?>
                        <div class="alert alert-success text-center mt-3">
                            <?php echo e(session('status')); ?>

                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Email<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" placeholder="Please enter your email" name="email" value="">
                            </div>
                            <?php $__errorArgs = ['email'];
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
                            <label for="exampleInputEmail1" class="form-label">Password<span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-lg" placeholder="Please enter your password" name="password" value="">
                            </div>
                            <?php $__errorArgs = ['password'];
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
                            <div class="d-flex align-items-center justify-space-between">
                                <div class="form-check">
                                    <label class="form-check-label" for="flexCheckDefault">
                                    </label>
                                </div>

                                <a href="<?php echo e(route('admin.forgotPassword')); ?>" class="forgotPass ms-auto">Forgot Password?</a>

                            </div>
                        </div>
                        <button type="submit" class="btn btn-lg btn-primary w-100">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.auth.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>