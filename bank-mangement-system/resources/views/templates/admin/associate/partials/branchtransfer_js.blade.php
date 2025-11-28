<script type="text/javascript">
$(document).on('keyup','#associate_code',function(){
     $('#associate_branch_transferdetail').hide();
    $('#associate_branch_transferdetail').html(''); 
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.associter_brnachtransferdataGets') !!}",
              dataType: 'JSON',
              data: {'code':code,'type':'senior'},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#associate_branch_transferdetail').show();
                  $('#associate_branch_transferdetail').html(response.view);                 
                  
                  
                }
                else
                {
                  $('#associate_branch_transferdetail').show();
                  if(response.msg_type=="error1")
                  { 
                    $('#associate_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>You can not transfer branch  because associate is  inactive!</strong> </div>');
                  }
                  else if(response.msg_type=="error2")
                  { 
                    $('#associate_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Associate Blocked!</strong> </div>');
                  }
                  else
                  {
                  
                  $('#associate_branch_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Associate not found!</strong> </div>');
                }
                }
              }
          });
    } 
    
  });


$('#filter').validate({
      rules: {
        associate_code:{ 
            number : true,
            required: true,			
			minlength:12,
			maxlength: 12,
          },
		   branch_id:{ 
            number : true,
            required: true,
          },
      

      },
      messages: { 
        associate_code: {
            required: "Please enter associate code.",
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
        $('#associate_branch_transferdetail').hide();
        $('#associate_code').val('');
       
        $('#new_associate_detail').hide();
        
    }
</script>