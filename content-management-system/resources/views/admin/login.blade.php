<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Design by foolishdeveloper.com -->
    <title>Parkscape</title>

    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />



    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo1.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}" />

    {{-- <link rel="stylesheet" href="{{asset('scss/theme-default.scss')}}" /> --}}


    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('/assets/js/config.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css" />


    <style media="screen">
        *,
        *:before,
        *:after {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url({{ asset('images/3.jpg') }});
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            height: 100vh;
            width: 100vw;
            color: black;
        }

        .background {
            width: 430px;
            height: 520px;
            position: absolute;
            transform: translate(-50%, -50%);
            left: 50%;
            top: 50%;
        }

        .background .shape {
            height: 200px;
            width: 200px;
            position: absolute;
            border-radius: 50%;
        }

        /* .shape:first-child {
            background: linear-gradient(#1845ad,
                    #23a2f6);
            left: -80px;
            top: -80px;
        }

        .shape:last-child {
            background: linear-gradient(to right,
                    #48D33A,
                    #FFDA15);
            right: -30px;
            bottom: -80px;
        } */

        form {
            height: 520px;
            width: 400px;
            /* background-color: rgba(255, 255, 255, 0.13); */
            background-color: #ffffffd1;
            position: absolute;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 40px rgba(8, 7, 16, 0.6);
            padding: 50px 35px;
        }

        form * {
            font-family: 'Poppins', sans-serif;
            color: white;
            letter-spacing: 0.5px;
            outline: none;
            border: none;
        }

        form h3 {
            font-size: 32px;
            font-weight: 500;
            line-height: 42px;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 30px;
            font-size: 16px;
            font-weight: 500;
            color: white;
        }

        input {
            display: block;
            background-color: white;
            border-radius: 3px;
            padding: 0 10px;
            margin-top: 8px;
            font-size: 14px;
            font-weight: 300;
        }

        ::placeholder {
            color: #f6f1f1;
        }

        button {
            margin-top: 50px;
            width: 100%;
            background-color: #ffffff;
            color: black;
            padding: 15px 0;
            font-size: 18px;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
        }

        .social {
            margin-top: 30px;
            display: flex;
        }

        .social div {
            background: red;
            width: 150px;
            border-radius: 3px;
            padding: 5px 10px 10px 5px;
            background-color: white;
            color: #eaf0fb;
            text-align: center;
        }

        .social div:hover {
            background-color: white;
        }

        .social .fb {
            margin-left: 25px;
        }

        .social i {
            margin-right: 4px;
        }

        .btn-primary {
            background: linear-gradient(0deg, #48D33A 0%, #2FA224 0.01%, #48D33A 100%);
            box-shadow: 0px 2px 9px rgb(0 0 0 / 17%);
            border-radius: 5px;
            color: white;
            border: none;
        }


::-webkit-scrollbar {
    display: none;

}

    </style>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.css'>
    <script src="https://unpkg.com/bulma-toast"></script>
</head>

<body>

    <div class="bs-toast toast toast-placement-ex m-2" role="alert" aria-live="assertive" aria-atomic="true"
        style="position: fixed; top: 0; right: 0;" data-delay="2000">
        <div class="toast-header">
            <i class='bx bx-bell me-2'></i>
            <div class="me-auto fw-semibold" id="header-toast"></div>
            <small></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toast-body">
            Hello
        </div>
    </div>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form role="form" method="post" action="{{ route('admin.login.post') }}" data-parsley-validate
        autocomplete="off">
        @csrf

        <div class="justify-content-center align-items-center d-flex">
            <img height="50px" width="50px" style="object-fit: contain;" src="{{ asset('images/logo1.png') }}">
            &nbsp;
            <img height="50px" width="150px"src="{{ asset('images/park1.png') }}"
                style="object-fit: contain; padding-top:3px;">
        </div>
        <h6 class="mt-3 text-center" style="color:black; ">Enter your registered email and password to continue.</h6>
        <div class="mb-1">
            <label for="email" class="form-label text-primary">Email</label>
            <input type="text" class="form-control" id="email" name="email"
                placeholder="Enter your email or username" autofocus autocomplete="new-password" />
        </div>
        <div class="mb-3 form-group">
            <div class="d-flex justify-content-between">
                <label class="form-label text-primary" for="password">Password</label>
                {{-- <a href="auth-forgot-password-basic.html">
                    <small>Forgot Password?</small>
                </a> --}}
            </div>
            <div class="input-group input-group-merge">
                {{-- <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" /> --}}


                <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" autocomplete="new-password">
                <span class="input-group-text cursor-pointer" onclick="PassHideShow(this)"><i
                        class="bx bx-show text-dark"></i></span>


                {{-- <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span> --}}
            </div>
        </div>

        <div class="btn-container mt-5">
            <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
        </div>
    </form>


    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/ui-toasts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script src="{{ asset('/assets/js/config.js') }}"></script>
    <script src="https://unpkg.com/bulma-toast"></script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    @include('admin.includes.alerts')
    {{-- @yield('js') --}}
    @stack('js')
</body>

</html>
