@extends('student.layout.app')

@section('title', 'Scholarship - Podcast Detail')
@section('content')

<!--Banner Start-->
<section class="main-inner-banner-one">
    <div class="blur-1">
        <img src="{{asset('images/Blur_1.png')}}" alt="bg blur">
    </div>
    <div class="blur-2">
        <img src="{{asset('images/Blur_2.png')}}" alt="bg blur">
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
                    <h1 class="h1-title">Podcast Detail</h1>
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
                                <a href="">Podcasts</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right"></i>
                            </li>
                            <li>
                                <a href="">Podcast Details</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Banner End-->

<!--Podcast Detail Main Section Starts-->
<section class="pod-detail-main-section">
    <div class="container">
            <div class="row">
                <!--Blog Detail Info Start-->
                <div class="col-xl-12 col-lg-12">
                    <div class="blog-detail-box-two">
                        <div class="blog-img-two wow fadeInUp" data-wow-delay=".4s">
                            <img src="{{asset('images/podcast-demo-img.jpg')}}" alt="Blog">
                            <div class="event-detail-tag-two">
                                <a href="javascript:void(0);">Tag name</a>
                            </div>
                        </div>
                        <div class="blog-date-author-two">
                            <div class="blog-date-two">
                                <div class="blog-circle-two"></div>
                                <a href="javascript:void(0);">21 August 2021</a>
                            </div>
                            <div class="blog-author-two">
                                <div class="blog-circle-two"></div>
                                <a href="javascript:void(0);">By Author Name</a>
                            </div>
                        </div>
                        <h2 class="h2-title blog-two">Podcast Name</h2>
                        <div class="pod-detail-audio">
                            <div class="pod-detail-audio-head">Listen to the Podcast Below !</div>
                            <audio src="{{asset('videos/pod-audio-demo.mp3')}}" controls></audio>
                        </div>
                        <div class="pod-detail-video">
                            <div class="pod-detail-video-head">Watch the Podcast Below !</div>
                            <iframe src="{{asset('videos/sample-video.mp4')}}" frameborder="0" width="500" height="300"></iframe>
                        </div>

                        <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant
                            morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit
                            et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.
                        </p>
                        <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant
                            morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit
                            et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.
                        </p>

                        <p>Aliquam ut semper lacus. Nam mattis, arcu quis viverra rutrum, nulla ligula pulvinar nunc,
                            suscipit lacinia odio velit ac neque. Quisque venenatis tincidunt leo. Pellentesque habitant
                            morbi tristique senectus et netus et malesuada fames ac turpis egestas. Duis condimentum
                            ornare aliquam.</p>
                        <h3 class="h3-title blog-detail-title-one">Curabitur consectetur vulputate nibh, vitae molestie
                            urna ultrices eu</h3>
                        <p>Cras blandit bibendum volutpat. Morbi congue auctor posuere. Fusce vel tincidunt mauris. Duis
                            velit velit, iaculis sed lectus vel, convallis mollis magna.</p>
                        
                        <!-- <div class="points-two">
                            <ul>
                                <li>
                                    <div class="point-circle-event-detail"></div>
                                    <p>Nunc faucibus lectus ut felis auctor, nec sagittis leo tempus. Phasellus augue
                                        urna, blandit eu elementum ut, sodales sed est.</p>
                                </li>
                                <li>
                                    <div class="point-circle-event-detail"></div>
                                    <p>Sed fringilla hendrerit mi non porta. Cras pulvinar a turpis varius. Suspendis
                                        varius non lacus quis fringilla sollicitudin vel ex nec, luctus condimentum
                                        nunc.</p>
                                </li>
                                <li>
                                    <div class="point-circle-event-detail"></div>
                                    <p>Curabitur dui nulla, tincidunt varius mattis ut, porta at justo. Integer vel erat
                                        in augue hendrerit pulvinar et mattis lacus ornare quis</p>
                                </li>
                            </ul>
                        </div>
                        <div class="x">
                            <div class="event-detail-single-gallery">
                                <div class="tz-gallery container">

                                    <div class="row mb-10">
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg')}}">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg')}}" alt="Park">
                                            </a>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg')}}">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg')}}" alt="Bridge">
                                            </a>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg')}}">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg')}}" alt="Tunnel">
                                            </a>
                                        </div>
                                        <div class="col-sm-6 col-md-3">
                                            <a class="lightbox event-detail-gallery-image" href="https://www.w3schools.com/howto/img_nature.jpg')}}">
                                                <img src="https://www.w3schools.com/howto/img_nature.jpg')}}" alt="Coast">
                                            </a>
                                        </div>
                                        
                                    </div>

                                </div>
                            </div>
                        </div> -->
                        <div class="blog-detail-tag-social-two">
                            <!-- <div class="blog-side-tag-two">
                                <ul>
                                    <li><a href="javascript:void(0);">Business</a></li>
                                    <li><a href="javascript:void(0);">Corporate</a></li>
                                    <li><a href="javascript:void(0);">Blog</a></li>
                                </ul>
                            </div> -->
                            <div class="blog-detail-social-media-two">
                                <ul>
                                    <li><a href="javascript:void(0);"><i class="fa fa-facebook"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-instagram"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-twitter"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="javascript:void(0);"><i class="fa fa-pinterest-p"
                                                aria-hidden="true"></i></a></li>
                                </ul>
                            </div>
                            <div class="line"></div>

                            <div class="blog-details-page__author-box custom-pb-75">
                                <div class="blog-details-page__author-box__inner">
                                    <div class="img-box">
                                        <img src="{{asset('images/author.jpg')}}" alt="">
                                    </div>
                                    <div class="text">
                                        <h3>Author Name</h3>
                                        <p>That pleasures have to be repudiated and annoyances accepted. The
                                            wise
                                            man therefore always holds in these matters to this principle of
                                            selection.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="podcast-comment-section">
                                <div class="comment-inner">
                                    <div class="comment-head-1">
                                        <h5>Add Your Comments Below</h5>
                                    </div>
                                    <div class="comment-body">
                                        <textarea type="text" class="input" placeholder="Write a comment" ></textarea>
                                        <button  class='primaryContained float-right' type="submit">Add Comment</button>
                                    </div>
                                </div>
                            </div>
                            <div class="podcast-prev-comments">
                                <div class="comment-head-1">
                                    <h5>Read Other Comments</h5>
                                </div>
                                <div class="prev-cmnt-body">
                                    <div class="prev-cmnt-body-main  row">
                                        <div class="col-md-1 img-prev-main-cmnt">
                                            <img src="{{asset('images/member1-one.jpg')}}" alt="">
                                        </div>
                                        <div class="col-md-11">
                                            <div class="name-prev-main-cmnt">Brendun Murphy</div>
                                            <div class="meta-prev-main-cmnt">
                                                <span class="meta-date">Aug 21, 2023</span> at <span class="meta-time">10:10PM</span>
                                            </div>
                                            <div class="body-content-prev-main-content">
                                                <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.</p>
                                            </div>
                                            <div class="reply-btn-prev-main-cmnt">
                                                <a href=""><span class="fa fa-reply"></span> Reply</a>
                                            </div>
                                            <div class="prev-reply-cmnt">
                                                <div class="row">
                                                    <div class="col-md-1 img-prev-main-cmnt">
                                                        <img src="{{asset('images/member1-one.jpg')}}" alt="">
                                                    </div>
                                                    <div class="col-md-11">
                                                        <div class="name-prev-main-cmnt">Brendun Murphy</div>
                                                        <div class="meta-prev-main-cmnt">
                                                            <span class="meta-date">Aug 21, 2023</span> at <span class="meta-time">10:10PM</span>
                                                        </div>
                                                        <div class="body-content-prev-main-content">
                                                            <p>Hi,</p>
                                                            <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.</p>
                                                            <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.</p>
                                                            <p>Curabitur consectetur vulputate nibh, vitae molestie urna ultrices eu. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aliquam egestas elit et vehicula sagittis. Vestibulum mi dolor, luctus sit amet eleifend non, lacinia id dolor.</p>
                
                                                        </div>
                                                        <div class="reply-btn-prev-main-cmnt">
                                                            <a href=""><span class="fa fa-reply"></span> Reply</a>
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

            </div>
    </div>
</section>


@endsection

