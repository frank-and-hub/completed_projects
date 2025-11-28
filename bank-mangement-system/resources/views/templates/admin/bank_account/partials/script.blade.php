<script type="text/javascript">
    var bank_account_listing;
    $(document).ready(function(){
        $('#bank_account').validate({
            rules:{
                bank_name:{
                    required:true,
                },
                branch_name:{
                    required:true,
                },
                account_number:{
                    required:true,
                    number:true,
                    minlength: 8,
                    maxlength: 16
                },
                ifsc:{
                    required:true,
                    checkIfsc:true,
                },
                address:{
                    required:true,
                },
            },
            messages:{
                bank_name:{
                    "required":"Please enter bank name."
                },
                 branch_name:{
                  "required":"Please enter branch name.",
                },
                account_number:{
                    "required":"Please enter account number.",
                },
                ifsc:{
                    "required":"Please enter ifsc code.",
                },
                address:{
                    "required":"Please enter address.",
                }
            }
        })
      
      bank_account_listing = $('#bank_account_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                    scrollTop: ($('#bank_account_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.bank_account.report.listing') !!}",
                "type": "POST",
                "data":function(d) {d.searchform=$('form#filter').serializeArray()},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                {data: 'bank_name', name: 'bank_name'},
                {data: 'branch_name', name: 'branch_name'},
                {data: 'account_number', name: 'account_number'},
                {data: 'ifsc_code', name: 'ifsc_code'},
                {data: 'address', name: 'address'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action'},
             
            ],"ordering": false,
        });
        $(bank_account_listing.table().container()).removeClass( 'form-inline' );   
         $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

        $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
    })
    
    function statusUpdate(headid,id,bank_id)
    {
        
        swal({
            title: "Are you sure?",
            text: "Do want to Change Status?",  
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
                  url: "{!! route('admin.update.status.bank_account') !!}",
                  dataType: 'JSON',
                  data: {'headid':headid,'id':id,'bankId':bank_id},
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) {
                  
                        if(response)
                        {
                            bank_account_listing.draw();
                            swal("Success", "Update Status successfully!", "success");
                        }
                        else
                        {
                              swal("Error", "Something went wrong.Try again!", "warning");
                        }
                    }
                  });
            }
          });
}

</script>