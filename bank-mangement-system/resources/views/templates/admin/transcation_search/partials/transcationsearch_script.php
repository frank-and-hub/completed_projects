


<script type="text/javascript">
$(document).ready(function () {



$('#filter').validate({
      rules: {
         
          transcation_id: "required", 
		  plan_type: "required", 
          

      },
      messages: {         
          transcation_id: {
            required: "Please enter  Transcation id.",
          }, 
          plan_type: {
            required: "Please enter plan type.",
          },
          
      },
      errorElement: 'label',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
  });

		
    $("#ssbac_detail").hide();

    $("#filter").on('submit',function(){
    
        event.preventDefault();
        var form_data= $('#filter').serialize();

		 $.ajax({
			
			  type: "POST",  
			  url: "/admin/transcationdetailssearch",
			  dataType: 'JSON',
			  data:form_data, 
			  headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  },
			  success: function(response) {
				  
				  if(response!='')
				  {
				 
			       $("#ssbac_detail").show();
					if(response.msg_type == "success"){
						 $('#ssbac_detail').html(response.view); 
					} else {
						 $("#ssbac_detail").hide();
				
						$('#select_type').val('');
						$('#transcation_id').val('');	
				  
						swal("Warning!", response.message, "warning");
						return false;
					}
				  }
				  
				}
			  });




		
			 
		
    });
	
	$('#select_type').on('change',function(){
		
		 $("#ssbac_detail").hide();
		  $('#transcation_id').val('');
	})
	   $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

  });
  	function resetForm()
    {
		   $("#ssbac_detail").hide();
        var form = $("#filter"),
        validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
		
        $('#select_type').val('');
		
        $('#transcation_id').val('');
        
        
    }
  
  
  
</script>