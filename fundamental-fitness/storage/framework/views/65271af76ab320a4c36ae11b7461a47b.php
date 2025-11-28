<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
<title><?php echo e(config('app.name')); ?></title>

</head>
<style>
  a {color: #3cbf5f;}
</style>
<body style="margin:0px; padding:0px; background: #eee;" >

  <table style="max-width: 100%; background: #fff;" class="deviceWidth" width="660" cellspacing="0" cellpadding="0" border="0" align="center">
    <tbody>
      <tr>
        <td style="padding:0px;" bgcolor="#ffffff">

  <table style="width:100%; border-collapse:collapse; font-family: 'Roboto', sans-serif; font-size: 14px;" >
    <tr>
           <td style="background:#fff1e9; background-color: #fff1e9; color: #000; font-weight: bold; font-size: 20px; text-align:center; padding:15px 0 15px;" > 
          <img src="<?php echo e(asset('assets/images/emaillogo.png')); ?>"
                                    style="height: 100px; width: 100px;" />
      </td>
    </tr>
    <tr>
      <td style="text-align:left; padding:20px 20px; word-break: break-word; color: #000000; font-size: 14px; " >


        <p style="line-height: 28px;margin: 0 0 20px 0;color: #000000;">
          Hello,
        </p>

        <p>Your One-Time Password (OTP) for verifying your <?php echo e(config('app.name')); ?> account is:</p>

        <br>

        <p style="margin-bottom: 20px;">
          <a href="#" style="background: #EC5D1F; padding: 10px 20px; color: #fff; font-size: 14px; font-weight: bold; text-decoration: none; "><?php echo e($otp); ?></a>
        </p>
        <br>
        <p>This OTP is valid for the next 10 minutes. Please do not share it with anyone.</p>
        <p>If you did not request this, please ignore this message.</p>
        <p>Thanks, <br>
                <strong>Team <?php echo e(config('app.name')); ?></strong><br />
        </p>

      </td>
    </tr>


  </table>
        </td>
      </tr>
    </tbody>
  </table>


</body>
</html>
<?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/emails/otp.blade.php ENDPATH**/ ?>