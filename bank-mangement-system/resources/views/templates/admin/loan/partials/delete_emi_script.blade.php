<script type="text/javascript">
var emiList;
$(document).ready(function() {
    $("#filter").submit(function(e){
        e.preventDefault();
    });
    emiList = $('#emi_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.loan.emi_record') !!}",
            "type": "POST",
            "data": function(d) {
                d.searchform = $('form#filter').serializeArray()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex'
            },
            {
                data: 'account_number',
                name: 'account_number'
            },
            {
                data: 'transaction_date',
                name: 'transaction_date'
            },
            {
                data: 'payment_mode',
                name: 'payment_mode'
            },
            // {
            //     data: 'penalty',
            //     name: 'penalty'
            // },
            {
                data: 'deposite',
                name: 'deposite'
            },
            {
                data: 'roi_amount',
                name: 'roi_amount'
            },
            {
                data: 'principal_amount',
                name: 'principal_amount'
            },
            {
                data: 'opening_balance',
                name: 'opening_balance'
            },
            {
                data: 'balance',
                name: 'balance'
            },
        ],"ordering": false
    });
    $(emiList.table().container()).removeClass('form-inline');
    // $(document).on('click', '.delete-loan-emi', function(e) {
    //     var url = $(this).attr('href');
    //     e.preventDefault();
    //     swal({
    //         title: "Are you sure, you want to delete this loan emi?",
    //         text: "",
    //         icon: "warning",
    //         buttons: [
    //             'No, cancel it!',
    //             'Yes, I am sure!'
    //         ],
    //         dangerMode: true,
    //     }).then(function(isConfirm) {
    //         if (isConfirm) {
    //             location.href = url;
    //         }
    //     });
    // })

    $(document).on('click', '.delete-loan-emi', function(e) {   
        var url = $(this).attr('href');
        e.preventDefault();
        swal({
            title: "Are you sure, you want to delete this loan emi?",
            text: "",
            content: {
                element: "textarea",
                attributes: {
                    id: "delete-loan-emi-reason",
                    placeholder: "Reason for deletion",
                    autocapitalize: 'off'
                },
            },
            buttons: {
                cancel: {
                    text: "No",
                    value: null,
                    visible: true,
                    className: "cancel_delete",
                    closeModal: true,
                },
                confirm: {
                    text: "Yes!",
                    value: null,
                    visible: true,
                    className: "delete_confirmed",
                    closeModal: false
                }
            },
            dangerMode: true,
        });
        // }).then(function(isConfirm) {
        //     if (isConfirm) {
        //         // location.href = url;
        //     }
        // });
        
        jQuery("#delete-loan-emi-reason").after('<input type="hidden" id="deletationUrl" value="'+url+'"><span class="delete-loan-emi-reason-error" style="color: red;"></span>');
        jQuery(".delete-loan-emi-reason-error").hide();
    })
    
    $(document).on('click', '.delete_confirmed', function(e) {
        document.querySelector(".swal-button--confirm").classList.remove("swal-button--loading");  
        var reason = $('#delete-loan-emi-reason').val();
        var url = $('#deletationUrl').val();
        // var ajaxUrl = url + '/' + reason;
        // console.log("reason = ", ajaxUrl);

        if (reason === "") {
            jQuery(".delete-loan-emi-reason-error").css("display", "block");
            jQuery(".delete-loan-emi-reason-error").text("Please add reason!");

            return false
        }else{

            $('.delete_confirmed').attr('disabled', true);
            $('.cancel_delete').attr('disabled', true);
            swal.close();
            
            $.ajaxSetup({
                headers: {'X-CSRF-Token': $('meta[name=token]').attr('content')}
            });
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: url,
                data: {
                    "reason": reason,
                },
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // jQuery(".sweet-alert .form-group").css("display", "none");
                    jQuery(".delete-loan-emi-reason-error").css("display", "none");
                    jQuery("#delete-loan-emi-reason").removeAttr("id");
                    jQuery(".delete-loan-emi-reason-error").text("");

                    //console.log('response= ', response);
                    if (response.success) {
                        swal("Success!", "Loan EMI deleted successfully!", "success").then(() => {
                            location.reload();
                        });

                    } else {
                        swal("Fail!", response.error);
                    }
                }
            });
            
        }
    }) 
});
function searchForm() {
    if ($('#filter').valid()) {
        $('#is_search').val("yes");
        const is_search = $('#is_search').val();
        const account_number = $('#account_number').val();
        const gDate = $('#gloDate').val();
        $('#filter_data').html('');
        $.post("{!! route('admin.loan.emi_record') !!}", {
                'is_search': is_search,
                'account_number': account_number,
                'gDate': gDate
            },function(response) {
                if (response.msg_type == 'success') {
                    $('#filter_data').html(response.view);
                } else {
                    swal("Warning!", "Loan EMI not generated!", "warning");
                    var account_number = $('#account_number').val('');
                }
            }
        )
    }
}
function resetForm() {
    $('#account_number').val('');
    $('#filter_data').html('');
}
</script>