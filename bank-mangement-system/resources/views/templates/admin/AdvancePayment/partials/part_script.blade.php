<script type="text/javascript">
    var expense;
    "use strict"
    $(document).ready(function() {
        $('#adj_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            startDate: '01/04/2021',
        });
        $('#adj_date').hover(function() {
            var today = $('#create_application_date').val();
            var adv_date = $('#date').val();
            $('#adj_date').datepicker('setEndDate', today);
            $('#adj_date').datepicker('setStartDate', adv_date);
        });
        $('#adj_date').change(function() {
            $("#payment_mode").val('');
            $('#payment_mode').trigger('change');
            $('.utrnumber').hide();
        });
        $("#payment_mode").on('change', function() {
            if ($('#adj_date').val() == null || $('#adj_date').val() == '') {
                $(this).val('');
                swal("Warning!", "Please select  payment date", "warning");
                return false;
            }
            $('.p-mode').hide();
            $('#recived_bank').hide();
            $('#cheque_detail').hide();
            $('#branchBalance2').hide();
            if ($(this).val() == 'BANK') {
                $("#transfer_mode").val('');
                $('#tmode').show();
                $('#account_id').val('');
                $('#ssb').hide();
                $('#branchBalance2').hide();

            } else if ($(this).val() == 'CASH') {
                $('.loader').show(); // show loader
                $(".utrnumber").hide();
                $(".rtgsnumber").hide();
                $('#recived_bank').hide();
                // Get the branch current balance
                const branch_id_new = $('#branch_id').val();

                var daybook = 0;
                var branch_id = branch_id_new;
                var entrydate = $('#adj_date').val();
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
                $("#tmode").hide();
                $('#utr').hide();
                $('#rtgs').hide();
                $('#tamount').hide();
                $('#bankss').hide();
                $('#accourid').hide();
                $('#bankbalance').hide();
                $('#cheque').hide();
                $("#transfer_mode").val('');
            }
        });

        $("#transfer_mode").on('change', function() {
            if ($(this).val() == 1) {
                $('#cheque_detail').hide();
                $('.p-mode').hide();
                $('#bankss').show();
                $('#bankbalance').show();
                $('.utrnumber').show();
                $('#accourid').show();
                $('#tasubmit').removeClass('d-none');
                $('#tcheckno').hide();
                $('#cheque').hide();
                $('#bank_id').val('');
                $('#account_id').val('');
                $('#cheque_id').val('');
                $('#bank_balance').val('');
                if ($('#remaining_amount').val() > 0) {
                    $('#recived_bank').show();
                    $(".rtgsnumber").hide();
                    $(".bankbalance ").hide();
                }
            } else if ($(this).val() == 0) {
                $('.p-mode').show();
                $('#ssb').show();
                $('#recived_bank').hide();
                $("#bankss").show();
                $(".bankbalance ").hide();
                $('.utrnumber').hide();
                $('#accourid').show();
                $('.rtgsnumber').hide();
                $('#tamount').hide();
                $('#branchBalance2').hide();
                $('#recived_bank').hide();
            }
        });

        $(document).on('change', '#bank_id', function() {
            $('#cheque_id').find('option').remove();
            $('#cheque_id').append('<option value="">Select cheque number</option>');
            $('.loader').show(); // show loader
            var bank_id = $('#bank_id').val();
            $('#bank_balance').val('0.00');
            $.ajax({
                type: "POST",
                url: "{!! route('admin.bank_account_list.inactive') !!}",
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
                    $('#account_id').append(
                        '<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#account_id").append("<option data-accountNumber=" +
                            value
                            .account_no + " value=" + value.id + ">" + value
                            .account_no +
                            "</option>");
                    });
                    $('#account_id').prop("disabled", false);;
                },
                complete: function() {
                    $('.loader').hide(); // hide loader
                }
            });
        });
        $('#account_id').on('change', function() {
            $('#cheque_id').find('option').remove();
            $('#cheque_id').append('<option value="">Select cheque number</option>');
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

                var account_id = $('#account_id').val();
                var total_amount = $('#total_amount').val();
                var adj_date = $('#adj_date').val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.advancePayment.recived_cheque') !!}",
                    dataType: 'JSON',
                    data: {
                        'account_id': account_id,
                        'total_amount': total_amount,
                        'adj_date': adj_date
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#cheque_id_r').find('option').remove();
                        $('#cheque_id_r').append(
                            '<option value="">Select cheque number</option>');
                        $.each(response.chequeListAcc, function(index, value) {
                            $("#cheque_id_r").append("<option value='" + value.id +
                                "'>" + value.cheque_no + "</option>");
                        });
                    }
                });
                // $.ajax({
                //     type: "POST",
                //     url: "{!! route('bankChequeList') !!}",
                //     dataType: 'JSON',
                //     data: {
                //         'account_id': account_id
                //     },
                //     headers: {
                //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //     },
                //     success: function(response) {
                //         $('#cheque_id').find('option').remove();
                //         $('#cheque_id').append(
                //             '<option value="">Select cheque number</option>');
                //         $.each(response.chequeListAcc, function(index, value) {
                //             $("#cheque_id").append("<option value='" + value.id +
                //                 "'>" + value.cheque_no + "</option>");
                //         });
                //     },
                //     complete: function() {
                //         $('.loader').hide(); // hide loader
                //     }
                // });
                // $.ajax({
                //     type: "POST",
                //     url: "{!! route('admin.bankChkbalance') !!}",
                //     dataType: 'JSON',
                //     data: {
                //         'account_id': account_id,
                //         'bank_id': bank_id,
                //         'company_id': company_id,
                //         'entrydate': entrydate
                //     },
                //     headers: {
                //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //     },
                //     success: function(response) {
                //         $('#bank_balance').val(response.balance);
                //     },
                //     complete: function() {
                //         $('.loader').hide(); // hide loader
                //     }

                // });
            }
        });
        $('#total_amount').keyup(function() {
            sum = Number($(this).val());
            var emp_amount = $('#amount').val();
            $('#remaining_amount').val(emp_amount - sum);
        });
        $.validator.addMethod('lettersOnly', function(value, e) {
            return this.optional(e) || /^[a-z ]+$/i.test(value);
        }, "Please Enter Letter Only");

        $.validator.addMethod("approveAmountLessThanTotal", function(value, element, p) {
            const approveAmount = $('[name=approveAmount]').val();
            const total = $('[name=total_amount]').val();
            if (parseFloat(approveAmount) < parseFloat(total)) {
                $.validator.messages.fa_code =
                    "The approved amount must be less than or equal to the total amount.";
                result = false;
            } else {
                result = true;
            }
            return result;
        }, "");
        $('form').validate({ // initialize the plugin

            rules: {
                branch_id: "required",

                adjdate: {
                    required: function() {
                        if ((!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                account_head: {
                    required: function() {
                        if ((!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                description: {
                    required: function() {
                        if ((!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                amount: {
                    required: function() {
                        if ((!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    min: function() {
                        if ((!$("#switch").is(":checked"))) {
                            return 1;
                        } else {
                            return false;
                        }
                    },
                    number: true,
                    // zero: true,
                },
                total_amount: {
                    required: true,
                    approveAmountLessThanTotal: true,
                    number: true,
                    min: 1,
                },
                cheque_id_r: "required",
                r_account_id: {
                    required: true,
                    number: true,
                    minlength: 10
                },
                r_bank_name: {
                    required: true,
                },
                account_id: "required",
                bank_id: "required",
                payment_mode: "required",
                transfer_mode: "required",
                utr_tran: "required",
            },

            messages: {
                date: {
                    required: "Please  select Date.",
                },
                payment_mode: {
                    required: "Please  select a payment mode.",
                },
                transfer_mode: {
                    required: "Please  select a transfer mode.",
                },
                utr_tran: {
                    required: "Please  enter utr/transaction refrence no.",
                },
                bank_id: {
                    required: "Please  select a Bank.",
                },
                account_id: {
                    required: "Please  select a Bank's account.",
                },
                r_account_id: {
                    required: "Please  enter account number.",
                },
                r_bank_name: {
                    required: "Please  enter bank name.",
                },
                total_amount: {
                    approveAmountLessThanTotal: "Total Amount must be less than or equal to the TA Amount.",
                    required: "Please enter amount.",
                }
            },

            submitHandler: function(form) {
                const formData = new FormData(form);
                $('#submit').prop('disabled', true);
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.advancepartPayment.save') !!}",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        // Handle the Ajax response
                        // console.log(response.message);
                        if (response.message == 'success') {
                            swal({
                                title: 'Success!',
                                text: 'Form submitted successfully!',
                                type: 'success'
                            });
                            window.location.href =
                                "{{ route('admin.advancePayment.paymentList') }}";
                        } else if (response.message == 'verror') {
                            swal({
                                title: 'warning!',
                                text: response.msg,
                                type: 'warning'
                            });
                        } else {
                            swal({
                                title: 'Error!',
                                text: 'Something Went Wrong!',
                                type: 'error'
                            });
                            // window.location.href = "{{ route('admin.advancePayment.paymentList') }}";
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle Ajax errors
                        $('#submit').prop('disabled', false);
                        console.error(error);
                        return false;


                    }

                });


            }

        });
    });
</script>
