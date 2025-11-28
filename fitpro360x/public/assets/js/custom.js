
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


function updateStatus(id, model, url, _this) {
    const status = $(_this).prop('checked') ? 1 : 0;

    $.ajax({
        url: url,
        method: 'POST',
        data: {
            id: id,
            model: model,
            status: status,
            _token: csrf
        },
        success: function(response) {
            if (response.status) {
                const actionText = status ? 'Activated' : 'Deactivated';
                toastr.success(`${actionText} Successfully.`);
            } else {
                toastr.error(response.message || 'Something went wrong.', 'Error!');
                $(_this).prop('checked', !status); // Revert if failed
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred while updating the status.', 'Error!');
            $(_this).prop('checked', !status); // Revert on error
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


