@extends('student.layout.app')
@section('title', 'Scholarship - Applied Scholarship')
@section('content')
<style>
    .dateinnercard{
        box-shadow: 0px 0px 5px 2px #00000018;
        padding: 30px;
        text-align: center;
        border-radius: 5px;
    }
    .dateinnercard .dateday{
        font-size: 60px;
        font-weight: 800;
        line-height: 60px;
    }
    .dateinnercard .datemonth{
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 0.02rem;
        font-weight: 700;
        margin-top: 5px;
        color: #1a91c9;
    }
    .dateouter .dateremain{
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.05rem;
        font-weight: 500;
        margin-top: 5px;
        color: black;
        text-align: center;
    }
    .event-list-card.row .event-image{
        height: 100%;
    }
    .event-list-card.row .event-image img{
        height: 100%;
        object-fit: cover;
    }
    .event-list-card.row .event-subtitle{
        font-size: 20px;
        font-weight: 600;
        letter-spacing: 0.03rem;
        line-height: 24px;
    }
    .event-list-card.row .event-title{
        font-size: 28px;
        text-transform: uppercase;
        font-weight: 800;
        margin: 5px 0px 20px 0px;
        line-height: 30px;
        color: #1a91c9;
    }
    .event-duration{
        padding: 5px 0px 20px 0px;
        border-bottom: 1px solid #e7e7e7;
        margin-bottom: 10px;
        position: relative;
        display: flex;
        align-items: center;
    }
    .event-duration .event-start-time{
        display: inline-block;
        width: 15%;
        text-align: center;
        font-size: 16px;
    }
    .event-duration .event-end-time{
        display: inline-block;
        width: 15%;
        text-align: center;
        font-size: 16px;
    }
    .event-duration .event-diff-time-main{
        display: inline-block;
        width: 35%;
        text-align: center;
        position: relative;
    }
    .event-diff-horizontal-line{
        border: 1px solid grey;
    }
    .event-diff-static{
        font-size: 14px;
        text-transform: uppercase;
    }
    .event-ctabtn a {
        background-color: #1a91c9;
        color: white;
        transition: all 0.5s ease;
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .event-ctabtn a:hover{
        background-color: black;
        transition: all 0.5s ease;
    }
    .event-list-item.row{
        margin: 20px;
        padding-bottom: 20px;
        border-bottom: 2px solid;
    }
</style>
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
                        <h1 class="h1-title">Resources & Events</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{ url('/') }}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Resources & Events</a>
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
                                    <div class="col-md-10">
                                        <form action="{{ route('Student.filter.resourse') }}" method="get">
                                            @csrf
                                            <div class="row align-items-end">
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <label for="scholarship_id">Select Scholarship</label>
                                                        <select class="form-control" name="scholarship_id" id="scholarship_id">
                                                            <option value="">Select Scholarship</option>
                                                            @foreach ($scholarships as $value)
                                                                <option @if (isset($scho_id) && $value->id == $scho_id) selected @endif
                                                                    value="{{ $value->id }}">{{ $value->scholarship_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-2 text-md-right">
                                        <a href="{{ route('Student.resourse') }}" class="btn btn-primary">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="scholar-resource-personal">
                            @if(count($resourse) > 0)
                            @foreach ($resourse as $value)
                                <div class="resource-card"
                                    style="display: inline-block;padding: 15px;border: 1px solid #e7e7e7;border-radius: 5%;width: 30%;margin: 10px;">
                                    <div class="resource-name"
                                        style="font-size: 18px;color: black;font-weight: 600;text-transform: uppercase;margin-bottom: 40px;">
                                        {{ $value->doc_name  ?? 'Resource' }}
                                    </div>

                                    <div class="resource-btn"
                                        style="display: inline-block;padding: 10px;background: #1a91c9;border-radius: 5%;margin: 0px;">
                                        <a href="{{ asset('receipts/' . $value->resource) }}"
                                            download="{{ $value->resource }}" style="color:white;">Download resources</a>
                                    </div>
                                </div>
                            @endforeach
                            @else

                                <p style="margin-left: 30px;">No data found !!</p>

                            @endif

                        </div>
                       <div class="row scholarship__list-wrap row-cols-1">
                            <h3 class="subheading" style="margin: 20px auto; width: fit-content; font-size: 35px; text-transform: uppercase; color: white; background: #1c92c9; padding: 5px  20px;">Events</h3>
                            <div class="col">
                                @foreach($webinar as $value)

                                @php
                                $date = new DateTime($value->date);
                                $day = $date->format('d'); // Day of the month (01 to 31)
                                $month = $date->format('F'); // Full month name (e.g., January, February, etc.)
                        
                                // Get today's date
                                $today = new DateTime();
                        
                                // Construct the target date for this year
                                $targetDate = new DateTime($today->format('Y') . '-' . $date->format('m') . '-' . $day);
                        
                                // Calculate the difference in days
                                $interval = $today->diff($targetDate);
                        
                                // Get the number of days
                                $daysLeft = $interval->days;
                        
                                // Adjust if the target date is in the past this year
                                if ($targetDate < $today) {
                                    // Calculate the target date for next year
                                    $targetDate->modify('+1 year');
                                    $interval = $today->diff($targetDate);
                                    $daysLeft = $interval->days;
                                }
                            @endphp
                           
                           @php
                           // Assuming $value->start_time and $value->end_time are in 'H:i' format
                           // and both times are in the same AM/PM format
                           $startTime = new DateTime($value->start_time); // E.g., '10:14 AM'
                           $endTime = new DateTime($value->end_time); // E.g., '11:15 AM'
                       
                           // Calculate the difference
                           $interval = $startTime->diff($endTime);
                       
                           // Get the total hours difference
                           $hoursDifference = $interval->h + ($interval->days * 24); // Adding days * 24 to account for differences over multiple days
                       
                          $diffen= $hoursDifference . ' hours'; // Outputs: e.g., 1 hour
                       @endphp
                       
                       
                                <div class="row event-list-item">
                                    <div class="col-md-3">
                                        <div class="dateouter">
                                            <div class="dateinnercard">
                                                <div class="dateday">{{ $day }}</div>
                                                <div class="datemonth">{{ $month }}</div>
                                            </div>
                                            <div class="dateremain">
                                                {{-- <div class="dateremaintrue">{{ $daysLeft }} Days Remaining</div> --}}
                                                <div class="dateremainfalse" style="display:none;">Event Passed</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="event-list-card row">
                                            <div class="col-md-4">
                                                <div class="event-image">
                                                    <img src="assets/images/blog-detail-img1-two.jpg" alt="">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="event-subtitle">
                                                    {{ $value->title }}
                                                </div>
                                                <div class="event-title">
                                                    {{ $value->title2 }}

                                                </div>
                                                <div class="event-duration">
                                                    <div class="event-start-time">{{ $value->start_time }}</div>
                                                    <div class="event-diff-time-main">
                                                        <div class="event-diff-time-inner">
                                                            <div class="event-diff-time">Start</div>
                                                            <div class="event-diff-horizontal-line"></div>
                                                            <div class="event-diff-static">End</div>
                                                        </div>
                                                    </div>

                                                    <div class="event-end-time">{{ $value->end_Time }}</div>
                                                </div>
                                                <div class="event-ctabtn">
                                                    <a href="{{ $value->link }}" target="_blank" class="btn btn-primary btn-event-cta-one">{{ $value->extra1 }}</a>
                                                    {{-- <a href="" class="btn btn-primary btn-event-cta-two" style="display:none;">Watch Recording</a> --}}
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
@endsection
