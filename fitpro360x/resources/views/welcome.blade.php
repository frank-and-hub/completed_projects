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
  <title>Home :: FitPro360X</title>
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
                <a class="nav-link page-scroll" href="#banner">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#aboutUs">About Us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#Features">Features</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#app_screens">Snapshots</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#contactUs">Contact Us </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>
    <div class="mainBody">
      <section class="bannerBlog" id="banner">
        <div class="container">
          <div class="row d-flax justify-content-center align-items-center">
            <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-up">
              <span class="bannerWelcome" style="text-transform:capitalize;">Welcome to FitPro360X</span>
              <h1 class="mt-2">Get Ready to Transform Your Fitness Journey</h1>
              <p class="mb-4">FitPro360x – Track. Train. Transform.</p>
              <p class="mb-4">FitPro360x is your personal fitness partner — bringing workouts, meal planning, progress tracking, and expert guidance into one powerful mobile app.</p>
              <p>Download the app now and start your fitness journey!</p>
              <ul class="app-icon-slider">
                <li><a href="#" class=""><img src="{{ asset('assets-frontend/images/appStore.png') }}" alt=""></a></li>
                <li><a href="#" class=""><img src="{{ asset('assets-frontend/images/googlePlay.png') }}" alt=""></a></li>
              </ul>
            </div>
            <div class="col-md-6 text-center" data-aos="fade-left">
              <span class="slidImg"><img src="{{ asset('assets-frontend/images/main_s.png') }}" alt=""></span>
            </div>
          </div>
        </div>
      </section>
      <section class="aboutBlog" id="aboutUs">
        <div class="container">
          <div class="row justify-content-center align-items-center">
            <div class="col-md-5">
              <div class="aboutUs mb-4">
                <img class="img-fluid" src="{{ asset('assets-frontend/images/aboutUs.png') }}" alt="">
              </div>
            </div>
            <div class="col-md-7">
              <h2 class="title_main">About Us</h2>
              <p>At FitPro360x, we believe fitness should be simple, accessible, and personalized. Our mission is to empower individuals to take control of their health by providing a complete fitness ecosystem in one app.

From customized workout plans to meal tracking, progress monitoring, and expert guidance, FitPro360x is designed to support you every step of the way. Whether you’re just starting out, looking to stay consistent, or aiming to hit new personal records, our app adapts to your lifestyle and goals.

With real-time tracking, smart insights, and motivational tools, FitPro360x ensures you stay focused and committed to transforming your body and mind.

Join a growing community of fitness enthusiasts and start your journey toward a healthier, stronger, and more confident you.</p>

              <p>FitPro360x – Track. Train. Transform.</p>              
            </div>
          </div>
        </div>
      </section>
      <section class="features-sec" id="Features">
        <div class="container">
          <div class="features-sec-in features-sld">
            <div class="fea-itm">
              <div class="row align-items-center ">
                <div class="col-md-6">
                  <div class="feature-cntr-img">
                    <img src="{{ asset('assets-frontend/images/fea-img-1.png') }}" class="img-fluid mx-auto d-block">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3 featuresBoxs">                
                    <div>
                      <h6 class="heading-typ1">All-in-One Fitness Companion</h6>
                      <p class="mb-0">FitPro360x combines workouts, meal planning, and progress tracking into one seamless app. Stay organized and focused on your goals without juggling multiple tools.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="fea-itm">
              <div class="row align-items-center ">
                <div class="col-md-6">
                  <div class="feature-cntr-img">
                    <img src="{{ asset('assets-frontend/images/fea-img-2.png') }}" class="img-fluid mx-auto d-block">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3 featuresBoxs">                   
                    <div>
                      <h6 class="heading-typ1">Personalized Plans, Expert Guidance</h6>
                      <p class="mb-0">Get tailored workout routines and nutrition plans designed for your unique needs. Backed by expert insights, the app helps you train smarter, not harder.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="fea-itm">
              <div class="row align-items-center ">
                <div class="col-md-6">
                  <div class="feature-cntr-img">
                    <img src="{{ asset('assets-frontend/images/fea-img-3.png') }}" class="img-fluid mx-auto d-block">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3 featuresBoxs">                    
                    <div>
                      <h6 class="heading-typ1">Track Progress, Stay Motivated</h6>
                      <p class="mb-0">Monitor your performance with real-time tracking and detailed analytics. Celebrate milestones and keep pushing forward on your fitness journey.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="fea-itm">
              <div class="row align-items-center ">
                <div class="col-md-6">
                  <div class="feature-cntr-img">
                    <img src="{{ asset('assets-frontend/images/fea-img-4.png') }}" class="img-fluid mx-auto d-block">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3 featuresBoxs">                
                    <div>
                      <h6 class="heading-typ1">Transform Anytime, Anywhere</h6>
                      <p class="mb-0">Access workouts, meal plans, and progress tracking right from your phone. FitPro360x gives you the flexibility to stay fit whether you’re at home, the gym, or on the go.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="app_screens" id="app_screens">
        <div class="container" data-aos="fade-up" data-aos-offset="-1500">
          <div class="row mb-5">
            <div class="col-md-12 text-center">
              <h2 class="title_main">Snapshots</h2>
            </div>
          </div>
          <div class="screen_slider">
            <div>
              <figure><img src="{{ asset('assets-frontend/images/app-screen1.png') }}" alt="" class="img-fluid"></figure>
            </div>
            <div>
              <figure><img src="{{ asset('assets-frontend/images/app-screen2.png') }}" alt="" class="img-fluid"></figure>
            </div>
            <div>
              <figure><img src="{{ asset('assets-frontend/images/app-screen3.png') }}" alt="" class="img-fluid"></figure>
            </div>
            <div>
              <figure><img src="{{ asset('assets-frontend/images/app-screen4.png') }}" alt="" class="img-fluid"></figure>
            </div>
          </div>
        </div>
      </section>
      <div class="parent-footer-container">
        <section class="contactBgRow">
          <div class="contactUsBlog" id="contactUs">
            <div class="row">
              <div class=" contctBlog">
                <div class="me-2">
                  <h2 class="mb-0">Contact Us</h2>                  
                </div>
                <div class="contactBox mt-4 me-2">
                  <div class="mailIcon">
                    <img src="{{ asset('assets-frontend/images/mail.svg') }}" alt="">
                  </div>
                  <div class="mailDetails">
                    <h5>Email Address</h5>
                    <a href="mailto:info@snapnfix.com">
                      <p class="mb-0">info@fitpro360x.com</p>
                    </a>
                  </div>
                </div>
                <div class="contactBox mt-4 me-2">
                  <div class="mailIcon">
                    <img src="{{ asset('assets-frontend/images/location_icon.svg') }}" alt="">
                  </div>
                  <div class="mailDetails">
                    <h5>Address</h5>
                    <a href="https://maps.app.goo.gl/wFeN1q2SAHcdL5hr6" target="_blank">
                      <p class="mb-0">Birmingham,  UK</p>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
    <footer class="footerBlog">
      <div class="bg-footer">
        <div class="container-fluid">
          <img class="img-fluid" src="{{ asset('assets-frontend/images/logo.png') }}" width="" alt="">
          <div class="footerMenu">
            <a aria-current="page" href="#banner">Home &nbsp; |</a>
            <a aria-current="page" href="#aboutUs">About Us &nbsp; |</a>
            <a aria-current="page" href="#Features">App Features &nbsp; |</a>
            <a href="{{ url('privacy-policies') }}">Privacy Policy &nbsp; |</a>
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