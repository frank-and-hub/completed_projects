@extends('student.layout.app')
@section('title', 'Faqs')

@section('content')
<!-- End Header -->
    <!--Banner Start-->
    <section class="main-inner-banner-one">
        <div class="blur-1">
            <img src="assets/images/Blur_1.png" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="assets/images/Blur_2.png" alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <!-- <img src="assets/images/banner-inner-shape-one.png" alt="shap"> -->
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">FAQs</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="#" class="capitalize-xstm-head">FAQs</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner Ends-->

    <!--FAQ Start-->
    <section class="main-faq-in-one">
        <div class="faq-shape1-one">
            <!-- <img src="assets/images/faq-shap1-one.png" alt="Shape"> -->
        </div>
        <div class="faq-shape2-one">
            <!-- <img src="assets/images/faq-shape2-one.png" alt="Shape"> -->
        </div>
        <div class="container">
            <div class="row">
            
                <div class="col-xl-12 col-lg-12">
                    <div class="faq-content-one">
                        <div class="fnaq-title-oe wow fadeInDown">
                            <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one capitalize-xstm-head">FAQs</h2>
                            </div>
                            <h2 class="h2-title">Popular Questions</h2>
                        </div>

                        @foreach($faqs as $faq)
                        <div class="faq-accordion-one" id="accordionExample{{$faq->id}}">
                            <div class="accordion one">
                                <div class="accordion-item one wow fadeInLeft">
                                    <h3 class="accordion-header h3-title" id="headingOne{{$faq->id}}">
                                        <button class="accordion-button one collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne{{$faq->id}}" aria-expanded="false" aria-controls="collapseOne">
                                        {{$faq->title}}?<span class="icon"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                                        </button>
                                    </h3>
                                    <div id="collapseOne{{$faq->id}}" class="accordion-collapse collapse" aria-labelledby="headingOne{{$faq->id}}" data-bs-parent="#accordionExample{{$faq->id}}">
                                        <div class="accordion-body">
                                            <P>
                                            {{$faq->description}}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection