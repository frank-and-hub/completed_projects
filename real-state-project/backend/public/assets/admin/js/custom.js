var base_url = $("#base_url").val();
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

const ToastAlert = (msg, cls = "success") => {
    $.notify(msg, {
        position: "top center",
        className: cls,
        arrowShow: true,
        autoHideDelay: 4000,
        arrowSize: 100,
        gap: 4,
    });
};

function handleServerError(formName, errors) {
    $("form[name='" + formName + "']")
        .find(".text-danger.serverside_error")
        .remove();
    $.each(errors, function (field, messages) {
        var $input = $("form[name='" + formName + "']").find(
            "[name='" + field + "']"
        );
        $.each(messages, function (index, message) {
            $input.after(
                '<span class="text-danger serverside_error">' +
                    message +
                    "</span>"
            );
        });
    });
}
$.validator.addMethod(
    "email_rule",
    function (value, element) {
        var emailRegex =
            /^([a-zA-Z0-9_\-\.]+)\+?([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        return this.optional(element) || emailRegex.test(value);
    },
    "Please enter a valid Email."
);

$.validator.addMethod(
    "maxWords",
    function (value, element, params) {
        return this.optional(element) || str_word_count(value) < params;
    },
    $.validator.format("Please enter less than {0} words.")
);

function str_word_count(value) {
    return value.trim().split(/\s+/).length;
}

function show_loader() {
    $("#loader-web").removeClass("loader-disable");
}

function hideloader() {
    $("#loader-web").addClass("loader-disable");
}

function previewFile(filePath) {
    document.getElementById("model_btn").click();
    const contractPreviewDiv = document.getElementById("contract_preview");
    contractPreviewDiv.innerHTML = "";
    console.log(filePath,);
    const secureFilePath = `${filePath}#toolbar=0&navpanes=0&scrollbar=1&view=FitH`;

    const pdfEmbed = document.createElement("object");
    pdfEmbed.data = secureFilePath;
    pdfEmbed.type = "application/pdf";
    pdfEmbed.style.width = "100%";
    pdfEmbed.style.height = "700px";
    pdfEmbed.style.pointerEvents = "auto";
    pdfEmbed.style.padding = "1rem";

    pdfEmbed.oncontextmenu = (e) => e.preventDefault();

    document.addEventListener("keydown", (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === "p") {
            e.preventDefault();
        }
    });

    const style = document.createElement("style");
    style.textContent = `
        @media print {
            body * {
                visibility: hidden;
            }
            #contract_preview, #contract_preview * {
                visibility: hidden;
            }
        }
    `;
    document.head.appendChild(style);

    // Append elements
    contractPreviewDiv.style.position = "relative";
    contractPreviewDiv.appendChild(pdfEmbed);
}

function disableAllInputsInForm(formId) {
    var form = document.getElementById(formId);
    var elements = form.querySelectorAll("input, select, textarea, button");

    elements.forEach(function (element) {
        element.disabled = true;
    });
}

function CheckAdminHasPlanExists() {
    $.ajax({
        url: CHECK_ADMIN_HAS_PLAN_EXISTS, //CheckAdminHasPlanExists,
        type: "POST",
        data: {
            lat: lat,
            lng: lng,
            zoom: zoom,
        },
        success: function (response) {
            for (value in response) {
                var lat_ajax = Number(response[value]["lat"]);
                var lng_ajax = Number(response[value]["lng"]);
                createMarker_drag(
                    map,
                    AdvancedMarkerElement,
                    lat_ajax,
                    lng_ajax,
                    response[value]
                );
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: ", status, error);
        },
    });
}

$(document).on("keypress", ".only_number", function (e) {
    var charCode = e.which ? e.which : event.keyCode;
    if (String.fromCharCode(charCode).match(/[^0-9]/g)) return false;
});

$(".nav-item").removeClass("active");

$(document).on("click", ".nav-item", function () {
    $(".nav-item").removeClass("active");
    $(this).addClass("active");
});

$(document).on("keypress", ".number_with_decimal", function (e) {
    var charCode = e.which ? e.which : event.keyCode;
    var inputValue = $(this).val();

    if (
        !(charCode >= 48 && charCode <= 57) &&
        charCode !== 46 &&
        charCode !== 8
    ) {
        return false;
    }

    if (charCode === 46 && inputValue.indexOf(".") !== -1) {
        return false;
    }
});

$(document).on("change", ".changestatus", function () {
    var $this = $(this);
    var previousStatus = $this.prop("checked");
    var datastatus = $this.is(":checked") ? "unblock" : "block";
    var dataId = $this.data("id");
    var datatable = $this.data("datatable");
    Swal.fire({
        title: "Are you sure?",
        text: "It will be " + datastatus + " user!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        allowOutsideClick: false,
        confirmButtonText: "Yes, " + datastatus + " it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: STATUS_UPDATE_ROUTE,
                type: "POST",
                data: {
                    dataId: dataId,
                    datastatus: datastatus,
                },
                success: function (response) {
                    if (response.status) {
                        if (response.type == 1) {
                            Swal.fire("Unblock!", response.msg, "success");
                        } else {
                            Swal.fire("Block", response.msg, "success");
                        }
                    } else {
                        Swal.fire("Oops !", response.msg, "error");
                        if (previousStatus == false) {
                            $this.prop("checked", true);
                        } else {
                            $this.prop("checked", false);
                        }
                    }
                    $("#" + datatable)
                        .DataTable()
                        .ajax.reload();
                },
                error: function (xhr, status, error) {
                    Swal.fire(
                        "Error",
                        "Status process encountered an error. Your file is safe :)",
                        "error"
                    );
                },
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            if (previousStatus == false) {
                $this.prop("checked", true);
            } else {
                $this.prop("checked", false);
            }
        }
    });
});

$(document).on("click", ".delete_btn", function () {
    var dataId = $(this).data("id");
    var datatable = $(this).data("datatable");
    var url = $(this).data("url");
    var ajax_type = $(this).data("method") ?? "POST";
    Swal.fire({
        title: "Are you sure?",
        text: "you want to permanently delete this user?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        allowOutsideClick: false,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: ajax_type,
                data: { dataId: dataId },
                success: function (response) {
                    if (response.status == "success") {
                        Swal.fire("Deleted!", response.msg, "success");
                    } else {
                        Swal.fire(
                            "Error",
                            "Deletion failed. Your file is safe",
                            "error"
                        );
                    }
                    $("#" + datatable)
                        .DataTable()
                        .ajax.reload();
                },
                error: function (xhr, status, error) {
                    Swal.fire(
                        "Error",
                        "Deletion process encountered an error. Your file is safe :)",
                        "error"
                    );
                },
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
        }
    });
});

