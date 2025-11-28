<script type="text/javascript">
$(document).ready(function() {
    $.validator.addMethod("lessThanEquals",
    function (value, element, param) {
          var $otherElement = $(param);
          return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
       return value > target.val();
    }, "Amount should be less than OR equals current available amount.");
$.validator.addMethod("decimal", function(value, element,p) {
      if(this.optional(element) || /^\d*\.?\d*$/g.test(value)==true)
      {
        $.validator.messages.decimal = "";
        result = true;
      }else{
        if($('#bank_account_number').val()!=2)
            {
                $.validator.messages.decimal = "Please enter valid numeric number.";
                result = false;
            }
            else{
                $.validator.messages.chkbank = "";
                result = true;
            }
      }
    return result;
  }, "");
jQuery.validator.addMethod("greaterThanZero", function(value, element) {
    return this.optional(element) || (parseFloat(value) > 0);
}, "Amount must be greater than Zero");
$.validator.addMethod("zero1", function(value, element,p) {
      if(value>=0)
      {
        $.validator.messages.zero1 = "";
        result = true;
      }else{
            if($('#bank_account_number').val()!=2)
            {
                $.validator.messages.zero1 = "Amount must be greater than 0.";
                result = false;
            }
            else{
                $.validator.messages.chkbank = "";
                result = true;
            }
      }
    return result;
  }, "")
  $.validator.addMethod("chkbank", function(value, element,p) {
      if($( "#payment_mode" ).val()==1)
      {
      if(parseFloat($('#bank_balance').val()) >= parseFloat($('#amount').val()))
      {
        $.validator.messages.chkbank = "";
        result = true;
      }else{
            if($('#bank_account_number').val()!=2)
            {
            $.validator.messages.chkbank = "Bank available balance must be grather than or equal to   amount";
            result = false;
            }
            else{
                $.validator.messages.chkbank = "";
                result = true;
            }
      }
    }
    else
    {
      $.validator.messages.chkbank = "";
        result = true;
    }
    return result;
  }, "");
	  $.validator.addMethod("chkBranch", function(value, element,p) {
		if($( "#payment_mode" ).val()==0)
		  {
		  if(parseFloat($('#available_balance').val()) >= parseFloat($('#amount').val()))
		  {
			$.validator.messages.chkBranch = "";
			result = true;
		  }else{
			$.validator.messages.chkBranch = "Branch total balance must be grather than or equal to  amount";
			result = false;
		  }
		}
		else
		{
		  $.validator.messages.chkBranch = "";
			result = true;
		}
		return result;
	  }, "");
	$.validator.addMethod("neft", function(value, element,p) {
		if($( "#bank_mode" ).val()==1 && $( "#payment_mode" ).val()==2)
		  {
			a= parseFloat($('#rtgs_neft_charge').val())+parseFloat($('#amount').val());
		  if(parseFloat($('#bank_balance').val()) >= parseFloat(a))
		  {
			$.validator.messages.neft = "2";
			result = true;
		  }else{
			$.validator.messages.neft = "Bank available balance must be grather than or equal to  sum of amount or NEFT charge";
			result = false;
		  }
		}
		else
		{
		  $.validator.messages.neft = "2";
			result = true;
		}
		return result;
	  }, "");
    $('#withdrawal-ssb').validate({ // initialize the plugin
        rules: {
            'branch_id' : 'required',
            'company_id' : 'required',
            'date' : 'required',
            'ssb_account_number' : {required: true, number: true},
            'amount' : {required: true, decimal: true,lessThanEquals: "#account_balance",zero1:true,greaterThanZero:true},
             // 'account_balance' : {min: 500},
            'bank' : 'required',
            'bank_mode' : 'required',
            'cheque_number' : 'required',
            'utr_no' : {required: true, number: true},
            'rtgs_neft_charge' : {required: true, decimal: true},
            'mbank' : 'required',
            'mbankac' : {required: true, number: true,minlength: 8,maxlength: 20},
            'mbankifsc' : { required:true, checkIfsc:true },
            'bank_balance' : {required: true, decimal: true,zero1:true,chkbank:true,neft:true},
            'payment_mode' : {required: true},
            'bank_account_number' : {required: true},
        },
        messages: {
            account_balance: {
                min: "Your ssb account balance should be greater than OR equals 500"
            },
        },
        submitHandler: function() {
            var accountBalance = $("#account_balance").val();
            var amount = $("#amount").val();
            var balance = accountBalance-amount;
            //  if(balance < 500){
            //    swal("Warning!", "Minimum Balance Should be 500 !", "warning");
            //     return false;
            // }
            var paymentModeVal = $( "#payment_mode option:selected").val();
            var microDaybookAmount = $( "#available_balance").val();
            if(paymentModeVal == 0){
                if(parseInt(amount) > parseInt(microDaybookAmount)){
                    swal("Warning!", "Amount should be less than or equal to micro daybook amount!", "warning");
                    return false;
                }
            }
            if(paymentModeVal == 1){ 
                if($('#bank_account_number').val()!=2)
                {
                    if(parseFloat(amount) > parseFloat($('#bank_balance').val())){
                        swal("Warning!", "Amount should be less than or equal to bank amount!", "warning");
                        return false;
                    }
                }
            }
            $('.submit').prop('disabled',true);
            return true;
        }
    });
    $(document).on('change','#branch',function(){
        var branchCode = $('option:selected', this).data('code');
        var branchId = $('option:selected', this).val();
        var companyId = $( "#company_id option:selected" ).val();
        var date = $('#date').val();
        $('#branch_code').val(branchCode);
        $('#ssb_account_number').val('');
        $('#account_number').val('');
        $('#account_holder_name').val('');
        $('#account_balance').val('');
        $('.signature').html('');
        $('.photo').html('');
        $('#member_id').val('');
        $('#payment_mode').val('');
        $('#payment_mode').trigger( "change" );
        $('#available_balance').val('');
		
        /*
		$.ajax({
            type: "POST",
            url: "{!! route('admin.withdraw.getdaybookdata') !!}",
            dataType: 'JSON',
            data: {'branchId':branchId,'date':date},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#available_balance').val(response.microAmount);
            }
        });
		*/
		$.ajax({
			type: "POST", 
			url: "{!! route('admin.branchBankBalanceAmount') !!}",
			dataType: 'JSON',
			data: {'branch_id':branchId,'entrydate':date,'company_id':companyId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
			success: function(response) { 
				// alert(response.balance);
				$('#available_balance').val(response.balance);  
			}
		});
    });
    $('#company_id').on('change',function(){
        var companyId = $( "#company_id option:selected" ).val();

        $('#payment_mode').val('');
        $('#bank').val('');
        $('#bank_account_number').val('');
        $('.bank').hide();
        $('.company-bank').hide();
        $('.'+companyId+'-company-bank').show();
    })
    $(document).on('change','#payment_mode ',function(){
        var branchId = $('option:selected', '#branch').val();
        var companyId = $( "#company_id option:selected" ).val();
        var date = $('#date').val();
        var paymentMode = $('option:selected', '#payment_mode').val();
        if(paymentMode == '0' && paymentMode != ''){
            if(branchId == ''){
                $('#date').val('');
                swal("Warning!", "Please Select branch!", "warning");
                return false;
            }
            $('#available_balance').val('');
/*
            $.ajax({
                type: "POST",
                url: "{!! route('admin.withdraw.getdaybookdata') !!}",
                dataType: 'JSON',
                data: {'branchId':branchId,'date':date},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#available_balance').val(response.microAmount);
                }
            });
*/
			$.ajax({
				type: "POST", 
				url: "{!! route('admin.branchBankBalanceAmount') !!}",
				dataType: 'JSON',
				data: {'branch_id':branchId,'entrydate':date,'company_id':companyId},
				success: function(response) { 
					// alert(response.balance);
					$('#available_balance').val(response.balance);  
				}
			});
        }
        if(paymentMode == 1 && paymentMode != ''){
            var bank_id=$('#bank').val();
            var account_id=$('#bank_account_number').val();
            $('.'+companyId+'-company-bank').show();
            $('#bank_balance').val('0.00');
            $.ajax({
              type: "POST",
              url: "{!! route('admin.bankChkbalance') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id,'bank_id':bank_id,'entrydate':date},
              success: function(response) {
               // alert(response.balance);
                $('#bank_balance').val(response.balance);
              }
          });
        }
    });
    $(document).on('change','#date ',function(){
        var branchId = $('option:selected', '#branch').val();
        var date = $('#date').val();
        var companyId = $( "#company_id option:selected" ).val();
        var paymentMode = $('option:selected', '#payment_mode').val();
        console.log(paymentMode);
        if(paymentMode == '0' && paymentMode != ''){
            if(branchId == ''){
                $('#date').val('');
                swal("Warning!", "Please Select branch!", "warning");
                return false;
            }
            $('#available_balance').val('');
/*
            $.ajax({
                type: "POST",
                url: "{!! route('admin.withdraw.getdaybookdata') !!}",
                dataType: 'JSON',
                data: {'branchId':branchId,'date':date},
                headers: { 
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#available_balance').val(response.microAmount);
                }
            });
*/
			$.ajax({ 
				type: "POST", 
				url: "{!! route('admin.branchBankBalanceAmount') !!}",
				dataType: 'JSON',
				data: {'branch_id':branchId,'entrydate':date,'company_id':companyId},
				success: function(response) { 
					// alert(response.balance);
					$('#available_balance').val(response.balance);  
				}
			});
        }
        if(paymentMode == 1 && paymentMode != ''){
            var bank_id=$('#bank').val();
            var account_id=$('#bank_account_number').val();
            $('#bank_balance').val('0.00');
            $.ajax({
              type: "POST",
              url: "{!! route('admin.bankChkbalance') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id,'bank_id':bank_id,'entrydate':date},
              success: function(response) {
               // alert(response.balance);
                $('#bank_balance').val(response.balance);
              }
          });
        }
    });
    // Get registered member by id
    $(document).on('change','#ssb_account_number',function(){
        var branchId = $( "#branch option:selected" ).val();
        var companyId = $( "#company_id option:selected" ).val();
        var date = $( "#date" ).val();
        $('#mbank').val('');
        $('#mbankac').val('');
        $('#mbankifsc').val('');
        var ssb_account_number = $(this).val();
        if(companyId == ''){
            swal('Warning','Please select Company First','warning');
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{!! route('admin.withdraw.accountdetails') !!}",
            dataType: 'JSON',
            data: {'account_number':ssb_account_number,'branchId':branchId,'date':date,'companyId':companyId},
            success: function(response) {
                // if(response.msg == 0){
                //     swal('Accout status','Your account is inactive..!!','warning');
                //     $('#ssb_account_number').val('');
                // } else {
                    //if(response.todayTransaction == 0){
                        if(response.resCount > 0){                       
                            let signature = response.signature;
                            let photo = response.photo;
                            console.log(response.ssbAccountDetails[0].ssbcustomer_data_get);
                            $('#member_id').val(response.ssbAccountDetails[0].ssbcustomer_data_get.member_id);
                            $('#account_holder_name').val(response.ssbAccountDetails[0].ssbcustomer_data_get.first_name+' '+response.ssbAccountDetails[0].ssbcustomer_data_get.last_name);
                            $('#account_balance').val(response.transactionBydate?response.transactionBydate.opening_balance:0 );                        
                            if(response.mb > 0)
                            {
                                $('#mbank').val(response.memberBank.bank_name);
                                $('#mbankac').val(response.memberBank.account_no);
                                $('#mbankifsc').val(response.memberBank.ifsc_code);
                            }
                            if(response.ssbAccountDetails[0].ssbcustomer_data_get.signature){
                                // $('.signature').html(' <img src="{{url('/')}}/asset/profile/member_signature/'+signature+'" alt="signature" width="180" height="100">');
                                $('.signature').html(' <img src="'+signature+'" alt="signature" width="180" height="100">');
                            }else{
                                $('.signature').html(' <img src="{{url('/')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');
                            }
                            if(response.ssbAccountDetails[0].ssbcustomer_data_get.photo){
                                // $('.photo').html(' <img src="{{url('/')}}/asset/profile/member_avatar/'+photo+'" alt="signature" width="180" height="100">');
                                $('.photo').html(' <img src="'+photo+'" alt="signature" width="180" height="100">');
                            }else{
                                $('.photo').html(' <img src="{{url('/')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');
                            }
                        }else{
                            $('#ssb_account_number').val('');
                            $('#account_number').val('');
                            $('#company_id').val('');
                            $('#branch_id').val('');
                            $('#account_holder_name').val('');
                            $('#account_balance').val('');
                            $('.signature').html('');
                            $('.photo').html('');
                            swal("Warning!", "Account Number does not exists!", "warning");
                        }
                    /*}else{
                        $('#ssb_account_number').val('');
                        $('#account_number').val('');
                        $('#account_holder_name').val('');
                        $('#account_balance').val('');
                        $('.signature').html('');
                        $('.photo').html('');
                        swal("Warning!", "You have permission only 1 withdrwal in a day!", "warning");
                    }*/
                // }
            }
        });
    });
    $(document).on('change','#payment_mode',function(){
        var paymentMode = $('option:selected', this).val();
        $('#bank_account_number').val('');
        $('#bank_balance').val('');
        $('#bank').val('');
        $('#mbank').val('');
        $('#mbankac').val('');
        $('#mbankifsc').val('');
        if(paymentMode == 0 && paymentMode != ''){
            $('.cash').show();
            $('.bank').hide();
            $('.cheque').hide();
            $('.online').hide();
            $('#bank_mode').val('');
            $('#cheque_number').val('');
            $('#utr_no').val('');
            $('#rtgs_neft_charge').val('');
        }else if(paymentMode == 1 && paymentMode != ''){
            $('.cash').hide();
            $('.bank').show();
            $('.cheque').hide();
            $('.online').hide();
            $('#bank_mode').val('');
            $('#cheque_number').val('');
            $('#utr_no').val('');
            $('#rtgs_neft_charge').val('');
        }else{
            $('.cash').hide();
            $('.bank').hide();
            $('.cheque').hide();
            $('.online').hide();
            $('#bank_mode').val('');
            $('#cheque_number').val('');
            $('#utr_no').val('');
            $('#rtgs_neft_charge').val('');
        }
    });
    $(document).on('change','#bank_mode',function(){
        var bank_mode = $('option:selected', this).val();
        if(bank_mode == 0 && bank_mode != ''){
            $('.cheque').show();
            $('.online').hide();
            $('#utr_no').val('');
            $('#rtgs_neft_charge').val('');
        }else if(bank_mode == 1 && bank_mode != ''){
            $('.cheque').hide();
            $('.online').show();
            $('#cheque_number').val('');
            $('#rtgs_neft_charge').val('');
        }else{
            $('.cheque').hide();
            $('.online').hide();
            $('#cheque_number').val('');
            $('#rtgs_neft_charge').val('');
        }
    });
    // $(document).on('change','#amount',function(){
    //     var cValue = $(this).val();
    //     var floatInteger = cValue/100;
    //     if(floatInteger % 1 != 0){
    //         $(this).val('');
    //         swal("Warning!", "Amount should be multiply 100!", "warning");
    //     }
    // });
// var today = new Date();
//     $('#date').datepicker({
//         format: "dd/mm/yyyy",
//         orientation: "top",
//         autoclose: true,
//         endDate: "today",
//         startDate: '01/04/2021',
//     });
   $("#date").hover(function(){
            const EndDate = $('#create_application_date').val();
            console.log(EndDate);
            $('#date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: EndDate,
        });
    })
    $(document).on('change','#bank',function(){
        var bank_account_number = $('option:selected', this).attr('data-val');
        $('#bank_account_number').val(bank_account_number);
    });
    $('#bank').on('change',function(){
     $('#bank_balance').val('0.00');
        var bank_id=$(this).val();
        $.ajax({
            url: "{!! route('admin.bank_account_list') !!}",
            type:"POST",
            dataType: 'JSON',
            data: {'bank_id':bank_id},
          success: function(response) {
            $('#bank_account_number').find('option').remove();
            $('#bank_account_number').append('<option value="">Select account number</option>');
             $.each(response.account, function (index, value) {
                    $("#bank_account_number").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
                });
          }
       })
     })
   $('#bank_account_number').on('change',function(){
    $('#cheque_number').val('');
    var bank_id=$('#bank').val();
    var companyId = $( "#company_id option:selected" ).val();
    var account_id=$('#bank_account_number').val();
    var entrydate=$('#date').val();
    $('#bank_balance').val('0.00');
      $.ajax({
              type: "POST",
              url: "{!! route('admin.bankChkbalance') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id,'bank_id':bank_id,'entrydate':entrydate,'companyId':companyId},
              success: function(response) {
               // alert(response.balance);
                $('#bank_balance').val(response.balance);
              }
          });
            $.ajax({
              type: "POST",
              url: "{!! route('admin.bank_cheque_list') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id},
              success: function(response) {
                $('#cheque_number').find('option').remove();
                $('#cheque_number').append('<option value="">Select cheque number</option>');
                 $.each(response.chequeListAcc, function (index, value) {
                        $("#cheque_number").append("<option value='"+value.id+"'>"+value.cheque_no+"</option>");
                    });
                 }
          });
    })
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});
</script>