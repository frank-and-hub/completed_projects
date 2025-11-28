@extends('student.layout.app')

@section('title', 'Home - Scholarsbox')

@section('content')
    <div id="carouselExampleIndicators" class="carousel slide mb-50 infinite" data-bs-ride="carousel" data-bs-interval="2500">
        `<div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
    @foreach ($banners as $key => $value)
        <div class="carousel-item{{ $key == 0 ? ' active' : '' }}">
            <a href="{{ $value->title }}">
                <img src="{{ asset('uploads/' . $value->image) }}" class="d-block w-100" alt="...">
            </a>
        </div>
    @endforeach
</div>

           
            {{-- <div class="carousel-item">
                <img src="https://stage.webshark.in/csr-box/assets/images/Wings%20to%20your%20dreams.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
                <img src="https://stage.webshark.in/csr-box/assets/images/Wings%20to%20your%20dreams.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
                <img src="https://stage.webshark.in/csr-box/assets/images/Wings%20to%20your%20dreams.jpg" class="d-block w-100" alt="...">
            </div> --}}
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>`
    </div>




    <!-- slider end -->


    <!--3 Easy Step Start-->
    <section class="main-history-in-one wow fadeInUp z-indx-1" data-wow-delay="0.4s">
        <div class="history-shape-one">
            <img src="{{asset('images/history-shape-one.png')}}" alt="Shape">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="history-title-one">
                        <!-- <div class="subtitle">
                            <div class="subtitle-circle-one"></div>
                            <h2 class="h2-subtitle-one">Quick Steps</h2>
                            <div class="subtitle-circle2-one"></div>
                        </div> -->
                        <h2 class="h2-title wow fadeInDown" data-wow-delay="0.4s">Apply for Your Scholarship in 3 Easy Steps
                        </h2>
                        <h6 class="h6-title wow fadeInDown" data-wow-delay="0.4s">Embarking on your journey towards academic excellence and career advancement has never been easier with ScholarsBox here to support you every step of the way. Through these three straightforward steps, you can swiftly apply for the scholarship of your dreams.</h6>
                    </div>
                </div>
            </div>
            <div class="row working-process-area">
                <div class="col-lg-4 col-sm-6 col-md-4">
                    <div class="single-work-process wow fadeInLeft" data-wow-delay="0.4s">
                        <a href="#"><div class="icon">
                            <i class="fa fa-graduation-cap"></i>
                        </div></a>
                        <h3>SIGN UP</h3>
                        <p>Start by signing up on our scholarship portal to access a personalized list of scholarships just
                            for you.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-md-4">
                    <div class="single-work-process wow fadeInDown" data-wow-delay="0.4s">
                        <div class="icon">
                            <i class="fa fa-binoculars"></i>
                        </div>
                        <h3>EXPLORE OPPORTUNITIES</h3>
                        <p>Find the ideal scholarship that matches your aspirations and gets you closer to your goals.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 col-md-4">
                    <div class="single-work-process wow fadeInRight" data-wow-delay="0.4s">
                        <div class="icon">
                            <i class="fa fa-search"></i>
                        </div>
                        <h3>APPLY WITH EASE</h3>
                        <p>Congratulations! You've found a scholarship. Take the next step and apply now to advance your
                            career with ScholarsBox!</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="history-title-one-below">
                        <h6 class="h6-title wow fadeInDown" data-wow-delay="0.4s">By following these steps, you're on the
                            fast track to securing the scholarship you've always envisioned. ScholarsBox is committed to
                            helping you succeed. Start your scholarship journey today!</h6>
                    </div>
                </div>
            </div>


        </div>
    </section>
    <!--3 Easy Step End-->


    <!--Start Partner with us-->
   <section class="partner-area tp-large-box partner-bg-two p-relative fix wow fadeInUp"
    style="background-image: url('https://stage.webshark.in/csr-box/assets/images/services-bg.png');"
    data-wow-delay="0.4s">
        <div class="partner-shape d-none d-xl-block">
            <div class="partner-shape-one">
                <img src="{{asset('images/partner-2shape-1.png')}}" alt="">
            </div>
            <div class="partner-shape-two">
                <img src="{{asset('assets/images/partner-2shape-2.png')}}" alt="">
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="history-title-one">

                        <h2 class="h2-title wow fadeInUp" data-wow-delay="0.4s">Partner With Us</h2>

                    </div>
                </div>
            </div>
            <div class="row mt-3">
                @foreach ($data as $key=>$value)
                <div id="homePartnerClick-{{$key}}" class="col-lg-6" style="cursor:pointer" data-url="{{route('get-involved')}}#{{$value->link}}">
                    <div class="partner-two mb-60 wow fadeInLeft" data-wow-delay="0.4s">
                        <div class="partner-two-bg"></div>
                        <div class="partner-two-icon">
                            <img src="{{asset('uploads/'.$value->image)}}" alt="" style="height: 120px;">
                        </div>
                        <div class="partner-two-content">
                            <h4 class="partner-two-title" >
                                <a
                                    href="#">{{$value->title}}</a></h4>
                                    <p style="font-size: 15px;
                                    line-height: 24px;
                                    color: #777777;
                                    font-weight: normal;
                                    margin-bottom: 12px;">
                              {!! $value->description !!}
                          </p>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('homePartnerClick-{{$key}}').addEventListener('click', function() {
                        var url = this.getAttribute('data-url');
                        window.location.href = url;
                    });
                </script>
                @endforeach
               
                
                <div class="col-lg-6">
                    <div class="history-title-one-side wow fadeInRight" data-wow-delay="0.4s">
                        <div class="home-partner-inner-div-1">
                            <p>Join ScholarsBox</p>
                        </div>
                        <div class="home-partner-inner-div-2">
                            <p>Partner with us today and make a difference in the world of scholarships and education</p>
                        </div>
                        <div class="col-lg-12 text-center">
                            <button type="button" class="btn sec-btn-one mt-3 sec-btn-one-two" data-bs-toggle="modal"
                                data-bs-target="#getInvolvedModal">
                                <!-- <i class="fa fa-arrow-right"></i> -->
                                Join Now </button>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>




    <!--Testimonial Start-->
    <section class="main-testimonial-one display-temp-none">
        <div class="testimonial-shape-one">
            <img src="{{asset('images/testimonial-shape-one.png')}}" alt="Shape">
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <div class="testimonial-content-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="testimonial-title-one">
                            <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Testimonial</h2>
                            </div>
                            <h2 class="h2-title">What Our Client Say</h2>
                        </div>
                        <p>In tincidunt eleifend libero, tempor dignissim ex placerat in. Nam ultricies posuere sodales.
                            Cras lacus odio, elementum vel viverra non.</p>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="testimonial-main-box-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="row testimonial-slider-one">
                            <div class="col-lg-6">
                                <div class="testimonial-box-one">
                                    <div class="testimonial-quote-one">
                                        <img src="{{asset('images/quote-one.png')}}" alt="Quote">
                                    </div>
                                    <div class="testimonial-client-box-one">
                                        <div class="testimonial-client-img-one">
                                            <img src="{{asset('images/member1-one.jpg')}}" class="rounded-circle"
                                                alt="Client">
                                        </div>
                                        <div class="testimonial-client-name-one">
                                            <h3 class="h3-title">Person Name</h3>
                                            <span>Designation</span>
                                        </div>
                                    </div>
                                    <p>&ldquo;Phasellus aliquam quis lorem amet dapibus feugiat vitae purus vitae
                                        efficitur. Vestibulum sed elit id orci rhoncus ultricies. Morbi vitae semper
                                        dapibus vitae purus eget quam&rdquo;.</p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="testimonial-box-one">
                                    <div class="testimonial-quote-one">
                                        <img src="{{asset('images/quote-one.png')}}" alt="Quote">
                                    </div>
                                    <div class="testimonial-client-box-one">
                                        <div class="testimonial-client-img-one">
                                            <img src="{{asset('images/member1-one.jpg')}}" class="rounded-circle"
                                                alt="Client">
                                        </div>
                                        <div class="testimonial-client-name-one">
                                            <h3 class="h3-title">Person Name</h3>
                                            <span>Designation</span>
                                        </div>
                                    </div>
                                    <p>&ldquo;Phasellus aliquam quis lorem amet dapibus feugiat vitae purus vitae
                                        efficitur. Vestibulum sed elit id orci rhoncus ultricies. Morbi vitae semper
                                        dapibus vitae purus eget quam&rdquo;.</p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="testimonial-box-one">
                                    <div class="testimonial-quote-one">
                                        <img src="{{asset('images/quote-one.png')}}" alt="Quote">
                                    </div>
                                    <div class="testimonial-client-box-one">
                                        <div class="testimonial-client-img-one">
                                            <img src="{{asset('images/member1-one.jpg')}}" class="rounded-circle"
                                                alt="Client">
                                        </div>
                                        <div class="testimonial-client-name-one">
                                            <h3 class="h3-title">Person Name</h3>
                                            <span>Designation</span>
                                        </div>
                                    </div>
                                    <p>&ldquo;Phasellus aliquam quis lorem amet dapibus feugiat vitae purus vitae
                                        efficitur. Vestibulum sed elit id orci rhoncus ultricies. Morbi vitae semper
                                        dapibus vitae purus eget quam&rdquo;.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Testimonial End-->

    <!--Counter Start-->
    <section class="main-counter-two display-temp-none">
        <div class="container">
            <div class="row counter-bg-two wow fadeInUp" data-wow-delay=".4s" id="counter">
                <div class="counter-bgoverlay-two">
                    <img src="{{asset('images/counter-bgover-two.png')}}" alt="Overlay">
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="counter-box-two">
                        <h2 class="h2-title counting-data" data-count="2769">0</h2>
                        <p>Students Registered on the Portal</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="counter-box-two">
                        <h2 class="h2-title counting-data" data-count="637">0</h2>
                        <p>Scholarships Available</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="counter-box-two">
                        <h2 class="h2-title counting-data" data-count="942">0</h2>
                        <p>Scholarships Disbursed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--Counter End-->


    <!--Blog Box Start-->
    <section class="main-blog-grid-in-one wow fadeInUp z-indx-1" data-wow-delay="0.4s">
        <div class="blog-grid-shape1-one">
            <img src="{{asset('images/blog-grid-shape1-one.png')}}" alt="Shape">
        </div>
        <div class="blog-grid-shape2-one">
            <img src="{{asset('images/blog-grid-shape2-one.png')}}" alt="Shape">
        </div>
        <div class="blog-grid-shape3-one">
            <img src="{{asset('images/blog-grid-shape1-one.png')}}" alt="Shape">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="blog-title-one">

                        <h2 class="h2-title wow fadeInUp" data-wow-delay="0.4s">Blogs</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                

                @foreach ($blogs->take(3) as $key => $value)
                    <div class="col-lg-4 col-md-6 {{ $key == 2 ? 'home-blogs-hide-on-tab' : '' }}">
                        <div class="blog-box-one wow fadeInUp" data-wow-delay=".4s">
                            <div class="blog-img-one">
                                <img src="{{ asset('uploads/'.$value->image) }}" alt="Blog" style="height: 250px;">
                            </div>
                            <div class="blog-box-content-one">
                                <div class="blog-date-author-one">
                                    <div class="blog-date-one">
                                        <div class="blog-circle-one"></div>
                                        <a href="javascript:void(0);">{{ date('d M Y', strtotime($value->created_at)) }}</a>
                                    </div>
                                    <div class="blog-author-one">
                                        <div class="blog-circle-one"></div>
                                        <a href="javascript:void(0);">ScholarsBox</a>
                                    </div>
                                </div>
                                <a href="{{ route('Student.blog-details', $value->slug) }}">
                                    <h3 class="h3-title">{{ $value->blog_title }}</h3>
                                </a>
                                <a href="{{ route('Student.blog-details', $value->slug) }}" class="btn-link-one">Read More</a>
                            </div>
                        </div>
                    </div>
                @endforeach

                
                {{-- <div class="col-lg-4 col-md-6">
                    <div class="blog-box-one wow fadeInUp" data-wow-delay=".4s">
                        <div class="blog-img-one">
                            <img src="assets/images/educator-img12.jpg" alt="Blog">
                            <div class="blog-tag-one">
                                <a href="javascript:void(0);">Exam</a>
                            </div>
                        </div>
                        <div class="blog-box-content-one">
                            <div class="blog-date-author-one">
                                <div class="blog-date-one">
                                    <div class="blog-circle-one"></div>
                                    <a href="javascript:void(0);">7 March 2021</a>
                                </div>
                                <div class="blog-author-one">
                                    <div class="blog-circle-one"></div>
                                    <a href="javascript:void(0);">By Scholarbox</a>
                                </div>
                            </div>
                            <a href="blog-details.php">
                                <h3 class="h3-title">Blog Title</h3>
                            </a>
                            <a href="blog-details.php" class="btn-link-one">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="blog-box-one wow fadeInUp" data-wow-delay=".4s">
                        <div class="blog-img-one">
                            <img src="assets/images/educator-img12.jpg" alt="Blog">
                            <div class="blog-tag-one">
                                <a href="javascript:void(0);">Exam</a>
                            </div>
                        </div>
                        <div class="blog-box-content-one">
                            <div class="blog-date-author-one">
                                <div class="blog-date-one">
                                    <div class="blog-circle-one"></div>
                                    <a href="javascript:void(0);">7 March 2021</a>
                                </div>
                                <div class="blog-author-one">
                                    <div class="blog-circle-one"></div>
                                    <a href="javascript:void(0);">By Scholarbox</a>
                                </div>
                            </div>
                            <a href="blog-details.php">
                                <h3 class="h3-title">Blog Title</h3>
                            </a>
                            <a href="blog-details.php" class="btn-link-one">Read More</a>
                        </div>
                    </div>
                </div> --}}
            </div>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{route('Student.blog.list')}}"><button type="button" class="btn sec-btn-one mt-3"><i class="fa fa-arrow-right"></i> Read
                        More</button></a>
                </div>
            </div>
        </div>
    </section>

@endsection
