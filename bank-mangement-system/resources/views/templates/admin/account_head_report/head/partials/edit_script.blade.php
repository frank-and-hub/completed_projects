<script type="text/javascript">
    $(document).ready(function(){
        
        $('#head').validate({
            rules:{
                new_head:{
                    required:true,
                },
				head1:{
					required:true,
				},
            },
            messages:{
              new_head:{
                "required":"Please enter title."
              },
			  head1:{
                "required":"Please Select Head1."
              },
            },
        })
         $('#head1').on('change',function(){

    	var head_id = $(this).val(); 
    	$.ajax({
                type: "POST",  
                url: "{!! route('admin.get.child_head') !!}",
                dataType: 'JSON',
                data: {'child_asset_id':head_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) { 
				
                     $('#head2').find('option').remove();
                     $('#head2').append('<option value="">---Select Child SubHead---</option>');
                     $('#head3').find('option').remove();
                     $('#head3').append('<option value="">---Select Child SubHead---</option>');
                     $('#head4').find('option').remove();
                     $('#head4').append('<option value="">---Select Child SubHead---</option>');
                        $.each(response.sub_child_assets, function (index, value) { 
                        $("#head2").append("<option value='"+value.head_id+"'>"+value.sub_head+"</option>");
                });     
                }
            });
   })
		$('#head2').on('change',function(){

    	var head_id = $(this).val(); 
    	$.ajax({
                type: "POST",  
                url: "{!! route('admin.get.child_head') !!}",
                dataType: 'JSON',
                data: {'child_asset_id':head_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) { 
				 console.log(response);
                     $('#head3').find('option').remove();
                     $('#head3').append('<option value="">---Select Child SubHead---</option>');
                       $('#head4').find('option').remove();
                     $('#head4').append('<option value="">---Select Child SubHead---</option>');
                        $.each(response.sub_child_assets, function (index, value) { 
                        $("#head3").append("<option value='"+value.head_id+"'>"+value.sub_head+"</option>");
                });     
                }
            });
   })
   
   $('#head3').on('change',function(){

    	var head_id = $(this).val(); 
    	$.ajax({
                type: "POST",  
                url: "{!! route('admin.get.child_head') !!}",
                dataType: 'JSON',
                data: {'child_asset_id':head_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) { 
				 console.log(response);
                     $('#head4').find('option').remove();
                     $('#head4').append('<option value="">---Select Child SubHead---</option>');
                        $.each(response.sub_child_assets, function (index, value) { 
                        $("#head4").append("<option value='"+value.head_id+"'>"+value.sub_head+"</option>");
                });     
                }
            });
   })

   $('.subhead').on('click',function(){
      var val = $(this).attr('data-value');
    
      
      $('.'+val+'-icon').toggleClass('fas fa-angle-down fas fa-angle-up');
     
    
      $('.'+val+'-child_head').toggle();
      $('.head3').hide();
      
       
      
      
     

   });
    $('.child_head').on('click',function(){


      var val = $(this).attr('data-value');
      
      $('.'+val+'-sub_child_head').toggle();
      $('.'+val+'-icon').toggleClass('fas fa-angle-up fas fa-angle-down ');
       var val2 = $('.'+val+'-sub_child_head').attr('data-value');
       $('.'+val2+'-sub_child_head2').hide();
       var val3 = $('.'+val2+'-sub_child_head2').attr('data-value');
       // alert(val3);
       $('.'+val3+'-head5').hide();

    
     
   });
    $('.sub_child_head').on('click',function(){
       var val = $(this).attr('data-value');
       
      $('.'+val+'-sub_child_head2').toggle();
      $('.'+val+'-icon').toggleClass('fas fa-angle-up fas fa-angle-down');
      var val2 = $('.'+val+'-sub_child_head2').attr('data-value');
    
      
      $('.'+val2+'-head5').hide();
       
     
   });
    $('.sub_child_head2').on('click',function(){
       var val = $(this).attr('data-value');
      $('.'+val+'-head5').toggle();
      $('.'+val+'-icon').toggleClass('fas fa-angle-up fas fa-angle-down');
     
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