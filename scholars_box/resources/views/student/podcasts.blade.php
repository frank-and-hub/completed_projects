@extends('student.layout.app')

@section('title', 'Scholarship - Podcases')
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
                    <h1 class="h1-title">Podcasts</h1>
                    <div class="breadcrum-one">
                        <ul>
                            <li>
                                <a href="{{url('/')}}">Home</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right"></i>
                            </li>
                            <li>
                                <a href="">Resources</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right"></i>
                            </li>
                            <li>
                                <a href="">Podcasts</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Banner End-->

<!--Podcast Body Starts-->

    <section class="podcast-main-sec">
        <div class="elec-media-shape1-two">
            <img src="{{asset('images/event-detail-shape-1-2.png')}}" alt="">
        </div>
        <div class="elec-media-shape1-two-2">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="elec-media-shape1-two-3">
            <img src="{{asset('images/circle-shape-3.png')}}" alt="">
        </div>
        <div class="elec-media-shape1-two-4">
            <img src="{{asset('images/circle-shape-3.png')}}" alt="">
        </div>
        <div class="elec-media-shape1-two-5">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="elec-media-shape1-two-6">
            <img src="{{asset('images/event-detail-shape-1-2.png')}}" alt="">
        </div>
        <div class="container pod-inner-cont">
            <div class="row vertical-center">
                <div class="col-md-4">
                    <div class="pod-card">
                        <div class="pod-card-inner-shape-1">
                            <img src="{{asset('images/shape1-two.png')}}" alt="">
                        </div>
                        <div class="pod-card-inner-shape-2">
                            <img src="{{asset('images/shape1-two.png')}}" alt="">
                        </div>
                        <div class="pod-card-inner-specif">
                        <span class="fa fa-video-camera"></span>
                        </div>
                        <div class="pod-card-inner-img">
                            <img src="{{asset('images/podcast-demo-img.jpg')}}" alt="">
                        </div>
                        <div class="pod-card-inner-name">
                            <p>Name of Podcast</p>
                        </div>
                        <div class="pod-card-inner-desc">
                            <p>Mauris vel neque ut leo interdum tincidunt et quis ex. Curabitur pellentesque odio eget nisi eleifend rutrum.</p>
                        </div>
                        <div class="pod-card-inner-btn">
                            <a href="{{route('Student.podcast-detail')}}">View Podcast</a>
                        </div>
                        <div class="pod-card-inner-separator">

                        </div>
                        <div class="pod-card-inner-meta row ">
                            <div class="col-md-6">
                                <div class="pod-card-meta-date">
                                    <div class="meta-info">
                                        <ul>
                                            <li><span class="fa fa-calendar"></span><a href="#">Aug 21, 2022</a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="pod-card-meta-author">
                                    Author
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="pod-card">
                        <div class="pod-card-inner-shape-1">
                            <img src="{{asset('images/shape1-two.png')}}" alt="">
                        </div>
                        <div class="pod-card-inner-shape-2">
                            <img src="{{asset('images/shape1-two.png')}}" alt="">
                        </div>
                        <div class="pod-card-inner-specif">
                        <span class="fa fa-volume-up"></span>
                        </div>
                        <div class="pod-card-inner-img">
                            <img src="{{asset('images/podcast-demo-img.jpg')}}" alt="">
                        </div>
                        <div class="pod-card-inner-name">
                            <p>Name of Podcast</p>
                        </div>
                        <div class="pod-card-inner-desc">
                            <p>Mauris vel neque ut leo interdum tincidunt et quis ex. Curabitur pellentesque odio eget nisi eleifend rutrum.</p>
                        </div>
                        <div class="pod-card-inner-btn">
                            <a href="{{route('Student.podcast-detail')}}">View Podcast</a>
                        </div>
                        <div class="pod-card-inner-separator">

                        </div>
                        <div class="pod-card-inner-meta row ">
                            <div class="col-md-6">
                                <div class="pod-card-meta-date">
                                    <div class="meta-info">
                                        <ul>
                                            <li><span class="fa fa-calendar"></span><a href="#">Aug 21, 2022</a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="pod-card-meta-author">
                                    Author
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="pod-card">
                        <div class="pod-card-inner-shape-1">
                            <img src="{{asset('images/shape1-two.png')}}" alt="">
                        </div>
                        <div class="pod-card-inner-shape-2">
                            <img src="{{asset('images/shape1-two.png')}}" alt="">
                        </div>
                        <div class="pod-card-inner-specif">
                        <span class="fa fa-video-camera"></span>
                        </div>
                        <div class="pod-card-inner-img">
                            <img src="{{asset('images/podcast-demo-img.jpg')}}" alt="">
                        </div>
                        <div class="pod-card-inner-name">
                            <p>Name of Podcast</p>
                        </div>
                        <div class="pod-card-inner-desc">
                            <p>Mauris vel neque ut leo interdum tincidunt et quis ex. Curabitur pellentesque odio eget nisi eleifend rutrum.</p>
                        </div>
                        <div class="pod-card-inner-btn">
                            <a href="{{route('Student.podcast-detail')}}">View Podcast</a>
                        </div>
                        <div class="pod-card-inner-separator">

                        </div>
                        <div class="pod-card-inner-meta row ">
                            <div class="col-md-6">
                                <div class="pod-card-meta-date">
                                    <div class="meta-info">
                                        <ul>
                                            <li><span class="fa fa-calendar"></span><a href="#">Aug 21, 2022</a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                                <div class="pod-card-meta-author">
                                    Author
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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