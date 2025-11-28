$(document).ready(function () {
    $(".sub-box-selected").tooltip();

    $(".collapse-card").click(function () {
        var accordionBtn = $(".collapse-card").not(this).find(".accordion-button");
        accordionBtn.addClass('collapsed');
        accordionBtn.attr('aria-expanded', false);
        accordionBtn.parent().parent().find('.accordion-collapse').removeClass('show');

        $(".delete-icon").tooltip();
    });

    Reviews(park_id);
});


var more_data=1;


function deleteReview(e) {
    var user_id = $(e).attr('user-id');
    var park_id = $(e).attr('park-id');
    $.confirm({
        title: 'Delete review',
        content: "Are you sure you want?",
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function (e) {
                    Reviews(park_id, user_id);
                }
            },
            No: function () {
            },
        }
    });
}

function Reviews(park_id, user_id = null) {
    $.ajax({
        url: delete_user_review_url,
        method: 'post',
        data: { 'park_id': park_id, 'user_id': user_id },
        beforeSend: function () {
            $("#review-loader").removeClass('d-none');
            $("#reviews").html('');
            $("#avg-rating").html('');

        },
        success: function (res) {
            $("#avg-rating").html(res.average_rating_html);
            $("#total-reviews").text("Reviews ("+res.total_rating+")");
            $("#review-loader").addClass('d-none');

            $("#reviews").html(res.html);
            $("#reviews").scrollTop(200);
            $('#reviews').find('.delete-icon').tooltip();

        }
    })
}

var lastScrollTop = 1200;
$(window).scroll(function (e) {
    var st = $(this).scrollTop();
    if (st > lastScrollTop) {

        lastScrollTop = st + 560;
        if (more_data > 0) {
            loadMoreData();

        }

    }


})

    ;

function loadMoreData() {
    $.ajax({
        url: load_more_url,
        method: 'post',
        data: { 'park_id': park_id, 'offset': offsetVal },
        beforeSend: function () {
            $("#review-loader").removeClass('d-none');
            // $("#reviews").html('');
        },
        success: function (res) {
            console.info("data:", res);
            offsetVal = offsetVal + 10;
            more_data = res.more_data;
            $("#reviews").append(res.html);
            $('#reviews').find('.delete-icon').tooltip();
            $("#review-loader").addClass('d-none');
        }
    })
}
