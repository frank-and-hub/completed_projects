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
				"url": "{!! route('admin.advancePayment.AdjListingtable') !!}",
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
						url: "{!! route('admin.branchBankBalanceAmount') !!}",
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

		//-------------------------------------


		$('#form').validate({ // initialize the plugin

			rules: {
				branch_id: "required",

				adjdate: {
					required: true,
				},
				account_head: {
					required: true,
				},
				description: {
					required: true,
				},
				amount: {
					required: true,
					number: true,
					// zero: true,
				},
				total_amount: {
					required: true,
					approveAmountLessThanTotal: true,
					number: true,
				},

				// total_amount: {
				// 	required: true,
				// 	number: true,
				// 	// zero: true,
				// 	// amountcheck: true,
				// },
			},

			messages: {
				date: {
					required: "Please  Select Date.",
				},
				total_amount: {
					approveAmountLessThanTotal: "Total Amount must be less than or equal to the Approve Amount.",
				}
			},

			submitHandler: function(form) {

				$('#form').submit(function(e) {
					// Prevent the form from submitting via the browser
					// e.preventDefault();

					// Serialize the form data
					var formData = $(this).serialize();

					// Send the Ajax request
					$.ajax({
						type: 'POST',
						url: "{!! route('admin.advancePaymentAdjestment.save') !!}",
						data: formData,
						success: function(response) {
							// Handle the Ajax response 

							if (response) {
								// return false;
								swal("Success", response, "success");
								// window.location.href = "{{ route('admin.advancePayment.requestList')}}";
							}

						},
						error: function(xhr, status, error) {
							// Handle Ajax errors
							var err = eval("(" + xhr.responseText + ")");

							return false;


						}

					});
				});

			}

		});





		var a = 0;
		var b = 0;

		$("#add_row").click(function() {

			$("#account_head_" + a).prop('required', true);
			$("#sub_head1" + a).prop('required', true);
			$("#amount_more" + a).prop('required', true);
			$("#date_more" + a).prop('required', true);

			b++;

			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.getHeads') !!}",
				dataType: 'JSON',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},

				success: function(response) {
					// const obj = JSON.parse(response);
					// console.log(obj);
					var len = response.length;
					console.log(response);

					var expendHtml = '<tr>';

					expendHtml += '<td > <div class="col-lg-12 error-msg">  <select name="account_head_more[' + a + ']" id="account_head_' + a + '" class="account_head_more form-control" data-value=' + b + ' > <option value="">Select Account Head</option><option value="1" data-name="expence">Expense</option><option value="2" data-name="fixedasset">Fixed Assets</option></select></div>';

					expendHtml += '<td > <div class="col-lg-12 error-msg">  <select name="sub_head1_more[' + a + ']" id="sub_head1' + a + '" class="form-control  ' + b + '-sub_head1_more sub_head1_more" data-value=' + b + ' > <option value="0">Select Sub Head1</option>';

					expendHtml += '<td > <div class="col-lg-12 error-msg">  <select name="sub_head2_more[' + a + ']" id="sub_head2' + a + '" class="form-control  ' + b + '-sub_head2_more sub_head2_more"  data-value=' + b + ' > <option value="0">Select Sub Head2</option>';

					expendHtml += '<td > <div class="col-lg-12 error-msg">  <select name="sub_head3_more[' + a + ']" id="sub_head3' + a + '" class="form-control  ' + b + '-sub_head3_more sub_head3_more"  data-value=' + b + ' > <option value="0">Select Sub Head3</option>';

					expendHtml += '<td > <div class="col-lg-12 error-msg">  <select name="sub_head4_more[' + a + ']" id="sub_head4' + a + '" class="form-control  ' + b + '-sub_head4_more sub_head4_more"  data-value=' + b + ' > <option value="0">Select Sub Head4</option>';

					expendHtml += '<td > <div class="col-lg-12 error-msg">  <select name="sub_head5_more[' + a + ']" id="sub_head5' + a + '" class="form-control  ' + b + '-sub_head5_more sub_head5_more"  data-value=' + b + ' > <option value="0">Select Sub Head5</option>';

					expendHtml += '<td > <div class="col-lg-12 error-msg"> <input type="text" id="amount_more' + a + '" name="amount_more[' + a + ']" class="form-control amount_more t_amount" ></div> </td><td > <div class="col-lg-12 error-msg"> <input type="text" id="description_more' + a + '" name="description_more[' + a + ']" class="form-control description_more t_description" ></div> </td>{{--<td > <div class="col-lg-12 error-msg"> <input type="text" id="date_more' + a + '" value="<?= date('d/m/Y') ?>" name="date_more[' + a + ']" class="form-control date_more frm" ></div> </td><td style=""> <button type="button" data-id=' + a + ' class="btn btn-primary remCF ml-3"><i class="icon-trash"></i></button> </td>--}}</tr>'

					$("#expense1").append(expendHtml);

					a++;

					var date11 = $('#create_application_date').val();

					$('.bill_date_more').datepicker({
						format: "dd/mm/yyyy",
						endHighlight: true,
						autoclose: true,
						orientation: "bottom",
						endDate: date11,
					})
				}
			})
		});


		$(document).on('change', '.account_head_more', function() {
			var option = '<option value="">Select Sub Head 1</option>';
			var id = $(this).val();
			var index = $(this).attr('data-value');
			$('.' + index + '-sub_head1_more').empty();

			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'head_id': id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					var len = response.account_heads.length;

					if (len > 0) {
						for (var i = 0; i < len; i++) {
							var head_id = response.account_heads[i].head_id;
							var sub_head = response.account_heads[i].sub_head;
							option += '<option value="' + head_id + '">' + sub_head + '</option>';
						}
						$('.' + index + '-sub_head1_more').append(option);
					} else {
						$('.' + index + '-sub_head1_more').append('<option value=0>Select Sub Head 1</option>');
					}
				}
			})
		});


		$(document).on('change', '.sub_head1_more', function() {
			var option = '<option value="">Select Sub Head 2</option>';
			var id = $(this).val();
			var index = $(this).attr('data-value');
			$('.' + index + '-sub_head2_more').empty();

			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'head_id': id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},

				success: function(response) {
					var len = response.account_heads.length;
					//	alert(response);
					//	alert(len);

					if (len > 0) {
						for (var i = 0; i < len; i++) {
							var head_id = response.account_heads[i].head_id;
							var sub_head = response.account_heads[i].sub_head;
							option += '<option value="' + head_id + '">' + sub_head + '</option>';
						}
						//alert(index);
						//	alert(option);
						$('.' + index + '-sub_head2_more').append(option);
					} else {
						$('.' + index + '-sub_head2_more').append('<option value="">Select Sub Head 2</option>');
					}
				}
			})
		});

		$('#neft_charge').on("keyup", function() {
			var sum = 0;
			$('.t_amount').each(function() {
				if ($(this).val() == 0 || $(this).val() > 0) {
					sum += Number($(this).val());
				}
			});
			$('#total_amount').val(sum);
		});
		$('#expense1').on("keyup", ".t_amount", function() {
			var sum = 0;
			$('.t_amount').each(function() {
				if ($(this).val() == 0 || $(this).val() > 0) {
					sum += Number($(this).val());
				}
			});
			$('#total_amount').val(sum);
		});


		$(document).on('click', '.remCF', function() {
			// const delid = $(this).data('id');
			// $("#account_head_"+delid).prop('required',true);
			// $("#sub_head1"+delid).prop('required',true);
			// $("#amount_more"+delid).prop('required',true);
			// $("#date_more"+delid).prop('required',true);
			//alert($(this).val());
			$(this).parent().parent().remove();
			$(".t_amount").trigger("keyup");
		});


		$('#account_head').on('change', function() {
			var id = $(this).val();

			var account_head_id = $(this).val();
			var account_head_name = $(this).find(':selected').attr('data-name');


			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'id': account_head_id,
					'name': account_head_name
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#sub_head1').find('option').remove();
					$('#sub_head1').append('<option value="">Select sub head1</option>');

					$.each(response.account_heads, function(index, value) {
						$("#sub_head1").append("<option value='" + value.head_id + "'>" + value.sub_head + "</option>");
					});

				}
			})
		})


		$('#sub_head1').on('change', function() {
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'head_id': id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#sub_head2').find('option').remove();
					$('#sub_head2').append('<option value="">Select sub head2</option>');

					$.each(response.account_heads, function(index, value) {
						$("#sub_head2").append("<option value='" + value.head_id + "'>" + value.sub_head + "</option>");
					});
				}
			})
		})


		$('#sub_head2').on('change', function() {
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'head_id': id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#sub_head3').find('option').remove();
					$('#sub_head3').append('<option value="">Select sub head3</option>');

					$.each(response.account_heads, function(index, value) {
						$("#sub_head3").append("<option value='" + value.head_id + "'>" + value.sub_head + "</option>");
					});
				}
			})
		})

		$('#sub_head3').on('change', function() {
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'head_id': id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#sub_head4').find('option').remove();
					$('#sub_head4').append('<option value="">Select sub head4</option>');

					$.each(response.account_heads, function(index, value) {
						$("#sub_head4").append("<option value='" + value.head_id + "'>" + value.sub_head + "</option>");
					});
				}
			})
		})

		$('#sub_head4').on('change', function() {
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
				dataType: 'JSON',
				data: {
					'head_id': id
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$('#sub_head5').find('option').remove();
					$('#sub_head5').append('<option value="">Select sub head5</option>');

					$.each(response.account_heads, function(index, value) {
						$("#sub_head5").append("<option value='" + value.head_id + "'>" + value.sub_head + "</option>");
					});
				}
			})
		})



		// expense = $('#expense_listing').DataTable({
		// 	processing:true,
		// 	serverSide:true,
		// 	pageLength:20,
		// 	lengthMenu:[10,20,40,50,100],
		// 	"fnRowCallback" : function(nRow, aData, iDisplayIndex) {
		// 		var oSettings = this.fnSettings ();
		// 		$('html, body').stop().animate({
		// 			scrollTop: ($('#expense_listing').offset().top)
		// 		}, 10);
		// 		$("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
		// 		return nRow;
		// 	},
		// 	ajax: {
		// 		"url": "{!! route('admin.expense_listing') !!}",
		// 		"type": "POST",
		// 		"data":function(d) {d.searchform=$('form#filter').serializeArray(),
		// 							d.bill_no=$('#bill_no').val(),
		// 							d.branch_id=$('#branch_id').val(),
		// 							d.created_at=$('#created_at').val()
		// 						},
		// 		headers: {
		// 			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 		},
		// 	},
		// 	"columnDefs": [{
		// 		"render": function(data, type, full, meta) {
		// 			return meta.row + 1; // adds id to serial no
		// 		},
		// 		"targets": 0
		// 	}],
		// 	columns: [
		// 		{data: 'DT_RowIndex', name: 'DT_RowIndex'},

		// 		{data: 'bill_date', name: 'bill_date'},
		// 		{data: 'payment_date', name: 'payment_date'},
		// 		{data: 'account_head', name: 'account_head'},
		// 		{data: 'sub_head1', name: 'sub_head1'},
		// 		{data: 'sub_head2', name: 'sub_head2'},
		// 		{data: 'particular', name: 'particular'},
		// 		{data: 'receipt', name: 'receipt '},
		// 		{data: 'amount', name: 'amount'},

		// 	]
		// })
		// $(expense.table().container()).removeClass( 'form-inline' ); 

		//Bill Expense Listing

		// var id = "{{Request::segment(1)}}";





		$(document).ajaxStart(function() {
			$(".loader").show();
		});

		$(document).ajaxComplete(function() {
			$(".loader").hide();
		});


		$('input.adjdate').datepicker().on('change', function(ev) {
			var daybook = 0;
			var entrydate = $(this).val();
			var branch_id = $('#branch_id').val();

			var payment_mode = $('#payment_mode').val();

			if (branch_id != '' && payment_mode != '') {
				if (payment_mode == 0) {
					$.ajax({
						type: "POST",
						url: "{!! route('admin.branchChkbalance') !!}",
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
							$('#branch_total_balance').val(response.balance);
						}
					});
				}
			}
		});

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
								url: "{!! route('admin.branchChkbalance') !!}",
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
				url: "{!! route('admin.bank_account_list') !!}",
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




		// $(document).on('change','#account_id',function(){
		// 	$('.od_hide').hide();
		// 	$('#bank_balance').val('0.00'); 
		// 	$('#bank_od_balance').val('0.00');
		// 	$('#is_od').val('');

		// 	var account_id=$('#account_id').val();
		// 	var bank_id=$('#bank_id').val();
		// 	var entrydate=$('#adjdate').val();

		// 	$.ajax({
		// 		type: "POST",  
		// 		url: "{!! route('admin.bank_cheque_list') !!}",
		// 		dataType: 'JSON',
		// 		data: {'account_id':account_id},
		// 		headers: {
		// 			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 		},

		// 		success: function(response) { 
		// 			$('#cheque_id').find('option').remove();
		// 			$('#cheque_id').append('<option value="">Select cheque number</option>');
		// 			$.each(response.chequeListAcc, function (index, value) { 
		// 				$("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+"</option>");
		// 			}); 
		//         }

		// 	});

		//     $.ajax({
		// 		type: "POST",  
		// 		url: "{!! route('admin.bankChkbalance') !!}",
		// 		dataType: 'JSON',
		// 		data: {'account_id':account_id,'bank_id':bank_id,'entrydate':entrydate},
		// 		headers: {
		// 			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 		},
		// 		success: function(response) { 
		// 			$('#bank_balance').val(response.balance);
		// 			$('#bank_od_balance').val(response.odcurrentbalance);
		// 			$('#is_od').val(response.is_od);
		// 			if(response.is_od==1)
		// 			{

		// 				$('.od_hide').show();
		// 			}

		// 			Oid = response.is_od;

		// 		}
		// 	});
		// });

		$(document).on('click', '.delete_expense', function() {
			var expense_id = $(this).attr("data-row-id");
			var title = $(this).attr("title");


			swal({
					title: "Are you sure?",
					text: "Do you want to delete this expense?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-primary",
					confirmButtonText: "Yes",
					cancelButtonText: "No",
					cancelButtonClass: "btn-danger",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						$.ajax({
							type: "POST",
							url: "{!! route('admin.expense.deleteBill') !!}",
							dataType: 'JSON',
							data: {
								'bill_no': expense_id,
								'title': title
							},
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							},
							success: function(response) {
								if (response.status == "1") {
									swal("Good job!", response.message, "success");
									location.reload();
								} else {
									swal("Warning!", response.message, "warning");
									return false;
								}
							}
						});
					}
				});
		})
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
	// 				url: "{!! route('admin.expense.approve_expense') !!}",
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