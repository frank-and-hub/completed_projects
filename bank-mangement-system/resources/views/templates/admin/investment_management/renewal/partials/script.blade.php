<script type="text/javascript">

$(document).ready(function() {

    fnBrowserDetect();
//Code added by amar 02 Feb 2022

$("#submitform").click(function (e) {
    var i = 1;
    var newObj = [];
    //$('#submitform').attr('disabled', 'disabled');
    newObj.push("0");
    $(".tr_class").each(function(){
        var ThisId = $(this).attr('id');
        var valll = ThisId.split('_');
        newObj.push(valll[1]);
    });
   // console.log(newObj);
/* $(".loaders").show();
  	$(".loaders").text("0%xx");
				$("#cover").fadeIn(100); */
                 // do whatever you want to do
/*
				$(".loaders").text("0%xx");
				$("#cover").fadeIn(100);    */

/* $( ".tr_class" ).each(function( index ) {
  console.log( index + ": " + $(".account_number_"+index ).val() );
});
return false;*/
// console.log($("#renewal-form").serialize());return false;

var form_data = $("#renewal-form").serialize();
var payment_mode= $('#payment_mode').val();

var renewplan_id= $('input[name="renewplan_id"]').val();
    var form = $( "#renewal-form" );
        form.validate();
    if ( form.valid() == true) {
        if(payment_mode != '')
        {
            if(renewplan_id != 2){
                $("#cover").fadeIn(100);
                $( ".loader" ).show();
                $(".loaders").text("0%");
                $(".spiners").css("display","block");
            }
        }

    }


var renew_investment_plan_id= $('input[name="renew_investment_plan_id"]').val();
var member_id= $('input[name="member_id"]').val();
var deposite_by_name= $('input[name="deposite_by_name"]').val();
var scheme_name= $('input[name="scheme_name"]').val();
var branch_id= $('input[name="branch_id"]').val();
var collector_account_blance= $('input[name="collector_account_blance"]').val();
var collector_code= $('input[name="collector_code"]').val();
var collector_name= $('input[name="collector_name"]').val();
var daily_no_of_accounts= $('input[name="daily_no_of_accounts"]').val();


var rdfrd_no_of_accounts= $('input[name="rdfrd_no_of_accounts"]').val();
var rdfrd_associate_code= $('input[name="rdfrd_associate_code"]').val();
var rdfrd_associate_name= $('input[name="rdfrd_associate_name"]').val();
var associate_code= $('input[name="associate_code"]').val();
var associate_name= $('input[name="associate_name"]').val();
var saving_account_balance= $('input[name="saving_account_balance"]').val();


var total_amount= $('input[name="total_amount"]').val();
var renewal_date= $('input[name="renewal_date"]').val();
var available_balance= $('input[name="available_balance"]').val();
var cheque_id= $('#cheque_id').val();
var cheque_number= $('input[name="cheque-number"]').val();
var bank_name= $('input[name="bank-name"]').val();
var branch_name= $('input[name="branch-name"]').val();
var cheque_date= $('input[name="cheque-date"]').val();
var cheque_amount= $('input[name="cheque-amount"]').val();
var deposit_bank_name= $('input[name="deposit_bank_name"]').val();
var deposit_bank_account= $('input[name="deposit_bank_account"]').val();


	if(payment_mode==''){
  swal("Error!", "Please select payment mode!", "error");
  $('#payment_mode').focus();
                return false;
}


/*	e.preventDefault();
	 $.ajaxSetup({
    	headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
	});*/

	var no_of_acc=0;
	success(1);
function success(i) {
if(daily_no_of_accounts>0){
	no_of_acc	=	daily_no_of_accounts;
}
else{
 no_of_acc	=	rdfrd_no_of_accounts;
}
  if (i > no_of_acc) return;

			var account_number= $('input[name="account_number['+newObj[i]+']"]').val();
			var investment_plan= $('input[name="investment_plan['+newObj[i]+']"]').val();
			var investment_id= $('input[name="investment_id['+newObj[i]+']"]').val();
			var investment_member_id= $('input[name="investment_member_id['+newObj[i]+']"]').val();
			var investment_tenure= $('input[name="investment_tenure['+newObj[i]+']"]').val();
			var investment_member_phone_no= $('input[name="investment_member_phone_no['+newObj[i]+']"]').val();
			var name= $('input[name="name['+newObj[i]+']"]').val();
			var amount= $('input[name="amount['+newObj[i]+']"]').val();
			var deo_amount= $('input[name="deo_amount['+newObj[i]+']"]').val();
			var hidden_due_amount= $('input[name="hidden_due_amount['+i+']"]').val();
			var hidden_deposite_amount= $('input[name="hidden_deposite_amount['+newObj[i]+']"]').val();
			var acoount_associate_name= $('input[name="acoount_associate_name['+newObj[i]+']"]').val();
			if(account_number==''){
				Swal.fire('Account number is required');return false;
			}
			if(amount==''){
				Swal.fire('Amount is required');return false;
			}

	e.preventDefault();
	 $.ajaxSetup({
    	headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
	});

	$.ajax({
		url: "{{ route('admin.renew.storeajax') }}",
		  async:true, // set async false to wait for previous response
		  //crossDomain: true,
		method: "POST",
		'_token': $('input[name="_token"]').val(),



	 /* data: $("#renewal-form").serialize(),*/

	     data: {
		 'i':i,
		 'no_of_acc':no_of_acc,
		'renewplan_id': renewplan_id, 'renew_investment_plan_id': renew_investment_plan_id, 'member_id': member_id,
		'deposite_by_name': deposite_by_name, 'scheme_name': scheme_name, 'branch_id': branch_id,
		'collector_account_blance': collector_account_blance, 'collector_code': collector_code, 'collector_name': collector_name,
		'daily_no_of_accounts': daily_no_of_accounts,

		'account_number[1]': account_number, 'investment_plan[1]': investment_plan,
		'investment_id[1]': investment_id, 'investment_member_id[1]': investment_member_id,
		'investment_tenure[1]': investment_tenure, 'investment_member_phone_no[1]': investment_member_phone_no,
		'name[1]': name, 'amount[1]': amount,
		'deo_amount[1]': deo_amount, 'hidden_due_amount[1]': hidden_due_amount,
		'hidden_deposite_amount[1]': hidden_deposite_amount, 'acoount_associate_name[1]': acoount_associate_name,

		'rdfrd_no_of_accounts': rdfrd_no_of_accounts,'rdfrd_associate_code': rdfrd_associate_code, 'rdfrd_associate_name': rdfrd_associate_name,
		'associate_code': associate_code, 'associate_name': associate_name, 'saving_account_balance': saving_account_balance,
		'payment_mode': payment_mode, 'total_amount': total_amount, 'renewal_date': renewal_date,

		'available_balance': available_balance, 'cheque_id': cheque_id, 'cheque-number': cheque_number,
		'bank-name': bank_name, 'branch-name': branch_name, 'cheque-date': cheque_date,
		'cheque-amount': cheque_amount, 'deposit_bank_name': deposit_bank_name, 'deposit_bank_account': deposit_bank_account,
		 'form_data' : form_data,
		 },
		dataType: "json", //response,xhr
		success: function(response ) {

			if(i==no_of_acc){

			 $("#row_"+newObj[i]).addClass('greenRow');
		 		 $(".loaders").text(response.percentage+"%");
				 $("#cover").fadeOut(100);
				 $( ".loader" ).hide();
				  $(".spiners").css("display","none");
            	//console.log('success: '+data);
				swal("Success!", "Renewal form submitted successfully", "success");
                //console.log(response.redirect_url);return false;
			    if(response.redirect_url != ''){
                    window.location.href = response.redirect_url;
                    //return true;
				}
			 }else{
                 console.log(response);
			  var pNumber =response.percentage;
			   if (Number.isInteger(pNumber)){
			 	 $(".loaders").text(response.percentage+"%");
			  	$("#row_" + newObj[i]).addClass("greenRow");
			  }else{
			 	 swal("Error!", "Renewal form not submitted", "alert");
				 window.location.reload();
			  }

                setTimeout(function() {
                    success(i+1);
                }, 100);

			 }
        },
        error: function(xmlhttprequest, textstatus, message) {

            if (xmlhttprequest.readyState == 4) {
            // HTTP error (can be checked by XMLHttpRequest.status and XMLHttpRequest.statusText)
                swal({
                           title: "error!",
                           text: "Something went wrong",
                           type: "error"
                         },
                       function(){
                           location.reload();
                       }
                    );
            }
            else if (xmlhttprequest.readyState == 0) {
                // Network error (i.e. connection refused, access denied due to CORS, etc.)
                alert('Internet connection refused please try again');
            }
            else {
                swal({
                           title: "error!",
                           text: "Something went wrong",
                           type: "error"
                         },
                       function(){
                           location.reload();
                       }
                    );
            }


           if (textstatus==="timeout") {
                swal({
                           title: "error!",
                           text: "Something went wrong",
                           type: "error"
                         },
                       function(){
                           location.reload();
                       }
                    );
            }else{
                swal({
                           title: "error!",
                           text: "Something went wrong",
                           type: "error"
                         },
                       function(){
                           location.reload();
                       }
                    );
            }


        }
	});
}
//	}//end of for loop
});





    // Investment Form validations
    var today = new Date();
    $('#renewal_date').datepicker({

        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true,
        endDate: "today",
        maxDate: today,
        startDate:'01/04/2021',


    });

    $.validator.addMethod("chequeAmount", function(value, element,p) {
        if($('#payment_mode').val()==1)
        {
            if(parseFloat($('#total_amount').val()).toFixed(2) == $('#cheque-amount').val())
                  {
                    $.validator.messages.chequeAmount = "";
                    result = true;
                  }else{
                    $.validator.messages.chequeAmount = "Cheque amount is not equal to renew total amount. Please select another cheque";
                    result = false;
                  }
        }
        else
        {
            result = true;
        }


        return result;
    }, "");

    $('#renewal-form').validate({ // initialize the plugin
        rules: {
            'collector_code' : 'required',
            'associate_code' : 'required',
            'payment_mode' : 'required',
            'rdfrd_associate_code' : 'required',
            'account_number[0]':'required',
            'amount[0]':'required',
            'daily_no_of_accounts' : {required: true, number: true, max:10, min:1},
            'rdfrd_no_of_accounts' : {required: true, number: true, max:7, min:1},
            'total_amount': {required: true, number: true, min:1,chequeAmount:true},
            'cheque_id': {
            required: function(element) {
              if ($( "#payment_mode" ).val()==1) {
                return true;
              } else {
                return false;
              }
            },
          },
        },
        submitHandler: function() {
            var renewPlan = $( "#renewplan option:selected" ).val();
            var branch = $( ".branch option:selected" ).val();
            if(renewPlan == '' || branch == ''){
                $('.submit-renew-form').prop('disabled', false);
                swal("Error!", "Please select a plan and branch first!", "error");
                return false;
            }else{
                $( ".loader" ).show();
                $('.submit-renew-form').prop('disabled', true);
                return true;
            }
        }
    });

    $(document).on('change','#renewplan',function(){
        var plan = $(this).val();
        $("#renewal-form")[0].reset();
        $('.cash').hide();
        $('#cheque-detail').hide();
        $('#cheque-detail-show').hide();
        $('#cheque-number').val('');
                 $('#bank-name').val('');
                 $('#branch-name').val('');
                 $('#cheque-date').val('');
                 $('#cheque-amount').val('');
                 $('.daily-no-of-accounts').attr('readonly', false);
                 $('.rdfrd-no-of-accounts').attr('readonly', false);
        $("#renewplan option").each(function()
        {
            var sectionClass = $(this).attr('data-val');
            $('.'+sectionClass+'').hide();
        });

        var attr = $('option:selected', this).attr('data-val');
        $('.'+attr+'').show();

        if(plan != ''){
            $('.comman-section').show();
        }else{
            $('.comman-section').hide();
        }

        if(plan == 0){
            $('#renew_investment_plan_id').val(7);
            $('#scheme_name').val('Daily Deposite');
        }else if(plan == 1){
            $('#renew_investment_plan_id').val(10);
            $('#scheme_name').val('Recurring Deposit');
        }else if(plan == 2){
            $('#renew_investment_plan_id').val(1);
            $('#scheme_name').val('Saving Account');
        }

        $('.daily-renew-input-number').html('');
        $('.rdfrd-renew-input-number').html('');
        $('.renew-account-table').hide();
        $('#renewplan_id').val(plan);
    });

    $(document).on('change','.renewinvestmentplan',function(){
        var plan = $(this).val();
        var text = $('option:selected', this).attr('data-val');
        $('#renew_investment_plan_id').val(plan);
        $('#scheme_name').val(text);
    });

    // Get investment detail by id
    $(document).on('change','.account-number',function(){
        var accountNumber = $(this).val();
        var attVal = $(this).attr('data-val');
        var renewPlan = $( "#renewplan option:selected" ).val();
        var renewPlanId = $( "#renew_investment_plan_id" ).val();

        var renewalDate = $( "#renewal_date" ).val();

        if(renewalDate == ''){
            $(this).val('');
            swal("Warning!", 'Please select a date first!!', "warning");
            return false;
        }

        var rDate = renewalDate.split('/');
        var rd1 = rDate[2];
        var rd2 = rDate[1];
        var rd3 = rDate[0];

        var convertRenewalDate = rd1+'-'+rd2+'-'+rd3;

        var accountArray = [];
        if(renewPlan==0){
            var fieldNumber = $('.daily-no-of-accounts').val();
            for (var x = 1; x <= fieldNumber; x++) {
                var accountNumbers = $('.account-number-'+x).val();
                if(attVal != x){
                    accountArray.push(accountNumbers);
                }
            }
            if((accountArray.indexOf(""+accountNumber+"") > -1)){
                swal("Warning!", 'Account Number should be different!!', "warning");
                $('.account-number-'+attVal+'').val('');
                return false;
            }
        }
        $.ajax({
            type: "POST",
            url: "{!! route('admin.investment.renewplan') !!}",
            dataType: 'JSON',
            data: {'account_number':accountNumber,'renewPlan':renewPlan,'renewPlanId':renewPlanId,'renewalDate':renewalDate},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function(response) {
                console.log(response);
                if(response.resCount > 0 && response.minutes > 5)
                {
                    var investDate = response.investment[0].created_at.split(' ');
                    var ind3 = investDate[0];

                    var convertInvestDate = ind3;
                   if(response.msg == true)
                    {
                        swal("Warning!", "Renewal not allowed. Maturity is due.!", "warning");
                        $('.account-number-'+attVal+'').val('');
                    }
                    /** as per discusseed with sachin sir on 29-12-2023 this code is removed from admin panel only */
                    if(convertRenewalDate < convertInvestDate){
                        // $('.account-number-'+attVal+'').val('');
                        // swal("Warning!", 'Account not open on renewal date!!', "warning");
                        // return false;
                    }
                    if(renewPlan==2 && !response.investment[0].ssb){
                        swal("Warning!", "Account Number does not exists!!", "warning");
                        $('.account-number-'+attVal+'').val('');
                        return false;
                    }
                    if(response.type == "demand-advice" )
                    {
                        swal("Warning!", "Demand Request Already Created!", "warning");
                        $('.account-number-'+attVal+'').val('');
                        return false;
                    }

                    $.each( response.investment, function( key, value ) {
                        if(value.member){
                            // value.member.first_name ? $('.name-'+attVal+'').val(value.member.first_name + ' ' + value.member.last_name??'') : $('.name-'+attVal+'').val("");

                            value.member.first_name ? $('.name-' + attVal + '').val((value.member.first_name) + ' ' + (value.member.last_name??'')) : $('.name-' + attVal + '').val("");
                        }
                        response.investment[0].deposite_amount ? $('.deno_amount-' + attVal + '').val(response.investment[0].deposite_amount) : $('.deno_amount-' + attVal + '').val(0);
                        response.dueAmount ? $('.deo-amount-'+attVal+'').val(response.dueAmount) : $('.deo-amount-'+attVal+'').val(0);
                        response.dueAmount ? $('.hidden-due-amount-'+attVal+'').val(response.dueAmount) : $('.hidden-due-amount-'+attVal+'').val(0);
                        value.deposite_amount ? $('.hidden-deposite-amount-'+attVal+'').val(value.deposite_amount) : $('.hidden-deposite-amount-'+attVal+'').val(0);
                        if(value.associate_member){
                            value.associate_member.associate_no ? $('.associate-code-'+attVal+'').val(value.associate_member.associate_no) : $('.associate-code-'+attVal+'').val("");
                            value.associate_member.first_name ? $('.associate-name-'+attVal+'').val(value.associate_member.first_name + ' ' + value.associate_member.last_name??'') : $('.associate-name-'+attVal+'').val("");
                        }

                        if(value.tenure){
                            value.tenure ? $('.investment-tenure-'+attVal+'').val(value.tenure) : $('.investment-tenure-'+attVal+'').val("");
                        }

                        if(value.ssb){
                            value.ssb ? $('.saving-account-balance-'+attVal+'').val(value.ssb.balance) : $('.saving-account-balance-'+attVal+'').val("");
                        }

                        $('.renew-amount-'+attVal+'').val('');
                        $('.rdfrd-renew-amount-'+attVal+'').val('');
                        $('.investment-id-'+attVal+'').val(value.id);
                        $('.investment-plan-'+attVal+'').val(value.plan_id);
                        $('.investment-member-id-'+attVal+'').val(value.member_id);
                        $('.investment_member_phone_no-'+attVal+'').val(value.member.mobile_no);

                        var sum = 0;
                        if(renewPlan==0 ){
                            $(".renew-amount").each(function(){
                                sum += +$(this).val();
                            });
                        }
                        if(renewPlan==1 ){
                            $(".rdfrd-renew-amount").each(function(){
                                sum += +$(this).val();
                            });
                        }

                        if(renewPlan==2 ){
                            $(".saving-amount").each(function(){
                                sum += +$(this).val();
                            });
                            $('.saving-total-amount').val(sum);
                        }
                        $('#total_amount').val(sum);
                    });
                }
                else if(response.type == "demand-advice" )
                {
                   swal("Warning!", "Demand request already created!", "warning");
                }
                else if(response.resCount > 0 && response.minutes <= 5)
                {
                    $('.name-'+attVal+'').val("");
                    $('.deo-amount-'+attVal+'').val("");
                    $('.hidden-due-amount-'+attVal+'').val("");
                    $('.associate-code-'+attVal+'').val("");
                    $('.associate-name-'+attVal+'').val('');
                    $('.renew-amount-'+attVal+'').val('');
                    $('.rdfrd-renew-amount-'+attVal+'').val('');
                    $(".saving-amount").val('');
                    $('.account-number-'+attVal+'').val('');
                    $('.investment-id-'+attVal+'').val('');
                    $('.investment-plan-'+attVal+'').val('');
                    $('.investment-member-id-'+attVal+'').val('');
                    var sum = 0;
                    if(renewPlan==0 ){
                        $(".renew-amount").each(function(){
                            sum += +$(this).val();
                        });
                    }
                    if(renewPlan==1 ){
                        $(".rdfrd-renew-amount").each(function(){
                            sum += +$(this).val();
                        });
                    }
                    if(renewPlan==2 ){
                        $(".saving-amount").each(function(){
                            sum += +$(this).val();
                        });
                        $('.saving-total-amount').val(sum);
                    }
                    $('#total_amount').val(sum);
                    $('.saving-total-amount-'+attVal+'').val(sum);
                    swal("Warning!", "Last Renewal time should be greater than 5 minute!!", "warning");
                }
               else if(response.resCount == 0 &&  response.msg == false )
                {
                       $('.name-'+attVal+'').val("");
                    $('.deo-amount-'+attVal+'').val("");
                    $('.hidden-due-amount-'+attVal+'').val("");
                    $('.associate-code-'+attVal+'').val("");
                    $('.associate-name-'+attVal+'').val('');
                    $('.renew-amount-'+attVal+'').val('');
                    $('.rdfrd-renew-amount-'+attVal+'').val('');
                    $(".saving-amount").val('');
                    $('.account-number-'+attVal+'').val('');
                    $('.investment-id-'+attVal+'').val('');
                    $('.investment-plan-'+attVal+'').val('');
                    $('.investment-member-id-'+attVal+'').val('');
                    swal("Warning!", "Record Not Found!", "warning");
                }
                else
                {
                    $('.name-'+attVal+'').val("");
                    $('.deo-amount-'+attVal+'').val("");
                    $('.hidden-due-amount-'+attVal+'').val("");
                    $('.associate-code-'+attVal+'').val("");
                    $('.associate-name-'+attVal+'').val('');
                    $('.renew-amount-'+attVal+'').val('');
                    $('.rdfrd-renew-amount-'+attVal+'').val('');
                    $(".saving-amount").val('');
                    $('.account-number-'+attVal+'').val('');
                    $('.investment-id-'+attVal+'').val('');
                    $('.investment-plan-'+attVal+'').val('');
                    $('.investment-member-id-'+attVal+'').val('');
                    var sum = 0;
                    if(renewPlan==0 ){
                        $(".renew-amount").each(function(){
                            sum += +$(this).val();
                        });
                    }
                    if(renewPlan==1 ){
                        $(".rdfrd-renew-amount").each(function(){
                            sum += +$(this).val();
                        });
                    }
                    if(renewPlan==2 ){
                        $(".saving-amount").each(function(){
                            sum += +$(this).val();
                        });
                        $('.saving-total-amount').val(sum);
                    }
                    $('#total_amount').val(sum);
                    $('.saving-total-amount-'+attVal+'').val(sum);
                    swal("Warning!", "You can not deposit, because account has been matured.", "warning");
                }

            }

        });

    });

    $(document).on('change','#associate_code,#rdfrd_associate_code',function(){
        var code = $(this).val();
        var attVal = $(this).attr('data-val');
        var renewPlan = $( "#renewplan option:selected" ).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.investment.getcollectorassociate') !!}",
            dataType: 'JSON',
            data: {'code':code},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function(response) {
                if(response.msg_type == 'success')
                {
                    if(renewPlan==0){
                        /*response.collectorDetails.carders_name ? $('.'+attVal+'').val(response.collectorDetails.carders_name) : $('.'+attVal+'').val("");*/
                        response.collectorDetails.first_name ? $('.'+attVal+'').val(response.collectorDetails.first_name+' '+response.collectorDetails.last_name??'') : $('.'+attVal+'').val("");
                        $('#member_id').val(response.collectorDetails.id);
                        $('#deposite_by_name').val(response.collectorDetails.first_name+' '+response.collectorDetails.last_name??'');
                    }else if(renewPlan==1 || renewPlan==2){
                        response.collectorDetails.first_name ? $('.'+attVal+'').val(response.collectorDetails.first_name + ' ' + response.collectorDetails.last_name??'') : $('.'+attVal+'').val("");
                        $('#member_id').val(response.collectorDetails.id);
                        $('#deposite_by_name').val(response.collectorDetails.first_name+' '+response.collectorDetails.last_name??'');
                    }
                     $('#collector_account_blance').val(response.collectorDetails.saving_account[0].balance);
                }
                else
                {
                    $('#collector_code').val('');
                    $('#associate_code').val('');
                    $('#rdfrd_associate_code').val('');
                    $('.'+attVal+'').val('');
                    $('#member_id').val('');
                    $('#deposite_by_name').val('');
                    $('#collector_account_blance').val('');
                    swal("Warning!", "Associate Code does not exists!!", "warning");
                }
            }

        });

    });

    $(document).on('change','#collector_code',function(){
        var code = $(this).val();
        var attVal = $(this).attr('data-val');
        var renewPlan = $( "#renewplan option:selected" ).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.investment.getcollectorassociate') !!}",
            dataType: 'JSON',
            data: {'code':code},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function(response) {
                if(response.msg_type == 'success')
                {
                    if(renewPlan==0){
                        /*response.collectorDetails.carders_name ? $('.'+attVal+'').val(response.collectorDetails.carders_name) : $('.'+attVal+'').val("");*/
                        response.collectorDetails.first_name ? $('.'+attVal+'').val(response.collectorDetails.first_name+' '+response.collectorDetails.last_name??'') : $('.'+attVal+'').val("");
                        $('#member_id').val(response.collectorDetails.id);
                        $('#deposite_by_name').val(response.collectorDetails.first_name+' '+response.collectorDetails.last_name??'');
                    }else if(renewPlan==1 || renewPlan==2){
                        response.collectorDetails.first_name ? $('.'+attVal+'').val(response.collectorDetails.first_name + ' ' + response.collectorDetails.last_name??'') : $('.'+attVal+'').val("");
                        $('#member_id').val(response.collectorDetails.id);
                        $('#deposite_by_name').val(response.collectorDetails.first_name+' '+response.collectorDetails.last_name??'');
                    }
                    $('#collector_account_blance').val(response.collectorDetails.saving_account[0].balance);
                }
                else
                {
                    $('#collector_code').val('');
                    $('#associate_code').val('');
                    $('#rdfrd_associate_code').val('');
                    $('.'+attVal+'').val('');
                    $('#member_id').val('');
                    $('#deposite_by_name').val('');
                    $('#collector_account_blance').val('');
                    swal("Warning!", "Associate Code does not exists!!", "warning");
                }
            }

        });

    });

    $('.daily-renew-input-number').on('change','.renew-amount',function(){
        var dInput = $(this).attr('data-val');
        var mId = $('.account-number-'+dInput+'').val();
        var depositeAmount = $('.hidden-deposite-amount-'+dInput+'').val();
        //var dueAmount = $('.deo-amount-'+dInput+'').val();
        var dueAmount = $('.hidden-due-amount-'+dInput+'').val();
        var cValue = $(this).val();
        var floatInteger = cValue/depositeAmount;

        if(!$.isNumeric(cValue) || cValue <= 0){
            $(this).val('');
            swal("Warning!", "Value must be numeric!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }else if(mId == '' ){
            $(this).val('');
            swal("Warning!", "First enter account number!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }/*else if(parseInt(cValue) > parseInt(dueAmount)){
            $(this).val('');
            swal("Warning!", "Amount should be less than due amount!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }*/else{
            if(dueAmount < 0){
                var checkAmount = parseInt(dueAmount)+parseInt(cValue);
                if(checkAmount > 0){
                    var updateAmount = -Math.abs(checkAmount);
                }else if(checkAmount <= 0){
                    var updateAmount = parseInt(dueAmount)+parseInt(cValue);
                }
            }else{
                var updateAmount = parseInt(dueAmount)-parseInt(cValue);
            }
            $('.deo-amount-'+dInput+'').val(parseInt(updateAmount));
        }

        /*if(parseInt(cValue) < parseInt(depositeAmount)){
            $(this).val('');
            swal("Warning!", "Amount should be greater than deno amount!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }*/

        /*if(floatInteger % 1 != 0){
            $(this).val('');
            swal("Warning!", "Amount should be multiply deno amount!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }*/

        var sum = 0;
        $(".renew-amount").each(function(){
            sum += +$(this).val();
        });
        $('#total_amount').val(sum);
    });

    $('.rdfrd-renew-input-number').on('change','.rdfrd-renew-amount',function(){
        var dInput = $(this).attr('data-val');
        var mId = $('.account-number-'+dInput+'').val();
        var iPlan = $('.investment-plan-'+dInput+'').val();
        var cValue = $(this).val();
        var depositeAmount = $('.hidden-deposite-amount-'+dInput+'').val();
        //var dueAmount = $('.deo-amount-'+dInput+'').val();
         var dueAmount = $('.hidden-due-amount-'+dInput+'').val();
        var floatInteger = cValue/depositeAmount;
        if(!$.isNumeric(cValue) || cValue <= 0 ){
            $(this).val('');
            swal("Warning!", "Value must be numeric!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }else if(mId == '' ){
            $(this).val('');
            swal("Warning!", "First enter account number!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }/*else if(parseInt(cValue) > parseInt(dueAmount)){
            $(this).val('');
            swal("Warning!", "Amount should be less than due amount!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }*/else{
            if(dueAmount < 0){
                var checkAmount = parseInt(dueAmount)+parseInt(cValue);
                if(checkAmount > 0){
                    var updateAmount = -Math.abs(checkAmount);
                }else if(checkAmount <= 0){
                    var updateAmount = parseInt(dueAmount)+parseInt(cValue);
                }
            }else{
                var updateAmount = parseInt(dueAmount)-parseInt(cValue);
            }
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }

        if(floatInteger % 1 != 0 && iPlan != 2){
            $(this).val('');
            swal("Warning!", "Amount should be multiply deno amount!", "warning");
            var updateAmount = $('.hidden-due-amount-'+dInput+'').val();
            $('.deo-amount-'+dInput+'').val(updateAmount);
        }

        var sum = 0;
        $(".rdfrd-renew-amount").each(function(){
            sum += +$(this).val();
        });
        $('#total_amount').val(sum);
    });

    $(document).on('change','.saving-amount',function(){
        var dInput = $(this).attr('data-val');
        var mId = $('.account-number-'+dInput+'').val();
        var cValue = $(this).val();
        if(!$.isNumeric(cValue) || cValue <= 0){
            $(this).val('');
            swal("Warning!", "Value must be numeric!", "warning");
        }else if(mId == '' ){
            $(this).val('');
            swal("Warning!", "First enter account number!", "warning");
        }

        var sum = 0;
        $(".saving-amount").each(function(){
            sum += +$(this).val();
        });
        $('#total_amount').val(sum);
        $('.saving-total-amount').val(sum);
    });

    $(document).on('keyup','.no-of-accounts',function(){
        var numberFields = $(this).val();
        var appendClass = $(this).attr('data-val');
        var tableClass = $(this).attr('data-table-class');
        var renewPlan = $( "#renewplan option:selected" ).val();
        if(renewPlan==0){
            fieldLimit = 10;
        }else if(renewPlan==1){
            fieldLimit = 7;
        }
        if(numberFields > 0 && numberFields <= fieldLimit){
            $('.'+tableClass+'').show();
        }else{
            $('.'+tableClass+'').hide();
        }
        $('.'+appendClass+'').html('');
        var renewPlan = $( "#renewplan option:selected" ).val();
        var x = 1;
        if(numberFields <= fieldLimit)
        {
            for (var x = 1; x <= numberFields; x++) {
                if(renewPlan==0){
                   /*var list_fieldHTML = '<tr><td><input type="text" data-val="'+x+'" name="account_number['+x+']" class="form-control account-number ' +
                       'account-number-'+x+'" autocomplete="off"><input type="hidden" name="investment_plan['+x+']" class="investment-plan-'+x+'"><input type="hidden" name="investment_id['+x+']" class="investment-id-'+x+'"><input type="hidden" ' +
                       'name="investment_member_id['+x+']" class="investment-member-id-'+x+'"><input type="hidden" name="investment_tenure['+x+']" ' +
                       'class="investment-tenure-'+x+'"><input type="hidden" name="investment_member_phone_no['+x+']" ' + 'class="investment_member_phone_no-'+x+'"></td><td><input type="text" data-val="'+x+'" name="name['+x+']" class="form-control name-'+x+'" readonly="" tabIndex="-1"></td><td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+x+'" name="amount['+x+']" class="form-control rupee-txt renew-amount renew-amount-'+x+'"></div></td><td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+x+'" name="deo_amount['+x+']" class="form-control rupee-txt deo-amount-'+x+'" readonly="" tabIndex="-1"><input type="hidden" name="hidden_due_amount['+x+']" class="hidden-due-amount-'+x+'"><input type="hidden" name="hidden_deposite_amount['+x+']" class="hidden-deposite-amount-'+x+'"></div></td><td><input type="text" data-val="'+x+'" name="acoount_associate_code['+x+']" class="form-control associate-code-'+x+'" readonly="" tabIndex="-1"></td><td><input type="text" data-val="'+x+'" name="acoount_associate_name['+x+']" class="form-control associate-name-'+x+'" readonly="" tabIndex="-1"><a href="javascript:void(0);" class="remove-daily-renew-button" tabIndex="-1">Remove</a></td></tr>'; //New input field html
                    */
                    var list_fieldHTML = '<tr class="tr_class" id="row_'+x+'"><td><input type="text" data-val="'+x+'" name="account_number['+x+']" class="form-control account-number ' +
                       'account-number-'+x+'" autocomplete="off" style="width:230px;" required><input type="hidden" name="investment_plan['+x+']" class="investment-plan-'+x+'"><input type="hidden" name="investment_id['+x+']" class="investment-id-'+x+'"><input type="hidden" ' +
                       'name="investment_member_id['+x+']" class="investment-member-id-'+x+'"><input type="hidden" name="investment_tenure['+x+']" ' +
                       'class="investment-tenure-'+x+'"><input type="hidden" name="investment_member_phone_no['+x+']" ' + 'class="investment_member_phone_no-'+x+'"></td><td><input type="text" data-val="'+x+'" name="name['+x+']" class="form-control name-'+x+'" readonly="" tabIndex="-1" style="width:230px;"></td><td><input type="text" data-val="'+x+'" name="deno_amount['+x+']" class="form-control deno_amount-'+x+'" readonly="" tabIndex="-1" style="width:230px;"></td><td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+x+'" name="amount['+x+']" class="form-control rupee-txt renew-amount renew-amount-'+x+'"></div></td><td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+x+'" name="deo_amount['+x+']" class="form-control rupee-txt deo-amount-'+x+'" readonly="" tabIndex="-1"><input type="hidden" name="hidden_due_amount['+x+']" class="hidden-due-amount-'+x+'"><input type="hidden" name="hidden_deposite_amount['+x+']" class="hidden-deposite-amount-'+x+'"></div></td><td><input type="text" data-val="'+x+'" name="acoount_associate_name['+x+']" class="form-control associate-name-'+x+'" readonly="" tabIndex="-1"><a href="javascript:void(0);" class="remove-daily-renew-button" tabIndex="-1">Remove</a></td></tr>'; //New input field html


                    $('.'+appendClass+'').append(list_fieldHTML); //Add field html
                }else if(renewPlan==1){
                    var list_fieldHTML = '<tr class="tr_class" id="row_'+x+'"><td><input type="text" data-val="'+x+'" name="account_number['+x+']" class="form-control account-number ' +
                        'account-number-'+x+'" autocomplete="off" style="width:230px;" required><input type="hidden" name="investment_plan['+x+']" class="investment-plan-'+x+'"><input type="hidden" name="investment_id['+x+']" class="investment-id-'+x+'"><input type="hidden" name="investment_member_id['+x+']" class="investment-member-id-'+x+'"><input type="hidden" name="investment_tenure['+x+']" class="investment-tenure-'+x+'"><input type="hidden" name="investment_member_phone_no['+x+']" class="investment_member_phone_no-'+x+'"></td><td><input type="text" data-val="'+x+'" name="name['+x+']" class="form-control name-'+x+'" readonly="" tabIndex="-1" style="width:230px;"></td><td><input type="text" data-val="'+x+'" name="deno_amount['+x+']" class="form-control deno_amount-'+x+'" readonly="" tabIndex="-1" style="width:230px;"></td><td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+x+'" name="amount['+x+']" class="form-control rupee-txt rdfrd-renew-amount rdfrd-renew-amount-'+x+'"></div></td><td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+x+'" name="deo_amount['+x+']" class="form-control rupee-txt deo-amount-'+x+'" readonly="" tabIndex="-1"><input type="hidden" name="hidden_due_amount['+x+']" class="hidden-due-amount-'+x+'"><input type="hidden" name="hidden_deposite_amount['+x+']" class="hidden-deposite-amount-'+x+'"></div><a href="javascript:void(0);" class="remove-rdfrd-renew-button" tabIndex="-1">Remove</a></td></tr>'; //New input field html
                    $('.'+appendClass+'').append(list_fieldHTML); //Add field html
                }
            }
        }

    });



    $('.daily-renew-input-number').on('click', '.remove-daily-renew-button', function() {
        $(this).closest('tr').remove(); //Remove field html
        var nAccounts = $('.daily-no-of-accounts').val();
        $('.daily-no-of-accounts').val(nAccounts-1);
        if((nAccounts-1)==0){
            $('.daily-renew-investment-table').hide();
        }
        //x--;

        var sum = 0;
        $(".renew-amount").each(function(){
            sum += +$(this).val();
        });
        $('#total_amount').val(sum);
    });

    $('.rdfrd-renew-input-number').on('click', '.remove-rdfrd-renew-button', function() {
        $(this).closest('tr').remove(); //Remove field html
        var nAccounts = $('.rdfrd-no-of-accounts').val();
        $('.rdfrd-no-of-accounts').val(nAccounts-1);
        if((nAccounts-1)==0){
            $('.rdfrd-renew-investment-table').hide();
        }
        var sum = 0;
        $(".rdfrd-renew-amount").each(function(){
            sum += +$(this).val();
        });
        $('#total_amount').val(sum);
       // x--;
    });

    $( "#renewal-form" ).submit(function( event ) {
        var aviBalance = $('#collector_account_blance').val();
        var depositeBalance = $('#total_amount').val();
        var paymentVal = $( "#payment_mode option:selected" ).val();
        if(paymentVal == '1')
        {
            if(parseFloat($('#total_amount').val()).toFixed(2)!=$('#cheque-amount').val())
            {
               // swal("Error!", "Cheque amount is not equal to renew total amount. Please select another cheque", "error");
                //event.preventDefault();
            }
        }
        if(paymentVal == '4'){
            if ( parseInt(depositeBalance) > parseInt(aviBalance) ) {
                $('#balance-error').show();
                $('#balance-error').html('Sufficient amount not available in your account.');
                event.preventDefault();
            }else{
                return true;
            }
        }else{
            $('#balance-error').html('');
            return true;
        }
    });

    $(document).on('change','#cheque_id',function(){
        var cheque_id=$('#cheque_id').val();

          $.ajax({
              type: "POST",
              url: "{!! route('admin.approve_cheque_details') !!}",
              dataType: 'JSON',
              data: {'cheque_id':cheque_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                //alert(response.id);
                 $('#cheque-number').val(response.cheque_no);
                 $('#bank-name').val(response.bank_name);
                 $('#branch-name').val(response.branch_name);
                 $('#cheque-date').val(response.cheque_create_date);
                 $('#cheque-amount').val(parseFloat(response.amount).toFixed(2));
                 $('#deposit_bank_name').val(response.deposit_bank_name);
                 $('#deposit_bank_account').val(response.deposite_bank_acc);
                 $('#cheque-detail-show').show();

              }
          });
    });


    // Select payment option
    $(document).on('change','#payment_mode',function(){
        var paymentMode = $('option:selected', this).attr('data-val');
        $('#cheque-detail').hide();
        $('#cheque-detail-show').hide();
        $('#cheque-number').val('');
        $('#bank-name').val('');
        $('#branch-name').val('');
        $('#cheque-date').val('');
        $('#cheque-amount').val('');
        $('.daily-no-of-accounts').attr('readonly', false);
        $('.rdfrd-no-of-accounts').attr('readonly', false);

        $('.cash').hide();
        if(paymentMode=='cheque-mode')
        {
            $('.cash').hide();
            $('#total_amount').val('')
            $('#cheque-detail').show();
            if($('#renewplan').val()==0)
            {
                $('.daily-no-of-accounts').val(1);
                $('.daily-no-of-accounts').attr('readonly', true);
            }
            if($('#renewplan').val()==1)
            {
                $('.rdfrd-no-of-accounts').val(1);
                $('.rdfrd-no-of-accounts').attr('readonly', true);
            }
            if($('#renewplan').val()!=2)
            {
                $( ".no-of-accounts" ).keyup();
            }

              $.ajax({
                  type: "POST",
                  url: "{!! route('admin.approve_recived_cheque_lists') !!}",
                  dataType: 'JSON',
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  success: function(response) {
                    $('#cheque_id').find('option').remove();
                    $('#cheque_id').append('<option value="">Select cheque number</option>');
                     $.each(response.cheque, function (index, value) {
                        $("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+" ( "+parseFloat(value.amount).toFixed(2)+")</option>");
                    });
                  }
              });
        }else if(paymentMode=='ssb'){
            $('.cash').show();
        }
    });

    $(document).on('change','#renewal_date',function(){
        var renewPlan = $( "#renewplan option:selected" ).val();
        if(renewPlan != 2){
            $('.no-of-accounts').trigger('keyup');
        }
    });

    $(document).on('change','#renewal_date,.associate-code',function(){

        var associateCode = $(this).val();

        var member_id = $('#member_id').val();
        var date = $('#renewal_date').val();

        if(associateCode == ''){
            swal("Warning!", 'Please fill collector code first!', "warning");
            return false;
        }else{

            $.ajax({
                type: "POST",
                url: "{!! route('admin.renewal.ssbamount') !!}",
                dataType: 'JSON',
                data: {'member_id':member_id,'date':date},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#available_balance').val(response.savingBalance);
                }
            });
        }
    });

    $(document).on('change', '.branch', function() {
        var branchId = $( ".branch option:selected" ).val();
        $('#branch_id').val(branchId);
    });

    // Show loading image
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });


    // Hide loading image

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });



});

