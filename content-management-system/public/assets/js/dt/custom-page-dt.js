$(document).ready(function(){
    db_table = $("#custom_page_tbl").DataTable({
        serverSide: true,
        stateSave: false,
        pageLength: 100,

        ajax: {
            url: custom_db_list_url,
            beforeSend:function(){
                showLoader();
            }
        },
        columns: [{
                name: 'name',
                data: 'name',
                width: '1000%',
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
            hideLoader();
        },

    });
});
