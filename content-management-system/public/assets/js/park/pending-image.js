function viewImg(e) {
    $(e).parent().find('.gallery-img').click();
}
$(document).ready(function () {
    getUserParkImages(park_id);


    lc_lightbox('.gallery-img', {
        wrap_class: 'lcl_fade_oc',
        gallery: true,
        thumb_attr: 'data-lcl-thumb',
        skin: 'minimal',
        radius: 0,
        padding: 0,
        border_w: 0,
        slideshow_time: 1000,
        download: false,
        skin: 'dark',
        show_title: false,
    });

    function getUserParkImages(park_id) {
        if (park_id) {
            $.ajax({
                url: userParkImgUrl,
                method: _method,
                data: { 'park_id': park_id, 'user_id': user_id },
                beforeSend: function () {
                    $("#load_images").html('');
                    $("#verify-unverify-loader").removeClass('d-none');
                },
                success: function (res) {
                    $(res.images).each(function (index, value) {
                        $('#select_labels').find('span').each(function (index, val) {
                            var id = $(this).attr('id');
                            var number_of_image = "(" + value[id] + ")";
                            $('#' + id).text(number_of_image);
                        });
                    });


                    $("#verify-unverify-loader").addClass('d-none');

                    $("#select_all_images").parent().parent().removeClass('d-none');
                    $("#load_images").html(res.data);
                    // checkVerifyAllImages();

                    const is_archived = res.is_archived;
                    if (is_archived) {
                        $("#archiveBtn").addClass('d-none');
                        $("#unarchiveBtn").removeClass('d-none');

                    } else {
                        $("#archiveBtn").removeClass('d-none');
                        $("#unarchiveBtn").addClass('d-none');
                    }

                    document.getElementById('verifyBtn').scrollIntoView();

                }
            })
        }

    }
    //click select all image
    $("#select_all_images").on("click", function () {
        var checked = $(this).is(':checked');
        $("#select_unverified_image").prop('checked', false);
        $("#select_verified_image").prop('checked', false);


        if (checked) {
            $('.image_box').addClass('box-shadow');
            $(".selected-img[select-img]").attr('select-img', true);

        } else {
            $('.image_box').removeClass('box-shadow');
            $(".selected-img[select-img]").attr('select-img', false);
        }
        selectedImg();
    });

    //click verfied checked button
    $("#select_verified_image").on("click", function () {
        deslectedAllImg();
        $("#select_unverified_image").prop('checked', false);
        $("#select_all_images").prop('checked', false);

        let checked = $(this).is(':checked');
        if (checked) {
            $('.parkimage_id[checked-mark=true]').parent().parent().addClass('box-shadow');
            $('.parkimage_id[checked-mark=true]').parent().parent().find(".selected-img[select-img]").attr('select-img', true);

        } else {
            $('.parkimage_id[checked-mark=true]').parent().parent().removeClass('box-shadow');
            $('.parkimage_id[checked-mark=true]').parent().parent().find(".selected-img[select-img]").attr('select-img', false);
        }
        selectedImg();

    })

    //click unverified button
    $("#select_unverified_image").on("click", function () {
        deslectedAllImg();
        let checked = $(this).is(':checked');
        $("#select_verified_image").prop('checked', false);
        $("#select_all_images").prop('checked', false);

        if (checked) {
            $('.parkimage_id[checked-mark=false]').parent().parent().addClass('box-shadow');
            $('.parkimage_id[checked-mark=false]').parent().parent().find(".selected-img[select-img]").attr('select-img', true);

        } else {
            $('.parkimage_id[checked-mark=false]').parent().parent().removeClass('box-shadow');
            $('.parkimage_id[checked-mark=true]').parent().parent().find(".selected-img[select-img]").attr('select-img', false);
        }
        selectedImg();

    })

    // $("#load_images").on('click', '.image_box', function () {

    //     var check_mark = $(this).find('.check-mark');

    //     var check_mark = $(this).find('.check');
    //     var is_selected_img = $(this).hasClass('box-shadow');
    //     if(is_selected_img){
    //         $(this).removeClass('box-shadow');
    //     }else{
    //         $(this).addClass('box-shadow');
    //     }

    //     // if (check_mark.hasClass('d-none')) {
    //     //     check_mark.removeClass('d-none');
    //     //     check_mark.find('.parkimage_id').attr('checked-mark', true);
    //     // } else {
    //     //     check_mark.addClass('d-none');
    //     //     check_mark.find('.parkimage_id').removeAttr('checked-mark');
    //     // }
    //     // checkVerifyAllImages();
    // })

});

function selectImg(id, e) {
    var is_selected_img = $(e).hasClass('box-shadow');
    if (is_selected_img) {
        $(e).removeClass('box-shadow');
        $(e).find('.selected-img[select-img]').attr('select-img', false);

    } else {
        $(e).addClass('box-shadow');
        $(e).find('.selected-img[select-img]').attr('select-img', true);
    }
    selectedImg();
}



