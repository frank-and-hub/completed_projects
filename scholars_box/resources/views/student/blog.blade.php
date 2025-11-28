@extends('student.layout.app')
@section('title', 'Blog')

@section('content')

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
                        <h1 class="h1-title">Blogs</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
								
							
                                <li>
                                    <a href="#">Blogs</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    
<!--Blog Box Start-->
<section class="main-blog-grid-in-one">
    <!-- <div class="blog-grid-shape1-one">
        <img src="https://media.istockphoto.com/id/636379014/photo/hands-forming-a-heart-shape-with-sunset-silhouette.jpg?s=612x612&w=0&k=20&c=CgjWWGEasjgwia2VT7ufXa10azba2HXmUDe96wZG8F0=" alt="Shape">
    </div>
    <div class="blog-grid-shape2-one">
        <img src="https://media.istockphoto.com/id/636379014/photo/hands-forming-a-heart-shape-with-sunset-silhouette.jpg?s=612x612&w=0&k=20&c=CgjWWGEasjgwia2VT7ufXa10azba2HXmUDe96wZG8F0=" alt="Shape">
    </div>
    <div class="blog-grid-shape3-one">
        <img src="https://media.istockphoto.com/id/636379014/photo/hands-forming-a-heart-shape-with-sunset-silhouette.jpg?s=612x612&w=0&k=20&c=CgjWWGEasjgwia2VT7ufXa10azba2HXmUDe96wZG8F0=" alt="Shape">
    </div> -->
    <div class="container">
        <div class="row">
            @if(count($blogs)>0)
            @foreach($blogs as $blog)
           
			<div class="col-lg-4 col-md-6">
                <div class="blog-box-one wow fadeInUp" data-wow-delay=".4s">
                    <div class="blog-img-one">
                        <img src="{{asset('uploads/'.$blog->image)}}" alt="Blog">
                        <div class="blog-tag-one">
                            <!--<a href="javascript:void(0);">Exam</a>-->
                        </div>
                    </div>
                    <div class="blog-box-content-one">
                        <div class="blog-date-author-one">
                            <div class="blog-date-one">
                                <div class="blog-circle-one"></div>
                                <a href="javascript:void(0);">{{$blog->created_at->format('d-m-Y')}}</a>
                            </div>
                            <div class="blog-author-one">
                                <div class="blog-circle-one"></div>
                                <a href="javascript:void(0);">ScholarsBox</a>
                            </div>
                        </div>
                        <a href="{{route('Student.blog-details',$blog->slug)}}"><h3 class="h3-title">{{$blog->blog_title}}</h3></a>
                        <a href="{{route('Student.blog-details',$blog->slug)}}" class="btn-link-one">Read More</a>
                    </div>
                </div>
            </div>
		@endforeach
		@else
		<h3>Blog Not Found !!</h3>
		@endif
			
        </div>
        <!--<div class="row">-->
        <!--    <div class="col-lg-12">-->
        <!--        <div class="blog-pagination-one">-->
        <!--            <a href="javascript:void(0);" class="pagination-arrow-one"><i class="fa fa-angle-left" aria-hidden="true"></i></a>-->
        <!--            <ul>-->
        <!--                <li class="active">1</li>-->
        <!--                <li>2</li>-->
        <!--                <li>3</li>-->
        <!--                <li>4</li>-->
        <!--            </ul>-->
        <!--            <a href="javascript:void(0);" class="pagination-arrow-one"><i class="fa fa-angle-right" aria-hidden="true"></i></a>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
    </div>
</section>
<!--Blog Box End-->




		</div>	<!-- END PAGE CONTENT -->
        <!--Newsletter Start-->

    <!--Newsletter End-->

@endsection