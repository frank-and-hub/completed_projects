$(document).ready(function () {
    $("#VerifyBtn").click(function () {
        $.confirm({
            title: 'Verify',
            content: "Are you sure you want to verify this review?",
            buttons: {
                Yes: {
                    btnClass: 'btn btn-success',
                    action: function (e) {
                        verifyReview();
                    }
                },
                No: function () {
                    null;
                },
            }
        });
    });

    function verifyReview() {
        $.ajax({
            url: verify_review_url,
            method: 'post',
            data: { 'park_id': park_id, 'user_id': user_id, 'status': 'verify' },
            beforeSend: function () {
                $("#loader").removeClass('d-none');

            },
            success: function (res) {
                $("#loader").addClass('d-none');
                location.href = pending_reviews_url;
            }
        })
    }

});
