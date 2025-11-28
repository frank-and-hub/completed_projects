
$(document).ready(function () {
    function dbTble(type = null) {
        db_table = $("#feature_type-table").DataTable({
            serverSide: true,
            stateSave: false,
            searching: false,

            pageLength: 100,

            ajax: {
                url: uRL,
                data: { 'type': type },
                beforeSend:function(){
                    showLoader();
                },
            },

            columns: [{
                name: 'name',
                data: 'name',
                width: '25%',
            },

            {
                name: 'type',
                data: 'type',
                width: '25%',
            },
            {
                name: 'total_child_features',
                data: 'total_child_features',
                width: '35%',
                orderable: false,
                searchable: false,

            },
            {
                name: 'priority',
                data: 'priority',
                width: '25%',
                orderable: false,
                searchable: false,

                // visible: false,
                // searchable: false,

            },

            {
                name: 'action',
                data: 'action',
                orderable: false
            },
            ],
            order: [0, 'asc'],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader();
            },

        });
    }


    function popularChildFeatuerTbl() {
        child_feature_tbl = $("#feature-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            ajax: {
                url: childFeatureUrl,
                beforeSend:function(){
                    showLoader(selector='#child-feature-dt-loader');
                },
            },

            columns: [
                { name: 'name', data: 'name', width: '25%' },
                { name: 'parent_feature', data: 'parent_feature', width: '25%' },
                { name: 'priority', data: 'priority', width: '50%' },
                { name: 'action', data: 'action' },

            ],
            order: [0, 'desc'],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader(selector='#child-feature-dt-loader');

            },
        });
    }

    function childFeatureDtTble(type = null) {
        child_FeatureDttbl = $("#child-feature-dt-table").DataTable({
            serverSide: true,
            stateSave: false,
            header: true,
            pageLength: 100,

            ajax: {
                method: 'post',
                url: ChildFeatureDttbl,
                data: {
                    'type': type
                },
                beforeSend:function(){
                    showLoader('#child-feature-dt-loader');
                },
            },
            columns: [
                {
                    name: 'name',
                    data: 'name',
                    width: '25%',


                }, {
                    name: 'type',
                    data: 'type',
                    width: '25%',

                },

                {
                    name: 'parent_feature',
                    data: 'parent_feature',
                    width: '50%',
                    orderable: false,


                },

                {
                    name: 'action',
                    data: 'action',
                    orderable: false
                },
            ],
            order: [0, 'asc'],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader('#child-feature-dt-loader');

            }

        });

    }

    dbTble();

    db_table.column(3).visible(false);

    deleteDbTableData("#feature_type-table",title="Delete feature", content="Are you sure?");
    changeStatus("#feature_type-table");

    $("#type").change(function () {
        db_table.destroy();
        db_table.ajax.reload();
        dbTble($(this).val());
        $('[rel="tooltip"]').tooltip('hide');

        if ($(this).val() == 'popular') {
            db_table.column(3).visible(true);
            $("#popular_child_feature").removeClass('d-none');
            popularChildFeatuerTbl();
            document.getElementById('feature-table').scrollIntoView();
        } else {
            $("#popular_child_feature").addClass('d-none');
            child_feature_tbl.destroy();
            db_table.column(3).visible(false);
        }



    })



    //select feature dropdown
    $("#selecteFeatureDropDown").change(function () {

        const feature = $(this).val();
        // Exchange type filter
        if (feature === 'child') {

            $("#parent-feature-type-filter").addClass('d-none');
            $("#child-feature-type-filter").removeClass('d-none');

            $("#parent_feature").addClass('d-none');
            $("#child_feature").removeClass('d-none');
            $("#popular_child_feature").addClass('d-none');

            $("#child_feature_type").val($("#child_feature_type option:first").val());
            db_table.column(3).visible(false);
            childFeatureDtTble();

        } else {
            $("#parent-feature-type-filter").removeClass('d-none');
            $("#child-feature-type-filter").addClass('d-none');
            $("#child_feature").addClass('d-none');
            $("#parent_feature").removeClass('d-none');

            // var type = $("#type :selected").val();
            child_FeatureDttbl.destroy();
            db_table.destroy();
            dbTble();
            db_table.column(3).visible(false);

        }

    });

    // chid feature type filter
    $("#child_feature_type").change(function () {
        const type = $(this).val();
        if (type != '') {
            child_FeatureDttbl.destroy();

            childFeatureDtTble(type)
        }
    })

    deletePopularChild('#feature-table');
    deleteChildFeature('#child-feature-dt-table');
    childFeatureChangeStatus('#child-feature-dt-table');



    function deletePopularChild(selector) {
        $(selector).on("click", ".dltBtn", function () {
            var child_feature_delete_url = $(this).attr('link');
            $.confirm({
                title: 'Delete feature',
                content: "Are you sure?",
                buttons: {
                    Yes: {
                        btnClass: 'btn btn-success',
                        action: function (e) {
                            $.ajax({
                                url: child_feature_delete_url,
                                method: "get",
                                success: function (res) {
                                    ToastAlert(res.msg, "Success", className = "bg-success");

                                    child_feature_tbl.destroy();
                                    popularChildFeatuerTbl();

                                }
                            })
                        }
                    },
                    No: function () {
                        null;
                    },
                }
            });
            $('[rel="tooltip"]').tooltip('hide');
        })
    }

    function deleteChildFeature(selector) {
        $(selector).on("click", ".dltBtn", function () {
            var child_feature_delete_url = $(this).attr('link');
            $.confirm({
                title: 'Delete feature',
                content: "Are you sure?",
                buttons: {
                    Yes: {
                        btnClass: 'btn btn-success',
                        action: function (e) {
                            $.ajax({
                                url: child_feature_delete_url,
                                method: "get",
                                success: function (res) {
                                    ToastAlert(res.msg, "Success", className = "bg-success");
                                    child_FeatureDttbl.destroy();
                                    childFeatureDtTble();


                                }
                            })
                        }
                    },
                    No: function () {
                        null;
                    },
                }
            });
            $('[rel="tooltip"]').tooltip('hide');
        })
    }

    function childFeatureChangeStatus(selector) {
        $(selector).on("click", "[type=checkbox]", function () {
            var id = null;
            id = $(this).attr('id');

            var status, msg;
            var status_tooltip = $(this).parent();
            var Url = $(this).attr('link');
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
                            $.ajax({
                                url: Url,
                                method: "get",
                                data: { 'status': status, 'current_id': id },
                                success: function (res) {
                                    ToastAlert(res.msg, "Success", className = "bg-success");
                                    status_tooltip.tooltip('hide');
                                    child_FeatureDttbl.destroy();
                                    childFeatureDtTble();
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



    //delete child feature dt





})
