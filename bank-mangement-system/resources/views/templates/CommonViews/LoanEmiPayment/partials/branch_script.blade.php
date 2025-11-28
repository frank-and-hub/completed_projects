<script type = "text/javascript" >

    $(document).ready(function() {
        $("#rowR").removeClass("row");
        $("#cardB").show();
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        // Hide Details Section
        $('.member_details').hide();


         // Change form action
         $(document).on('change','#loan_type' ,function(){
            $('.member_details').hide();
            $('#account_number').val('');
            if($(this).val()=="L"){

                $("#loan_emi").attr("action", "{{ Auth::user()->role_id != 3 ? route('admin.loan.depositeloanemi') : route('branch.loan.depositeloanemi') }}");
            }
            else if($(this).val()=="G"){

                $("#loan_emi").attr("action", "{{ Auth::user()->role_id != 3 ? route('admin.grouploan.depositeloanemi') : route('grouploan.depositeloanemi') }}");
            }
            else{

            }
        });

        // Get details according to Account number on change Account number
        $('#account_number').on('change', function() {
            $('#customer_cheque').find('option').remove();
            $('#customer_cheque').append('<option value="">--- Select Cheque ---</option>');
            $('input[name="cheque_total_amount"]').val('');
            $('input[name="customer_branch_name"]').val('');
            $('input[name="cheque-date"]').val('');
            $('input[name="cheque_company_bank"]').val('');
            $('input[name="company_bank_account_number"]').val('');
            $('input[name="customer_bank_name"]').val('');
            var endDate = $('#gdatetime').val();
            var formattedDate = new Date(endDate).toLocaleDateString('en-GB');
            if ($(this).val() == "") {
                $('.member_details').hide();
                $('.cash-mode').hide();
                $('.bank-mode').hide();
                $('.ssb-mode').hide();
                $('.online-mode').hide();
                $('.cheque-mode').hide();
                $('#plan').val('');
                $('#customer_id').val('');
                $('#name').val('');
                $('#sanction_amount').val('');
                $('#emi_amount').val('');
                $('#recoverd_amount').val('');
                // $('#last_recover_date').val(
                $('#closure_amount').val('');
                $('#due_amount').val('');
                $('#last_recoverd_amount').val('');
                $('#ssb_account_number').val('');
                $('#ssbbalance').val('');
                $('#branch').val('');
                $('#associate_id').val('');
                $('#company_id').val('');
                $('#ecs_type').val('');

                return false;
            } else {
                const account_number = $(this).val();
                const type = $('#loan_type').val();
                if (type == '') {
                    swal("Warning!", "Select Type First", "warning");
                    $('#account_number').val('');
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    url: '{{ route("branch.common.LoanAccountDetails") }}',
                    data: {
                        'account_number': account_number,
                        'loan_type': type,
                    },
                    success: function(res) {
                        function handleCommonActions() {
                            $('#account_number').val('');
                            $('.member_details, .cash-mode, .bank-mode, .ssb-mode, .online-mode, .cheque-mode').hide();
                        }

                        switch (res) {
                            case 1:
                                swal("Error!", "Account Number does not exist!", "error");
                                handleCommonActions();
                                return false;

                            case 2:
                                swal("Warning!", "You loan is approved, but not in running!", "warning");
                                handleCommonActions();
                                return false;

                            case 3:
                                swal("Warning!", "Your Loan has been Rejected!", "warning");
                                handleCommonActions();
                                return false;

                            case 4:
                                swal("Warning!", "Your loan account has been cleared!", "warning");
                                handleCommonActions();
                                return false;

                            case 5:
                                swal("Warning!", "Your Loan is Rejected!", "warning");
                                handleCommonActions();
                                return false;

                            case 6:
                                swal("Warning!", "Your Loan is on Hold!", "warning");
                                handleCommonActions();
                                return false;

                            case 7:
                                swal("Warning!", "Your Loan is Approved but on Hold!", "warning");
                                handleCommonActions();
                                return false;

                            default:
                                $('.member_details').show();
                                $('#plan').val(res?.loanRecord?.loans?.name);
                                $('#customer_id').val(res?.loanRecord?.loan_member?.member_id);
                                var lastName =res?.loanRecord?.loan_member?.last_name;
                                if(lastName == null){
                                    lastName = "";
                                }
                                $('#name').val(res?.loanRecord?.loan_member?.first_name + " " + lastName);
                                // $('#name').val(res?.loanRecord?.loan_member?.first_name + " " + res?.loanRecord?.loan_member?.last_name);
                                var ecsType = res?.loanRecord?.ecs_type;
                                $('#ecs_type').val(ecsType === 1 ? 'BANK' : ecsType === 2 ? 'SSB' : ecsType === 0 ? '' : '');


                                $('#sanction_amount').val(res?.loanRecord?.amount);
                                $('#loan_emi_amount').val(res?.loanRecord?.emi_amount);
                                $('#recovered_amount').val(parseFloat(res?.recoverdAmount).toFixed(2));
                                // $('#last_recover_date').val(res?.);
                                const parsedCloserAmount = parseFloat(res?.closerAmount).toFixed(2) ?? 0.00;
                                $('#closure_amount').val(String(parsedCloserAmount));
                                $('#due_amount').val(parseFloat(res?.due_amount).toFixed(2));
                                // $('#last_recovered_amount').val(parseFloat(res?.loanRecord?.due_amount).toFixed(2));
                                $('#last_recovered_amount').val(parseFloat(res?.lastrecoversAmount).toFixed(2));

                                $('#loan_associate_code').val(res?.loan_associate_code).attr('readonly', 'readonly');
								$('#loan_associate_name').val(res?.loan_associate_name).attr('readonly', 'readonly');
								$('#loan_associate_id').val(res?.loan_associate_id);
								$('#associate_member_id').val(res?.loan_associate_id);

                                $('#ssb_account_number').val(res?.ssbAccount);
                                $('#ssb_id').val(res?.ssbId);
                                $('#approve_date').val(res?.loanRecord?.approve_date);
                                $('#ssb_account').val(res?.ssbBalance);
                                $('#branch_name').val(res?.loanRecord?.loan_branch?.name);
                                $('#branch').val(res?.loanRecord?.branch_id);
                                $('#loan_id').val(res?.loanRecord?.id);
                                $('#associate_id').val(res?.associateId);
                                $('#company_id').val(res?.loanRecord?.company_id);
                                $('#application_date').val(formattedDate);
                                $('#loan_associate_code').change();
                                if (res.loginBranch) {
                                    $('#loan_branch option').remove();
                                    const optionBranch = `<option value="${res.loginBranch?.id}">${res.loginBranch?.name}</option>`;
                                    $('#loan_branch').append(optionBranch);
                                }
                                break;
                        }
                    }


                });
            }

        });



        // Change Payment Mode
        $(document).on('change', '#loan_emi_payment_mode', function() {
            var type = $(this).val();
            var branchId = $('#branch').val();
            var companyId = $('#company_id').val();
            if (type == '') {
                $('.cash-mode').hide();
                $('.bank-mode').hide();
                $('.ssb-mode').hide();
                $('.online-mode').hide();
                $('.cheque-detail-show').hide();
                $('.newId').text("Submit");
                $('.newId').removeClass("send_otp");
                $('.newId').attr("id", "submitBtn");
                $('.cheque-mode').hide();
                $('#customer_cheque').val('');



            } else if (type == 2) {
                $('.cash-mode').show();
                $('.bank-mode').hide();
                $('.ssb-mode').hide();
                $('.online-mode').hide();
                $('.cheque-detail-show').hide();
                $('.newId').text("Submit");
                $('.newId').removeClass("send_otp");
                $('.newId').attr("id", "submitBtn");
                $('.cheque-mode').hide();
                $('#customer_cheque').val('');




            } else if (type == 1) {
                $('.bank-mode').show();
                $('.cash-mode').hide();
                $('.ssb-mode').hide();
                $('.cheque-detail-show').hide();
                $('.newId').text("Submit");
                $('.newId').removeClass("send_otp");
                $('.newId').attr("id", "submitBtn");
                $('.cheque-mode').hide();
                $('#customer_cheque').val('');




                // Get Banks of related company
                $.ajax({
                    type: "POST",
                    url: "{{ route('branch.fetchbranchbycompanyid') }}",
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
                            var optionBank = `<option value="">----Please Select----</option>`;
                            myObj.bank.forEach(element => {
                                optionBank +=
                                    `<option value="${element.id}">${element.bank_name}</option>`;
                            });
                            $('#company_bank').html(optionBank);
                        }
                    }
                });

            } else if (type == 0) {
                $('.ssb-mode').show();
                $('.cash-mode').hide();
                $('.bank-mode').hide();
                $('.online-mode').hide();
                $('.cheque-detail-show').hide();
                $('.cheque-mode').hide();
                $('#customer_cheque').val('');

                // $('#submitBtn').addClass("send_otp");
                // $('#submitBtn').removeAttr("id");

                // Change the text value of the button to "Send OTP"
                // $('.send_otp').text("Send OTP");



            }
        });

        // payment mode in bank case change
        $(document).on('change', '#bank_transfer_mode', function() {
            var branchId = $('#branch').val();
            var companyId = $('#company_id').val();
            console.log(branchId, companyId);
            var bank_payment_mode = $(this).val();
            if (bank_payment_mode == '') {
                $('.cheque-mode').hide();
                $('.online-mode').hide();
                $('#customer_cheque').val('');
                $('.cheque-detail-show').hide();

            } else if (bank_payment_mode == 0) {
                $('.cheque-mode').show();
                $('.online-mode').hide();
                $('#cheque-detail-show').hide();
                $('#customer_cheque').val('');

                $.ajax({
                    type: 'POST',
                    url: "{!! route('branch.approve_cheque_branchwise') !!}",
                    dataType: 'JSON',
                    data: {
                        'company_id': companyId,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response);
                        $('#customer_cheque').find('option').remove();
                        $('#customer_cheque').append(
                            '<option value="">--- Select Cheque ---</option>');
                        if (response.length > 0) {
                            // $('.cheque-transaction').show();
                            var options = $.each(response, function(key, value) {
                                $('#customer_cheque').
                                append('<option value="' + value.id +
                                    '" id="cheque_no">' + value.cheque_no +
                                    '(' + value.amount + ')' + '</option>');
                            })
                            // $('#customer_cheque').append(options);
                        } else {
                            var msg = 'No Cheque';
                            // $('#cheque-detail-show').hide();
                            var options =
                                $('#customer_cheque').
                            append('<option value="">' + msg + '</option>');
                            swal("Error!", "No Cheque Found!", "error")
                        }
                    }
                })


            } else if (bank_payment_mode == 1) {
                $('.cheque-mode').hide();
                $('.online-mode').show();
                $('#cheque-detail-show').hide();
                $('#customer_cheque').val('');

            }
        });

        // Get Associate details
        $('#loan_associate_code').on('change', function() {
            var associateCode = $(this).val();
            var applicationDate = $('.application_date').val();
            $.ajax({
                type: "POST",
                url: "{!! route('branch.loan.getcollectorassociate') !!}",
                dataType: 'JSON',
                data: {
                    'code': associateCode,
                    'applicationDate': applicationDate
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == 'success') {
                        var firstName = response.collectorDetails.first_name ? response
                            .collectorDetails.first_name : '';
                        var lastName = response.collectorDetails.last_name ? response
                            .collectorDetails.last_name : '';
                        $('#associate_member_id').val(response.collectorDetails.id);
                        $('#loan_associate_name').val(firstName + ' ' + lastName);
                        $('#loan_associate_id').val(response.collectorDetails.id);
                    } else if (response.msg_type == 'error') {
                        $('#loan_associate_code').val('');
                        $('#loan_associate_name').val('');
                        swal("Error!", "Associate Code does not exists!", "error");
                    }
                }
            });
        });

        // Get bank account of relevant banks
        $('#company_bank').change(function() {
            var bankId = $(this).val();
            $.ajax({
                type: "POST",
                url: "{{ route('branch.getBankAccountNos') }}",
                data: {
                    'bank_id': bankId,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let data = JSON.parse(response)
                    let html = ` <option value="">---- Please Select----</option>`;
                    data.forEach(element => {
                        html +=
                            `<option value="${element.account_no}">${element.account_no}</option>`;
                    });
                    $('#bank_account_number').html(html);

                }
            });

        });

        $('#loan_emi').validate({
            rules: {
                'application_date': {
                    required: true
                },
                'loan_associate_code': {
                    required: true,
                    number: true
                },
                'loan_type': {
                    required: true,
                },
                'loan_emi_payment_mode': 'required',
                'ssb_account': {
                    required: true,
                    number: true
                },
                'deposite_amount': {
                    required: true,
                    number: true,
                    greaterThanZero: true,
                    lessThanSanction: "#closure_amount",
                    'lessThanSsb': function() {
                        if ($("#loan_emi_payment_mode").val() != "" && $("#loan_emi_payment_mode").val() == '0') {
                            return "#ssb_account";
                        } else {
                            return false;
                        }
                    },
                    'ssbMinimum': function() {
                        if($('#company_id').val() == '1'){
                            var r;
                            if ($("#loan_emi_payment_mode").val() != "" && $("#loan_emi_payment_mode").val() == '0') {
                                r = "#ssb_account";
                            } else {
                                r = false;
                            }
                            return r;
                        }else{
                            return false;
                        }
                    },
                    'equalToCheque': function() {
                        if ($("#customer_cheque").val() != "") {
                            return "#cheque-amount";
                        } else {
                            return false;
                        }
                    }
                },
                'transaction_id': {
                    required: true,
                    number: true
                },
                'account_number': {
                    required: true,
                    number: true
                },
                'customer_bank_name': 'required',
                'customer_bank_account_number': {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                'customer_branch_name': {
                    required: true
                },
                'customer_ifsc_code': {
                    required: true,
                    checkIfsc: true
                },
                'company_bank': {
                    required: true
                },
                'company_bank_account_number': {
                    required: true,
                    number: true
                },
                'bank_account_number': {
                    required: true
                },
                'customer_cheque': {
                    required: true,
                    number: true
                },
                'company_bank_account_balance': {
                    required: true
                },
                'bank_transfer_mode': {
                    required: true
                },
                'utr_transaction_number': {
                    required: true
                },
                'online_total_amount': {
                    required: true
                },
                'cheque_id': {
                    required: true
                },
                'cheque_total_amount': {
                    required: true
                },
                'loan_branch': {
                    required: true
                },
                'ssb_account_number': {
                    required: true
                },
                'ssb_account': {
                    required: true,
                }
            },
            messages: {
                'application_date': "Please enter the deposit date",
                'loan_associate_code': "Please enter loan associate code",
                'loan_emi_payment_mode': "Please select a payment mode",
                'ssb_account': "Please enter Amount",
                'deposite_amount': {
                    required: "Please enter deposit amount",
                    // lessThanSanction: "Deposit amount must be less than the sanction amount.",
                    lessThanSsb: "Deposit amount must be less than the ssb amount",
                    pattern: "Enter Valid Amount",
                },
                'closure_amount':{
                    lessThanSanction: "Deposit amount must be less than  or equal to the closure amount.",
                },
                'transaction_id': "Please enter  transaction ID",
                'account_number': "Please enter account number",
                'customer_bank_name': "Please enter the customer's bank name",
                'customer_bank_account_number': "Please enter customer bank account number (numeric, 8-20 digits)",
                'customer_branch_name': "Please enter the customer's branch name",
                'customer_ifsc_code': "Please enter IFSC code",
                'company_bank': "Please enter the company's bank name",
                'company_bank_account_number': "Please Select company bank account number",
                'bank_account_number': "Please Select the bank account number",
                'customer_cheque': "Please enter customer cheque number)",
                'company_bank_account_balance': "Please enter the company bank account balance",
                'bank_transfer_mode': "Please select a bank transfer mode",
                'utr_transaction_number': "Please enter a UTR transaction number",
                'online_total_amount': "Please enter the total online amount",
                'cheque_id': "Please enter cheque ID",
                'cheque_total_amount': "Please enter the total cheque amount",
                'loan_branch': "Please select the loan branch",
                'ssb_account_number': "Please enter SSB account number"
            },
            submitHandler: function(form) {
                $("#submitBtn").prop("disabled", true);
                form.submit();
            }
        });

        $(document).on('change', '#customer_cheque', function() {
            $('#cheque-detail-show').hide();
            var cheque_id = $('option:selected', this).val();
            var deposite_amount = parseFloat($('#deposite_amount').val()).toFixed(2);
            $.ajax({
                type: "POST",
                url: "{!! route('branch.approve_cheque_details') !!}",
                dataType: 'JSON',
                data: {
                    'cheque_id': cheque_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(deposite_amount);
                    console.log(parseFloat(response.amount).toFixed(0));
                    console.log(parseFloat(response.amount).toFixed(2));
                    if (deposite_amount != parseFloat(response.amount).toFixed(2)) {
                        swal('Error!', 'Cheque amount should be equal to deposite amount',
                            'error');
                        $('#customer_cheque').val('');
                    } else {
                        $('#customer_bank_name').val(response.bank_name);
                        $('#customer_branch_name').val(response.branch_name);
                        $('#cheque-date').val(response.cheque_create_date);
                        $('#cheque-amount').val(parseFloat(response.amount).toFixed(2));
                        $('#cheque_company_bank').val(response.deposit_bank_name);
                        $('#company_bank_account_number').val(response.deposite_bank_acc);
                        $('#cheque-detail-show').show();
                    }
                }
            });
        });

        // Value must be greater than zero
        $.validator.addMethod("greaterThanZero", function(value, element) {
            if (parseFloat(value) > 0) {
                return true;
            } else {
                return false;
            }
        }, "Value must be greater than 0.");

        // amount equal to checque amount
        $.validator.addMethod("equalToCheque", function(value, element, params) {
                var depositAmount = parseFloat(value);
                var sanctionAmount = parseFloat($(params).val());

                console.log(value, element, params);
                if (isNaN(sanctionAmount)) {
                    return true;
                }
                else {
                if (depositAmount != sanctionAmount) {
                    console.log('true');
                    return false;
                } else {
                    console.log('false');

                    return true;
                }
            }
        }, "Cheque Amount and deposite amount must be same.");
        // amount must be less than sanction amount
        $.validator.addMethod("lessThanSanction", function(value, element, params) {
            var depositAmount = parseFloat(value);
            var sanctionAmount = parseFloat($(params).val());


            if (depositAmount <= sanctionAmount) {
                return true;
            } else {
                return false;
            }
        }, "Deposit amount must be less than  or equal to the closure amount.");

        // Amount should be less than ssb account
        $.validator.addMethod("lessThanSsb", function(value, element, params) {
            console.log(value, element, params);
            var depositAmount = parseFloat(value);
            var sanctionAmount = parseFloat($(params).val());
            var company = $('#company_id').val();
            if (isNaN(sanctionAmount)) {
                return true;
            } else {
                if(company != 1){
                    if (depositAmount <= sanctionAmount) {
                        return true;
                    } else {
                        return false;
                    }
                }else{
                    if (depositAmount < sanctionAmount) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }

        }, "Deposit amount must be less than the ssb amount.");

        // Mininimum account shout be 500 in ssbacount
        $.validator.addMethod("ssbMinimum", function(value, element, params) {
            // console.log(value, element, params);
            var depositAmount = parseFloat(value);
            var sanctionAmount = parseFloat($(params).val());
            var ssbAmount = sanctionAmount - depositAmount;
            // console.log(depositAmount,sanctionAmount,ssbAmount);
            if (isNaN(sanctionAmount)) {
                return true;
            } else {
                if (ssbAmount >= 500) {
                    return true;
                } else {
                    return false;
                }
            }

        }, "Minimum amount in ssb account should be 500.");

        $('#deposite_amount').on('change',function(){
            var closure_amount = parseFloat($('#closure_amount').val());
            if(closure_amount === 0){
                $(this).val('');
                swal('Warning','Contact to Loan Department !','warning');
            }
        });
        // OTP section

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
            highlight: function(element) {
                $(element).css('border', '1px solid red');
                $(element).removeClass('error');
            },
            unhighlight: function(element) {
                $(element).css('border', '');
            },
            errorPlacement: function(error, element) {
                // Do nothing to remove the error message
            }
        })
        $(".otp_inputs").keyup(function(e) {
            if (this.value.length == this.maxLength) {
                $(this).next('.otp_inputs').focus();
            }
            if (e.which >= 65 && e.which <= 90) {

                e.preventDefault();
            }
        });
        $(document).on('keypress', '.otp_inputs', function(event) {
            var keycode = (event.keycode ? event.keyCode : event.which);
            if (keycode >= 65 && keycode <= 90 || keycode >= 97 && keycode <= 122 || keycode >= 186 &&
                keycode <= 192 || keycode >= 219 && keycode <= 222 ||
                keycode >= 32 && keycode <= 47 || keycode >= 58 && keycode <= 64 || keycode >= 91 &&
                keycode <= 96 || keycode >= 123 && keycode <= 126) { // Disable all alphabet keys
                event.preventDefault();
            }
        });
        $('#verify').on('click', function(e) {
            e.preventDefault();
            var d = new Date($.now());
            const hrs = d.getHours();
            const minute = d.getMinutes();
            const second = d.getSeconds();
            var currentDate = hrs + ":" + minute + ":" + second;
            const currentTime = currentDate;
            const accountNumber = $('#ssb_account_number').val();
            let otp = '';
            $(".otp :input").each(function(e) {
                otp += $(this).val();
            });
            if ($("#otp_form").valid()) {
                $.ajax({
                    type: "POST",
                    url: "{!! route('branch.verify.ssb_otp') !!}",
                    dataType: 'JSON',
                    data: {
                        'currentTime': currentTime,
                        'otp': otp,
                        'accountNumber': accountNumber
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const type = (response.code == 200) ? 'success' : 'warning';
                        const selectedpaymentMode = sessionStorage.getItem('paymentMode');
                        //  swal(type,response.msg,type);
                        (response.code == 200) ? (
                            $('#withdrawal-ssb input').attr('readonly', 'readonly'),
                            $("#payment_mode").children().first().remove(),
                            $('.newId').text("Submit"),
                            $('.newId').removeClass("send_otp"),
                            $('.newId').attr("id", "submitBtn"),
                            $('.subButton').show(),
                            $('.otpbtn').hide(),
                            $("#exampleModal").modal('hide').fadeOut(),
                            $("#exampleModal").modal('hide').fadeOut(),
                            swal(type, response.msg, type),
                            emptyOtp()
                        ) : (
                            $('.subButton').hide(),
                            $('.otpbtn').show(),
                            $("#exampleModal").modal('show').fadeIn(),
                            $(".error-message").empty().removeClass(
                                'd-flex justify-content-center mt-2 text-danger'),
                            $(".error-message").append(response.msg).addClass(
                                'd-flex justify-content-center mt-2 text-danger')
                        );
                        //  $('#withdrawal-ssb').attr('')
                    }
                })
            }
        })
        $(document).on('click', '.send_otp, .cursor', function(e) {
            // alert('asda');
            e.preventDefault();
            const accountNumber = $('#ssb_account_number').val();
            var branchId = $('#branch').val();
            var companyId = $('#company_id').val();
            console.log(branchId, companyId);
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
                success: function(response) {
                    console.log("response", response);
                    let signature = response.signature;
                    let photo = response.photo;
                    if (response.msg == "inactive") {
                        // swal('Accout status','Your account is inactive. Change the account no.!!','warning');
                        swal('Accout status', 'Your account is inactive, please contact to admin !', 'warning');
                    } else {
                        otpGenerate();
                    }
                }
            });

            function otpGenerate() {
                const amount = $('#deposite_amount').val();
                const date = $('#created_at').val();
                let type = 'Warning';
                const paymentModeVal = $("#loan_emi_payment_mode option:selected").val();
                sessionStorage.setItem('paymentMode', paymentModeVal);
                if ($('#loan_emi').valid()) {
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
                        success: function(response) {
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
                                $("#exampleModal").modal('show').fadeIn();
                            }

                        }
                    })
                }
            }
        });




    });

// Function opt

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
                data: {
                    'account_number': accountNumber
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res) {
                        return true;
                    }
                }
            })
        }
    }
    ticker();
}

function emptyOtp(){
    var accountNo = $('#ssb_account_number').val();
    $.ajax({
                type: "POST",
                url: "{!! route('branch.update.ssb.otp') !!}",
                dataType: 'JSON',
                data: {
                    'account_number': accountNo
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res) {
                        return true;
                    }
                }
            });
}

function myStopFunction() {
    clearTimeout(setCounter);
}
</script>
