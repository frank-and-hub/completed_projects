@extends('student.layout.app')
@section('title', 'Scholarship - Scholarsbox')
@section('content')
<?php
$state = \App\Models\CountryData\State::whereStatus('active')->orderBy('name', 'asc')->get(); 
$district = \App\Models\CountryData\District::whereStatus('active')->get(); 
$tag = \DB::table('tags')->select('slug')->groupBy('slug')->pluck('slug');
$tagName = \DB::table('tags')->pluck('slug','name')->toArray();
?>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<!--Banner Start-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.js"
    integrity="sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ asset('frontend/js/jquery.steps.js') }}"></script>

<!--Banner Start-->
    <!-- <section class="get-inv-banner">
        <div class="get-inv-ban-img">
            <img src="{{asset('images/Scholarship-Section-Banner.jpg')}}" alt="">
        </div>
    </section> -->


<div id="carouselExampleIndicators" class="carousel slide mb-50 infinite" data-bs-ride="carousel" data-bs-interval="2500">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <a href="https://hyundai.scholarsbox.in/#scholarship" #about>
                    <img src="{{asset('images/CLAT Aspirants Banner.jpg')}}" class="d-block w-100" alt="...">
                </a>
            </div>
            <div class="carousel-item">
                <a href="https://hyundai.scholarsbox.in/#scholarship">
                    <img src="{{asset('images/UPSC Aspirants Banner.jpg')}}" class="d-block w-100" alt="...">
                </a>
            </div>
            <div class="carousel-item">
                <a href="https://hyundai.scholarsbox.in/#scholarship">
                    <img src="{{asset('images/IIT Aspirants Banner copy.jpg')}}" class="d-block w-100" alt="...">
                </a>
            </div> 
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="prev" style="height: 10%; top: 40%; width: 5%; left: 2%; background-color: #00000050;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="next" style="height: 10%; top: 40%; width: 5%; right: 2%; background-color: #00000050;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>`
</div>

<!--Banner End-->



    <!-- slider end -->

<section class="marquee-line">
    <marquee attribute_name="attribute_value" ....more attributes>
        {{DB::table('marque')->first()->description}}
    </marquee>
</section>


<!-- all-scholarship -->
<section class="all-scholarship-area mt-10 mb-10">
    <div class="container">
        <div class="row">

        </div>
       
         <div class="row">
                <div class="col-xl-3 col-lg-4 order-2 order-lg-0">
                    <aside class="scholarship__sidebar">
                        <div class="scholar-filter-accordian">
                            <div class="accordion" id="accordionExample">
                                
                                @foreach($tag as $k => $v)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$k}}" aria-expanded="true" aria-controls="collapse_{{$k}}">
                                        {{$v}}
                                    </button>
                                    </h2>
                                    <div id="collapse_{{$k}}" class="accordion-collapse collapse" aria-labelledby="heading_{{$k}}" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="scholar-widget">
                                                <div class="scholar-cat-list">
                                                    <ul class="list-wrap">
                                                        @foreach($tagName as $key => $value)
                                                            @if($value === $v)
                                                                <li>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" value="{{$key}}" name="filter[]" id="filter_{{strtolower(str_replace(' ','_',$key))}}">
                                                                        <label class="form-check-label" for="filter_{{strtolower(str_replace(' ','_',$key))}}">{{$key}}</label>
                                                                    </div>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                                @endforeach
                            </div>
                        </div>
                        <div class="scholar-cstm-education-video" style="padding: 20px 5px;">
                            <div class="scholar-cstm-heading" style="color:#1a91c9; font-size: 20px; font-weight: 600; margin-bottom: 10px; margin-top: 5px; text-transform: uppercase;">How To Apply</div>
                            <video width="100%" style="border-radius: 5px;" controls poster="{{asset('images/v-thumb.jpg')}}">
                                <source src="{{asset('videos/educated-scholarship.mp4')}}" type="video/mp4">
                            </video>
                        </div>
                    </aside>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="shop-top-wrap scholarship-top-wrap">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="shop-top-left">
                                    <p id="scholarshipsCount" ></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="scholar-switching-btns">
                                    <input type="radio" name="type[]" value="column" id="columnRadio"  class="invisible">
                                    <button onclick="showSection('myTabContent')">
                                        <label for="columnRadio"><img src="{{asset('images/column-view-icon.png')}}" alt="" class="switch-btn-img"></label>
                                    </button>
                                    
                                    <input type="radio" name="type[]" value="grid" id="gridRadio"  class="invisible">
                                    <button onclick="showSection('gridViewContent')">
                                        <label for="gridRadio"><img src="{{asset('images/grid-view-icon.png')}}" alt="" class="switch-btn-img"></label>
                                    </button>
                                </div>
                            </div><input id="t" name="t" type="hidden" />
                            <div class="col-md-3">
                                <div class="d-flex justify-content-center justify-content-md-end align-items-center">
                                    <div class="shop-top-right m-0 ms-md-auto">
                                        
                                        <select name="filter[]" id="orderby" class="orderby">
                                            <option value="">Default sorting</option>
                                            <option value="desc">Sort by latest</option>
                                            <option value="asc">Sort by oldest</option>
                                            <option value="a_z">Sort by A-Z</option>
                                            <option value="z_a">Sort by Z-A</option>
                                        </select>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" id="appendAllScholarships">
                    </div>
                </div>
            </div>
        </div>
    </div>
     @include('student.scholarship.calander')
</section>


</div> <!-- END PAGE CONTENT -->



@endsection