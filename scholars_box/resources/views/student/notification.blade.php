@extends('student.layout.app')
@section('title', 'Scholarship - Awarded Scholarship')
@section('content')

<style>


    .notifications{
        width: 100%;
    }
    .notifications-list{
        list-style: none;
        margin: 0px 15px;
        padding: 0px 0px 5px 0px;
        position: relative;
    }
    .notification-list-item{
        padding: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #c0c0c0;
        position: relative;
    }
    .notification-list-item .row:before{
        content: '';
        width: 5px;
        height: 95%;
        background-color: #1c92c6;
        position: absolute;
        top: 0;
        left: 0;
    }
    .ntf-content .ntf-meta .ntf-tag{
        padding: 5px 15px;
        background-color: #1c92c6;
        color: white;
        display: inline-block;
        font-size: 14px;
        font-weight: 500;
        border-radius: 5px;
    }
    .ntf-meta{
        padding-bottom: 10px;
    }
    .ntf-meta .row{
        align-items: center;
    }
    .ntf-meta .ntf-time{
        font-size: 13px;
        color: grey;
        text-align: end;
    }
    
    
    .ntf-mssg-heading h5{
        font-size: 17px;
        font-weight: 500;
        font-family: 'Poppins';
    }
    .ntf-mssg-text {
        font-size: 14px;
        color: #535353;
        font-weight: 400;
        line-height: 18px;
    }
    .ntf-author h6{
        margin-top: 10px;
        color: #1c93c6;
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 700;
    }
    .ntf-icon img{
        border-radius: 10px;
    }
    .notify-upperbox .row{
        align-items: center;
        justify-content: space-between;
    }
    .notfiy-sorter{
        margin-left: -20px;
    }
    
        
    </style>

     <!--Banner Start-->
     <section class="main-inner-banner-one innerpage-banner">
        <!-- <div class="blur-1">
            <img src="assets/images/Blur_1.png" alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="assets/images/Blur_2.png" alt="bg blur">
        </div> -->
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
                        <h1 class="h1-title">Notifications</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="index.php">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Notifications</a>
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
                        <nav class="dashboard-menu">
                            @include('student.sidebar')
                        </nav>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="card profile-main">
                        <div class="card-body">
                            <div class="scholar-mainbox notify-upperbox">
                                <div class="row">
                                    <div class="col-md-5 scholar-innerbox">
                                        <div class="row">
                                            <div class="col-md-2 text-center">
                                                <i class="fa fa-bell"></i>
                                            </div>
                                            <div class="col-md-10 m-auto m-0 p-0">
                                                <h6 class="m-0">All Notifications <span>{{count($notifications)}}</span></h5>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row">
                                        <select name="orderby" class="orderby notfiy-sorter">
                                            <option value="">All Notifications</option>
                                            <option value="New Scholarships">New Scholarships</option>
                                            <option value="Featured Scholarships">Featured Scholarships</option>
                                            <option value="Relevant Scholarships">Relevant Scholarships</option>
                                            <option value="Newsletter">Newsletter</option>
                                            
                                            <option value="Application Updates">Application Updates</option>
                                            <option value="Scholarship News">Scholarship News</option>
                                            <option value="Blog Updates">Blog Updates</option>
                                            <option value="Account Notifications">Account Notifications</option>
                                        </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stu-appliedslist">
                                <div class="row scholarship__list-wrap row-cols-1">
                                    <div class="col">
                                        <div class="scholarship__item-two shine__animate-item">
                                            <div class="notifications">
                                                <ul class="notifications-list">
                                                    @if(count($notifications) > 0)
                                                    @foreach($notifications as $value)
                                                    <li class="notification-list-item">
                                                        <div class="row">
                                                            <div class="col-md-1 ntf-icon">
                                                                <img src="assets/images/notification-icon-img.jpg" alt="">
                                                            </div>
                                                            <div class="col-md-11 ntf-content">
                                                                <div class="ntf-meta">
                                                                    <div class="row">
                                                                        <div class="col-md-6 col-6">
                                                                            <div class="ntf-tag">{{$value->teg}}</div>
                                                                        </div>
                                                                        <div class="col-md-6 col-6">
                                                                            @php
                                                                                $carbonDate = \Carbon\Carbon::parse($value->created_at);
                                                                                $formattedDate = $carbonDate->format('d M Y');
                                                                                $formattedTime = $carbonDate->format('h:i A');
                                                                            @endphp
                                                                            <div class="ntf-time">
                                                                                <span><i class="fa fa-clock-o"></i></span>
                                                                                
                                                                                <span class="ntf-time-date">{{$formattedDate}}</span>
                                                                                <span class="ntf-time-time">{{$formattedTime}}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="ntf-message">
                                                                    <div class="ntf-mssg-heading">
                                                                        <h5>{{$value->title}}</h5>
                                                                    </div>
                                                                    <div class="ntf-mssg-text">
                                                                        {{$value->description}}
                                                                    </div>
                                                                </div>
                                                                <div class="ntf-author">
                                                                    <h6>{{$value->author_name}}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li><!--item-1 ends-->
                                                    @endforeach
                                                    @else
                                                    <h3>Notification Not Found !!</h3>
                                                    @endif
                                                </ul>
                                           
                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

 <script>
    $(document).ready(function () {
        // Function to handle onchange event of orderby filter
        $('.notfiy-sorter').change(function () {
            var selectedValue = $(this).val();
            
            // Make an AJAX request to the server
            $.ajax({
                url: '{{ route('Student.notifications.filter') }}',
                type: 'GET',
                data: { orderby: selectedValue },
                success: function (response) {
                    // Update the notifications section with the returned data
                    updateNotifications(response.notifications);
                },
                error: function (error) {
                    console.error('Error fetching notifications:', error);
                }
            });
        });

        // Function to update the notifications section
        function updateNotifications(notifications) {
            var notificationsList = $('.notifications-list');

            // Clear the existing content
            notificationsList.empty();

            if (notifications.length > 0) {
                // Append each notification to the list
                $.each(notifications, function (index, value) {
                    var notificationItem = `
                        <li class="notification-list-item">
                            <div class="row">
                                <div class="col-md-1 ntf-icon">
                                    <img src="assets/images/notification-icon-img.jpg" alt="">
                                </div>
                                <div class="col-md-11 ntf-content">
                                    <div class="ntf-meta">
                                        <div class="row">
                                            <div class="col-md-6 col-6">
                                                <div class="ntf-tag">${value.teg}</div>
                                            </div>
                                            <div class="col-md-6 col-6">
                                                <div class="ntf-time">
                                                    <span><i class="fa fa-clock-o"></i></span>
                                                    <span class="ntf-time-date">${value.formattedDate}</span>
                                                    <span class="ntf-time-time">${value.formattedTime}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ntf-message">
                                        <div class="ntf-mssg-heading">
                                            <h5>${value.title}</h5>
                                        </div>
                                        <div class="ntf-mssg-text">
                                            ${value.description}
                                        </div>
                                    </div>
                                    <div class="ntf-author">
                                        <h6>${value.author_name}</h6>
                                    </div>
                                </div>
                            </div>
                        </li>
                    `;

                    notificationsList.append(notificationItem);
                });
            } else {
                // Show 'Notification Not Found' message
                notificationsList.append('<h3>Notification Not Found !!</h3>');
            }
        }
    });
</script>





    <!-- all-scholarship-end -->
@endsection
