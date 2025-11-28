    <!-- Loder Start -->
   
    <div class="loader-box-two">
        <div class="dank-ass-loader">
            <div class="load-shap">
                <div class="arrow-two up outer outer-18"></div>
                <div class="arrow-two down outer outer-17"></div>
                <div class="arrow-two up outer outer-16"></div>
                <div class="arrow-two down outer outer-15"></div>
                <div class="arrow-two up outer outer-14"></div>
            </div>
            <div class="load-shap">
                <div class="arrow-two up outer outer-1"></div>
                <div class="arrow-two down outer outer-2"></div>
                <div class="arrow-two up inner inner-6"></div>
                <div class="arrow-two down inner inner-5"></div>
                <div class="arrow-two up inner inner-4"></div>
                <div class="arrow-two down outer outer-13"></div>
                <div class="arrow-two up outer outer-12"></div>
            </div>
            <div class="load-shap">
                <div class="arrow-two down outer outer-3"></div>
                <div class="arrow-two up outer outer-4"></div>
                <div class="arrow-two down inner inner-1"></div>
                <div class="arrow-two up inner inner-2"></div>
                <div class="arrow-two down inner inner-3"></div>
                <div class="arrow-two up outer outer-11"></div>
                <div class="arrow-two down outer outer-10"></div>
            </div>
            <div class="load-shap">
                <div class="arrow-two down outer outer-5"></div>
                <div class="arrow-two up outer outer-6"></div>
                <div class="arrow-two down outer outer-7"></div>
                <div class="arrow-two up outer outer-8"></div>
                <div class="arrow-two down outer outer-9"></div>
            </div>
            <p class="loader__label-one">Loading...</p>
        </div>
    </div>
    <!-- Loder End -->
    

    <!-- Header Start -->
    <header class="site-header-one">
        <!-- Top start -->
        <!-- <div class="header-temporary-information header-temp-mobile-tab-dis-none"> -->
        <!-- <div class="container header-temporary-beta-info"> -->
        <!--  <p>*This website is currently in beta version. There may still be some undiscovered issues or features that need improvement.</p> -->
        <!-- </div> -->
        <!-- </div> -->
        <div class="header-top-one">
            
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="top-contact-one">
                            <div class="top-mail">
                                <div class="top-mail-icon-one">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                </div>
                                <div class="top-mail-content-one">
                                    <a href="mailto:info@scholarsbox.in" title="Email Now">
                                        <p>info@scholarsbox.in</p>
                                    </a>
                                </div>
                            </div>
                            <div class="top-call-one">
                                <div class="top-call-icon-one">
                                    <i class="fa fa-phone" aria-hidden="true"></i>
                                </div>
                                <div class="top-call-content-one">
                                    <a href="tel:+918401019730" title="Call Now">
                                        <p>+91 8401019730</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 cstm-align-right-col">
                        <div class="main-navigation-one">
                            
                            <ul class="menu">
                                
                                <li class="sub-items-one cstm-header-myacc-icon">
                                    <a href="javascript:void(0);" title="My Account">
                                        <img src="{{asset('images/profile-user-100.png')}}" alt="Logo" class="cstm-header-myacc-icon-img"></a>
                                    <ul class="sub-menu-one">
                                       
                                        @auth
                                         <li><a href="{{route('Student.dashboard')}}" title="Dashboard">Dashboard</a></li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                                <li> <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a></li>
                        @else
                                        <li><a href="{{route('Student.login')}}" title="Login">Login</a></li>
                                        @endif
                                    </ul>
                                </li>
                                
                            </ul>
                        </div>
                        <div class="top-right login-header-btn cstm-top-hdr-myacc-icon">
                            <div class="login-head-btn">
                                @auth
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                <a class="btn btn-sm cstm-pd-0" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                            @else
                                

                           
                                <a href="{{ route('Student.login') }}">Login </a>/ <a href="{{ route('Student.login') }}">Register</a>
                                @endauth
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Top End -->
        <!--Navbar Start  -->
        <div class="header-bottom-one">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-3 col-lg-2">
                        <!-- Sit Logo Start -->
                        <div class="site-branding-one">
                            <a href="{{route('homes')}}" title="Scholarsbox">
                                <img src="{{asset('images/Scholars Box-Logo-01-crop.png')}}" alt="Logo">
                            </a>
                        </div>
                        <!-- Sit Logo End -->
                    </div>
                    <div class="col-xl-9 col-lg-10">
                        <div class="header-menu-one">
                            <nav class="main-navigation-one">
                                <button class="toggle-button-one">
                                    <span></span>
                                    <span class="toggle-width-one"></span>
                                    <span></span>
                                </button>
                                <ul class="menu">
                                    <li class="">
                                        <a href="{{route('homes')}}" title="Home">Home</a>
                                    </li>
                                    
                                    <li class="">
                                        <a href="{{route('Student.scholarship.index')}}" title="Scholarships">Scholarships</a>
                                    </li>
                                    <li class="sub-items-one custom-display-desktop">
                                        <a href="javascript:void(0);" title="Get Involved">Get Involved</a>
                                        <ul class="sub-menu-one">
                                            <li><a href="{{route('get-involved')}}#get_inv_students" title="">Students</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_corporate" title="">Corporates</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_academic" title="">Academic Institutions</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_ngo" title="">NGO</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_government" title="">Government</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_recommend" title="">Recommend Aspirants</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_support" title="">Support Individual Aspirants</a></li>
                                             <li><a href="{{route('get-involved')}}#get_inv_work_with_us" title="">Work With Us</a></li>  
                                        </ul>
                                    </li>
                                    <li class="sub-items-one custom-display-mobtab">
                                        <a href="javascript:void(0);" title="Get Involved">Get Involved</a>
                                        <ul class="sub-menu-one">
                                            <li><a href="{{route('get-involved')}}#get_inv_students" title="">Students</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_corporate" title="">Corporate</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_academic" title="">Academic Institutions</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_ngo" title="">NGO</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_government" title="">Government</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_recommend" title="">Recommend Aspirants</a></li>
                                            <li><a href="{{route('get-involved')}}#get_inv_support" title="">Support Individual Aspirants</a></li>
                                            <!-- <li><a href="{{route('get-involved')}}#get_inv_work_with_us" title="">Work With Us</a></li> -->
                                        </ul>
                                    </li>
                                    {{--<li class="sub-items-one"> 
                                     <a href="javascript:void(0);" title="Resources">Ressdsources</a>
                                      <ul class="sub-menu-one">
                                            <!-- <li><a href="{{route('news-letter')}}" title="Newsletters">Newsletters</a></li> -->
                                        <li><a href="{{route('study-material')}}" title="Study Material">Study Material</a></li>
                                            <!-- <li><a href="calendar.php" title="">Updates</a></li> -->
                                        </ul>
                                    </li>--}}
                                    
                                    
                                    <li class="">
                                        <a href="{{route('user.about-us')}}" title="About Us">About Us</a>
                                    </li>
                                    <li class="sub-items-one custom-display-desktop">
                                        <a href="javascript:void(0);" title="Contact Us">Contact Us</a>
                                        <ul class="sub-menu-one">
                                            <li><a href="{{route('contact-us')}}" title="Get In Touch">Get In Touch</a></li>
                                            <li><a href="{{route('faq')}}" title="FAQ">FAQs</a></li>
                                        </ul>
                                    </li>
                                    <li class="sub-items-one custom-display-mobtab">
                                        <a href="javascript:void(0);" title="Contact">Contact Us</a>
                                        <ul class="sub-menu-one">
                                            <li><a href="{{route('contact-us')}}" title="Get In Touch">Get In Touch</a></li>
                                            <li><a href="{{route('faq')}}" title="FAQ">FAQs</a></li>
                                        </ul>
                                    </li>
                                    <li class="sub-items-one ustom-display-mobtab">
                                        <a href="javascript:void(0);" title="My Account">My Account</a>
                                        <ul class="sub-menu-one">
                                            @auth
                                            <li>
                                                <a href="{{route('Student.dashboard')}}" title="Dashboard">Dashboard</a>
                                            </li>
                                            <li>
                                                <a href="{{route('calendar')}}" title="Calendar">Calendar</a>
                                            </li>
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                            <li> 
                                                <a href="{{ route('logout') }}"
                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    {{ __('Logout') }}
                                                </a>
                                            </li>
                                            @else
                                            <li>
                                                <a href="{{route('Student.login')}}" title="Login">Login</a>
                                            </li>
                                            @endif
                                        </ul>
                                    </li>
                                </ul>
                            </nav>
                            <div class="search-box-one">
                                <div class="search-icon-one">
                                    <a href="#" title="Search"><i class="fa fa-search"
                                            aria-hidden="true"></i></a>
                                </div>
                                <div class="search-input-one">
                                    <div class="search-input-box-one">
                                        <form action="{{route('search-data')}}" method="get">
                                        @csrf
                                            <input type="text" name="search" class="form-input-one"
                                                placeholder="Search Here..." required>
                                            <button type="submit" class="sec-btn-one"><span><i class="fa fa-search"
                                                        aria-hidden="true"></i></span></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="black-shadow-one"></div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Navbar End  -->
    </header>
    <!-- Header End -->
