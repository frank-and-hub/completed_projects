<script type="text/javascript">

  $("#account_no").keypress(function (e){
	  var charCode = (e.which) ? e.which : e.keyCode;
	  if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
		return false;
	  }

	  
	}); 
  
  
  $("#new_associate").keypress(function (e){
	  var charCode = (e.which) ? e.which : e.keyCode;
	  if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
		return false;
	  }
	});   
$(document).on('keyup','#account_no',function(){
    $('#investment_detail').html('');
    $('.associate_changes').hide();
    $('#new_associate_detail').html('');
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.loancollectordataget') !!}",
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
                  $('#new_associate').val('');
                  $('#new_associate').show();
                  $('#new_associate_detail').html('');
                 
                }
                else 
                  { 
                    if(response.msg_type=="error_clear"){
                      $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>You can not change collector of clear loan plans!</strong> </div>');
                    }else{
                      $('#investment_detail').html('<div class="alert alert-danger alert-block">  <strong>Account not found!</strong> </div>');
                      $('.btncollector').attr("disabled", true);
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
              url: "{!! route('admin.getnewAssociteData') !!}",
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
                  $('.btncollector').removeAttr('disabled');
                  
                }
                else 
                {
                  //$('#new_associate_senior').val('');
                  //$('#old_code').html(code);
                  if(response.msg_type=="error1")
                  { 
                    $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Collector Inactive!</strong> </div>');
                  }
                  else if(response.msg_type=="error2")
                  { 
                    $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Collector Blocked!</strong> </div>');
                  }
                  else if(response.msg_type=="error3")
                  { 
                    $('#new_associate_detail').html("<div class='alert alert-danger alert-block'>  <strong>Collector must be greater than associate's carder </strong> </div>");
                  }
                  else
                  {
                    $('.btncollector').attr("disabled", true);
                    $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>Collector not found!</strong> </div>');
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
            minlength:10,
			      maxlength: 12
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
            required: "Please enter old collector code.",
            number: "Please enter  valid code.",
          },
          new_associate: {
            required: "Please enter new collector code.",
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
        $('#account_no').val('');
        $('#new_associate').hide(); 
        $('#new_associate_detail').hide();
        $('.associate_changes').hide();
        $('#investment_detail').html('');
        $('.btncollector').attr("disabled", true);
       
    }
</script>