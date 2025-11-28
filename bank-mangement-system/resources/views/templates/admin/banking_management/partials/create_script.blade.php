<script type="text/javascript">

var expense;

$(document).ready(function(){

	$("#payment_vendor_name,#received_payment_vendor_name,#payment_customer_name,#received_payment_customer_name").select2({dropdownAutoWidth : true});

	var today = new Date();
	
	$('#expense_date,#received_payment_vendor_date,#payment_vendor_date,#credit_card_payment_date,#indirect_income_date,#payment_customer_date,#received_payment_customer_date,#start_date,#end_date').datepicker( {
	   format: "dd/mm/yyyy",
	   orientation: "bottom",
	   autoclose: true,
	   endDate: "today",
       maxDate: today
	});
	
	$(document).on('change','.banks_id ',function(){ 
		var banks_id = $(this).val();
		var idd = $(this).attr("id");
		if(idd == "expense_bank_id"){
			var accountID = "expense_account_no";
		}
		if(idd == "payment_customer_bank_id"){
			var accountID = "payment_customer_bank_account_number";
		}
		if(idd == "payment_vendor_bank_id"){
			var accountID = "payment_vendor_bank_account_number";
		}
		if(idd == "credit_card_bank_id"){
			var accountID = "credit_card_account_number";
		} 
		if(idd == "received_payment_vendor_bank_id"){
			var accountID = "received_payment_vendor_bank_account_number";
		} 
		if(idd == "received_payment_customer_bank_id"){
			var accountID = "received_payment_customer_bank_account_number";
		} 
		if(idd == "indirect_income_bank_id"){
			var accountID = "indirect_income_account_no";
		}
		$.ajax({
			type: "POST",  
			url: "{!! route('admin.getAccountNumberOfBank') !!}",
			dataType: 'JSON',
			data: {'banks_id':banks_id},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				$("#"+accountID).empty();
				$("#"+accountID).append("<option value=''>Choose account number</option>");
				if(response.length > 0){
					for(var k=0; k<response.length; k++){
						$("#"+accountID).append("<option value="+response[k].id+">"+response[k].account_no+"</option>");
					}
				}
			}
		});
	});
	
	
	$(document).on('change','.paid_via_account_number',function(){
		var account_id = $(this).val();
		var idd = $(this).attr("id");
		if(idd == "expense_account_no"){
			var accountID = "expense_cheque_no";
		}
		if(idd == "payment_customer_bank_account_number"){
			var accountID = "payment_customer_cheque_no";
		}
		if(idd == "payment_vendor_bank_account_number"){
			var accountID = "payment_vendor_cheque_no";
		}
		if(idd == "credit_card_account_number"){
			var accountID = "credit_card_customer_cheque_no";
		} 
		if(idd == "received_payment_vendor_bank_account_number"){
			var accountID = "received_payment_vendor_cheque_no";
		} 
		if(idd == "received_payment_customer_bank_account_number"){
			var accountID = "received_payment_customer_cheque_no";
		} 
		if(idd == "indirect_income_account_no"){
			var accountID = "indirect_income_cheque_no";
		}
		$.ajax({
			type: "POST",  
			url: "{!! route('admin.getChequeNumberOfBank') !!}",
			dataType: 'JSON',
			data: {'account_id':account_id},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				$("#"+accountID).empty();
				$("#"+accountID).append("<option value=''>Choose cheque number</option>");
				if(response.length > 0){
					for(var k=0; k<response.length; k++){
						$("#"+accountID).append("<option value="+response[k].id+">"+response[k].cheque_no+"</option>");
					}
				}
			}
		});
	});
	
	
	$(document).on('change','.expence_head_id',function(){
		var hId = $(this).attr("data-row-id");
		var head_id = $(this).val();
		
		if(head_id > 0){
			$.ajax({
				type: "POST",  
				url: "{!! route('admin.getHeadLedgerData') !!}",
				dataType: 'JSON',
				data: {'hId':hId,'head_id':head_id},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					if(hId == 1){
						$("#expense_account1").empty();
						$("#expense_account1").append("<option value=''>Choose Sub Head</option>")
						$("#expense_account2").empty();
						$("#expense_account2").append("<option value=''>Choose Sub Head</option>");	
						$("#expense_account3").empty();
						$("#expense_account3").append("<option value=''>Choose Sub Head</option>");						
					}else if(hId == 2){
						$("#expense_account2").empty();
						$("#expense_account2").append("<option value=''>Choose Sub Head</option>");
						$("#expense_account3").empty();
						$("#expense_account3").append("<option value=''>Choose Sub Head</option>");	
					}else if(hId == 3){
						$("#expense_account3").empty();
						$("#expense_account3").append("<option value=''>Choose Sub Head</option>");
						$("#expense_account4").empty();
						$("#expense_account4").append("<option value=''>Choose Sub Head</option>");	
					}
					$("#expense_account"+hId).empty();
					$("#expense_account"+hId).append("<option value=''>Choose Sub Head</option>");
					if(response.length > 0){
						for(var k=0; k<response.length; k++){
							$("#expense_account"+hId).append("<option value="+response[k].head_id+">"+response[k].sub_head+"</option>");
						}
					}
				}
			}); 
		}	
	});
	
	
	$(document).on('change','#expense_mode ',function(){ 
		var expense_mode = $(this).val();
		if(expense_mode == "1"){
			$(".bankDiv").css("display","block");
			$(".CashDiv").css("display","none");
			$(".chequeDiv").css("display","none");
			$(".bankutrDiv").css("display","none");
		} else if(expense_mode == "2"){
			$(".bankDiv").css("display","none");
			$(".CashDiv").css("display","block");
			$(".chequeDiv").css("display","none");
			$(".bankutrDiv").css("display","none");
		} else {
			$(".bankDiv").css("display","none");
			$(".CashDiv").css("display","none");
			$(".chequeDiv").css("display","none");
			$(".bankutrDiv").css("display","none");
		}
	});
	
	$(document).on('change','#expense_paid_via ',function(){ 
		var expense_paid_via = $(this).val();
		if(expense_paid_via == "1"){
			$(".bankDiv").css("display","block");
			$(".CashDiv").css("display","none");
			$(".bankutrDiv").css("display","none");
			$(".chequeDiv").css("display","flex");
			$(".bankneftutrDiv").css("display","none");
		} else {
			$(".bankDiv").css("display","block");
			$(".CashDiv").css("display","none");
			$(".bankutrDiv").css("display","block");
			$(".chequeDiv").css("display","none");
			$(".bankneftutrDiv").css("display","block");
		}
	});
	
	$(document).on('change','#paid_via ',function(){ 
		var paid_via = $(this).val();
		if(paid_via == "1"){
			//$(".bankDiv").css("display","block");
			//$(".CashDiv").css("display","none");
			$(".chequeDiv").css("display","flex");
		} else {
			//$(".bankDiv").css("display","none");
			//$(".CashDiv").css("display","none");
			$(".chequeDiv").css("display","none");
		}
	});

	jQuery.validator.addMethod("greaterThanZero", function(value, element) {
	    return this.optional(element) || (parseFloat(value) > 0);
	}, "Amount must be greater than zero");
	
	$('#banking-form').validate({ // initialize the plugin
        rules: {
            'expense_account' : {required: true},
            //'expense_account1' : {required: true},
            'expense_date' : {required: true},
            'expense_amount' : {required: true,number: true,greaterThanZero: 0},
            'expense_receipt' : {required: true},
            'expense_description' : {required: true},
            'expense_mode' : {required: true},
            'expense_bank_id' : {required: true},
            'expense_account_no' : {required: true},
            'expense_paid_via' : {required: true},
            'expense_cheque_no' : {required: true},
            'expense_branch_id' : {required: true},
            'expense_cash_type' : {required: true},
            'income_head_id' : {required: true},
            'income_head_id' : {required: true},
            'indirect_income_date' : {required: true},
            'indirect_income_amount' : {required: true,number: true,greaterThanZero: 0},
            'indirect_income_description' : {required: true},
            'indirect_income_mode' : {required: true},
            'indirect_income_bank_id' : {required: true},
            'indirect_income_account_no' : {required: true},
            'indirect_income_paid_via' : {required: true},
            'indirect_income_neft' : {required: true},
            'indirect_income_cheque_no' : {required: true},
            'indirect_income_branch_id' : {required: true},
            'indirect_income_cash_type' : {required: true},
            'vendor_payment_amount' : {required: true,number: true,greaterThanZero: 0},
            'payment_customer_amount' : {required: true,number: true,greaterThanZero: 0},
            'vendor_received_payment_amount' : {required: true,number: true,greaterThanZero: 0},
            'received_customer_payment_amount' : {required: true,number: true,greaterThanZero: 0},
            'payment_account_payment' : {required: true},
            'receive_payment_account_type' : {required: true},
            'vendor_neft' : {required: true,number: true},
            'customer_neft' : {required: true,number: true},
            'received_payment_vendor_neft' : {required: true,number: true},
            'received_payment_customer_neft' : {required: true,number: true},
            'expense_neft' : {required: true,number: true},
            'indirect_income_neft' : {required: true,number: true},
            'credit_card_amount' :  {required: true,number: true},
            'credit_card_account_number' : {required: true,number: true},
        },
        submitHandler: function(form) {
        	var formType = $('#banking-form').attr('data-type');
        	var type = $('option:selected', '#payment_account_payment').val();	
        	var receivedType = $('option:selected', '#receive_payment_account_type').val();	
        	var receivedSubType = $('option:selected', '#received_payment_vendor_type').val();
        	var subType = $('option:selected', '#payemnt_vendor_type').val();
        
        	if(formType == 2 && type == 1 && subType == 0){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 1 && subType == 1){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 1 && subType == 2){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();

        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 1 && subType == 3){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 2){
        		var amount = $('#payment_customer_amount').val();
        		var totalAmount = $('#customer_total_amount').val();
        		/*if(totalAmount == 0 || totalAmount == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else */if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 0){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 1){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 2){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 3){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 2){
        		var amount = $('#received_customer_payment_amount').val();
        		var totalAmount = $('#received_customer_total_amount').val();
        		/*if(totalAmount == 0 || totalAmount == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else*/ if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 3){
        		var amount = $('#credit_card_amount').val();
        		var totalAmount = $('#credit_card_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "No due bills!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}
        	return true;
        }
   	});

	$('#edit-banking-form').validate({ // initialize the plugin
        rules: {
            'expense_account' : {required: true},
            //'expense_account1' : {required: true},
            'expense_date' : {required: true},
            'expense_amount' : {required: true,number: true,greaterThanZero: 0},
            'expense_receipt' : {required: true},
            'expense_description' : {required: true},
            'expense_mode' : {required: true},
            'expense_bank_id' : {required: true},
            'expense_account_no' : {required: true},
            'expense_paid_via' : {required: true},
            'expense_cheque_no' : {required: true},
            'expense_branch_id' : {required: true},
            'expense_cash_type' : {required: true},
            'income_head_id' : {required: true},
            'income_head_id' : {required: true},
            'indirect_income_date' : {required: true},
            'indirect_income_amount' : {required: true,number: true,greaterThanZero: 0},
            'indirect_income_description' : {required: true},
            'indirect_income_mode' : {required: true},
            'indirect_income_bank_id' : {required: true},
            'indirect_income_account_no' : {required: true},
            'indirect_income_paid_via' : {required: true},
            'indirect_income_neft' : {required: true},
            'indirect_income_cheque_no' : {required: true},
            'indirect_income_branch_id' : {required: true},
            'indirect_income_cash_type' : {required: true},
            'vendor_payment_amount' : {required: true,number: true,greaterThanZero: 0},
            'payment_customer_amount' : {required: true,number: true,greaterThanZero: 0},
            'vendor_received_payment_amount' : {required: true,number: true,greaterThanZero: 0},
            'received_customer_payment_amount' : {required: true,number: true,greaterThanZero: 0},
            'payment_account_payment' : {required: true},
            'receive_payment_account_type' : {required: true},
            'vendor_neft' : {required: true,number: true},
            'customer_neft' : {required: true,number: true},
            'received_payment_vendor_neft' : {required: true,number: true},
            'received_payment_customer_neft' : {required: true,number: true},
            'expense_neft' : {required: true,number: true},
            'indirect_income_neft' : {required: true,number: true},
            'credit_card_amount' :  {required: true,number: true},
            'credit_card_account_number' : {required: true,number: true},
        },
        submitHandler: function(form) {
        	var formType = $('#edit-banking-form').attr('data-type');
        	/*var type = $('option:selected', '#payment_account_payment').val();	
        	var receivedType = $('option:selected', '#receive_payment_account_type').val();	
        	var receivedSubType = $('option:selected', '#received_payment_vendor_type').val();
        	var subType = $('option:selected', '#payemnt_vendor_type').val();*/

        	var type = $('#payment_account_payment').val();	
        	var receivedType = $('#receive_payment_account_type').val();	
        	var receivedSubType = $('#received_payment_vendor_type').val();
        	var subType = $('#payemnt_vendor_type').val();

        	if(formType == 2 && type == 1 && subType == 0){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 1 && subType == 1){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 1 && subType == 2){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();

        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 1 && subType == 3){
        		var amount = $('#vendor_payment_amount').val();
        		var totalAmount = $('#vendor_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 2 && type == 2){
        		var amount = $('#payment_customer_amount').val();
        		var totalAmount = $('#customer_total_amount').val();
        		/*if(totalAmount == 0 || totalAmount == ''){
        			swal("Warning!", "You have not any pending bills!", "warning");
        			return false;
        		}else */if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 0){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 1){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 2){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 1 && receivedSubType == 3){
        		var amount = $('#vendor_received_payment_amount').val();
        		var totalAmount = $('#vendor_received_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}else if(formType == 4 && receivedType == 2){
        		var amount = $('#received_customer_payment_amount').val();
        		var totalAmount = $('#received_customer_total_amount').val();
        		/*if(totalAmount == 0 || totalAmount == ''){
        			swal("Warning!", "Advanced amount not received!", "warning");
        			return false;
        		}else*/ if(parseInt(amount) < parseInt(totalAmount)){
        			swal("Warning!", "Amount should be greater than or equal to "
        				+totalAmount+"!", "warning");
        			return false;
        		}
        	}else if(formType == 3){
        		var amount = $('#credit_card_amount').val();
        		var totalAmount = $('#credit_card_total_amount').val();
        		if(parseInt(totalAmount) == 0 || parseInt(totalAmount) == ''){
        			swal("Warning!", "No due bills!", "warning");
        			return false;
        		}else if(parseInt(amount) != parseInt(totalAmount)){
        			swal("Warning!", "Amount should be equal or less than "+totalAmount+" !", "warning");
        			return false;
        		}
        	}
        	return true;
        }
    });

    innerListingTable = $('#branch_banking_inner_listing').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.banking.ajaxinnerlisting') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#filter-inner-listing').serializeArray(),
                d.type=$('#listingttype').val(),
                d.id=$('#listingttypeid').val(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'},
            {data: 'type', name: 'type'},
            {data: 'subtype', name: 'subtype'},
            {data: 'amount', name: 'amount'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
    });
    $(innerListingTable.table().container()).removeClass( 'form-inline' );
	
	// Payment Mode 
	$(document).on('change','#payment_account_payment ',function(){ 
		var payment_account_payment = $(this).val();
		if(payment_account_payment == "1"){
			$(".payment_vendor_div").css("display","flex");
			$(".payment_customer_div").css("display","none");
		} else if(payment_account_payment == "2"){
			$(".payment_vendor_div").css("display","none");
			$(".payment_customer_div").css("display","flex");
		} else {
			$(".payment_vendor_div").css("display","none");
			$(".payment_customer_div").css("display","none");
		}

		$('.rent_transaction_table').hide();
		$('.salary_transaction_table').hide();
	});
	
	$(document).on('change','#vendor_payment_mode ',function(){ 
		var payment_vendor_paid_via = $(this).val();
		if(payment_vendor_paid_via == "1"){
			$(".PaymentVendorbankDiv").css("display","block");
			$(".PaymentVendorCashDiv").css("display","none");
			$(".PaymentVendorChequebankDiv").css("display","none");
		} else {
			$(".PaymentVendorbankDiv").css("display","none");
			$(".PaymentVendorCashDiv").css("display","block");
			$(".PaymentVendorChequebankDiv").css("display","none");
		}
	});
	
	$(document).on('change','#payment_vendor_paid_via ',function(){ 
		var payment_vendor_paid_via = $(this).val();
		if(payment_vendor_paid_via == "1"){
			$(".PaymentVendorChequebankDiv").css("display","block");
			$(".PaymentVendorbankutrDiv").css("display","none");
			$(".PaymentVendorbankneftutrDiv").css("display","none");
		} else {
			$(".PaymentVendorChequebankDiv").css("display","none");
			$(".PaymentVendorbankutrDiv").css("display","block");
			$(".PaymentVendorbankneftutrDiv").css("display","block");
		}
	});
	
	$(document).on('change','#customer_payment_mode ',function(){ 
		var customer_payment_mode = $(this).val();
		if(customer_payment_mode == "1"){
			$(".PaymentCustomerbankDiv").css("display","block");
			$(".PaymentCustomerCashDiv").css("display","none");
			$(".PaymentCustomerCashDiv").css("display","none");
		} else {
			$(".PaymentCustomerbankDiv").css("display","none");
			$(".PaymentCustomerCashDiv").css("display","block");
			$(".PaymentCustomerCashDiv").css("display","none");
		}
	});
	
	$(document).on('change','#payment_customer_paid_via ',function(){ 
		var payment_customer_paid_via = $(this).val();
		if(payment_customer_paid_via == "1"){
			$(".PaymentCustomerChequebankDiv").css("display","block");
			$(".PaymentCustomerbankutrDiv").css("display","none");
			$(".PaymentCustomerbankneftutrDiv").css("display","none");
		} else {
			$(".PaymentCustomerChequebankDiv").css("display","none");
			$(".PaymentCustomerbankutrDiv").css("display","block");
			$(".PaymentCustomerbankneftutrDiv").css("display","block");
		}
	});
	
	$(document).on('change','#credit_card_mode ',function(){ 
		var credit_card_mode = $(this).val();
		if(credit_card_mode == "1"){
			$(".CreditCardbankDiv").css("display","block");
			$(".CreditCardCustomerChequebankDiv").css("display","none");
		} else {
			$(".CreditCardbankDiv").css("display","none");
			$(".CreditCardCustomerChequebankDiv").css("display","none");
		}
	});
	
	$(document).on('change','#credit_card_customer_paid_via ',function(){ 
		var credit_card_customer_paid_via = $(this).val();
		if(credit_card_customer_paid_via == "1"){
			$(".CreditCardCustomerChequebankDiv").css("display","block");
			$('.CreditCardbankneftutrDiv').hide();
			$('.CreditCardbankutrDiv').hide();
		} else {
			$(".CreditCardCustomerChequebankDiv").css("display","none");
			$('.CreditCardbankneftutrDiv').show();
			$('.CreditCardbankutrDiv').show();
		}
	});
	
	$(document).on('change','#receive_payment_account_type ',function(){ 
		var receive_payment_account_type = $(this).val();
		if(receive_payment_account_type == "1"){
			$(".received_payment_vendor_div").css("display","flex");
			$(".received_payment_customer_div").css("display","none");
		} else if(receive_payment_account_type == "2"){
			$(".received_payment_vendor_div").css("display","none");
			$(".received_payment_customer_div").css("display","flex");
		} else {
			$(".received_payment_vendor_div").css("display","none");
			$(".received_payment_customer_div").css("display","none");
		}
	});
	
	$(document).on('change','#vendor_received_payment_mode ',function(){ 
		var vendor_received_payment_mode = $(this).val();
		if(vendor_received_payment_mode == "1"){
			$(".ReceivedPaymentVendorbankDiv").css("display","block");
			$(".ReceivedPaymentVendorChequebankDiv").css("display","none");
			$(".ReceivedPaymentVendorCashDiv").css("display","none");
		} else {
			$(".ReceivedPaymentVendorbankDiv").css("display","none");
			$(".ReceivedPaymentVendorCashDiv").css("display","block");
			$(".ReceivedPaymentVendorChequebankDiv").css("display","none");
		}
	});
	
	$(document).on('change','#received_payment_vendor_paid_via ',function(){ 
		var received_payment_vendor_paid_via = $(this).val();
		if(received_payment_vendor_paid_via == "1"){
			$(".ReceivedPaymentVendorbankutrDiv").hide();
			$(".ReceivedPaymentVendorChequebankDiv").css("display","block");
			$(".ReceivedPaymentVendorbankneftutrDiv").hide();
		} else {
			$(".ReceivedPaymentVendorbankutrDiv").show();
			$(".ReceivedPaymentVendorChequebankDiv").css("display","none");
			$(".ReceivedPaymentVendorbankneftutrDiv").show();
		}
	});
	
	$(document).on('change','#received_payment_customer_mode ',function(){ 
		var customer_received_payment_mode = $(this).val();
		if(customer_received_payment_mode == "1"){
			$(".ReceivedPaymentCustomerbankDiv").css("display","block");
			$(".ReceivedPaymentCustomerChequebankDiv").css("display","none");
			$(".ReceivedPaymentCustomerCashDiv").css("display","none");
		} else {
			$(".ReceivedPaymentCustomerbankDiv").css("display","none");
			$(".ReceivedPaymentCustomerChequebankDiv").css("display","none");
			$(".ReceivedPaymentCustomerCashDiv").css("display","block");
		}
	});
	
	$(document).on('change','#received_payment_customer_paid_via ',function(){ 
		var received_payment_customer_paid_via = $(this).val();
		if(received_payment_customer_paid_via == "1"){
			$(".ReceivedPaymentCustomerChequebankDiv").css("display","block");
			$(".ReceivedPaymentCustomerbankutrDiv").hide();
			$(".ReceivedPaymentCustomerbankneftutrDiv").hide();
		} else {
			$(".ReceivedPaymentCustomerChequebankDiv").css("display","none");
			$(".ReceivedPaymentCustomerbankutrDiv").show();
			$(".ReceivedPaymentCustomerbankneftutrDiv").show();
		}
	});
	
	$(document).on('change','.income_head_id',function(){
		var hId = $(this).attr("data-row-id");
		var head_id = $(this).val();
		
		if(head_id > 0){
			$.ajax({
				type: "POST",  
				url: "{!! route('admin.getHeadLedgerData') !!}",
				dataType: 'JSON',
				data: {'hId':hId,'head_id':head_id},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					$("#income_head_id"+hId).empty();
					$("#income_head_id"+hId).append("<option value=''>Choose Sub Head</option>");
					if(response.length > 0){
						for(var k=0; k<response.length; k++){
							$("#income_head_id"+hId).append("<option value="+response[k].head_id+">"+response[k].sub_head+"</option>");
						}
					}
				}
			}); 
		}	
	});
	
	$(document).on('change','#indirect_income_mode ',function(){ 
		var indirect_income_mode = $(this).val();
		if(indirect_income_mode == "1"){
			$(".IndirectIncomebankDiv").css("display","block");
			$(".IndirectIncomeCashDiv").css("display","none");
			$(".IncomdchequeDiv").css("display","none");
			$(".IndirectIncomebankneftutrDiv").hide();
			$(".IndirectIncomebankutrDiv").hide();
		} else if(indirect_income_mode == "2"){
			$(".IndirectIncomebankDiv").css("display","none");
			$(".IndirectIncomeCashDiv").css("display","block");
			$(".IncomdchequeDiv").css("display","none");
			$(".IndirectIncomebankneftutrDiv").hide();
			$(".IndirectIncomebankutrDiv").hide();
		} else {
			$(".IndirectIncomebankDiv").css("display","none");
			$(".IndirectIncomeCashDiv").css("display","none");
			$(".IncomdchequeDiv").css("display","none");
			$(".IndirectIncomebankneftutrDiv").hide();
			$(".IndirectIncomebankutrDiv").hide();
		}
	});
	
	$(document).on('change','#indirect_income_paid_via ',function(){ 
		var indirect_income_paid_via = $(this).val();
		if(indirect_income_paid_via == "1"){
			$(".IndirectIncomebankDiv").css("display","block");
			$(".IndirectIncomeCashDiv").css("display","none");
			$(".IndirectIncomebankutrDiv").css("display","none");
			$(".IncomdchequeDiv").css("display","flex");
			$(".IndirectIncomebankneftutrDiv").css("display","none");
		} else {
			$(".IndirectIncomebankDiv").css("display","block");
			$(".IndirectIncomeCashDiv").css("display","none");
			$(".IndirectIncomebankutrDiv").css("display","block");
			$(".IncomdchequeDiv").css("display","none");
			$(".IndirectIncomebankneftutrDiv").css("display","block");
		}
	});
	
	/*$(document).on('change','#indirect_income_paid_via ',function(){ 
		var paid_via = $(this).val();
		if(paid_via == "1"){
			$(".IncomdchequeDiv").css("display","flex");
		} else {
			$(".IncomdchequeDiv").css("display","none");
		}
	});*/
	
	$(document).on('click', '.delete-transaction', function(e){

        var url = $(this).attr('href');

        e.preventDefault();

        swal({

          title: "Are you sure, you want to delete this entry?",

          text: "",

          icon: "warning",

          buttons: [

            'No, cancel it!',

            'Yes, I am sure!'

          ],

          dangerMode: true,

        }).then(function(isConfirm) {

          if (isConfirm) {

            location.href = url;

          } 

        });

  	})

	$(document).on('change', '#payemnt_vendor_type', function(e){
		var type = $(this).val();
		var vendorType = $('option:selected', '#payment_account_payment').val();
		if(vendorType == 1){
			var branchId = $('option:selected', '#payment_branch_id').val();
		}else{
			var branchId = $('option:selected', '#customer_branch_id').val();
		}

		if(type == 2){
			$('.vendor-associate-name').html('Associate ID <sup>*</sup>');
		}else{
			$('.vendor-associate-name').html('Vendor Name <sup>*</sup>');
		}

		var branchId = $('option:selected', '#payment_branch_id').val();
		var contactRes = contactList(type,vendorType,branchId);
		$('.transaction_table').hide();
		$('.transaction_table_list').html('');
	})

	$(document).on('change', '#payment_account_payment', function(e){
		var type = 5;
		var vendorType = $('option:selected', '#payment_account_payment').val();
		if(vendorType == 1){
			var branchId = $('option:selected', '#payment_branch_id').val();
		}else{
			var branchId = $('option:selected', '#customer_branch_id').val();
		}
		var contactRes = contactList(type,vendorType,branchId);
		$('.transaction_table').hide();
		$('.transaction_table_list').html('');
	})

	$(document).on('change', '#receive_payment_account_type', function(e){
		var type = 5;
		var vendorType = $('option:selected', '#receive_payment_account_type').val();
		if(vendorType == 1){
			var branchId = $('option:selected', '#received_payment_branch_id').val();
		}else{
			var branchId = $('option:selected', '#received_payment_customer_branch_id').val();
		}
		
		var contactRes = contactList(type,vendorType,branchId);
		$('.transaction_table').hide();
		$('.transaction_table_list').html('');
	})

	$(document).on('change', '#received_payment_vendor_type', function(e){
		var type = $(this).val();
		var vendorType = $('option:selected', '#receive_payment_account_type').val();
		if(vendorType == 1){
			var branchId = $('option:selected', '#received_payment_branch_id').val();
		}else{
			var branchId = $('option:selected', '#received_payment_customer_branch_id').val();
		}

		if(type == 2){
			$('.received-vendor-associate-name').html('Associate ID <sup>*</sup>');
		}else{
			$('.received-vendor-associate-name').html('Vendor Name <sup>*</sup>');
		}

		var contactRes = contactList(type,vendorType,branchId);
	})

	/********************** Pending & Advanced  **************************/
  	$(document).on('change', '#payment_vendor_name', function(e){
  		var type = $('option:selected', '#payemnt_vendor_type').val();
  		var typeId = $(this).val();
	  	$.ajax({

			type: "POST", 

		    url: "{!! route('admin.banking.transaction') !!}",

		    dataType: 'JSON',

		    data: {'type':type,'typeId':typeId},

		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

		    },

		    success: function(response) {
		    	if(response.type == 0)
		    	{
		    		$('.rent_transaction_table').show();
		    		$('#rent_pending_bills').val(1);
		    		$(".rent_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
			    		$.each(response.result, function (index, value) {
			    			amount = parseInt(amount)+parseInt(value.rent_amount);
			    			//var date = moment(value.created_at).format('DD/MM/YYYY');
			    			var pendingAmount = (value.rent_amount)-(value.transferred_amount);
		      				$(".rent_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+value.month_name+"</td><td>"+value.year+"</td><td>"+Math.round(pendingAmount)+"</td><td><input type='text' name='rent_payment_amount["+value.id+"]' class='rent_payment_amount_"+index+" rent_payment_amount form-control' style='width:100px;' data-pending-rent="+Math.round(pendingAmount)+" required></td></tr>");
		      			});
			    	}else{
			    		$(".rent_transaction_table_list").append('<tr><td colspan="5" style="text-align: center;">No Record Found!</td></tr>');
			    	}
		    	}else if(response.type == 1){
		    		$('.salary_transaction_table').show();
		    		$(".salary_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
			    			amount = parseInt(amount)+parseInt(value.total_salary);
			    			//var date = moment(value.created_at).format('DD/MM/YYYY');
			    			var pendingAmount = (value.total_salary)-(value.transferred_salary);
		      				$(".salary_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+value.month_name+"</td><td>"+value.year+"</td><td>"+Math.round(pendingAmount)+"</td><td><input type='text' name='salary_payment_amount["+value.id+"]' class='salary_payment_amount_"+index+" salary_payment_amount form-control' style='width:100px;' data-pending-salary="+Math.round(pendingAmount)+" required></td></tr>");
		      			});
		    		}else{
			    		$(".salary_transaction_table_list").append('<tr><td colspan="5" style="text-align: center;">No Record Found!</td></tr>');
			    	}
		    	}else if(response.type == 2){
		    		$('.associate_transaction_table').show();
		    		$(".associate_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
			    			var commissionAmount = Math.round(value.amount)-Math.round(value.transferred_amount);
			    			var fuelAmount = Math.round(value.fuel)-Math.round(value.transferred_fuel_amount);
		      				$(".associate_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+commissionAmount+"</td><td>"+fuelAmount+"</td><td><input type='text' name='associate_commission_payment_amount["+value.id+"]' class='associate_payment_amount_"+index+" associate_payment_amount associate_commission_payment_amount form-control' style='width:100px;' data-pending-associate="+commissionAmount+" required></td><td><input type='text' name='associate_fuel_payment_amount["+value.id+"]' class='associate_payment_amount_"+index+" associate_payment_amount associate_fuel_payment_amount form-control' style='width:100px;' data-pending-associate="+fuelAmount+" required></td></tr>");
		      			});
		    		}else{
			    		$(".associate_transaction_table_list").append('<tr><td colspan="4" style="text-align: center;">No Record Found!</td></tr>');
			    	}
		    	}else if(response.type == 3){
		    		$('.vendor_transaction_table').show();
		    		$(".vendor_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
			    			var dueAmount = Math.round(value.payble_amount);
		      				$(".vendor_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+value.bill_number+"</td><td>"+dueAmount+"</td><td><input type='text' name='vendor_pending_payment_amount["+value.id+"]' class='vendor_pending_payment_amount"+index+" vendor_pending_payment_amount form-control' style='width:100px;' data-pending-vendor="+dueAmount+" required></td></tr>");
		      			});
		    		}else{
			    		$(".vendor_transaction_table_list").append('<tr><td colspan="4" style="text-align: center;">No Record Found!</td></tr>');
			    	}	
		    	}
		    }
	   	});
  	})

  	$(document).on('change','.salary_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-salary');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to pending amount!", "warning");
  		}
  		var sum = 0;
        $(".salary_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_payment_amount').val(Math.round(sum));
	   	$('#vendor_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.rent_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-rent');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to pending amount!", "warning");
  		}
  		var sum = 0;
        $(".rent_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_payment_amount').val(Math.round(sum));
	   	$('#vendor_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.associate_commission_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-associate');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to pending amount!", "warning");
  		}
  		var sum = 0;
        $(".associate_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_payment_amount').val(Math.round(sum));
	   	$('#vendor_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.associate_fuel_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-associate');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to pending amount!", "warning");
  		}
  		var sum = 0;
        $(".associate_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_payment_amount').val(Math.round(sum));
	   	$('#vendor_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.vendor_pending_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-vendor');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to pending amount!", "warning");
  		}
  		var sum = 0;
        $(".vendor_pending_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_payment_amount').val(Math.round(sum));
	   	$('#vendor_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.rent_advanced_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-advanced-rent');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to advanced amount!", "warning");
  		}
  		var sum = 0;
        $(".rent_advanced_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_received_payment_amount').val(Math.round(sum));
	   	$('#vendor_received_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change', '#payment_customer_name', function(e){
  		var accountType = $('option:selected', '#payment_account_payment').val();
  		var type = $('#cus_advance_type').val();
  		var typeId = $(this).val();
	  	$.ajax({

			type: "POST", 

		    url: "{!! route('admin.banking.advancedamount') !!}",

		    dataType: 'JSON',

		    data: {'type':type,'accountType':accountType,'typeId':typeId},

		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

		    },

		    success: function(response) {
	    		$('.customer_transaction_table').show();
	    		var amount = 0;
	    		if(response.result.length > 0){
	    			$.each(response.result, function (index, value) {
	    				if(value.amount > 0){
	      					$(".customer_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+Math.round(value.amount)+"</td><td><input type='text' name='cus_payment_amount["+value.id+"]' class='cus_payment_amount_"+index+" cus_payment_amount form-control' style='width:100px;' data-pending-cus="+Math.round(value.amount)+" required></td></tr>");
	    				}
	      			});
	    		}else{
	    			$(".customer_transaction_table_list").append('<tr><td colspan="3" style="text-align: center;">No Record Found!</td></tr>');
	    		}
	    		
		    }
	   	});
  	})

  	$(document).on('change','.cus_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-cus');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be equal to "+pendingAmount+"!", "warning");
  		}
  		var sum = 0;
        $(".cus_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#payment_customer_amount').val(Math.round(sum));
	   	$('#customer_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change', '#received_payment_customer_name', function(e){
  		var accountType = $('option:selected', '#receive_payment_account_type').val();
  		var type = $('#received_cus_advance_type').val();
  		var typeId = $(this).val();
	  	$.ajax({

			type: "POST", 

		    url: "{!! route('admin.banking.advancedamount') !!}",

		    dataType: 'JSON',

		    data: {'type':type,'accountType':accountType,'typeId':typeId},

		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

		    },

		    success: function(response) {
	    		$('.received_customer_transaction_table').show();
	    		$(".received_customer_transaction_table_list").html('');
	    		var customer_refund_payment = 0;
	    		if(response.result.length > 0){
	    			$.each(response.result, function (index, value) {
	    				if(value.customer_refund_payment > 0){
	    					$(".received_customer_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+Math.round(value.customer_refund_payment)+"</td><td><input type='text' name='received_cus_payment_amount["+value.id+"]' class='received_cus_payment_amount_"+index+" received_cus_payment_amount form-control' style='width:100px;' data-pending-received-cus="+Math.round(value.customer_refund_payment)+" required></td></tr>");
	    				}
	      			});
	    		}else{
	    			$(".received_customer_transaction_table_list").append('<tr><td colspan="3" style="text-align: center;">No Record Found!</td></tr>')
	    		}
	    		
		    }
	   	});
  	})

  	$(document).on('change','.received_cus_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-received-cus');
  		var amount = $(this).val();
  		if(parseInt(amount) != parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be equal to "+pendingAmount+"!", "warning");
  		}
  		var sum = 0;
        $(".received_cus_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#received_customer_payment_amount').val(Math.round(sum));
	   	$('#received_customer_total_amount').val(Math.round(sum));
  	});

  	/********************** Received Advanced **************************/
  	$(document).on('change', '#received_payment_vendor_name', function(e){
  		var accountType = $('option:selected', '#receive_payment_account_type').val();
  		var type = $('option:selected', '#received_payment_vendor_type').val();
  		var typeId = $(this).val();

	  	$.ajax({

			type: "POST", 

		    url: "{!! route('admin.banking.advancedamount') !!}",

		    dataType: 'JSON',

		    data: {'type':type,'typeId':typeId,'accountType':accountType},

		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

		    },

		    success: function(response) {
		    	if(response.type == 0){
		    		$('.rent_advanced_transaction_table').show();
		    		$(".rent_advanced_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
		    				if(value.advanced_amount > 0){
		    					var amount = Math.round(value.amount);
		    					var advancedAmountVal = Math.round(value.advanced_amount);
		      					$(".rent_advanced_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+amount+"</td><td>"+advancedAmountVal+"</td><td><input type='text' name='rent_advanced_payment_amount["+value.id+"]' class='rent_advanced_payment_amount_"+index+" rent_advanced_payment_amount form-control' data-advanced-rent="+advancedAmountVal+" style='width:100px;' required></td></tr>");
		      				}
		      			});
		    		}else{
		    			$(".rent_advanced_transaction_table_list").append('<tr><td colspan="5" style="text-align: center;">No Record Found!</td></tr>');
		    		}
		    		
		    	}else if(response.type == 1){
		    		$('.salary_advanced_transaction_table').show();
		    		$(".salary_advanced_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
		    				var amount = Math.round(value.amount);
		    				var advancedAmount = Math.round(value.advanced_amount);
		    				if(value.advanced_amount > 0){
		      					$(".salary_advanced_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+amount+"</td><td>"+advancedAmount+"</td><td><input type='text' name='salary_advanced_payment_amount["+value.id+"]' class='salary_advanced_payment_amount_"+index+" salary_advanced_payment_amount form-control' data-advanced-salary="+advancedAmount+" style='width:100px;' required></td></tr>");
		      				}
		      			});
		    		}else{
		    			$(".salary_advanced_transaction_table_list").append('<tr><td colspan="4" style="text-align: center;">No Record Found!</td></tr>');
		    		}
		    		
		    	}else if(response.type == 2){
		    		$('.associate_advanced_transaction_table').show();
		    		$(".associate_advanced_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
		    				var amount = Math.round(value.amount);
		    				var advancedAmount = Math.round(value.advanced_amount);
		    				if(value.advanced_amount > 0){
		      					$(".associate_advanced_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+amount+"</td><td>"+advancedAmount+"</td><td><input type='text' name='associate_advanced_payment_amount["+value.id+"]' class='associate_advanced_payment_amount_"+index+" associate_advanced_payment_amount form-control' data-advanced-associate="+advancedAmount+" style='width:100px;' required></td></tr>");
		      				}
		      			});
		    		}else{
		    			$(".associate_advanced_transaction_table_list").append('<tr><td colspan="4" style="text-align: center;">No Record Found!</td></tr>');
		    		}
		    		
		    	}else if(response.type == 3){
		    		$('.vendor_advanced_transaction_table').show();
		    		$(".vendor_advanced_transaction_table_list").html('');
		    		var amount = 0;
		    		if(response.result.length > 0){
		    			$.each(response.result, function (index, value) {
		    				var amount = Math.round(value.amount);
		    				var advancedAmount = Math.round(value.advanced_amount);
		    				if(value.advanced_amount > 0){
		      					$(".vendor_advanced_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+amount+"</td><td>"+advancedAmount+"</td><td><input type='text' name='vendor_advanced_payment_amount["+value.id+"]' class='vendor_advanced_payment_amount_"+index+" vendor_advanced_payment_amount form-control' data-advanced-vendor="+advancedAmount+" style='width:100px;' required></td></tr>");
		      				}
		      			});
		    		}else{
		    			$(".vendor_advanced_transaction_table_list").append('<tr><td colspan="4" style="text-align: center;">No Record Found!</td></tr>');
		    		}
		    		
		    	}
		    }
	   	});
  	})

	/********************** Credit Card ******************************/
	$(document).on('change', '#credit_card_id', function(e){
  		var typeId = $(this).val();
	  	$.ajax({

			type: "POST", 

		    url: "{!! route('admin.banking.transaction') !!}",

		    dataType: 'JSON',

		    data: {'type':4,'typeId':typeId},

		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

		    },

		    success: function(response) {
		    	
	    		$('.credit_card_transaction_table').show();
	    		$(".credit_card_transaction_table_list").html('');
	    		var amount = 0;
	    		if(response.result.length > 0){
	    			$.each(response.result, function (index, value) {
	    				if(value.bill_id != null){
	    					var biiNumber = value.bill_id;
	    				}else{
	    					var biiNumber = 'N/A';
	    				}
	    				var amount = Math.round(value.total_amount);
		    			var dueAmount = Math.round(value.total_amount-value.used_amount);
	      				$(".credit_card_transaction_table_list").append("<tr><td>"+value.id+"</td><td>"+biiNumber+"</td><td>"+amount+"</td><td>"+dueAmount+"</td><td><input type='text' name='credit_card_payment_amount["+value.id+"]' class='credit_card_payment_amount"+index+" credit_card_payment_amount form-control' style='width:100px;' data-pending-credit-card="+dueAmount+" required></td></tr>");
	      			});
	    		}else{
		    		$(".credit_card_transaction_table_list").append('<tr><td colspan="4" style="text-align: center;">No Record Found!</td></tr>');
		    	}	
		    }
	   	});
  	})

  	$(document).on('change','.credit_card_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-pending-credit-card');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to advanced amount!", "warning");
  		}
  		var sum = 0;
        $(".credit_card_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#credit_card_amount').val(Math.round(sum));
	   	$('#credit_card_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.salary_advanced_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-advanced-salary');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to advanced amount!", "warning");
  		}
  		var sum = 0;
        $(".salary_advanced_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_received_payment_amount').val(Math.round(sum));
	   	$('#vendor_received_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.associate_advanced_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-advanced-associate');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to advanced amount!", "warning");
  		}
  		var sum = 0;
        $(".associate_advanced_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_received_payment_amount').val(Math.round(sum));
	   	$('#vendor_received_total_amount').val(Math.round(sum));
  	});

  	$(document).on('change','.vendor_advanced_payment_amount',function(){
  		var pendingAmount = $(this).attr('data-advanced-vendor');
  		var amount = $(this).val();
  		if(parseInt(amount) > parseInt(pendingAmount))
  		{
  			$(this).val('');
  			swal("Warning!", "Amount should be less than or equal to advanced amount!", "warning");
  		}
  		var sum = 0;
        $(".vendor_advanced_payment_amount").each(function(){
            sum += +$(this).val();
        });
        $('#vendor_received_payment_amount').val(Math.round(sum));
	   	$('#vendor_received_total_amount').val(Math.round(sum));
  	});

})