$(".toggle-password").click(function () {
    $(this).toggleClass("fa-eye fa-eye-slash");
    input = $(this).parent().find("input");
    if (input.attr("type") == "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});

$(document).on("click", ".deletemodel", function () {
    var dataId = $(this).data("id");
    var datatable = $(this).data("datatable");
    var url = $(this).data("url");
    $("#dataId").val(dataId);
    $("#deletemodel").modal("show");
    var ajax_type = $(this).data("method") ?? "POST";

    $("form[name='delete-form']").validate({
        rules: {
            password: {
                required: true,
            },
        },
        messages: {
            password: {
                required: "Enter Your password",
            },
        },
        submitHandler: function (form) {
            $.ajax({
                url: url,
                type: ajax_type,
                data: $(form).serialize(),
                success: function (response) {
                    if (response.status == "success") {
                        $(".error_msg").fadeOut();
                        $("#delete-form")[0].reset();
                        $("#deletemodel").modal("hide");
                        $("#delete-message").text(response.msg);
                        $("#deletesuccessmodel").modal("show");
                        $("#" + datatable)
                            .DataTable()
                            .ajax.reload();
                    } else {
                        $(".error_msg").html(response.msg).fadeIn();
                        setTimeout(function () {
                            $(".error_msg").fadeOut();
                        }, 3000);
                    }
                },
                error: function (xhr, status, error) {
                    $(".error_msg")
                        .html("An error occurred. Please try again.")
                        .fadeIn();
                    setTimeout(function () {
                        $(".error_msg").fadeOut();
                    }, 3000);
                },
            });
        },
    });
});

$(document).on("click", ".requestTypeAgencies", function () {
    var dataId = $(this).data("id");

    var datatable = $(this).data("datatable");
    var url = $(this).data("url");
    $("#request_verification_dataId").val(dataId);
    $("#requestTypeAgencies").modal("show");

    $("form[name='requestTypeAgencies-form']").validate({
        rules: {
            password: {
                required: true,
            },
        },
        messages: {
            password: {
                required: "Enter Your password",
            },
        },
        submitHandler: function (form) {
            $.ajax({
                url: url,
                type: "POST",
                data: $(form).serialize(),
                success: function (response) {
                    if (response.status == "success") {
                        $(".error_msg").fadeOut();
                        $("#requestTypeAgencies-form")[0].reset();
                        $("#requestTypeAgencies").modal("hide");
                        $("#request-verification-message").text(response.msg);
                        $("#requestVerificationPopUp").modal("show");
                        $("#" + datatable)
                            .DataTable()
                            .ajax.reload();
                    } else {
                        $(".error_msg").html(response.msg).fadeIn();
                        setTimeout(function () {
                            $(".error_msg").fadeOut();
                        }, 3000);
                    }
                },
                error: function (xhr, status, error) {
                    $(".error_msg")
                        .html("An error occurred. Please try again.")
                        .fadeIn();
                    setTimeout(function () {
                        $(".error_msg").fadeOut();
                    }, 3000);
                },
            });
        },
    });
});

$(document).ready(function () {
    hideloader();

    ///////////////countries-select2
    $(".countries-select2")
        .select2({
            ajax: {
                url: COUNTRIES_SELECT2_URL,
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || "",
                        page: params.page || 1,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more,
                        },
                    };
                },
                cache: true,
            },
            placeholder: "Search for a country",

            allowClear: true,
        })
        .on("select2:select", function (e) {
            // Enable State dropdown when Country is selected
            $(".state-select2")
                .prop("disabled", false)
                .val(null)
                .trigger("change");
        });

    $(".state-select2")
        .select2({
            ajax: {
                url: STATES_SELECT2_URL,
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || "",
                        page: params.page || 1,
                        country_id: $(".countries-select2").val(),
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more,
                        },
                    };
                },
                cache: true,
            },
            placeholder: "Search for a state",
            allowClear: true,
        })
        .on("select2:select", function (e) {
            $(".city-select2")
                .prop("disabled", false)
                .val(null)
                .trigger("change");
            $(".suburb-select2")
                .prop("disabled", false)
                .val(null)
                .trigger("change");
        });

    $(".city-select2")
        .select2({
            ajax: {
                url: CITIES_SELECT2_URL,
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || "",
                        page: params.page || 1,
                        country_id: $(".countries-select2").val(),
                        state_id: $(".state-select2").val(),
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more,
                        },
                    };
                },
                cache: true,
            },
            placeholder: "Search for a city",
            allowClear: true,
        })
        .on("select2:select", function (e) {
            $(".suburb-select2")
                .prop("disabled", false)
                .val(null)
                .trigger("change");
        });

    $(".suburb-select2").select2({
        ajax: {
            url: SUBURB_SELECT2_URL,
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || "",
                    page: params.page || 1,
                    country_id: $(".countries-select2").val(),
                    state_id: $(".state-select2").val(),
                    city_id: $(".city-select2").val(),
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more,
                    },
                };
            },
            cache: true,
        },
        placeholder: "Search for a Suburb",
        allowClear: true,
    });
});
