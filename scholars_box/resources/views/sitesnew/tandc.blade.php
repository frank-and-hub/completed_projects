<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hyundai Hope Scholarship</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @include('layouts.includes.style')
</head>
<style>
    .hyundai-login {
        background-color: transparent;
    }

    .hyundai-login .h1-title {
        color: #000;
        font-size: 60px;
        font-weight: 800;
        line-height: 70px;
    }

    .hyundai-login p {
        color: black !important;
    }

    .hyundai-two {
        padding: 70px 110px;
    }

    .hyundai-two .row {
        align-items: center;
    }

    #page-id-hyundai .scolarship-title-one h2 {
        font-family: 'Roboto', sans-serif;
        font-size: 45px;
        font-weight: 600;
    }

    .hyundai-two .row .two-head {
        color: #000;
        font-size: 36px;
        font-weight: 700;
        line-height: 44px;
    }

    .hyundai-two .row .two-desc p {
        font-size: 15px;
        line-height: 28px;
        color: #000;
        text-align: justify;
        margin-top: 17px;
        margin-bottom: 30px;
    }

    .program-hyundai {
        padding: 70px 110px;
        background-color: #ededed;
    }

    .program-hyundai .hyundai-three-head h3 {
        color: #000;
        font-size: 60px;
        font-weight: 800;
        line-height: 70px;
    }

    .program-hyundai .hyundai-head-two h5 {
        color: #000;
        font-size: 40px;
        font-weight: 700;
        line-height: 50px;
    }

    .program-hyundai .hyundai-three-desc-one p {
        font-size: 15px;
        line-height: 28px;
        color: #000;
        text-align: justify;
        margin-top: 17px;
        margin-bottom: 30px;
    }

    .hyundai-two .row .hyundai-three-desc-two li {
        font-size: 15px;
        line-height: 28px;
        color: #000 !important;
        margin-top: 17px;
        margin-bottom: 30px;
    }

    #page-id-hyundai .main-footer-one {
        background-color: #013369;
    }

    #page-id-hyundai .footer-copyright-one {
        background-color: #0298d7;
    }

    #page-id-hyundai .sec-btn-two:before {
        background-color: #013369;
    }

    #page-id-hyundai .sec-btn-two {
        background: #0298d7 !important;
        border-radius: 5px !important;
    }

    #page-id-hyundai .sec-btn-one {
        background-color: #013369;
    }

    #page-id-hyundai .sec-btn-one::before {
        background-color: #0298d7 !important;
        border-radius: 5px !important;
    }

    #page-id-hyundai .hyundai-login .h1-title {
        font-size: 30px !important;
        line-height: 50px !important;
        font-family: 'Roboto', sans-serif;
        font-weight: 600 !important;
    }

    #page-id-hyundai .banner-slide-text p {
        font-size: 15px;
        text-align: justify;
    }

    #page-id-hyundai .program-hyundai .hyundai-three-head h3 {
        font-size: 50px !important;
    }

    #page-id-hyundai .scolarship {
        background-color: #013369 !important;
    }

    #page-id-hyundai .sidebar a {
        color: white !important;
    }

    #page-id-hyundai .site-branding-one img {
        width: 150px !important;
    }

    .program-hyundai .hyundai-three-head h4 {
        color: #000;
        font-size: 36px;
        font-weight: 700;
        line-height: 50px;
    }

    .hyundai-t-c {
        text-align: right;
    }

    .hyundai-t-c a {
        font-size: 15px;
        color: white;
    }

    .hyundai-two li {
        color: black !important;
        text-align: justify;
    }

    li,
    p {
        color: black !important;
    }

    .smalldesc div {
        color: black !important;
        font-family: 'Roboto';
    }

    .newReadMoreBtn {
        font-size: 16px;
        text-transform: uppercase;
        color: white;
        background: #013369;
        padding: 10px 20px;
        display: inline-block;
        text-align: left;
        border-radius: 5%;
        margin-top: 20px;
    }

    .newReadMoreBtn:hover {
        color: white;
    }

    .newReadMoreBtn span.leftarrowicon {
        margin-left: 0px;
        transition: all 0.5s ease;
    }

    .newReadMoreBtn:hover span.leftarrowicon {
        margin-left: 15px;
        transition: all 0.5s ease;
    }

    .hyundai-one img {
        width: 100% !important;
    }

    .imageinthissectionone {
        width: 50%;
    }

    .hyundai-sch-disp-main {
        padding: 0px 75px 50px 75px;
        width: 90%;
        margin: auto;
    }

    .sch-dis-card {
        text-align: center;
    }

    .hyundai-sch-disp-main .col-md-4 {
        padding: 0px 50px;
    }

    .card-sch-heading {
        background: #e7e7e7;
        border-radius: 10px 10px 10px 10px;
        margin-bottom: -5px;
        position: relative;
        padding: 10px 25px;
        padding-bottom: 50px;
        min-height: 200px;
    }

    .card-sch-heading .schl-name {
        font-size: 18px;
        font-weight: 600;
    }

    .sch-dis-card-inner {
        padding: 20px;
    }

    .card-sch-detail-one {
        background: #013369;
        padding: 20px 20px 0px 20px;
        color: white;
        font-size: 14px;
    }

    .card-sch-detail {
        background: #013369;
        padding: 5px 20px 30px 20px;
        border-radius: 0px 0px 10px 10px;
        color: white;
        font-size: 18px;
    }

    .card-icon img {
        width: 75px;
        margin: 15px 0px;
    }

    .custom-shape-divider-bottom-1716994080 {
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        overflow: hidden;
        line-height: 0;
    }

    .custom-shape-divider-bottom-1716994080 svg {
        position: relative;
        display: block;
        width: calc(100% + 1.3px);
        height: 50px;
    }

    .custom-shape-divider-bottom-1716994080 .shape-fill {
        fill: #013369;
    }

    #scholarship .col-md-12.scholar-list .course-grid-3 {
        width: 33% !important;
        display: inline-block;
        padding: 0px 10px;
    }

    #scholarship .col-md-12.scholar-list .course-grid-3 .rbt-card.card-list-2 .rbt-card-img {
        width: 100% !important;
    }

    #scholarship .col-md-12.scholar-list .course-grid-3 .rbt-card.card-list-2 {
        display: block !important;
        min-height: 520px;
    }

    #scholarship .col-md-12.scholar-list .course-grid-3 .rbt-card.card-list-2 .rbt-card-body .rbt-card-title {
        font-size: 22px !important;
    }

    .rbt-card.card-list-2 .rbt-card-body {
        padding-left: 25px;
    }

    #scholarship .col-md-12.scholar-list .course-grid-3 .bottom-list {
        display: inline;
    }

    #scholarship .rbt-card-body .row-first-block {
        display: none !important;
    }

    @media only screen and (min-width: 767.5px) and (max-width: 1140px) {
        .hyundai-one {
            margin-top: 70px;
        }

        .hyundai-two {
            padding: 40px 30px;
        }

        .hyundai-two .row .two-head {
            color: #000;
            font-size: 32px;
            font-weight: 700;
            line-height: 40px;
        }

        .hyundai-two .row .two-desc p {
            text-align: justify !important;
        }

        .hyundai-two .row .col-6 {
            width: 100%;
        }

        .program-hyundai {
            padding: 40px 30px;
            background-color: #ededed;
        }

        .program-hyundai .hyundai-three-head h4 {
            color: #000;
            font-size: 32px;
            font-weight: 700;
            line-height: 40px;
        }

        .program-hyundai .hyundai-three-desc-one p {
            text-align: justify;
        }

        #scholarship .scolarship .rbt-course-grid-column.active-list-view .container {
            max-width: 95%;
        }

        #loginnew .container {
            max-width: 100%;
        }

        .banner-content-one {
            padding: 0 30px;
        }

        .main-banner-one.hyundai-login .container .banner-slide-text p {
            text-align: justify !important;
        }

        #page-id-hyundai .hyundai-login .h1-title {
            font-size: 30px !important;
            line-height: 40px !important;
        }

        .main-footer-one .container {
            max-width: 95%;
        }

        .main-footer-one .container .col-md-6 {
            max-width: 65%;
            margin: auto;
        }

        .footer-copyright-one.micro {
            margin-top: 30px;
        }

        .imageinthissectionone {
            width: 100%;
        }

        .hyundai-sch-disp-main {
            padding: 0px 0px 20px 0px;
        }

        .hyundai-sch-disp-main .col-md-4 {
            padding: 0px;
        }

        .card-icon img {
            width: 60px;
        }

        .card-sch-heading {
            min-height: 250px;
        }
    }

    @media only screen and (min-width: 767.5px) and (max-width: 8300px) {
        .card-sch-heading {
            min-height: 270px;
        }
    }

    @media only screen and (max-width:767.5px) {
        .hyundai-one {
            margin-top: 70px;
        }

        .hyundai-two {
            padding: 25px 20px;
        }

        .hyundai-two .row .col-6 {
            width: 100%;
        }

        .hyundai-two .row .two-head {
            color: #000;
            font-size: 30px;
            font-weight: 700;
            line-height: 40px;
        }

        .hyundai-two .row .two-desc p {
            text-align: justify !important;
        }

        .program-hyundai {
            padding: 40px 20px;
        }

        .program-hyundai .hyundai-three-head h4 {
            color: #000;
            font-size: 30px;
            font-weight: 700;
            line-height: 40px;
        }

        .program-hyundai .hyundai-three-desc-one p {
            text-align: justify;
        }

        #scholarship .rbt-course-grid-column .container {
            padding-left: 20px;
            padding-right: 20px;
        }

        .main-banner-one.hyundai-login .container {
            padding-left: 20px;
            padding-right: 20px;
        }

        .main-banner-one.hyundai-login .container .banner-slide-text p {
            text-align: justify !important;
        }

        #page-id-hyundai .hyundai-login .h1-title {
            font-size: 30px !important;
            line-height: 40px !important;
            text-align: left;
        }

        .hyundai-t-c {
            text-align: center;
        }

        .hyundai-login p {
            color: black !important;
            margin-top: -20px !important;
        }

        #scholarship .scholar-list .anchor {
            height: 10px !important;
        }

        #page-id-hyundai .rbt-card .rbt-card-body .row .col-10 {
            width: 100% !important;
        }

        #page-id-hyundai .rbt-card .rbt-card-body .row .col-10 .rbt-card-title a {
            font-size: 22px !important;
        }

        #page-id-hyundai .scholar-list {
            padding-left: 15px !important;
            margin-top: 80px;
        }

        #page-id-hyundai .banner-slide-text {
            margin-top: 30px;
        }

        .newReadMoreBtn {
            font-size: 14px;
            padding: 7px 15px;
        }

        #page-id-hyundai .scolarship-title-one h2 {
            font-size: 30px;
        }

        .imageinthissectionone {
            width: 100%;
        }

        .hyundai-sch-disp-main {
            padding: 0px 0px 20px 0px;
        }

        .hyundai-sch-disp-main .col-md-4 {
            padding: 0px 25px;
        }

    }

    @media only screen and (min-width: 1199px) and (max-width: 1410px) {
        .hyundai-sch-disp-main .col-md-4 {
            padding: 0px 5px;
        }

        .card-sch-heading {
            min-height: 240px;
        }

        #scholarship .col-md-12.scholar-list .course-grid-3 .bottom-list .list-btn {
            margin-top: 10px;
        }

        #scholarship .col-md-12.scholar-list .course-grid-3 .rbt-card.card-list-2 {
            min-height: 560px;
        }
    }

    @media only screen and (min-width: 1411px) {
        .hyundai-sch-disp-main .col-md-4 {
            padding: 0px 25px;
        }

        .card-sch-heading {
            min-height: 240px;
        }
    }
