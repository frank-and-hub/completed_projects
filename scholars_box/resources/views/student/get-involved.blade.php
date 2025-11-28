@extends('student.layout.app')

@section('title', 'Scholarship - Get involved')
@section('content')

<?php

$positions = App\Models\JobPosition::where('status',1)->get();
?>
<!--Banner Start-->
    <section class="get-inv-banner">
        <div class="get-inv-ban-img">
            <img src="{{asset('images/get-inv-main-banner-new.jpg')}}" alt="">
        </div>
    </section>
    <section class="getinvolved-section" id="get_inv_students">

        <div class="container z-indx-1">
            <div class="row align-items-center">
                <div class="col-lg-6 pr-3" >
                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="getinvolved-img1-one">
                            <img src="{{asset('images/Students-New.png')}}" alt="partner us">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="student-title-one">
                            <!-- <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Scholars Box</h2>
                            </div> -->
                            <h2 class="h2-title get-inv-h2-title">Students</h2>
                        </div>
                        <p>At ScholarsBox, we understand that the path to achieving your dreams often begins with securing the right scholarship opportunities. Our platform is dedicated to helping you uncover scholarships that align perfectly with your goals, interests, and, most importantly, your qualifications. With our intuitive platform, you'll receive timely updates on new scholarship listings and a lot more. </p>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--getinvolved End-->

    <section class="getinvolved-section" id="get_inv_corporate">

        <div class="container">
            <div class="row align-items-center flex-reverse-mobile">
            <div class="col-lg-6" >
                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="student-title-one">
                            <!-- <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Scholars Box</h2>
                            </div> -->
                            <h2 class="h2-title get-inv-h2-title">Corporates</h2>
                        </div>
                        <p>ScholarsBox is the perfect platform for discovering up-to-date and impactful scholarship opportunities, promoting your corporate scholarships or finding out scholarship donations. The team is here to assist companies interested in making a positive impact through student scholarships. Our comprehensive database of students allows for a better understanding of the scholarship process. Drawing from our extensive experience in this field, we efficiently navigate the process, ensuring transparency and up-to-date communication that helps assess the impact on students.</p>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-lg-6 pr-3">
                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="getinvolved-img1-one">
                            <img src="{{asset('images/Corporates-New.png')}}" alt="partner us">
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>


    <section class="getinvolved-section" id="get_inv_academic">

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 pr-3">
                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="getinvolved-img1-one">
                            <img src="{{asset('images/Academic-Institutions-New.png')}}" alt="partner us">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="student-title-one">
                            <!-- <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Scholars Box</h2>
                            </div> -->
                            <h2 class="h2-title get-inv-h2-title">Academic Institutions</h2>
                        </div>
                        <p>Would you like to highlight and list your institution’s scholarship opportunities? Or are you looking to nurture the future of your students? Whether it be promoting customized scholarships or empowering students to take their first step into the world with a strong portfolio and career-shaping opportunities. ScholarsBox is your trusted partner in offering a helping hand to you and your students.</p>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>


    

    <section class="about section-padding style-4" id="get_inv_ngo">
        <div class="content frs-content">
            <img src="{{asset('images/about_s4_lines.png')}}" alt="" class="lines">
            <img src="{{asset('images/about_s4_bubble.png')}}" alt="" class="bubble">
            <div class="container z-indx-1">
                <div class="row align-items-center justify-content-between flex-reverse-mobile">
                    <div class="col-lg-6" id="get_inv_ngo">
                        <div class="about-content-one wow fadeInRight" data-wow-delay=".4s"
                            style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInRight;">
                            <div class="about-title-one">
                                <!-- <div class="subtitle">
                                    <div class="subtitle-circle-one"></div>
                                    <h2 class="h2-subtitle-one">Lorem Ipsum </h2>
                                </div> -->
                                <h2 class="h2-title get-inv-h2-title">NGOs</h2>
                            </div>
                            <p>Are you an NGO dedicated to guiding promising and qualified students towards a brighter and more knowledgeable future? If so, you found the right place. ScholarsBox offers the latest and most suitable scholarships with varying eligibility criteria, providing valuable information about all scholarship opportunities. Join us in creating a better future for society and the youth.</p>
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">
                    <div class="getinvolved-img1-one">
                            <img src="{{asset('images/NGO1.png')}}" alt="partner us">
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </section>


    <!--getinvolved Start-->
    <section class="getinvolved-section" id="get_inv_government">

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 pr-3">
                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="getinvolved-img1-one">
                            <img src="{{asset('images/Government1.png')}}" alt="partner us">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="student-title-one">
                            <!-- <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Scholars Box</h2>
                            </div> -->
                            <h2 class="h2-title get-inv-h2-title">Government</h2>
                        </div>
                        <p>Partnering with the Government bridges the gap that separates scholarships and deserving candidates, be it by the provision of funds or by creating the right opportunities. ScholarsBox serves as a platform for showcasing these Government scholarships and opportunities while streamlining the process of finding the perfect scholarship and enrollment.</p>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </section>
    <!--getinvolved End-->

    <section class="getinvolved-section" id="get_inv_recommend">

        <div class="container">
            <div class="row align-items-center flex-reverse-mobile">
                
                <div class="col-lg-6">
                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="student-title-one">
                            <!-- <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Scholars Box</h2>
                            </div> -->
                            <h2 class="h2-title get-inv-h2-title">Recommend Aspirants</h2>
                        </div>
                        <p>Encountered any aspiring students with exceptional aptitude and promise? In a world brimming with unparalleled talent and untapped potential, we want to hear from you about these remarkable individuals through your recommendations! At ScholarsBox, we're dedicated to empowering deserving aspirants by providing scholarships to fuel their educational pursuits. By endorsing these talented individuals, you're not just making a recommendation; you're actively shaping the future of our society. Join us in championing these extraordinary students and paving the way for their success.</p>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-lg-6 pr-3">
                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="getinvolved-img1-one">
                            <img src="{{asset('images/Recommend-Aspirants-New.png')}}" alt="partner us">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="getinvolved-section" id="get_inv_support">

        <div class="container z-indx-1">
            <div class="row align-items-center">
                <div class="col-lg-6 pr-3">
                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                        <div class="getinvolved-img1-one">
                            <img src="{{asset('images/Support-Individual-Aspirants-New.png')}}" alt="partner us">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                        <div class="student-title-one">
                            <!-- <div class="subtitle">
                                <div class="subtitle-circle-one"></div>
                                <h2 class="h2-subtitle-one">Scholars Box</h2>
                            </div> -->
                            <h2 class="h2-title get-inv-h2-title">Support Individual Aspirants</h2>
                        </div>
                        <p>We are here to help you reach your full potential. ScholarsBox is tailored to support individual aspirants, providing guidance and resources to achieve personal goals and aspirations.</p>
                        <p>Our individualized programs are crafted to empower individuals on their unique paths, arming them with the tools and support needed to pursue their dreams. This is the perfect platform for all individuals looking for academic success, career advancement, or personal growth. We’ll go the extra mile to help you reach all your milestones.</p>
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal"
                                                        data-bs-target="#getInvolvedModal">Join Now</button>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </section>


    

    <section class="padding_layout_1" style="background-image: url('assets/images/bg_layout_3.png');" id="get_inv_work_with_us">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                    <div class="student-title-one">
                        <h2 class="h2-title text-white">Work with Us</h2>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="full">
                        <div class="target_section">
                            <div class="row align-items-center">

                                <div class="col-lg-6">
                                    <div class="getinvolved-content-one wow fadeInRight" data-wow-delay=".4s">
                                        <div class="student-title-one">
                                            <div class="subtitle">
                                                <div class="subtitle-circle-one"></div>
                                                <h2 class="h2-subtitle-one" style="text-transform: none;">ScholarsBox</h2>
                                            </div>
                                            <h2 class="h2-title">Work With Us</h2>
                                        </div>
                                        <p>Join Our Team!</p>
                                        <p>ScholarsBox is actively seeking dedicated individuals who are passionate about making a positive impact through their work. If you're enthusiastic about education, social impact, and scholarships, and believe your skills can contribute to our team, don't hesitate to get in touch. We welcome the opportunity to connect with you!</p>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <button type="button" class="btn sec-btn-one mt-3" data-bs-toggle="modal" data-bs-target="#jobModal">Join Now</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-lg-6 pr-3">
                                    <div class="student-img-box-one wow fadeInLeft" data-wow-delay=".4s">
                                        <div class="getinvolved-img1-one">
                                            <img src="{{asset('images/Career-16-15.jpg')}}" alt="partner us">
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

    </div> <!-- END PAGE CONTENT -->



    <!-- Get Involved Modal -->
    <div class="modal fade" id="getInvolvedModal" tabindex="-1" role="dialog" aria-labelledby="getInvolvedModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="getInvolvedModalLabel">Join us</h5>
                    <button type="button" id="closeGetInvolvedModalButton" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="getinvolved-form" data-wow-delay=".4s">
                        <form action="#" id="getinvolved_form" class="" >
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Ms / Mr / Mrs*</label>
                                        <select class="form-input-one">
                                            <option value="">Select Ms / Mr / Mrs</option>
                                            <option>Ms</option>
                                            <option>Mr</option>
                                            <option>Mrs</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Full Name*</label>
                                        <input type="text" name="FirstName" id="FirstName" class="form-input-one"
                                            placeholder="Full Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Email Id*</label>
                                        <input type="email" name="email" id="email" class="form-input-one"
                                            placeholder="Email Address">
                                    </div>
                                </div>
                                <div class="col-md-6">
    <div class="form-box-one">
        <label for="PhoneNo">Working Contact Number*</label>
        <input type="text" name="PhoneNo" id="PhoneNo" class="form-input-one" placeholder="Phone No." pattern="\d+" title="Please enter only numbers">
        <div id="phoneNoError" style="color: red;"></div>
    </div>
