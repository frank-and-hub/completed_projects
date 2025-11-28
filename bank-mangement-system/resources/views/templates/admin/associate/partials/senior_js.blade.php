<script type="text/javascript">
$(document).on('keyup','#associate_code',function(){
     $('#associate_detail').hide();
    $('#associate_detail').html(''); 
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.associter_dataGet') !!}",
              dataType: 'JSON',
              data: {'code':code,'type':'senior'},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#associate_detail').show();
                  $('#associate_detail').html(response.view);                 
                  $('.associate_changes').show();
                  
                }
                else
                {
                  $('#associate_detail').show();
                  if(response.msg_type=="error1")
                  { 
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate Inactive!</strong> </div>');
                  }
                  else if(response.msg_type=="error2")
                  { 
                    $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate Blocked!</strong> </div>');
                  }
                  else
                  {
                  
                  $('#associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Associate not found!</strong> </div>');
                }
                }
              }
          });
    } 
    
  });


$(document).on('keyup','#new_associate_senior',function(){
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
        associate_code:{ 
            number : true,
            required: true,
          },
          old_senior_code:{ 
            number : true,
            required: true,
          },
          new_associate_senior:{ 
            number : true,
            required: true,
          },
          new_associate_senior:{ 
            number : true,
            required: true,
          },
          new_senior_chk:{  
            required: true,
          },

      },
      messages: { 
        associate_code: {
            required: "Please enter associate code.",
            number: "Please enter  valid code.",
          },
        old_senior_code: {
            required: "Please enter old senior code.",
            number: "Please enter  valid code.",
          },
          new_associate_senior: {
            required: "Please enter new senior code.",
            number: "Please enter  valid code.",
          },
          new_senior_chk: {
            required: "New senior detail not showing", 
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
        $('#associate_detail').hide();
        $('#associate_code').val('');
        $('#new_associate_senior').val(''); 
        $('#new_associate_detail').hide();
        $('.associate_changes').hide();
    }
</script>