@extends('student.layout.app')
@section('title', 'Scholarship - Events Detail')
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
                        <h1 class="h1-title">Events Detail</h1>
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
                                    <a href="">Events</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Events Details</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->



    <!--Blog Detail Start-->
    <section class="main-blog-detail-two">
        
        <div class="event-detail-shape1-two">
            <img src="{{asset('images/event-detail-shape-1-2.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-two">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-three">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-three-1">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-three-2">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-three-3">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-three-4">
            <img src="{{asset('images/event-detail-shape-1-1.png')}}" alt="">
        </div>
        <div class="event-detail-shape2-four">
            <img src="{{asset('images/event-detail-shape-3.png')}}" alt="">
        </div>
        <div class="container">
            <div class="row">
                <!--Blog Detail Info Start-->
                <div class="col-xl-12 col-lg-12">
                    <div class="blog-detail-box-two">
                        <div class="blog-img-two wow fadeInUp" data-wow-delay=".4s">
                            <img src="{{asset('images/event-detail-img-1.jpg')}}" alt="Blog">
                            <div class="event-detail-tag-two">
                                <a href="javascript:void(0);">Tag name</a>
                            </div>
                        </div>
                        <div class="blog-date-author-two">
                            <div class="blog-date-two">
                                <div class="blog-circle-two"></div>
                                <a href="javascript:void(0);">14 August 2021</a>
                            </div>
                            <div class="blog-author-two">
                                <div class="blog-circle-two"></div>
                                <a href="javascript:void(0);">By Author Name</a>
                            </div>
                        </div>
                        <h2 class="h2-title blog-two">Event Name</h2>
                        <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant
                            morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit
                            et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.
                        </p>
                        <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant
                            morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit
                            et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.
                        </p>

                        <p>Aliquam ut semper lacus. Nam mattis, arcu quis viverra rutrum, nulla ligula pulvinar nunc,
                            suscipit lacinia odio velit ac neque. Quisque venenatis tincidunt leo. Pellentesque habitant
                            morbi tristique senectus et netus et malesuada fames ac turpis egestas. Duis condimentum
                            ornare aliquam.</p>
                        <h3 class="h3-title blog-detail-title-one">Curabitur consectetur vulputate nibh, vitae molestie
                            urna ultrices eu</h3>
                        <p>Cras blandit bibendum volutpat. Morbi congue auctor posuere. Fusce vel tincidunt mauris. Duis
                            velit velit, iaculis sed lectus vel, convallis mollis magna.</p>
                        <div class="points-two">
                            <ul>
                                <li>
                                    <div class="point-circle-event-detail"></div>
                                    <p>Nunc faucibus lectus ut felis auctor, nec sagittis leo tempus. Phasellus augue
                                        urna, blandit eu elementum ut, sodales sed est.</p>
                                </li>
                                <li>
                                    <div class="point-circle-event-detail"></div>
                                    <p>Sed fringilla hendrerit mi non porta. Cras pulvinar a turpis varius. Suspendis
                                        varius non lacus quis fringilla sollicitudin vel ex nec, luctus condimentum
                                        nunc.</p>
                                </li>
                                <li>
                                    <div class="point-circle-event-detail"></div>
                                    <p>Curabitur dui nulla, tincidunt varius mattis ut, porta at justo. Integer vel erat
                                        in augue hendrerit pulvinar et mattis lacus ornare quis</p>
                                </li>
                            </ul>
                        </div>
                        <div class="x">
                            <div class="event-detail-single-gallery">
                                <div class="tz-gallery container">

                                    <div class="row mb-10">
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Park">
                                            </a>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Bridge">
                                            </a>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Tunnel">
                                            </a>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Coast">
                                            </a>
                                        </div>
                                        
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="blog-detail-tag-social-two">
                            {{--<div class="blog-side-tag-two">
                                <ul>
                                    <li><a href="javascript:void(0);">Business</a></li>
                                    <li><a href="javascript:void(0);">Corporate</a></li>
                                    <li><a href="javascript:void(0);">Blog</a></li>
                                </ul>
                            </div>--}}
                            <div class="blog-detail-social-media-two">
                                <ul>
                                    <li><a href="javascript:void(0);"><i class="fa fa-facebook"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-instagram"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-twitter"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-pinterest-p"
                                                aria-hidden="true"></i></a></li>
                                </ul>
                            </div>
                            <div class="line"></div>

                            <div class="blog-details-page__author-box custom-pb-75">
                                <div class="blog-details-page__author-box__inner">
                                    <div class="img-box">
                                        <img src="{{asset('images/author.jpg')}}" alt="">
                                    </div>
                                    <div class="text">
                                        <h3>Author Name</h3>
                                        <p>That pleasures have to be repudiated and annoyances accepted. The
                                            wise
                                            man therefore always holds in these matters to this principle of
                                            selection.</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--Event Detail End-->


    <!--Start Event Page One -->
    <section class="article-style1-area">
        <div class="container">
            <div class="row">
                <div class="subtitle">
                    <div class="subtitle-circle-one"></div>
                    <h2 class="h2-subtitle-one">Events</h2>
                </div>
                <h2 class="h2-title">Related Events</h2>
                <!--Start Single event Style1-->
                <div class="col-xl-4 col-lg-4">
                    <div class="single-article-style1">
                        <div class="img-holder">
                            <div class="inner">
                                <img src="{{asset('images/educator-img12.jpg')}}" alt="">
                            </div>
                            <div class="event-detail-category-box">
                                <div class="dot-box"></div>
                                <p>Author Name</p>
                            </div>
                        </div>
                        <div class="text-holder">
                            <h3><a href="">Event Title</a></h3>
                            <div class="text">
                                <p>Duty obligations of business frequently
                                    occur pleasures enjoy...</p>
                            </div>
                            <div class="bottom-box">
                                <div class="btn-box">
                                    <a href="">
                                        <span class="fa fa-arrow-right"></span>Read More
                                    </a>
                                </div>
                                <div class="meta-info">
                                    <ul>
                                        <li><span class="fa fa-calendar"></span><a href="#">Nov 25, 2022</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End Single event Style1-->

                <!--Start Single event Style1-->
                <div class="col-xl-4 col-lg-4">
                    <div class="single-article-style1">
                        
                        <div class="img-holder">
                            <div class="inner">
                                <img src="{{asset('images/educator-img12.jpg')}}" alt="">
                            </div>
                            <div class="event-detail-category-box">
                                <div class="dot-box"></div>
                                <p>Author Name</p>
                            </div>
                        </div>
                        <div class="text-holder">
                            <h3><a href="">Event Title</a></h3>
                            <div class="text">
                                <p>Duty obligations of business frequently
                                    occur pleasures enjoy...</p>
                            </div>
                            <div class="bottom-box">
                                <div class="btn-box">
                                    <a href="">
                                        <span class="fa fa-arrow-right"></span>Read More
                                    </a>
                                </div>
                                <div class="meta-info">
                                    <ul>
                                        <li><span class="fa fa-calendar"></span><a href="#">Nov 25, 2022</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End Single aevent Style1-->

                <!--Start Single event Style1-->
                <div class="col-xl-4 col-lg-4">
                    <div class="single-article-style1">
                        <div class="img-holder">
                            <div class="inner">
                                <img src="{{asset('images/educator-img12.jpg')}}" alt="">
                            </div>
                            <div class="event-detail-category-box">
                                <div class="dot-box"></div>
                                <p>Author Name</p>
                            </div>
                        </div>
                        <div class="text-holder">
                            <h3><a href="">Event Title</a></h3>
                            <div class="text">
                                <p>Duty obligations of business frequently
                                    occur pleasures enjoy...</p>
                            </div>
                            <div class="bottom-box">
                                <div class="btn-box">
                                    <a href="">
                                        <span class="fa fa-arrow-right"></span>Read More
                                    </a>
                                </div>
                                <div class="meta-info">
                                    <ul>
                                        <li><span class="fa fa-calendar"></span><a href="#">Nov 25, 2022</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End Single event Style1-->

            </div>

        </div>
    </section>
    <!--End event Style1 Area-->
    @endsection
