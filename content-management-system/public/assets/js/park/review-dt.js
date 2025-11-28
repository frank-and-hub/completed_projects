$(document).ready(function() {
    function db_tbl(){
        db_table = $("#reviews-park-tbl").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            ajax: {
                url: review_url,
                data: function(d) {
                }
            },
            columns: [{
                    name: 'name',
                    data: 'name',
                    width:'25%'
                },
                {
                    name: 'review',
                    data: 'review',
                    width:'50%',
                    orderable:false,
                    searchable:false,

                },
                {
                    name: 'ratings',
                    data: 'ratings',
                    width:'50%',

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
    // changeStatus('#category-table');
        });
    }
    db_tbl();
    deleteDbTableData('#reviews-park-tbl');

})
