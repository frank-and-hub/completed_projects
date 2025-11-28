<!--Back To Top Start-->
<div class="progress-wrap active-progress">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"
            style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919, 307.919; stroke-dashoffset: 273.171;">
        </path>
    </svg>
</div>
<!--Back To Top End-->

<!-- Jquery JS Link -->
<script src="{{asset('frontend/js/jquery.min.js')}}"></script>

<!-- Bootstrap JS Link -->
<script src="{{asset('frontend/js/bootstrap.min.js')}}"></script>
<script src="{{asset('frontend/js/popper.min.js')}}"></script>

<!-- Custom JS Link -->
<script src="{{asset('frontend/js/custom.js')}}"></script>

<!-- Slick Slider JS Link -->
<script src="{{asset('frontend/js/slick.min.js')}}"></script>
<!-- Text Typing Js Link -->
<script src="{{asset('frontend/js/typed.min.js')}}"></script>
<script src="{{asset('frontend/js/custom-typed.js')}}"></script>
<!-- Wow Animation JS -->
<script src="{{asset('frontend/js/wow.min.js')}}"></script>

<!-- Bg Moving Js -->
<script src="{{asset('frontend/js/bg-moving.js')}}"></script>

<!--Scroll Counter Js-->
<script src="{{asset('frontend/js/custom-scroll-count.js')}}"></script>

<!--Back To Top JS-->
<script src="{{asset('frontend/js/back-to-top.js')}}"></script>
<script src="{{asset('frontend/js/jquery.steps.js')}}"></script>
<script>
    $(function () {
        $("#wizard").steps({
            headerTag: "h4",
            bodyTag: "section",
            transitionEffect: "fade",
            enableAllSteps: true,
            transitionEffectSpeed: 500,
            onStepChanging: function (event, currentIndex, newIndex) {
                if (newIndex === 1) {
                    $('.steps ul').addClass('step-2');
                } else {
                    $('.steps ul').removeClass('step-2');
                }
                if (newIndex === 2) {
                    $('.steps ul').addClass('step-3');
                } else {
                    $('.steps ul').removeClass('step-3');
                }
                if (newIndex === 3) {
                    $('.steps ul').addClass('step-4');
                    $('.actions ul').addClass('step-last');
                } else {
                    $('.steps ul').removeClass('step-4');
                    $('.actions ul').removeClass('step-last');
                }
                return true;
            },
            labels: {
                finish: "Submit",
                next: "Next",
                previous: "Previous"
            }
        });
        $('.wizard > .steps li a').click(function () {
            $(this).parent().addClass('checked');
            $(this).parent().prevAll().addClass('checked');
            $(this).parent().nextAll().removeClass('checked');
        });
        $('.forward').click(function () {
            $("#wizard").steps('next');
        })
        $('.backward').click(function () {
            $("#wizard").steps('previous');
        })
        $('.checkbox-circle label').click(function () {
            $('.checkbox-circle label').removeClass('active');
            $(this).addClass('active');
        })
    })
</script>

<script>
    $(function () {
        $("#applywizard").steps({
            headerTag: "h4",
            bodyTag: "section",
            transitionEffect: "fade",
            enableAllSteps: true,
            transitionEffectSpeed: 500,
            onStepChanging: function (event, currentIndex, newIndex) {
                if (newIndex === 1) {
                    $('.steps ul').addClass('step-2');
                    
                } else {
                    $('.steps ul').removeClass('step-2');
                }
                if (newIndex === 2) {
                    $('.steps ul').addClass('step-3');
                } else {
                    $('.steps ul').removeClass('step-3');
                }
                if (newIndex === 3) {
                    $('.steps ul').addClass('step-4');
                } else {
                    $('.steps ul').removeClass('step-4');
                }
                if (newIndex === 4) {
                    $('.steps ul').addClass('step-5');
                    $('.actions ul').addClass('step-last');
                    
                } else {
                    $('.steps ul').removeClass('step-5');
                    $('.actions ul').removeClass('step-last');
                }
                return true;
            },
            labels: {
                finish: "Submit",
                next: "Next",
                previous: "Previous"
            }
        });
        $('.wizard > .steps li a').click(function () {
            $(this).parent().addClass('checked');
            $(this).parent().prevAll().addClass('checked');
            $(this).parent().nextAll().removeClass('checked');
        });
        $('.forward').click(function () {
            $("#wizard").steps('next');
        })
        $('.backward').click(function () {
            $("#wizard").steps('previous');
        })
        $('.checkbox-circle label').click(function () {
            $('.checkbox-circle label').removeClass('active');
            $(this).addClass('active');
        })
    })
