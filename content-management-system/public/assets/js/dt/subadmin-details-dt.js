
$(document).ready(function () {

    db_table =  $("#dt-table").DataTable({
        serverSide: true,
        stateSave: false,
         pageLength: 100,


        ajax: {
            url: uRL,
            data: function (d) {
            }

        },
        columns: [{
            name: 'name',
            data: 'name',
            width: '50%',
        },

        {
            name: 'city',
            data: 'city',
            width: '25%',
        },

        {
            name: 'created_at',
            data: 'created_at_',
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
            $('[rel="tooltip"]').tooltip();
        },

    });
    deleteDbTableData('#dt-table');
    changeStatus('#dt-table');
});
