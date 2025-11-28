<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h2 {
            color:#EC5D1F;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
        }
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
        }
        button {
            padding: 12px;
            background-color:#EC5D1F;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color:#EC5D1F;
        }
        .errors {
            color: red;
            margin-bottom: 15px;
        }
        /* Error alert box */
        .errors {
            background: #ffe6e6;
            border: 1px solid #ff4d4d;
            color: #b30000;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: left;
        }
        .errors ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .errors li::before {
            content: "⚠ ";
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if($errors->any()): ?>
            <div class="errors">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.reset.update')); ?>">
            <?php echo csrf_field(); ?>

            <input type="hidden" name="token" value="<?php echo e(old('token',$token)); ?>">
            <input type="hidden" name="email" value="<?php echo e(old('email',$email)); ?>">

            <input type="password" name="password" placeholder="New Password" required>

            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/reset_password.blade.php ENDPATH**/ ?>