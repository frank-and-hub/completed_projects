<script type="text/javascript">
$(document).ready(function() {
    $('#bound_listing').hide();
    // DatePicker Apply
    $('#start_date').hover(function() {
        var company_date = $('#company_register_date').val();
        var current_date = $('#create_application_date').val();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
        }).on('change', function() {
            var date = $('#start_date').val();
            $('#end_date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,

            });
            $('#end_date').datepicker('setStartDate', date);
            $('#end_date').datepicker('setEndDate', current_date);

        })
        $('#start_date').datepicker('setStartDate', company_date);
        $('#start_date').datepicker('setEndDate', current_date);



    })



    $('#company_id').on('change', function() {
        var company_id = $(this).val();
        if (company_id == '') {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#company_register_date').val('');
            $('#start_date').datepicker('destroy');
            $('#end_date').datepicker('destroy');
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: "{{route('admin.vendor.companydate')}}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                },
                success: function(response) {
                    $('#company_register_date').val(response);
                    $('#start_date').val('');
                    $('#end_date').val('');


                }
            });
        }
    });

    companyboundTable = $('#company_bound_list').DataTable({
        processing: true,
        searching: false,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings();
            $('html, body').stop().animate({
                scrollTop: ($('#company_bound_list').offset().top)
            }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.company_bound_listing') !!}",
            "type": "POST",
            "data": function(d) {
                // Serialize the form data and add it to the AJAX request as 'searchform'
                d.searchform = $('form').serializeArray();
                // d.is_search = true; // Include the is_search parameter and set it to true
                d._token = $('meta[name="csrf-token"]').attr('content');
            },
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },

            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'bank_name',
                name: 'bank_name'
            },
            {
                data: 'fd_no',
                name: 'fd_no'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'remark',
                name: 'remark'
            },
            {
                data: 'maturity_date',
                name: 'maturity_date'
            },
            {
                data: 'file',
                name: 'file'
            },
            {
                data: 'receive_bank',
                name: 'receive_bank'
            },
            {
                data: 'receive_bank_account',
                name: 'receive_bank_account'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action'
            },
        ],"ordering": false
    });
    $(companyboundTable.table().container()).removeClass('form-inline');

    $('.export').on('click', function(e) {
        e.preventDefault();
        var extension = $(this).attr('data-extension');
        $('#company_bond').val(extension);

        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
        $(".spiners").css("display", "block");
        $(".loaders").text("0%");
        doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
        $("#cover").fadeIn(100);


    });


    // function to trigger the ajax bit
    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.comapnyBond.export') !!}",
            data: formData,
            success: function(response) {
                console.log(response);
                if (response.result == 'next') {
                    start = start + chunkSize;
                    doChunkedExport(start, limit, formData, chunkSize);
                    $(".loaders").text(response.percentage + "%");
                } else {
                    var csv = response.fileName;
                    console.log('DOWNLOAD');
                    $(".spiners").css("display", "none");
                    $("#cover").fadeOut(100);
                    window.open(csv, '_blank');
                }
            }
        });
    }


    jQuery.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        jQuery.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };



    $(document).on('click', '.delete_expense', function() {
        var expense_id = $(this).attr("data-row-id");

        swal({
                title: "Are you sure?",
                text: "Do you want to delete this Bond?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                cancelButtonClass: "btn-danger",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.fd.delete') !!}",
                        dataType: 'JSON',
                        data: {
                            'id': expense_id
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status == "1") {
                                swal("Good job!", response.message, "success");
                                location.reload();
                            } else {
                                swal("Warning!", response.message, "warning");
                                return false;
                            }
                        }
                    });
                }
            });
    })
})


function searchForm() {
    if ($('#filter').valid()) {
        $('#is_search').val("yes");
        companyboundTable.draw();
        $('#bound_listing').show();
    }
}

function resetForm() {
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#end_date').val('');
    $('#fd_no').val("");
    $('#company_id').val('');
    $('#fd_status').val('');
    $('#bound_listing').hide();

    companyboundTable.draw();
}
</script>