<script type="text/javascript">
var employeeTable;
$(document).ready(function () {
	
	
  var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,   
    autoclose: true
  });

  $('#head_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,
    autoclose: true
  });
  
  
  $(document).on('change','#type',function(){ 
		var type = $(this).val();
		if(type == "1"){
			$("#moneyOutDiv").css("display","none");
			$("#moneyInDiv").css("display","flex");
			$("#creditCardDiv").css("display","none");
		} else if(type == "2"){
			$("#moneyInDiv").css("display","none");
			$("#moneyOutDiv").css("display","flex");
			$("#otherIncomeDiv").css("display","none");
		} else {
			$("#moneyInDiv, #moneyOutDiv, #creditCardDiv, #otherIncomeDiv").css("display","none");
		}
  });
  
  
   $(document).on('change','#money_out',function(){ 
		var money_out = $(this).val();
		if(money_out == "3"){
			$("#creditCardDiv").css("display","block");
		} else {
			$("#creditCardDiv").css("display","none");
		}
  });
  
  
  $(document).on('change','#money_in',function(){ 
		var money_in = $(this).val();
		if(money_in == "1"){
			$("#otherIncomeDiv").css("display","none");
		} else {
			$("#otherIncomeDiv").css("display","block");
		}
  });
  
  
   $(document).on('change','#bank_id',function(){ 
		var bank_id = $(this).val();
		var current_div_id = $(this).attr("id");
		
		$.ajax({
		  type: "POST",  
		  url: "{!! route('admin.get-banks-data') !!}",
		  dataType: 'JSON',
		  data: {'bank_id':bank_id},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) {
			  
				$("#bank_account_number").empty();
				$("#bank_account_number").append('<option value="">Select Account Number...</option>');
				if(response.accountNumbers.length > 0){
					for(var i=0; i < response.accountNumbers.length; i++){
						var account_id =  response.accountNumbers[i]['id'];
						var account_no =  response.accountNumbers[i]['account_no'];
						$("#bank_account_number").append('<option value="'+account_id+'">'+account_no+'</option>');
					}
				}

			}
		});
		
  });
  
  
  
  
  $(document).on('change','#bank_account_number',function(){ 
		var account_id = $(this).val();
		
		$.ajax({
		  type: "POST",  
		  url: "{!! route('admin.get-cheque-data') !!}",
		  dataType: 'JSON',
		  data: {'account_id':account_id},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) {
			  
				$("#cheque_id").empty();
				$("#cheque_id").append('<option value="">Select Cheque...</option>');
				if(response.cheque.length > 0){
					for(var j=0; j < response.cheque.length; j++){
						var cheque_id =  response.cheque[j]['id'];
						var cheque_no =  response.cheque[j]['cheque_no'];
						$("#cheque_id").append('<option value="'+cheque_id+'">'+cheque_no+'</option>');
					}
				}

			}
		});
		
  });
  
  
  
  
  $(document).on('change','#bank_other_income_bank_id',function(){ 
		var bank_id = $(this).val();
		var current_div_id = $(this).attr("id");
		
		$.ajax({
		  type: "POST",  
		  url: "{!! route('admin.get-banks-data') !!}",
		  dataType: 'JSON',
		  data: {'bank_id':bank_id},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) {
			  
				$("#bank_other_income_account_number").empty();
				$("#bank_other_income_account_number").append('<option value="">Select Account Number...</option>');
				if(response.accountNumbers.length > 0){
					for(var i=0; i < response.accountNumbers.length; i++){
						var account_id =  response.accountNumbers[i]['id'];
						var account_no =  response.accountNumbers[i]['account_no'];
						$("#bank_other_income_account_number").append('<option value="'+account_id+'">'+account_no+'</option>');
					}
				}

			}
		});
		
  });
  
  
  
  
  $(document).on('change','#bank_other_income_account_number',function(){ 
		var account_id = $(this).val();
		
		$.ajax({
		  type: "POST",  
		  url: "{!! route('admin.get-cheque-data') !!}",
		  dataType: 'JSON',
		  data: {'account_id':account_id},
		  headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		  },
		  success: function(response) {
			  
				$("#bank_other_income_cheque_id").empty();
				$("#bank_other_income_cheque_id").append('<option value="">Select Cheque...</option>');
				if(response.cheque.length > 0){
					for(var j=0; j < response.cheque.length; j++){
						var cheque_id =  response.cheque[j]['id'];
						var cheque_no =  response.cheque[j]['cheque_no'];
						$("#bank_other_income_cheque_id").append('<option value="'+cheque_id+'">'+cheque_no+'</option>');
					}
				}

			}
		});
		
  });
  
  
  $(document).on('change','#mode',function(){ 
		var mode = $(this).val();
		if(mode == "1"){
			$("#bankOtherIncome").css("display","block");
			$("#cashOtherIncome").css("display","none");
		} else if(mode == "2") {
			$("#cashOtherIncome").css("display","block");
			$("#bankOtherIncome").css("display","none");
		} else {
			$("#bankOtherIncome").css("display","none");
			$("#cashOtherIncome").css("display","none");
		}
  });
  
  
  
  
  
  }); 
  

</script>