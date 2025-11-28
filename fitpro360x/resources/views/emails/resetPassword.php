<!DOCTYPE html>
<html>
<head>
    <title>Reset Password Notification</title>
</head>


    <body style="margin:0px; padding:0px; background: #eee;">
        <table style="max-width: 100%; background: #fff;" class="deviceWidth" width="660" cellspacing="0" cellpadding="0"
            border="0" align="center">
            <tbody>
                <tr>
                    <td style="padding:0px;" bgcolor="#ffffff">

                        <table
                            style="width:100%; border-collapse:collapse; font-family: 'Roboto', sans-serif; font-size: 14px;">
                            <tr>
                                <td
                                    style="background:#ecfbf0; background-color: #ecfbf0; color: #000; font-weight: bold; font-size: 20px; text-align:center; padding:20px 0 20px;">
                                      <img src="{{ asset('assets/images/emaillogo.png') }}"
                                    style="height: 100px; width: 100px;" />
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="text-align:left; padding:20px 20px; word-break: break-word; color: #000000; font-size: 14px; ">
                                    

                                    <p style="line-height: 28px;margin: 0 0 20px 0;color: #000000;">
                                        Hi, <strong> {{ ucfirst($name) }}</strong>
                                    </p>
                                    <p>We received a request to reset the password for your {{ env('APP_NAME') }} account.</p>
                                    <p>To reset your password, please click the button below:</p>                                    
                                    
                                    <br>
                                    <p style="margin-bottom: 20px;"><a href="{{ $url }}">Reset Password</a></p>
                                    <br>
                                    <p>If you did not request this change, please ignore this email — your password will remain unchanged.</p>
                                    <p>Thanks,</p>
                                    <p style="line-height: 28px;margin: 0 0 20px 0;">
                                            <strong>Team {{ env('APP_NAME') }}</strong><br />
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
