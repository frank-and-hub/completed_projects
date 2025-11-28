<script type="text/javascript">
    $(document).ready(function() {
        var demandAdviceTable;

        // if rent then hiden employee name 
        var paymentType = $('#paymentType2').val();
        if (paymentType == 0 || paymentType == '') {
            $('.employeename').hide();
        }


        $('#tasubmit').on('click', function() {
            $('#tasubmit').submit();
        });
        // Using for hide and show section according to payment type
        $('#paymentType').on('change', function() {
            var selected = $('option:selected', this).val();


            if (selected == 2) {
                $('.taadvance').show();
                $('#ta_employee_code').val('');
                $('#advanced_rent_party_name').val('');
                $('#ename').val('');
                $('#narration').val('');
                $('#aamount').val('');
                $('#advanced_salary_mobile_number2').val('');
                $('#advanced_salary_bank_name2').val('');
                $('#advanced_salary_bank_account_number2').val('');
                $('#advanced_salary_ifsc_code2').val('');
                $('#ssbno').val('');
                $('#payment_mode').val('');
                $('#transfer_mode').val('');
                $('#bank_balance').val('');
                $('#account_id').val('');
                $('#bank_id').val('');
                $('#utr_tran').val('');
                $('#neft_charge').val('');
                $('#cheque_id').val('');
                $('#tmode').hide('');
                $('.paymentmode').hide();
                $('#bankss').hide();
                $('#accourid').hide();
                $('#bankbalance').hide();
                $('#cheque').hide();
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();

                $(".employeecode").show();
                $(".employeename").show();
                $(".amount").text('Advance Amount');
                $(".ownerlist").hide();
                $('h3').text("Advance TA /Imprest Payment");
            } else if (selected == 1) {
                $('.taadvance').show();
                $('#ta_employee_code').val('');
                $('#advanced_rent_party_name').val('');
                $('#ename').val('');
                $('#narration').val('');
                $('#aamount').val('');
                $('#advanced_salary_mobile_number2').val('');
                $('#advanced_salary_bank_name2').val('');
                $('#advanced_salary_bank_account_number2').val('');
                $('#advanced_salary_ifsc_code2').val('');
                $('#ssbno').val('');
                $('#payment_mode').val('');
                $('#transfer_mode').val('');
                $('#bank_balance').val('');
                $('#account_id').val('');
                $('#bank_id').val('');
                $('#utr_tran').val('');
                $('#neft_charge').val('');
                $('#cheque_id').val('');
                $('#tmode').hide('');
                $('.paymentmode').hide();
                $('#bankss').hide();
                $('#accourid').hide();
                $('#bankbalance').hide();
                $('#cheque').hide();
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();


                $(".amount").text('Advance Salary');
                $(".employeecode").show();
                $(".employeename").show();
                $(".ownerlist").hide();
                $('h3').text("Advance Salary Payment");
            } else if (selected == 0) {
                $('.taadvance').show();
                $('#ta_employee_code').val('');
                $('#advanced_rent_party_name').val('');
                $('#ename').val('');
                $('#narration').val('');
                $('#aamount').val('');
                $('#advanced_salary_mobile_number2').val('');
                $('#advanced_salary_bank_name2').val('');
                $('#advanced_salary_bank_account_number2').val('');
                $('#advanced_salary_ifsc_code2').val('');
                $('#ssbno').val('');
                $('#payment_mode').val('');
                $('#transfer_mode').val('');
                $('#bank_balance').val('');
                $('#account_id').val('');
                $('#bank_id').val('');
                $('#utr_tran').val('');
                $('#neft_charge').val('');
                $('#cheque_id').val('');
                $('#tmode').hide('');
                $('.paymentmode').hide();
                $('#bankss').hide();
                $('#accourid').hide();
                $('#bankbalance').hide();
                $('#cheque').hide();
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();


                $(".amount").text('Advance Rent');
                $(".employeecode").hide();
                $(".employeename").hide();
                $(".ownerlist").show();
                $('h3').text("Advance Rent Payment");
            } else {
                $(".taadvance").hide();
                $("form :input:not(:checkbox,:radio,:submit,:select)").val("");
            }


        });

        // Using for date selection date Picker code
        $("#date").on('mouseover', function() {
            var today = $('.create_application_date').val();

            console.log(today);
            if ($('#companyDate').val()) {
                var sdate = $('#companyDate').val();
            } else {
                var sdate = '01/04/2021';
            }
            $('#date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                endDate: today, // Make sure 'today' is defined in your JavaScript
                startDate: sdate, // Make sure 'sdate' is defined in your JavaScript
                beforeShowDay: function(date) {
                    // var formattedDate = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                    // console.log('Formatted Date:', formattedDate);
                    // console.log('Disabled Dates:', {!!json_encode($data['disabled_dates']) !!});

                    // return ($.inArray(formattedDate, {!!json_encode($data['disabled_dates']) !!}) == -1);

                    var day = date.getDate();
    var month = date.getMonth() + 1; // Note: Month is zero-based
    var year = date.getFullYear();

    // Add leading zero for single-digit days and months
    var formattedDate = year + '-' + (month < 10 ? '0' + month : month) + '-' + (day < 10 ? '0' + day : day);

    console.log('Formatted Date:', formattedDate);
    console.log('Disabled Dates:', {!! json_encode($data['disabled_dates']) !!});

    return ($.inArray(formattedDate, {!! json_encode($data['disabled_dates']) !!}) == -1);
                }

            });


        })

        $("#date").ready(function() {
            $("#date").change(function() {
                // Update branch Balance
                const branch_id_new = $('#branch').val();
                $('#account_id').val('');
                var daybook = 0;
                var branch_id = branch_id_new;
                var date = $('#date').val();
                var company_id = $('#company_id').val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.advancePaymentAdjestment.branchCurrentBalance') !!}",
                    dataType: 'JSON',
                    data: {
                        'branch_id': branch_id,
                        'company_id': company_id,
                        'date': date
                    },
                    success: function(response) {
                        // console.log(response['balance']);
                        $('#branchBalance').val(response);
                    },
                    complete: function() {
                        $('.loader').hide(); // hide loader
                    }
                });
            });
        });

        $(document).on('change', '#branch', function() {
            $('.loader').show(); // show loader
            const branch_id_new = $('#branch').val();

            var daybook = 0;
            var branch_id = branch_id_new;
            var entrydate = $('#date').val();
            var company_id = $('#company_id').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePaymentAdjestment.branchCurrentBalance') !!}",
                dataType: 'JSON',
                data: {
                    'branch_id': branch_id,
                    'company_id': company_id,
                    'entrydate': entrydate
                },
                success: function(response) {
                    // console.log(response['balance']);
                    $('#branchBalance').val(response);
                },
                complete: function() {
                    $('.loader').hide(); // hide loader
                }
            });
        });

        var paymentType = $('#paymentType').val();
        var transfer_mode = $('#transfer_mode').val();



        // Validation
        $('#fillter').validate({ // initialize the plugin

            rules: {

                'paymentType': {
                    required: true
                },

                'date': {
                    required: true
                },


                'ta_employee_code': {
                    required: true
                },
                'narration': {
                    required: true
                },

                'ename': {
                    required: true
                },

                'particular': {
                    required: true
                },

                'aamount': {
                    required: true,
                    digits: true,
                    min: 1,
                },

                'neft_charge': {
                    decimal: false
                },

                'payment_mode': {
                    required: true
                },

                'transfer_mode': {
                    required: function(element) {
                        if (($("#payment_mode").val() == 'Bank' || $("#transfer_amount").val() == '')) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                },

                'bank_id': {
                    required: function(element2) {
                        if (($("#transfer_mode").val() != '')) {
                            return true;
                        } else {
                            return false;
                        }
                    }

                },
                'account_id': {
                    required: function(element3) {
                        if (($("#bank_id").val() != '')) {
                            return true;
                        } else {
                            return true;
                        }
                    }
                },

                'cheque_id': {
                    required: function(element4) {
                        if (($("#transfer_mode").val() == 0)) {
                            return true;
                        } else {
                            return true;
                        }
                    }
                },

                'utr_tran': {
                    required: function(element4) {
                        if (($("#transfer_mode").val() == 1)) {
                            return true;
                        } else {
                            return true;
                        }
                    }
                },

            },
            messages: {
                bank_id: {
                    required: "this field is required!",
                },
            },

            submitHandler: function(form) {
                // Submit form save
                $('input[type=submit]', this).prop('disabled', true); // disable submit button


                // Serialize the form data
                var formData = $("#fillter").serialize();
                var transferMode = $('#transfer_mode').val();
                var checkId2 = $('#cheque_id').val();
                var bank_balance = parseFloat($('#bank_balance').val());
                var aamount = parseFloat($('#aamount').val());
                var paymentMode = $("#payment_mode").val();
                var utr = $("#utr_tran").val();
                var neft = $("#neft_charge").val();
                var branchBalance = parseFloat($('#branchBalance').val());
                console.log(transferMode);


                if (paymentMode == "BANK") {


                    if (transferMode == 0) {

                        if (bank_balance <= aamount) {
                            swal("warning", "Insufficient funds!", "warning");
                            return false;
                        }

                        if (checkId2 == '') {
                            swal("warning", "Please Select Cheque!", "warning");
                            return false;
                        }

                    } else if (transferMode == 1) {

                        if (bank_balance <= aamount) {
                            swal("warning", "Insufficient funds!", "warning");
                            return false;
                        }

                        if (utr == '') {
                            swal("warning", "Field could not be empty UTR number!", "warning");
                            return false;
                        }

                        // if (neft == '') {
                        //     swal("warning", "Field could not be empty RTGS/NEFT Charge!", "warning");
                        //     return false;
                        // }


                    }
                }
                if (paymentMode == "CASH") {
                    if (branchBalance <= aamount) {
                        swal("warning", "Insufficient Branch Balance!", "warning");
                        return false;
                    }

                }



                // Send the Ajax request
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.advancePayment.saveadvancepayment') !!}",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Handle the Ajax response 
                        if (response) {
                            // console.log(response);
                            if (response.success && response.success == 'true') {
                                swal({
                                    title: 'Successfully!',
                                    text: response.message,
                                    type: 'success'
                                }, function(isConfirm) {
                                    window.location.href = "{{route('admin.advancePayment.requestList')}}";
                                });
                                $('#tasubmit').prop('disabled', true);
                            } else if (response.fail == 'berror') {
                                swal({
                                    title: 'warning!',
                                    text: response.message,
                                    type: 'warning'
                                });
                                return false;
                            } else {
                                swal("warning", "There was an error Please try again or change Payment Method!", "warning");
                                return false;
                            }

                            // swal("Success", response.message, "success");
                            // window.location.href = response.redirect;

                        }

                    },
                    error: function(xhr, status, error) {

                        // Handle Ajax errors
                        var err = eval("(" + xhr.responseText + ")");
                        swal("Warning!", err.errors[0], "warning");
                        // err.error

                        return false;


                    },
                    complete: function() {
                        $('.loader').hide(); // hide loader
                        $('input[type=submit]', this).prop('disabled', false); // enable submit button
                    }

                });
            }


        });


        // Fetch the employee data with employe code using ajax 
        $(document).on('change', '#ta_employee_code', function() {

            if ($('#date').val() == '') {

                if ($("#date").attr('required') == undefined) {
                    // Remove value from columns
                    $('#ename').val('');
                    $('#advanced_salary_mobile_number2').val('');
                    $('#advanced_salary_bank_name2').val('');
                    $('#advanced_salary_bank_account_number2').val('');
                    $('#advanced_salary_ifsc_code2').val('');

                    // Showing the error that date is required
                    swal("Warning!", "Please select the date!", "warning");
                    return false;
                }
            }



            var paymenttype = $('#paymentType').val();

            var employee_code = $(this).val();

            const branchId = $('#branch').val();

            var classVal = $(this).attr('data-val');

            $.ajax({

                type: "POST",

                url: "{!! route('admin.advancePayment.getemployee') !!}",

                dataType: 'JSON',

                data: {
                    'employee_code': employee_code,
                },

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                },
                async: true,

                success: function(response) {
                    if (response[0] == null) {
                        $('#ta_employee_code').val('');
                        $('#ename').val('');
                        $('#narration').val('');
                        $('#aamount').val('');
                        $('#advanced_salary_mobile_number2').val('');
                        $('#advanced_salary_bank_name2').val('');
                        $('#advanced_salary_bank_account_number2').val('');
                        $('#advanced_salary_ifsc_code2').val('');
                        $('#ssbno').val('');
                        swal("Warning!", "Employee Code not found!", "warning");
                    }

                    if (paymenttype == 2 || paymenttype == 1) {
                        $('#ename').val(response[0].employee_name);
                        $('#advanced_salary_mobile_number2').val(response[0].mobile_no);
                        $('#advanced_salary_bank_name2').val(response[0].bank_name);
                        $('#advanced_salary_bank_account_number2').val(response[0].bank_account_no);
                        $('#advanced_salary_ifsc_code2').val(response[0].bank_ifsc_code);
                        $('#ssbno').val(response[0]['get_ssb'].account_no);
                        var member = $('#member_id').val(response[0]['get_ssb'].member_id);
                        console.log(response[0]['get_ssb']);
                        $('#employee_id').val(response[0].id);


                        $('.paymentmode').show();

                    }



                },

                error: function(xhr, status, error) {
                    // handle error

                    // Remove ta/Imprest value if employee code not found
                    $('#ta_employee_code').val('');
                    $('#ename').val('');
                    $('#narration').val('');
                    $('#aamount').val('');
                    $('#advanced_salary_mobile_number2').val('');
                    $('#advanced_salary_bank_name2').val('');
                    $('#advanced_salary_bank_account_number2').val('');
                    $('#advanced_salary_ifsc_code2').val('');
                    $('#ssbno').val('');
                    // Ending Remove ta/Imprest value if employee code not found

                    $('#ssb').val('');
                    $('#advanced_salary_mobile_number').val('');
                    $('#advanced_salary_employee_name').val('');
                    $('#advanced_salary_ssb_account').val('');
                    $('#advanced_salary_bank_account_number').val('');
                    $('#advanced_salary_bank_name').val('');
                    $('#advanced_salary_ifsc_code').val('');
                    swal("Warning!", "Employee Code not found!", "warning");
                },
                complete: function() {
                    $('.loader').hide(); // hide loader
                }


            });

        });

        $("#payment_mode").on('change', function() {

            if ($(this).val() == 'SSB') {
                var ssbno = $('#ssbno').val();

                if (ssbno == '') {
                    swal("Warning!", "SSB account not available please select another method", "warning");
                    $(this).val("");
                    return false;
                }

                $('#ssb').show();
                $("#tmode").hide();
                $("#bankss").hide();
                $("#accourid").hide();
                $(".bankbalance ").hide();
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();
                $('#tamount').hide();
                $('#cheque').hide();
                $('#branchBalance2').hide();
                $('#tasubmit').removeClass('d-none');
            } else if ($(this).val() == 'BANK') {

                $.validator.addClassRules({
                    transfer_mode: {
                        required: true,
                    },
                    submitHandler: function(form) {
                        return false;
                    }
                });

                $("#transfer_mode").val('');
                $('#tmode').show();
                $('#account_id').val('');
                $('#ssb').hide();
                $('#branchBalance2').hide();


                // $('#tasubmit').addClass('d-none');
            } else if ($(this).val() == 'CASH') {
                $('.loader').show(); // show loader
                $(".utrnumber").hide();
                $(".rtgsnumber").hide();

                // Get the branch current balance
                const branch_id_new = $('#branch').val();

                var daybook = 0;
                var branch_id = branch_id_new;
                var entrydate = $('#date').val();
                var company_id = $('#company_id').val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.advancePaymentAdjestment.branchCurrentBalance') !!}",
                    dataType: 'JSON',
                    data: {
                        'branch_id': branch_id,
                        'company_id': company_id,
                        'entrydate': entrydate
                    },
                    success: function(response) {
                        // console.log(response['balance']);
                        $('#branchBalance').val(response);
                    },
                    complete: function() {
                        $('.loader').hide(); // hide loader
                    }
                });

                $("#branchBalance2").removeAttr('style');
                $("#tmode").hide();
                $('#utr').hide();
                $('#rtgs').hide();
                $('#tamount').hide();
                $('#bankss').hide();
                $('#accourid').hide();
                $('#bankbalance').hide();
                $('#cheque').hide();
                $('#tasubmit').removeClass('d-none');
            } else {

            }
        });

        $("#transfer_mode").on('change', function() {

            if ($(this).val() == 0) {
                $('#tcheckno').show();
                $('#tamount').show();
                $('#bankss').show();
                $('#bankbalance').show();
                $('#tasubmit').removeClass('d-none');
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();
                $('#accourid').show();

                $('#bank_id').val('');

                $('#cheque_id').val('');
                $('#bank_balance').val('0.00');

                $('#cheque').removeAttr('style');


                // $('#tamount').hide();
            } else {
                $('#bankss').show();
                $('#bankbalance').show();
                $('.utrnumber').show();
                $('.rtgsnumber').show();
                $('#accourid').show();
                $('#tasubmit').removeClass('d-none');
                $('#tcheckno').hide();
                $('#cheque').hide();
                $('#bank_id').val('');
                $('#account_id').val('');
                $('#cheque_id').val('');
                $('#bank_balance').val('');
            }

        });

        $(document).on('change', '#bank_id', function() {
            $('.loader').show(); // show loader
            var bank_id = $('#bank_id').val();

            $('#bank_balance').val('0.00');



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
                    $('#cheque_id').find('option').remove();

                    $('#account_id').append('<option value="">Select account number</option>');

                    $.each(response.account, function(index, value) {

                        $("#account_id").append("<option data-accountNumber=" + value.account_no + " value=" + value.id + ">" + value.account_no + "</option>");

                    });
                    $('#account_id').prop("disabled", false);;


                },
                complete: function() {
                    $('.loader').hide(); // hide loader
                }

            });



        });

        $('#account_id').on('change', function() {

            var selectOption = $(this).find('option:selected');
            var accountNumber = selectOption.data('accountnumber');

            // put selected bank account number to input
            $('#accountNumber').val(accountNumber);

            $('#bank_balance').val('0.00');
            var account_id = $('#account_id').val();
            var bank_id = $('#bank_id').val();
            var entrydate = $('#date').val();
            var company_id = $('#company_id').val();

            if (entrydate == '') {
                $('#account_id').val(' ');
                swal("Warning!", "Please select at payment date", "warning");

            } else {
                $('.loader').show(); // show loader
                $.ajax({
                    type: "POST",
                    url: "{!! route('bankChequeList') !!}",
                    dataType: 'JSON',
                    data: {
                        'account_id': account_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#cheque_id').find('option').remove();
                        $('#cheque_id').append('<option value="">Select cheque number</option>');
                        $.each(response.chequeListAcc, function(index, value) {
                            $("#cheque_id").append("<option value='" + value.id + "'>" + value.cheque_no + "</option>");
                        });
                    },
                    complete: function() {
                        $('.loader').hide(); // hide loader
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.bankChkbalance') !!}",
                    dataType: 'JSON',
                    data: {
                        'account_id': account_id,
                        'bank_id': bank_id,
                        'company_id': company_id,
                        'entrydate': entrydate
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#bank_balance').val(response.balance);
                    },
                    complete: function() {
                        $('.loader').hide(); // hide loader
                    }

                });
            }
        });

        // on select cheque then show the submit button
        $('#cheque_id').on('change', function() {

            const payamount = parseFloat($('#aamount').val());
            const bankBalance = parseFloat($('#bank_balance').val());

            if (isNaN(payamount) || isNaN(bankBalance)) {
                swal("Error!", "Amount field is required!", "error");
                return false;
            }

            if (bankBalance < payamount) {
                swal("Warning!", "Insufficient Balance", "warning");
                return false;
            }

            return true;

            $('#tasubmit').removeClass('d-none');
        });






        // On select branch check the branch balance and update on branch update balance column
        $('#payment_branch').on('change', function() {

            var daybook = 0;
            var branch_id = $('#payment_branch').val();
            var entrydate = $('#select_date').val();
            $('#branch_total_balance').val('0.00');

            if (branch_id > 0 && daybook == 0) {
                if (entrydate == '') {
                    swal("Warning!", "Please select  payment date", "warning");
                    $('#branch_total_balance').val('0.00');
                } else {
                    $('.loader').show(); // show loader
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
                        },
                        complete: function() {
                            $('.loader').hide(); // hide loader
                        }
                    });
                }
            }
        })


        // // Fetch the owner details
        $(document).on('change', '#advanced_rent_party_name', function() {
            var val = $(this).val();
            var classVal = $(this).attr('data-val');

            // If date not selected then showing error start
            if ($('#date').val() == '') {

                if ($("#date").attr('required') == undefined) {
                    // Remove value from columns
                    $('#ename').val('');
                    $('#advanced_salary_mobile_number2').val('');
                    $('#advanced_salary_bank_name2').val('');
                    $('#advanced_salary_bank_account_number2').val('');
                    $('#advanced_salary_ifsc_code2').val('');

                    // Showing the error that date is required
                    swal("Warning!", "Please select the date!", "warning");
                    return false;
                }
            }
            // If date not selected then showing error End
            $('.loader').show(); // show loader
            $.ajax({
                type: "POST",
                url: "{!! route('admin.demand.getowner') !!}",
                dataType: 'JSON',
                data: {
                    'val': val
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.ownerDetails) {
                        console.log(response.ownerDetails);
                        $('#advanced_salary_mobile_number2').val(response.ownerDetails.owner_mobile_number);
                        $('#advanced_salary_bank_name2').val(response.ownerDetails.owner_bank_name);
                        $('#advanced_salary_bank_account_number2').val(response.ownerDetails.owner_bank_account_number);
                        $('#advanced_salary_ifsc_code2').val(response.ownerDetails.owner_bank_ifsc_code);
                        $('#ssbno').val(response.ownerDetails.owner_ssb_number);



                        $('.paymentmode').show();

                    } else {
                        $('#ta_employee_code').val('');
                        $('#ename').val('');
                        $('#narration').val('');
                        $('#aamount').val('');
                        $('#advanced_salary_mobile_number2').val('');
                        $('#advanced_salary_bank_name2').val('');
                        $('#advanced_salary_bank_account_number2').val('');
                        $('#advanced_salary_ifsc_code2').val('');
                        $('#ssbno').val('');
                        swal("Warning!", "Owner details not found!", "warning");
                    }
                },
                complete: function() {
                    $('.loader').hide(); // hide loader
                }
            });
        });


    });
</script>