</div>
<div class="col-md-6">
    <div class="form-box-one">
        <label for="AlternateNo">Alternate Contact Number*</label>
        <input type="text" name="AlternateNo" id="AlternateNo" class="form-input-one" placeholder="Phone No." pattern="\d+" title="Please enter only numbers">
        <div id="alternateNoError" style="color: red;"></div>
    </div>
</div>
                                <div class="col-md-6">
                                    <div class="form-box-one">

                                        <label>Category*</label>

                                        <select class="form-input-one" name="category" id="category">
                                            <option value="">Select Category</option>
                                            <option value="Aspirants">Aspirants</option>
                                            <option value="Students">Students</option>
                                            <option value="Corporate">Corporate</option>
                                            <option value="Academic Institutions">Academic Institutions</option>
                                            <option value="NGO">NGO</option>
                                            <option value="Government">Government</option>
                                            <option value="Recommend Aspirants">Recommend Aspirants</option>
                                            <option value="support Individual Aspirants">Support Individual Aspirants</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-box-one">

                                        <label>Subject*</label> 

                                        <select class="form-input-one" name="subject" id="subject">
                                            <option value="">Select Subject</option>
                                            <option value="I am a representative of CSR fund & I have a scholarship project to offer">I am a representative of CSR fund & I have a scholarship project to offer</option>
                                            <option value="We would like to know more about a particular scholarship">We would like to know more about a particular scholarship</option>
                                            <option value="We would like ScholarsBox to participate in a particular event">We would like ScholarsBox to participate in a particular event</option>
                                            <option value="We have a Government partnership to offer">We have a Government partnership to offer</option>
                                            <option value="We would like you to partner with our institution">We would like you to partner with our institution</option>
                                            <option value="We would like to work as an implementation partner for ScholarsBox">We would like to work as an implementation partner for ScholarsBox</option>
                                            <option value="We are from media, would like to know more">We are from media, would like to know more</option>
                                            <option value="Others">Others</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-box-one">
                                        <label>Messages *</label>
                                        <textarea class="form-input-one" name="message" placeholder="Messages" id="message"></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-box-one mb-0">
                                        <button type="submit" class="sec-btn-one"><span>Submit</span></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
            </div>
        </div>
    </div>

    <!-- Job Modal -->
    <div class="modal fade" id="jobModal" tabindex="-1" role="dialog" aria-labelledby="jobModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobModalLabel">Join us</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="getinvolved-form" data-wow-delay=".4s">
                        <form action="{{route('save.position.now')}}" method="post" enctype="multipart/form-data">
                        @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Full Name*</label>
                                        <input type="text" name="name" class="form-input-one"
                                            placeholder="First Name" required>
                                    </div>
                                </div>
                                <input type="hidden" name="type" value="1">
                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Email Id*</label>
                                        <input type="email" class="form-input-one"
                                            placeholder="Email Address" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Phone Number*</label>
                                        <input type="text" name="working_no" class="form-input-one" placeholder="Phone No."
                                        pattern="\d+" title="Please enter only numbers" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-box-one">

                                        <label>Category*</label>

                                        <select class="form-input-one" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Open Positions">Open Positions</option>
                                            <option value="Volunteer">Volunteer</option>
                                            <option value="Internship">Internship</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-box-one">

                                        <label>Position*</label>
                                        

                                        <select class="form-input-one" name="position" required>
                                            
                                            <option value="">Select Category</option>
                                            @foreach($positions as $value)
                                            <option value="{{$value->value}}">{{$value->name}}</option>
                                            @endforeach
                                           

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-box-one">
                                        <label>Upload Your Resume*</label>
                                        <input type="file"  class="" placeholder="" name="resume" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-box-one">
                                        <label>Messages *</label>
                                        <textarea class="form-input-one" placeholder="Messages" name="message"></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-box-one mb-0">
                                        <button type="submit" id="joinUssasSubmitButton" class="sec-btn-one"><span>Submit</span></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
        $(document).ready(function () {
            // Add an event listener for the form submission
            $('#getinvolved_form').submit(function (e) {
                e.preventDefault(); // Prevent the default form submission
    
                // Define an array of field IDs that are required
                var requiredFieldIds = ['FirstName', 'email', 'PhoneNo', 'AlternateNo', 'category', 'subject', 'message'];
    
                // Validate form fields
                var isValid = true;
                $.each(requiredFieldIds, function(index, fieldId) {
                    var fieldValue = $('#' + fieldId).val();
                    if (!fieldValue || fieldValue.trim() === '') {
                        $('#' + fieldId).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('#' + fieldId).removeClass('is-invalid');
                    }
                });
    
                // If any field is empty, do not proceed with AJAX submission
                if (!isValid) {
                    toastr.error('Please fill in all required fields.');
                    return;
                }
    
                // Get form data
                var formData = $(this).serialize();
                $('#getInvolvedModal').modal('hide'); 
                // Send AJAX request
                $.ajax({
                    type: 'POST',
                    url: '{{ route('save.join.now') }}', // Replace with your actual route
                    data: formData,
                    success: function (response) {
                        // Handle success (you may show a success message or close the modal)
                        toastr.success(response.message);
                   
                        $('#getInvolvedModal').modal('hide'); // Close the modal
                    },
                    error: function (error) {
                        // Handle errors (you may display an error message to the user)
                        console.log(error.responseJSON);
                    }
                });
            });
        });
    </script>
    <script>
    document.getElementById('PhoneNo').addEventListener('input', function(event) {
        var phoneNumber = event.target.value.trim();
        var errorDiv = document.getElementById('phoneNoError');
        
        if (phoneNumber.length !== 10 || isNaN(phoneNumber)) {
            errorDiv.textContent = "Please enter a valid 10-digit phone number.";
        } else {
            errorDiv.textContent = "";
        }
    });
 
</script>
<script>
    document.getElementById('AlternateNo').addEventListener('input', function(event) {
        var alternateNumber = event.target.value.trim();
        var errorDiv = document.getElementById('alternateNoError');
        
        if (alternateNumber.length !== 10 || isNaN(alternateNumber)) {
            errorDiv.textContent = "Please enter a valid 10-digit alternate phone number.";
        } else {
            errorDiv.textContent = "";
        }
    });
</script>

@endsection