</script>
<script>
    var elements = document.getElementsByTagName('aside');

    for (var i = 0; i < elements.length; i++) {
        new hcSticky(elements[i], {
            stickTo: elements[i].parentNode,
            top: 100,
            bottomEnd: 30
        });
    }

    document.querySelector('.readmore').addEventListener('click', function () {
        document.querySelector('.smalldesc').classList.toggle('expand');
    });

    $('.onclickexpand').click(function () {
        if($( ".smalldesc" ).hasClass( "expand" )){
        }else{
            $('.smalldesc').addClass('expand');
        }
    });

    
</script>
<script>
    var bodyEl = $(".side-list li a");
    $(window).on("scroll", function () {
        var scrollTop = $(this).scrollTop();
        $(".anchor").each(function () {

            var el = $(this),
                className = el.attr("id");
            if (el.offset().top > scrollTop) {

                bodyEl.removeClass(className);
            }
            if (el.offset().top < scrollTop) {
                bodyEl.addClass(className);
            }

        });
    });

    $('side-list li a').on('click', function (e) {
        e.preventDefault();
        var $href = $(this).attr('href');
        var $id = $('div').attr('id');
        $(this).addClass('active');
        //HERE I WANT TO SELECT THE DIV WHOSE "id" MATCHES THE "href" of the <a> clicked 
        $('div').id($href).addClass('active');
    });
</script>
<script>
    $('.signup-trigger').on('click', function (e) {
        setTimeout(function () {
            $('.signinform--disapear').hide(500);
            $('.signupform--disapear').show(500);
        }, 300);
    });
    $('.signin-trigger').on('click', function (e) {
        setTimeout(function () {
            $('.signupform--disapear').hide(500);
            $('.signinform--disapear').show(500);
        }, 300);
    });
</script>
<script>
/*******Home page slider script*********/

var $slider = $(".slideshow .slider"),
  maxItems = $(".item", $slider).length,
  dragging = false,
  tracking,
  rightTracking;

$sliderRight = $(".slideshow")
  .clone()
  .addClass("slideshow-right")
  .appendTo($(".split-slideshow"));

rightItems = $(".item", $sliderRight).toArray();
reverseItems = rightItems.reverse();
$(".slider", $sliderRight).html("");
for (i = 0; i < maxItems; i++) {
  $(reverseItems[i]).appendTo($(".slider", $sliderRight));
}

$slider.addClass("slideshow-left");
$(".slideshow-left")
  .slick({
    vertical: true,
    verticalSwiping: true,
    arrows: false,
    infinite: true,
    dots: true,
    autoplay: true,
    speed: 1000,
    cssEase: "cubic-bezier(0.7, 0, 0.3, 1)"
  })
  .on("beforeChange", function (event, slick, currentSlide, nextSlide) {
    if (
      currentSlide > nextSlide &&
      nextSlide == 0 &&
      currentSlide == maxItems - 1
    ) {
      $(".slideshow-right .slider").slick("slickGoTo", -1);
      $(".slideshow-text").slick("slickGoTo", maxItems);
    } else if (
      currentSlide < nextSlide &&
      currentSlide == 0 &&
      nextSlide == maxItems - 1
    ) {
      $(".slideshow-right .slider").slick("slickGoTo", maxItems);
      $(".slideshow-text").slick("slickGoTo", -1);
    } else {
      $(".slideshow-right .slider").slick(
        "slickGoTo",
        maxItems - 1 - nextSlide
      );
      $(".slideshow-text").slick("slickGoTo", nextSlide);
    }
  })
  .on("mousewheel", function (event) {
    event.preventDefault();
    if (event.deltaX > 0 || event.deltaY < 0) {
      $(this).slick("slickNext");
    } else if (event.deltaX < 0 || event.deltaY > 0) {
      $(this).slick("slickPrev");
    }
  })
  .on("mousedown touchstart", function () {
    dragging = true;
    tracking = $(".slick-track", $slider).css("transform");
    tracking = parseInt(tracking.split(",")[5]);
    rightTracking = $(".slideshow-right .slick-track").css("transform");
    rightTracking = parseInt(rightTracking.split(",")[5]);
  })
  .on("mousemove touchmove", function () {
    if (dragging) {
      newTracking = $(".slideshow-left .slick-track").css("transform");
      newTracking = parseInt(newTracking.split(",")[5]);
      diffTracking = newTracking - tracking;
      $(".slideshow-right .slick-track").css({
        transform:
          "matrix(1, 0, 0, 1, 0, " + (rightTracking - diffTracking) + ")"
      });
    }
  })
  .on("mouseleave touchend mouseup", function () {
    dragging = false;
  });

$(".slideshow-right .slider").slick({
  swipe: false,
  vertical: true,
  arrows: false,
  infinite: true,
  speed: 950,
  cssEase: "cubic-bezier(0.7, 0, 0.3, 1)",
  initialSlide: maxItems - 1
});
$(".slideshow-text").slick({
  swipe: false,
  vertical: true,
  arrows: false,
  infinite: true,
  speed: 900,
  cssEase: "cubic-bezier(0.7, 0, 0.3, 1)"
});
</script>