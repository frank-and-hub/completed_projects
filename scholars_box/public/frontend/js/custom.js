/*=========
=========*/

/*=========
----- JS INDEX -----
1.Whole Script Strict Mode Syntax
2.Loader Js
3.WoW Animation Js
4.Sticky Header JS
5.Search Form JS
6.Partners Logo Slider JS
7.Team Slider JS
8.Testimonial-One JS
9.Toggle Menu Mobile JS
10.If Mobile Slider JS
11.Portfolio Two JS
12. My Work Box Slider JS
=========*/

$(document).ready(function($) {

	// Whole Script Strict Mode Syntax
	"use strict";

	// Loader JS Start
	$(window).ready(function(){
		$('.loader-box-one, .loader-box-two').fadeOut();
		$('body').removeClass('fixed');
   });
	// Loader JS End

	// Wow Animation JS Start
	new WOW().init(); 
	// Wow Animation JS Start

    // Sticky Header JS Start
	$(window).on( 'scroll', function(){
		if ($(window).scrollTop() >= 100) {
		  $('.site-header-one').addClass('sticky-header-one');
		 }
		 else {
		  $('.site-header-one').removeClass('sticky-header-one');
		 }
	});
	// Sticky Header JS Two
	$(window).on( 'scroll', function(){
		if ($(window).scrollTop() >= 100) {
		  $('.site-header-two').addClass('sticky-header-two');
		 }
		 else {
		  $('.site-header-two').removeClass('sticky-header-two');
		 }
	});
	// Sticky Header JS End

	// Search Form JS Start
	$(".search-icon-one a").on( 'click', function(){
		$(".search-input-one").fadeToggle(300);
		$(".search-input-one .black-shadow-one").fadeIn();
	});
	$(".search-input-one .black-shadow-one").on( 'click', function(){
		$(this).fadeOut();
		$(".search-input-one").fadeOut(300);
	});
	// Search Form JS Two
	$(".search-icon-two a").on( 'click', function(){
		$(".search-input-two").fadeToggle(300);
		$(".search-input-two .black-shadow-two").fadeIn();
	});
	$(".search-input-two .black-shadow-two").on( 'click', function(){
		$(this).fadeOut();
		$(".search-input-two").fadeOut(300);
	});
	// Search Form JS End

	// Partners Logo Slider Start JS
	$('.partners-slider').slick({
		infinite: true,
		slidesToShow: 4,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow-team prev-arrow"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>',
		nextArrow: '<button class="slick-arrow-team next-arrow"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',
		dots: false,
		arrows: false,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 4,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 3,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 3,
			  }
		  },
		  {
			breakpoint: 576,
			settings: {
				slidesToShow: 2,
			}
		  },
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  //Partners Logo Slider End JS


	  // microsite Slider Start JS
	$('.microsite-slider').slick({
		infinite: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow-micro prev-arrow"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>',
		nextArrow: '<button class="slick-arrow-micro next-arrow"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',
		dots: true,
		arrows: true,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
			}
		  },
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  //microsite Slider End JS


	  // Team Box Slider JS Start
	$('.team-slider').slick({
		infinite: true,
		slidesToShow: 4,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
		nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
		arrows: false,
		dots: true,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 3,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 3,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
			}
		  },
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  // Team Box Slider JS End

	// Testimonial-One Box Slider JS Start
	$('.testimonial-slider-one').slick({
		infinite: true,
		slidesToShow: 2,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
		nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
		arrows: false,
		dots: true,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  // Testimonial-One Box Slider JS End

	  // Testimonial-One Box Slider JS Start
	$('.testimonial-slider-two').slick({
		infinite: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
		nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
		arrows: false,
		dots: true,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 1,
			  }
		  },
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  // Testimonial-One Box Slider JS End

	  // Toggle Menu Mobile JS Start
		$(".toggle-button-one").on( 'click', function(){
			$(".main-navigation-one").toggleClass('toggle-menu-one');
			$(".header-menu-one .black-shadow-one").fadeToggle();
		});
		$(".main-navigation-one ul li a").on( 'click', function(){
			$(".main-navigation-one").removeClass('toggle-menu-one');
			$(".header-menu-one .black-shadow-one").fadeOut();
		});
		$(".main-navigation-one ul li.sub-items-one>a").on( 'click', function(){
			$(".main-navigation-one").addClass('toggle-menu-one');
			$(".header-menu-one .black-shadow-one").stop();
		});
		$(".header-menu-one .black-shadow-one").on( 'click', function(){
			$(this).fadeOut();
			$(".main-navigation-one").removeClass('toggle-menu-one');
		});
		// Toggle Menu Mobile JS Two
		$(".toggle-button-two").on( 'click', function(){
			$(".main-navigation-two").toggleClass('toggle-menu-two');
			$(".header-menu-two .black-shadow-two").fadeToggle();
		});
		$(".main-navigation-two ul li a").on( 'click', function(){
			$(".main-navigation-two").removeClass('toggle-menu-two');
			$(".header-menu-two .black-shadow-two").fadeOut();
		});
		$(".main-navigation-two ul li.sub-items-two>a").on( 'click', function(){
			$(".main-navigation-two").addClass('toggle-menu-two');
			$(".header-menu-two .black-shadow-two").stop();
		});
		$(".header-menu-two .black-shadow-two").on( 'click', function(){
			$(this).fadeOut();
			$(".main-navigation-two").removeClass('toggle-menu-two');
		});
		// Toggle Menu Mobile JS End

		// If Mobile Slider JS Start
		if ($(window).width() < 992) {

			// Submenu For Mobile JS Start
			$('body').on('click', '.main-navigation-one ul li.sub-items-one>a', function() {
				if (($(this).parent().hasClass('active-sub-menu-one'))) {
				$(this).parent().removeClass('active-sub-menu-one');
				$(this).parent().find('ul').slideUp();
				} else {
					$(this).parent().addClass('active-sub-menu-one');
					$(this).parent().find('ul').slideDown();
				}
			});
			// Submenu For Mobile JS Two
			$('body').on('click', '.main-navigation-two ul li.sub-items-two>a', function() {
				if (($(this).parent().hasClass('active-sub-menu-two'))) {
				$(this).parent().removeClass('active-sub-menu-two');
				$(this).parent().find('ul').slideUp();
				} else {
					$(this).parent().addClass('active-sub-menu-two');
					$(this).parent().find('ul').slideDown();
				}
			});
			// Submenu For Mobile JS End

			// Portfolio Slider For Mobile JS Start
			$('.portfolio-slider-one').slick({
			infinite: true,
			slidesToShow: 2,
			slidesToScroll: 1,
			prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
			nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
			dots: true,
			arrows: false,
			autoplay: false,
				autoplaySpeed: 2000,	
				responsive: [
				{
					breakpoint: 768,
					settings: {
						slidesToShow: 2,
					}
				},
				{
					breakpoint: 576,
					settings: {
						slidesToShow: 1,
					}
				}
			]	
			});
			// portfolio Slider For Mobile JS End

			// Blog Slider For Mobile JS Start
			$('.blog-slider-one').slick({
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 1,
				prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
				nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
				dots: true,
				arrows: false,
				autoplay: false,
					autoplaySpeed: 2000,	
					responsive: [
					{
						breakpoint: 768,
						settings: {
							slidesToShow: 1,
						}
					},
					{
						breakpoint: 576,
						settings: {
							slidesToShow: 1,
						}
					}
				]	
				});
			// Blog Slider For Mobile JS End

			// Pricing Slider For Mobile JS Start
			$('.pricing-slider-two').slick({
				infinite: true,
				slidesToShow: 2,
				slidesToScroll: 1,
				prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
				nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
				dots: true,
				arrows: false,
				autoplay: false,
					autoplaySpeed: 2000,	
					responsive: [
					{
						breakpoint: 768,
						settings: {
							slidesToShow: 1,
						}
					},
					{
						breakpoint: 576,
						settings: {
							slidesToShow: 1,
						}
					}
				]	
				});
			// Pricing Slider For Mobile JS End

		}
		// If Mobile Slider JS End

	// Portfolio Box Slider JS Start
	$('.portfolio-slider-two').slick({
		infinite: true,
		slidesToShow: 3,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
		nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
		arrows: false,
		dots: true,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
			}
		},
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  // Portfolio Box Slider JS End

	  // My Work Box Slider JS Start
	$('.my-work-slider').slick({
		infinite: true,
		slidesToShow: 3,
		slidesToScroll: 1,
		prevArrow: '<button class="slick-arrow prev-arrow color-arrow"><i class="fa fa-chevron-left"></i></button>',
		nextArrow: '<button class="slick-arrow next-arrow color-arrow"><i class="fa fa-chevron-right"></i></button>',
		arrows: false,
		dots: true,
		autoplay: true,
		autoplaySpeed: 2000,
		responsive: [
		  {
			  breakpoint: 1200,
			  settings: {
				  slidesToShow: 3,
			  }
		  },
		  {
			  breakpoint: 992,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			  breakpoint: 768,
			  settings: {
				  slidesToShow: 2,
			  }
		  },
		  {
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
			}
		},
		  {
			  breakpoint: 376,
			  settings: {
				  slidesToShow: 1,
			  }
		  }
	  ]
	  });
	  // My Work Box Slider JS End
   
});