function fnBrowserDetect(){

    let userAgent = navigator.userAgent;

    let browserName;
    if(userAgent.match(/chrome|chromium|crios/i)){
         browserName = "chrome";
       }else if(userAgent.match(/firefox|fxios/i)){
         browserName = "firefox";
       }  else if(userAgent.match(/safari/i)){
         browserName = "safari";
       }else if(userAgent.match(/opr\//i)){
         browserName = "opera";
       } else if(userAgent.match(/edg/i)){
         browserName = "edge";
       }else{
         browserName="No browser detection";
       }
     if(browserName != 'chrome')
     {
        $('.comp').css("display","none");
        document.querySelector(".h1").innerText= browserName +" browser is not compatible for this module";
     }


      }


function printDiv(elem) {

    $("#"+elem).print({
                    //Use Global styles
                    globalStyles : true,
                    //Add link with attrbute media=print
                    mediaPrint : true,
                    //Custom stylesheet
                    stylesheet : "{{url('/')}}/asset/print.css",
                    //Print in a hidden iframe
                    iframe : false,
                    //Don't print this
                    noPrintSelector : ".avoid-this",
                    //Add this at top
                  //  prepend : "Hello World!!!<br/>",
                    //Add this on bottom
                   // append : "<span><br/>Buh Bye!</span>",
                   header: null,               // prefix to html
                  footer: null,
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() {    })
                });


}
</script>
