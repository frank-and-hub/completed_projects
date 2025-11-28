<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Change Password'); ?>

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Change Password</h4>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="whiteBg">
                    <form action="<?php echo e(route('admin.changePassword')); ?>" method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label for="current-password" class="form-label">Current Password</label>
                                <input type="text" class="form-control" name="current_password"
                                    placeholder="Enter current password" id="current-password">
                                <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="form-valid-error"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="new-password" class="form-label">New Password</label>
                                <input type="text" class="form-control" name="new_password"
                                    placeholder="Enter password" id="new-password">
                                <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="form-valid-error"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="text" class="form-control" name="new_password_confirmation"
                                    placeholder="Enter confirm password" id="confirm-password">
                                <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="form-valid-error"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn admin-btn">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/change-password/index.blade.php ENDPATH**/ ?>