@extends('student.layout.app')


@section('title', 'Scholarship - Newsletter Detail')
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
                        <h1 class="h1-title">Newsletter Detail</h1>
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
                                    <a href="">Newsletter Detail</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!--Banner End-->

    <section class="mt-5 mb-5">
        <div class="container">
            <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="blog-detail-box-one">
                    <h2 class="h2-title blog-one">Lorem Ipsum</h2>
                    <div class="blog-date-author-one">
                        <div class="blog-date-one">
                            <div class="blog-circle-one"></div>
                            <a href="javascript:void(0);">7 March 2021</a>
                        </div>
                        <div class="blog-author-one">
                            <div class="blog-circle-one"></div>
                            <a href="javascript:void(0);">By Developers</a>
                        </div>
                        <div class="blog-comment-one">
                            <div class="blog-circle-one"></div>
                            <a href="javascript:void(0);">7 Comments</a>
                        </div>
                    </div>
                    <div class="blog-img-one wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                        <img src="{{asset('images/blog-detail-img1-two.jpg')}}" alt="Blog">
                        <div class="blog-tag-one">
                            <a href="javascript:void(0);">Advertising</a>
                        </div>
                    </div>
                    <p>Nulla tempus orci sed diam tempor efficitur. Aenean rhoncus nulla tellus, ac ultricies velit dictum non. Quisque ac fermentum mauris. Suspendisse quis pulvinar tortor. Cras consequat ligula sit amet turpis consequat, facilisis laoreet dolor semper. Sed tellus metus, volutpat eget gravida sit amet, accumsan ac ante. Praesent vel leo massa. Ut ut ultrices magna.</p>
                    <p>Aliquam imperdiet purus et odio pellentesque accumsan. Sed auctor libero id risus interdum rhoncus. Curabitur sagittis purus id enim tempor euismod. Morbi semper sed libero sed venenatis. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                    <h3 class="h3-title blog-detail-title-one">Know Your Digital Goals</h3>
                    <p>Maecenas vitae pellentesque felis, ut feugiat eros. Nulla facilisi. Integer congue mollis varius. Sed ut leo malesuada, efficitur sapien vel, posuere erat. Interdum et malesuada fames ac ante ipsum primis in faucibus. Ut sed massa vel magna efficitur posuere. Morbi at justo est. Curabitur a eros eros.</p>
                    
                    <h3 class="h3-title">Get Specific Analytics</h3>
                    <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Maecenas feugiat odio diam, quis suscipit libero fringilla vel. Vivamus vel vulputate leo. Praesent sem eros, sollicitudin id dapibus a, pulvinar non ex.</p>
                    <div class="points-one">
                        <ul>
                            <li>
                                <div class="check-list">
                                    <img src="{{asset('images/check.png')}}" alt="check">
                                </div>
                                <p>Quisque condimentum fringilla justo eu maximus. Pellentesque pharetra convallis tortor et finibus.</p>
                            </li>
                            <li>
                                <div class="check-list">
                                    <img src="{{asset('images/check.png')}}" alt="check">
                                </div>
                                <p>Quisque condimentum fringilla justo eu maximus. Pellentesque pharetra convallis tortor et finibus.</p>
                            </li>
                            <li>
                                <div class="check-list">
                                    <img src="{{asset('images/check.png')}}" alt="check">
                                </div>
                                <p>Quisque condimentum fringilla justo eu maximus. Pellentesque pharetra convallis tortor et finibus.</p>
                            </li>
                        </ul>
                    </div>
                    <h3 class="h3-title mt-3">Always Remember Your Goals!</h3>
                    <p>Proin tempor erat magna, in vehicula lectus eleifend in. Praesent pharetra nisi malesuada aliquet pellentesque. Proin scelerisque tincidunt nisi, sed pretium diam vulputate ut. Integer a accumsan tortor. Sed nec ligula sodales nibh gravida maximus. Quisque aliquam quam ut justo placerat vulputate.</p>
                    
                    <p>Fusce eu fermentum leo, et bibendum est. Curabitur finibus nisl non mollis tincidunt. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>
                    <div class="blog-detail-tag-social-one">
                        <div class="blog-side-tag-one">
                        {{--
                            <ul>
                                <li><a href="javascript:void(0);">Business</a></li>
                                <li><a href="javascript:void(0);">Corporate</a></li>
                                <li><a href="javascript:void(0);">Blog</a></li>
                            </ul>
                        --}}
                        </div>
                        <div class="blog-detail-social-media-one">
                            <ul>
                                <li><a href="javascript:void(0);"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                <li><a href="javascript:void(0);"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                <li><a href="javascript:void(0);"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                <li><a href="javascript:void(0);"><i class="fa fa-pinterest-p" aria-hidden="true"></i></a></li>
                            </ul>
                        </div>
                        <div class="line"></div>
                    </div>
                    <!--About Me Start-->
                    <div class="blog-about-me-one wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                        <div class="blog-about-me-img-one">
                            <img src="{{asset('images/author.jpg')}}" alt="Member">
                        </div>
                        <div class="blog-about-me-content">
                            <h3 class="h3-title">About Johen Flores</h3>
                            <p>Aliquam imperdiet purus et odio pellentesque accumsan. Sed auctor libero id risus interdum rhoncus.</p>
                            <div class="blog-about-me-social-one">
                                <span>In Socials:</span>
                                <div class="blog-detail-social-media-one">
                                    <ul>
                                        <li><a href="javascript:void(0);"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                        <li><a href="javascript:void(0);"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                        <li><a href="javascript:void(0);"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                        <li><a href="javascript:void(0);"><i class="fa fa-pinterest-p" aria-hidden="true"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!--Comments Start-->
                    <div class="blog-detail-comment-one">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="blog-detail-comment-title-one">
                                    <div class="subtitle">
                                        <div class="subtitle-circle-one"></div>
                                        <h2 class="h2-subtitle-one">read Comments</h2>
                                    </div>
                                    <h2 class="h2-title">Comments (02)</h2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="blog-detail-comment-box-one wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                                    <div class="blog-detail-comment-img-one">
                                        <img src="{{asset('images/author.jpg')}}" alt="Comment">
                                    </div>
                                    <div class="blog-detail-comment-content-one">
                                        <div class="blog-detail-comment-name-reply">
                                            <div class="blog-detail-comment-name-one">
                                                <h3 class="h3-title">David Parker</h3>
                                                <span>7 March, 2021</span>
                                            </div>
                                            <a href="javascript:void(0);" class="sec-btn-one">Reply</a>
                                        </div>
                                        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Maecenas feugiat odio diam, quis suscipit libero fringilla vel. Vivamus vel vulputate leo. </p>
                                    </div>
                                </div>
                            </div>
                            <div class="line"></div>
                            <div class="col-lg-12">
                                <div class="blog-detail-comment-box-one wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                                    <div class="blog-detail-comment-img-one">
                                        <img src="{{asset('images/author.jpg')}}" alt="Comment">
                                    </div>
                                    <div class="blog-detail-comment-content-one">
                                        <div class="blog-detail-comment-name-reply">
                                            <div class="blog-detail-comment-name-one">
                                                <h3 class="h3-title">Harry Olson</h3>
                                                <span>7 March, 2021</span>
                                            </div>
                                            <a href="javascript:void(0);" class="sec-btn-one">Reply</a>
                                        </div>
                                        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Maecenas feugiat odio diam, quis suscipit libero fringilla vel. Vivamus vel vulputate leo. </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Leave Comment Start-->
                    <div class="blog-detail-leave-comment-one">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="blog-detail-leave-comment-title-one">
                                    <div class="subtitle">
                                        <div class="subtitle-circle-one"></div>
                                        <h2 class="h2-subtitle-one">Write Comments</h2>
                                    </div>
                                    <h2 class="h2-title">Leave A Comment</h2>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                                <form class="leave-reply-form-one">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-6">
                                            <div class="form-box-one">
                                                <input type="text" class="form-input-one" name="firstname" placeholder="First Name" required="">
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-6">
                                            <div class="form-box-one">
                                                <input type="text" class="form-input-one" name="lastname" placeholder="Last Name" required="">
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-12">
                                            <div class="form-box-one">
                                                <input type="email" class="form-input-one" name="email" placeholder="Email Address" required="">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-box-one">
                                                <textarea class="form-input-one" placeholder="Message"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-box-one mb-0">
                                                <button type="submit" class="sec-btn-one"><span>Post Now</span></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>

    </section>
@endsection