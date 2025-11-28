$(document).ready(function () {
    prioritySection();//custom.js
    function dbTble(type = null) {
        db_table = $("#feature-table").DataTable({
            serverSide: true,
            stateSave: false,
            searching: false,
            header: true,
            pageLength: 100,


            ajax: {
                method: 'post',
                url: child_features_url,
                data: {
                    'feature_type_id': feature_type_id,
                    'type': type
                }
            },
            columns: [
                {
                    name: 'name',
                    data: 'name',
                    width: '50%',


                }, {
                    name: 'type',
                    data: 'type',
                    width: '50%',

                },
                {
                    name: 'action',
                    data: 'action',
                    orderable: false
                },
            ],
            order: [0, 'asc'],
            drawCallback: function (settings, json) {
                $("[rel=tooltip]").tooltip({
                    container: '#feature-table'
                })
            }

        });

    }
    dbTble();
    deleteDbTableData("#feature-table",title="Delete feature");
    changeStatus("#feature-table")

    $("#type").change(function () {
        db_table.destroy();
        db_table.ajax.reload();
        dbTble($(this).val());

    })



});
