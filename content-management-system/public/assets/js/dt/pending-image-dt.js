$(document).ready(function () {
    db_table = $("#dt-table").DataTable({
        serverSide: true,
        stateSave: false,
        pageLength: 100,


        ajax: {
            url: uRL,
            data: function (d) {

            },


        },
        columns: [{
            name: 'name',
            data: 'name',
            width: '25%',
            orderable: true,



        },

        {
            name: 'username',
            data: 'username',
            width: '25%',
            orderable: true,


            // searchable: false,
        },
        {
            name: 'total_pending_image',
            data: 'total_pending_image',
            width: '25%',
            searchable: false,



            // orderable: false,
            // searchable: false,
        },
        {
            name: 'total_verify_image',
            data: 'total_verify_image',
            width: '25%',
            searchable: false,



            // orderable: false,
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
        },

    });
    // deleteDbTableData('#category-table');
    // changeStatus('#category-table');

});



