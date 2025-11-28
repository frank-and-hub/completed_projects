<script type="text/javascript">
	var expense;


	$("#submit").on('click', function() {
		$('#form').submit();
		$(this).attr('disabled');
	});


	$(document).ready(function() {

		var id = $('#id').val();
		$('#Advance_request').DataTable({
			processing: true,
			serverSide: true,
			searching: false,
			shorting: false,
			ordering: false,
			language: {
				infoFiltered: ''
			},
			pageLength: 20,
			lengthMenu: [10, 20, 40, 50, 100],
			"fnRowCallback": function(nRow, aData, iDisplayIndex) {
				var oSettings = this.fnSettings();
				$('html, body').stop().animate({
					scrollTop: ($('#Advance_request').offset().top)
				}, 1000);
				$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
				return nRow;
			},
			ajax: {
				"url": "{!! route('branch.advancePayment.AdjListingtable') !!}",
				"type": "POST",
				"data": {
					'id': id,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			},
			columns: [{
					data: 'Sno',
				},

				{
					data: 'account_head',
				},

				{
					data: 'sub_head1name',
				},

				{
					data: 'sub_head2name',
				},

				{
					data: 'sub_head3name',
				},

				{
					data: 'sub_head4name',
				},

				{
					data: 'sub_head5name',
				},

				{
					data: 'amount',
				},

				{
					data: 'description',
				},

				{
					data: 'image',
				},

				{
					data: 'date',
				},

			]
		});

		// Add date picker to adjestmetnt date or  append rows

		const adjdate2 = $("form#adjdate").text();
		$("form#adjdate").val(adjdate2);

		const adjdate = $("#date").val();
		var dateString = adjdate;
		var parts = dateString.split("/");
		var year = parts[2];
		var month = parts[1];
		var day = parts[0];
		var isoDate = year + "/" + month + "/" + day;

		var dd = new Date(isoDate);
		// Adjestment date Picker 
		$('#adjdate').datepicker({
			format: "dd/mm/yyyy",
			orientation: "bottom",
			autoclose: true,
			startDate: dd,
			minDate: 0

		});

		// Initialize the date picker on all instances of .date_more
		$(document).on('focus', '.date_more', function() {
			var date11 = $('#create_application_date').val();
			$(this).datepicker({
				format: "dd/mm/yyyy",
				endHighlight: true,
				autoclose: true,
				orientation: "bottom",
				endDate: date11
			});
		});




		// Using for date selection date Picker code
		$("#adjestmentdate").on('mouseover', function() {
			var today = $('.date').val();

			console.log(today);

			$('#adjestmentdate').datepicker({
				format: "dd/mm/yyyy",
				orientation: "bottom",
				autoclose: true,
				endDate: today,
				// maxDate: today,
				startDate: '01/04/2021',
				minDate: 0

			})


		})



		//   
		let Oid = '';
		$('.od_hide').hide();
		var today = new Date();
		$('#adjdate').datepicker({
			format: "dd/mm/yyyy",
			orientation: "bottom",
			autoclose: true,
			endDate: "today",
			maxDate: today,
			startDate: '01/04/2021',

		})
		var date = new Date();
		$('#start_date').datepicker({
			format: "dd/mm/yyyy",
			orientation: "bottom",
			autoclose: true

		});

		$('#end_date').datepicker({
			format: "dd/mm/yyyy",
			orientation: "bottom",
			autoclose: true

		});

		$("#bill_date").hover(function() {
			var date = $('#create_application_date').val();
			$('#bill_date').datepicker({
				format: "dd/mm/yyyy",
				endHighlight: true,
				autoclose: true,
				orientation: "bottom",
				endDate: date,
				startDate: '01/04/2021',


			})
		})



		$('#branch_id').on('change', function() {

			var branch_id = $('#branch_id').val();
			$('#branch_total_balance').val('0.00');
			if (branch_id > 0) {
				var entrydate = $('#adjdate').val();
				if (entrydate == '') {
					swal("Warning!", "Please select  payment date", "warning");
					$('#branch_total_balance').val('0.00');
				} else {
					$.ajax({
						type: "POST",
						url: "{!! route('branch.branchBankBalanceAmount') !!}",
						dataType: 'JSON',
						data: {
							'branch_id': branch_id,
							'entrydate': $('#created_at').val()
						},
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						success: function(response) {
							// alert(response.balance);
							$('#branch_total_balance').val(response.balance);
						}
					});
				}
			}
		})


		$.validator.addMethod("amountcheck", function(value, element, p) {



			if (parseFloat($('#branch_total_balance').val()) >= parseFloat($('#total_amount').val())) {
				$.validator.messages.amountcheck = "";
				result = true;
			} else if (parseFloat($('#bank_balance').val()) >= parseFloat($('#total_amount').val())) {
				$.validator.messages.amountcheck = "";
				result = true;
			} else {

				if ($('#branch_total_balance').val() > 0) {
					$.validator.messages.amountcheck = "";
					$.validator.messages.amountcheck = "Balance must be greater than or equal to total amount";
				}
				if ($('#bank_balance').val() > 0) {
					$.validator.messages.amountcheck = "";
					$.validator.messages.amountcheck = "Bank Balance must be greater than or equal to total amount";
				}

				result = false;
			}
			// }



			return result;

		}, "");


		$.validator.addMethod('lettersOnly', function(value, e) {
			return this.optional(e) || /^[a-z ]+$/i.test(value);
		}, "Please Enter Letter Only");

		$.validator.addMethod("approveAmountLessThanTotal", function(value, element, p) {
			const approveAmount = $('[name=approveAmount]').val();
			const total = $('[name=total_amount]').val();
			if (parseInt(approveAmount) <= parseInt(total)) {
				$.validator.messages.fa_code = "The approved amount must be less than or equal to the total amount.";
				result = false;
			} else {
				result = true;
			}
			return result;
		}, "");



		// A function to turn all form data into a jquery object
		jQuery.fn.serializeObject = function() {
			var o = {};
			var a = this.serializeArray();
			jQuery.each(a, function() {
				if (o[this.name] !== undefined) {
					if (!o[this.name].push) {
						o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				} else {
					o[this.name] = this.value || '';
				}
			});
			return o;
		};

		// $.validator.addClassRules("amount_more", {
		// 	required: true,
		// 	number: true
		// });



		$(document).ajaxStart(function() {
			$(".loader").show();
		});

		$(document).ajaxComplete(function() {
			$(".loader").hide();
		});


		// $('input.adjdate').datepicker().on('change', function(ev) {
		// 	var daybook = 0;
		// 	var entrydate = $(this).val();
		// 	var branch_id = $('#branch_id').val();

		// 	var payment_mode = $('#payment_mode').val();

		// 	if (branch_id != '' && payment_mode != '') {
		// 		if (payment_mode == 0) {
		// 			$.ajax({
		// 				type: "POST",
		// 				url: "{{-- route('branch.branchChkbalance') --}}",
		// 				dataType: 'JSON',
		// 				data: {
		// 					'branch_id': branch_id,
		// 					'daybook': daybook,
		// 					'entrydate': entrydate
		// 				},
		// 				headers: {
		// 					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 				},
		// 				success: function(response) {
		// 					$('#branch_total_balance').val(response.balance);
		// 				}
		// 			});
		// 		}
		// 	}
		// });

		$('#branch_id').on('change', function() {
			var daybook = 0;
			var branch_id = $('#branch_id').val();
			var entrydate = $('#adjdate').val();
			$('.cash_box').hide();

			$('#payment_mode').val('');

			if (branch_id > 0) {
				if (entrydate == '') {
					$('#branch_id').val('');
					swal("Warning!", "Please select  payment date first!!", "warning");
				}
			}
		})



		$('.cash_box').hide();
		$('#bank_details').hide();
		$('#payment_mode').on('change', function() {
			$('.od_hide').hide();
			var daybook = 0;
			var branch_id = $('#branch').val();
			var entrydate = $('#adjdate').val();
			var payment_mode = $('#payment_mode').val();

			$('.cash_box').hide();
			$('#bank_details').hide();

			$('#branch_total_balance').val('0.00');

			if (branch_id != '' && entrydate != '') {
				if (branch_id > 0) {
					if (payment_mode != '') {
						if (payment_mode == 0) {
							$('.cash_box').show();
							$('#bank_details').hide();

							$.ajax({
								type: "POST",
								url: "{{-- route('branch.branchChkbalance') --}}",
								dataType: 'JSON',
								data: {
									'branch_id': branch_id,
									'daybook': daybook,
									'entrydate': entrydate
								},
								headers: {
									'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
								},
								success: function(response) {
									// alert(response.balance);
									$('#branch_total_balance').val(response.balance);
								}
							});
						} else {
							$('.cash_box').hide();
							$('#bank_details').show();
							if (payment_mode == 1) {
								$('#chq_details').show();
								$('#online_details').hide();
							} else {
								$('#online_details').show();
								$('#chq_details').hide();
							}
						}
					}
				}
			} else {
				swal("Warning!", "Please select payment date and Branch first", "warning");
				$('#payment_mode').val('');
				return false;
			}
		})



		$(document).on('change', '#bank_id', function() {
			var bank_id = $('#bank_id').val();
			$('.od_hide').hide();
			$('#bank_balance').val('0.00');
			$('#bank_od_balance').val('0.00');
			// $('#is_od').val('');
			$('#cheque_id').find('option').remove();
			$.ajax({
				type: "POST",
				url: "{!! route('branch.bank_account_list') !!}",
				dataType: 'JSON',
				data: {
					'bank_id': bank_id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#account_id').find('option').remove();
					$('#account_id').append('<option value="">Select account number</option>');
					$.each(response.account, function(index, value) {
						$("#account_id").append("<option value='" + value.id + "'>" + value.account_no + "</option>");
					});
				}
			});
		});




	})
</script>
<script>
	// $(document).on('click','.reject_block',function(){ 
	// 	var expense_id = $(this).attr("data-row-id");
	// 	var type = $(this).attr("data-type");
	// 	$('#reason-form').modal();
	// 	$('#expense_id').val(expense_id);
	// 	$('#type').val(type);
	// 	$('#reason').val('');

	// 	var text_type = "";
	// 	if(type == 2){ text_type = "Reject"; }
	// 	else{ text_type = "Block"; }
	// 	$('#reason_text').html(text_type);
	// });

	// $(document).ready(function() {
	// 	$('#comment-form').validate({ // initialize the plugin
	// 		rules: {
	// 			'reason' : 'required',
	// 		},
	// 		messages: {		  
	// 			reason: "Please enter Comments!",
	// 	  	},
	// 	});
	// });


	// $(document).on('click','.approve_expense',function(){ 
	// 	var bill_no = $(this).attr("data-row-id");
	// 	var msg = "Do you want to approve this Expense?";

	// 	swal({
	// 		title: "Are you sure?",
	// 		text: msg,  
	// 		type: "warning",
	// 		showCancelButton: true,
	// 		confirmButtonClass: "btn-primary",
	// 		confirmButtonText: "Yes",
	// 		cancelButtonText: "No",
	// 		cancelButtonClass: "btn-danger",
	// 		closeOnConfirm: false,
	// 		closeOnCancel: true
	// 	},
	// 	function(isConfirm) {
	// 		if (isConfirm) {
	// 			$.ajax({
	// 				type: "POST",  
	// 				url: "{!! route('branch.expense.approve_expense') !!}",
	// 				dataType: 'JSON',
	// 				data: {'bill_no':bill_no},
	// 				headers: {
	// 				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	// 				},
	// 				success: function(response) {
	// 					//alert(response.status);
	// 					if(response.status == "1"){
	// 						swal("Good job!", response.message, "success");
	// 						location.reload();
	// 					} else {
	// 						swal("Warning!", response.message, "warning");
	// 						return false;
	// 					}
	// 				}
	// 			});
	// 		}
	// 	});
	// });

	function searchForm() {
		if ($('#filter').valid()) {
			$('#is_search').val("yes");
			$(".table-section").removeClass('hideTableData');
			expense.draw();
		}
	}

	function resetForm() {
		$('#is_search').val("no");
		$('#start_date').val('');
		$('#end_date').val('');
		$('#branch_id').val('');
		$('#party_name').val('');
		$('#status').val('');
		$(".table-section").addClass("hideTableData");
		expense.draw();
	}

	// function printDiv(elem) {
	//    $("#"+elem).print({
	// 		//Use Global styles
	// 		globalStyles : true,
	// 		//Add link with attrbute media=print
	// 		mediaPrint : true,
	// 		//Custom stylesheet
	// 		stylesheet : "{{url('/')}}/asset/print.css",
	// 		//Print in a hidden iframe
	// 		iframe : false,
	// 		//Don't print this
	// 		noPrintSelector : ".avoid-this",
	// 		//Add this at top
	// 		//  prepend : "Hello World!!!<br/>",
	// 		//Add this on bottom
	// 		// append : "<span><br/>Buh Bye!</span>",
	// 		header: null,               // prefix to html
	// 		footer: null,  
	// 		//Log to console when printing is done via a deffered callback
	// 		deferred: $.Deferred().done(function() {    })
	// 	});
	// }
</script>