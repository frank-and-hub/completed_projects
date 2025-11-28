<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        var email = prompt('Enter your email address:');
        var password = prompt('Enter your password:');

        if (email && password) {

            $.ajax({
                url: `{{ route('api_properties.properties.index') }}`,
                type: 'POST',
                data: {
                    email: email,
                    password: password,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Authentication Successful: ' + res.message);
                        let jsonData = JSON.stringify(res.data, null, 2);
                    } else {
                        alert('Authentication Failed: ' + res.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        } else {
            alert('Username and Password are required.');
        }
    });
</script>

</html>
