<script type="text/javascript">
	$(document).ready(function() {
		//var date = $('#create_application_date').val();

		//var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

		// $("#date").hover(function() {

		// 	//var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

		// 	$('#date').datepicker({
		// 		format: "dd/mm/yyyy",
		// 		endHighlight: true,
		// 		autoclose: true,
		// 		endDate: date,
		// 		startDate: '01/04/2021',

		// 	})
		// })

		$.validator.addMethod("zero", function(value, element, p) {
			if (value >= 0) {
				$.validator.messages.zero = "";
				result = true;
			} else {
				$.validator.messages.zero = "Amount must be greater than or equal to 0.";
				result = false;
			}

			return result;
		}, "");


		$.validator.addMethod("decimal", function(value, element, p) {
			if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
				$.validator.messages.decimal = "";
				result = true;
			} else {
				$.validator.messages.decimal = "Please Enter valid numeric number.";
				result = false;
			}

			return result;
		}, "");



		$('#bank').on('change', function(selected_account) {
			$('#bank_balance').val('0.00');
			// $('#date').val('');
			var bank_id = $(this).val();

			$.ajax({
				type: "POST",
				url: "{!! route('admin.bank_account_list.inactive') !!}",
				dataType: 'JSON',
				data: {
					'bank_id': bank_id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {

					$('#bank_account').find('option').remove();
					$('#bank_account').append('<option value="">Select account number</option>');
					$.each(response.account, function(index, value) {
						$("#bank_account").append("<option value='" + value.id + "'>" + value.account_no + "</option>");
					});
				}
			});
		})


		$.validator.addMethod("chkAmount", function(value, element, p) {

			if (parseFloat($('#bank_balance').val()) >= parseFloat($('#amount').val())) {
				$.validator.messages.chkAmount = "";
				result = true;
			} else {
				$.validator.messages.chkAmount = "Bank available balance  must be grather than or equal to  amount";
				result = false;
			}

			return result;
		}, "");


		$('#brs_bank_charge').validate({
			rules: {
				bank: {
					required: true,
				},
				bank_account: {
					required: true,
				},
				company_id: {
					required: true,
				},
				date: {
					required: true,
				},
				bank_charge: {
					required: true,
				},
				description: {
					required: true,
				},
				amount: {
					required: true,
					decimal: true,
					zero: true,
					chkAmount: true,
				},
				bank_balance: {
					required: true,
					decimal: true,
					zero: true,
					//chkAmount:true,
				},
			},
			messages: {
				bank: {
					required: "Please Select Bank",
				},
				bank_account: {
					required: "Please Select Bank Account",
				},
				company_id: {
					required: "Please Select Company Name",
				},
				date: {
					required: "Please Select Date",
				},
				bank_charge: {
					required: "Please Select Bank Charge",
				},
				description: {
					required: "Please Enter the Description",
				},
				amount: {
					required: "Please Enter   Amount",
				},
				bank_balance: {
					required: "Please Enter   Amount",
				},

			}
		})

		$(document).ajaxStart(function() {
			$(".loader").show();
		});

		$(document).ajaxComplete(function() {
			$(".loader").hide();
		});

		// $(document).on('change', '#date', function() {
		// 	// $('#bank_balance').val('');
		// 	// $('#bank_account').val('');
		// 	// $('#bank').val('');
		// });


		$(document).on('change', '#bank_account', function() {
			$('#bank_balance').val('0.00');
			var account_id = $('#bank_account').val();
			var companyId = $('#company_id').val();
			var bank_id = $('#bank').val();
			var entrydate = $('#date').val();
			$.ajax({
				type: "POST",
				url: "{!! route('admin.bankChkbalance') !!}",
				dataType: 'JSON',
				data: {
					'account_id': account_id,
					'bank_id': bank_id,
					'entrydate': entrydate,
					'company_id': companyId
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#bank_balance').val(response.balance);
				}
			});
		});
		$(document).on('change', '#date', function() {
			$('#bank_balance').val('0.00');
			var account_id = $('#bank_account').val();
			var companyId = $('#company_id').val();
			var bank_id = $('#bank').val();
			var entrydate = $('#date').val();
			$.ajax({
				type: "POST",
				url: "{!! route('admin.bankChkbalance') !!}",
				dataType: 'JSON',
				data: {
					'account_id': account_id,
					'bank_id': bank_id,
					'entrydate': entrydate,
					'company_id': companyId
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#bank_balance').val(response.balance);
				}
			});
		});
		$('#company_id').on('change', function() {
			let companyId = $('#company_id option:selected').val();
			var bank_id = $(this).val();
			$('#bank_account').val('');
			$.ajax({
				type: "POST",
				url: "{{ route('admin.fetchbranchbycompanyBank') }}",
				data: {
					'company_id': companyId,
					'bank': 'true',
					'branch': 'no',
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					let myObj = JSON.parse(response);
					if (myObj.bank) {
						var optionBank =
							`<option value="">----Please Select Bank---</option>`;
						myObj.bank.forEach(element => {
							optionBank +=
								`<option value="${element.id}">${element.bank_name}</option>`;
						});
						$('#bank').html(optionBank);
					}
				}
			})
		});
		$('#company_id').on('change', function() {
			$('#bank_balance').val('0.00');
			var company_id = $(this).val();
			$.ajax({
				type: "POST",
				url: "{{route('admin.brs.companydate')}}",
				dataType: 'JSON',
				data: {
					'company_id': company_id,
				},
				success: function(response) {
					$('.company_register_date').val(response);
					
				}
			});
		});
		$("#date").hover(function() {
            var edate = $('#create_application_date').val();
            var Sdate = $('.company_register_date').val();
        $('#date').datepicker({
            format: "dd/mm/yyyy",
            // todayHighlight: true,
            autoclose: true,
        });
        $('#date').datepicker('setStartDate', Sdate);
        $('#date').datepicker('setEndDate', edate);
        });



		


		$('#company_id').on('change', function() {
            var id = 92;
			var company_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
					'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#bank_charge').find('option').remove();
                    $('#bank_charge').append('<option value="">Select Bank Charge</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#bank_charge").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });

                }
            })
        })



	});
</script>