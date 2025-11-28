<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Dashboard'); ?>

<div class="container-fluid">
    <div class="page-content-wrapper">
        <h4 class="my-3">Dashboard</h4>
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="<?php echo e(route('admin.userIndex')); ?>">
                        <div class="dash-item-icon">
                            <img src="<?php echo e(asset('assets/images/total-users.svg')); ?>">
                        </div>
                        <div>
                            <h6>TOTAL USERS</h6>
                            <p class="mb-0"><?php echo e($total_users); ?></p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                <a href="<?php echo e(route('admin.exerciseIndex')); ?>">
                        <div class="dash-item-icon">
                            <img src="<?php echo e(asset('assets/images/exerciesicon.svg')); ?>">
                        </div>
                        <div>
                            <h6>EXERCISES</h6>
                            <p class="mb-0"><?php echo e($total_exercises); ?></p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="#">
                        <div class="dash-item-icon">
                            <img src="<?php echo e(asset('assets/images/total-workout.svg')); ?>">
                        </div>
                        <div>
                            <h6>Total EXERCISE VIDEO VIEWS</h6>
                            <p class="mb-0"><?php echo e($total_exercise_video_views); ?></p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="#">
                        <div class="dash-item-icon">
                        <img src="<?php echo e(asset('assets/images/fitnes-chall.svg')); ?>">
                        </div>
                        <div>
                            <h6>Weekly Workout Completion Rate</h6>
                            <p class="mb-0"><?php echo e($total_weekly_workout_completion_rate); ?></p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="dash-item">
                    <a href="#">
                        <div class="dash-item-icon">
                        <img src="<?php echo e(asset('assets/images/fitnes-chall.svg')); ?>">
                        </div>
                        <div>
                            <h6>Total Subscription Revenue</h6>
                            <p class="mb-0"><?php echo e($total_subscription_revenue); ?></p>
                        </div>
                    </a>
                </div>
        </div>
    </div>
    <div class="footer">
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>