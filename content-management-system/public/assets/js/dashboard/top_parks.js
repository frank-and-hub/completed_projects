$(document).ready(function() {
    $.ajax({
        url: top_parks_url,
        method: 'get',
        data: {},
        beforeSend: function() {
            $("#top_parks").html('');
            $("#top_five_park_loader").removeClass('d-none');

        },
        success: function(res) {
            $("#top_five_park_loader").addClass('d-none');
            $("#top_parks").html(res);
        }
    })

})
