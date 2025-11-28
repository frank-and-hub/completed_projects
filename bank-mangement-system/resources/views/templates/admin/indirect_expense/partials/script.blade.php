<script type="text/javascript">
    $(document).ready(function(){
        
        $('#indirect_expense').validate({
            rules:{
                title:{
                    required:true,
                },
            },
            messages:{
              title:{
                "required":"Please enter title."
              },
            },
        })
         $('#child_indirect_expense').on('change',function(){

    	var child_asset_id = $(this).val(); 
    	$.ajax({
                type: "POST",  
                url: "{!! route('admin.get.child_asset') !!}",
                dataType: 'JSON',
                data: {'child_asset_id':child_asset_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) { 
                     $('#sub_child_indirect_expense').find('option').remove();
                     $('#sub_child_indirect_expense').append('<option value="">Select Expense Type</option>');
                        $.each(response.sub_child_assets, function (index, value) { 
                        $("#sub_child_indirect_expense").append("<option value='"+value.head_id+"'>"+value.sub_head+"</option>");
                });     
                }
            });
   })
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
                  url: "{!! route('admin.update.status.indirect_expense') !!}",
                  dataType: 'JSON',
                  data: {'id':id},
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) {
                   
                        if(response)
                        {
                           
                            swal("Success", "Update Status successfully!", "success");
                             location.reload();
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