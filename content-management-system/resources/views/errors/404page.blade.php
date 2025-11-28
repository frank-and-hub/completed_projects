<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo1.png') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landing-page/css/404page.css') }}" />

    <title>Page Not Found</title>
</head>


<body style="background-color: #F4FFF3">


    <div class="d-flex justify-content-center mt-5">
        <span class="not-found-txt">404 Page Not Found</span><br>
    </div>
    <div class="d-flex justify-content-center">
        <a href="{{ route('admin.dashboard') }}"><small style="font-size: 18px" class="text-success">Go to
                dashboard</small></a>
    </div>

    <div style='position:absolute; bottom:1px; width:100%; height:70%'>
        <div class="app_sec">
            <div class="container">

            </div>
        </div>
    </div>


</body>

</html>
