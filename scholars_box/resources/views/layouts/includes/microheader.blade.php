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
    <header class="site-header-one microsite-mainheader">
        
        
        <!--Navbar Start  -->
        <div class="header-bottom-one">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-3 col-lg-2">
                        <!-- Sit Logo Start -->
                        <div class="site-branding-one">
                            <a href="index.php" title="Scholarsbox">
                                <img src="{{asset($data->logo)}}" alt="Logo">
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
                                        <a href="#home" title="Home">Home</a>
                                    </li>
                                    <li class="">
                                        <a href="#about" title="About">About Us</a>
                                    </li>
                                    <li class="">
                                        <a href="#scholarship" title="Scholarship">Scholarship</a> 
                                    </li>

                                    <li class="">
                                        @if(isset(auth()->user()->id))
                                        <a href="{{route('Student.dashboard.redirect')}}" title="Login">Dashboard</a>

                                        @else
                                        <a href="#loginnew" title="Login">Login / Register</a>
                                        @endif
                                    </li>
                                   
                                </ul>
                            </nav>
                            <!-- <div class="search-box-one">
                                <div class="search-icon-one">
                                    <a href="javascript:void(0);" title="Search"><i class="fa fa-search"
                                            aria-hidden="true"></i></a>
                                </div>
                                <div class="search-input-one">
                                    <div class="search-input-box-one">
                                        <form>
                                            <input type="text" name="search" class="form-input-one"
                                                placeholder="Search Here..." required>
                                            <button type="submit" class="sec-btn-one"><span><i class="fa fa-search"
                                                        aria-hidden="true"></i></span></button>
                                        </form>
                                    </div>
                                </div>
                            </div> -->
                            <div class="black-shadow-one"></div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Navbar End  -->
    </header>
    <!-- Header End -->
