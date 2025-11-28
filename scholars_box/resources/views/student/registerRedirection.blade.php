<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScholarsBox Scholarship Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="message">
        <h1>Welcome to the ScholarsBox Scholarship Application!</h1>
        <p>You're being directed to the ScholarsBox Sign up page! Enter your details and sign up the scholarship now!</p>
    </div>

    <script>
        setTimeout(function()  {
            window.location.href = "{{ route('Student.register') }}";
        }, 5000); // 3000 milliseconds = 3 seconds
    </script>
</body>
</html>
