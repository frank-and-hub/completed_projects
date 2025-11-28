$(document).ready(function () {
    $("#savedImage ,#imageuploader").on('mouseenter', '.jquery-uploader-preview-action', function () {
        $(this).parent().find('.img-btns-group').removeClass('d-none');
        $(this).css('opacity', 100);


    }).on('mouseleave', '.jquery-uploader-preview-action', function () {
        $(this).parent().find('.img-btns-group').addClass('d-none');
        $(this).css('opacity', 0);

    }).on('mouseenter', '.img-btns-group', function () {
        $(this).parent().find('.jquery-uploader-preview-action').css('opacity', 100);
        $(this).removeClass('d-none');
    }).on('mouseleave', '.img-btns-group', function () {
        $(this).parent().find('.jquery-uploader-preview-action').css('opacity', 0);

        $(this).addClass('d-none');
    })

    $(".card-body").on("click", ".bannerBtn", function () {

        var card = $(this).parent().parent().parent().parent();
        var btn = $(this);
        var img_tmp_id = card.attr('id');
        var id = card.attr('image-id');
        setBanner(img_tmp_id, card, btn, id);

    })
    $(".card-body").on("click", ".removeBannerBtn", function () {
        var card = $(this).parent().parent().parent().parent();
        var btn = $(this);
        var img_tmp_id = card.attr('id');
        var id = card.attr('image-id');
        unsetBanner(img_tmp_id, card, btn, id);
    })

    let ajaxConfig = {
        ajaxRequester: function (config, uploadFile, pCall, sCall, eCall) {

            var formData = new FormData()
            formData.append('park', uploadFile.file);
            formData.append('park_id', park_id);
            formData.append('img_tmp_id', uploadFile.id);
            saveImage(formData, pCall, sCall, uploadFile);
        }
    }

    $("#parkimage").uploader({
        multiple: true,
        ajaxConfig: ajaxConfig,
        autoUpload: true,
    })
        .on('upload-success', function (file, data) {

            $("#" + data.id).find('.bannerBtn').remove();
            // $("#" + data.id).find('.jquery-uploader-preview-action').append(
            //     '<button class="btn btn-primary btn-sm bannerBtn ">Set As Banner</button>'
            // );
            $("#" + data.id).find('.jquery-uploader-preview-action').after('<div class="img-btns-group d-none">\
            <div class="img-btns">\
                <i class="fa fa-trash-o text-white deleteFile temp-delete" aria-hidden="true"\></i>\
                <button class="btn btn-primary btn-sm bannerBtn ">Set As Banner</button>\
                    </div></div>');

        }).on('file-remove', function (file, data) {
            deleteImage(self, data.id);
        })

    function deleteImage(self, id) {
        $.ajax({
            url: remvoeUrl,
            method: 'post',
            data: {
                'id': id,
            },
            success: function (res) {
                if (res.status == '1') {
                    ToastAlert(msg = res.msg, type =
                        "Success", className =
                    "bg-success");
                }
            },
        })
    }

    function saveImage(formData, pCall, sCall, uploadFile) {
        $.ajax({
            url: save_image_url,
            method: 'post',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function (jqXHR) {

                var size = uploadFile.file.size;
                var img_id = uploadFile.id;
                var size_in_mb = (size) / Math.pow(10, 6);


                if (size_in_mb > 2) {
                    // $("#" + img_id).find('.jquery-uploader-preview-main').remove();
                    $("#" + img_id).append("<div class='invalid-preview-process'><div class='invalid-preview-cross-icon' title='Max size must be 2mb'><i class='fa fa-warning text-danger' aria-hidden='true'></i></div> </div>");

                    $("#" + img_id).removeAttr('id');
                    uploadFile.clean();
                    jqXHR.abort();
                }
            },
            success: function (res) {

                let progress = 0
                let interval = setInterval(() => {

                    progress += 10;
                    pCall(progress)
                    if (progress >= 100) {
                        clearInterval(interval)
                        const windowURL = window.URL || window.webkitURL;
                        sCall({
                            data: windowURL.createObjectURL(
                                uploadFile.file)
                        })

                    }
                }, 100)
            },
            error: function (textStatus) {
                var size = uploadFile.file.size;
                var img_id = uploadFile.id;
                // $("#" + img_id).find('.jquery-uploader-preview-progress').css('display', 'none');
                // $("#" + img_id).append("<div class='invalid-preview-process'><div class='image-reload'><i class='fa fa-refresh text-danger'></i> </div>");


            }

        })
    }

    // async function getFileFromUrl(url, name=null, defaultType = 'image/jpeg') {
    //     const response = await fetch(url);
    //     const data = await response.blob();
    //     return new File([data], data.name, {
    //         type: data.type || defaultType,
    //     });
    // }

    // $(".card-body").on("click", ".image-reload", function () {
    $(".card-body").on("click", ".invalid-preview-cross-icon", function () {


        var image = $(this).parent().parent().find(".files_img").attr('src');
        // let file = fetch(image).then(r => r.blob()).then(blobFile => new File([blobFile], "abc.png", { type: "image/png" }));

        // var id = $(this).parent().parent().attr('id');
        // const file = getFileFromUrl(image,+".jpeg");


    })

    function setBanner(img_tmp_id, card, btn, id = null) {

        $.ajax({
            url: setunsetbannerUrl,
            method: 'post',
            data: {
                'park_id': park_id,
                'img_tmp_id': img_tmp_id,
                'id': id,
                'type': 'set_banner',
            },
            beforeSend: function () {
                btn.prop('disabled', true);
            },
            success: function (res) {

                $(".card-body").find('.jquery-uploader-card').removeClass('box-shadow');
                $(".card-body").find('.check-mark').remove();
                var removeBannerBtn = $(".card-body").find(".removeBannerBtn");
                removeBannerBtn.removeClass('btn-danger');
                removeBannerBtn.addClass('btn-primary');
                removeBannerBtn.text('Set As Banner');
                removeBannerBtn.removeClass('removeBannerremoveBannerBtn');
                removeBannerBtn.addClass('bannerBtn');

                card.addClass('box-shadow');
                card.append(
                    "<div class='check-mark'> <svg xmlns='http://www.w3.org/2000/svg' width='30' height='30' fill='#2fa224' class='bi bi-check-circle-fill' viewBox='0 0 16 16'> <path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path> </svg> </div>"
                );


                btn.removeClass('btn-primary');
                btn.addClass('btn-danger');
                btn.text('Unset Banner');
                btn.addClass('removeBannerBtn');
                btn.removeClass('bannerBtn');

                $("#bannerImg").attr('src', res.banner_img);
                btn.prop('disabled', false);
            }
        })
    }

    function unsetBanner(img_tmp_id, card, btn, id = null) {
        $.ajax({
            url: setunsetbannerUrl,
            method: 'post',
            data: {
                'park_id': park_id,
                'img_tmp_id': img_tmp_id,
                'type': 'unset_banner',
                'id': id,
            },
            success: function (res) {

                card.find('.check-mark').remove();
                card.removeClass('box-shadow');
                btn.addClass('btn-primary');
                btn.removeClass('btn-danger');
                btn.text('Set As Banner');
                btn.removeClass('removeBannerBtn');
                btn.addClass('bannerBtn');

                $("#bannerImg").attr('src', default_img);
            }
        })
    }

    //delete saved image file
    $("#savedImage").on("click", ".deleteFile", function () {


        var parent = $(this).parent().parent().parent().parent();
        var park_img_id = $(this).attr('park_image_id');
        const user_id = $(this).attr('user_id');
        const park_id = $(this).attr('park_id');
        console.info(`user_id`, user_id);

        var self = $(this);

        $.confirm({
            title: 'Delete Image',
            content: 'Are you sure?',
            buttons: {
                confirm: {
                    btnClass: 'btn btn-success',
                    action: function () {
                        $.ajax({
                            url: remvoeUrl,
                            method: 'post',
                            data: {
                                'park_img_id': park_img_id,
                                'user_id': user_id,
                                'park_id': park_id,
                            },
                            beforeSend: function () {
                                $("#image-loader").removeClass('d-none');
                            },
                            success: function (res) {
                                parent.remove();

                                if (res.status == '1') {
                                    ToastAlert(msg = res.msg, type =
                                        "Success", className =
                                    "bg-success");
                                $("#image-loader").addClass('d-none');

                                }
                                if ($("#savedImage").children().length ==
                                    0) {
                                    $("#savedImage").parent().remove();
                                }
                                if (self.parent().parent().parent().find(
                                    'button').hasClass(
                                        'removeBannerBtn')) {
                                    $("#bannerImg").attr('src',
                                        default_img);
                                    // alert("working");

                                }
                            }

                        })
                    }
                },
                cancel: function () { },
            }
        });



    });
    $("input:checkbox[name=parkimage]").click(function () {
        var total_checked = $("input:checkbox[name=parkimage]:checked").length;
        var total_check_box = $("input:checkbox[name=parkimage]").length;
        if (total_checked == total_check_box) {
            $("#select_all_images").prop('checked', true);
        } else {
            $("#select_all_images").prop('checked', false);

        }

    });

    $("#select_all_images").click(function () {
        if ($(this).is(":checked")) {
            $("input:checkbox[name=parkimage]").each(function () {
                $(this).prop('checked', true);
            })
        }
        else {
            $("input:checkbox[name=parkimage]").each(function () {
                $(this).prop('checked', false);
            })
        }

    })


    //delete multiple image process

    $(".deleteImageBtn").click(function () {
        var self = $(this);

        var check_image_id = [];
        const user_id = [];
        $("input:checkbox[name=parkimage]:checked").each(function () {
            check_image_id.push($(this).attr('value'));
            const user_id_ = $(this).parent().parent().find("i[user_id]").attr('user_id');
            if (user_id_) {
                user_id.push(user_id_);
            }
        })


        $.confirm({
            title: 'Delete images',
            content: 'Are you sure?',
            buttons: {
                confirm: {
                    btnClass: 'btn btn-success',
                    action: function () {
                        deleteMultipleImage(check_image_id, user_id);
                        $("#select_all_images").prop('checked', false);

                        // location.reload();
                    }
                },
                cancel: function () {
                    null;
                },
            }
        });

    })




    //delete multiple image ajax
    function deleteMultipleImage(id, user_id) {

        $.ajax({
            url: deleteMultipleImgUrl,
            method: 'post',
            data: {
                'id': id,
                'user_id': user_id,
                'park_id': park_id
            },
            beforeSend: function () {
                $("#image-loader").removeClass('d-none');
                // $("#savedImage").html('');
            },
            success: function (res) {
                $("input:checkbox[name=parkimage]:checked").each(function () {
                    $(this).parent().parent().remove();
                })
                if (res.status == '1') {
                    ToastAlert(msg = res.msg, type = "Success", className = "bg-success");
                    $("#image-loader").removeClass('d-none');


                } else if (res.status == '0') {
                    ToastAlert(msg = res.msg, type = "Error", className = "bg-danger");

                }
                if ($("#savedImage").children().length == 0) {
                    $("#savedImage").parent().remove();

                }

                location.reload();


            },
            error: function (error) {
                console.error(error);
            }

        })
    }

    //load more event click
    $(".loadMoreBtn").click(function () {
        var offset = $(this).attr('offset');
        var btn = $(this);
        loadMoreData(btn, offset);
    })

    function loadMoreData(btn, offset, select = null) {
        $.ajax({
            url: loadMoreImageUrl,
            method: 'post',
            data: {
                'park_id': park_id,
                'offset': offset,
                'select': select
            },
            beforeSend: function () {
                btn.prop('disabled', true);
            },
            success: function (res) {
                offset = parseInt(offset) + 200;
                btn.attr('offset', offset);
                $("#savedImage").append(res.html);
                console.info('data', res);
                btn.prop('disabled', false);
                if (res.more_data == 0) {
                    btn.addClass('d-none');
                }
            }
        })
    }




    //tooltip
    $("[rel=tooltip]").tooltip({
        placement: 'bottom'
    })
    // $(".card-body").find(".invalid-preview-cross-icon").tooltip({

    // });
    $(".card-body").on('load', ".invalid-preview-cross-icon", function () {
        $(this).tooltip({});
    })
    $(".card-body").on("mouseenter", ".invalid-preview-cross-icon", function () {
        $(this).tooltip({});
    })



});
function filterImage(e) {
    var value = $(e).val();
    let type = $(e).find('option:selected').attr('type');
    let txt = $(e).find('option:selected').text().trim();
    if (type != undefined && type == 'user') {
        txt = `<i class="bx bxs-user"></i> ${txt}`;
    }

    $('#image_group').html(`${txt}`);
    if (value == 'all') {
        getFilterImageData();
    }

    var id = isNaN(value) ? null : value;
    if (id == null) {
        type = value;
    }

    getFilterImageData(type, id);
}

function getFilterImageData(type = null, id = null,) {
    $.ajax({
        url: filterImgUrl,
        method: 'post',
        data: { 'type': type, 'id': id },
        beforeSend: function () {
            $("#image-loader").removeClass('d-none');
            $("#savedImage").html('');
        },
        success: function (res) {
            $("#image-loader").addClass('d-none');
            $("#savedImage").html(res.html);
            oldIndexVal = res.oldIndexVal[0];
            $("#total_img").text(res.total_image);

        }
    });
}

