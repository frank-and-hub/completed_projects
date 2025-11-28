
$(".menu-item." + window.active_page).addClass('active');
$(".nav-page-title").html(window.page_title);



function ShowTooltip(position = 'top') {
    $('[rel=tooltip]').tooltip({
        placement: position
    });
}

function prioritySection() {
    $(document).ready(function () {
        ShowPrioritySection($(".selectpicker").val());

        $(".selectpicker").on('changed.bs.select', function () {
            var type = $(this).val();
            ShowPrioritySection(type);

        });
    });
}


function ShowPrioritySection(type) {
    $("#priority_section").addClass('d-none');

    if (type == 'popular') {
        $("#priority_section").removeClass('d-none');
    }

}


function ToastAlert(msg, type = 'Success', className) {
    const toastPlacementExample = document.querySelector('.toast-placement-ex');
    let selectedType, selectedPlacement, toastPlacement;
    if (toastPlacement) {
        toastDispose(toastPlacement);
    }
    selectedType = className;
    selectedPlacement = "top-0 end-0";
    toastPlacementExample.classList.add(selectedType);
    toastPlacement = new bootstrap.Toast(toastPlacementExample);
    document.getElementById("toast-body").innerHTML = msg;
    document.getElementById("header-toast").innerHTML = type;
    toastPlacement.show();

}


function toastDispose(toast) {
    if (toast && toast._element !== null) {
        if (toastPlacementExample) {
            toastPlacementExample.classList.remove(selectedType);
            DOMTokenList.prototype.remove.apply(toastPlacementExample.classList, selectedPlacement);
        }
        toast.dispose();
    }
}

function BasicDelete(Url, dbtable, self, title_ = "Delete", content_ = "Are you sure?") {
    $.confirm({
        title: title_,
        content: content_,
        buttons: {
            Yes: {
                btnClass: 'btn btn-success',
                action: function (e) {
                    $.ajax({
                        url: Url,
                        method: "get",
                        success: function (res) {

                            ToastAlert(res.msg, "Success", className = "bg-success");
                            dbtable.ajax.reload();

                        }
                    })
                }
            },
            No: function () {
                // self.prop('disabled', false);
                $("body").on("click");

            },
        }
    });
}

function deleteDbTableData(selector, title, content) {
    $(selector).on("click", ".dltBtn", function () {
        console.info('this is a delete btn');
        var Url = $(this).attr('link');
        var self = $(this);
        self.tooltip('disable');
        self.tooltip('hide');

        BasicDelete(Url, db_table, self, title, content);

    })

    $(selector).on("mouseenter", ".dltBtn", function () {
        var self = $(this);
        self.tooltip('enable');



    })


}

function changeStatus(selector) {
    $(selector).on("click", "[type=checkbox]", function () {
        var id = null;
        id = $(this).attr('id');
        var table = $(selector).DataTable();
        var Url = $(this).attr('link');
        var status, msg;
        var status_tooltip = $(this).parent();
        var self = $(this);

        if ($(this).is(":checked")) {
            status = 1;
            status_tooltip.attr('data-bs-original-title', 'Active');
            status_tooltip.tooltip('show');
        } else {
            status = 0;
            status_tooltip.attr('data-bs-original-title', 'Inactive');
            status_tooltip.tooltip('show');
        }

        $.confirm({
            title: 'Status',
            content: "Do you want to change status?",
            buttons: {
                Yes: {
                    btnClass: 'btn btn-success',
                    action: function (e) {
                        // $(selector).DataTable().draw();
                        $.ajax({
                            url: Url,
                            method: "get",
                            data: { 'status': status, 'current_id': id },
                            success: function (res) {
                                ToastAlert(res.msg, "Success", className = "bg-success");
                                status_tooltip.tooltip('hide');
                            },
                            error: function (err) {
                                console.error(err);
                                ToastAlert(err.responseJSON.msg, "Error", className = "bg-danger");
                                status_tooltip.attr('data-bs-original-title', self.is(":checked") ? 'Active' : 'Inactive');
                            },
                            complete: function () {
                                // $('[rel="tooltip"]').tooltip('hide');
                                table.draw();
                            }
                        })
                    }
                },
                No: function () {
                    if (status == 1) {
                        self.prop('checked', false);
                        status_tooltip.attr('data-bs-original-title', 'Inactive');
                    } else {
                        self.prop('checked', true);
                        status_tooltip.attr('data-bs-original-title', 'Active');
                    }
                },
            }
        });


    });
}

function PassHideShow(e) {
    var type = $(e).parent().find('input').attr('type');
    var password_field = $(e).parent().find('input');
    var view_icon = $(e).find('i');

    if (type == 'password') {
        view_icon.addClass('bx-hide');
        view_icon.removeClass('bx-show');
        password_field.attr('type', 'text');

    } else {
        view_icon.addClass('bx-show');
        view_icon.removeClass('bx-hide');
        password_field.attr('type', 'password');
    }
}


const firstCap = (txt) => {
    return txt.toLowerCase().charAt(0).toUpperCase() + txt.toLowerCase().slice(1);
}

const hideLoader = (selector = '#dt-loader') => {
    $(selector).addClass('d-none');
}

const showLoader = (selector = '#dt-loader') => {
    // selector.removeClass('d-none');
    $(selector).removeClass('d-none');

}