</style>

<body id="page-id-hyundai">

    <!-- Loder Start -->
    <div class="loader-box-two">
        <div class="dank-ass-loader">
            <div class="load-shap">
                <div class="arrow-two up outer outer-18"></div>
                <div class="arrow-two down outer outer-17"></div>
                <div class="arrow-two up outer outer-16"></div>
                <div class="arrow-two down outer outer-15"></div>
                <div class="arrow-two up outer outer-14"></div>
            </div>
            <div class="load-shap">
                <div class="arrow-two up outer outer-1"></div>
                <div class="arrow-two down outer outer-2"></div>
                <div class="arrow-two up inner inner-6"></div>
                <div class="arrow-two down inner inner-5"></div>
                <div class="arrow-two up inner inner-4"></div>
                <div class="arrow-two down outer outer-13"></div>
                <div class="arrow-two up outer outer-12"></div>
            </div>
            <div class="load-shap">
                <div class="arrow-two down outer outer-3"></div>
                <div class="arrow-two up outer outer-4"></div>
                <div class="arrow-two down inner inner-1"></div>
                <div class="arrow-two up inner inner-2"></div>
                <div class="arrow-two down inner inner-3"></div>
                <div class="arrow-two up outer outer-11"></div>
                <div class="arrow-two down outer outer-10"></div>
            </div>
            <div class="load-shap">
                <div class="arrow-two down outer outer-5"></div>
                <div class="arrow-two up outer outer-6"></div>
                <div class="arrow-two down outer outer-7"></div>
                <div class="arrow-two up outer outer-8"></div>
                <div class="arrow-two down outer outer-9"></div>
            </div>
            <p class="loader__label-one">Loading..
                ...</p>
        </div>
    </div>
    <!-- Loder End -->

    <!-- Header Start -->
    <header class="site-header-one microsite-mainheader">
        @include('student.scholarship.calander')

        <!--Navbar Start  -->
        <div class="header-bottom-one">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-3 col-lg-2">
                        <!-- Sit Logo Start -->
                        <div class="site-branding-one">
                            <a href="index.php" title="Scholarsbox">
                                <img src="{{ asset('uploads/HMIF LOGO-1.jpg') }}" alt="Logo">
                            </a>
                        </div>
                        <!-- Sit Logo End -->
                    </div>
                    <div class="col-xl-9 col-lg-10">
                        <div class="header-menu-one">
                            <nav class="main-navigation-one">
                                <button class="toggle-button-one">
                                    <span></span>
                                    <span class="toggle-width-one"></span>
                                    <span></span>
                                </button>
                                <ul class="menu">
                                    <li class="">
                                        <a href="/" title="Home">Home</a>
                                    </li>
                                    <li class="">
                                        <a href="https://hyundai.scholarsbox.in/#aboutTheProgram" title="About">About the program</a>
                                    </li>
                                    <li class="">
                                        <a href="https://hyundai.scholarsbox.in/#scholarship" title="Scholarship">Scholarship</a>
                                    </li>
                                    <li class="">
                                        <a href="https://hyundai.scholarsbox.in/#faqs" title="Faqs">FAQs</a>
                                    </li>
                                    {{-- <li class="">
                                        @if (isset(auth()->user()->id))
                                        <a href="{{route('Student.dashboard.redirect')}}" title="Login">Dashboard</a>

                                        @else
                                        <a href="#loginnew" title="Login">Login / Register</a>
                                        @endif
                                    </li> --}}

                                    <li class="">
                                        <a href="{{route('subdomainterm')}}"
                                            target="_blank" title="T&C">Terms & Conditions</a>
                                    </li>

                                    <li class="">
                                        <a href="https://hyundai.scholarsbox.in" target="_blank" title="How To Apply">How To Apply</a>
                                    </li>


                                </ul>
                            </nav>
                            <!-- <div class="search-box-one">
                                <div class="search-icon-one">
                                    <a href="javascript:void(0);" title="Search"><i class="fa fa-search"
                                            aria-hidden="true"></i></a>
                                </div>
                                <div class="search-input-one">
                                    <div class="search-input-box-one">
                                        <form>
                                            <input type="text" name="search" class="form-input-one"
                                                placeholder="Search Here..." required>
                                            <button type="submit" class="sec-btn-one"><span><i class="fa fa-search"
                                                        aria-hidden="true"></i></span></button>
                                        </form>
                                    </div>
                                </div>
                            </div> -->
                            <div class="black-shadow-one"></div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Navbar End  -->
    </header>
    <!-- Header End -->



    <div class="text-info-box-one">

        <div class="terms-detail-shape1-two">
                    <img src="assets/images/event-detail-shape-1-2.png" alt="">
                </div>
                <div class="terms-detail-shape2-two">
                    <img src="assets/images/event-detail-shape-1-1.png" alt="">
                </div>
                <div class="terms-detail-shape2-three">
                    <img src="assets/images/event-detail-shape-1-1.png" alt="">
                </div>
                <div class="terms-detail-shape2-three-1">
                    <img src="assets/images/event-detail-shape-1-1.png" alt="">
                </div>
            <div class="container"> 
                <div class="about-title-one" style="margin-top:80px;">
                    <h2 class="h2-title">Terms & Conditions</h2>
                </div>
             <p>   
                {!! $data->desc !!}
             </p>
            </div>
        </div>











    @include('student.includes.scripts')

 






</body>

</html>