//click verify btn
$("#verifyBtn").click(function () {

    var id = getSelectedImgId();
    $.confirm({
        title: 'Verify images',
        content: 'Do you want to verify selected images?',
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function () {
                    verifyUnverifyImg(id, 'verify');

                }
            },
            No: function () {
                null;
            },
        }
    });
});

//click unverify Btn
$("#unverifyBtn").click(function () {

    var id = getSelectedImgId();
    $.confirm({
        title: 'Unverify images',
        content: 'Do you want to unverify selected images?',
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function () {
                    verifyUnverifyImg(id, 'unverify');
                }
            },
            No: function () {
                null;
            },
        }
    });
});

//click archive btn
$("#archiveBtn").click(function () {
    let ids = [];
    $(".selected-img").each(function () {
        ids.push($(this).attr('value'));
    });
    $.confirm({
        title: 'Archive images',
        content: 'Do you want to archive selected images?',
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function () {
                    verifyUnverifyImg(ids, 'archive');
                }
            },
            No: function () {
                null;
            },
        }
    });
})

//click to unarchive Btn
$('#unarchiveBtn').click(function () {
    const data = {
        'park_id': park_id,
        'user_id': user_id
    }
    unarchiveImage(unarchive_image_url, data);
});


//click to verify unverify image
function verifyUnverifyImg(ids, status) {
    $.ajax({
        url: verify_unverify_url,
        method: 'post',
        data: { 'status': status, 'id': ids, 'park_id': park_id, 'user_id': user_id },
        beforeSend: function () {
            $("#verify-unverify-loader").removeClass('d-none');
        },
        success: function (res) {
            $("#verify-unverify-loader").addClass('d-none');


            if (res.status == 'archived') {
                // location.href = indexUrl;
                // window.location =
                window.history.go(-1);
            } else {
                location.reload();
            }

        }
    })
}



function unarchiveImage(unarchive_image_url, data) {
    $.confirm({
        title: 'Unarchive',
        content: 'Do you want to unarchive?',
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function () {
                    $.post(unarchive_image_url, data, function (res) {
                        location.reload();
                    });
                }
            },
            No: function () {

                // $(e).removeAttr('data-bs-original-title');
                // $(e).tooltip('disable');


            },
        }
    });
}


function getSelectedImgId() {
    var checked_img_ids = [];
    $(".selected-img[select-img=true]").each(function () {
        checked_img_ids.push($(this).attr('value'));
    });
    return checked_img_ids;
}

function selectedImg() {
    total_img = $(".image_box").length;
    total_verified_img = $(".parkimage_id[checked-mark=true]").length;
    total_unverified_img = $(".parkimage_id[checked-mark=false]").length;

    total_selected_img = $(".box-shadow").length;
    total_selected_verified_img = $(".box-shadow").find(".parkimage_id[checked-mark=true]").length;
    total_selected_unverified_img = $(".box-shadow").find(".parkimage_id[checked-mark=false]").length;

    (total_img == total_selected_img) ? $("#select_all_images").prop('checked', true) : $("#select_all_images").prop('checked', false);
    (total_selected_img > 0) ? $("#DeleteBtn").removeClass('d-none') : $("#DeleteBtn").addClass('d-none');
    $("#DeleteBtn >span").text(`(${total_selected_img})`);


    if (total_verified_img > 0) {

        ((total_verified_img == total_selected_verified_img) && total_selected_unverified_img == 0) ? $("#select_verified_image").prop('checked', true) : $("#select_verified_image").prop('checked', false);
    }
    if (total_unverified_img > 0) {
        ((total_unverified_img == total_selected_unverified_img) && total_selected_verified_img == 0) ? $("#select_unverified_image").prop('checked', true) : $('#select_unverified_image').prop('checked', false);

    }
    if (total_selected_unverified_img == 0 && total_selected_verified_img > 0) {
        $("#unverifyBtn").removeClass('d-none');
        $("#verifyBtn").addClass('d-none');

    } else {
        (total_selected_img > 0) ? $("#verifyBtn").removeClass('d-none') : $("#verifyBtn").addClass('d-none');
        $("#unverifyBtn").addClass('d-none');

    }

}

function deslectedAllImg() {
    $('.image_box').removeClass('box-shadow');
    $(".selected-img[select-img]").attr('select-img', false);
}


//click to delete button
$('#DeleteBtn').click(function () {
    $.confirm({
        title: 'Delete image',
        content: 'Are you sure?',
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function () {
                    // verifyUnverifyImg(ids, 'archive');
                    const data = {
                        'id': getSelectedImgId()
                    }
                    deleteImg(data);
                }
            },
            No: function () {
                null;
            },
        }
    });

    function deleteImg(data) {

        $.ajax({
            url: deleteUplodedImgUrl,
            method: 'delete',
            data: data,
            beforeSend: function () {
                $("#verify-unverify-loader").removeClass('d-none');

            },
            success: function (res) {
                location.reload();
                $("#verify-unverify-loader").addClass('d-none');


            }
        })
    }
});




