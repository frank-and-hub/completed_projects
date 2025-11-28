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
  <title>Privacy Policy :: FitPro360X</title>
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
              <h2 class="title_main">Privacy Policy</h2>
                <h3>1. Data Collection</h3>
                <p>We collect the following user data:</p>
                <ul>
                    <li>Personal details (name, age, weight, height, fitness goals)</li>
                    <li>Login information (email, password)</li>
                    <li>Activity inputs (workout plans, meals, physical activities)</li>
                    <li>Device and usage data for analytics</li>
                </ul>

                <h3>2. Use of Information</h3>
                <p>Data collected is used to:</p>
                <ul>
                    <li>Personalize your fitness plans</li>
                    <li>Process subscriptions and payments</li>
                    <li>Provide app support and updates</li>
                    <li>Improve app performance and security</li>
                </ul>

                <h3>3. Data Storage & Security</h3>
                <p>User data is stored securely on encrypted servers. We take reasonable steps to prevent unauthorized access, loss, or misuse.</p>

                <h3>4. Sharing of Data</h3>
                <p>We do not sell, rent, or trade your personal data to third parties. Data may be shared with service providers (e.g., payment gateways) strictly for service-related purposes.</p>

                <h3>5. Cookies & Tracking</h3>
                <p>Our app may use cookies or analytics tools to improve user experience and monitor engagement.</p>

                <h3>6. User Rights</h3>
                <p>You may access, modify, or delete your data anytime via the app settings. You may also request complete account deletion.</p>

                <h3>7. Retention Policy</h3>
                <p>Data will be retained for as long as necessary to fulfill the purpose of its collection or as required by law.</p>

                <h3>8. Updates to this Policy</h3>
                <p>We may update this Privacy Policy periodically. Users will be notified of significant changes.</p>

                <h3>9. Contact Information</h3>
                <p>If you have any questions or concerns about this Privacy Policy or how your data is handled, you can contact us at:</p>
                <ul>
                    <li>Email: <a href="mailto:imann@live.co.uk">imann@live.co.uk</a></li>
                    <li>Phone: <a href="tel:00447590024810">00447590024810</a></li>
                </ul>    
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
            <a href="javascript:void(0);">Privacy Policy &nbsp; |</a>
            <a href="{{ url('terms-and-conditions') }}">Terms And Condition </a>
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