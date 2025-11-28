$(document).ready(function() {
    $.ajax({
        url: dashboard_countr_url,
        method: 'get',
        data: {},
        beforeSend: function() {
        },
        success: function(res) {
            $(res.data).each(function(index, value) {
                $(".count").each(function(indx, val) {
                    var id = $(this).attr('id');
                    $("#"+id).text(value[id])
                })
            })
        }
    })
    $(".dashboard-card").on("click", function() {
        var url = $(this).find("a").attr('href');
        location.href = url;
    })
})
