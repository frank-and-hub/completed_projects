@extends('student.layout.app')

@section('title', 'Scholarship - Newsletter')
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
                        <h1 class="h1-title">Newsletter</h1>
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
                                    <a href="">Newsletter</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!--Banner End-->

    <section class="rs-inner-newsletter">
        <div class="container">
            <div class="row">
                <div class="col-md-6 wow fadeInLeft">
                    <div class="newsletter-item mb-50">
                        <div class="newsletter-img">
                            <a href="{{route('Student.newsletter-detail')}}"><img src="{{asset('images/newsletter.jpg')}}" alt=""></a>
                            <div class="newsletter-meta">
                                <ul class="btm-cate">
                                    <li>
                                        <div class="user-svg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg> By Username
                                        </div>
                                    </li>
                                    <li>
                                        <div class="newsletter-date">
                                            <i class="fa fa-calendar"></i> October 10, 2022
                                        </div>
                                    </li>
                                    <li>
                                        <div class="tag-line">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-file">
                                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                </path>
                                                <polyline points="13 2 13 9 20 9"></polyline>
                                            </svg>
                                            <a href="#">Category</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="newsletter-content">
                            <h3 class="newsletter-title"><a href="{{route('Student.newsletter-detail')}}">News Headline</a>
                            </h3>
                            <div class="newsletter-desc">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text ever since the 1500s,
                            </div>
                            <div class="newsletter-button inner-btn">
                                <a class="newsletter-btn" href="{{route('Student.newsletter-detail')}}">Continue Reading
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 wow fadeInLeft">
                    <div class="newsletter-item mb-50">
                        <div class="newsletter-img">
                            <a href="{{route('Student.newsletter-detail')}}"><img src="{{asset('images/newsletter.jpg')}}" alt=""></a>
                            <div class="newsletter-meta">
                                <ul class="btm-cate">
                                    <li>
                                        <div class="user-svg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg> By Username
                                        </div>
                                    </li>
                                    <li>
                                        <div class="newsletter-date">
                                            <i class="fa fa-calendar"></i> October 10, 2022
                                        </div>
                                    </li>
                                    <li>
                                        <div class="tag-line">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-file">
                                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                </path>
                                                <polyline points="13 2 13 9 20 9"></polyline>
                                            </svg>
                                            <a href="#">Category</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="newsletter-content">
                            <h3 class="newsletter-title"><a href="{{route('Student.newsletter-detail')}}">News Headline</a>
                            </h3>
                            <div class="newsletter-desc">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text ever since the 1500s,
                            </div>
                            <div class="newsletter-button inner-btn">
                                <a class="newsletter-btn" href="{{route('Student.newsletter-detail')}}">Continue Reading
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 wow fadeInLeft">
                    <div class="newsletter-item mb-50">
                        <div class="newsletter-img">
                            <a href=""><img src="{{asset('images/newsletter.jpg')}}" alt=""></a>
                            <div class="newsletter-meta">
                                <ul class="btm-cate">
                                    <li>
                                        <div class="user-svg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg> By Username
                                        </div>
                                    </li>
                                    <li>
                                        <div class="newsletter-date">
                                            <i class="fa fa-calendar"></i> October 10, 2022
                                        </div>
                                    </li>
                                    <li>
                                        <div class="tag-line">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-file">
                                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                </path>
                                                <polyline points="13 2 13 9 20 9"></polyline>
                                            </svg>
                                            <a href="#">Category</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="newsletter-content">
                            <h3 class="newsletter-title"><a href="{{route('Student.newsletter-detail')}}">News Headline</a>
                            </h3>
                            <div class="newsletter-desc">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text ever since the 1500s,
                            </div>
                            <div class="newsletter-button inner-btn">
                                <a class="newsletter-btn" href="{{route('Student.newsletter-detail')}}">Continue Reading
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 wow fadeInLeft">
                    <div class="newsletter-item mb-50">
                        <div class="newsletter-img">
                            <a href="{{route('Student.newsletter-detail')}}"><img src="{{asset('images/newsletter.jpg')}}" alt=""></a>
                            <div class="newsletter-meta">
                                <ul class="btm-cate">
                                    <li>
                                        <div class="user-svg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg> By Username
                                        </div>
                                    </li>
                                    <li>
                                        <div class="newsletter-date">
                                            <i class="fa fa-calendar"></i> October 10, 2022
                                        </div>
                                    </li>
                                    <li>
                                        <div class="tag-line">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-file">
                                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                </path>
                                                <polyline points="13 2 13 9 20 9"></polyline>
                                            </svg>
                                            <a href="#">Category</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="newsletter-content">
                            <h3 class="newsletter-title"><a href="{{route('Student.newsletter-detail')}}">News Headline</a>
                            </h3>
                            <div class="newsletter-desc">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text ever since the 1500s,
                            </div>
                            <div class="newsletter-button inner-btn">
                                <a class="newsletter-btn" href="{{route('Student.newsletter-detail')}}">Continue Reading
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 wow fadeInLeft">
                    <div class="newsletter-item mb-50">
                        <div class="newsletter-img">
                            <a href="{{route('Student.newsletter-detail')}}"><img src="{{asset('images/newsletter.jpg')}}" alt=""></a>
                            <div class="newsletter-meta">
                                <ul class="btm-cate">
                                    <li>
                                        <div class="user-svg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg> By Username
                                        </div>
                                    </li>
                                    <li>
                                        <div class="newsletter-date">
                                            <i class="fa fa-calendar"></i> October 10, 2022
                                        </div>
                                    </li>
                                    <li>
                                        <div class="tag-line">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-file">
                                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                </path>
                                                <polyline points="13 2 13 9 20 9"></polyline>
                                            </svg>
                                            <a href="#">Category</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="newsletter-content">
                            <h3 class="newsletter-title"><a href="{{route('Student.newsletter-detail')}}">News Headline</a>
                            </h3>
                            <div class="newsletter-desc">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text ever since the 1500s,
                            </div>
                            <div class="newsletter-button inner-btn">
                                <a class="newsletter-btn" href="{{route('Student.newsletter-detail')}}">Continue Reading
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 wow fadeInLeft">
                    <div class="newsletter-item mb-50">
                        <div class="newsletter-img">
                            <a href="{{route('Student.newsletter-detail')}}"><img src="{{asset('images/newsletter.jpg')}}" alt=""></a>
                            <div class="newsletter-meta">
                                <ul class="btm-cate">
                                    <li>
                                        <div class="user-svg">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg> By Username
                                        </div>
                                    </li>
                                    <li>
                                        <div class="newsletter-date">
                                            <i class="fa fa-calendar"></i> October 10, 2022
                                        </div>
                                    </li>
                                    <li>
                                        <div class="tag-line">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-file">
                                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z">
                                                </path>
                                                <polyline points="13 2 13 9 20 9"></polyline>
                                            </svg>
                                            <a href="#">Category</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="newsletter-content">
                            <h3 class="newsletter-title"><a href="{{route('Student.newsletter-detail')}}">News Headline</a>
                            </h3>
                            <div class="newsletter-desc">
                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum
                                has been the industry's standard dummy text ever since the 1500s,
                            </div>
                            <div class="newsletter-button inner-btn">
                                <a class="newsletter-btn" href="{{route('Student.newsletter-detail')}}">Continue Reading
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="row">
            <div class="col-lg-12">
                <div class="blog-pagination-one">
                    <a href="javascript:void(0);" class="pagination-arrow-one"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
                    <ul>
                        <li class="active">1</li>
                        <li>2</li>
                        <li>3</li>
                        <li>4</li>
                    </ul>
                    <a href="javascript:void(0);" class="pagination-arrow-one"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
        </div>
    </section>


    </div> <!-- END PAGE CONTENT -->
@endsection