function contactList(type,vendorType,branchId)
{ 
	$("#payment_vendor_name,#received_payment_vendor_name").html('');

	$("#payment_vendor_name,#received_payment_vendor_name").append("<option value=''>Please Select</option>");

	if(vendorType == 1){
		if(type == 1){
			var url = "{!! route('admin.jv.getemployees') !!}"
		}else if(type == 0){
			var url = "{!! route('admin.jv.getrentliability') !!}"
		}else if(type == 2){
			var url = "{!! route('admin.jv.getassociates') !!}"
		}else if(type == 3){
			var url = "{!! route('admin.banking.getvendor') !!}"
		}
	}else if(vendorType == 2){
		var url = "{!! route('admin.banking.getcustomers') !!}"
	}
	

	$("#payment_vendor_name,#received_payment_vendor_name,#payment_customer_name,#received_payment_customer_name").select2({
		minimumInputLength: 3,
	    ajax: {

			type: "POST", 

			delay: 250, 

	        url: url,

	        dataType: 'JSON',

	        data: function(params) {
                return {
                    query: params.term, // search term
                    branchId: branchId,
                    "_token": "{{ csrf_token() }}",
                };
            },
            processResults: function(response) {

                return {
                    results: response
                };
            },
            cache: true

		}
	});
}

function searchForm()
{ 
    if($('#filter-inner-listing').valid())
    {
        $('#is_search').val("yes");
        innerListingTable.draw();
    }
}

function resetForm()
{
    $('#is_search').val("yes");
    $('#start_date').val('');
    $('#end_date').val('');
    innerListingTable.draw();
}
</script>