$(document).ready(function () {
    //select all
    $("#select_verify_images").on("click", function () {
        var checked = $(this).is(':checked');

        if (checked) {
            $("#verified_images").find('.check').removeClass('d-none');
            $('.checked_parkimage_id').attr('checked-mark', true);
        } else {
            $("#verified_images").find('.check').addClass('d-none');
            $('.checked_parkimage_id').removeAttr('checked-mark');

        }
        checkedImage();
    });

    //click image
    $(".image_box").click(function () {
        var check_mark = $(this).find('.check');
        var is_selected_img = $(this).hasClass('box-shadow');
        if(is_selected_img){
            $(this).removeClass('box-shadow');
        }else{
            $(this).addClass('box-shadow');
        }



        // if (check_mark.hasClass('d-none')) {
        //     check_mark.removeClass('d-none');
        //     check_mark.find('.checked_parkimage_id').attr('checked-mark', true);
        // } else {
        //     check_mark.addClass('d-none');
        //     check_mark.find('.checked_parkimage_id').removeAttr('checked-mark');
        // }

        // checkedImage();
    });


    $("#unverifyBtn").click(function () {
        var checked_img_ids = [];

        $(".checked_parkimage_id[checked-mark=true]").each(function () {
            checked_img_ids.push($(this).attr('value'));
        });

        $.confirm({
            title: 'Unverifying Image(s)',
            content: 'Do you want to unverify selected image(s) ?',
            buttons: {
                Yes: {
                    btnClass: 'btn btn-success',
                    action: function () {
                        unverifyImages(checked_img_ids);
                    }
                },
                No: function () {
                    null;
                },
            }
        });
    });



    function unverifyImages(ids) {
        $.ajax({
            url: unverifyImgUrl,
            method: 'post',
            data: { 'id': ids },
            success: function (res) {
                location.reload();
            }
        });
    }

    function checkedImage() {
        total_check_mark = $("#verified_images").find(".check").length;
        total_checked_mark = $("#verified_images").find(".check").not('.d-none').length;
        (total_check_mark == total_checked_mark) ? $("#select_verify_images").prop('checked', true) : $("#select_verify_images").prop('checked', false);
        (total_checked_mark > 0) ? $("#unverifyBtn").prop('disabled', false) : $("#unverifyBtn").prop('disabled', true);
    }



});

