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
				console.log(response);
                if(response.msg_type=="success")
                {
                  $('#investment_detail').show();
                  $('#investment_detail').html(response.view);                 
                  $('.associate_changes').show();
                  $('#new_associate').val('');
                  $('#new_associate').show();
                  $('#new_associate_detail').html('');
				  $('#type').val(response.loan_type);
				  $('#etype').val(response.ecs_type);

				  
				  @if($title == 'Loan Update | Ecs Type Change')
					var selectEcsType = $('select[name="ecs_type"]');
					var ecsTypeOptions = '<option value=""  data-ref="" >Select ECS Type</option>';

					if (response.ecs_type == '1') {
						console.error(response.ecs_type);
						ecsTypeOptions += '<option value="2" data-ref="' + response.ecs_ref_no + '">SSB</option><option value="0" data-ref="">ECS Unregister</option>';
					} else if (response.ecs_type == '2') {
						console.error(response.ecs_type);
						ecsTypeOptions += '<option value="1" data-ref="">Bank</option><option value="0" data-ref="">ECS Unregister</option>';
					} else {
						ecsTypeOptions += '<option value="2" data-ref="' + response.ecs_ref_no + '">SSB</option><option value="1" data-ref="">Bank</option>';
					}
					selectEcsType.html(ecsTypeOptions);
					
                 @endif			 
                }
                else
                { 
					@if($title==='Loan Update | Ecs Type Change')
                    if(response.msg_type=="error_clear"){
                      $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>You can not change ECS Type of clear loan Account !</strong> </div>');
                    }
					@else
					if(response.msg_type=="error_clear"){
					  $('#new_associate_detail').html('<div class="alert alert-danger alert-block">  <strong>You can not change collector of clear loan Account !</strong> </div>');
					}
					@endif
					else
					{
                      $('#investment_detail').html('<div class="alert alert-danger alert-block">  <strong>Account not found!</strong> </div>');
                      $('.btncollector').attr("disabled", true);
                    }
                  }
              }
          });
    }    
  });

$('select[name="ecs_type"]').on('change', function() {
    let ecstype = $(this).find('option:selected').data('ref');
	let ecstypev = $(this).find('option:selected').val();
	
    if (ecstypev !== '') {
		if(ecstype !== ''){
			$('#ecs_ref_no').val(ecstype).prop('readonly',true);
		}else{
			$('#ecs_ref_no').val(ecstype).prop('readonly',false);
		}        
		$('.btncollector').attr("disabled", false);
    } else {
        $('#ecs_ref_no').val('');
		$('.btncollector').attr("disabled", true);
    }
});
$(document).on('blur' , '#ecs_ref_no',function(){        
	var refNo = $('#ecs_ref_no').val();
	var loanType = $('#type').val();
	$.ajax({
		type: "POST",  
		url: "{{route('ecs.refNo.exist')}}",  
		data: {
			'refNo': refNo,'loanType':loanType,
		},
		async: false,		
		success: function (response) {
			console.log(response);
			if(response == 1){
				swal('Warning','Reference Number Already Exist!','warning');
				$('#ecs_ref_no').val('');
			}
		}
	});
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
$.validator.addMethod("requiredWithEcsType", function(value, element) {
	var selectedOption = $('select[name="ecs_type"]').val();
	return (selectedOption && selectedOption !== "");
}, "Please select an ECS type.");

$.validator.addMethod("ecsRefNoValidation", function(value, element) {
	var selectedOption = $('select[name="ecs_type"]').val();
	return (selectedOption && selectedOption !== "" && selectedOption == '1') ? /^[A-Za-z]{4}\d{16}$/.test(value) : true;
}, "Please enter a valid format like 'PUNB7021602245001957' when ECS type is selected.");
	
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
		  @if($title == 'Loan Update | Ecs Type Change')
		  ecs_ref_no: {
			requiredWithEcsType: true,
			ecsRefNoValidation:true
		  },
		  ecs_remark:{
			 required: true,
		  },
		  ecs_type:{
			  required: true,
		  }
		  @else
		  new_associate:{  
			required: true,
	      },
		  @endif
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
		ecs_remark:{
			required: "Please enter ECS update remark.",
		}		  
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
		$('#new_associate, #new_associate_detail, .associate_changes').hide();
		$('#investment_detail').empty();
		$('select[name="ecs_remark"], input[name="ecs_remark"], input[name="ecs_ref_no"] , input[name="account_no"]').val('');
        $('.btncollector').attr("disabled", true);
       
    }
</script>