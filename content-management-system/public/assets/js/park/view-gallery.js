$(document).ready(function () {
    var lastScrollTop = 55;
    var xhr;
    //image card hover
    $(".content").on("mouseenter", "a", function () {
        bannerBtn = $(this).find('.galleryBannerBtn');
        bannerBtn.removeClass('d-none');

    }).on("mouseleave", "a", function () {
        bannerBtn.addClass('d-none');
    });


    //bannter btn hover end leave
    $(".content").on("mouseenter", ".btn", function () {
        a = $(this).parent().parent();
        a.prop('disabled', true);
        href = a.attr('href');
        a.removeAttr('href');

    }).on("mouseleave", ".btn", function () {
        a.prop('disabled', false);
        a.attr('href', href);


    });

    $(".content").on("click", ".btn", function () {
        var btn = $(this);
        var anchor = btn.parent().parent();
        var img_tmp_id = btn.attr('id');
        if (btn.hasClass('bannerBtn')) {
            setBanner(btn = btn, img_tmp_id = img_tmp_id, a = anchor);
        } else if (btn.hasClass('unsetBanner')) {
            unsetBanner(btn = btn, img_tmp_id = img_tmp_id, a = anchor);
        }
    })



    function setBanner(btn, img_tmp_id, a) {
        $.ajax({
            url: setunsetbannerUrl,
            method: 'post',
            data: {
                'park_id': park_id,
                'img_tmp_id': img_tmp_id,
                'type': 'set_banner',
            },
            beforeSend: function () {
                btn.prop('disabled', true);
            },
            success: function (res) {
                var unsetBanner = $("a").find('.unsetBanner');
                unsetBanner.removeClass('btn-danger unsetBanner');
                unsetBanner.addClass('btn-primary bannerBtn');
                unsetBanner.text('Set As Banner');
                $("a").removeClass('box-shadow');
                $("a").find('.check-mark').remove();
                btn.text("Unset Banner");
                a.addClass('box-shadow');
                a.append(
                    "<div class='check-mark'> <svg xmlns='http://www.w3.org/2000/svg' width='30' height='30' fill='#2fa224' class='bi bi-check-circle-fill' viewBox='0 0 16 16'> <path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path> </svg> </div>"
                );
                btn.prop('disabled', false);
                btn.addClass('btn-danger unsetBanner');
                btn.removeClass('btn-primary bannerBtn');
            }
        })
    }

    function unsetBanner(btn, img_tmp_id, a) {
        $.ajax({
            url: setunsetbannerUrl,
            method: 'post',
            data: {
                'park_id': park_id,
                'img_tmp_id': img_tmp_id,
                'type': 'unset_banner',
            },
            beforeSend: function () {
                btn.prop('disabled', true);
            },
            success: function (res) {
                a.removeClass('box-shadow');
                a.find('.check-mark').remove();
                btn.removeClass('btn-danger unsetBanner')
                btn.addClass('btn-primary bannerBtn')
                btn.text("Set As Banner");
                btn.prop('disabled', false);
            }
        })
    }

    //click select picker
    $(".selectpicker").on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        var type = $(this).val();
        offsetVal = 0;
        loadData(type);

        let text = $(this).find('option:selected').text();

        $("#card_title").text(text);

        // $(window).scrollTop(0);

    });

    //-------------scroll detect and paginate-----
    $(window).scroll(function (e) {
        var st = $(this).scrollTop();
        if (st > lastScrollTop) {

            lastScrollTop = st + 560;
            var type = $('.selectpicker').val();
            if (type == 'user') {
                loadData(type);
            } else {
                loadData();
            }

        }
        // loadData();

    })

    function loadData(type = null) {
        $.ajax({
            url: viewUrl,
            method: 'get',
            data: {
                'offset': offsetVal,
                'type': type
            },
            beforeSend: function () {
                if (offsetVal == 0) {
                    $(".content").html('');
                }
                $(".loader").removeClass('d-none');

            },
            success: function (res) {
                $(".loader").addClass('d-none');


                offsetVal = offsetVal + 9;
                $(".content").append(res.html);

            }
        })

    }
});
