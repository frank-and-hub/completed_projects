<script type="text/javascript">

  $('.emi').hide();
  $('.emiDueDate').hide();

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
	
	
	$(document).on('change','#account_no',function(){
		$('#investment_detail').html('');
		$('.associate_changes').hide();
		$('#new_associate_detail').html('');
		var code = $(this).val();
		if (code!='') {
		$.ajax({
				type: "POST",  
				url: "{!! route('admin.loan-data-get') !!}",
				dataType: 'JSON',
				data: {'code':code},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) { 
					if(response.msg_type=="success")
					{
						console.log(response);
					$('#investment_detail').show();
					$('#investment_detail').html(response.view);                 
					$('.associate_changes').show();
					$('#new_associate').val('');
					$('#new_associate').show();
					$('#new_associate_detail').html('');
					$('#type').val(response.loan_type);
					$('#etype').val(response.ecs_type);
					$('#sanction_date').val(response.approve_date);
					$('#emi_due_date').val(response.emi_due_date);
					$('#emi_amount').val(response.emi_amount);
					}
					else if(response.msg_type=="error")
					{ 
						swal("Warning!", "Account no. does not exist!", "warning");
						$("#account_no").val('');
					}
					else if(response.msg_type=="error_clear")
					{ 
						swal("Warning!", "You cannot change the details of clear accounts!", "warning");
						$("#account_no").val('');
					}
				}
			});
		}    
	});

	$(document).on('change','#change_type',function(){
		
		var type= $(this).val();
		if (type == ''){
			$('.emi').hide();
  			$('.emiDueDate').hide();
			$('#emi').val('');
  			$('#emiDueDate').val('');
		}else if(type == 1){
			$('.emi').hide();
			$('#emi').val('');
  			$('.emiDueDate').show();
		}else{
			$('.emi').show();
  			$('.emiDueDate').hide();
  			$('#emiDueDate').val('');

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
			
			ecs_ref_no: {
				requiredWithEcsType: true,
				ecsRefNoValidation:true
			},
			remark:{
				required: true,
			},
			change_type:{
				required: true,
			},
			emiDueDate:{
				required: true,
			},
			emi:{
				required: true,
				number:true,
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
			remark:{
				required: "Please enter update remark.",
			},
			change_type: "Please select type."	,
			emiDueDate: "Please select emi due date",
			emi: {
				required:"Please enter emi amount",	  
				number:"Please enter valid amount",
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

	$("#emiDueDate").hover(function () {
        var approveDate = $('#sanction_date').val();
        $('#emiDueDate').datepicker({
            format: "dd/mm/yyyy",
            endDateHighlight: true,
            // endDate: $('.create_application_date').val(),
            autoclose: true,
            startDate: $('.create_application_date').val(),
        });
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
			// $('.btncollector').attr("disabled", true);
		
	}
</script>