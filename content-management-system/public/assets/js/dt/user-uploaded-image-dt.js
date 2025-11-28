$(document).ready(function() {
    db_table = $("#dt-table").DataTable({
        serverSide: true,
        stateSave: false,
        searching:false,
        pageLength: 100,

        ajax: {
            url: user_upload_image_dt_url,
            data: function(d) {

            }

        },
        columns: [{
                name: 'name',
                data: 'name',
                width: '25%',

            },
            {
                name:'total_images',
                data:'total_images',
                width:'25%',
                orderable:false,
                searchable:false,
            },
            {
                name:'unverified_images',
                data:'unverified_images',
                width:'25%',
                orderable:false,
                searchable:false,
            },
            {
                name:'verified_images',
                data:'verified_images',
                width:'25%',
                orderable:false,
                searchable:false,
            },


            {
                name: 'action',
                data: 'action',
                orderable: false
            },
        ],
        order: [0, 'desc'],
        drawCallback: function(settings, json) {
            $('[rel="tooltip"]').tooltip();
        },

    });
    // deleteDbTableData('#category-table');
    // changeStatus('#category-table');

})
