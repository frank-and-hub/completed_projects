<!DOCTYPE html>
<html lang="en">

<head>
    <title>Find Nearby Parks with the Parkscape App</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Find Nearby Parks with the Parkscape App">
    <meta name="description"
        content="Find your next outdoor adventure in Boston and beyond with Parkscape. With 150+ searchable features like playgrounds, outdoor gyms, hiking trails, and more, finding parks has never been easier">
    <meta name="tags" content="Parks In USA, Parks in Canada">
    <meta name="google-site-verification" content="bYcg9SikSjWa6m9lqvYX3C2KoZ3mgAtawCMQ5X8zk6U" />
    <title>Parkscape</title>
    <!-- google fonts -->
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo1.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google tag (gtag.js) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@700&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TR9LM7ZZN4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-TR9LM7ZZN4');
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <!-- aos-animation -->
    <link rel="stylesheet" href="{{ asset('assets/landing-page/css/sal.css') }}">
    <!-- loding-css -->
    <link rel="stylesheet"
        href="{{ asset('assets/landing-page/https://cdn.jsdelivr.net/npm/pace-js@latest/pace-theme-default.min.css') }}">
    <!-- stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/landing-page/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/landing-page/css/responsive.css') }}">
</head>

<body>
    <div class="hero_sec">
        <img src="{{ asset('assets/landing-page/images/hero-banner.jpg') }}" alt="Parkscape Banner"
            class="cover_thumbnail">
        <div class="container">
            <div class="hero_row">
                <div class="hero-content" data-sal="fade" data-sal-delay="200">
                    <div title="Parkscape"><img src="{{ asset('assets/landing-page/images/logo.svg') }}"
                            alt="Parkscape Logo"></div>
                    <h1>Making Discovering Outdoor Fun Simple</h1>
                    <a class="btn_green" href="https://parkscape.app/blog/" title="Discover Great Parks">Discover Great
                        Parks</a>
                    <div class="btn_group">
                        <a href="https://apps.apple.com/us/app/parkscape/id6447565798" target="_blank"
                            title="Download Parkscape On App Store"><img
                                src="{{ asset('assets/landing-page/images/app-store.svg') }}" alt="Play Store"> <span
                                class="sr-only">Download Parkscape On App Store</span></a>
                        <a href="https://play.google.com/store/apps/details?id=com.parkscape&hl=en_US&gl=US&pli=1"
                            target="_blank" title="Download Parkscape On Google Store"><img
                                src="{{ asset('assets/landing-page/images/play-store.svg') }}" alt="Play Store"> <span
                                class="sr-only">Download Parkscape On Google Store</span></a>
                    </div>
                </div>
                <!-- content/end -->
                <div class="hero-thumbnail" data-sal="slide-up" data-sal-delay="500">
                    {{-- <img src="images/hero-thumbnail.png" alt="Parkscape Thumbnail"> --}}
                    <img src="{{ asset('assets/landing-page/images/hero-thumbnail.png') }}" alt="Parkscape Thumbnail">
                </div>
                <!-- hero-thumb/end -->
            </div>
            <!-- hero_row/end -->
        </div>
        <!-- cont/end -->
        <div class="follow-sec" data-sal="fade" data-sal-delay="1000">
            <a href="https://twitter.com/parkscapeapp" target="_blank"><img
                    src="{{ asset('assets/landing-page/images/twitter-new.png') }}" alt="Follow Parkscape On twitter">
                <span class="sr-only">Parkscape On Twitter</span></a>
            <a href="https://www.instagram.com/parkscapeapp/?igshid=MzRlODBiNWFlZA%3D%3D" target="_blank"><img
                    src="{{ asset('assets/landing-page/images/instagram.svg') }}" alt="Follow Parkscape On instagram">
                <span class="sr-only">Parkscape On instagram</span></a>
            <a href="https://www.facebook.com/people/Parkscape/61550881466565/" target="_blank"><img
                    src="{{ asset('assets/landing-page/images/facebook.svg') }}" alt="Follow Parkscape On facebook">
                <span class="sr-only">Parkscape On facebook</span></a>
        </div>
        <!-- follow-sec/end -->
    </div>
    <!-- hero-sec/end -->
    <!-- about-sec -->
    <div class="about_sec">
        <div class="container">
            <div class="about_row row">
                <div class="about_content" data-sal="slide-up">
                    <h2 class="h1">About <span>the App</span></h2>
                    <p>Parkscape is a mobile app designed to help you discover all types of parks. From small
                        neighborhood parks to national parks, from beaches to hiking trails, we got you covered.</p>
                    <p>Search for sports facilities, playgrounds, dog parks, and over 100 more features to quickly
                        locate your next park outing.</p>
                </div>
                <!-- content/end -->
                <div class="about_thumb">
                    <!-- <img src="images/about-thumbnail.jpg" alt="About Parkscape"> -->
                    <svg width="567" height="718">
                        <mask id="svgmask1">
                            <path d="M284.617 90L412.789 164V312L284.617 386L156.445 312V164L284.617 90Z"
                                fill="#D9D9D9" />
                            <path d="M355.117 369L422.234 407.75V485.25L355.117 524L288 485.25V407.75L355.117 369Z"
                                fill="#D9D9D9" />
                            <path d="M277.117 563L344.234 601.75V679.25L277.117 718L210 679.25V601.75L277.117 563Z"
                                fill="#D9D9D9" />
                            <path d="M423.117 500L490.234 538.75V616.25L423.117 655L356 616.25V538.75L423.117 500Z"
                                fill="#D9D9D9" />
                            <path d="M500.117 126L567.234 164.75V242.25L500.117 281L433 242.25V164.75L500.117 126Z"
                                fill="#D9D9D9" />
                            <path d="M419.117 0L486.234 38.75V116.25L419.117 155L352 116.25V38.75L419.117 0Z"
                                fill="#D9D9D9" />
                            <path d="M67.117 203L134.234 241.75V319.25L67.117 358L0 319.25V241.75L67.117 203Z"
                                fill="#D9D9D9" />
                            <path d="M144.617 329L272.789 403V551L144.617 625L16.4452 551V403L144.617 329Z"
                                fill="#D9D9D9" />
                        </mask>
                        <image xmlns:xlink="http://www.w3.org/1999/xlink"
                            xlink:href="{{ asset('assets/landing-page/images/about-thumbnail.jpg') }}"
                            mask="url(#svgmask1)"></image>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <!-- sec/end -->
    <!-- work-sec -->
    <div class="work_sec">
        <div class="container">
            <h3 class="h1">How Parkscape Works</h3>
            <div class="row work_row">
                <div class="work_card" data-sal="slide-up" data-sal-delay="200">
                    <div class="work_count">
                        <span>01</span>
                    </div>
                    <img src="{{ asset('assets/landing-page/images/work-01.svg') }}" alt="Create Account">
                    <h4 class="h1">Create an Account</h4>
                    <p>Create a free account and help our community grow or skip this step and find new places right
                        away.</p>
                </div>
                <div class="work_card" data-sal="slide-up" data-sal-delay="300">
                    <div class="work_count">
                        <span>02</span>
                    </div>
                    <img src="{{ asset('assets/landing-page/images/work-02.svg') }}" alt="Home Page">
                    <h4 class="h1">Discover Great Parks</h4>
                    <p>Browse our home page and find the best outdoor spaces in your area hand-selected by our team.</p>
                </div>
                <div class="work_card" data-sal="slide-up" data-sal-delay="400">
                    <div class="work_count">
                        <span>03</span>
                    </div>
                    <img src="{{ asset('assets/landing-page/images/work-03.svg') }}" alt="Search Parks">
                    <h4 class="h1">Find Your Park</h4>
                    <p>With any possible feature a park can have available to search from, easily find the exact place
                        you're looking for.</p>
                </div>
                <div class="work_card" data-sal="slide-up" data-sal-delay="500">
                    <div class="work_count">
                        <span>04</span>
                    </div>
                    <img src="{{ asset('assets/landing-page/images/work-04.svg') }}" alt="Save Parks">
                    <h4 class="h1">Save Favorites</h4>
                    <p>With a free account you can create lists and save your favorite parks effortlessly to keep track
                        of your most-liked outdoor spaces.</p>
                </div>
            </div>
            <!-- row/end -->
        </div>
    </div>
    <!-- work/end -->
    <div class="feature_sec">
        <div class="container">
            <div class="row feature_row">
                <div class="feature_thumb">
                    <img class="fthumb_1" src="{{ asset('assets/landing-page/images/app-thumb-1.png') }}"
                        alt="Features Thumbnail" data-sal="slide-up" data-sal-delay="200">
                    <img class="fthumb_2" src="{{ asset('assets/landing-page/images/app-thumb-2.png') }}"
                        alt="Features Thumbnail" data-sal="slide-up" data-sal-delay="400">
                </div>
                <div class="feature_content">
                    <h3 class="h1">Features</h3>
                    <ul>
                        <li>Handpicked home page content showcasing the best parks around</li>
                        <li>Search for any feature you're looking for in our growing database of parks</li>
                        <li>Save your favorite places, add photos, and leave reviews with a user profile</li>
                        <!-- <li>Save with your favorite places, add photos, and leave reviews with a user profile</li> -->
                    </ul>
                </div>
            </div>
        </div>
        <!-- cont/end -->
    </div>
    <!-- featire/end -->
    <!-- app-gallery -->
    <div class="app_sec">
        <div class="container">
            <h3 class="h1" data-sal="slide-up" data-sal-delay="300">App <span>Gallery</span></h3>
            <div class="splide" role="group" aria-label="Splide Basic HTML Example">
                <div class="splide__track">
                    <ul class="splide__list">
                        <li class="splide__slide">
                            <img src="{{ asset('assets/landing-page/images/gallery-1.png') }}"
                                alt="Parkscape app screen">
                            <span class="sr-only">Parkscape Home page</span>
                        </li>
                        <li class="splide__slide">
                            <img src="{{ asset('assets/landing-page/images/gallery-2.png') }}"
                                alt="Parkscape app screen">
                            <span class="sr-only">Parkscape Home page</span>
                        </li>
                        <li class="splide__slide">
                            <img src="{{ asset('assets/landing-page/images/gallery-3.png') }}"
                                alt="Parkscape app screen">
                            <span class="sr-only">Parkscape Home page</span>
                        </li>
                        <li class="splide__slide">
                            <img src="{{ asset('assets/landing-page/images/gallery-4.png') }}"
                                alt="Parkscape app screen">
                            <span class="sr-only">Parkscape Home page</span>
                        </li>
                        <li class="splide__slide">
                            <img src="{{ asset('assets/landing-page/images/gallery-5.png') }}"
                                alt="Parkscape app screen">
                            <span class="sr-only">Parkscape Home page</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
    <!-- gallery/end -->
    <div class="download_sec">
        <div class="container">
            <div class="row">
                <div class="download_thumb" data-sal="slide-up" data-sal-delay="300">
                    <img src="{{ asset('assets/landing-page/images/splash-thumb.png') }}"
                        alt="Parkscape Splash Screen">
                    <span class="sr-only">Download Parkscape</span>
                </div>
                <div class="download_content" data-sal="fade" data-sal-delay="1000">
                    <h4 class="h1">Download Our App</h4>
                    <div class="btn_group">
                        <a href="https://apps.apple.com/us/app/parkscape/id6447565798" target="_blank"
                            title="Download Parkscape On App Store"><img
                                src="{{ asset('assets/landing-page/images/white-app-store.svg') }}" alt="Play Store">
                            <span class="sr-only">Download Parkscape On App Store</span></a>
                        <a href="https://play.google.com/store/apps/details?id=com.parkscape&hl=en_US&gl=US&pli=1"
                            target="_blank" title="Download Parkscape On Google Store"><img
                                src="{{ asset('assets/landing-page/images/white-google-store.svg') }}"
                                alt="Play Store"> <span class="sr-only">Download Parkscape On Google Store</span></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- cont/end -->
    </div>
    <!-- download/end -->
    <footer id="footer">
        <div class="container">
            <div class="foot_logo">
                <div><img src="{{ asset('assets/landing-page/images/logo.svg') }}" alt="Parkscape"> <span
                        class="sr-only">Parkscape</span></div>
            </div>
            <div class="row">
                <div class="foot_row" data-sal="fade" data-sal-delay="200">
                    <h5 class="h1">Information</h5>
                    <ul class="foot-nav">
                        <li><a href="{{ route('privacy-policy') }}" target='_blank'>Privacy Policy</a></li>
                        <li><a href="{{ route('terms_and_conditions') }}" target='_blank'>Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="foot_row" data-sal="fade" data-sal-delay="300">
                    <h5 class="h1">Follow Us</h5>
                    <ul class="social-nav">
                        <li><a href="https://www.facebook.com/people/Parkscape/61550881466565/" target="_blank"><img
                                    src="{{ asset('assets/landing-page/images/fb.svg') }}"
                                    alt="Parkscape On Facebook"><span class="sr-only">Follow On Facebook</span></a>
                        </li>
                        <li><a href="https://twitter.com/parkscapeapp" target="_blank"><img
                                    src="{{ asset('assets/landing-page/images/twitter-new-ft.png') }}"
                                    alt="Parkscape On Twitter"><span class="sr-only">Follow On Twitter</span></a></li>
                        <li><a href="https://www.instagram.com/parkscapeapp/?igshid=MzRlODBiNWFlZA%3D%3D"
                                target="_blank"><img
                                    src="{{ asset('assets/landing-page/images/instagram-ico.svg') }}"
                                    alt="Parkscape On Instagram"><span class="sr-only">Follow On instagram</span></a>
                        </li>
                    </ul>
                </div>
                <div class="foot_row" data-sal="fade" data-sal-delay="400">
                    <h5 class="h1">Get In Touch</h5>
                    <ul class="foot-nav foot_info">
                        {{-- <li class="map-ico"><a href="#">5331 Rexford Court,<br> Montgomery AL 36116, USA</a></li>
                    <li class="call-ico"><a href="tel:+1 202 555 0168">+1 202 555 0168</a></li> --}}
                        <li class="mail-ico"><a href="mailto:alex@parkscape.app">alex@parkscape.app</a></li>
                    </ul>
                </div>
            </div>
            <p class="copy_info">© 2023 Parkscape. All Rights Reserved.</p>
        </div>
        <!-- cont/end -->
    </footer>
    <!-- footer/end -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script src="{{ asset('assets/landing-page/js/sal.js') }}"></script>
    <!-- loading -->
    <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
    <script>
        // app-carousel
        document.addEventListener('DOMContentLoaded', function() {
            var splide = new Splide('.splide', {
                updateOnMove: true,
                autoplay: 'true',
                type: 'loop',
                perPage: 3,
                perMove: 1,
                focus: 'center',
                arrow: 'false',
                padding: '10%',
                breakpoints: {
                    640: {
                        perPage: 1,
                    },
                },
                autoScroll: {
                    speed: 1,
                },
            });
            splide.mount();
        });
        // animation
        // AOS.init({once: true});
        sal({
            once: true,
        });
    </script>
</body>

</html>
