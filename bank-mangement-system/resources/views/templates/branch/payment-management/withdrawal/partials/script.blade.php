<script type="text/javascript">
  $(document).ready(function () {
    $.validator.addMethod("lessThanEquals",
      function (value, element, param) {
        var $otherElement = $(param);
        return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
        return value > target.val();
      }, "Amount should be less than OR equals current available amount.");
    $.validator.addMethod("decimal", function (value, element, p) {
      if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
        $.validator.messages.decimal = "";
        result = true;
      } else {
        $.validator.messages.decimal = "Please enter valid numeric number.";
        result = false;
      }
      return result;
    }, "");
    $.validator.addMethod("zero1", function (value, element, p) {
      if (value >= 0) {
        $.validator.messages.zero1 = "";
        result = true;
      } else {
        $.validator.messages.zero1 = "Amount must be greater than 0.";
        result = false;
      }
      return result;
    }, "")
    $.validator.addMethod("chkbank", function (value, element, p) {
      if ($("#payment_mode").val() == 1) {
        if (parseFloat($('#bank_balance').val()) >= parseFloat($('#amount').val())) {
          $.validator.messages.chkbank = "";
          result = true;
        } else {
          $.validator.messages.chkbank = "Bank available balance must be grather than or equal to   amount";
          result = false;
        }
      }
      else {
        $.validator.messages.chkbank = "";
        result = true;
      }
      return result;
    }, "");
    $.validator.addMethod("chkBranch", function (value, element, p) {
      if ($("#payment_mode").val() == 0) {
        if (parseFloat($('#available_balance').val()) >= parseFloat($('#amount').val())) {
          $.validator.messages.chkBranch = "";
          result = true;
        } else {
          $.validator.messages.chkBranch = "Branch total balance must be grather than or equal to  amount";
          result = false;
        }
      }
      else {
        $.validator.messages.chkBranch = "";
        result = true;
      }
      return result;
    }, "");
    jQuery.validator.addMethod("greaterThanZero", function (value, element) {
      return this.optional(element) || (parseFloat(value) > 0);
    }, "Amount must be greater than Zero");
    $.validator.addMethod("neft", function (value, element, p) {
      if ($("#bank_mode").val() == 1 && $("#payment_mode").val() == 2) {
        a = parseFloat($('#rtgs_neft_charge').val()) + parseFloat($('#amount').val());
        if (parseFloat($('#bank_balance').val()) >= parseFloat(a)) {
          $.validator.messages.neft = "2";
          result = true;
        } else {
          $.validator.messages.neft = "Bank available balance must be grather than or equal to  sum of amount or NEFT charge";
          result = false;
        }
      }
      else {
        $.validator.messages.neft = "2";
        result = true;
      }
      return result;
    }, "");
    $('#withdrawal-ssb').validate({ // initialize the plugin
      rules: {
        'branch': 'required',
        'company_id': 'required',
        'date': 'required',
        'ssb_account_number': { required: true, number: true },
        'amount': { required: true, decimal: true, lessThanEquals: "#account_balance", max: 19900, zero1: true, greaterThanZero: true },
        'account_balance': {
          min: () => ($('input[name="company_id"]').val() === '1') ? 500 : false
        },
        'bank': 'required',
        'bank_mode': 'required',
        'cheque_number': 'required',
        'utr_no': { required: true, number: true },
        'rtgs_neft_charge': { required: true, decimal: true },
        'mbank': 'required',
        'mbankac': { required: true, number: true, minlength: 8, maxlength: 20 },
        'mbankifsc': 'required',
        'bank_balance': { required: true, decimal: true, zero1: true, chkbank: true, neft: true },
        'payment_mode': { required: true },
        'bank_account_number': { required: true },
      },
      submitHandler: function () {
        var accountBalance = $("#account_balance").val();
        // updated by Sachin Sir on 06-06-2023
        var amount = $("#amount").val();
        var companyId = $("#company_id").val();
        var balance = accountBalance - amount;
        if (balance < 500 && companyId == 1) {
          swal("Warning!", "Minimum Balance Should be 500 !", "warning");
          return false;
        }
        // updated by Sachin Sir on 06-06-2023
        var paymentModeVal = $("#payment_mode option:selected").val();
        var microDaybookAmount = $("#available_balance").val();
        if (paymentModeVal == 0) {
          if (parseInt(amount) > parseInt(microDaybookAmount)) {
            swal("Warning!", "Amount should be less than or equal to micro daybook amount!", "warning");
            return false;
          }
        }
        if (paymentModeVal == 1) {
          if (parseFloat(amount) > parseFloat($('#bank_balance').val())) {
            swal("Warning!", "Amount should be less than or equal to bank amount!", "warning");
            return false;
          }
        }
        $('.submit').prop('disabled', true);
        return true;
      }
    });
    $(document).on('change', '#branch', function () {
      var branchCode = $('option:selected', this).attr('data-val');
      var companyId = $("#company_id option:selected").val();
      $('#branch_code').val(branchCode);
      $('#ssb_account_number').val('');
      $('#account_number').val('');
      $('#account_holder_name').val('');
      $('#account_balance').val('');
      $('.signature').html('');
      $('.photo').html('');
      $('#member_id').val('');
      $('#payment_mode').val('');
      $("#payment_mode").trigger("change");
      $.ajax({
        type: "POST",
        url: "{!! route('branch.branchBankBalanceAmount') !!}",
        dataType: 'JSON',
        data: { 'branch_id': branchId, 'entrydate': date, 'company_id': copmpanyId },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          alert(response.balance);
          $('#available_balance').val(response.balance);
        }
      });
    });
    // Get registered member by id
    $(document).on('change', '#ssb_account_number,#company_id', function () {
      var ssb_account_number = $('#ssb_account_number').val();
      var branchId = $('#branch_id').val();
      var companyId = $('#company_id').val();
      $('#mbank').val('');
      $('#mbankac').val('');
      $('#mbankifsc').val('');
      if (companyId == '') {
        $('#ssb_account_number').val('');
        swal("Warning!", "please Select Company First !", "warning");
        return false;
      }
      if (ssb_account_number == '') {
        return false;
      }
      $.ajax({
        type: "POST",
        url: "{!! route('branch.withdraw.accountdetails') !!}",
        dataType: 'JSON',
        data: { 'account_number': ssb_account_number, 'branchId': branchId, 'companyId': companyId },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          let signature = response.signature;
          let photo = response.photo;
          if(response.msg == 'inactive') {
              swal('Accout status','Your account is inactive..!!','warning');
              $('#ssb_account_number').val('');
          } else if(response.msg == 'empty') {
              swal('Warning','Data Not Found!','warning');
              $('#ssb_account_number').val('');
          } else if(response.msg == 'withdrawal again') {
              swal("Warning!", "Please upload Photo and Signature of Customer and withdrawal again !", "warning");
              $('#ssb_account_number').val('');
          } else {
            /*
            if (response.currectBranch) {
            */
            if (response.todayTransaction == 0) {
              if (response.resCount > 0) {
                $('#mobile_no').empty();
                let signature = response.signature;
                let photo = response.photo;
                if (response.msg == 0) {
                  swal('Accout status', 'Your account is inactive !', 'warning');
                  $('#ssb_account_number').val('');
                }
                
                let text = response.ssbAccountDetails[0].ssb_member_customer.member.mobile_no;
                let subMobile = text.substring(6, 10);
                let result = text.substring(0, 6).replace(new RegExp("[0-9]", "g"), "X");
                $('#mobile_no').append('+91 ' + result + subMobile);
                $('#account_holder_name').val(response.ssbAccountDetails[0].ssb_member_customer.member.first_name + ' ' + response.ssbAccountDetails[0].ssb_member_customer.member.last_name);
                if (response.transactionBydate) {
                  $('#account_balance').val(response.transactionBydate.opening_balance);
                } else {
                  $('#account_balance').val(0.00);
                  swal("Warning!", "Customer dos't have Account Balance !", "warning");
                }
                $('#member_id').val(response.ssbAccountDetails[0].ssb_member_customer.member.member_id);
                if (response.mb > 0) {
                  $('#mbank').val(response.memberBank.bank_name);
                  $('#mbankac').val(response.memberBank.account_no);
                  $('#mbankifsc').val(response.memberBank.ifsc_code);
                }
                if (response.ssbAccountDetails[0].ssb_member_customer.member.signature) {
                  $('.signature').html(' <img src="' + signature + '" alt="signature" width="180" height="100">');
                } else {
                  $('.signature').html(' <img src="{{url(' / ')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');
                }
                if (response.ssbAccountDetails[0].ssb_member_customer.member.photo) {
                  $('.photo').html(' <img src="' + photo + '" alt="signature" width="180" height="100">');
                } else {
                  $('.photo').html(' <img src="{{url(' / ')}}/asset/images/no-image.png" alt="signature" width="180" height="100">');
                }
              } else if (response.resCount == 'no') {
                $('#ssb_account_number').val('');
                $('#account_number').val('');
                $('#account_holder_name').val('');
                $('#account_balance').val('');
                $('.signature').html('');
                $('.photo').html('');
                $('#member_id').val('');
                swal("Warning!", "You can not withdrawal, because debit card already issued on this account.!", "warning");
              } else {
                $('#ssb_account_number').val('');
                $('#account_number').val('');
                $('#member_id').val('');
                $('#account_holder_name').val('');
                $('#account_balance').val('');
                $('.signature').html('');
                $('.photo').html('');
                swal("Warning!", "Account Number does not exists!", "warning");
              }
            } else {
              $('#member_id').val('');
              $('#ssb_account_number').val('');
              $('#account_number').val('');
              $('#account_holder_name').val('');
              $('#account_balance').val('');
              $('.signature').html('');
              $('.photo').html('');
              swal("Warning!", "You don't have permission more than one withdrawal in a day!", "warning");
            }
            /*
              }else{
                $('#member_id').val('');
                $('#ssb_account_number').val('');
                $('#account_number').val('');
                $('#account_holder_name').val('');
                $('#account_balance').val('');
                $('.signature').html('');
                $('.photo').html('');
                swal("Warning!", "Withdrawal of this account can be done from " + response.branchname + " branch", "warning");
              }
            */
          }
        }
      });
    });
    $(document).on('change', '#payment_mode', function () {
      var paymentMode = $('option:selected', this).val();
      var companyId = $('#company_id').val();
      var branchId = $('#branch_id').val();
      var date = $('input[name="date"]').val();
      $('#bank_account_number').val('');
      $('#bank_balance').val('');
      $('#bank').val('');
      $('#mbank').val('');
      $('#mbankac').val('');
      $('#mbankifsc').val('');
      if (paymentMode == 0 && paymentMode != '') {
        $('.cash').show();
        $('.bank').hide();
        $('.cheque').hide();
        $('.online').hide();
        $('#bank_mode').val('');
        $('#cheque_number').val('');
        $('#utr_no').val('');
        $('#rtgs_neft_charge').val('');
      } else if (paymentMode == 1 && paymentMode != '') {
        $('.cash').hide();
        $('.bank').show();
        $('.cheque').hide();
        $('.online').hide();
        $('#bank_mode').val('');
        $('#cheque_number').val('');
        $('#utr_no').val('');
        $('#rtgs_neft_charge').val('');
      } else {
        $('.cash').hide();
        $('.bank').hide();
        $('.cheque').hide();
        $('.online').hide();
        $('#bank_mode').val('');
        $('#cheque_number').val('');
        $('#utr_no').val('');
        $('#rtgs_neft_charge').val('');
      }
      $.ajax({
        type: "POST",
        url: "{!! route('branch.getbranchbankbalanceamount') !!}",
        dataType: 'JSON',
        data: { 'branch_id': branchId, 'entrydate': date, 'company_id': companyId },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          $('#available_balance').val(response.balance);
        }
      })
    });
    $(document).on('change', '#bank_mode', function () {
      var bank_mode = $('option:selected', this).val();
      if (bank_mode == 0 && bank_mode != '') {
        $('.cheque').show();
        $('.online').hide();
        $('#utr_no').val('');
        $('#rtgs_neft_charge').val('');
      } else if (bank_mode == 1 && bank_mode != '') {
        $('.cheque').hide();
        $('.online').show();
        $('#cheque_number').val('');
        $('#rtgs_neft_charge').val('');
      } else {
        $('.cheque').hide();
        $('.online').hide();
        $('#cheque_number').val('');
        $('#rtgs_neft_charge').val('');
      }
    });
    $(document).on('change', '#amount', function () {
      var cValue = $(this).val();
      var company_id = $('#company_id').val();
      var floatInteger = cValue / 100;
      // updated by Sachin Sir on 06-06-2023 
      if ((floatInteger % 1 != 0) && (company_id == 1)) {
        $(this).val('');
        swal("Warning!", "Amount should be multiply 100!", "warning");
      }
      // updated by Sachin Sir on 06-06-2023 
    });
    $('#date').datepicker({
      format: "dd/mm/yyyy",
      orientation: "top",
      autoclose: true
    });
    $('#bank').on('change', function () {
      $('#bank_balance').val('0.00');
      var bank_id = $(this).val();
      $.ajax({
        url: "{!! route('branch.bank_account_list') !!}",
        type: "POST",
        dataType: 'JSON',
        data: { 'bank_id': bank_id },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          $('#bank_account_number').find('option').remove();
          $('#bank_account_number').append('<option value="">Select account number</option>');
          $.each(response.account, function (index, value) {
            $("#bank_account_number").append("<option value='" + value.id + "'>" + value.account_no + "</option>");
          });
        }
      })
    })
    $('#bank_account_number').on('change', function () {
      $('#cheque_number').val('');
      var bank_id = $('#bank').val();
      var account_id = $('#bank_account_number').val();
      var entrydate = $('#created_at').val();
      $('#bank_balance').val('0.00');
      $.ajax({
        type: "POST",
        url: "{!! route('branch.bankChkbalanceBranch') !!}",
        dataType: 'JSON',
        data: { 'account_id': account_id, 'bank_id': bank_id, 'entrydate': entrydate },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          // alert(response.balance);
          $('#bank_balance').val(response.balance);
        }
      });
      $.ajax({
        type: "POST",
        url: "{!! route('branch.bank_cheque_list') !!}",
        dataType: 'JSON',
        data: { 'account_id': account_id },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          $('#cheque_number').find('option').remove();
          $('#cheque_number').append('<option value="">Select cheque number</option>');
          $.each(response.chequeListAcc, function (index, value) {
            $("#cheque_number").append("<option value='" + value.id + "'>" + value.cheque_no + "</option>");
          });
        }
      });
    })
    $('#otp_form').validate({
      rules: {
        'otp1': {
          required: true,
          number: true,
          minlength: 1,
          maxlength: 1,
        },
        'otp2': {
          required: true,
          number: true,
          minlength: 1,
          maxlength: 1,
        },
        'otp3': {
          required: true,
          number: true,
          minlength: 1,
          maxlength: 1,
        },
        'otp4': {
          required: true,
          number: true,
          minlength: 1,
          maxlength: 1,
        },
      },
      highlight: function (element) {
        $(element).css('border', '1px solid red');
        $(element).removeClass('error');
      },
      unhighlight: function (element) {
        $(element).css('border', '');
      },
      errorPlacement: function (error, element) {
        // Do nothing to remove the error message
      }
    })
    $(".otp_inputs").keyup(function (e) {
      if (this.value.length == this.maxLength) {
        $(this).next('.otp_inputs').focus();
      }
      if (e.which >= 65 && e.which <= 90) {
        console.log("jh");
        e.preventDefault();
      }
    });
    $(document).on('keypress', '.otp_inputs', function (event) {
      var keycode = (event.keycode ? event.keyCode : event.which);
      if (keycode >= 65 && keycode <= 90 || keycode >= 97 && keycode <= 122 || keycode >= 186 && keycode <= 192 || keycode >= 219 && keycode <= 222
        || keycode >= 32 && keycode <= 47 || keycode >= 58 && keycode <= 64 || keycode >= 91 && keycode <= 96 || keycode >= 123 && keycode <= 126) { // Disable all alphabet keys
        event.preventDefault();
      }
    });
    $('#verify').on('click', function (e) {
      e.preventDefault();
      var d = new Date($.now());
      const hrs = d.getHours();
      const minute = d.getMinutes();
      const second = d.getSeconds();
      var currentDate = hrs + ":" + minute + ":" + second;
      const currentTime = currentDate;
      const accountNumber = $('#ssb_account_number').val();
      let otp = '';
      $(".otp :input").each(function (e) {
        otp += $(this).val();
      });
      if ($("#otp_form").valid()) {
        $.ajax({
          type: "POST",
          url: "{!! route('branch.verify.ssb_otp') !!}",
          dataType: 'JSON',
          data: { 'currentTime': currentTime, 'otp': otp, 'accountNumber': accountNumber },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            const type = (response.code == 200) ? 'success' : 'warning';
            const selectedpaymentMode = sessionStorage.getItem('paymentMode');
            //  swal(type,response.msg,type); 
            (response.code == 200) ? (
              $('#withdrawal-ssb input').attr('readonly', 'readonly'),
              $("#payment_mode").children().first().remove(),
              $('.subButton').show(),
              $('.otpbtn').hide(),
              $("#exampleModal").modal('hide'),
              $("#exampleModal").modal('hide'),
              swal(type, response.msg, type)
            ) : (
              $('.subButton').hide(),
              $('.otpbtn').show(),
              $("#exampleModal").modal('show'),
              $(".error-message").empty().removeClass('d-flex justify-content-center mt-2 text-danger'),
              $(".error-message").append(response.msg).addClass('d-flex justify-content-center mt-2 text-danger')
            );
            //  $('#withdrawal-ssb').attr('')     
          }
        })
      }
    })
    $('#otp,.cursor').on('click', function (e) {
      e.preventDefault();
      const accountNumber = $('#ssb_account_number').val();
      var branchId = $('#branch_id').val();
      var companyId = $('#company_id').val();
      $.ajax({
        type: "POST",
        url: "{!! route('branch.withdraw.accountdetails') !!}",
        dataType: 'JSON',
        data: {
          'account_number': accountNumber,
          'branchId': branchId,
          'companyId': companyId
        },
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          let signature = response.signature;
          let photo = response.photo;
          if (response.msg == 0) {
            swal('Accout status', 'Your account is inactive, please contact to admin !', 'warning');
          }
          else {
            otpGenerate();
          }
        }
      });

      function otpGenerate() {
        const amount = $('#amount').val();
        const date = $('#created_at').val();
        let type = 'Warning';
        const paymentModeVal = $("#payment_mode option:selected").val();
        sessionStorage.setItem('paymentMode', paymentModeVal);
        if ($('#withdrawal-ssb').valid()) {
          $.ajax({
            type: "POST",
            url: "{!! route('branch.send.ssb.otp') !!}",
            dataType: 'JSON',
            data: {
              'account_number': accountNumber,
              'amount': amount,
              'date': date
            },
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
              if (response.status == 200) {
                myStopFunction();
                countDown(60, accountNumber);
                $(".error-message").empty(),
                  $(".error-message").removeClass(
                    'd-flex justify-content-center mt-2 text-danger'),
                  (e.target.id == "resend") ?
                    $(".error-message").append('OTP Send Successfully!!')
                      .addClass(
                        'd-flex justify-content-center mt-2 text-success') :
                    '';
                $('#otp_form')[0].reset();
                type = 'success';
                $("#exampleModal").modal('show');
              }
              // swal(type,response.message,type);
              // if(response.status == 200)
              // {
              //   $("#exampleModal").modal('show');
              // }
            }
          })
        }
      }
    })
    /*
    $('#otp,.cursor').on('click',function(e){
      e.preventDefault();
        const accountNumber =$('#ssb_account_number').val();
        const amount =$('#amount').val();
        const date =$('#created_at').val();
        let type = 'Warning';
        const paymentModeVal = $( "#payment_mode option:selected").val();
        sessionStorage.setItem('paymentMode', paymentModeVal);
        if($('#withdrawal-ssb').valid()) {
          $.ajax({
              type: "POST",
              url: "{!! route('branch.send.ssb.otp') !!}",
              dataType: 'JSON',
              data: {'account_number':accountNumber,'amount':amount,'date':date},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
                if(response.status == 200 )
                {
                  myStopFunction();
                  countDown(60,accountNumber);
                  $(".error-message").empty(),
                  $(".error-message").removeClass('d-flex justify-content-center mt-2 text-danger'),
                  (e.target.id == "resend") 
                  ? $(".error-message").append('OTP Send Successfully!!').addClass('d-flex justify-content-center mt-2 text-success') 
                  :'';
                  $('#otp_form')[0].reset();
                  type = 'success';
                  $("#exampleModal").modal('show');
                }
                // swal(type,response.message,type);
                // if(response.status == 200)
                // {
                //   $("#exampleModal").modal('show');
                // }
              }
       }) 
        }
    })
    
      */
    $(document).ajaxStart(function () {
      $(".loader").show();
    });
    $(document).ajaxComplete(function () {
      $(".loader").hide();
    });
  });
  let setCounter;
  function countDown(seconds, accountNumber) {
    function ticker() {
      let counter = document.getElementById('counter');
      seconds--;
      counter.innerHTML = "00:" + (seconds < 10 ? "0" : "") + String(seconds);
      if (seconds > 0) {
        setCounter = setTimeout(ticker, 1000);
      }
      if (seconds == 0) {
        $.ajax({
          type: "POST",
          url: "{!! route('branch.update.ssb.otp') !!}",
          dataType: 'JSON',
          data: { 'account_number': accountNumber },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (res) {
            if (res) {
              return true;
            }
          }
        })
      }
    }
    ticker();
  }
  function myStopFunction() {
    clearTimeout(setCounter);
  }
</script>