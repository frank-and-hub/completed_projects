<link rel="stylesheet" href="/css/app.css">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css"> --}}

<link rel="shortcut icon" type="image/x-icon" href="http://localhost/images/logo.png" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css" />
  <!-- FavIcon CSS -->
  <!--<link rel="icon" href="{{asset('images/favicon.png')}}" type="image/gif" sizes="16x16">-->

<!--Bootstrap CSS-->
<link rel="stylesheet" type="text/css" href="{{asset('frontend/css/bootstrap.min.css')}}">

<!--Google Fonts CSS-->
<link rel="preconnect" href="https://fonts.googleapis.com/">
<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&amp;display=swap"
    rel="stylesheet">

<!--Font Awesome Icon CSS-->
<link rel="stylesheet" type="text/css" href="{{asset('frontend/css/font-awesome.min.css')}}">

<!-- Slick Slider CSS -->
<link rel="stylesheet" type="text/css" href="{{asset('frontend/css/slick.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('frontend/css/slick-theme.css')}}">

<!-- Wow Animation CSS -->
<link rel="stylesheet" type="text/css" href="{{asset('frontend/css/animate.min.css')}}">

<!-- Main Style CSS  -->
<link rel="stylesheet" type="text/css" href="{{asset('frontend/css/style.css')}}">


<script src="{{asset('frontend/js/hc-sticky.js')}}"></script>


<style>
    /* Sliding Switch */
    .form-check {
        padding-left: 0rem !important;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #0ad480;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #0ad480;
    }

    input:checked+.slider:before {
        transform: translateX(36px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .elegant_box {
        border: 0px solid steelblue;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.45);
        padding: 15px;
        background: #ffffff;
        margin: 20px 0;
    }

    .elegant_box2 {
        border: 0px solid steelblue;
        border-radius: 10px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.45);
        padding: 15px;
        background: #ffffff;
        margin: 20px 0;
    }

    .material-icons {
        font-size: 13px !important;
    }

    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        color: #fff;
        background-color: #007bff !important;
    }

    .error {
        font-size: 11px !important;
        color: orangered;
    }

    .bg-primary {
        background-color: steelblue !important;
    }

    .main-sidebar {
        background: steelblue !important;
        color: white !important;
    }

    .sidebar a {
        color: cornsilk !important;
    }

    .sidebar a:hover,
    .brand-link {
        color: white !important;
    }

    .card-body {
        padding: 15px 15px !important;
        border-radius: 15px;
    }


    .loader-wrapper {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(70, 131, 180, 0.685);
        z-index: 9999;
    }

    .loader {
        border: 8px solid rgba(0, 0, 0, 0.452);
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border-top: 8px solid white;
        position: absolute;
        top: 40%;
        left: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<style>
    .bg-primary {
        background-color: steelblue !important;
    }

    .btn-primary {
        border-radius: 30px !important;
        background-color: steelblue !important;
    }

    .page-item.active .page-link {
        background-color: steelblue !important;
        border-color: steelblue !important;
    }

    .brand-text {
        margin-left: 10px !important;
    }


    .button-container {
        display: flex;
        justify-content: left;
        align-items: left;
        gap: 20px;
        /* Adds space between the buttons */
    }

    .custom-button {
        margin-top: 5px;
        font-size: 13px;
        position: relative;
        padding: 6px 25px;
        overflow: hidden;
        color: white;
        text-decoration: none;
        border: none;
        border-radius: 30px;
        transition: background 0.3s ease-out, box-shadow 0.3s ease-out;
    }

    /* Updated primary button color to steelblue */
    .primary-button {
        background-color: steelblue;
    }

    .primary-button:hover {
        background: linear-gradient(to right, steelblue, #4682b4);
        box-shadow: 0 0 0 4px rgba(70, 130, 180, 0.5);
        text-decoration: none;
        color: white;
    }

    /* Updated secondary button color to a similar green */
    .secondary-button {
        background-color: #a74545;
        /* ...any other properties you might have... */
    }

    .secondary-button:hover {
        background: linear-gradient(to right, #a74545, #962d2d);
        box-shadow: 0 0 0 4px rgba(167, 45, 45, 0.5);
        text-decoration: none;
        color: white;
    }

    .sliding-element {
        position: absolute;
        top: 50%;
        right: 0;
        width: 32px;
        height: 128px;
        margin-top: -64px;
        background-color: rgba(255, 255, 255, 0.1);
        transform: rotate(12deg) translateX(48px);
        transition: transform 1s ease;
    }

    .custom-button:hover .sliding-element {
        transform: rotate(12deg) translateX(-160px);
    }

    .form-control {
        font-size: 14px !important;
    }
</style>

<!-- Additional CSS -->
@yield('styles')
