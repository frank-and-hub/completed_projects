require('datatables.net');
require('datatables.net-bs4');

var table = $('#table').DataTable({
    dom: 'Blfrtip',
    processing: true,
    serverSide: true,
    // responsive: true,

    lengthChange: false,
    pageLength: 10,

    'language': {
        "emptyTable": "No data available",
        "loadingRecords": "&nbsp;",
        "processing": "<div>Processing...</div>"
    },
    "destroy": true,
    "scrollX": false,
    "ajax": {
        "type": "GET",
        "headers": {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        "url": "/admin/scholarship/json",
        "dataType": "json",
        "contentType": 'application/jsondt; charset=utf-8',
    },
    columns: [

        {
            data: null,
            name: 'extra_column',
            "width": "120px",
            "targets": 'no-sort',
            "orderable": false,
            'printable': false,
            "render": function (data, type, row) {
                if (row.scholarship_application) {
                    return "<button class='primary-button custom-button applicantButton' data-id='" + row.id + "'>View</button>";
                } else {
                    return "No Applicants";
                }
            }
        },

        {
            data: null,
            name: 'extra_column',
            "width": "120px",
            "targets": 'no-sort',
            "orderable": false,
            'printable': false,
            "render": function (data, type, row) {
                if (row.application_form) {
                    return "<button class='primary-button custom-button viewButton' data-id='" + row.id + "'>View</button>";
                } else {
                    return "<button class='secondary-button custom-button createButton' data-id='" + row.id + "'>Create</button>";
                }
            }
        },

        {
            data: 'id',
            name: 'id',
        },
        {
            data: 'name_of_csr',
            name: 'name_of_csr',
            render: function (data, type, row) {
                return truncateString(row.name_of_csr);
            }
        }, {
            data: 'scholarship_title',
            name: 'scholarship_title',
            render: function (data, type, row) {
                return truncateString(data);
            }
        },
        {
            data: 'year',
            name: 'year',
            render: function (data, type, row) {
                return truncateString(data);
            }
        },
        {
            data: 'status',
            name: 'status',
            "width": "120px",
            "orderable": false,
            'printable': false,
            "render": function (data, type, row) {
                var selectedOpen = data === 'active' ? 'selected' : '';
                var selectedClose = data === 'inactive' ? 'selected' : '';
                return `<select class="status-dropdown custom-select" data-id="${row.id}" style="border-radius: 20px; background-color: #f2f2f2;">
                            <option value="active" ${selectedOpen}>Active</option>
                            <option value="inactive" ${selectedClose}>Inactive</option>
                        </select>`;
            }
        },

        {
            data: 'action',
            name: 'action',
            "width": "120px",
            "targets": 'no-sort',
            "orderable": false,
            'printable': false,
            // responsivePriority: 0,
        },
    ]
});

$('#table').on('change', '.status-dropdown', function () {
    var id = $(this).data('id');
    var status = $(this).val();


    $.ajax({
        type: "GET",
        "headers": {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        url: "/admin/scholarship/update-status",
        data: {
            id: id,
            status: status
        },
        success: function (response) {
            new Noty({
                type: 'success',
                text: 'Status updated successfully!'
            }).show();
        },
        error: function (error) {
            new Noty({
                type: 'error',
                text: 'An unexpected error occurred.'
            }).show();
        }
    });
});


$('#table').on('click', '.applicantButton', function () {
    var id = $(this).data('id');
    window.location.href = "/admin/scholarship/applicants/" + id;
});

$('#table').on('click', '.viewButton', function () {
    var id = $(this).data('id');
    window.location.href = "/admin/scholarship/application-form/show/" + id;
});

$('#table').on('click', '.createButton', function () {
    var id = $(this).data('id');
    window.location.href = "/admin/scholarship/application-form/add/" + id;
});




$('#application-form-table').DataTable({
    dom: 'Blfrtip',
    processing: true,
    serverSide: true,
    // responsive: true,

    lengthChange: false,
    pageLength: 10,

    'language': {
        "emptyTable": "No data available",
        "loadingRecords": "&nbsp;",
        "processing": "<div>Processing...</div>"
    },
    "destroy": true,
    "scrollX": false,
    "ajax": {
        "type": "GET",
        "headers": {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        "url": "/admin/scholarship/application-form/json",
        "dataType": "json",
        "contentType": 'application/jsondt; charset=utf-8',
    },
    columns: [{
            data: 'id',
            name: 'id',
        },
        {
            data: 'name_of_csr',
            name: 'name_of_csr',
            render: function (data, type, row) {
                return truncateString(row.name_of_csr);
            }
        }, {
            data: 'scholarship_title',
            name: 'scholarship_title',
            render: function (data, type, row) {
                return truncateString(data);
            }
        },
        {
            data: 'year',
            name: 'year',
            render: function (data, type, row) {
                return truncateString(data);
            }
        },
        {
            data: 'action',
            name: 'action',
            "width": "120px",
            "targets": 'no-sort',
            "orderable": false,
            'printable': false,
            // responsivePriority: 0,
        },
    ]
});


// Function to truncate a string to a specific length
function truncateString(str, length = 50) {
    if (str) {
        if (str.length > length) {
            return str.substring(0, length - 3) + '...';
        }
        return str;
    } else {
        return null;
    }
}
