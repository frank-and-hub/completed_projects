<script type="text/javascript">
var employeeTable;
var cardhistoryTable;
$(document).ready(function () {
	var date = new Date();
	$('#start_date').datepicker({
		format: "dd/mm/yyyy",
		todayHighlight: true,  
		endDate: date, 
		autoclose: true
	});

	$('#end_date').datepicker({
		format: "dd/mm/yyyy",
		todayHighlight: true, 
		endDate: date,  
		autoclose: true
	});
  
  	/*$("#ssb_ac").on("keypress", function(e) {
		if (e.which === 32 && !this.value.length) e.preventDefault(); 
	});
	$("#card_no").on("keypress", function(e) {
		if (e.which === 32 && !this.value.length) e.preventDefault(); 
	});
	$("#card_holder_name").on("keypress", function(e) {
		if (e.which === 32 && !this.value.length) e.preventDefault(); 
	});
	$("#credit_card_number").on("keypress", function(e) {
		if (e.which === 32 && !this.value.length) e.preventDefault(); 
	});
	$("#credit_card_account_number").on("keypress", function(e) {
		if (e.which === 32 && !this.value.length) e.preventDefault(); 
	});
	$("#credit_card_bank").on("keypress", function(e) {
		if (e.which === 32 && !this.value.length) e.preventDefault(); 
	});*/
  
  
	employeeTable = $('#debit_card_listing').DataTable({
		processing: true,
		serverSide: true,
		pageLength: 20,
		lengthMenu: [10, 20, 40, 50, 100],
		"fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
			var oSettings = this.fnSettings ();
			$('html, body').stop().animate({
			scrollTop: ($('#debit_card_listing').offset().top)
		}, 1000);
			$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
			return nRow;
		},
		ajax: {
			"url": "{!! route('admin.debit-card.debit_card_listing') !!}",
			"type": "POST",
			"data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
		},
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'issue_date', name: 'issue_date'},
			{data: 'card_no', name: 'card_no'},
			{data: 'br_name', name: 'br_name'},
			{data: 'branch_code', name: 'branch_code'},
			{data: 'card_type', name: 'card_type'},
			{data: 'valid_from', name: 'valid_from'},
			{data: 'valid_to', name: 'valid_to'},
			{data: 'mem_ssb_ac', name: 'mem_ssb_ac'},
			{data: 'mem_name', name: 'mem_name'},
			{data: 'app_date', name: 'app_date'},
			{data: 'ref_no', name: 'ref_no'},
			{data: 'emp_code', name: 'emp_code'},
			{data: 'emp_name', name: 'emp_name'},
			{data: 'status', name: 'status'},
			{data: 'action', name: 'action',orderable: false, searchable: false},
		],"ordering": false
	});
	$(employeeTable.table().container()).removeClass( 'form-inline' );


	cardhistoryTable = $('#debit_card_transaction_listing').DataTable({
		processing: true,
		serverSide: true,
		pageLength: 20,
		lengthMenu: [10, 20, 40, 50, 100],
		"fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
			var oSettings = this.fnSettings ();
			$('html, body').stop().animate({
			scrollTop: ($('#debit_card_transaction_listing').offset().top)
		}, 1000);
			$("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
			return nRow;
		},
		ajax: {
			"url": "{!! route('admin.debit-card.card_tr_history') !!}",
			"type": "POST",
			"data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
		},
		columns: [
			{data: 'DT_RowIndex', name: 'DT_RowIndex'},
			{data: 'card_no', name: 'card_no'},
			{data: 'account_no', name: 'account_no'},
			{data: 'amount', name: 'amount'},
			{data: 'payment_type', name: 'payment_type'},
			{data: 'status', name: 'status'},
			{data: 'entry_date', name: 'entry_date'},
			
			//{data: 'action', name: 'action',orderable: false, searchable: false},
		],"ordering": false
	});
	$(cardhistoryTable.table().container()).removeClass( 'form-inline' );


	$(document).on('click','.deleteDebitCard',function(){ 
		var debit_card_id = $(this).attr("data-row-id");
		swal({
			title: "Are you sure?",
			text: "Do you want to delete this debit card?",  
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
					url: "{!! route('admin.debit-card.delete-debit-card') !!}",
					dataType: 'JSON',
					data: {'table_id':debit_card_id},
					headers: {
					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function(response) {
					
						if(response.status == "1"){
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
	
	
	$(document).on('click','.actionDebitCard',function(){ 
		var debit_card_id = $(this).attr("data-row-id");
		var type = $(this).attr("data-type");
		var globaldate = $('.gdate').text();
		
		//var text_type = ''; //(type == 1) ? "Do you want to approve this debit card?" : "Do you want to reject this debit card?";
		var text_type = "";
		if(type == 1){ text_type = "approve"; }
		else if(type == 2){ text_type = "reject"; }
		else if(type == 3){ text_type = "block"; }
		//else{ text_type = "unblock"; }
		
		var msg = "Do you want to "+text_type+" this debit card?";
		
		swal({
			title: "Are you sure?",
			text: msg,  
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
					url: "{!! route('admin.debit-card.approve_reject-debit-card') !!}",
					dataType: 'JSON',
					data: {'table_id':debit_card_id, 'type':type,'date':globaldate},
					headers: {
					  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function(response) {
						//alert(response.status);
						if(response.status == "1"){
							swal("Good job!", response.message, "success");
							//location.reload();
						} else {
							swal("Warning!", response.message, "warning");
							return false;
						}
					}
				});
			}
		});
	});
	
	$(document).on('click','.action_reject_block',function(){ 
		var debit_card_id = $('#debit_card_id').val();
		var type = $('#type').val();
		var reason = $('#reason').val();

		if(reason != ''){
			$.ajax({
				type: "POST",  
				url: "{!! route('admin.debit-card.approve_reject-debit-card') !!}",
				dataType: 'JSON',
				data: {'table_id':debit_card_id, 'type':type, 'reason':reason},
				headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					if(response.status == "1"){
						swal("Good job!", response.message, "success");
						location.reload();
					} else {
						swal("Warning!", response.message, "warning");
						return false;
					}
				}
			});
		}
		
	})
	
	var now= new Date();
	var month=now.getMonth();
	var year1=now.getFullYear();
	
	$('#debit_card_add').validate({ 
		rules: {
			card_no: {
				required: true,
				number: true,
				minlength: 16,
				maxlength: 16 
			},
			from_month: "required",
			from_year: "required",
			to_month: "required",
			//to_year: "required",
			card_charge: "required",
			payment_mode: "required",
			ref_no: "required",
			emp_code: "required",
			card_type: "required",
			to_year: {
			   required: true,
			   checkYear:true
			}
	  	},
	  	messages: {		  
		   	card_no: {
				required: "Please enter debit card number",
				number: "Please enter valid number.",
				minlength: "Please enter valid debit card number",
				maxlength: "Please enter valid debit card number" 
			},
			from_month: "Please enter valid from month.",
			from_year: "Please enter valid from year.",
			to_month: "Please enter valid to month.",
			to_year: "Please enter valid to year.",
			card_charge: "Please enter card charge.",
			payment_mode: "Please enter payment mode.",
			ref_no: "Please enter Reference Number.",
			emp_code: "Please enter Employee Code.",
			card_type: "Please select card type.",
	  	},
		errorElement: 'label',
		errorPlacement: function (error, element) {
			error.addClass(' ');
			element.closest('.error-msg').append(error);
		},
		highlight: function (element, errorClass, validClass) {
			$(element).addClass('is-invalid');
		},
		unhighlight: function (element, errorClass, validClass) {
			$(element).removeClass('is-invalid');
		}
	});
	
	$.validator.addMethod('minStrict', function (value, el, param) {
		return value >= param;
	});
	$.validator.addMethod('monthCheck', function (value, el, param) {
		//code logic here
	});

 
    
	$('.export').on('click',function(){
		var extension = $(this).attr('data-extension');
		$('#emp_export').val(extension);
		$('form#filter').attr('action',"{!! route('admin.hr.employee_export') !!}");
		$('form#filter').submit();
		return true;
	}); 
	
	$( document ).ajaxStart(function() {
		$( ".loader" ).show();
	});
	
	$( document ).ajaxComplete(function() {
		$( ".loader" ).hide();
	});



	$('#filter').validate({
		rules: {
			//status:"required",  
		},
		messages: {  
			//status: "Please select status",
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
});


//payment mode script start here
$(document).ready(function () {
	$("#cash").show();
	$("#ssb").hide();
	
	$("#payment_mode").on('change', function(){
		var selVal = $(this).val();

		$("#cash").hide();
		$("#ssb").hide();
		var ssb_ac = $("#ssb_ac").val();
		if(selVal==1){
						
			$("#ssb").show();
			$("#cash").hide();
		}else if(selVal==2){
			$("#cash").show();
			$("#ssb").hide();
		}
	})
});
//payment mode script end here


$(document).on('blur','#emp_code',function(){
	var emp_code = $('#emp_code').val();
	
	$('#emp_name').val("");
	$('#emp_id').val("");
	
	$.ajax({
		type: "POST",
		url: "{!! route('admin.debit-card.emp_detail_show') !!}",
		data: { 
			emp_code: emp_code,
		},
		headers: {
		  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function(result) {
			var json_data = JSON.parse(result);
			//console.log(json_data.employee_name);
			$('#emp_name').val("");
			$('#emp_id').val("");
			if(json_data.status == "1"){
				$('#emp_name').val(json_data.employee_name);
				$('#emp_id').val(json_data.employee_id);
			}
			else{
				$('#emp_name').val("");
				$('#emp_id').val("");
				return false;
			}
		},
		error: function(result) {
			alert('error');
		}
	});
});

//payment mode script start here
$(document).ready(function () {
	/*$("#ssb_detail_show").show();
	$("#debit_card_add").hide();
	$("#ssb_submit").on('click', function(){
		var selVal = $(this).val();
		$("#debit_card_add").show();
		$("#ssb_detail_show").hide();
		
		var ssb_ac = $("#ssb_ac").val();

		$.ajax({
			type: "POST",
			url: "{!! route('admin.debit-card.ssb_detail_show') !!}",
			data: { 
				ssb_ac: ssb_ac,
			},
			headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(result) {
				var json_data = JSON.parse(result);
				alert(json_data[0].id);
				
			},
			error: function(result) {
				alert('error');
			}
		});
	})*/
	
	
	//$("#ssb").hide();
	$("#debit_card_data").hide();
	$('#show_card_detail').hide();
	$(document).on('keyup','#ssb_ac',function(){
	
		$('#show_card_detail').html();
		$("#cash").hide();
		var ssb_ac = $("#ssb_ac").val();
		$.ajax({
			type: "POST",
			url: "{!! route('admin.debit-card.ssb_detail_show') !!}",
			data: { 
				ssb_ac: ssb_ac,
			},
			headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(result) {
				var json_data = JSON.parse(result);
				//alert(result);
				//alert(json_data.data[0].id);
				if(json_data.tot_cnt > 0){
					if(json_data.debit_cnt > 0){
						$("#ssb_ac").val('');
						$("#debit_card_data").hide();
						swal("Warning!", "Card already assigned to this SSB Account Number.", "warning");
						return false;
					}else{
						$('#show_card_detail').hide();
						$("#debit_card_data").show();
						//alert(json_data.data[0].id);

						var f_name = json_data.data[0].first_name;
						var l_name = json_data.data[0].last_name == null ? "" :json_data.data[0].last_name;
						var name = f_name +" "+ l_name;

						$('#member_code').html(json_data.data[0].member_code);
						$('#member_name').html(name);
						$('#user_name').val(name);
						$('#branch_code').val(json_data.data[0].branch_code);
						$('#branch_name').html(json_data.data[0].branch_name);
						$('#ssb_bal').html(json_data.data[0].opening_balance);
						$('#ass_name').html(json_data.data[0].ass_name);
						$('#ass_code').html(json_data.data[0].ass_code);
						$('#ssb_account_no').html(json_data.data[0].account_no);
						$('#ssb_id').val(json_data.data[0].id);
						$('#member_id').val(json_data.data[0].member_id);
						$('#branch_id').val(json_data.data[0].branch_id);
						$('#card_charge').val(json_data.debit_card_charge);
						$('#created_at').html(json_data.data[0].create_date);
						

						$('#member_photo').attr('src', '{{url('/')}}/asset/profile/' + json_data.data[0].photo);
					}
					if(json_data.gstamount > 0)
					{	
						if(json_data.IntraState)
						{
							$('#cgst').html('CGST Charge ' +json_data.percentage/2 + '%');
							$('#sgst').html('SGST Charge ' +json_data.percentage/2 + '%');
							$('#cgst_amount').html( json_data.gstAmount );
							$('#sgst_amount').html( json_data.gstAmount);
							$('.cgst_type').show();
							$('#igst_type').hide();
						}
						else{
							$('#igst').html('IGST Charge ' +json_data.percentage + '%' );
							$('#igst_amount').html( json_data.gstAmount);
							$('.cgst_type').hide();
							$('#igst_type').show();
						}
					}
					else{
						$('.cgst_type').hide();
						$('#igst_type').hide();
					}
					
					
			
					//console.log(json_data.ssb_min_bal);
					//$("#ssb_payment_mode").hide();
					if(json_data.data[0].opening_balance < json_data.ssb_min_bal){
						//$("#ssb_payment_mode").show();
						$("#debit_card_data").hide();
						swal("Warning!", "Insufficient balance in SSB account", "warning");
						return false;
					}
					console.log(json_data.debit_exist);
					// if(json_data.debit_cnt > 0){
					// 	//$("#ssb_payment_mode").show();
					// 	$("#debit_card_data").hide();
					// 	swal("Warning!", "Card already assigned to this SSB Account Number.", "warning");
					// 	return false;
					// }
					
					
					//$("#card_reissue").prop("checked", false);
					//$("#card_new").prop("checked", false);
					if(json_data.debit_exist == 0){
						$("#card_type_new").show();
						$("#card_type_reissue").hide();
						$("#card_new").prop("checked", true);
					}
					else{
						$("#card_type_new").hide();
						$("#card_type_reissue").show();
						$("#card_reissue").prop("checked", true);
					}
					
				}
				else{
					$("#debit_card_data").hide();
					$('#show_card_detail').show();
					
					$('#member_code').html();
					$('#member_name').html();
					$('#branch_code').val();
					$('#branch_name').html();
					$('#ssb_bal').html();
					$('#ass_name').html();
					$('#ass_code').html();
					$('#ssb_account_no').html();
					$('#ssb_id').val();
					$('#member_id').val();
					$('#branch_id').val();
					$('#card_charge').val();
					$('#member_photo').attr('src', '');
					$('#created_at').html();
				}
			},
			error: function(result) {
				$("#debit_card_data").hide();
				alert('error');
			}
		});
	});
});
//payment mode script end here


function searchForm(){  
    if($('#filter').valid()){
        $('#is_search').val("yes");
		$(".table-section").removeClass('hideTableData');
        employeeTable.draw();
    }
}
function resetForm(){
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
	
	$('#card_no').val('');
    $('#ssb_ac1').val('');
    $('#status').val('');
	$('#branch_id').val('');
	$(".table-section").addClass("hideTableData");
    employeeTable.draw();
}

function searchForm1(){  
    if($('#filter').valid()){
        $('#is_search').val("yes");
		$(".table-section").removeClass('hideTableData');
        cardhistoryTable.draw();
    }
}
function resetForm1(){
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");

    $('#card_no').val('');
    $('#ssb_ac1').val('');
	$(".table-section").addClass("hideTableData");
    cardhistoryTable.draw();
}

</script>
<script>
$(document).on('click','.reject_block',function(){ 
	var debit_card_id = $(this).attr("data-row-id");
	var type = $(this).attr("data-type");
	$('#reason-form').modal();
	$('#debit_card_id').val(debit_card_id);
	$('#type').val(type);
	$('#reason').val('');
	
	var text_type = "";
	if(type == 2){ text_type = "Reject"; }
	else{ text_type = "Block"; }
	$('#reason_text').html(text_type);
});

$(document).ready(function() {
	$('#comment-form').validate({ // initialize the plugin
		rules: {
			'reason' : 'required',
		},
		messages: {		  
			reason: "Please enter Comments!",
	  	},
	});
});
</script>