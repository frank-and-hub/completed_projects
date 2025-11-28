<script type="text/javascript">
    $(document).ready(function() {


        // $('#bank_transfer_mode').on('change', function() {
        //     var companyId = $('#company_id').val();
        //     var branch = $('option:selected', '#loan_branch').val();
        //     var paymentMode = $('option:selected', this).val();
        //     if (paymentMode == '0') {
        //         $.ajax({
        //             type: 'POST',
        //             url: "{!! route('admin.approve_cheque_branchwise') !!}",
        //             dataType: 'JSON',
        //             data: {
        //                 'branch_id': branch,'companyId':companyId
        //             },
        //             headers: {
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             },
        //             success: function(response) {
        //                 console.log(response);
        //                 $('#customer_cheque').find('option').remove();
        //                 $('#customer_cheque').append(
        //                     '<option value="">--- Select Cheque ---</option>');
        //                 if (response.length > 0) {
        //                     // $('.cheque-transaction').show();
        //                     var options = $.each(response, function(key, value) {
        //                         $('#customer_cheque').
        //                         append('<option value="' + value.id +
        //                             '" id="cheque_no">' + value.cheque_no +
        //                             '(' + value.amount + ')' + '</option>');
        //                     })
        //                     // $('#customer_cheque').append(options);
        //                 } else {
        //                     var msg = 'No Cheque';
        //                     $('#cheque-detail-show').hide();
        //                     var options =
        //                         $('#customer_cheque').
        //                     append('<option value="">' + msg + '</option>');
        //                     swal("Error!", "No Cheque Found!", "error")
        //                 }
        //             }
        //         })
        //     }
        // })

        $('#pay_file_charge').on('change', function() {

            var deDate = $('#date').val();
            const penaltyAmount = $('#insurance_amount1').val() ?? 0;
            var loanId = $('.loan_id').val();
            let gstAmount = 0;
            var gstFileCharge = 0;
            const igstFile = parseFloat($('#igst_file_charge_amount').attr('data-amount') ?? 0).toFixed(
                2);
            const cgstFile = parseFloat($('#cgst_file_charge_amount').attr('data-amount') ?? 0).toFixed(
                2);
            const sgstFile = parseFloat($('#sgst_file_charge_amount').attr('data-amount') ?? 0).toFixed(
                2);
            const igst = parseFloat($('#igst_amount').attr('data-amount') ?? 0).toFixed(2);
            const cgst = parseFloat($('#cgst_amount').attr('data-amount') ?? 0).toFixed(2);
            const sgst = parseFloat($('#sgst_amount').attr('data-amount') ?? 0).toFixed(2);
            const ecsCharge = parseFloat($('#ecs_charge').attr('data-amount') ?? 0).toFixed(2);
            const ecsigst = parseFloat($('#ecs_charge_igst').attr('data-amount') ?? 0).toFixed(2);
            const ecscgst = parseFloat($('#ecs_charge_cgst').attr('data-amount') ?? 0).toFixed(2);
            const ecssgst = parseFloat($('#ecs_charge_sgst').attr('data-amount') ?? 0).toFixed(2);


            var fileChragemethod = $('#pay_file_charge').val() ?? 0;
            var fileChrage = parseFloat($('#file_charge').val()).toFixed(2) ?? 0;
            var transferAmount = parseFloat($('#transfer_amount').attr('data-amount')).toFixed(2) ?? 0;
            // var transferAmountElement = $('#transfer_amount');


            if (fileChragemethod == 0 && fileChragemethod.length != '') {
                // console.log(transferAmount, fileChrage, penaltyAmount,ecsCharge, igst ?? 0, cgst, sgst, igstFile,
                //     cgstFile, sgstFile,ecsigst,ecscgst,ecssgst);
                var newTransferAmount = transferAmount - fileChrage - penaltyAmount - ecsCharge  - igst - cgst -
                    sgst - igstFile - cgstFile - sgstFile - ecsigst - ecscgst - ecssgst ;
            } else if (fileChragemethod == 1) {
                var newTransferAmount = transferAmount;
            } else {
                var newTransferAmount = 0;
            }
            $('#transfer_amount').html(parseFloat(newTransferAmount).toFixed(2));
            // console.log(newTransferAmount)
            var transferAmountElement = $('#transfer_amount');
            var hiddenTransferAmount = transferAmountElement.html();
            $('#hiddenTransferAmount').val(hiddenTransferAmount);

        })


        $(document).on('change', '#company_bank', function() {
            var account = $('option:selected', this).val();
            $('#company_bank_account_number').val('');
            $('#bank_account_number').val('');
            $('.c-bank-account').hide();
            $('.' + account + '-bank-account').show();
            $('#company_bank_account_balance').val('');
        });
        $(document).on('change', '#company_bank_account_number', function() {
            var account = $('option:selected', this).attr('data-account');
            var accountId = $('option:selected', this).val();
            var companyId = $('#companyId').val();
            var bank_id = $('option:selected', '#company_bank').val();
            $('#cheque_id').val('');
            $('.c-cheque').hide();
            $('.' + account + '-c-cheque').show();
            var date = $('#date').val();
            $.ajax({
                type: "POST",
                url: "{{ route('admin.bankChkbalance') }}",
                dataType: 'JSON',
                data: {
                    'bank_id': bank_id,
                    'account_id': account,
                    'entrydate': date,
                    'company_id': companyId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#company_bank_account_balance').val(response.balance);
                }
            });
        });


        $(document).on('change', '#rtgs_neft_charge', function() {
            var account = $(this).val();
            var fileCharge = $('#file_charge').val();
            var ins = $('#insurance_amount1').val();
            var transferType = $('option:selected', '#pay_file_charge').val();
            var bankTransferMode = $('option:selected', '#bank_transfer_mode').val();
            var tAmount = $('#online_total_amount').val();
            const traAmount = $('#transfer_amount').text();
            if (bankTransferMode == 1) {
                if (transferType == 0) {
                    if (account > 0) {
                        var accountVal = account;
                    } else {
                        var accountVal = 0;
                    }
                    if (tAmount > 0) {
                        var tAmountVal = tAmount;
                    } else {
                        var tAmountVal = 0;
                    }
                    if (fileCharge > 0) {
                        var fileChargeVal = fileCharge;
                    } else {
                        var fileChargeVal = 0;
                    }
                    if (ins > 0) {
                        var insVal = ins;
                    } else {
                        var insVal = 0;
                    }
                    $('#total_online_amount').val(parseFloat(traAmount));
                } else {
                    if (account > 0) {
                        var accountVal = account;
                    } else {
                        var accountVal = 0;
                    }
                    if (tAmount > 0) {
                        var tAmountVal = tAmount;
                    } else {
                        var tAmountVal = 0;
                    }
                    $('#total_online_amount').val(parseFloat(traAmount));
                }
            } else {
                $('#total_online_amount').val(0);
            }
        });
        $(document).on('change', '#pay_file_charge', function() {
            $('#rtgs_neft_charge').trigger('change');
        });
        $(document).on('change', '#insurance_amount1', function() {
            $('#rtgs_neft_charge').trigger('change');
        });
        $('#loan_emi_payment_mode').on('change', function() {
            var paymentMode = $('option:selected', this).val();
            var depositeAmount = $('#deposite_amount').val();
            var ssb_AccountNumber = $('#ssb_account_number').val();
            var date = $('#date').val();
            if (date == '') {
                var branch = $('#loan_emi_payment_mode').val('');
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false;
            }

            if (paymentMode == 0 /* && paymentMode != ''*/ ) {
                $('.ssb-account').show();
                $('.other-bank').hide();
                // var ssbAccount = $('#ssbaccount').val();
                $('#ssb_account_number').show();
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
                if (ssb_AccountNumber > 0) {
                    $.ajax({
                        type: "POST",
                        url: "{--!! route('admin.investment.planform_saving_account') !!--}",
                        data: {
                            'account_no': ssb_AccountNumber
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.data == 0) {
                                swal("Warning",
                                    "You can not pay with inactive SSB account, Please select other payment mode for payment!",
                                    "warning");
                                $("#loan_emi_payment_mode option:selected").prop("selected",
                                    false);
                                return false;
                            }
                            if (response.data == 2) {
                                swal("Error", "Member dose not have SSB Account !",
                                    "error");
                                $("#payment-mode option:selected").prop("selected", false);
                            }
                        }
                    });
                }
            } else if (paymentMode == 1 && paymentMode != '') {
                $('.ssb-account').hide();
                $('.other-bank').show();
                $('#company_bank_detail').hide('');
            } else {
                $('.ssb-account').hide();
                $('.other-bank').hide();
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            }
            $('.cheque-transaction').hide();
            $('.online-transaction').hide();
        });
        $(document).on('change', '#date', function() {
            var transferType = $('option:selected', '#payment_mode').val();
            var type = $('#type').val();
            var date = $('#date').val();
            var ssbCreatedDate = $('#ssb_created_date').val();
            if (transferType == 0 && transferType != '') {
                var dString = date.split("/");
                var nDate = dString[2] + '-' + dString[1] + '-' + dString[0];
                if (new Date(ssbCreatedDate) > new Date(nDate)) {
                    $('#payment_mode').val('');
                    $('#ssb_account_number').val('');
                    $('#payment_mode').trigger('change');
                    swal("Warning!", "SSB account not created at this date!", "warning");
                    return false;
                }
            }
        });
        $('#date').on('change', function() {
            const branch = $('#branchid').val();
            const sancDate = $('#date').val();
            const sancAmount = $('#amount').val();
            var payment_mode = $('option:selected', '#payment_mode').val();
            if (payment_mode == 2) {
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.withdraw.getdaybookdata') !!}",
                    dataType: 'JSON',
                    data: {
                        date: sancDate,
                        branchId: branch
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response.microAmount, sancAmount, date, branch);
                        if (response.microAmount < sancAmount) {
                            swal("Warning!", "Insufficient balance!", "warning");
                            $('#date').val('');
                            $('#payment_mode').val('');
                        }
                    }
                })
            }
        })
        $('#payment_mode').on('change', function() {
            var paymentMode = $('option:selected', this).val();
            var date = $('#date').val();
            var ssbCreatedDate = $('#ssb_created_date').val();
            if (date == '') {
                var branch = $('#payment_mode').val('');
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false;
            }
            if (paymentMode == 0 && paymentMode != '') {
                var ssbAccount = $('#ssbaccount').val();
                var dString = date.split("/");
                var nDate = dString[2] + '-' + dString[1] + '-' + dString[0];
                if (new Date(ssbCreatedDate) > new Date(nDate)) {
                    $('#payment_mode').val('');
                    $('.ssb-transfer').hide();
                    $('.other-bank').hide();
                    $('#ssb_account_number').val('');
                    $('#ssb_account_number').attr('readonly', false);
                    $('#customer_bank_name').val('');
                    $('#customer_bank_account_number').val('');
                    $('#customer_branch_name').val('');
                    $('#customer_ifsc_code').val('');
                    $('#company_bank').val('');
                    $('#company_bank_account_number').val('');
                    $('#company_bank_account_balance').val('');
                    $('#bank_transfer_mode').val('');
                    $('#cheque_id').val('');
                    $('#total_amount').val('');
                    $('#utr_transaction_number').val('');
                    $('#total_amount').val('');
                    $('#rtgs_neft_charge').val('');
                    // $('#total_online_amount').val('');
                    $('.cheque-transaction').hide();
                    $('.online-transaction').hide();
                    swal("Warning!", "SSB account not created at this date!", "warning");
                    return false;
                }
                $('#ssb_account_number').val(ssbAccount);
                $('.ssb-transfer').show();
                $('.other-bank').hide();
                $('#ssb_account_number').attr('readonly', true);
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#company_bank_detail').hide();
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            } else if (paymentMode == 1 && paymentMode != '') {
                $('.ssb-transfer').hide();
                $('.other-bank').show();
                $('#company_bank_detail').show();
                $('#ssb_account_number').val('');
                $('#ssb_account_number').attr('readonly', false);
            } else {
                const branch = $('#branchid').val();
                const sancDate = $('#date').val();
                const sancAmount = $('#amount').val();
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.withdraw.getdaybookdata') !!}",
                    dataType: 'JSON',
                    data: {
                        date: sancDate,
                        branchId: branch
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response.microAmount, sancAmount, date, branch);
                        if (response.microAmount < sancAmount) {
                            swal("Warning!", "Insufficient balance!", "warning");
                            $('#date').val('');
                            $('#payment_mode').val('');
                        }
                    }
                })
                $('.ssb-transfer').hide();
                $('.other-bank').hide();
                $('#ssb_account_number').val('');
                $('#ssb_account_number').attr('readonly', false);
                $('#company_bank_detail').hide();
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            }
            $('.cheque-transaction').hide();
            $('.online-transaction').hide();
        });
        // $('#payment_mode').on('change',function(){
        //     var paymentMode = $('option:selected', this).val();
        //     var date = $('#date').val();
        //     var ssbCreatedDate = $('#ssb_created_date').val();
        //     if(date == ''){
        //         var branch = $('#payment_mode').val('');
        //         swal("Warning!", "Please select a transfer date first!", "warning");
        //         return false;
        //     }
        //     if(paymentMode == 0 && paymentMode != ''){
        //         var ssbAccount = $('#ssbaccount').val();
        //         var dString = date.split("/");
        //         var nDate = dString[2]+'-'+dString[1]+'-'+dString[0];
        //         if(new Date(ssbCreatedDate) > new Date(nDate)){
        //             $('#payment_mode').val('');
        //             $('.ssb-transfer').hide();
        //             $('.other-bank').hide();
        //             $('#ssb_account_number').val('');
        //             $('#ssb_account_number').attr('readonly', false);
        //             $('#customer_bank_name').val('');
        //             $('#customer_bank_account_number').val('');
        //             $('#customer_branch_name').val('');
        //             $('#customer_ifsc_code').val('');
        //             $('#company_bank').val('');
        //             $('#company_bank_account_number').val('');
        //             $('#company_bank_account_balance').val('');
        //             $('#bank_transfer_mode').val('');
        //             $('#cheque_id').val('');
        //             $('#total_amount').val('');
        //             $('#utr_transaction_number').val('');
        //             $('#total_amount').val('');
        //             $('#rtgs_neft_charge').val('');
        //             $('#total_online_amount').val('');
        //             $('.cheque-transaction').hide();
        //             $('.online-transaction').hide();
        //             swal("Warning!", "SSB account not created at this date!", "warning");
        //             return false;
        //         }
        //         $('#ssb_account_number').val(ssbAccount);
        //         $('.ssb-transfer').show();
        //         $('.other-bank').hide();
        //         $('#ssb_account_number').attr('readonly', true);
        //         $('#customer_bank_name').val('');
        //         $('#customer_bank_account_number').val('');
        //         $('#customer_branch_name').val('');
        //         $('#customer_ifsc_code').val('');
        //         $('#company_bank').val('');
        //         $('#company_bank_account_number').val('');
        //         $('#company_bank_account_balance').val('');
        //         $('#bank_transfer_mode').val('');
        //          $('#company_bank_detail').hide();
        //         $('#cheque_id').val('');
        //         $('#total_amount').val('');
        //         $('#utr_transaction_number').val('');
        //         $('#total_amount').val('');
        //         $('#rtgs_neft_charge').val('');
        //         $('#total_online_amount').val('');
        //     }else if(paymentMode == 1 && paymentMode != ''){
        //         $('.ssb-transfer').hide();
        //         $('.other-bank').show();
        //          $('#company_bank_detail').show();
        //         $('#ssb_account_number').val('');
        //         $('#ssb_account_number').attr('readonly', false);
        //     }else{
        //         $('.ssb-transfer').hide();
        //         $('.other-bank').hide();
        //         $('#ssb_account_number').val('');
        //         $('#ssb_account_number').attr('readonly', false);
        //         $('#company_bank_detail').HIDE();
        //         $('#customer_bank_name').val('');
        //         $('#customer_bank_account_number').val('');
        //         $('#customer_branch_name').val('');
        //         $('#customer_ifsc_code').val('');
        //         $('#company_bank').val('');
        //         $('#company_bank_account_number').val('');
        //         $('#company_bank_account_balance').val('');
        //         $('#bank_transfer_mode').val('');
        //         $('#cheque_id').val('');
        //         $('#total_amount').val('');
        //         $('#utr_transaction_number').val('');
        //         $('#total_amount').val('');
        //         $('#rtgs_neft_charge').val('');
        //         $('#total_online_amount').val('');
        //     }
        //     $('.cheque-transaction').hide();
        //     $('.online-transaction').hide();
        // });
        //old code
        // $("#loan-transfer-form").submit(function(event) {
        //     var transferType = $('option:selected', '#payment_mode').val();
        //     var cAmount = $('#company_bank_account_balance').val();
        //     var cBank = $('#company_bank').val();
        //     if (transferType == 1) {
        //         var mode = $('option:selected', '#bank_transfer_mode').val();
        //         if (mode == 0) {
        //             var amount = $('#cheque_total_amount').val();
        //         } else {
        //             // var amount = $('#total_online_amount').val();
        //         }
        //         // Changes By Anup SIr = 01-09-2022 (Aman jain )
        //         //https://pm.w3care.com/projects/1892/tasks/45618
        //         if (cBank != 2) {
        //             if (parseInt(amount) > parseInt(cAmount)) {
        //                 swal("Warning!", "Insufficient balance!", "warning");
        //                 event.preventDefault();
        //             }
        //         }
        //     } else {
        //         return true;
        //     }
        // });
            // updated by mahesh on 30 jan 2024 
        $("#loan-transfer-form").validate({
            rules: {
                ecs_ref_no: {
                    required: true
                },
                date: 'required',
                payment_mode: 'required',
                ssb_account_number: 'required',
                company_bank: 'required',
                company_bank_account_number: 'required',
                bank_transfer_mode: 'required',
                cheque_id: 'required',
                utr_transaction_number: 'required',
                rtgs_neft_charge: {
                    required: true,
                    number:true
                },
            },
            messages: {
                date: "Please select a date.",
                payment_mode: "Please select a payment mode.",
                ssb_account_number: "Please enter  SSB account number.",
                company_bank: "Please Select Company bank.",
                company_bank_account_number: "Please Select company bank account number.",
                bank_transfer_mode: "Please select  bank transfer mode.",
                cheque_id: "Please Select Cheque .",
                utr_transaction_number: "Please enter UTR transaction number.",
                rtgs_neft_charge: "Please enter the RTGS/NEFT charge."
            },
            submitHandler: function(form) {
                var transferType = $('option:selected', '#payment_mode').val();
                var cAmount = $('#company_bank_account_balance').val();
                var cBank = $('#company_bank').val();

                if (transferType == 1) {
                    var mode = $('option:selected', '#bank_transfer_mode').val();
                    var amount;

                    if (mode == 0) {
                        amount = $('#cheque_total_amount').val();
                    } else {
                        amount = $('#total_online_amount').val();
                    }

                    if (cBank != 2) {
                        if (parseInt(amount) > parseInt(cAmount)) {
                            swal("Warning!", "Insufficient balance!", "warning");
                            return false; // Prevent form submission
                        }
                    }
                }

                // If everything is valid, allow the form submission
                form.submit();
            }
        });

        // Existing submit logic (if any) can be removed from here

        $('#submit').on('click', function() {
            var ssbAccount = $('#ssb_account_number').val();
            var transferType = $('option:selected', '#payment_mode').val();
            $('.create_at').val($('.gdate').text());
            if (ssbAccount == '' && transferType == 0) {
                swal("Warning!", "Please Create SSB Account First!!", "warning");
                return false;
            }
        })
        $("#loan_emi").submit(function(event) {
            var transferType = $('option:selected', '#loan_emi_payment_mode').val();
            var cAmount = $('#company_bank_account_balance').val();
            if (transferType == 1) {
                var mode = $('option:selected', '#bank_transfer_mode').val();
                if (mode == 0) {
                    var amount = $('#cheque_total_amount').val();
                } else {
                    var amount = $('#online_total_amount').val();
                }
                if (parseInt(amount) > parseInt(cAmount)) {
                    swal("Warning!", "Insufficient balance!", "warning");
                    event.preventDefault();
                }
            } else {
                return true;
            }
        });
        $('#deposite_amount').on('change', function() {
            if ($('#deposite_amount').val()) {
                var depositAmount = $('#deposite_amount').val();
            } else {
                var depositAmount = 0;
            }
            // if ($('#penalty_amount').val()) {
            //     var penaltyAmount = $('#penalty_amount').val();
            // } else {
            //     var penaltyAmount = 0;
            // }
            if (depositAmount > 0) {
                var depositAmountVal = depositAmount;
            } else {
                var depositAmountVal = 0;
            }
            // if (penaltyAmount > 0) {
            //     var penaltyAmountVal = penaltyAmount;
            // } else {
            // }
            var penaltyAmountVal = 0;
            $('#cheque_total_amount').val(parseFloat(depositAmountVal) + parseFloat(penaltyAmountVal));
            // $('#total_online_amount').val(parseFloat(depositAmountVal) + parseFloat(penaltyAmountVal));
        });
        $('#bank_transfer_mode').on('change', function() {
            var bankTransferMode = $('option:selected', this).val();
            if (bankTransferMode == 0 && bankTransferMode != '') {
                const acType = $('option:selected', '#company_bank_account_number').attr(
                'data-account');
                console.log('.' + acType + '-c-cheque');
                $('.cheque-transaction').show();
                $('.' + acType + '-c-cheque').show();
                $('.cheque-transaction').show();
                $('.online-transaction').hide();
                $('#company_bank_detail').hide();
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            } else if (bankTransferMode == 1 && bankTransferMode != '') {

                $('.online-transaction').show();
                $('.cheque-transaction').hide();
                $('#cheque-detail-show').hide();
                $('#company_bank_detail').show();
                $('#cheque_id').val('');
                $('#total_amount').val('');
            } else {
                $('.online-transaction').hide();
                $('.cheque-transaction').hide();
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
                $('#company_bank_detail').hide();
            }
        });

        $('#company_bank').change(function() {
            var bankId = $(this).val();
            $.ajax({
                type: "POST",
                url: "{{ route('admin.getBankAccountNos') }}",
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
                    //             $('#from_Bank_account_no').html(`
                    // <option value="">---- Please Select----</option>
                    // <option  value="${response.account_no}">${response.account_no}</option>
                    // `);
                }
            });

        });
        $('#cheque_number').on('change', function() {
            var chequeDate = $('option:selected', this).attr('data-cheque-date');
            var chequeAmount = $('option:selected', this).attr('data-cheque-amount');
            var first_date = moment("" + chequeDate + "").format('DD/MM/YYYY');
            $('#cheque_date').val(first_date);
            $('#cheque_amount').val(chequeAmount);
        });
        $(document).on('change', '#customer_cheque', function() {
            var cheque_id = $('option:selected', this).val();
            var deposite_amount = parseFloat($('#deposite_amount').val()).toFixed(2);
            $.ajax({
                type: "POST",
                url: "{!! route('admin.approve_cheque_details') !!}",
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
                        swal('Error!', 'Cheque Amount Should be Equal to Deposite Amount',
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
        $('#bank_name').on('change', function() {
            var accountNumber = $('option:selected', this).attr('data-account-number');
            $('#account_number').val(accountNumber);
        });
        $('#loan_associate_code').on('change', function() {
            var associateCode = $(this).val();
            var applicationDate = $('.application_date').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.loan.getcollectorassociate') !!}",
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
                        //$('#ssb_account_number').val(response.collectorDetails.saving_account[0].account_no);
                        //$('#ssb_account').val(response.ssbAmount);
                        // $('#ssb_id').val(response.collectorDetails.saving_account[0].id);
                    } else if (response.msg_type == 'error') {
                        $('#loan_associate_code').val('');
                        $('#associate_member_id').val('');
                        $('#loan_associate_name').val('');
                        $('#ssb_account_number').val('');
                        $('#ssb_account').val('');
                        $('#ssb_id').val('');
                        swal("Error!", "Associate Code does not exists!", "error");
                    }
                }
            });
        });
        $('.application_date').on('change', function() {
            var currentEmiDate = $(this).val();
            var emiDate = $('#myID').attr('data-allemidate')
            var emiOpion = $('#myID').attr('emiOption')
            var title = $('#myID').attr('title')
            var emiDate = emiDate.split(',');
            var splitDate = currentEmiDate.split('/');
            var newEmidate = splitDate[2] + '-' + splitDate[1] + '-' + splitDate[0];
            console.log(emiOpion);
            // if(emiOpion == 1)
            // {
            //     if(title !== 'Pay Advanced EMI')
            // {
            //     if(emiDate.includes(newEmidate) == false)
            //     {
            //         swal('Sorry','Please Use Advance Payment or Emi date Should be Correct','error');
            //         $(this).val('');
            //         return false;
            //     }
            // }
            // else{
            //     if(emiDate.includes(newEmidate) == true)
            //     {
            //         swal('Sorry','Please Use  Pay Emi or Emi date Should be Correct','error');
            //         $(this).val('');
            //         return false;
            //     }
            // }
            // }
            // $('#penalty_amount').trigger('change')
        })
        $(document).on('click', '.pay-emi', function(e) {
            $('.gst1').hide();
            $('.gst2').hide();
            var loanId = $(this).attr('data-loan-id');
            var companyId = $(this).attr('data-company-id');
            var emiDates = $(this).attr('data-allemidate');
            var emiOption = $(this).attr('data-emioption');
            emiDates = emiDates.split('/');
            $('.pay-emi').removeAttr('id');
            $(this).removeAttr('id');
            $(this).attr('id', 'myID');
            $(this).attr('data-allemidate', emiDates);
            $(this).attr('emiOption', emiOption);
            var loanEMI = $(this).attr('data-loan-emi');
            var EmiDatesAll = $(this).attr('data-allemidate');
            var title = $(this).attr('title')
            var ssbAmount = $(this).attr('data-ssb-amount');
            var ssbAccount = $(this).attr('data-ssb-account');
            var ssbId = $(this).attr('data-ssb-id');
            var recoveredAmount = $(this).attr('data-recovered-amount');
            var lastRecoveredAmount = $(this).attr('data-last-recovered-amount');
            var closingAmount = $(this).attr('data-closing-amount');
            var dueAmount = $(this).attr('data-due-amount');
            var penaltyAmount = $(this).attr('data-penalty-amount');
            var EmiAmount = $(this).attr('data-emi-amount');
            var companyId = $(this).attr('data-company-id');

            $.post("{{ route('admin.fetchbranchbycompanyid') }}", {
                'company_id': companyId,
                'bank': 'false',
                'branch': 'true'
            }, function(e) {
                var branchData = e.branch;
                var selectElement = $('#loan_branch');
                selectElement.empty();
                for (var i = 0; i < branchData.length; i++) {
                    var option = $('<option></option>');
                    option.val(branchData[i][0].id);
                    option.text(branchData[i][0].name);
                    selectElement.append(option);
                }
            }, 'JSON');
            $('#deposite_amount').on('change', function() {
                var dAmount = $(this).val();
                var currentEmiDate = $('.application_date').val();
                var splitDate = currentEmiDate.split('/');
                var newEmidate = splitDate[2] + '-' + splitDate[1] + '-' + splitDate[0];
                var emiDate = $('#myID').attr('data-allemidate')
                // if( $(this).attr('data-emi-amount') == 1)
                // {
                //     if(title == 'Pay EMI')
                //     {
                //             if(dAmount > loanEMI)
                //         {
                //             swal('Sorry','Amount Shold be Less Than or Equal to Emi Amount','error');
                //             $(this).val('');
                //         }
                //     }
                //     else{
                //         if(dAmount <= loanEMI && emiDate.includes(newEmidate) == true)
                //         {
                //             swal('Sorry','Amount Shold be Less Than or Equalddd to Emi Amount','error');
                //             $(this).val('');
                //         }
                //     }
                // }
                // $('#ssbaccount').val(ssbAccount);
                $('#ssb_account').val(ssbAmount);
                $('#ssb_id').val(ssbId);
            })
            $('#title').val(title);
            $('#loan_id').val(loanId);
            $('#loan_emi_amount').val(loanEMI);
            $('#deposite_amount').val();
            $('#ssb_account_number').val(ssbAccount);
            $('#ssb_account').val(ssbAmount);
            $('#ssb_id').val(ssbId);
            //$('#ssb_id').val(ssbId);
            $('#recovered_amount').val(recoveredAmount);
            $('#closing_amount').val(closingAmount);
            $('#due_amount').val(dueAmount);
            $('#last_recovered_amount').val(lastRecoveredAmount);
            $('#companyId').val(companyId);

            if (penaltyAmount != '') {
                // $('#penalty_amount').val(penaltyAmount);
                // $('#penalty_amount').attr('readonly', false);
            } else {
                // $('#penalty_amount').val('');
                // $('#penalty_amount').attr('readonly', true);
            }
        })
        // $(document).on('click', '.pay-emi', function(e){
        //     var loanId = $(this).attr('data-loan-id');
        //     var loanEMI = $(this).attr('data-loan-emi');
        //     var ssbAmount = $(this).attr('data-ssb-amount');
        //     var ssbAccount = $(this).attr('data-ssb-account');
        //     var ssbId = $(this).attr('data-ssb-id');
        //     var recoveredAmount = $(this).attr('data-recovered-amount');
        //     var lastRecoveredAmount = $(this).attr('data-last-recovered-amount');
        //     var closingAmount = $(this).attr('data-closing-amount');
        //     var dueAmount = $(this).attr('data-due-amount');
        //     var penaltyAmount = $(this).attr('data-penalty-amount');
        //     $('#loan_id').val(loanId);
        //     $('#loan_emi_amount').val(loanEMI);
        //     $('#ssb_account_number').val(ssbAccount);
        //     $('#ssb_account').val(ssbAmount);
        //     $('#ssb_id').val(ssbId);
        //     $('#recovered_amount').val(recoveredAmount);
        //     $('#closing_amount').val(closingAmount);
        //     $('#due_amount').val(dueAmount);
        //     $('#last_recovered_amount').val(lastRecoveredAmount);
        //     if(penaltyAmount != ''){
        //         $('#penalty_amount').val(penaltyAmount);
        //         $('#penalty_amount').attr('readonly',false);
        //     }else{
        //         $('#penalty_amount').val('');
        //         $('#penalty_amount').attr('readonly',true);
        //     }
        // })
        $(document).on('click', '.reject-loan', function(e) {
            var url = $(this).attr('href');
            e.preventDefault();
            swal({
                title: "Are you sure, you want to delete this loan request?",
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
        /*
        $('.export').on('click',function(){
            var extension = $(this).attr('data-extension');
            $('#loan_recovery_export').val(extension);
            $('form#filter').attr('action',"{!! route('admin.loanrecovery.export') !!}");
            $('form#filter').submit();
            return true;
        });
        */
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#loan_recovery_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#loan_recovery_filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportmt(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#loan_recovery_export').val(extension);
                $('form#loan_recovery_filter').attr('action', "{!! route('admin.loanrecovery.export') !!}");
                $('form#loan_recovery_filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportmt(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loanrecovery.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportmt(start, limit, formData, chunkSize);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
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
        /*
            $('.export-loan').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#loan_details_export').val(extension);
                $('form#loan-filter').attr('action',"{!! route('admin.loandetails.export') !!}");
                $('form#loan-filter').submit();
                return true;
            });
        */
        $('.export-loan').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#loan_details_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#loan-filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportk(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#loan_details_export').val(extension);
                $('form#loan-filter').attr('action', "{!! route('admin.loandetails.export') !!}");
                $('form#loan-filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportk(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loandetails.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportk(start, limit, formData, chunkSize);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
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
        /*
            $('.export-group-loan').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#group_loan_recovery_export').val(extension);
                $('form#grouploanfilter').attr('action',"{!! route('admin.grouploanrecovery.export') !!}");
                $('form#grouploanfilter').submit();
                return true;
                
                "data": function(d) {
                    d.searchform = $('form#grouploanrecoveryfilter').serializeArray()
                },
            });
        */
        $('.export-group-loan').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#group_loan_recovery_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#grouploanrecoveryfilter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportm(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#group_loan_recovery_export').val(extension);
                $('form#grouploanfilter').attr('action', "{!! route('admin.grouploanrecovery.export') !!}");
                $('form#grouploanfilter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportm(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.grouploanrecovery.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportm(start, limit, formData, chunkSize);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
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
        /*
            $('.export-group-loan-details').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#group_loan_details_export').val(extension);
                $('form#group-loan-filter').attr('action',"{!! route('admin.grouploandetails.export') !!}");
                $('form#group-loan-filter').submit();
                return true;
            });
        	*/
        loanTransactionTable = $('#loan_transaction_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.transactionlist') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#transaction-loan-filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                // {
                //     data: 'branch_code',
                //     name: 'branch_code'
                // },
                // {
                //     data: 'sector',
                //     name: 'sector'
                // },
                // {
                //     data: 'region',
                //     name: 'region'
                // },
                // {
                //     data: 'zone',
                //     name: 'zone'
                // },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'plan_name',
                    name: 'plan_name'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'emi_amount',
                    name: 'emi_amount'
                },
                {
                    data: 'loan_sub_type',
                    name: 'loan_sub_type'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                }
                // {data: 'action', name: 'action',orderable: false, searchable: false},
            ],
            "bDestroy": true,
        });
        $(loanTransactionTable.table().container()).removeClass('form-inline');
        $('.export-group-loan-details').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#group_loan_details_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#group-loan-filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#group_loan_details_export').val(extension);
                $('form#group-loan-filter').attr('action', "{!! route('admin.grouploandetails.export') !!}");
                $('form#group-loan-filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.grouploandetails.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExport(start, limit, formData, chunkSize);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
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
        $(document).on('click', '.close-loan', function(e) {
            var loan_id = $(this).attr('data-id');
            var created_at = $('.created_at').val();
            e.preventDefault();
            swal({
                title: "Are you sure, you want to close this loan?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.loan.close') !!}",
                        dataType: 'JSON',
                        data: {
                            'loan_id': loan_id,
                            'created_at': created_at
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            location.reload(true);
                        }
                    });
                }
            });
        });
        $(document).on('click', '.close-group-loan', function(e) {
            var loan_id = $(this).attr('data-id');
            var created_at = $('.created_at').val();
            e.preventDefault();
            swal({
                title: "Are you sure, you want to close this loan?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.grouploan.close') !!}",
                        dataType: 'JSON',
                        data: {
                            'loan_id': loan_id,
                            'created_at': created_at
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            location.reload(true);
                        }
                    });
                }
            });
        });
        var today = new Date();
        $('.from_date,.to_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: "today",
            maxDate: today
        });
        $(".application_date,#date").hover(function() {
            var EndDate = $('.create_application_date').val();
            $('.application_date,#date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                endDate: EndDate,
                maxDate: today
            }).on("changeDate", function(e) {
                // console.log(( e.date));
                $('#due_date').datepicker('setDate', e.date);
            });
            $("#due_date").datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                orientation: "bottom",
            });
        });
        $(document).on('change', '.application_date', function() {
            var aDate = $(this).val();
            $('#created_date').val(aDate);
            var associateCode = $('#loan_associate_code').val();
            if (associateCode != '') {
                $('#loan_associate_code').trigger('change');
            }
        });
        $(document).on('change', '#loan_branch', function(e) {
            var loanId = $(this).val();
            $('#cheque_number').val('');
            $('#cheque_date').val('');
            $('.branch-cheques').hide();
            $('.' + loanId + '-branch').show();
        })

        $('#demandRejectReason').validate({ // initialize the plugin
            rules: {
                'rejectreason': {
                    required: true
                },
            },
        });
        $(document).on('click', '.reject-demand-advice', function(e) {
            const modalTitle = $(this).attr('modal-title');
            const loanId = $(this).attr('demandId');
            const loanType = $(this).attr('loantype');
            const loanCategory = $(this).attr('loanCategory');
            const status = $(this).attr('status');
            const el = document.createElement("input");
            const statusData = document.createElement("input");
            console.log("status", status);
            $('.dinput').remove();
            $('#demandRejectReason').attr('action', "{!! route('admin.loan.reject_hold') !!}")
            $('#exampleModalLongTitle').html(modalTitle);
            $inputData =
                '<input type="hidden" id="loanCategory" class="dinput" name="loanCategory" value = "' +
                loanCategory +
                '"><input type="hidden" id="loanType" class="dinput" name="loanType" value = "' +
                loanType + '"><input type="hidden" class="dinput" id="status" name="status" value = "' +
                status + '">'
            $('#demandRejectReason').append($inputData);
            $('#demandId').val(loanId);
        })
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        $('#pay_file_charge').trigger('change');
    });

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            loanRecoveryTable.draw();
        }
    }

    // function loanSearchForm() {
    //     $('#is_search').val("yes");
    //     $(".table-section").addClass("show-table");
    //     loanRequestTable.draw();
    // }


    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('.from_date').val('');
        $('.to_date').val('');
        $('#date').val('');
        $('#loan_account_number').val('');
        $('#member_name').val('');
        $('#member_id').val('');
        $('#associate_code').val('');
        $('#plan').val('');
        $('#status').val('');
        loanRecoveryTable.draw();
        $(".table-section").addClass('hideTableData');
    }

    //Loan Transaction Search Button Function 
    function loanTransactionSearchForm() {
        if ($('#transaction-loan-filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            loanTransactionTable.draw();
        }
    }

    //Loan Transaction Reset Button Function 
    function loanTransactionResetForm() {
        var form = $("#transaction-loan-filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#payment_mode').val('');
        $('#company_id').val('');
        $('#company_id').trigger('change');
        $('#date').val('');
        $('.from_date').val('');
        $('.to_date').val('');
        $('#application_number').val('');
        $('#transaction_loan_type').val('');
        $('#member_name').val('');
        $('#member_id').val('');
        $('#customer_id').val('');
        $('#associate_code').val('');
        $('#plan').val('');
        $('#status').val('');
        $(".table-section").addClass("hideTableData");
        loanTransactionTable.draw();
    }

    //Loan Recovery Search Button Function 
    function loanrecoverysearchForm() {
        if ($('#loan_recovery_filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            loanRecoveryTable.draw();
        }
    }

    //Loan Recovery Reset Button Function
    function loanrecoveryresetForm() {
        var form = $("#loan_recovery_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#company_id').val("");
        $('#company_id').trigger('change');
        $('.to_date').val("");
        $('.from_date ').val("");
        $('#loan_account_number').val("");
        $('#loan_recovery_type').val("");
        $('#loan_recovery_plan').empty();
        $('#loan_recovery_plan').append(' <option value="">----Select Loan Plan----</option>');
        $('#member_name').val("");
        $('#member_id').val("");
        $('#associate_code').val("");
        $('#group_loan_common_id').val("");
        $(".table-section").addClass('hideTableData');
        loanRecoveryTable.draw();
    }

    //Group Loan Recovery Search Button Function 
    function groupLoanRecoverySearchForm() {
        if ($('#grouploanrecoveryfilter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            groupLoanRecoveryTable.draw();
        }
    }

    //Group Loan Recovery Reset Button Function
    function groupLoanRecoveryResetForm() {
        var form = $("#grouploanrecoveryfilter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#company_id').val("");
        $('#company_id').trigger('change');
        $('#date_to').val("");
        $('#date_from').val("");
        $('#loan_account_number').val("");
        $('#group_loan_recovery_type').val('');
        $('#group_loan_recovery_plan').empty();
        $('#group_loan_recovery_plan').append(' <option value="">----Select Loan Plan----</option>');
        $('#member_name').val("");
        $('#member_id').val("");
        $('#associate_code').val("");
        $('#group_loan_common_id').val("");
        $('#table-section').addClass("hideTableData");
        groupLoanRecoveryTable.draw();
    }

    //Group Loan Search Button Function 
    function groupLoanSearchForm() {
        if ($('#group-loan-filter').valid()) {
            $('#is_search').val("yes");
            $(".d-none").removeClass('d-none');
            groupLoanRequestTable.draw();
        }
    }

    //Group Loan Reset Button Function
    function groupLoanResetForm() {
        var form = $("#group-loan-filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        groupLoanRequestTable.draw();
    }
</script>
