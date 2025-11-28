@extends('student.layout.app')
@section('title', 'Scholarship - Study Material')
@section('content')

<?php 
$study = App\Models\Study::get();
?>

    <!--Banner Start-->
    <section class="main-inner-banner-one">
        <div class="blur-1">
            <img src="{{asset('images/Blur_1.png')}} alt="bg blur">
        </div>
        <div class="blur-2">
            <img src="{{asset('images/Blur_2.png')}} alt="bg blur">
        </div>
        <div class="banner-one-shape1 animate-this wow fadeIn" data-wow-delay=".7s"></div>
        <div class="banner-one-shape2 animate-this wow fadeIn" data-wow-delay=".9s"></div>
        <div class="banner-one-shape3 animate-this wow fadeIn" data-wow-delay="1s"></div>
        <div class="banner-one-shape4">
            <img src="{{asset('images/banner-inner-shape-one.png')}}" alt="shap">
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Study Material</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Resources</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="">Study Material</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <section class="main-services-in-one">
    <div class="service-shape-one">
        <img src="{{asset('images/service-shape-one.png')}} alt="Shape">
    </div>
    <div class="container">
        <div class="row">
            @foreach($study as $data)
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                <div class="service-box-one wow fadeInUp" data-wow-delay=".4s" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;">
                    <div class="service-boxbg-shape-one">
                        <img src="{{asset('images/blur_2.png')}} alt="Blur">
                    </div>
                    <div class="service-icon-one">
                        <img src="https://stage.webshark.in/csr-box/assets/images/study-material-image-demo.png" class="yes-d-one" alt="Icon">
                    </div>
                    <div class="service-box-content-one">
                        <a href=""><h3 class="h3-title">{{$data->title}}</h3></a>
                        <p>{!!$data->description!!}.</p>
                        <span class="date">Date: {{ date('d-m-Y', strtotime($data->created_at)) }}</span>

                    </div>

                   
                    <button type="button" onclick="downloadFile('{{ url($data->link) }}', '{{ pathinfo($data->link, PATHINFO_EXTENSION) }}')" class="btn sec-btn-one mt-3"><i class="fa fa-download"></i> Download</button>
                    <td>
                                            
                                        </td>
                </div>
            </div>
            @endforeach

            
        </div>
    </div>
</section>





    </div>

    <script>

function downloadFile(fileUrl, fileExtension) {
        var link = document.createElement('a');
        link.href = fileUrl;
        link.download = 'receipt.' + fileExtension;
        link.click();
    }
        </script>
@endsection
