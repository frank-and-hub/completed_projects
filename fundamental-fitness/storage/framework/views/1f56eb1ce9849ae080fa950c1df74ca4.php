<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Successful</title>
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
        p {
            color: #555;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thank You!</h2>
        <p><?php echo e(session('message')); ?></p>
    </div>
</body>
</html>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/thankyou.blade.php ENDPATH**/ ?>