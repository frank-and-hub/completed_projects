<script type="text/javascript">
$(document).on('keyup','#account_no',function(){
    $('#investment_detail').html('');
    $('.associate_changes').hide();
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.investmentDataGet') !!}",
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
                  $('.associate_changes').show();
                  
                }
                else
                {  

                  if(response.msg_type=="error1")
                  { 
                    $('#investment_detail').html('<div class="alert alert-danger alert-block">  <strong>You enter SSb account number.Enter any other plan account number!</strong> </div>');
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


$(document).on('keyup','#new_associate',function(){
    $('#new_associate_detail').html('');
    $('#new_senior_chk').val('')
    $('#old_code').html('');
    var code = $(this).val();
    var carder = $('#associate_carder').val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.associterSeniorDataGet') !!}",
              dataType: 'JSON',
              data: {'code':code,'carder':carder},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#new_senior_chk').val(1)
                  $('#new_associate_detail').show();
                  $('#new_associate_detail').html(response.view);                 
                  
                  
                }
                else 
                {
                  //$('#new_associate_senior').val('');
                  //$('#old_code').html(code);
                  if(response.msg_type=="error1")
                  { 
                    $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Senior associate Inactive!</strong> </div>');
                  }
                  else if(response.msg_type=="error2")
                  { 
                    $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Senior associate Blocked!</strong> </div>');
                  }
                  else if(response.msg_type=="error3")
                  { 
                    $('#new_associate_detail').html("<div class='alert alert-danger alert-block'>  <strong>Senior associate's carder must be greater than associate's carder </strong> </div>");
                  }
                  else
                  {
                  
                    $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Senior associate not found!</strong> </div>');
                  }
                }
              }
          });
    } 
    
  });

$('#filter').validate({
      rules: {
        account_no:{  
            required: true,
          },
          old_associate_code:{  
            required: true,
          },
          new_associate:{  
            required: true,
          }, 

      },
      messages: { 
        associate_code: {
            required: "Please enter account number.", 
          },
        old_associate_code: {
            required: "Please enter old senior code.",
            number: "Please enter  valid code.",
          },
          new_associate: {
            required: "Please enter new senior code.",
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
        $('#new_associate').hide(); 
        $('#new_associate_detail').hide();
        $('.associate_changes').hide();
    }
</script>