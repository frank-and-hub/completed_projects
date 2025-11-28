@extends('student.layout.app')
@section('title', 'Scholarship - Article Detail')
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
                        <h1 class="h1-title">Article Detail</h1>
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
                                    <a href="">Article</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Article Details</a>
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
        <div class="blog-detail-shape1-two">
            <img src="{{asset('images/shape-5-two.png')}}" alt="Shape">
        </div>
        <div class="blog-detail-shape2-two">
            <img src="{{asset('images/shape2-two.png')}}" alt="Shape">
        </div>
        <div class="blog-detail-shape3-two">
            <img src="{{asset('images/shape-3-two.png')}}" alt="Shape">
        </div>
        <div class="blog-detail-shape4-two">
            <img src="{{asset('images/shape-6-two.png')}}" alt="Shape">
        </div>
        <div class="blog-detail-shape5-two">
            <img src="{{asset('images/shape-4-two.png')}}" alt="Shape">
        </div>
        <div class="blog-detail-shape6-two">
            <img src="{{asset('images/shape-7-two.png')}}" alt="Shape">
        </div>
        <div class="blog-detail-shape7-two">
            <img src="{{asset('images/shape-3-two.png')}}" alt="Shape">
        </div>
        <div class="container">
            <div class="row">
                <!--Blog Detail Info Start-->
                <div class="col-xl-12 col-lg-12">
                    <div class="blog-detail-box-two">
                        <div class="blog-img-two wow fadeInUp" data-wow-delay=".4s">
                            <img src="{{asset('images/blog-detail-img1-two.jpg')}}" alt="Blog">
                            <div class="blog-tag-two">
                                <a href="javascript:void(0);">Tag name</a>
                            </div>
                        </div>
                        <div class="blog-date-author-two">
                            <div class="blog-date-two">
                                <div class="blog-circle-two"></div>
                                <a href="javascript:void(0);">7 March 2021</a>
                            </div>
                            <div class="blog-author-two">
                                <div class="blog-circle-two"></div>
                                <a href="javascript:void(0);">By Author Name</a>
                            </div>
                        </div>
                        <h2 class="h2-title blog-two">Article Name</h2>
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
                        {{--
                        <h3 class="h3-title blog-detail-title-one">Curabitur consectetur vulputate nibh, vitae molestie
                            urna ultrices eu</h3>
                        <p>Cras blandit bibendum volutpat. Morbi congue auctor posuere. Fusce vel tincidunt mauris. Duis
                            velit velit, iaculis sed lectus vel, convallis mollis magna.</p>
                        <div class="points-two">
                            <ul>
                                <li>
                                    <div class="point-circle"></div>
                                    <p>Nunc faucibus lectus ut felis auctor, nec sagittis leo tempus. Phasellus augue
                                        urna, blandit eu elementum ut, sodales sed est.</p>
                                </li>
                                <li>
                                    <div class="point-circle"></div>
                                    <p>Sed fringilla hendrerit mi non porta. Cras pulvinar a turpis varius. Suspendis
                                        varius non lacus quis fringilla sollicitudin vel ex nec, luctus condimentum
                                        nunc.</p>
                                </li>
                                <li>
                                    <div class="point-circle"></div>
                                    <p>Curabitur dui nulla, tincidunt varius mattis ut, porta at justo. Integer vel erat
                                        in augue hendrerit pulvinar et mattis lacus ornare quis</p>
                                </li>
                            </ul>
                        </div>
                        --}}
                        <div class="blog-detail-tag-social-two">
                            <div class="blog-side-tag-two">
                                <ul>
                                    {{--<li><a href="javascript:void(0);">Business</a></li>
                                    <li><a href="javascript:void(0);">Corporate</a></li>
                                    <li><a href="javascript:void(0);">Blog</a></li>--}}
                                </ul>
                            </div>
                            <div class="blog-detail-social-media-two">
                                <ul>
                                    <li><a href="javascript:void(0);"><i class="fa fa-thumbs-up" aria-hidden="true"></i></a> (2)</li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-pinterest-p" aria-hidden="true"></i></a></li>                                
                                    <li><a href="javascript:void(0);"><i class="fa fa-bookmark" aria-hidden="true"></i></a></li>
                                </ul>
                            </div>
                            <div class="line"></div>

                            <div class="blog-details-page__author-box">
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

                            <div class="blog-detail-comment-one pt-5">
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

                    <div class="blog-detail-leave-comment-one pt-5">
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
                                <form class="leave-reply-form-one" action="{{route('Student.comment-form')}}" method="post">
                                    @csrf
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
                                                <lt-mirror contenteditable="false" style="display: none;" data-lt-linked="1"><lt-highlighter contenteditable="false" style="display: none;"><lt-div spellcheck="false" class="lt-highlighter__wrapper" style="width: 1084px !important; height: 118px !important; transform: none !important; transform-origin: 543px 60px !important; zoom: 1 !important; margin-top: 1px !important; margin-left: 1px !important;"><lt-div class="lt-highlighter__scroll-element" style="top: 0px !important; left: 0px !important; width: 1084px !important; height: 118px !important;"></lt-div></lt-div></lt-highlighter><lt-div spellcheck="false" class="lt-mirror__wrapper notranslate" data-lt-scroll-top="0" data-lt-scroll-left="0" data-lt-scroll-top-scaled="0" data-lt-scroll-left-scaled="0" data-lt-scroll-top-scaled-and-zoomed="0" data-lt-scroll-left-scaled-and-zoomed="0" style="border: 1px solid rgb(119, 119, 119) !important; border-radius: 5px !important; direction: ltr !important; font: 400 15px / 24px Roboto, sans-serif !important; font-synthesis: weight style small-caps !important; hyphens: manual !important; letter-spacing: normal !important; line-break: auto !important; margin: 0px !important; padding: 12px 30px !important; text-align: start !important; text-decoration: none solid rgb(22, 22, 22) !important; text-indent: 0px !important; text-rendering: auto !important; text-transform: none !important; transform: none !important; transform-origin: 543px 60px !important; unicode-bidi: normal !important; white-space: pre-wrap !important; word-spacing: 0px !important; overflow-wrap: break-word !important; writing-mode: horizontal-tb !important; zoom: 1 !important; -webkit-locale: &quot;en&quot; !important; -webkit-rtl-ordering: logical !important; width: 1024px !important; height: 94px !important;"><lt-div class="lt-mirror__canvas" style="margin-top: 0px !important; margin-left: 0px !important; width: 1024px !important; height: 94px !important;"></lt-div></lt-div></lt-mirror><textarea class="form-input-one" placeholder="Message" data-lt-tmp-id="lt-183787" spellcheck="false" data-gramm="false"></textarea>
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
        </div>
    </section>
    <!--Blog Detail End-->


    <!--Start article Page One -->
    <section class="article-style1-area">
        <div class="container">
            <div class="row">
                <div class="subtitle">
                    <div class="subtitle-circle-one"></div>
                    <h2 class="h2-subtitle-one">Articles</h2>
                </div>
                <h2 class="h2-title">Related Articles</h2>
                <!--Start Single article Style1-->
                <div class="col-xl-4 col-lg-4">
                    <div class="single-article-style1">
                        <div class="img-holder">
                            <div class="inner">
                                <img src="{{asset('images/educator-img12.jpg')}}" alt="">
                            </div>
                            <div class="category-box">
                                <div class="dot-box"></div>
                                <p>Author Name</p>
                            </div>
                        </div>
                        <div class="text-holder">
                            <h3><a href="">Article Title</a></h3>
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
                <!--End Single article Style1-->

                <!--Start Single article Style1-->
                <div class="col-xl-4 col-lg-4">
                    <div class="single-article-style1">
                        <div class="img-holder">
                            <div class="inner">
                                <img src="{{asset('images/educator-img12.jpg')}}" alt="">
                            </div>
                            <div class="category-box">
                                <div class="dot-box"></div>
                                <p>Author Name</p>
                            </div>
                        </div>
                        <div class="text-holder">
                            <h3><a href="">Article Title</a></h3>
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
                <!--End Single article Style1-->

                <!--Start Single article Style1-->
                <div class="col-xl-4 col-lg-4">
                    <div class="single-article-style1">
                        <div class="img-holder">
                            <div class="inner">
                                <img src="{{asset('images/educator-img12.jpg')}}" alt="">
                            </div>
                            <div class="category-box">
                                <div class="dot-box"></div>
                                <p>Author Name</p>
                            </div>
                        </div>
                        <div class="text-holder">
                            <h3><a href="">Article Title</a></h3>
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
                <!--End Single article Style1-->

            </div>

        </div>
    </section>
    <!--End article Style1 Area-->
@endsection