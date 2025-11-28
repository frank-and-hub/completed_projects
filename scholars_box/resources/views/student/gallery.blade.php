@extends('student.layout.app')

@section('title', 'Scholarship - Photo Gallery')
@section('content')

    <!--Banner Start-->
    
    <section class="main-inner-banner-one">
        <div class="blur-1">
            <img src="asset('images/Blur_1.png')}}" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="asset('images/Blur_2.png')}}" alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="asset('images/banner-inner-shape-one.png')}}" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Photo Gallery</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="gallery.php">Photo Gallery</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!--Gallery Inner-->
    
    <section class="gallery-inner z-indx-1">

        <div class="gallery-tabs">
            <ul class="nav nav-tabs gallery-tab-ul" id="myTab" role="tablist">
                @foreach(\DB::table('categories')->get()->toArray() as $key => $val )
                    <li class="nav-item " role="presentation">
                        <button class="nav-link active sec-btn-one" id="{{ucwords($val->slug)}}" data-bs-toggle="tab" data-bs-target="#{{($val->name)}}" type="button" role="tab" aria-controls="{{($val->slug)}}" aria-selected="true">{{ucwords($val->slug)}}</button>
                    </li>
                @endforeach
                {{--
                <li class="nav-item " role="presentation">
                    <button class="nav-link active sec-btn-one" id="btnall" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="home" aria-selected="true">All</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link sec-btn-one" id="btncat01" data-bs-toggle="tab" data-bs-target="#cat01" type="button" role="tab" aria-controls="profile" aria-selected="false">Category 1</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link sec-btn-one" id="btncat02" data-bs-toggle="tab" data-bs-target="#cat02" type="button" role="tab" aria-controls="contact" aria-selected="false">Category 2</button>
                </li>
                --}}
            </ul>

            <div class="tab-content" id="myTabContent">
                {{--
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <div class="tz-gallery container">

                        <div class="row mb-10">
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Park">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Bridge">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Tunnel">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Coast">
                                </a>
                            </div>
                            
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Park">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Bridge">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Tunnel">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Coast">
                                </a>
                            </div>
                            
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="cat01" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="tz-gallery container">

                        <div class="row mb-10">
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Park">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Bridge">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Tunnel">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Coast">
                                </a>
                            </div>
                            
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Park">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Bridge">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Tunnel">
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <a class="lightbox" href="https://www.w3schools.com/howto/img_nature.jpg">
                                    <img src="https://www.w3schools.com/howto/img_nature.jpg" alt="Coast">
                                </a>
                            </div>
                            
                        </div>

                    </div>
                </div>
                --}}
                @foreach(\App\Models\Category::with('gallery')->get() as $key => $val )
                <div class="tab-pane fade show active" id="{{($val->name)}}" role="tabpanel" aria-labelledby="{{($val->slug)}}-tab">
                    <div class="tz-gallery container">

                        <div class="row">
                            @foreach($val->gallery as $k => $v)
                            <div class="col-3 m-4">
                                <a class="lightbox" href="{{asset($v->image)}}">
                                    <img src="{{asset($v->image)}}" alt="Park">
                                </a>
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
            
        </div>
        <img src="{{asset('images/about_s4_bubble.png')}}" alt="" class="bubble">
        <img src="{{asset('images/banner-gall-shap2.png')}}" alt="" class="gall-bg-2">
        <img src="{{asset('images/about_s4_lines.png')}}" alt="" class="lines">

                
    </section>
    
@endsection
