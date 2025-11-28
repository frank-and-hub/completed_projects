$(document).ready(function() {
    db_table = $("#bookmark-tbl").DataTable({
        serverSide: true,
        stateSave: false,
        searching:false,
        pageLength: 100,

        columnDefs: [
            {
                target: 2,
                visible: false,
                searchable: false,
            },

        ],

        ajax: {
            url: uRL,
            data: function(d) {

            }

        },
        columns: [{
                name: 'name',
                data: 'name',
                width:'50%',

            },
            {
                name:'bookmark_type_',
                data:'bookmark_type_',
                width:'25%',
                orderable: false,
                searchable:false,

            },

            {
                name:'created_at',
                data:'created',
                width:'25%',
                searchable:false,


            },

            // {
            //     name: 'action',
            //     data: 'action',
            //     orderable: false,
            // },
        ],
        order: [2, 'desc'],
        drawCallback: function(settings, json) {
            $('[rel="tooltip"]').tooltip();
        },

    });
})
