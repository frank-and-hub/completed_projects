@extends('student.layout.app')
@section('title', 'Scholarship - Applied Scholarship')
@section('content')
<section class="main-inner-banner-one innerpage-banner">
    <div class="blur-1">
        <img src="{{ asset('images/Blur_1.png') }}" alt="bg blur">
    </div>
    <div class="blur-2">
        <img src="{{ asset('images/Blur_2.png') }}" alt="bg blur">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
            integrity="sha384-ezG1pTCk5Je7fqU6DIk5I2bTxUEI6xI5WGPLnx4r0fOjLlqypXg5V88KCEIY9e2I" crossorigin="anonymous">

    </div>
    <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
    <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
    <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
    <div class="banner-one-shape4">
        <img src="{{ asset('images/banner-inner-shape-one.png') }}" alt="shap">
    </div>
    <style>
        .last-record {
            background-color: red;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrum-title-one wow fadeInDown">
                    <h1 class="h1-title">Applied Scholarship</h1>
                    <div class="breadcrum-one">
                        <ul>
                            <li>
                                <a href="{{url('/')}}">Home</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right"></i>
                            </li>
                            <li>
                                <a href="">Applied Scholarship</a>
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
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-4 order-lg-0">
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
            <div class="col-xl-9 col-lg-8">
                <div class="card profile-main">
                    <div class="card-body">
                        <div class="scholar-mainbox">
                            <div class="row">
                                <div class="col-md-5 scholar-innerbox">
                                    <div class="row">
                                        <div class="col-md-2 text-center">
                                            <img src="{{ asset('images/scholarship-applied.png') }}" />
                                        </div>
                                        <div class="col-md-10 m-auto m-0 p-0">
                                            <h6 class="m-0">Scholarship Applied <span>
                                                    @if (count($scholarship) > 5)
                                                        {{ count($scholarship) - 1 }}
                                                    @else
                                                        {{ count($scholarship) }}
                                                    @endif
                                                </span></h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 scholar-innerbox">
                                    <div class="row">
                                        <div class="col-md-2 text-center">
                                            <img src="{{ asset('images/scholarship-submit.png') }}" />
                                        </div>
                                        <div class="col-md-10 m-auto m-0 p-0">
                                            <h6 class="m-0">Scholarship Submitted
                                                <span>{{ count($scholarship) }}</span></h5>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($scholarship as $key => $value)
                            <div class="stu-appliedslist">
                                <div class="row scholarship__list-wrap row-cols-1">
                                    <div class="col">
                                        @php
                                        $user = Auth::user(); // Assuming you are using Laravel's authentication system
                                        
                                        $completionPercentage = $user->profileCompletionPercentage();
                                            @endphp
                                        <div class="scholarship__item-two shine__animate-item">

                                            <div class="scholarship__item-two-content">
                                                {{-- <a href="#" class="scholarship__item-tag danger">Not Submitted</a> --}}
                                                <h5 class="scholarship__title"><a href="">
                                                        {{ $value->scholarship->scholarship_name ?? '' }}</a></h5>
                                                <ul class="scholarship__item-meta list-wrap">
                                                    
                                                    <li><i class="fa fa-info-circle" title="Latest Status"></i>
                                                        {{ ucwords(str_replace('_', ' ', $value->status)) }} </li>

                                                    <!-- <li><i class=""></i> 60</li> -->

                                                </ul>
                                                <!-- scholarship stages -->
                                                <div class="sclrship-stage-global">
                                                    <div class="sclrship-stage-inner">
                                                        <div class="sclrship-stage-heading">
                                                            <h4>Application Process Steps</h4>
                                                        </div>
                                                        <div class="sclrship-stage-ul">
                                                            <div class="ul-line">
                                                                
                                                            </div>
                                                            <ul>
                                                                @php
                                                                    $processes = explode(', ', $value->scholarship->application_processs);
                                                                @endphp

                                                                @foreach ($processes as $values)
                                                                    <li class="sclrship-stage-li">
                                                                        <span class="icon">
                                                                            <img src="{{asset('images/scholarship-stage-icon-1.png')}}"
                                                                                alt="">
                                                                        </span>
                                                                        <span
                                                                            class="stage-text">{{ $values }}</span>
                                                                    </li>
                                                                @endforeach


                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- scholarship stage ends -->
                                                <div class="statusaccordion" id="accordionExample">

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="heading{{ $key }}">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapse{{ $key }}"
                                                                aria-expanded="false"
                                                                aria-controls="collapse{{ $key }}">
                                                                See Application Status
                                                            </button>
                                                        </h2>
                                                        <div id="collapse{{ $key }}"
                                                            class="accordion-collapse collapse"
                                                            aria-labelledby="heading{{ $key }}"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div id="tracking-pre"></div>
                                                                <div id="tracking">
                                                                    <div class="tracking-list">
                                                              
                                                                        @if (count($value->applicationStatuus) > 0)
                                                                            @foreach ($value->applicationStatuus as $key => $val)
                                                                                <div class="tracking-item">
                                                                                    {{-- <div class="tracking-icon status-review @if ($loop->last) last-record @endif"><i class="fa fa-star"></i> </div> --}}
                                                                                    <div class="tracking-icon"
                                                                                        style="{{ $loop->last ? 'background-color: #0d6efd;' : 'background-color: gray;' }}">
                                                                                        <i class="fa fa-star"></i>
                                                                                    </div>



                                                                                    <div class="tracking-date">
                                                                                        {{ date('M d, Y', strtotime($val->created_at)) }}<span></span>
                                                                                    </div>
                                                                                    <div class="tracking-content">
                                                                                        {{ ucwords(str_replace('_', ' ', $val->status)) }}<span></span>
                                                                                    </div>
                                                                                    <div class="tracking-content">
                                                                                        <span>{{ $val->descss }}</span>
                                                                                    </div>
                                                                                    @if(isset($val->button))
                                                                                    <div class="tracking-content" style="float: right;">
                                                                                     <a href="{{ $val->link }}">   <button class="btn btn-primary">{{$val->button }}</button></a>
                                                                                    </div>
                                                                                    @elseif(isset($val->extra1) && $val->extra1 == 'on')
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <input type="text" class="form-control" id="doc_type" name="doc_type" placeholder="Doc Name">
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <input type="file" id="new_documents" name="document">
                                                                                        </div>
                                                                                      
                                                                                        <input type="hidden" id="sch_id" name="sch_id" value="{{ $value->scholarship_id }}">
                                                                                    </div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            {{-- <div class="tracking-item">
                                                                                <div
                                                                                    class="tracking-icon status-review">
                                                                                    <i class="fa fa-star"></i> </div>
                                                                                <div class="tracking-date">
                                                                                <span>{{ \Carbon\Carbon::now()->format('d-m-Y') }}</span>
                                                                                <div class="tracking-content">
                                                                                    Application submitted<span></span>
                                                                                </div>
                                                                                <div class="tracking-content">
                                                                                    <span>Your Application has been
                                                                                        Submitted Sucessfully.. </span>
                                                                                </div>

                                                                            </div> --}}

                                                                            <div class="tracking-item">
                                                                                {{-- <div class="tracking-icon status-review @if ($loop->last) last-record @endif"><i class="fa fa-star"></i> </div> --}}
                                                                                <div class="tracking-icon"
                                                                                    style="{{ $loop->last ? 'background-color: #0d6efd;' : 'background-color: gray;' }}">
                                                                                    <i class="fa fa-star"></i>
                                                                                </div>



                                                                                <div class="tracking-date">
                                                                                    {{ \Carbon\Carbon::now()->format('d-m-Y') }}<span></span>
                                                                                </div>
                                                                                <div class="tracking-content">
                                                                                    Application submitted<span></span>
                                                                                </div>
                                                                                <div class="tracking-content">
                                                                                    <span>Your Application has been
                                                                                        Submitted Sucessfully..</span>
                                                                                </div>
                                                                              
                                                                            </div>
                                                                        @endif
                                                                        {{-- <div class="tracking-item">
                                                                            <div class="tracking-icon status-screening" 
                                                                            @if ($value->status == 'under_review')
                                                                            style="background-color: gray;"
                                                                            @endif
                                                                        >
                                                                            <i class="fas fa-camera"></i>
                                                                        </div>
                                                                            <div class="tracking-date">Aug 10, 2018<span>11:19 AM</span></div>
                                                                            <div class="tracking-content">Under Review<span>{{ $value->status == 'under_review' ? $value->descrition : '' }}</span></div>
                                                                        </div>
                                                                        
                                                                        <div class="tracking-item">
                                                                            <div class="tracking-icon status-screening" 
                                                                            @if ($value->status == 'screening_stage')
                                                                            style="background-color: gray;"
                                                                            @endif
                                                                        >
                                                                            <i class="fas fa-camera"></i>
                                                                        </div>
                                                                            <div class="tracking-date">Aug 10, 2018<span>11:19 AM</span></div>
                                                                            <div class="tracking-content">Screening Stage<span>{{ $value->status == 'screening_stage' ? $value->descrition : '' }}</span></div>
                                                                        </div>
                                                                        <div class="tracking-item">
                                                                            <div class="tracking-icon status-verification"
                                                                                @if ($value->status == 'verification_and_due_diligence_stage')
                                                                                style="background-color: gray;"
                                                                            @endif
                                                                            >
                                                                                <i class="fa fa-id-card-o"></i>
                                                                            </div>
                                                                            <div class="tracking-date">Jul 27,
                                                                                2018<span>04:08 PM</span></div>
                                                                            <div class="tracking-content">Verification and Due Diligence Stage<span>{{ $value->status == 'verification_and_due_diligence_stage' ? $value->descrition : '' }}</span></div>
                                                                        </div>

                                                                        <div class="tracking-item">
                                                                            <div class="tracking-icon status-selected" @if ($value->status == 'selected')
                                                                                style="background-color: gray;"
                                                                            @endif>
                                                                               
                                                                                <i class="fa fa-check-square-o"></i>
                                                                            </div>
                                                                            <div class="tracking-date">Jul 10,
                                                                                2018<span>03:59 AM</span></div>
                                                                            <div class="tracking-content">Selected<span>{{ $value->status == 'selected' ? $value->descrition : '' }}</span></div>
                                                                        </div>

                                                                        <div class="tracking-item">
                                                                            <div class="tracking-icon status-not-selected" 
                                                                                @if ($value->status == 'not_selected')
                                                                                style="background-color: gray;"
                                                                                @endif
                                                                            >
                                                                                <i class="fa fa-window-close-o"></i>
                                                                            </div>
                                                                            <div class="tracking-date">Jul 10, 2018<span>03:59 AM</span></div>
                                                                            <div class="tracking-content">Not Selected<span>{{ $value->status == 'not_selected' ? $value->descrition : '' }}</span></div>
                                                                        </div>

                                                                        <div class="tracking-item">
                                                                            <div class="tracking-icon status-waitlisted"
                                                                                @if ($value->status == 'waitlisted')
                                                                                style="background-color: gray;"
                                                                            @endif
                                                                            >
                                                                                <i class="fa fa-question-circle-o"></i>
                                                                            </div>
                                                                            <div class="tracking-date">Jul 10,
                                                                                2018<span>03:59 AM</span></div>
                                                                            <div class="tracking-content">Waitlisted<span>{{ $value->status == 'waitlisted' ? $value->descrition : '' }}</span></div>
                                                                        </div> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

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
                <img src="{{ asset('images/document.jpg') }}">
            </div>
            <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#new_documents').on('change', function(event) {
            var fileInput = event.target;
            var file = fileInput.files[0];
            
            if (file) {
                var formData = new FormData();
                formData.append('new_documents', file);
            formData.append('doc_type', $('#doc_type').val());
            formData.append('sch_id', $('#sch_id').val());
            formData.append('_token', '{{ csrf_token() }}'); // Include CSRF token if needed
    
                $.ajax({
                    url: '{{ route("Student.upload.new.documents") }}', // Replace with your route URL
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {

                        $('#doc_type').val('');
                        $('#new_documents').val('');
                        new Noty({
                            text: 'Document Uploaded successfully',
                            timeout: 3000
                        }).show();
                        // Handle success response
                    },
                    error: function(xhr, status, error) {
                        console.error('File upload failed:', error);
                        // Handle error response
                    }
                });
            }
        });
    });
    </script>
@endsection