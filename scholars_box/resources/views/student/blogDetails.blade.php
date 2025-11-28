@extends('student.layout.app')
@section('title', 'Blog Details')

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
            <!-- <img src="assets/images/banner-inner-shape-one.png" alt="shap"> -->
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrum-title-one wow fadeInDown">
                        <h1 class="h1-title">Blog Details</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="{{route('Student.blog.list')}}">Blogs</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                              
                                <li>
                                    <a href="#">Blog Details</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->


    <!--Blog Detail Start-->
    <section class="main-blog-detail-one">
        <div class="blog-detail-shape1-one">
    </div>
    <div class="blog-detail-shape2-one">
    </div>
        <div class="container">
            <div class="row">
                <!--Blog Detail Info Start-->
                <div class="col-xl-8 col-lg-7">
                    <div class="blog-detail-box-one">
                        <h2 class="h2-title blog-one">{{$blogDetails->blog_title}}</h2>
                        <div class="blog-date-author-one">
                            <div class="blog-date-one">
                                <div class="blog-circle-one"></div>
                                <a href="javascript:void(0);">{{$blogDetails->created_at->format('d-m-Y')}}</a>
                            </div>
                            <div class="blog-author-one">
                                <div class="blog-circle-one"></div>
                                <a href="javascript:void(0);">By ScholarsBox</a>
                            </div>
                        </div>
                        <div class="blog-img-one wow fadeInUp  blog-detail-cstm-force-height-100" data-wow-delay=".4s">
                            <img src="{{asset('uploads/'.$blogDetails->image)}}" alt="Blog">
                            <div class="blog-tag-one">
                                <a href="javascript:void(0);">{{$blogDetails->teg?ucwords($blogDetails->teg):''}}</a>
                            </div>
                        </div>

                       
                        <p>{!!$blogDetails->description!!}</p>
                      

                        
                       

                        

                    </div>

                    <div class="blog-detail-tag-social-one">
                        <!--<div class="blog-side-tag-one">-->
                        <!--    <ul>-->
                        <!--        <li><a href="javascript:void(0);">Business</a></li>-->
                        <!--        <li><a href="javascript:void(0);">Corporate</a></li>-->
                        <!--        <li><a href="javascript:void(0);">Blog</a></li>-->
                        <!--    </ul>-->
                        <!--</div>-->
                        <div class="blog-detail-social-media-one">
                            <ul>
                                
                            <li>
                                
     

                                <!--<li><a href="javascript:void(0);"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>-->
                                <li><a href="https://www.instagram.com/scholarsbox_in"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                <li><a href="https://twitter.com/ScholarsBox"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                <li><a href="https://www.linkedin.com/company/scholarsbox/"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>                                
                                <!--<li><a href="javascript:void(0);"><i class="fa fa-bookmark" aria-hidden="true"></i></a></li>-->
                            </ul>
                        </div>
                        <div class="line"></div>
                    </div>
                <br>
                    
                </div>
                <!--Side Bar Start-->
                <div class="col-xl-4 col-lg-5">
                    <div class="blog-search-form">
                        <form action="{{route('search.blogs')}}" method="post">
                            @csrf
                            <div class="form-box">
                                <input type="text" name="search" placeholder="Search..." required>
                                <button type="submit" class="sec-btn-one"><span><i class="fa fa-search"
                                            aria-hidden="true"></i></span></button>
                            </div>
                        </form>
                    </div>

                    <div class="blog-recent-post-one">
                        <h3 class="h3-title">Recent Blog</h3>
                        <div class="blog-side-line-one"></div>
                        <ul>
                            @foreach($Allblogs as $value)
                            <li>
                                <div class="blog-recent-post-img-one">
                                    <img src="{{asset('uploads/'.$value->image)}}"  style="width:100px;height:80px;"alt="Blog">
                                </div>
                                <div class="blog-recent-post-title-one">
                                    <a href="{{route('Student.blog-details',$value->slug)}}"><span>{{$value->blog_title}}</span></a>
                                    <div class="blog-date-one">
                                        <div class="blog-circle-one"></div>
                                        <a href="javascript:void(0);">{{ date('d M Y', strtotime($value->created_at)) }}</a>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
              @php
                                                                    $processes = explode(', ', $blogDetails->tags);
                                                                   
                                                                    @endphp
                                                                     @if(count($processes) > 0 && $blogDetails->tags != '')   
                                 
                    <div class="blog-side-tag-one">
                        <h3 class="h3-title">Tags</h3>
                        <div class="blog-side-line-one"></div>
           

                                                       
                        <ul>
                            
                             @foreach ($processes as $values)
                            <li><a href="javascript:void(0);">{{$values}}</a></li>
                            @endforeach
                     
                        </ul>
                       
                    </div>
 @endif
                </div>
            </div>

            <!--<div class="row">-->
                <!--Related Blog Start-->
            <!--    <div class="blog-detail-related-post-one">-->
            <!--            <div class="row">-->
            <!--                <div class="col-lg-12">-->
            <!--                    <div class="blog-detail-related-post-title-one">-->
            <!--                        <div class="subtitle">-->
            <!--                            <div class="subtitle-circle-one"></div>-->
            <!--                            <h2 class="h2-subtitle-one">Our Blog</h2>-->
            <!--                        </div>-->
            <!--                        <h2 class="h2-title">Related Posts</h2>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--            <div class="row">-->
            <!--                <div class="col-xl-6 col-lg-12">-->
            <!--                    <div class="blog-box-one wow fadeInUp" data-wow-delay=".4s">-->
            <!--                        <div class="blog-img-one">-->
            <!--                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRVv_NLgONGe_Cpht4K14DOwxLEI7Yb-Qf6bw&usqp=CAU" alt="Blog">-->
            <!--                            <div class="blog-tag-one">-->
            <!--                                <a href="javascript:void(0);">Exam</a>-->
            <!--                            </div>-->
            <!--                        </div>-->
            <!--                        <div class="blog-box-content-one">-->
            <!--                            <div class="blog-date-author-one">-->
            <!--                                <div class="blog-date-one">-->
            <!--                                    <div class="blog-circle-one"></div>-->
            <!--                                    <a href="javascript:void(0);">7 March 2021</a>-->
            <!--                                </div>-->
            <!--                                <div class="blog-author-one">-->
            <!--                                    <div class="blog-circle-one"></div>-->
            <!--                                    <a href="javascript:void(0);">By Scholarsbox</a>-->
            <!--                                </div>-->
            <!--                            </div>-->
            <!--                            <a href=""><h3 class="h3-title">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</h3></a>-->
            <!--                            <a href="" class="btn-link-one">Read More</a>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--                <div class="col-xl-6 col-lg-12">-->
            <!--                    <div class="blog-box-one wow fadeInDown" data-wow-delay=".5s">-->
            <!--                        <div class="blog-img-one">-->
            <!--                            <img src="https://images.pexels.com/photos/220429/pexels-photo-220429.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="Blog">-->
            <!--                            <div class="blog-tag-one">-->
            <!--                                <a href="javascript:void(0);">Marketing</a>-->
            <!--                            </div>-->
            <!--                        </div>-->
            <!--                        <div class="blog-box-content-one">-->
            <!--                            <div class="blog-date-author-one">-->
            <!--                                <div class="blog-date-one">-->
            <!--                                    <div class="blog-circle-one"></div>-->
            <!--                                    <a href="javascript:void(0);">7 March 2021</a>-->
            <!--                                </div>-->
            <!--                                <div class="blog-author-one">-->
            <!--                                    <div class="blog-circle-one"></div>-->
            <!--                                    <a href="javascript:void(0);">By Scholarsbox</a>-->
            <!--                                </div>-->
            <!--                            </div>-->
            <!--                            <a href=""><h3 class="h3-title">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</h3></a>-->
            <!--                            <a href="" class="btn-link-one">Read More</a>-->
            <!--                        </div>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--</div>-->
        </div>
    </section>
    <!--Blog Detail End-->





    </div> <!-- END PAGE CONTENT -->
<script>
    $(document).ready(function () {
        $('.like-btn').click(function () {
            var form = $(this).closest('form');
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    // Update the UI or do something after a successful like
                    console.log(response);
                }
            });
        });

        $('.unlike-btn').click(function () {
            var form = $(this).closest('form');
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    // Update the UI or do something after a successful unlike
                    console.log(response);
                }
            });
        });
    });
</script>
    @endsection