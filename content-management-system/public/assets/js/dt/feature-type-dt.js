$(document).ready(function () {
    const pageLength = 100;

    function dbTble(type = null) {
        const tableId = "#feature_type-table";
        let display_by = $("#selecteFeatureDropDown").val();
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().clear().destroy();
        }
        db_table = $(tableId).DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: pageLength,
            ajax: {
                url: uRL,
                data: { type: type, display_by: display_by },
                beforeSend: function () {
                    showLoader();
                },
            },
            columns: [
                {
                    name: "name",
                    data: "name",
                    width: "25%",
                },
                {
                    name: "type",
                    data: "type",
                    width: "25%",
                },
                {
                    name: "total_child_features",
                    data: "total_child_features",
                    width: "35%",
                    orderable: false,
                    searchable: false,
                },
                {
                    name: "priority",
                    data: "priority",
                    width: "25%",
                    orderable: false,
                    searchable: false,
                },
                {
                    name: "action",
                    data: "action",
                    width: "25%",
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

    function popularChildFeatuerTbl() {
        const tableId = "#feature-table";
        let display_by = $("#selecteFeatureDropDown").val();
        let type = $("#type").val();

        // Destroy existing DataTable instance safely
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().clear().destroy();
        }

        child_feature_tbl = $(tableId).DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: pageLength,
            ajax: {
                url: childFeatureUrl,
                data: {
                    type: type,
                    display_by: display_by,
                },
                beforeSend: function () {
                    showLoader((selector = "#child-feature-dt-loader"));
                },
            },
            columns: [
                { name: "name", data: "name", width: "50%", searchable: false },
                {
                    name: "related_parks",
                    data: "related_parks",
                    width: "25%",
                    orderable: true,
                },
                {
                    name: "action",
                    data: "action",
                    width: "25%",
                    orderable: false,
                },
            ],
            order: [0, "asc"],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader((selector = "#child-feature-dt-loader"));
            },
        });
    }

    function childFeatureDtTble(type = null) {
        const tableId = "#child-feature-dt-table";
        let display_by = $("#selecteFeatureDropDown").val();
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().clear().destroy();
        }
        child_FeatureDttbl = $(tableId).DataTable({
            serverSide: true,
            stateSave: false,
            header: true,
            pageLength: pageLength,

            ajax: {
                method: "post",
                url: ChildFeatureDttbl,
                data: {
                    type: type,
                    display_by: display_by,
                },
                beforeSend: function () {
                    showLoader("#child-feature-dt-loader");
                },
            },
            columns: [
                {
                    name: "name",
                    data: "name",
                    width: "25%",
                },
                {
                    name: "type",
                    data: "type",
                    width: "25%",
                },

                {
                    name: "parent_feature",
                    data: "parent_feature",
                    width: "50%",
                    orderable: false,
                },
                // {
                //     name: "related_parks",
                //     data: "related_parks",
                //     width: "25%",
                //     orderable: true,
                // },
                {
                    name: "action",
                    data: "action",
                    width: "25%",
                    orderable: false,
                },
            ],
            order: [0, "asc"],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader("#child-feature-dt-loader");
            },
        });
    }

    popularChildFeatuerTbl();
    deleteDbTableData(
        "#feature_type-table",
        (title = "Delete feature"),
        (content = "Are you sure?")
    );

    $("#type").change(function () {
        let type = $(this).val();
        dbTble(type);
        childFeatureDtTble(type);
        popularChildFeatuerTbl();
        $('[rel="tooltip"]').tooltip("hide");
        if (type == "popular") {
            db_table.column(3).visible(true);
            // $("#popular_child_feature").removeClass("d-none");
            popularChildFeatuerTbl();
            document.getElementById("feature-table").scrollIntoView();
        } else if (type == "seo") {
            // $("#popular_child_feature").removeClass("d-none");
            popularChildFeatuerTbl();
            db_table.column(3).visible(false);
        } else {
            // $("#popular_child_feature").addClass("d-none");
            child_feature_tbl.destroy();
            db_table.column(3).visible(false);
            popularChildFeatuerTbl();
        }
        // $("#popular_child_feature").addClass("d-none");
    });

    //select feature dropdown
    $("#selecteFeatureDropDown").change(function () {
        const feature = $(this).val();
        // Exchange type filter
        if (feature === "child") {
            $("#popular_child_feature").addClass("d-none");

            $("#parent-feature-type-filter").addClass("d-none");
            $("#child-feature-type-filter").removeClass("d-none");

            $("#parent_feature").addClass("d-none");
            $("#child_feature").removeClass("d-none");

            $("#child_feature_type").val(
                $("#child_feature_type option:first").val()
            );

            var type = $("#type :selected").val();
            dbTble(type);
            childFeatureDtTble(type);
            db_table.column(3).visible(false);
        } else if (feature === "parent") {
            $("#popular_child_feature").addClass("d-none");

            $("#parent-feature-type-filter").removeClass("d-none");
            $("#child-feature-type-filter").addClass("d-none");
            $("#child_feature").addClass("d-none");
            $("#parent_feature").removeClass("d-none");

            var type = $("#type :selected").val();
            dbTble(type);
            childFeatureDtTble(type);
            db_table.column(3).visible(false);
        } else if (feature === "all") {
            $("#popular_child_feature").removeClass("d-none");

            $("#parent-feature-type-filter").removeClass("d-none");
            $("#child-feature-type-filter").addClass("d-none");
            $("#child_feature").addClass("d-none");
            $("#parent_feature").addClass("d-none");

            var type = $("#type :selected").val();
            child_FeatureDttbl.destroy();
            dbTble(type);
            childFeatureDtTble(type);
            db_table.column(3).visible(false);
        }
    });

    // chid feature type filter
    $("#child_feature_type").change(function () {
        const type = $(this).val();
        if (type != "") {
            child_FeatureDttbl.destroy();

            childFeatureDtTble(type);
        }
    });

    changeStatus("#feature_type-table");
    AllchangeStatus("#feature-table");
    deletePopularChild("#feature-table");
    deleteChildFeature("#child-feature-dt-table");
    childFeatureChangeStatus("#child-feature-dt-table");

    function deletePopularChild(selector) {
        $(selector).on("click", ".dltBtn", function () {
            var child_feature_delete_url = $(this).attr("link");
            $.confirm({
                title: "Delete feature",
                content: "Are you sure?",
                buttons: {
                    Yes: {
                        btnClass: "btn btn-success",
                        action: function (e) {
                            $.ajax({
                                url: child_feature_delete_url,
                                method: "get",
                                success: function (res) {
                                    ToastAlert(
                                        res.msg,
                                        "Success",
                                        (className = "bg-success")
                                    );

                                    child_feature_tbl.destroy();
                                    popularChildFeatuerTbl();
                                },
                            });
                        },
                    },
                    No: function () {
                        null;
                    },
                },
            });
            $('[rel="tooltip"]').tooltip("hide");
        });
    }

    function deleteChildFeature(selector) {
        $(selector).on("click", ".dltBtn", function () {
            var child_feature_delete_url = $(this).attr("link");
            $.confirm({
                title: "Delete feature",
                content: "Are you sure?",
                buttons: {
                    Yes: {
                        btnClass: "btn btn-success",
                        action: function (e) {
                            $.ajax({
                                url: child_feature_delete_url,
                                method: "get",
                                success: function (res) {
                                    ToastAlert(
                                        res.msg,
                                        "Success",
                                        (className = "bg-success")
                                    );
                                    child_FeatureDttbl.destroy();
                                    childFeatureDtTble();
                                },
                            });
                        },
                    },
                    No: function () {
                        null;
                    },
                },
            });
            $('[rel="tooltip"]').tooltip("hide");
        });
    }

    function childFeatureChangeStatus(selector) {
        $(selector).on("click", "[type=checkbox]", function () {
            var id = null;
            id = $(this).attr("id");

            var status, msg;
            var status_tooltip = $(this).parent();
            var Url = $(this).attr("link");
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
                                    child_FeatureDttbl.destroy();
                                    childFeatureDtTble();
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
        });
    }

    function AllchangeStatus(selector) {
        $(selector).on("click", "[type=checkbox]", function () {
            var id = null;
            id = $(this).attr("id");

            var status, msg;
            var status_tooltip = $(this).parent();
            var Url = $(this).attr("link");
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
                                    child_FeatureDttbl.destroy();
                                    popularChildFeatuerTbl();
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
        });
    }

    //delete child feature dt
});
