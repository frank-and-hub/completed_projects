window.filter_val = null;

$(document).ready(function () {

    function dbTble(filterVal = filter_val) {
        db_table = $("#category-table").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,
            ajax: {
                url: uRL,
                data: {
                    'filterVal': filterVal,
                    'city': $('#city').val(),
                    'seo_feature': $('#seo_feature').val(),
                },
                beforeSend: function () {
                    showLoader();
                },
            },
            columns: [{
                name: 'name',
                data: 'name',
                width: '50%',
            }, {
                name: 'city',
                data: 'city',
                width: '50%'
            }, {
                name: 'action',
                data: 'action',
                orderable: false
            },
            ],
            order: [0, 'asc'],
            // info: true,
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
                hideLoader();

                $('#total_park_count').html(settings.json.recordsFiltered);
            },
        });
        $('#category-table_wrapper').append('<div id="bottom-table-count"></div>');
    }
    dbTble();
    deleteDbTableData('#category-table', title = "Delete park", content = "Are you sure?");
    changeStatus('#category-table');

    //click to reset button
    $('#ResetAllSelectedBtn').click(function () {
        $("#dorpDownFilter").selectpicker('deselectAll');
        $("#city").selectpicker('deselectAll');
        $("#seo_feature").selectpicker('deselectAll');
        $('#city').val('');
        $('#city').change();
        $('#seo_feature').val('');
        $('#seo_feature').change();
        filter_val = [];
        refreshDtbl();

    });

    let dropdownValues = [];

    $("#ApplyBtn").click(function () {
        filter_val = dropdownValues;
        refreshDtbl();
    })

    //cick to on dropdown
    $("#dorpDownFilter").on('change', function () {
        const values = $(this).val();
        dropdownValues = values;
        if (values.length > 0) {
            $("#ApplyBtn,#ResetAllSelectedBtn").prop('disabled', false);
        } else {
            $("#ApplyBtn,#ResetAllSelectedBtn").prop('disabled', true);
            filter_val = [];
            refreshDtbl();
        }
    })

    $("#city,#seo_feature").on('change', function () {
        const values = $(this).val();
        if (values.length > 0) {
            $("#ApplyBtn,#ResetAllSelectedBtn").prop('disabled', false);
        } else {
            // $("#ApplyBtn,#ResetAllSelectedBtn").prop('disabled', true);
        }
    })


    function refreshDtbl() {
        db_table.destroy();
        db_table.ajax.reload();
        dbTble(filter_val);
    }

})
