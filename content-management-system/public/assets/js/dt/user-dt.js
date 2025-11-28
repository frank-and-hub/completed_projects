$(document).ready(function() {
    function dbTble() {
        db_table = $("#user-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            ajax: {
                url: uRL,
                data: function(d) {

                },
                beforeSend:function(){
                    showLoader();
                }

            },
            columns: [{
                    name: 'name',
                    data: 'name'
                },
                {
                    name: 'email',
                    data: 'email'
                },
                {
                    name: 'username',
                    data: 'username',
                    orderable: false,

                },
                {
                    name: 'action',
                    data: 'action',
                    orderable: false
                },
            ],
            order: [0, 'asc'],
            drawCallback: function(settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader();

            },

        });
    }


    dbTble();
    changeStatus("#user-table");


})
