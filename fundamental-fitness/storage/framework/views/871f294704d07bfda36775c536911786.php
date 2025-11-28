<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Profile'); ?>
<?php

    // pree($currentUserInfo);
?>
<div class="container-fluid pt-3 bg children-detail">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-0 ms-3">View User Details</h4>
            <a href="manage-children.html" class="btn btn-outline-dark  mb-2"><img src="" class="mb-1">Back</a>
        </div>
        <div class="bg-white-container ms-2 mt-3">
            <div class="row">
                <div class="col-md-2 user-im">
                    <img src="<?php echo e(get_avatar($currentUserInfo->profile_photo)); ?>" alt="img" width="100%">
                </div>
                <div class="col-md-10 user-detail">
                    <div class="ms-3">
                        <h3><?php echo e($currentUserInfo->first_name . ' ' . $currentUserInfo->last_name); ?></h3>
                    </div>
                    <div class="ms-3 mt-3">

                        <div class="mb-3 ">
                            <p class="mb-1" style="font-weight: 600; font-size: 14px;">Email</p>
                            <div class="bg-light-green">
                                <p class="mb-0"><?php echo e($currentUserInfo->email ?? '-'); ?></p>
                            </div>

                        </div>


                        <div class="mb-3 ">
                            <p class="mb-1" style="font-weight: 600; font-size: 14px;">Mobile</p>
                            <div class="bg-light-green">
                                <p class="mb-0"><?php echo e($currentUserInfo->phone_no ?? '-'); ?></p>
                            </div>

                        </div>
                        <div class="mb-3 ">
                            <p class="mb-1" style="font-weight: 600; font-size: 14px;">DOB</p>
                            <div class="bg-light-green">
                                <p class="mb-0"><?php echo e(getDateInFormat($currentUserInfo->dob) ?? '-'); ?></p>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/profile/index.blade.php ENDPATH**/ ?>