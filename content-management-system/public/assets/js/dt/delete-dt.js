$(document).ready(function () {
    function dbTble() {
        db_table = $("#user-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            ajax: {
                url: uRL,
                data: function (d) {

                },
                beforeSend: function () {
                    showLoader();
                }

            },
            columns: [{
                name: 'name',
                data: 'name',
                orderable: false,
            },
            {
                name: 'email',
                data: 'email',
                orderable: false,
            },
            {
                name: 'reason',
                data: 'reason',

            },
            {
                name: 'action',
                data: 'action',
                orderable: false
            },
            ],
            order: [2, 'asc'],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader();

            },

        });
    }


    dbTble();
    changeStatus("#user-table");


})
