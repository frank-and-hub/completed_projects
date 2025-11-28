
$(window).scroll(function () {
    if ($(window).scrollTop() >= 40) {
        $('.top-header').addClass('fixedheader');
    }
    else {
        $('.top-header').removeClass('fixedheader');
    }
});


$(".slidetoggle").click(function () {
    $(".innerbody").toggleClass("menu-collapse");
});
$(".closemenu-btn").click(function () {
    $(".innerbody").removeClass("menu-collapse");
});


$('.menubar > li > a').click(function () {
    $(".menubar > li > a.active").removeClass('active');
    $(this).toggleClass('active');
});


function updateStatus(id, model, url, checkbox) {
    let status = checkbox.checked ? 1 : 0;

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id: id,
            model: model,
            status: status,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status === 'success') {
                toastr.clear(); // ✅ remove old toasts
                if (status === 1) {
                    toastr.success("Activated Successfully.");
                } else {
                    toastr.success("Deactivated Successfully.");
                }
            } else {
                toastr.error(response.message || "Something went wrong.");
            }
        },
        error: function () {
            toastr.error("Something went wrong.");
        }
    });
}





// (function ($) {
//     $(function () {


//         $('.slider').slick({
//             dots: true,
//             prevArrow: '<a class="slick-prev slick-arrow" href="#" style=""><div class="icon icon--ei-arrow-left"><svg class="icon__cnt"><use xlink:href="#ei-arrow-left-icon"></use></svg></div></a>',
//             nextArrow: '<a class="slick-next slick-arrow" href="#" style=""><div class="icon icon--ei-arrow-right"><svg class="icon__cnt"><use xlink:href="#ei-arrow-right-icon"></use></svg></div></a>',
//             customPaging: function (slick, index) {
//                 var targetImage = slick.$slides.eq(index).find('img').attr('src');
//                 return '<img src=" ' + targetImage + ' "/>';
//             }
//         });


//     });
// })(jQuery);

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' &&
                event.target.tagName !== 'TEXTAREA' &&
                event.target.type !== 'submit' &&
                event.target.type !== 'button') {
                event.preventDefault();
            }
        });
    });
});


