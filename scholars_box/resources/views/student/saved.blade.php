@extends('student.layout.app')
@section('title', 'Scholarship - Saved Scholarship')
@section('content')
<section class="main-inner-banner-one innerpage-banner">
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
        <img src="{{ asset('images/banner-inner-shape-one.png') }}" alt="shap">
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrum-title-one wow fadeInDown">
                    <h1 class="h1-title">Saved Scholarship</h1>
                    <div class="breadcrum-one">
                        <ul>
                            <li>
                                <a href="{{url('/')}}">Home</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right"></i>
                            </li>
                            <li>
                                <a href="">Saved Scholarship</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Banner End-->

<!-- all-scholarship -->
<section class="mt-10 mb-10 stu-dashboard">
<div class="team-shape-one">
    <img src="assets/images/service-shape-one.png" alt="Shape">
</div>
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-4 order-1 order-lg-0">
                <div class="profile-sidebar">
                    <div class="widget-profile pro-widget-content">
                        @include('student.profile_img')
                    </div>
                    <div class="dashboard-widget">
                        <nav class="dashboard-menu">
                            @include('student.sidebar')
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8 order-2">
                <div class="card profile-main">
                    <div class="card-body">

                  
                        <div class="row scholarship__list-wrap row-cols-1">
                            <h3 class="subheading">Saved Scholarship ({{$scholarships->count('id');}})</h3>
                            @foreach ($scholarships as $scholarship)
                           
                            <div class="col">
                                <div class="scholarship__item-two shine__animate-item">
                                    <div class="scholarship__item-two-thumb">
                                        <a href="" class="shine__animate-link">
                                            <img src="{{ $scholarship->savescholorship && $scholarship->savescholorship->avatar ? asset($scholarship->savescholorship->avatar) : asset('images/logo.png') }}" alt="img">
                                        </a>
                                    </div>
                                    <div class="scholarship__item-two-content w-100">
                                        <a href="{{($scholarship->savescholorship) ? route('subdomain.home',$scholarship->savescholorship->company->company_name) : 'javascript:void(0)' }}" class="scholarship__item-tag">{{ $scholarship->savescholorship ? $scholarship->savescholorship->company ? $scholarship->savescholorship->company->company_name : '' : '' }}</a>
                                        <span class="heart-btn" style="color:#a2cc3b;" onClick="test({{$scholarship->id}})" >
                                            <i class="fa fa-heart"></i>
                                        </span>
                                        <h5 class="scholarship__title"><a href="javascript:void(0)">{{ $scholarship->savescholorship ? $scholarship->savescholorship->scholarship_name : '' }}</a></h5>
                                        <ul class="scholarship__item-meta list-wrap">
                                            <li><i class="fa fa-calendar" title="Last Date"></i>Published Date: {{ $scholarship->savescholorship ? \Carbon\Carbon::parse($scholarship->savescholorship->published_date)->format('jS F Y') : date('Y-m-d')}}</li>

                                        </ul>
                                        <p>{{$scholarship->savescholorship ? $scholarship->savescholorship->scholarship_info : '' }} </p>
                                        <div class="scholarship__item-bottom">
                                            <div class="course__button">
                                                <a href="{{route('Student.scholarship.details',$scholarship->id)}}" class="btn sec-btn-one list-btn">Learn More</a>
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
        </div>
</section>
    <!-- all-scholarship-end -->

<!-- Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">10th Board Marksheet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="assets/images/document.jpg">
            </div>
        </div>
    </div>
</div>
<script>
    function test(schid) {
        $.ajax({
            type: 'POST',
            url: "{{ route('Student.save.scholorship') }}",
            data: { scholarshipId: schid },
            success: function(response) {
                // Display toastr popup on success
                toastr.success(response.message);
                location.reload();
            },
            error: function(error) {
                console.error('Error saving scholarship', error);
                // Display toastr popup on error
                toastr.error('Error saving scholarship');
            }
        });
    }
</script>
</div> <!-- END PAGE CONTENT -->
@endsection