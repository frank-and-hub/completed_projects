<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin : <?php echo $__env->yieldContent('admin-title'); ?></title>
    <link href="<?php echo e(asset('assets/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/style.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/responsive.css')); ?>" rel="stylesheet">

    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">


    <link href="<?php echo e(asset('assets/css/custom.css')); ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('assets/images/fav-icon.png')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>

</head>
<?php $isCollapsed = session('menu_collapse', false); ?>

<body class="innerbody <?php echo e($isCollapsed ? 'menu-collapse' : ''); ?>">
    <aside class="sidebar">
        <div class="closemenu-btn"><img src="<?php echo e(asset('assets/images/Close_round_fill.png')); ?>" class="img-fluid"></div>
        <div class="text-center sildebarlogo">
            <a href="<?php echo e(route('admin.dashboard')); ?>">
                <img src="<?php echo e(asset('assets/images/logo-login.svg')); ?>" class="img-fluid logo-icon">
            </a>
        </div>
        <div class="menubar-holder">
            <ul class="menubar">
                <li class="<?php echo e(Request::routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.dashboard')); ?>"><img
                            src="<?php echo e(asset('assets/images/dashboard-icon.svg')); ?>">
                        <span>Dashboard</span></a>
                </li>

                <li class="mt-2 <?php echo e(Request::routeIs('admin.userIndex') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.userIndex')); ?>"><img src="<?php echo e(asset('assets/images/user-icon.svg')); ?>">
                        <span>Users</span></a>
                </li>
                <li class="mt-2 <?php echo e(Request::routeIs('admin.exercise*') ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('admin.exerciseIndex')); ?>">
                        <img src="<?php echo e(asset('assets/images/Workout-plans.svg')); ?>">
                        <span>Exercises</span>
                    </a>
                </li>

        </div>
    </aside>
    <!--header part-->
    <div class="header">
        <a href="javascript:void(0)" class="slidetoggle" id="sidebarToggle"><img
                src="<?php echo e(asset('assets/images/menu (2).svg')); ?>"> </a>
        <div class="user-set-menu dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <img src="<?php echo e(asset('assets/images/user-lg-pic.svg')); ?>" class="me-2">
                <div style="line-height: 1;" class="pe-2">
                    <h6><?php echo e(ucfirst($currentUserInfo->fullname)); ?></h6>
                </div>
            </a>
            <ul class="dropdown-menu">

                <li class="d-flex px-2"><img src="<?php echo e(asset('assets/images/lock.svg')); ?>" alt="">
                    <a class="dropdown-item ps-2" href="<?php echo e(route('admin.password.change')); ?>">Change Password</a>
                </li>
                <li class="d-flex px-2"><img src="<?php echo e(asset('assets/images/logout.svg')); ?>" alt="">
                    <a class="dropdown-item ps-2" href="<?php echo e(route('admin.logout')); ?>">Logout</a>
                </li>

            </ul>
        </div>
    </div>
    <!--header part-->
    <!--PAGE CONTENT-->
    <?php echo $__env->yieldContent('content'); ?>
    <!--PAGE CONTENT-->
    <script>
        var csrf = "<?php echo e(csrf_token()); ?>";
        var baseUrl = "<?php echo e(url('/')); ?>";
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="<?php echo e(asset('assets/js/bootstrap.bundle.min.js')); ?>"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="<?php echo e(asset('assets/js/custom.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
<script>
    $('#sidebarToggle').on('click', function() {
        $.ajax({
            url: '<?php echo e(route('admin.toggle.sidebar')); ?>',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            success: function(res) {
                if (res.collapsed) {
                    $('body').addClass('menu-collapse');
                } else {
                    $('body').removeClass('menu-collapse');
                }
            }
        });
    });
</script>
<script>
    <?php if(session('success')): ?>
        toastr.success("<?php echo e(session('success')); ?>");
    <?php endif; ?>
    <?php if(session('error')): ?>
        toastr.error("<?php echo e(session('error')); ?>");
    <?php endif; ?>
</script>
<?php echo $__env->yieldPushContent('script'); ?>

</html>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/layout/index.blade.php ENDPATH**/ ?>