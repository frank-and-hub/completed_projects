<script type="text/javascript">
$(document).on('keyup','#account_number',function(){
     $('#loan_branch_transferdetail').hide();
    $('#loan_branch_transferdetail').html(''); 
    var code = $(this).val();
	  //var type = $("#loan_id").val();
	
	
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.loan_brnachtransferdataGets') !!}",
              dataType: 'JSON',
              data: {'code':code,'type':'type'},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
              //  alert(response.msg_type);
                if(response.msg_type=="success")
                {
                  $('#loan_branch_transferdetail').show();
                  $('#loan_branch_transferdetail').html(response.view); 
                                    
                }
                else{

                  if(response.msg_type=="error_cleargoup")
                  {
                    $('#loan_branch_transferdetail').show();  
                    $('#loan_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>You can not transfer branches because in this group loan accounts are already cleared!</strong> </div>');
                  } 
                  else if(response.msg_type=="error_clear")
                  {
                    $('#loan_branch_transferdetail').show();  
                    $('#loan_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>You can not transfer branch because account has been already cleared!</strong> </div>');
                  }           
                  else 
                  {
                      $('#loan_branch_transferdetail').show();                 
                      $('#loan_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Account not found!</strong> </div>');                 
                  }

                  
                }
                
              }
          });
    } 
    
  });


 $('#filter').validate({
      rules: {
        account_number:{ 
            number : true,
            required: true,
			minlength:10,
			maxlength: 12,
          },
		   branch_id:{  
            required: true,
          },
		   loan_id:{ 
           
            required: true,
          },
      

      },
      messages: { 
        account_number: {
            required: "Please enter Account Number.",
            number: "Please enter  valid number.",
          },
		   branch_id: {
            required: "Please enter branch name.", 
          },
		   loan_id: {
            required: "Please enter loan type.",
            
          },
        
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
  });
 $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

    function resetForm()
    {
      var validator = $( "#filter" ).validate();
validator.resetForm();
        $('#loan_branch_transferdetail').hide();
        $('#account_number').val('');
       
       
        
    }
</script>