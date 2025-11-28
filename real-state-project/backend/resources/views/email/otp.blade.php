<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Profile Update with OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .content {
            padding: 20px;
            border: 1px solid #dddddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .otp {
            font-size: 1.2em;
            color: #333333;
            font-weight: bold;
        }

        .company-info {
            text-align: center;
            margin-bottom: 40px;
        }

        .company-info p {
            margin: 5px 0;
        }

        .company-info .bold {
            font-weight: bold;
        }

    </style>
</head>
<body>
    <div class='content'>
        <p>Dear <b>{{$user->name}}</b>,</p>

        <p>We have received a request to update your profile information on PocketProperty. To ensure the security of your account, please use the following One-Time Password (OTP) to verify your request.</p>

        <p class='otp'>Your OTP is: {{$otp}}</p>

        <p>This OTP is valid for the next 5 minutes. Please do not share this OTP with anyone.</p>

        <p>If you did not request this change, please ignore this email or contact our support team immediately.</p>

        <p>Thank you for helping us keep your account secure.</p>

        <p>Best regards,</p>

        {{-- <table cellspacing="0" cellpadding="0" border="0" style=" border-collapse: collapse;table-layout: fixed;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;direction: ltr" emb-background-style="">
            <tbody>
                <tr>
                    <td style="width: 100%; vertical-align: middle; padding-bottom: 15px">
                        <table style="border-collapse: collapse;table-layout: fixed;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;direction: ltr" emb-background-style="">
                            <tbody></tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%;background-color: #F30051; border-radius: 3px">
                        <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse;table-layout: fixed;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;direction: ltr" width="100%" emb-background-style="">
                            <tbody>
                                <tr>
                                    <td style="width: 35%; text-align: left;vertical-align: middle; padding-left: 18px; padding-right: 10px;"><img src="https://cdn.zoviz.com/static/common/frontier/lip/0qgBnRd82p36jK2w.png?v=1" width="100%"></td>
                                    <td style="width: 65%; font-family: Arial, Helvetica, sans-serif;;font-size: 13px;line-height: 20px; padding-top: 10px; padding-bottom: 10px; padding-left:10px;">
                                        <table cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse;table-layout: fixed;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;direction: ltr" emb-background-style="">
                                            <tbody>
                                                <tr>
                                                    <td style="color:#ffffff;text-decoration:none;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;padding-bottom:5px;">Email: <a href="mailto:services@pocketproperty.app" style="color:#ffffff;text-decoration:none;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;">services@pocketproperty.app</a></td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#ffffff;text-decoration:none;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;padding-bottom:5px;">Address: <span>Claremont, Cape Town, WC</span></td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#ffffff;text-decoration:none;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;padding-bottom:5px;">Tel: <a href="tel:+27 79 338 9178" style="color:#ffffff;text-decoration:none;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;">+27 79 338 9178</a></td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#ffffff;text-decoration:none;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;padding-bottom:5px;"><a href="//https://pocketproperty.app" target="_blank" rel="noopener noreferrer" style="color:#ffffff;text-decoration:underline;font-size:13px;font-family:Arial, Helvetica, sans-serif;font-weight:normal;line-height:18px;mso-line-height-rule:exactly;">https://pocketproperty.app</a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table> --}}

        <div class="company-info">
            <p class="bold">PocketProperty</p>
            <p>Claremont</p>
            <p>Cape Town</p>
            <p><a style="color:#000;text-decoration:none" href="mailto:services@pocketproperty.app">services@pocketproperty.app</a></p>
            <p><a style="color:#000;text-decoration:none" href="tel:27 79 338 9178">+27 79 338 9178</a></p>
        </div>

    </div>
</body>
</html>
