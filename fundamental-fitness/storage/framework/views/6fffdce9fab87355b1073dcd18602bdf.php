<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Change Password'); ?>

<div class="container-fluid">
    <div class="pate-content-wrapper-dash ">
        <div class="page-title-row ">
            <h5>Change Password</h5>
        </div>

        <!-- Change Password Form Section -->
        <div class="whiteBg d-flex justify-content-center">
            <div class="confirm-pass w-100" style="max-width: 400px;">
                <div class="mb-3">
                        <form method="POST" action="<?php echo e(route('admin.password.update')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="form-group mb-1">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="currentPassword" class="form-control" placeholder="******" autocomplete="off">
                            </div>
                            <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <div class="form-group mb-1">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="newPassword" class="form-control" placeholder="******" autocomplete="off">
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

                            <div class="form-group mb-1">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" id="confirmPassword" class="form-control" placeholder="******" autocomplete="off">
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

                            <div class="w-100 mb-3">
                                <button type="submit" class="btn btn-primary w-100 mt-2">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Change Password Form Section -->
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* Basic Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Body Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
    }

    .children-detail {
        background-color: #f8f9fa;
        padding: 20px;
    }

    .page-title-row h4 {
        color: #333;
    }

    .btn-outline-dark {
        color: #343a40;
        border-color: #343a40;
    }

    .btn-outline-dark:hover {
        background-color: #343a40;
        color: white;
    }

    /* Form Card */
    .card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background-color: white;
    }

    .card-body {
        padding: 30px;
    }

    /* Input fields */
    .form-label {
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control {
        height: 45px;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .card {
            padding: 15px;
        }

        .card-body {
            padding: 15px;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/auth/change_pass.blade.php ENDPATH**/ ?>