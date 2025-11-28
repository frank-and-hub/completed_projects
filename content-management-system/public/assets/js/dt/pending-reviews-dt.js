$(document).ready(function () {
    function dbTble(type = null) {
        db_table = $("#dt-table").DataTable({
            serverSide: true,
            // stateSave: false,
            pageLength: 100,

            ajax: {
                url: uRL,
                data: function (d) {

                },


            },
            columns: [
                {
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

                },

                {
                    name: 'review',
                    data: 'review',
                    width: '25%',
                    // orderable: false,
                    searchable: false,

                },

                {
                    name: 'rating',
                    data: 'rating',
                    width: '25%',
                    // orderable: false,
                    searchable: false,

                },
                // {
                //     name: 'pedning_reviews',
                //     data: 'pedning_reviews',
                //     width: '25%',

                // },
                // {
                //     name: 'verified_reviews',
                //     data: 'verified_reviews',
                //     width: '25%',

                // },
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
    }
    dbTble();

    deleteDbTableData('#dt-table');
    // changeStatus('#category-table');

});
