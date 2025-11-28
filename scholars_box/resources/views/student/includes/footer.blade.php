<!--Newsletter Start-->
@include('student.includes.newsletter')
<!--Newsletter End-->
@include('student.includes.model')
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
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-box-one">
                                <label>Ms./Mr./Mrs.*</label>
                                <select class="form-input-one" required>
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
                                <input type="text" name="FirstName" class="form-input-one"
                                    placeholder="Full Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box-one">
                                <label>Email ID*</label>
                                <input type="email" name="EmailAddress" class="form-input-one"
                                    placeholder="Email Address" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box-one">
                                <label>Contact Number*</label>
                                <input type="text" name="PhoneNo" class="form-input-one" placeholder="Phone No."
                                pattern="\d+" title="Please enter only numbers" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box-one">
                                <label>Alternate Contact Number*</label>
                                <input type="text" name="AlternateNo" class="form-input-one" placeholder="Phone No."
                                    pattern="\d+" title="Please enter only numbers" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box-one">

                                <label>Category*</label>

                                <select class="form-input-one" required>
                                    <option value="">Select Category</option>
                                    <option>Aspirants</option>
                                    <option>Corporate</option>
                                    <option>Academic Institutions</option>
                                    <option>NGO</option>
                                    <option>Government</option>
                                    <option>Recommend Aspirants</option>
                                    <option>Support Individual Aspirants</option>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-box-one">

                                <label>Subject*</label>

                                <select class="form-input-one" required>
                                    <option value="">Select Subject</option>
                                    <option>I am a representative of CSR fund & I have a scholarship project to offer</option>
                                    <option>We would like to know more about a particular scholarship</option>
                                    <option>We would like ScholarsBox to participate in a particular event</option>
                                    <option>We have a Government partnership to offer</option>
                                    <option>We would like you to partner with our institution</option>
                                    <option>We would like to work as an implementation partner for ScholarsBox</option>
                                    <option>We are from media, would like to know more</option>
                                    <option value="">Others</option>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-box-one">
                                <label>Message *</label>
                                <textarea class="form-input-one" placeholder="Message" required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-box-one mb-0">
                                <button type="button" id="joinUsSubmitButton" class="sec-btn-one"><span>Submit</span></button>
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
<script>
    document.addEventListener('click', function (event) { console.log(event.target.id);
        if (event.target.id === 'joinUsSubmitButton') {
        
            event.preventDefault();
            new Noty({
                text: 'Information saved successfully!',
            }).show();
        }
    });

    const passwordInput = document.querySelector('input[type="password"]');
    const passwordInputC = document.querySelector('input[name="confirm_password"]');
    const passwordToggle = document.getElementById('password-toggle');
    const passwordToggle2 = document.getElementById('password-toggle2');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function () {
            // Toggle the password input type between "password" and "text"
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            // Toggle the eye icon based on the password input type
            passwordToggle.classList.toggle('fa-eye-slash', passwordInput.type === 'text');
        });
    }
    if (passwordToggle2) {
        passwordToggle2.addEventListener('click', function () {
            // Toggle the password input type between "password" and "text"
            passwordInputC.type = passwordInputC.type === 'password' ? 'text' : 'password';
            // Toggle the eye icon based on the password input type
            passwordToggle.classList.toggle('fa-eye-slash', passwordInputC.type === 'text');
        });
    }
                                        
</script>

    </div>
