window.filter_val = null;

$(document).ready(function () {
    function dbTble(filterVal = filter_val) {
        const tableId = "#location-table";

        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().clear().destroy();
        }

        db_table = $(tableId).DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 10,
            ajax: {
                url: uRL,
                data: {
                    filterVal: filterVal,
                },
                beforeSend: function () {
                    showLoader();
                },
            },
            columns: [
                {
                    name: "city",
                    data: "city",
                    width: "30%",
                },
                // {
                //     name: 'park_count',
                //     data: 'park_count',
                //     width: '10%',
                //     orderable: false
                // },
                {
                    name: "state",
                    data: "state",
                    width: "25%",
                },
                {
                    name: "country",
                    data: "country",
                    width: "25%",
                },
                {
                    name: "action",
                    data: "action",
                    width: "10%",
                    orderable: false,
                },
            ],
            order: [0, "asc"],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader();
            },
        });
    }

    dbTble();
    deleteDbTableData(
        "#location-table",
        (title = "Delete park"),
        (content = "Are you sure?")
    );
    changeStatus("#location-table");

    $("#ResetAllSelectedBtn").click(function () {
        // $("#dorpDownFilter").selectpicker("deselectAll");
       $('#dorpDownFilter').val('').trigger('change').trigger('refresh');
        // $("#dorpDownFilter").reset();
        filter_val = [];
        refreshDtbl();
    });

    $("#ResetAllSelectedBtn").click();

    let dropdownValues = [];

    $("#ApplyBtn").click(function () {
        filter_val = dropdownValues;
        refreshDtbl();
    });

    $("#dorpDownFilter").on("change", function () {
        const values = $(this).val();
        dropdownValues = values;
        // if (values.length > 0) {
        if (values) {
            filter_val = dropdownValues;
            $("#ApplyBtn,#ResetAllSelectedBtn").prop("disabled", false);
        } else {
            $("#ApplyBtn,#ResetAllSelectedBtn").prop("disabled", true);
            filter_val = [];
            refreshDtbl();
        }
    });

    // $('#dorpDownFilter').change();
    //  $('#dorpDownFilter').selectpicker('refresh');
    // $('#ApplyBtn').click();

    function refreshDtbl() {
        dbTble(filter_val);
    }

    function changeStatus(selector) {
        $(selector).on("click", "input[type=checkbox]", function () {
            var id = null,
                is_allowed = false;
            id = $(this).attr("id");

            var status, msg;
            var status_tooltip = $(this).parent();
            var Url = $(this).attr("link");
            is_allowed = $(this).attr("is_allowed");
            var self = $(this);

            if ($(this).is(":checked")) {
                status = 1;
                status_tooltip.attr("data-bs-original-title", "Active");
                status_tooltip.tooltip("show");
            } else {
                status = 0;
                status_tooltip.attr("data-bs-original-title", "Inactive");
                status_tooltip.tooltip("show");
            }
            if (!is_allowed) {
                self.prop("checked", false);
                ToastAlert(
                    `Add container page information to activate this location.`,
                    "Information",
                    (className = "bg-info")
                );
            } else {
                $.confirm({
                    title: "Status",
                    content: "Do you want to change status?",
                    buttons: {
                        Yes: {
                            btnClass: "btn btn-success",
                            action: function (e) {
                                $.ajax({
                                    url: Url,
                                    method: "get",
                                    data: { status: status, current_id: id },
                                    success: function (res) {
                                        ToastAlert(
                                            res.msg,
                                            "Success",
                                            (className = "bg-success")
                                        );
                                        status_tooltip.tooltip("hide");
                                    },
                                });
                            },
                        },
                        No: function () {
                            if (status == 1) {
                                self.prop("checked", false);
                                status_tooltip.attr(
                                    "data-bs-original-title",
                                    "Inactive"
                                );
                            } else {
                                self.prop("checked", true);
                                status_tooltip.attr(
                                    "data-bs-original-title",
                                    "Active"
                                );
                            }
                        },
                    },
                });
            }
        });
    }
});

var fileThumbnailInput = document.querySelector(
        ".account-file-input-thumbnail"
    ),
    resetFileThumbnailInput = document.querySelector(
        ".account-image-reset-thumbnail"
    ),
    accountUserThumbnailImage = document.getElementById(
        "uploadedAvatarThumbnail"
    ),
    resetThumbnailImage;

var fileBannerInput = document.querySelector(".account-file-input-banner"),
    resetFileBannerInput = document.querySelector(
        ".account-image-reset-banner"
    ),
    accountUserBannerImage = document.getElementById("uploadedAvatarBanner"),
    resetBannerImage;

document.addEventListener("DOMContentLoaded", function (e) {
    (function () {
        if (accountUserThumbnailImage) {
            resetThumbnailImage = accountUserThumbnailImage.src;
            fileThumbnailInput.onchange = () => {
                if (fileThumbnailInput.files[0]) {
                    accountUserThumbnailImage.src = window.URL.createObjectURL(
                        fileThumbnailInput.files[0]
                    );
                }
            };
        }
        if (accountUserBannerImage) {
            resetBannerImage = accountUserBannerImage.src;
            fileBannerInput.onchange = () => {
                if (fileBannerInput.files[0]) {
                    accountUserBannerImage.src = window.URL.createObjectURL(
                        fileBannerInput.files[0]
                    );
                }
            };
        }
    })();
});

function rstbnr(e) {
    var url = $(e).attr("link");
    var id = $(e).attr("id");
    fileBannerInput.value = "";
    accountUserBannerImage.src = resetBannerImage;
    var default_img = $(e).attr("default-img-url");

    if (id != "") {
        deleteBannerImage(url, default_img);
    }
}

function deleteBannerImage(uRL, default_img) {
    $.ajax({
        url: uRL,
        success: function (res) {
            console.info(res.status);
            accountUserBannerImage.src = default_img;
        },
    });
}

function rst(e) {
    var url = $(e).attr("link");
    var id = $(e).attr("id");
    fileThumbnailInput.value = "";
    accountUserThumbnailImage.src = resetThumbnailImage;
    var default_img = $(e).attr("default-img-url");

    if (id != "") {
        deleteImage(url, default_img);
    }
}

function deleteImage(uRL, default_img) {
    $.ajax({
        url: uRL,
        success: function (res) {
            console.info(res.status);
            accountUserThumbnailImage.src = default_img;
        },
    });
}
