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
        $.validator.messages.decimal = "Please enter valid numeric number.";
        result = false;
      }

    return result;
  }, "");

$.validator.addMethod("zero1", function(value, element,p) {
      if(value>=0)
      {
        $.validator.messages.zero1 = "";
        result = true;
      }else{
        $.validator.messages.zero1 = "Amount must be greater than 0.";
        result = false;
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
        $.validator.messages.chkbank = "Bank available balance must be grather than or equal to   amount";
        result = false;
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

            'branch' : 'required',
            'date' : 'required',
            'ssb_account_number' : {required: true, number: true},
            'amount' : {required: true, decimal: true,lessThanEquals: "#account_balance",max: 19900,zero1:true,},
            'account_balance' : {min: 500},
            'bank' : 'required',
            'bank_mode' : 'required',
            'cheque_number' : 'required',
            'utr_no' : {required: true, number: true},
            'rtgs_neft_charge' : {required: true, decimal: true},
            'mbank' : 'required',
            'mbankac' : {required: true, number: true,minlength: 8,maxlength:20},
            'mbankifsc' : 'required',
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

             if(balance < 500){

               swal("Warning!", "Minimum Balance Should be 500 !", "warning");

                return false;

            }



            var paymentModeVal = $( "#payment_mode option:selected").val();

            var microDaybookAmount = $( "#available_balance").val();

            if(paymentModeVal == 0){

                if(parseInt(amount) > parseInt(microDaybookAmount)){

                    swal("Warning!", "Amount should be less than or equal to micro daybook amount!", "warning");

                    return false;

                }

            }
            if(paymentModeVal == 1){
                if(parseFloat(amount) > parseFloat($('#bank_balance').val())){
                    swal("Warning!", "Amount should be less than or equal to bank amount!", "warning");
                    return false;
                }

            }
            $('.submit').prop('disabled',true);
            return true;

        }

    });



    $(document).on('change','#branch',function(){
        var branchCode = $('option:selected', this).attr('data-val');
        $('#branch_code').val(branchCode);
        $('#branch_code').val(branchCode);
        $('#ssb_account_number').val('');
        $('#account_number').val('');
        $('#account_holder_name').val('');
        $('#account_balance').val('');
        $('.signature').html('');
        $('.photo').html('');
        $('#member_id').val('');
        $('#payment_mode').val('');
        $( "#payment_mode" ).trigger( "change" );
		/*
		$.ajax({
			type: "POST", 
			url: "{!! route('admin.branchBankBalanceAmount') !!}",
			dataType: 'JSON',
			data: {'branch_id':branchId,'entrydate':date},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) { 
				// alert(response.balance);
				$('#available_balance').val(response.balance);  
			}
		});
		*/
    });



    // Get registered member by id

    $(document).on('change','#ssb_account_number',function(){

        var ssb_account_number = $(this).val();

        var branchId = $('#branch_id').val();
        $('#mbank').val('');
        $('#mbankac').val('');
        $('#mbankifsc').val('');


        $.ajax({
            type: "POST",
            url: "{!! route('branch.withdraw.accountdetails') !!}",
            dataType: 'JSON',
            data: {'account_number':ssb_account_number,'branchId':branchId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: function(response) {
              var photo = response.photo;
              var signature = response.signature;
                if(response.todayTransaction == 0){

                    if(response.resCount > 0){
                        $('#member_id').val(response.ssbAccountDetails[0]['ssb_member'].member_id);

                        $('#account_holder_name').val(response.ssbAccountDetails[0]['ssb_member'].first_name+' '+response.ssbAccountDetails[0]['ssb_member'].last_name);

                        $('#account_balance').val(response.transactionBydate.opening_balance);
                        if(response.mb>0)
                        {
                            $('#mbank').val(response.memberBank.bank_name);
                            $('#mbankac').val(response.memberBank.account_no);
                            $('#mbankifsc').val(response.memberBank.ifsc_code);
                        }


                        if(response.ssbAccountDetails[0]['ssb_member'].signature){

                            // $('.signature').html(' <img src="{{url('/')}}/asset/profile/member_signature/'+response.ssbAccountDetails[0]['ssb_member'].signature+'" alt="signature" width="180" height="100">');
                            $('.signature').html(' <img src="'+signature+'" alt="signature" width="180" height="100">');

                        }else{

                            $('.signature').html(' <img src="{{url('/')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');

                        }



                        if(response.ssbAccountDetails[0]['ssb_member'].photo){

                            // $('.photo').html(' <img src="{{url('/')}}/asset/profile/member_avatar/'+response.ssbAccountDetails[0]['ssb_member'].photo+'" alt="photo" width="180" height="100">');
                            $('.photo').html(' <img src="'+photo+'" alt="photo" width="180" height="100">');

                        }else{

                            $('.photo').html(' <img src="{{url('/')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');

                        }

                    }
                    else if(response.resCount  == 'no')
                    {

                      $('#ssb_account_number').val('');

                      $('#account_number').val('');

                      $('#account_holder_name').val('');

                      $('#account_balance').val('');

                      $('.signature').html('');

                      $('.photo').html('');

                      swal("Warning!", "You can not withdrawal, because debit card already issued on this account.!", "warning");
                    }
                    else{

                        $('#ssb_account_number').val('');

                        $('#account_number').val('');

                        $('#account_holder_name').val('');

                        $('#account_balance').val('');

                        $('.signature').html('');

                        $('.photo').html('');

                        swal("Warning!", "Account Number does not exists!", "warning");

                    }

                }else{
                  $('#member_id').val('');
                    $('#ssb_account_number').val('');

                    $('#account_number').val('');

                    $('#account_holder_name').val('');

                    $('#account_balance').val('');

                    $('.signature').html('');

                    $('.photo').html('');

                    swal("Warning!", "You don't have permission more than one withdrawal in a day!", "warning");

                }

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



    $(document).on('change','#amount',function(){

        var cValue = $(this).val();

        var floatInteger = cValue/100;

        if(floatInteger % 1 != 0){

            $(this).val('');

            swal("Warning!", "Amount should be multiply 100!", "warning");

        }

    });



    $('#date').datepicker({

        format: "dd/mm/yyyy",

        orientation: "top",

        autoclose: true

    });



    $('#bank').on('change',function(){
     $('#bank_balance').val('0.00');
        var bank_id=$(this).val();
        $.ajax({
            url: "{!! route('branch.bank_account_list') !!}",
            type:"POST",
            dataType: 'JSON',
            data: {'bank_id':bank_id},
            headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
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
    var account_id=$('#bank_account_number').val();
    var entrydate=$('#created_at').val();
    $('#bank_balance').val('0.00');
      $.ajax({
              type: "POST",
              url: "{!! route('branch.bankChkbalanceBranch') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id,'bank_id':bank_id,'entrydate':entrydate},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
               // alert(response.balance);
                $('#bank_balance').val(response.balance);
              }
          });

            $.ajax({
              type: "POST",
              url: "{!! route('branch.bank_cheque_list') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
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
