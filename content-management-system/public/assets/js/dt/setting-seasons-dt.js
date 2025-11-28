$(document).ready(function () {
    function dbTble(season_type = null) {
        db_table = $("#season-tbl").DataTable({
            serverSide: true,
            stateSave: false,
            searching:false,
            ajax: {
                url: season_dt_url,
                data: { 'season_type': season_type },
                beforeSend:function(){
                    showLoader();
                }

            },
            columns: [{
                name: 'season',
                data: 'season',
                orderable: false

            },
            {
                name: 'hemisphere',
                data: 'hemisphere',
                orderable: false

            },
            {
                name: 'start_date',
                data: 'start_date',
                orderable: false,


            },
            {
                name: 'end_date',
                data: 'end_date',
                orderable: false

            },
            {
                name: 'action',
                data: 'action',
                orderable: false
            },
            ],
            // order: [2, 'desc'],
            drawCallback: function(settings, json) {
                // $('[rel="tooltip"]').tooltip();
                hideLoader();
            },

        });
    }

    dbTble();


    $("#hemisphere").change(function () {
        var season_type = $(this).val();
        db_table.destroy();
        db_table.ajax.reload();
        dbTble(season_type);
        $('[rel="tooltip"]').tooltip('hide');

    })



})


