$(document).ready(() => {

    getUserParkImages(park_id);
    parkName();


    // select all unverified images
    $("#select_all_images").on("click", function () {
        var checked = $(this).is(':checked');

        if (checked) {
            $('.check-mark').removeClass('d-none');
            $('.parkimage_id').attr('checked-mark', true);
        } else {
            $('.check-mark').addClass('d-none');
            $('.parkimage_id').removeAttr('checked-mark');

        }

        checkVerifyAllImages();


    })

    //click image
    $("#load_images").on('click', '.image_box', function () {

        var check_mark = $(this).find('.check-mark');

        var check_mark = $(this).find('.check');
        var is_selected_img = $(this).hasClass('box-shadow');
        if(is_selected_img){
            $(this).removeClass('box-shadow');
        }else{
            $(this).addClass('box-shadow');
        }

        // if (check_mark.hasClass('d-none')) {
        //     check_mark.removeClass('d-none');
        //     check_mark.find('.parkimage_id').attr('checked-mark', true);
        // } else {
        //     check_mark.addClass('d-none');
        //     check_mark.find('.parkimage_id').removeAttr('checked-mark');
        // }
        // checkVerifyAllImages();
    })



    //click verify btn
    $("#verifyBtn").click(function () {
        park_id = $('.selectpicker').val();
        var checked_img_ids = [];
        // var park_ids =[];
        $(".parkimage_id[checked-mark=true]").each(function () {
            checked_img_ids.push($(this).attr('value'));
            // park_ids.push($(this).attr('park_id'));
        })
        $.confirm({
            title: 'Verifying Image(s)',
            content: 'Do you want to verify selected image(s) ?',
            buttons: {
                Yes: {
                    btnClass: 'btn btn-success',
                    action: function () {

                        verifyImages(checked_img_ids, park_id);
                    }
                },
                No: function () {
                    null;
                },
            }
        });


    });

    function verifyImages(ids = null, park_id) {
        $.ajax({
            url: verifyImgUrl,
            method: 'post',
            data: { 'id': ids, 'user_id': user_id, 'park_id': park_id },
            success: function (res) {
                location.reload();

            }
        })
    }

    function getUserParkImages(park_id) {
        if (park_id) {
            $.ajax({
                url: userParkImgUrl,
                method: _method,
                data: { 'park_id': park_id, 'user_id': user_id },
                beforeSend: function () {
                    $("#load_images").html('');
                    $(".loader").removeClass('d-none');
                },
                success: function (res) {
                    $(".loader").addClass('d-none');

                    $("#select_all_images").parent().parent().removeClass('d-none');
                    $("#load_images").html(res.data);
                    checkVerifyAllImages();

                    document.getElementById('verifyBtn').scrollIntoView();

                }
            })
        }

    }


    //selectpicker click
    $(".selectpicker").on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        var park_id = $(this).val();
        getUserParkImages(park_id);
        parkName();

    });
    function checkVerifyAllImages() {
        total_check_mark = $(".check-mark").length;
        total_checked_mark = $(".check-mark").not('.d-none').length;
        (total_check_mark == total_checked_mark) ? $("#select_all_images").prop('checked', true) : $("#select_all_images").prop('checked', false);

        (total_checked_mark > 0) ? $("#verifyBtn").prop('disabled', false) : $("#verifyBtn").prop('disabled', true);
    }

    function parkName() {
        var text = $('.selectpicker').find("option:selected").text();
        text = text.trim();
        $("#parkName").text(text);

    }
})