</div>
</div>
    
    <!--Footer Start-->
    <footer class="main-footer-one">
        <div class="footer-blur1-one" style="display:none;">
            <img src="{{asset('images/blur_7.png')}}" alt="Blur">
        </div>
        <div class="footer-blur2-one" style="display:none;">
            <img src="{{asset('images/blur_7.png')}}" alt="Blur">
        </div>
        <!-- <div class="footer-one-shape1 animate-this wow fadeIn" data-wow-delay=".4s"></div> -->
        <div class="footer-one-shape2 animate-this wow fadeIn" data-wow-delay=".5s"></div>
        <div class="footer-one-shape3 animate-this wow fadeIn" data-wow-delay=".6s"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-12 col-sm-6">
                    <div class="footer-logo-content-one footer-contact-one">
                        <a href="https://scholarsbox.in">
                            <img src="{{asset('images/Scholars Box-Logo-03-footer.png')}}" alt="" class="footer-logo">
                        </a>
                        <p>ScholarsBox is a dedicated social impact platform committed to the democratization of education access through the provision of scholarships funded by Corporate Social Responsibility (CSR) initiatives. We collaborate with corporations, non-governmental organizations (NGOs), and educational institutions to develop impactful scholarship programs aimed at empowering individuals and communities.</p>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-8 col-md-12 col-sm-6">
                    <div class="footer-links-one">

                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-3 mobile-mb-30">
                                <h3 class="h3-title">Explore More</h3>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <ul>
                                            <!--<li><a href="javascript:void(0);">-->
                                            <!--        <div class="footer-link-hover-one"></div><span>Updates</span>-->
                                            <!--    </a>-->
                                            <!--</li>-->
                                            <li>
                                                <a href="{{route('Student.scholarship.index')}}">
                                                    <div class="footer-link-hover-one"></div><span>Scholarships</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{route('get-involved')}}">
                                                    <div class="footer-link-hover-one"></div><span>Partner with Us</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{route('get-involved')}}#get_inv_support">
                                                    <div class="footer-link-hover-one"></div><span>Support Individual Aspirants</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{route('get-involved')}}#get_inv_recommend">
                                                    <div class="footer-link-hover-one"></div><span>Recommend Individual Aspirants</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                   
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3">
                                <h3 class="h3-title">Quick Links</h3>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <ul>
                                              <li>
                                                <a href="{{route('contact-us')}}">
                                                    <div class="footer-link-hover-one"></div><span>Help Desk</span>
                                                </a>
                                            </li>
                                            @foreach (\App\Models\CmsPage::all() as $value)
                                            <li>
                                                <a href="{{route('cmspages', $value->slug)}}">
                                                    <div class="footer-link-hover-one"></div><span>{{$value->page_name}}</span>
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <!--<h3 class="h3-title">Quick Links</h3>-->
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        <div class="footer-links-one">
                                            <div class="footer-contact-one">
                                                <ul class="address-first-child-ul-mob-tab">
                                                    <li>
                                                        <div class="footer-contact-icon-one">
                                                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                                                        </div>
                                                        <span><a href="https://scholarsbox.in/contact/us#contact_get_in_touch" style="color:#fff !important;">Swati Trinity, Applewood Township, A-903(i), Shela, Sarkhej-Okaf, Gujarat 380058</a></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="footer-links-one">
                                            <div class="footer-contact-one">
                                                <ul>
                                                    <li class="w-100">
                                                        <div class="footer-contact-icon-one">
                                                            <i class="fa fa-phone" aria-hidden="true"></i>
                                                        </div>
                                                        <span><a href="tel:+918401019730" style="color:#fff !important;">+91 8401019730</a></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="footer-links-one">
                                            <div class="footer-contact-one">
                                                <ul class="address-last-child-ul-mob-tab">
                                                    <li>
                                                        <div class="footer-contact-icon-one">
                                                            <i class="fa fa-envelope" aria-hidden="true"></i>
                                                        </div>
                                                        <span>info@scholarsbox.in</span>
                                                    </li>
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
        </div>



















<!--copyright portion starts-->        
        <div class="footer-copyright-one">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-7">
                        <div class="copyright-text-one">
                            <span>Copyright &copy; {{date('Y')}} <a href="">ImpactBox Venture Private Limited.</a> All rights reserved.</span>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-5">
                        <div class="copyright-one float-right">
                            <div class="footer-social-media-one">
                                <ul>
                                    <?php
                                    $socialmedialinks = DB::table('social')->get();
                                    ?>
                                    @foreach($socialmedialinks as $val)
                                    @if($val->icon == 'twitter')
                                      <li class="{{strtolower($val->title)}}">
                                        <a href="{{$val->link}}"><i class="fa-brands fa-x-twitter"
                                                aria-hidden="true"></i></a>
                                    </li>
                                    @else
                                    <li class="{{strtolower($val->title)}}">
                                        <a href="{{$val->link}}"><i class="fa fa-{{$val->icon}}"
                                                aria-hidden="true"></i></a>
                                    </li>
                                    @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--Footer Start-->
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-8PK2QYQN7R"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-8PK2QYQN7R');
</script>