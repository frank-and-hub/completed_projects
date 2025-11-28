<script type="text/javascript">
    $(document).ready(function() {

        $("#select_date").hover(function() {
            const EndDate = $('#create_application_date').val();
            var date1 = $('#ledger_date').val();
            $('#select_date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: date1,
                endDate: EndDate,
            })
        }).on('change', function() {
            $('#bank_id').val('');
            $('#account_id').val('');
            $('#bank_balance').val('0.00');
            $("#bank_id").trigger("change");
            $("#payment_branch").trigger("change");
            $('#branch_total_balance').val('0.00');
        });;
        $.validator.addMethod("maxpDate", function(value, element) {
            moment.defaultFormat = "DD/MM/YYYY HH:mm";
            var f1 = moment($('#ledger_date').val() + ' 00:00', moment.defaultFormat).toDate();
            var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
            var from = new Date(Date.parse(f1));
            var to = new Date(Date.parse(f2));
            var sDate = $('#ledger_date').val();
            var curDate = moment(sDate).format('DD/MM/YYYY');
            if (f2 >= f1)
                return true;
            return false;
        }, "Payment date must be grather than Ledger creation date");
        $('#payment_branch').on('change', function() {
            //alert('hi');
            var daybook = 0;
            var branch_id = $('#payment_branch').val();
            var entrydate = $('#select_date').val();
            var companyId = $('#company_id').val();
            $('#branch_total_balance').val('0.00');
            if (branch_id > 0 && daybook == 0) {
                if (entrydate == '') {
                    swal("Warning!", "Please select  payment date", "warning");
                    $('#branch_total_balance').val('0.00');
                } else {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.branchBankBalanceAmount') !!}",
                        dataType: 'JSON',
                        data: {
                            'branch_id': branch_id,
                            'daybook': daybook,
                            'entrydate': entrydate,
                            'company_id': companyId
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#branch_total_balance').val(response.balance);
                        }
                    });
                }
            }
        })
        $.validator.addMethod("decimal", function(value, element, p) {
            if (this.optional(element) || $.isNumeric(value) == true) {
                $.validator.messages.decimal = "";
                result = true;
            } else {
                $.validator.messages.decimal = "Please enter valid numeric number.";
                result = false;
            }

            return result;
        }, "");
        $.validator.addMethod("bchk_branch", function(value, element, p) {
            if (($("#amount_mode").val() == 0)) {
                total_transfer_amount = $('#total_transfer_amount').val();
                bank_balance = $('#branch_total_balance').val();
                if (parseFloat(total_transfer_amount) <= parseFloat(bank_balance)) {
                    $.validator.messages.bchk_branch = "";
                    result = true;
                } else {
                    $.validator.messages.bchk_branch = "Sufficient amount not available in branch!";
                    result = false;
                }
            } else {
                $.validator.messages.bchk_branch = "";
                result = true;
            }
            return result;
        }, "");
        $.validator.addMethod("bchk", function(value, element, p) {
            if (($("#amount_mode").val() == 2)) {
                total_transfer_amount = $('#total_transfer_amount').val();
                bank_balance = $('#bank_balance').val();
                if (parseFloat(total_transfer_amount) <= parseFloat(bank_balance)) {
                    $.validator.messages.bchk = "";
                    result = true;
                } else {
                    $.validator.messages.bchk = "Sufficient amount not available in bank account!";
                    result = false;
                }
            } else {
                $.validator.messages.bchk = "";
                result = true;
            }
            return result;
        }, "");
        $('#transfer_save').validate({
            rules: {
                select_date: {
                    required: true,
                    maxpDate: true,
                },

                amount_mode: "required",
                total_transfer_amount: {
                    required: true,
                    decimal: true,
                    bchk: function(element) {
                        if (($("#amount_mode").val() == 2)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },

                bank_balance: {
                    required: true,
                    decimal: true,
                    bchk: function(element) {
                        if (($("#amount_mode").val() == 2)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                payment_mode: {
                    required: function(element) {
                        if (($("#amount_mode").val() == 2)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                bank_id: {
                    required: true,
                },
                account_id: {
                    required: true,
                },
                cheque_id: {
                    required: function(element) {
                        if (($("#payment_mode").val() == 1)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                utr_tran: {
                    required: function(element) {
                        if (($("#payment_mode").val() == 2)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                neft_charge: {
                    required: function(element) {
                        if (($("#payment_mode").val() == 2)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    decimal: true,
                },
                advance_payment: {
                    required: true,
                    decimal: true,
                    zero1: true,
                },
                actual_transfer: {
                    required: true,
                    decimal: true,
                    chkactual_amount: true,
                    zero1: true,
                },
                advance_settel: {
                    required: true,
                    decimal: true,
                    chkadvancr: true,
                    zero1: true,
                },
                transfer_amount: {
                    required: true,
                    decimal: true,
                    zero: true,
                    chk_tra: true,
                },
                payment_branch: {
                    required: function(element) {
                        if (($("#amount_mode").val() == 0)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                branch_total_balance: {
                    required: true,
                    decimal: true,
                    bchk_branch: function(element) {
                        if (($("#amount_mode").val() == 0)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
            },
            messages: {
                select_date: {
                    required: "Please select date.",
                },
                amount_mode: "Please amount mode.",
                total_transfer_amount: {
                    required: "Please enter total amount.",
                },
                bank_balance: {
                    required: "Please enter amount.",
                },
                payment_mode: "Please select payment mode.",
                bank_id: "Please select bank.",
                account_id: "Please select account.",
                cheque_id: "Please select cheque.",
                utr_tran: "Please enter UTR number / Transaction Number.",
                neft_charge: {
                    required: "Please enter amount.",
                    decimal: "Please enter a valid date.",
                },
                transfer_amount: {
                    required: "Please enter amount.",
                    decimal: "Please enter a valid date.",
                },
                advance_settel: {
                    required: "Please enter amount.",
                    decimal: "Please enter a valid date.",
                },
                actual_transfer: {
                    required: "Please enter amount.",
                    decimal: "Please enter a valid date.",
                },
                advance_payment: {
                    required: "Please enter amount.",
                    decimal: "Please enter a valid date.",
                },
                payment_branch: "Please select branch.",
                branch_total_balance: {
                    required: "Please enter amount.",
                },
            },
            errorElement: 'label',
            errorPlacement: function(error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });



        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $('#amount_mode').change(function() {
            $('.online').hide();
            $('.cheque').hide();
            $('#payment_mode').val('');
            $('#bank_id').val('');
            $('#account_id').val('');
            $('#cheque_id').val('');
            $('#utr_tran').val('');
            $('#neft_charge').val('');
            //  $('#online_total_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 
            //  $('#total_transfer_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 
            $('#payment_branch').val('');
            $('#branch_total_balance').val('0.00');
            $('.bank').hide();
            $('.branch').hide();
            if ($('#transfer_amount').val() == 0) {
                $('#amount_mode').val('');
                $("#amount_mode").prop('disabled', true);

            } else {
                if ($(this).val() == 1) {
                    $('.bank').hide();
                } else if ($(this).val() == 2) {
                    $('.bank').show();
                } else {
                    $('.branch').show();
                }
            }

        });
        $('#payment_mode').change(function() {
            $('#bank_id').val('');
            $('#account_id').val('');
            $('#cheque_id').val('');
            $('#utr_tran').val('');
            $('#neft_charge').val('');
            // $('#online_total_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 
            // $('#total_transfer_amount').val('{{ number_format((float)$total_transfer, 2, '.', '') }}'); 
            if ($(this).val() == 1) {
                $('.online').hide();
                $('.cheque').show();
            }
            if ($(this).val() == 2) {
                $('.online').show();
                $('.cheque').hide();
            }
        });
        $(document).on('change', '#bank_id', function() {
            var bank_id = $('#bank_id').val();
            var company_id = $('#company_id').val();
            $('#bank_balance').val('0.00');
            $.ajax({
                type: "POST",
                url: "{!! route('admin.bank_account_list') !!}",
                dataType: 'JSON',
                data: {
                    'bank_id': bank_id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_id').find('option').remove();
                    $('#account_id').append('<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#account_id").append("<option value='" + value.id + "'>" +
                            value.account_no + "</option>");
                    });
                }
            });
        });
        $(document).on('change', '#account_id', function() {
            $('#bank_balance').val('0.00');
            var account_id = $('#account_id').val();
            var bank_id = $('#bank_id').val();
            var entrydate = $('#select_date').val();
            // alert(entrydate);
            if (entrydate == '') {
                $('#account_id').val(' ');
                swal("Warning!", "Please select at payment date", "warning");

            } else {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.bank_cheque_list') !!}",
                    dataType: 'JSON',
                    data: {
                        'account_id': account_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        //alert(response);

                        $('#cheque_id').find('option').remove();
                        $('#cheque_id').append(
                            '<option value="">Select cheque number</option>');
                        $.each(response.chequeListAcc, function(index, value) {
                            $("#cheque_id").append("<option value='" + value.id + "'>" +
                                value.cheque_no + "</option>");
                        });
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.bankChkbalance') !!}",
                    dataType: 'JSON',
                    data: {
                        'account_id': account_id,
                        'bank_id': bank_id,
                        'entrydate': entrydate
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // alert(response.balance);
                        $('#bank_balance').val(response.balance);

                    }
                });
            }
        });
        $(document).on('change', '#cheque_id', function() {
            $('#cheque_number').val($("#cheque_id option:selected").text());
        });
        $(document).on('keyup', '#neft_charge', function() {
            charge = $(this).val();
            total = $('#online_tran_amount').val();
            if (charge == '') {
                charge = 0;
            }
            total_salary_transfer = parseFloat(charge) + parseFloat(total);
            $('#online_total_amount').val(parseFloat(total_salary_transfer).toFixed(2));
            $('#total_transfer_amount').val(parseFloat(total_salary_transfer).toFixed(2));

        });

        $(document).on('keyup', '#transfer_amount', function() {
            total = $(this).val();
            total_salary_transfer = parseFloat(total);
            $('#total_transfer_amount').val(parseFloat(total_salary_transfer).toFixed(2));
            $('#online_tran_amount').val(parseFloat(total_salary_transfer).toFixed(2));
            $('#online_total_amount').val(parseFloat(total_salary_transfer).toFixed(2));

        });

        $("#amount_mode").trigger("change");

        $.validator.addMethod("zero", function(value, element, p) {
            if (value > 0) {
                $.validator.messages.zero = "";
                result = true;
            } else {
                $.validator.messages.zero = "Amount must be greater than 0.";
                result = false;
            }

            return result;
        }, "");
        $.validator.addMethod("zero1", function(value, element, p) {
            if (value >= 0) {
                $.validator.messages.zero1 = "";
                result = true;
            } else {
                $.validator.messages.zero1 = "Amount must be greater than 0.";
                result = false;
            }

            return result;
        }, "");
        $.validator.addMethod("chkadvancr", function(value, element, p) {

            // alert(res);
            settle_amount = $('#advance_settel').val();
            advance_payment = $('#advance_payment').val();
            if (parseFloat(advance_payment) < parseFloat(settle_amount)) {
                $.validator.messages.chkadvancr =
                    "Advance payment amount must be greater than settle amount";
                result = false;
            } else {
                $.validator.messages.chkadvancr = "";
                result = true;
            }
            return result;


        }, "");
        $.validator.addMethod("chkactual_amount", function(value, element, p) {
            actual_amount = $('#actual_transfer').val();
            transfer_amount = $('#transfer_amount').val();

            if (parseFloat(actual_amount) < parseFloat(transfer_amount)) {
                $.validator.messages.chkactual_amount =
                    "Actual transferred amount  must be greater than transfer amount";
                result = false;
            } else {
                $.validator.messages.chkactual_amount = "";
                result = true;
            }
            return result;


        }, "");
        $.validator.addMethod("chk_tra", function(value, element, p) {
            actual_amount = $('#due_amount').val();
            transfer_amount = $('#transfer_amount').val();
            if (parseFloat(transfer_amount) > parseFloat(actual_amount)) {
                $.validator.messages.chk_tra =
                    "Transfer amount  must be lower or equal to due amount";
                result = false;
            } else {
                $.validator.messages.chk_tra = "";
                result = true;
            }
            return result;


        }, "");

        $.validator.addMethod("decimal", function(value, element, p) {
            if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
                $.validator.messages.decimal = "";
                result = true;
            } else {
                $.validator.messages.decimal = "Please enter valid numeric number.";
                result = false;
            }

            return result;
                }, "");

       
    });
</script>