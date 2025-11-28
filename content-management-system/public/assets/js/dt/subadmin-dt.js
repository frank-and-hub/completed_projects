$(document).ready(function () {
    function dbTble() {
        db_table = $("#subadmin-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            columnDefs: [
                { width: '100%', targets: 0 }
            ],

            ajax: {
                url: Url,
                data: function (d) {
                },
                beforeSend:function(){
                    showLoader();
                }

            },
            columns: [{
                name: 'name',
                data: 'name',
                width: '50%',
            },
            {
                name: 'email',
                data: 'email',
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
                hideLoader();
            },

        });
    }
    dbTble();
    deleteDbTableData('#subadmin-table');
    changeStatus('#subadmin-table');

})
