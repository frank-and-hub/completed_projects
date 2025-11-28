<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="background-color: #f5f6f8; margin: 0; padding: 30px; font-family: Arial, sans-serif;">
    <table width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;">
        <tr>
              <td style="background:#fff1e9; background-color: #fff1e9; color: #000; font-weight: bold; font-size: 20px; text-align:center; padding:15px 0 15px;" > 
          <img src="{{ asset('assets/images/emaillogo.png') }}"
                                    style="height: 100px; width: 100px;" />
      </td>
        </tr>
 
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #2d3748; margin: 0 0 16px;">
                    <strong>Hi {{ ucfirst($name) }},</strong>
                </p>
 
                <p style="font-size: 15px; color: #4a5568; line-height: 1.6;">
                    We received a request to reset the password for your <b> {{ env('APP_NAME') }} </b> Account.
                </p>
 
                <p style="font-size: 15px; color: #4a5568; line-height: 1.6;">
                    To reset your password, please click the button below:
                </p>
 
                <p style="text-align: center; margin: 30px 0;">
                    <a href="{{ $resetUrl }}"
                       style="background-color: #2d3748; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-size: 15px;">
                        Reset Password
                    </a>
                </p>
 
                <p style="font-size: 14px; color: #4a5568;">
                    This password reset link will expire in 60 minutes.
                </p>
 
                <p style="font-size: 14px; color: #4a5568;">
                    If you did not request this change, please ignore this email — your password will remain unchanged.
                </p>
 
                <p style="font-size: 14px; color: #4a5568; margin-top: 24px;">
                 Thanks,<br>  <strong> Team {{ env('APP_NAME') }} </strong>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>