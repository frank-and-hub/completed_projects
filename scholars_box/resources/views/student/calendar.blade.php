<!DOCTYPE html>
<html lang="en">

<head>
    <title>Calendar - Scholarsbox</title>
    <meta name="keywords" content="Scholarsbox" />
    <meta name="description" content="Scholarsbox" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

   
    @include('student.includes.styles');
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.css" />
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/cupertino/jquery-ui.css" />


</head>
@php

    
$events = \App\Models\Webinar::where('student_id',auth()->user()->id)->get();

@endphp
<body>

    {{-- <?php include'includes/header.php' ?> --}}

    @include('student.includes.header');
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
            <img src="assets/images/banner-inner-shape-one.png" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Calendar</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="index.php">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">News</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Calendar</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <div class="container calendar-box">
        <div id="calendar"></div>
    </div>


    </div> <!-- END PAGE CONTENT -->

    {{-- <?php include'includes/footer.php' ?> --}}
    @include('student.includes.footer');


    {{-- <?php include'includes/scripts.php' ?> --}}
    @include('student.includes.scripts');

    <script src="https://cdn.jsdelivr.net/npm/moment@2/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.js"></script>

    <script src="https://static.cloudflareinsights.com/beacon.min.js"></script>


    <script>
        // initialize your calendar, once the page's DOM is ready
        $(function () {

            $('#calendar').fullCalendar({
                themeSystem: 'jquery-ui',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay,listMonth'
                },
                weekNumbers: true,
                eventLimit: true, // allow "more" link when too many events
                events: [
                    @foreach ($events as $event)    
                {
                        title: @json($event->title2),
                        start: @json($event->date),
                        end: @json($event->date),
                        
                    },
                    @endforeach
                ]
            });

        });
    </script>
</body>

</html>