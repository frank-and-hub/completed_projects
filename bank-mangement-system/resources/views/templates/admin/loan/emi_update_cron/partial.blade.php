<script type="text/javascript">

	$(document).on('change','#account_number',function(){
		$('#investment_detail').html('');
		$('.associate_changes').hide();
		$('#new_associate_detail').html('');
		var code = $(this).val();
		if (code!='') {
		$.ajax({ 
				type: "POST",  
				url: "{!! route('admin.emi.account.details') !!}",
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
					$('#type').val(response.loan_type);
					$('#etype').val(response.ecs_type);
					$('#sanction_date').val(response.approve_date);
					$('#emi_due_date').val(response.emi_due_date);
					$('#emi_amount').val(response.emi_amount);
					curDate = $('.create_application_date').val();
						console.log(curDate);
						$('#errDate').datepicker({
							format: "dd/mm/yyyy",
							endDateHighlight: true,
							endDate: curDate,
							autoclose: true,
							startDate: response.approve_date,
						}).on("change",function(){
							var startDate=$("#errDate").val();
							$('#date').datepicker({
							format: "dd/mm/yyyy",
							endDateHighlight: true,
							endDate: curDate,
							autoclose: true,
							startDate: startDate,
						})
						});
						
					}
					else if(response.msg_type=="error")
					{ 
						swal("Warning!", "Account no. does not exist!", "warning");
						$("#account_number").val('');
					}
					else if(response.msg_type=="error_clear")
					{ 
						swal("Warning!", "You cannot change the details of clear accounts!", "warning");
						$("#account_number").val('');
					}
				}
			});
		}    
	});

	$("#filter").validate({
        rules: {
            account_number:"required",
            date:"required",
            errDate:"required",

        },
        messages: {
			account_number:"Enter account number",
			date:"Please select date",
			errDate:"Please select Error date",
        },
        submitHandler: function(form) {
            $("#submitBtn").prop("disabled", true);
            form.submit();
        }
    });
</script>