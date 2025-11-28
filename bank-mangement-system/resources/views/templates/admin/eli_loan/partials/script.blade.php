<script type="text/javascript">
    var EliLoan;
    $(document).ready(function(){
        $('#eli_loan').validate({
            rules:{
                title:{
                    required:true,
                },
            },
            messages:{
              title:{
                "required":"Please enter title.",
              },
            },
        })
        
        EliLoan = $('#eli_loan_listing').DataTable({
            processing:true,
            serverSide:true,
            pageLength:20,
            lengthMenu:[10,20,40,50,100],
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                    scrollTop: ($('#eli_loan_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
             ajax: {
                "url": "{!! route('admin.eli-loan.listing') !!}",
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
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action'},
              
            ],"ordering": false
        })
            $(EliLoan.table().container()).removeClass( 'form-inline' ); 
            
          $( document ).ajaxStart(function() { 
              $( ".loader" ).show();
           });
    
           $( document ).ajaxComplete(function() {
              $( ".loader" ).hide();
           });
    })
    
   function statusUpdate(id)
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
                  url: "{!! route('admin.update.status.eli-loan') !!}",
                  dataType: 'JSON',
                  data: {'id':id},
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) {
                 
                        if(response)
                        {
                            EliLoan.draw();
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