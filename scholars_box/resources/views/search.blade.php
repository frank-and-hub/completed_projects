@extends('student.layout.app')

@section('title', 'Home - Scholarsbox')

@section('content')

<style>

    .search-bar-main{
        margin: 30px 0px;
    }
    .search-bar-inner{
        text-align: center;
    }
    .search-bar-main .search-bar-inner input{
        width: 60vw;
        border: 2px solid #1a91c9;
        border-radius: 45px;
        height: 50px;
        padding: 20px 30px;
        font-size: 16px;
    }
    .search-bar-main .search-bar-inner button{
        border: none;
        background-color: #1a91c9;
        color: white;
        height: 42px;
        padding: 0px 15px;
        border-radius: 100%;
        margin-left: -3.5%;
    }
    .search-result-main{
        padding: 25px 10vw;
        margin: 45px 0px;
    }
    .search-result-main .search-result-top-head h6{
        text-align: center;
        font-size: 18px;
        color: grey;
        font-weight: 400;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner{
        box-shadow: 0px 0px 10px 5px #e4e4e4;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 25px;
        overflow: hidden;
        position: relative;
    }
    .search-result-cards-main{
        margin-top: 30px;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner img{
        position: absolute;
        width: 100%;
        top: -135px;
        left: 0;
        transform: rotate(90deg);
    }
    .search-result-main .search-result-cards-main .search-result-card-inner:hover img{
        filter: brightness(4);
    }
    .search-result-main .search-result-cards-main .search-result-card-inner:hover{
        background-color: #1a91c9;
        transition: all 0.3s ease;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner:hover .page-name{
        color: white;
        transition: all 0.3s ease;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner .page-name{
        margin-top: 30px;
        font-size: 18px;
        font-weight: 600;
        font-family: 'Poppins';
        color: #1a91c9;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner .page-link{
        border: none;
        padding: 0;
        margin-top: 10px;
        font-size: 14px;
        background: none;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner .page-link a{
        color: #000;
        font-weight: 500;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner:hover .page-link a{
        color: white;
        transition: all 0.3s ease;
    }
    .search-result-main .search-result-cards-main .search-result-card-inner .page-link i{
        transform: rotate(45deg);
        font-weight: 200;
    }
    @media only screen and (min-width: 767.5px) and (max-width: 833px){
        .search-bar-main .search-bar-inner input{
            width: 80vw;
        }
        .search-bar-main .search-bar-inner button{
            margin-left: -7vw;
        }
        .search-result-cards-main .col-md-3{
            width: 32%;
        }
    }
    @media only screen and (min-width: 833.5px) and (max-width: 991px){
        .search-bar-main .search-bar-inner input{
            width: 80vw;
        }
        .search-bar-main .search-bar-inner button{
            margin-left: -6.5vw;
        }
        .search-result-cards-main .col-md-3{
            width: 32%;
        }
    }
    @media only screen and (max-width: 767.5px){
        .search-bar-main .search-bar-inner input{
            width: 80vw;
        }
        .search-bar-main .search-bar-inner button{
            margin-left: -13.5vw;
        }
    }
    @media only screen and (max-width: 385px){
        .search-bar-main .search-bar-inner input{
            width: 80vw;
        }
        .search-bar-main .search-bar-inner button{
            margin-left: -14.5vw;
        }
    }


    
</style>

<body>

    <!--Banner Start-->
    <section class="main-inner-banner-one innerpage-banner">
        
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
                        <h1 class="h1-title">Search Results</h1>
                        <!-- <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="index.php">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Suggested Scholarships</a>
                                </li>
                            </ul>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!-- SEARCH BAR -->

    <section class="search-bar-main">

        <div class="search-bar-inner">
        <form action="{{route('search-data')}}" method="get">
                                        @csrf
            <span><input type="text" name="search" value="{{$searchTerm}}" placeholder="Search..."></span>
            <span><button type="submit"><i class="fa fa-search"></i></button></span>
</form>
        </div>
    </section>
    <!-- SEARCH BAR ENDS -->

    <!-- SEARCH MAIN CONTENT STARTS HERE -->

    <section class="search-result-main">
        <div class="search-result-top-head">
            <h6>Showing Results for "<span>keyword</span>"</h6>
        </div>
        <div class="search-result-cards-main">
            <div class="row">
                @foreach($data as $value)
             
            <div class="col-md-3">
                    <div class="search-result-card-inner">
                        <img src="https://scholarsbox.in/images/about_s4_lines.png" alt="" class="lines">
                        <div class="page-name">{{ $value->scholarship_name }}</div>

                        <div class="page-link"><a href="{{ route('Student.scholarship.details', $value->slug) }}">Visit Page <i class="fa fa-arrow-up"></i></a></div>
                    </div>
                </div>
                @endforeach
                            </div>
                           
        </div>
    </section>

    <!-- SEARCH MAIN CONTENT ENDS HERE -->

    </div> <!-- END PAGE CONTENT -->
@endsection