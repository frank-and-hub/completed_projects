$(document).ready(function() {
    $.ajax({
        url: top_users_url,
        method: 'get',
        data: {},
        beforeSend: function() {
            $("#top_users").html('');
            $("#top_five_user_loader").removeClass('d-none');

        },
        success: function(res) {
            $("#top_five_user_loader").addClass('d-none');
            $("#top_users").html(res);
        }
    })

})
