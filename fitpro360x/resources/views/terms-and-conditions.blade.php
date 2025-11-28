<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/fav-icon.png') }}" type="image/png" sizes="64x64">
  <link href="{{ asset('assets-frontend/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets-frontend/css/slick.css') }}" rel="stylesheet">
  <link href="{{ asset('assets-frontend/css/slick-theme.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets-frontend/css/aos.css') }}">
  <link href="{{ asset('assets-frontend/css/style.css') }}" rel="stylesheet">
  <link href="{{ asset('assets-frontend/css/responsive.css') }}" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <title>Term & Conditions :: FitPro360X</title>
</head>
<body>
  <div class="outerContainer">
    <header class="header">
      <nav class="navbar navbar-expand-lg navbar-light headerBg fixed-top">
        <div class="container align-items-center">
          <a class="navbar-brand logo" href="{{ url('/') }}"><img src="{{ asset('assets-frontend/images/logo.png') }}" alt="FitPro360X"
              title="Build With B"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse " id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link page-scroll" href="{{ url('/') }}#banner">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="{{ url('/') }}#aboutUs">About Us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="{{ url('/') }}#Features">Features</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="{{ url('/') }}#app_screens">Snapshots</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="{{ url('/') }}#contactUs">Contact Us </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>
    <div class="mainBody">      
      <section class="aboutBlog contentPagesOuter" id="aboutUs">
        <div class="container">
          <div class="row justify-content-center align-items-center">           
            <div class="col-md-12">
              <h2 class="title_main">Terms & Conditions</h2>                    
              <h3>1. Acceptance of Terms</h3>
              <p>By accessing or using the FitPro360X mobile app or web portals, you agree to be bound by these Terms and Conditions. If you do not agree, please refrain from using the service.</p>
              <h3>2. Eligibility</h3>
              <p>You must be at least 13 years old to use this application. Users under 18 must use the application under the supervision of a parent or guardian.</p>
              <h3>3. User Accounts</h3>
              <p>Users are responsible for maintaining the confidentiality of their login credentials and for all activities under their account. You agree to provide accurate, current, and complete information.</p>
              <h3>4. Subscriptions & Payments</h3>
              <p>
                  FitPro360X offers three subscription plans (Workout Only, Gold, Platinum). Features are available as per your active plan.
                  Subscription upgrades/downgrades follow the platform-specific (iOS/Android) policies.
                  No refund is guaranteed unless governed by Apple/Google refund rules.
              </p>
              <h3>5. Content Ownership</h3>
              <p>All workout plans, Meal Plans, exercises, and challenges are proprietary to FitPro360X. Users may not copy, distribute, or reuse content for commercial purposes without prior permission.</p>
              <h3>6. Health Disclaimer</h3>
              <p>
                  The app is intended for general fitness guidance only. It does not substitute for professional medical advice, diagnosis, or treatment. Consult your physician before starting any new exercise or diet program.
              </p>
              <h3>7. Limitations of Liability</h3>
              <p>FitPro360X and its affiliates are not responsible for any injuries, damages, or losses arising from the use or misuse of the application or its content.</p>
              <h3>8. Account Termination</h3>
              <p>We reserve the right to suspend or terminate accounts that violate these terms or abuse system functionality.</p>
              <h3>9. Changes to Terms</h3>
              <p>We may update these Terms & Conditions from time to time. Continued use of the app implies your acceptance of the revised terms.</p>
            </div>
          </div>
        </div>
      </section>   
    </div>
    <footer class="footerBlog">
      <div class="bg-footer">
        <div class="container-fluid">
          <img class="img-fluid" src="{{ asset('assets-frontend/images/logo.png') }}" width="" alt="">
          <div class="footerMenu">
            <a aria-current="page" href="{{ url('/') }}#banner">Home &nbsp; |</a>
            <a aria-current="page" href="{{ url('/') }}#aboutUs">About Us &nbsp; |</a>
            <a aria-current="page" href="{{ url('/') }}#Features">App Features &nbsp; |</a>
            <a href="{{ url('privacy-policies') }}">Privacy Policy &nbsp; |</a>
            <a href="javascript:void(0);">Terms And Condition </a>
          </div>
          <div class="social mt-2">
            <a href="#" class="ms-2"><img src="{{ asset('assets-frontend/images/facebook.svg') }}" alt=""></a>
            <a href="#" class="ms-2"><img src="{{ asset('assets-frontend/images/instagram.svg') }}" alt=""></a>
          </div>
        </div>
      </div>
      <div class="col-md-12 text-center">
        <div class="ftCopyRight">
          <p>Copyright © 2025 FitPro360X - All Rights Reserved.</p>
        </div>
      </div>
    </footer>
  </div>
      <script src="{{ asset('assets-frontend/js/jquery.min.js') }}"></script>
      <script src="{{ asset('assets-frontend/js/bootstrap.bundle.min.js') }}"></script>
      <script src="{{ asset('assets-frontend/js/slick.min.js') }}"></script>
      <script src="{{ asset('assets-frontend/js/aos.js') }}"></script>
<script>        
        $(document).ready(function () {        
          setTimeout(() => {
            $('.screen_slider').slick({
              dots: false,
              arrows: true,
              infinite: true,
              speed: 300,
              slidesToShow: 4,
              slidesToScroll: 1,
              autoplay: true,
              responsive: [
                {
                  breakpoint: 767,
                  settings: {
                    dots: false,
                    // arrows: false,
                    slidesToShow: 1,
                  }
                }
              ]
            });
          }, "500");

          // features section slider
          setTimeout(() => {
            $('.features-sld').slick({
              dots: false,
              arrows: true,
              infinite: true,
              speed: 300,
              slidesToShow: 1,
              slidesToScroll: 1,
              // autoplay: true,
              responsive: [
                {
                  breakpoint: 767,
                  settings: {
                    dots: false,
                    // arrows: false,
                    slidesToShow: 1,
                  }
                }
              ]
            });
          }, "500");
        });
      </script>
         <script>
        AOS.init();
      </script>
      <script>
  // Close navbar on nav-link click (for mobile)
  $(document).ready(function () {
    $('.navbar-nav .nav-link').on('click', function () {
      $('.navbar-collapse').collapse('hide');
    });
  });
</script>
</body>
</html>