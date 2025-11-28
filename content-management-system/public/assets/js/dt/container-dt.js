$(document).ready(function () {
    var db_table, selected_db_table;

    function dbTble(id = null, type = null) {
        if ($.fn.DataTable.isDataTable("#park-table")) {
            db_table.clear().destroy();
        }

        db_table = $("#park-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 10,
            lengthChange: false,
            ajax: {
                url: uRL,

                data: {
                    type: type,
                    id: id,
                    feature_id: $("#feature").val(),
                },

                beforeSend: function () {
                    showLoader();
                },
            },
            columns: [
                {
                    name: "name",
                    data: "name",
                    width: "45%",
                },
                {
                    name: "address",
                    data: "address",
                    width: "45%",
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

    function dbSelectedTble(id = null, type = null) {
        if ($.fn.DataTable.isDataTable("#selected-park-table")) {
            selected_db_table.clear().destroy();
        }

        selected_db_table = $("#selected-park-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 10,
            lengthChange: false,
            paging: false,
            language: {
                info: "",
                infoEmpty: "",
                infoFiltered: "",
            },
            searching: false,
            ajax: {
                url: uRL2,
                data: {
                    type: type,
                    id: id,
                    feature_id: $("#feature").val(),
                },

                beforeSend: function () {
                    showLoader();
                },
            },
            columns: [
                {
                    name: "name",
                    data: "name",
                    width: "45%",
                },
                {
                    name: "address",
                    data: "address",
                    width: "45%",
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

                var length = $(".park-remove").length;
                $("#selected_park_count").html(`${length}`);
            },
        });
    }

    $(document).on("change", "#feature", function () {
        let value = $(this).val();
        let type = $(this).find(":selected").data("type");
        $("#feature_date_type").val(type);
        dbTble();
        dbSelectedTble();
    });

    $("#feature").change();

    $("#delete-container-btn").on("click", function (event) {
        event.preventDefault();
        var Url = $(this).attr("link");
        var self = $(this);
        self.tooltip("disable");
        self.tooltip("hide");

        $.confirm({
            title: "Delete",
            content: "Are you sure you want to delete this container?",
            buttons: {
                Yes: {
                    btnClass: "btn btn-danger",
                    action: function () {
                        $.ajax({
                            url: Url,
                            type: "DELETE",
                            dataType: "json",
                            success: function (res) {
                                ToastAlert(res.msg, "Success", className = "bg-success");
                                window.location.href = res.redirect_url;
                            },
                            error: function (xhr) {
                                ToastAlert(xhr.responseJSON.message, "Error", className = "bg-danger");
                            },
                        });
                    },
                },
                No: function () {
                    self.tooltip("enable");
                },
            },
        });
    });

    $("#containerForm").on("submit", function (event) {
        event.preventDefault();
        var selectedParks = [];
        $(".park-remove").each(function () {
            selectedParks.push($(this).data("id"));
        });
        $("#selectedParks").val(selectedParks.join(","));
        this.submit();
    });

    $(document).on("click", ".additionSubtractionButton", function () {
        var button = $(this);
        var length = $(".park-remove").length;
        if (length >= 10) {
            ToastAlert(
                `Max 10 Parks already selected in a container`,
                "Information",
                (className = "bg-info")
            );
        } else {
            $("#selected_park_count").html(`${length + 1}`);
            var projectId = button.attr("data-id");
            var checkbox = button.find("input.park-select");
            checkbox.prop("checked", !checkbox.prop("checked"));
            if (checkbox.prop("checked")) {
                button.find("span").removeClass("bx-plus").addClass("bx-minus");
                button.addClass("btn-danger").removeClass("btn-success");
                button.attr("title", '{{ __("Remove") }}');
            } else {
                button.find("span").removeClass("bx-minus").addClass("bx-plus");
                button.addClass("btn-success").removeClass("btn-danger");
                button.attr("title", '{{ __("Add") }}');
            }
            dbTble(projectId, "remove");
            dbSelectedTble(projectId, "add");
            dbTble();
        }
    });

    $(document).on("click", ".subtractionAdditionButton", function () {
        var button = $(this);
        var projectId = button.attr("data-id");

        var checkbox = button.find("input.park-select");
        $("#selected_park_count").html(`${length - 1}`);
        checkbox.prop("checked", !checkbox.prop("checked"));

        if (checkbox.prop("checked")) {
            button.find("span").removeClass("bx-minus").addClass("bx-plus");
            button.addClass("btn-success").removeClass("btn-danger");
            button.attr("title", '{{ __("Add") }}');
        } else {
            button.find("span").removeClass("bx-plus").addClass("bx-minus");
            button.addClass("btn-danger").removeClass("btn-success");
            button.attr("title", '{{ __("Remove") }}');
        }
        dbTble(projectId, "add");
        dbSelectedTble(projectId, "remove");
        dbTble();
    });
});
