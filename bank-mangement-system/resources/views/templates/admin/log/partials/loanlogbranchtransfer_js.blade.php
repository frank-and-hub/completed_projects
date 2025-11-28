<script type="text/javascript">
$(document).on('keyup','#account_number',function(){
     $('#loan_branch_transferdetail').hide();
    $('#loan_branch_transferdetail').html(''); 
    var code = $(this).val();
	  //var type = $("#loan_id").val();
	
	
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.loan_brnachtransferdataLogGets') !!}",
              dataType: 'JSON',
              data: {'code':code},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#loan_branch_transferdetail').show();
                  $('#loan_branch_transferdetail').html(response.view);                 
                  
                  
                }
                else
                {
                  $('#loan_branch_transferdetail').show();
                  if(response.msg_type=="error1")
                  { 
                    $('#loan_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>loan Inactive!</strong> </div>');
                  }
                  else if(response.msg_type=="error2")
                  { 
                    $('#loan_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>loan Blocked!</strong> </div>');
                  }
                  else
                  {
                  
                  $('#loan_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>loan not found!</strong> </div>');
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
			minlength:8,
			maxlength: 12,
          },
		  

      },
      messages: { 
        account_number: {
            required: "Please enter associate code.",
            number: "Please enter  valid code.",
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