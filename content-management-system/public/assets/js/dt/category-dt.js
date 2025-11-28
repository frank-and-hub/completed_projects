$(document).ready(function () {
    function dbTble(type = null) {
        db_table = $("#category-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            ajax: {
                url: uRL,

                data: {
                    'type': type
                },

                beforeSend: function () {
                    showLoader();
                },
            },
            columns: [{
                name: 'name',
                data: 'name'
            }, {
                name: 'type',
                data: 'type'
            }, {
                name: 'priority',
                data: 'priority'
            },
            {
                name: 'total_child_categories',
                data: 'total_child_categories',
                orderable: false,
                searchable: false,
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


    dbTble();
    deleteDbTableData("#category-table",title="Delete category",content="Are you sure?");
    changeStatus("#category-table");

    $("#type").change(function () {
        var type = $(this).val();
        db_table.destroy();
        db_table.ajax.reload();
        dbTble(type);
        $('[rel="tooltip"]').tooltip('hide');

    })
})
