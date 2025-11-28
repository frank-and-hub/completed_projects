<script type="text/javascript">
$(document).on('keyup','#account_no',function(){
    $('#investment_detail').html('');
    $('.associate_changes').hide();
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.investmentBranchtransferDataGet') !!}",
              dataType: 'JSON',
              data: {'code':code},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#investment_detail').show();
                  $('#investment_detail').html(response.view);                 
                 
                  
                }
                else
                {  

                  if(response.msg_type=="error_mature")
                  { 
                    $('#investment_detail').html('<div class="alert alert-danger alert-block">  <strong>You can not transfer branch because account has been already matured!</strong> </div>');
                  }
                  else 
                  { 
                    $('#investment_detail').html('<div class="alert alert-danger alert-block">  <strong>Investment not found!</strong> </div>');
                  }

                    
                   
                }
              }
          });
    } 
    
  });

 $('#filter').validate({
      rules: {
        account_no:{ 
            // number : true,
            required: true,
			minlength:12,
			maxlength: 14,
          },
		   branch_id:{ 
            number : true,
            required: true,
          },
      

      },
      messages: { 
        account_no: {
            required: "Please enter account no.",
            number: "Please enter  valid code.",
          },
		   branch_id: {
            required: "Please enter branch name.",
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
        $('#investment_detail').hide();
        $('#new_associate').hide(); 
        $('#new_associate_detail').hide();
         $('#account_no').val('');
    }
	
	
	
	
 
</script>