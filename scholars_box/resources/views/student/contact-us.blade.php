@extends('student.layout.app')
@section('title', 'Scholarship - Contact Us')
@section('content')
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
                        <h1 class="h1-title">Contact Us</h1>
                        <div class="breadcrum-one">
                            <ul>
                                <li>
                                    <a href="{{url('/')}}">Home</a>
                                </li>
                                <li>
                                    <i class="fa fa-chevron-right"></i>
                                </li>
                                <li>
                                    <a href="#">Contact Us</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Banner End-->

    <!--Contact Us Start-->
    <section class="main-contact-us-in-one"  id="contact_get_in_touch">
        <div class="contact-us-shape-one">
            <img src="{{asset('images/contact-us-shape-one.png')}}" alt="Shape">
        </div>
        <div class="container">
            <div class="contact-us-bg-one">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="contact-map-one wow fadeInLeft" data-wow-delay=".8s">
                            <!--<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d469822.6540042018!2d72.17828858900373!3d23.0791708115469!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395e848aba5bd449%3A0x4fcedd11614f6516!2sAhmedabad%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1688026702828!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>-->
                            <iframe src="{{$contact->map}}" width="100%" height="450" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="contact-us-content-one wow fadeInRight" data-wow-delay=".8s">
                            <div class="contact-us-title-one">
                                <div class="subtitle">
                                    <div class="subtitle-circle-one"></div>
                                    <h2 class="h2-subtitle-one">Contact Us</h2>
                                </div>
                                <h2 class="h2-title">Get In Touch</h2>
                                <!--<p>Maecenas sit amet felis purus. Ut nec interdum ligula. Duis volutpat libero ante, gravida finibus lorem finibus in.</p>-->
                                <p>{!!$contact->description!!}</p>
                            </div>
                            <ul>
                                <li>
                                    <div class="contact-us-icon-one">
                                        <img src="{{asset('images/contact-email-one.png')}}" alt="Email">
                                    </div>
                                    <div class="contact-us-text-one">
                                        <h3 class="h3-title">Email:</h3>
                                        <!--<span>info@scholarsbox.in</span>-->
                                        <span><a href="mailto:{{$contact->email}}" style="color: #777777 !important;">{{$contact->email}}</a></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="contact-us-icon-one">
                                        <img src="{{asset('images/contact-call-one.png')}}" alt="Call">
                                    </div>
                                    <div class="contact-us-text-one">
                                        <h3 class="h3-title">Call Now:</h3>
                                        <span><a href="tel:{{$contact->number}}" style="color: #777777 !important;">{{$contact->number}}</a></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="contact-us-icon-one">
                                        <img src="{{asset('images/conatct-location-one.png')}}" alt="Location">
                                    </div>
                                    <div class="contact-us-text-one">
                                        <h3 class="h3-title">Address:</h3>
                                        <span>{{$contact->address}}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Contact Us End-->

    <!--Get In Touch Start-->
    <section class="main-get-in-touch-in-page-one">
    <div class="get-touch-shape-one">
        <img src="{{asset('images/get-touch-shape-one.png')}}" alt="Shape">
    </div>
    <div class="container">
        <div class="row align-items-top">
            <div class="col-xl-6 col-lg-5">
                <div class="get-in-touch-title-one wow fadeInLeft" data-wow-delay=".4s">
                    <div class="subtitle">
                        <div class="subtitle-circle-one"></div>
                        <h2 class="h2-subtitle-one">Get In Touch</h2>
                    </div>
                    <h2 class="h2-title">{{$contact->title}} </h2>
                    <p>{!!$contact->long_description!!}</p>
                </div>
            </div>
            <div class="col-xl-6 col-lg-7">
                <div class="get-in-touch-form-one wow fadeInRight" data-wow-delay=".4s">
                    <form id="contact_form" name="contact_form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-box-one">
                                    <input type="text" name="name" class="form-input-one" placeholder="First Name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-box-one">
                                    <input type="text" name="LastName" class="form-input-one" placeholder="Last Name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-box-one">
                                    <input type="email" name="email" class="form-input-one" placeholder="Email Address" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-box-one">
                                    <input type="text" name="working_no" class="form-input-one" placeholder="Phone No." required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-box-one">
                                    <textarea class="form-input-one" name="message" placeholder="Message..."></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="type" value="4">
                            <div class="col-12">
                                <div class="form-box-one mb-0">
                                    <button type="button" class="sec-btn-one" id="submit_contact"><span>Submit Now</span></button>
                                    <button type="reset" class="sec-btn-one d-none" id="reset_contact"><span>Reset</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
    <!--Get In Touch End-->
<script>
   document.getElementById('submit_contact').addEventListener('click', function(event) {
        event.preventDefault();
    
        var formData = new FormData(document.getElementById('contact_form'));
        var serializedData = [];
    
        for (var [key, value] of formData.entries()) {
            serializedData.push({ 'name': key, 'value': value });
        }
  
        fetch("{{ route('Student.contact_usss') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData,
        })
        .then(response => response.json()) 
        .then(data => {
            var contactForm = document.getElementById('contact_form');
           
            if (contactForm) {
                toastr.success('Details Saved Sucessfully !!');
                contactForm.reset();
            }
        })
        .catch(error => {
            console.error('Error:', error); // Log any errors that occur during the request
        });
    });

</script>
{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        $('#submit_contact').on('click', function(){
            $.ajax({
                url: '{{ route('newsletter.mail') }}', // Change this to your endpoint
                type: 'POST',
                data: $('#contact_form').serialize(),
                success: function(response) {
            console.log(response);
            if(response.data == 0){
                toastr.error(response.message);
                $('#contact_form')[0].reset(); // Reset the form
            }else{
                toastr.success(response.message);
            $('#contact_form')[0].reset(); // Reset the form
            }
        
        },
        error: function(error) {
            console.log(error.responseJSON);
            toastr.error('Email Not found. !! ');
                $('#contact_form')[0].reset(); // Reset the form
        }
            });
        });
    });
</script> --}}
@endsection
