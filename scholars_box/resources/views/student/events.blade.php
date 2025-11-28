@extends('student.layout.app')
@section('title', 'Scholarship - Events')
@section('content')
    <!--Banner Start-->

    <section class="main-inner-banner-one">
        <div class="blur-1">
            <img src="{{asset('images/Blur_1.png')}}" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="{{asset('images/Blur_2.png')}}" alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="{{asset('images/banner-inner-shape-one.png')}}" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Events</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="events.php">Events</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!--Events upper -->

    <section class="sec-events-upper">
        <div class="container events-upper">
            <div class="row">
                <div class="col-md-4 events-up-col">
                    <div class="row vertical-center">
                        <div class="col-md-4 ev-up-col-img">
                            <img src="{{asset('images/ev-up-sponsor-icon.png')}}" alt="" class="ev-up-img">
                        </div>
                        <div class="col-md-8 ev-up-col-content">
                            <h5 class="ev-up-heading">100+ Events</h5>
                            <div class="ev-up-des">Loreum Ipsum Loreum Ipsum</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 events-up-col">
                    <div class="row vertical-center">
                        <div class="col-md-4 ev-up-col-img">
                            <img src="{{asset('images/ev-up-location-icon.png')}}" alt="" class="ev-up-img">
                        </div>
                        <div class="col-md-8 ev-up-col-content">
                            <h5 class="ev-up-heading">23+ Locations</h5>
                            <div class="ev-up-des">Loreum Ipsum Loreum Ipsum</div>
                        </div>
                    </div>
                </div>
                
                
                <div class="col-md-4 events-up-col no-border">
                    <div class="row vertical-center">
                        <div class="col-md-4 ev-up-col-img">
                            <img src="{{asset('images/ev-up-speaker-icon.png')}}" alt="" class="ev-up-img">
                        </div>
                        <div class="col-md-8 ev-up-col-content">
                            <h5 class="ev-up-heading">25+ Speakers</h5>
                            <div class="ev-up-des">Loreum Ipsum Loreum Ipsum</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Events Upper ends-->

    <!--Events Main Sections-->

    <section class="sec-events-main">
        <div class="sec-ev-main-one container">
            <div class="row vertical-center">
                <div class="col-md-6">
                    <div class="main-ev-heading">
                        <p>Upcoming Events</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="main-ev-filter">
                        <div class="row vertical-center">
                            <div class="col-md-3"></div>
                            <div class="col-md-3"></div>
                            <div class="col-md-3">
                                <div class="m-ev-filter-button">
                                <select class="form-select" aria-label="Default select example">
                                    <option selected>Category</option>
                                    <option value="1">Upcoming Events</option>
                                    <option value="2">Workshop</option>
                                    <option value="3">Campaigns</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                            <div class="m-ev-filter-button">
                                <select class="form-select" aria-label="Default select example">
                                    <option selected>Event Type</option>
                                    <option value="1">Online</option>
                                    <option value="2">Offline</option>
                                </select>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!-- Main Content -->
            <div class="row vertical-center margin-b50 margin-t50">
                <div class="col-md-4">
                    <div class="event-single-box">
                        <div class="event-single-box-before">
                            <img src="{{asset('images/about_s4_bubble.png')}} alt="">
                        </div>
                        <div class="event-single-box-img">
                            <img src="{{asset('images/events-thumbnail-1.jpg')}}" alt="">
                        </div>
                        <div class="event-single-box-desc">
                            <div class="row vertical-center">
                                <div class="col-md-3">
                                    <div class="event-single-box-desc-date">
                                        <div class="date-number">15</div>
                                        <div class="date-month">Sept</div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <a href="{{route('Student.event-detail')}}"><div class="event-single-box-desc-heading"><h3>Name of the Event</h3></div></a>
                                    <div class="event-single-box-desc-des">Loreum Ipsum loreum ipsum loreum ipsum loreum ipsum loreum ipsum</div>
                                </div>
                            </div>
                        </div>
                        <div class="event-single-box-after">
                            <img src="{{asset('images/shape2-two.png')}} alt="">
                        </div>
                        
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="event-single-box">
                        <div class="event-single-box-before">
                            <img src="{{asset('images/about_s4_bubble.png')}} alt="">
                        </div>
                        <div class="event-single-box-img">
                            <img src="{{asset('images/events-thumbnail-1.jpg')}}" alt="">
                        </div>
                        <div class="event-single-box-desc">
                            <div class="row vertical-center">
                                <div class="col-md-3">
                                    <div class="event-single-box-desc-date">
                                        <div class="date-number">15</div>
                                        <div class="date-month">Sept</div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                <a href="{{route('Student.event-detail')}}"><div class="event-single-box-desc-heading"><h3>Name of the Event</h3></div></a>
                                    <div class="event-single-box-desc-des">Loreum Ipsum loreum ipsum loreum ipsum loreum ipsum loreum ipsum</div>
                                </div>
                            </div>
                        </div>
                        <div class="event-single-box-after">
                            <img src="{{asset('images/shape2-two.png')}} alt="">
                        </div>
                        
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="event-single-box">
                        <div class="event-single-box-before">
                            <img src="{{asset('images/about_s4_bubble.png')}} alt="">
                        </div>
                        <div class="event-single-box-img">
                            <img src="{{asset('images/events-thumbnail-1.jpg')}}" alt="">
                        </div>
                        <div class="event-single-box-desc">
                            <div class="row vertical-center">
                                <div class="col-md-3">
                                    <div class="event-single-box-desc-date">
                                        <div class="date-number">15</div>
                                        <div class="date-month">Sept</div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <a href="{{route('Student.event-detail')}}"><div class="event-single-box-desc-heading"><h3>Name of the Event</h3></div></a>
                                    <div class="event-single-box-desc-des">Loreum Ipsum loreum ipsum loreum ipsum loreum ipsum loreum ipsum</div>
                                </div>
                            </div>
                        </div>
                        <div class="event-single-box-after">
                            <img src="{{asset('images/shape2-two.png')}} alt="">
                        </div>
                        
                    </div>
                </div>
            </div>
             <!--  Main Content Ends-->
            <div class="row vertical-center ">
                <div class="col-lg-12">
                    <div class="blog-pagination-one">
                        <a href="javascript:void(0);" class="pagination-arrow-one"><i class="fa fa-angle-left"
                                aria-hidden="true"></i></a>
                        <ul>
                            <li class="active">1</li>
                            <li>2</li>
                            <li>3</li>
                            <li>4</li>
                        </ul>
                        <a href="javascript:void(0);" class="pagination-arrow-one"><i class="fa fa-angle-right"
                                aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection