<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name')); ?> <?php echo $__env->yieldContent('admin-title'); ?></title>
    <link href="<?php echo e(asset('assets/css/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/style.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/responsive.css')); ?>" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('assets/images/fav-icon.png')); ?>">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
</head>

<body>
    <?php echo $__env->yieldContent('content'); ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script src="<?php echo e(asset('assets/js/bootstrap.bundle.min.js')); ?>"></script>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        <?php if(session('flash-error')): ?>
            toastr.error("<?php echo e(session('flash-error')); ?>");
        <?php endif; ?>

        <?php if(session('flash-success')): ?>
            toastr.success("<?php echo e(session('flash-success')); ?>");
        <?php endif; ?>

        <?php if(session('status')): ?>
            toastr.success("<?php echo e(session('status')); ?>");
        <?php endif; ?>

        <?php if(session('message')): ?>
            toastr.error("<?php echo e(session('message')); ?>");
        <?php endif; ?>


    </script>
</body>
</html>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/auth/index.blade.php ENDPATH**/ ?>