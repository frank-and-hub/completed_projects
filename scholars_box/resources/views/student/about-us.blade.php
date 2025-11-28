@extends('student.layout.app')
@section('title', 'Scholarship - About us')
@section('content')
    <section class="get-inv-banner">
        <div class="get-inv-ban-img">
            <img src="{{asset('images/About-Us-Banner-new.jpg')}}" alt="">
        </div>
    </section>
    <!--Banner End-->

    <!--About Us Start-->
    <section class="main-about-us-in-one z-indx-1">
        <div class="about-shape-one">
            <img src="{{asset('images/about-shape-one.png')}}" alt="shape">
        </div>
        <div class="container z-indx-1">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="about-img1-one">
                            <img src="{{asset($about->session_image)}}" alt="about us">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="about-title-one">
                            <h2 class="h2-title">{{$about->session_title}}</h2>
                        </div>
                        <p>{!! $about->session_description !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--About Us End-->


    <!--How does it work? Start-->
    <section class="main-history-in-one">
        <div class="history-shape-one">
            <img src="{{asset('images/history-shape-one.png')}}" alt="Shape">
        </div>
        <div class="container z-indx-1">
            <div class="row">
                <div class="col-lg-12">
                    <div class="history-title-one">
                        <h2 class="h2-title">How does it work?</h2>
                    </div>
                </div>
            </div>
            <div class="row history-line-position mt-5">
                <div class="history-line-one"></div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="history-box-one wow fadeInUp" data-wow-delay=".4s">
                        <h3 class="h3-title history-year">Step 1</h3>
                        <div class="history-circle-one"></div>
                        <h3 class="h3-title history-title">{{$about->title_1}}</h3>
                        <p>{!!$about->description_1!!}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="history-box-one wow fadeInDown" data-wow-delay=".5s">
                        <h3 class="h3-title history-year">Step 2</h3>
                        <div class="history-circle-one"></div>
                        <h3 class="h3-title history-title">{{$about->title_2}}</h3>
                        <p>{!!$about->description_2!!}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="history-box-one wow fadeInUp" data-wow-delay=".6s">
                        <h3 class="h3-title history-year">Step 3</h3>
                        <div class="history-circle-one"></div>
                        <h3 class="h3-title history-title">{{$about->title_3}}</h3>
                        <p>{!!$about->description_3!!}</p>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <section class="about section-padding style-4">
        <div class="content frs-content">
            <div class="container z-indx-1">
                <div class="row align-items-center justify-content-between">
                    <div class="col-lg-6">
                        <div class="img mb-30 mb-lg-0">
                            <img src="{{asset($about->session_image_second)}}" alt="">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-content-one wow fadeInRight" data-wow-delay=".4s">
                            <div class="about-title-one">
                                <h2 class="h2-title">{{$about->session_title_second}}</h2>
                            </div>
                            <p>{!! $about->session_description_second !!}</p>
                        </div>
                    </div>
                </div>
            </div>
            <img src="{{asset('images/about_s4_lines.png')}}" alt="" class="lines">
            <img src="{{asset('images/about_s4_bubble.png')}}" alt="" class="bubble">
        </div>
    </section>
@endsection
