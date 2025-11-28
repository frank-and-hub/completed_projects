<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .receipt-container {
            max-width: 800px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f30051;
        }

        .header h1 {
            margin: 0;
            color: #f30051;
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

        .name {
            color: #f30051;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }


        .login_btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            background-color: #f30051;
            /* Button background color */
            color: #fff;
            /* Text color */
            border: 2px solid transparent;
            transition: all 0.3s ease;
            /* Smooth transition for hover effect */
        }

        /* Hover effect for the login button */
        .login_btn:hover {
            background-color: #f30051;
            /* Darker shade for hover effect */
            border-color: #f30051;
            /* Border color change on hover */
            transform: translateY(-2px);
            /* Slight raise effect */
        }

        /* Focus effect for accessibility */
        .login_btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(38, 143, 255, 0.5);
            /* Blue shadow on focus */
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Login Credential</h1>
        </div>
        <div class="receipt-details">
            <h2>Dear {{ $credentialData['name'] }}</h2>
            <p>We are pleased to welcome you to <b class="name">PocketProperty </b>. Below are your login details:</p>
            @if ($credentialData['type'] == 'agent')
                <p><strong>Agency Name : - </strong>{{ $credentialData['agency_name'] }}</p>
            @endif
            <p><strong>Username : - </strong>{{ $credentialData['email'] }}</p>
            <p><strong>Password : -</strong> {{ $credentialData['password'] }}</p>
            <p>Alternatively, you can log in directly using the following link:</p>
            @if ($credentialData['type'] == 'agency')
                <p>{{ route('sub_login', 'agency') }}</p>
            @elseif($credentialData['type'] == 'agent')
                <p>{{ route('sub_login', 'agent') }}</p>
            @endif
        </div>
        <div class="footer">
            <p>Thank you for choosing PocketProperty!</p>
        </div>
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
