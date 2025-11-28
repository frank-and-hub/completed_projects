$(document).ready(function () {
    db_table = $("#parks-dt-table").DataTable({
        serverSide: true,
        stateSave: false,
        pageLength: 100,
        ajax: {
            url: uRL,
            data: {'id':id},
            beforeSend: function () {
                showLoader();
            },

        },
        columns: [{
            name: 'name',
            data: 'name',
            width: '100%',
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
    deleteDbTableData('#parks-dt-table',title="Delete category",content="Are you sure?");
    changeStatus('#parks-dt-table');
})
