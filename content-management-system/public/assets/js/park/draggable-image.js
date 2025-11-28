window.notDragged = true;

$(document).ready(function () {
    $(".card-body #savedImage").sortable({
        revert: true
    })
    $(".jquery-uploader-card").draggable({
        cursor: 'move',

        // containment: "parent",
        connectToSortable: '#savedImage',
        revert: 'invalid',
        // stack:".jquery-uploader-preview-container",
        zIndex: 100,
        // stop: handleDragStop,
        classes: {
            "ui-draggable": "highlight"
        },

    });


    dragImage();

    $("#saveImgBtn").click(function () {
        // var self = $(this);
        notDragged = false;
        dragImage(true);
    });


    function createIndex() {
        var indexArr = [];
        var images = $(".card-body").find(".jquery-uploader-card");
        images.each(function (idx, element) {
            var indexVal = $(this).attr('image-id');
            if (indexVal == undefined) {
                indexArr.push('null');
            } else {
                indexArr.push(indexVal);
            }

        })
        return indexArr;

    }


    function dragImage(clickSaveBtn = false, oldIndexVal) {
        var indexArr = createIndex();
        
        if (notDragged) {
            indexArr = indexArr.sort();
        }
        
        $.ajax({
            url: draggableSortUrl,
            method: 'post',
            data: {
                'id': indexArr,
                'park_id': park_id,
                'notDragged':notDragged,
                'old_index_val': oldIndexVal,
            },
            beforeSend: function () {
                if (clickSaveBtn) {
                    $("#saveImgBtn").prop('disabled', true);
                }
                $("#image-loader").removeClass('d-none');
                $(".loader").parent().parent().css("opacity", "0.6")
            },
            success: function (res) {
                if (clickSaveBtn) {
                    // window.location.reload();
                    window.location.href = storeUrl;
                } else {

                    $("#image-loader").addClass('d-none');
                    $(".loader").parent().parent().css("opacity", "1")
                }



            }
        })
    }

})
