$(document).ready(function () {
    function dbTble(type = null) {
        db_table = $("#priority-tbl").DataTable({
            serverSide: true,
            stateSave: false,
            pageLength: 100,

            ajax: {
                url: uRL,

                data: {
                    'type': type
                }
            },
            columns: [{
                name: 'name',
                data: 'name'
            }, {
                name: 'type',
                data: 'type'
            },

            {
                name: 'show',
                data: 'show',
                orderable: false,
                searchable: false,
            },

            {
                name: 'priority',
                data: function (row) {
                    let edit = '<div><span class="text-primary edt" role="button" onclick="editpriority(this)">' + row.priority + '</span>\
                    <input type="number" min="1" name="priority" value="'+ row.priority + '" class="ml-1 priority d-none"> <button class="btn btn-primary btn-sm d-none" onclick="updatepriority(this,' + row.id + ')"><i class="fa fa-check"></i></button></div>';
                    return edit;
                },
            },

            ],
            order: [0, 'asc'],
            drawCallback: function (settings, json) {
                $('[rel="tooltip"]').tooltip();
            },
        });
    }

    dbTble();
    // deleteDbTableData("#category-table");
    // changeStatus("#category-table");

    $("#type").change(function () {
        var type = $(this).val();
        db_table.destroy();
        db_table.ajax.reload();
        dbTble(type);
        $('[rel="tooltip"]').tooltip('hide');

    })



})
function editpriority(e) {
    $(e).parent().find('input').removeClass('d-none');
    $(e).parent().find('button').removeClass('d-none');
    $(e).addClass('d-none');

}

function updatepriority(e, id) {

    var priority = $(e).parent().find('input').val();
    var self = $(e);
    if (priority == '') {
        ToastAlert(msg = "Priority is required.", type = 'Error', className = 'bg-danger');
    }
    if (priority < 1) {
        ToastAlert(msg = "Invalid priority.", type = 'Error', className = 'bg-danger');

    }
    $.ajax({
        url: priority_udpate_url,
        type: 'post',
        data: { 'id': id, 'priority': priority },
        beforeSend: function () {
            $("#loader").removeClass('d-none');
        },
        success: function (res) {
            $("#loader").addClass('d-none');

            // db_table.destroy();
            // db_table.ajax.reload();
            // dbTble();
            ToastAlert(msg = res.msg, type = 'Success', className = 'bg-success');

            // location.reload();
            self.parent().find('span').removeClass('d-none');
            self.parent().find('input').addClass('d-none');
            self.parent().find('button').addClass('d-none');
            self.addClass('d-none');
            self.parent().find('.edt').removeClass('d-none');
            self.parent().find('span').text(priority);

        }
    });

}


