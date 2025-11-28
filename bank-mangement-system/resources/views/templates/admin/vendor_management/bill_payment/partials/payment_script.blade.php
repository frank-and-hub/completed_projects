<script type="text/javascript">
//only for design purposes start
$('#company_id').parent('.col-lg-12').siblings('.col-lg-12').addClass('col-lg-3').removeClass('col-lg-12');
$('#branch').parent().parent('.col-lg-12').siblings('.col-lg-12').addClass('col-lg-3').removeClass('col-lg-12');
$('#company_id').parent('.col-lg-12').addClass('col-lg-9').removeClass('col-lg-12');
$('#branch').parent().parent('.col-lg-12').addClass('col-lg-9').removeClass('col-lg-12');
//only for design purposes end
$(document).ready(function() {
    $('#company_id').prop('disabled', true);
    $('#pay_list').on("keyup", ".t_amount", function() {
        // $(this).valid();
        var amount = $('#amount').val();
        var sum = 0;
        $('.t_amount').each(function() {
            if ($(this).val() == 0 || $(this).val() > 0) {
                sum += Number($(this).val());
            }
        });
        $('#total_amount').val(parseFloat(sum).toFixed(2));

        $('#amount_used').val(parseFloat(sum).toFixed(2));
        amount_excess_calculate();
        var total_amount = $('#total_amount').val();
        if (amount == '') {
            $('#amount').val(total_amount);
        }
        if (parseFloat(total_amount) > parseFloat(amount)) {
            $('#amount').val(total_amount);
            $('#amount_paid').val(total_amount);
        }
    });
    $('#pay_list').on("blur",".t_amount", function() {
        //  $(this).valid();
        if ($(this).valid()) {
            $(this).siblings('.error').remove();
        }
    });
    $("#amount").on('keyup', function() {
        var sum = 0;
        if ($(this).val() == 0 || $(this).val() > 0) {
            sum += Number($(this).val());
        }
        $('#amount_paid').val(parseFloat(sum).toFixed(2));
        amount_excess_calculate();
    });
    $("#payment_date").hover(function() {
        var date = $('#create_application_date').val();
        var startDate = $('#last_date').val();
        $('#payment_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
            orientation: "bottom",
            startDate: startDate,
            endDate: date,
        })
    })
    $('#payment_date').on('change', function() {
        $('#payment_mode').val('');
        $('#branch').val('');
        $('#branch_balance').val('');
        $('#bank_id').val('');
        $('#bank_ac').val('');
        $('#bank_balance').val('');
        $('#utr_no').val('');
        $('#neft_charge').val('');
        $('#cheque_id').val('');
        $("#payment_mode").trigger("change");
    });
    $('#branch').on('change', function() {
        var daybook = 0;
        var branch_id = $('#branch').val();
        var entrydate = $('#payment_date').val();
        var company_id = $('#company_id').val();
        $('#branch_balance').val('0.00');
        if (entrydate == '') {
            swal("Warning!", "Please select  payment date", "warning");
            $('#branch_balance').val('0.00');
            return false;
        }
        if (branch_id > 0) {
            $.ajax({
                type: "POST",
                url: "{!! route('admin.branchBankBalanceAmount') !!}",
                dataType: 'JSON',
                data: {
                    'branch_id': branch_id,
                    'company_id': company_id,
                    'entrydate': entrydate
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#branch_balance').val(response.balance);
                }
            });
        }
    })
    $('#payment_mode').on('change', function() {
        $(".t_amount").prop("readonly", false);
        $('.cash').hide();
        $('.bank').hide();
        $('.online').hide();
        $('.cheque').hide();
        $('.eli_amount').hide();
        $('#branch').val('');
        $('#branch_balance').val('');
        $('#bank_id').val('');
        $('#bank_ac').val('');
        $('#bank_balance').val('');
        $('#utr_no').val('');
        $('#neft_charge').val('');
        $('#cheque_id').val('');
        paymentMode = $('#payment_mode').val();
        if (paymentMode == 1) {
            $('.bank').show();
            $('.cheque').show();
            let companyId = $('#company_id option:selected').val();
            $('#bank_id option[data-companyId="' + companyId + '"]').show();
        }
        if (paymentMode == 2) {
            $('.bank').show();
            $('.online').show();
            let companyId = $('#company_id option:selected').val();
            $('#bank_id option[data-companyId="' + companyId + '"]').show();
        }
        if (paymentMode == 3) {
            var entrydate = $('#payment_date').val();
            var company_id = $('#company_id option:selected').val();
            $('#eli_balance').val('0.00');
            if (entrydate == '') {
                swal("Warning!", "Please select  payment date", "warning");
                $('#eli_balance').val('0.00');
                return false;
            }
            $('.eli_amount').show();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.eli_amount_get') !!}",
                dataType: 'JSON',
                data: {
                    'entrydate': entrydate,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#eli_balance').val(response.balance);
                }
            });
        }
        if (paymentMode == 0 && paymentMode != '') {
            $('.cash').show();
        }
    })

    $('#bank_id').on('change', function(selected_account) {
        var bank_id = $(this).val();
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

                $('#bank_ac').find('option').remove();
                $('#bank_ac').append('<option value="">Select account number</option>');
                $.each(response.account, function(index, value) {
                    $("#bank_ac").append("<option value='" + value.id + "'>" +
                        value.account_no + "</option>");
                });
            }
        });
    })

    $('#bank_ac').on('change', function() {
        var bank_id = $('#bank_id').val();
        var account_id = $('#bank_ac').val();
        var entrydate = $('#payment_date').val();
        var company_id = $('#company_id option:selected').val();
        $('#bank_balance').val('0.00');

        $('#cheque_id').val('');
        if (entrydate == '') {
            swal("Warning!", "Please select  payment date", "warning");
            $('#bank_ac').val('');
            $('#bank_balance').val('0.00');
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{!! route('admin.bankChkbalance') !!}",
            dataType: 'JSON',
            data: {
                'account_id': account_id,
                'bank_id': bank_id,
                'entrydate': entrydate,
                'company_id': company_id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#bank_balance').val(response.balance);
            }
        });

        $.ajax({
            type: "POST",
            url: "{!! route('admin.bank_cheque_list') !!}",
            dataType: 'JSON',
            data: {
                'account_id': account_id,
                'company_id': company_id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#cheque_id').find('option').remove();
                $('#cheque_id').append(
                    '<option value="">Select cheque number</option>');
                $.each(response.chequeListAcc, function(index, value) {
                    $("#cheque_id").append("<option value='" + value.id +
                        "'>" + value.cheque_no + "</option>");
                });
            }
        });

    })


    $(document).on('change', '#cheque_id', function() {
        $('#cheque_number').val($("#cheque_id option:selected").text());
    });
    ///-----------------------------------------------

    $.validator.addMethod("dateDdMm", function(value, element, p) {
        if (this.optional(element) ||
            /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
            $.validator.messages.dateDdMm = "";
            result = true;
        } else {
            $.validator.messages.dateDdMm = "Please enter valid date.";
            result = false;
        }

        return result;
    }, "");


    $.validator.addMethod("zero", function(value, element, p) {
        if (parseFloat(value) >= 0) {
            $.validator.messages.zero = "";
            result = true;
        } else {
            $.validator.messages.zero = "Amount must be greater than or equal to 0.";
            result = false;
        }
        return result;
    }, "");

    $.validator.addMethod("zero1", function(value, element, p) {
        if (parseFloat(value) > 0) {
            $.validator.messages.zero1 = "";
            result = true;
        } else {
            $.validator.messages.zero1 = "Amount must be greater than 0.";
            result = false;
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

    $.validator.addMethod("chkamount_excess", function(value, element, p) {

        amount = $('#amount').val();
        amount_used = $('#amount_used').val();
        if (amount_used == '') {
            amount_used = 0.00;
        }
        if ((parseFloat(amount)) >= (parseFloat(amount_used))) {
            $.validator.messages.chkamount_excess = "";
            result = true;
        } else {
            $.validator.messages.chkamount_excess =
                "The amount entered for individual bill(s) exceeds the total payment made to the vendor. Please check and try again.";
            result = false;
        }
        return result;
    }, "");

    $.validator.addMethod("chkBranch", function(value, element, p) {
        if ($("#payment_mode").val() == 0 && $("#payment_mode").val() != '') {
            if (parseFloat($('#branch_balance').val()) >= parseFloat($('#amount').val())) {
                $.validator.messages.chkBranch = "";
                result = true;
            } else {
                $.validator.messages.chkBranch =
                    "Branch total balance must be grather than or equal to  amount";
                result = false;
            }
        } else {
            $.validator.messages.chkBranch = "";
            result = true;
        }
        return result;
    }, "");


    $.validator.addMethod("neft", function(value, element, p) {
        if ($("#payment_mode").val() == 2) {
            a = parseFloat($('#neft_charge').val()) + parseFloat($('#amount').val());
            if (parseFloat($('#bank_balance').val()) >= parseFloat(a)) {
                $.validator.messages.neft = "";
                result = true;
            } else {
                $.validator.messages.neft =
                    "Bank available balance must be grather than or equal to  sum of amount or NEFT charge";
                result = false;
            }
        } else {
            $.validator.messages.neft = "";
            result = true;
        }
        return result;
    }, "");

    $.validator.addMethod("billAmountChk", function(value, element, p) {
        id = $(element).data("rowid");
        a = $('#bill_balance' + id).val();
        b = $('#pay_amount' + id).val();
        if (b > 0) {
            //alert(b)
            if (parseFloat(a) >= parseFloat(b)) {
                $.validator.messages.billAmountChk = "";
                result = true;
            } else {
                $.validator.messages.billAmountChk = "Payment must be less than or equal to amount due";
                result = false;
            }
        } else {
            $.validator.messages.billAmountChk = "";
            result = true;
        }

        return result;
    }, "");

    $.validator.addClassRules({
        t_amount: {
            decimal: true,
            billAmountChk: true
        }
    });

    // Initializing the validator separately (assuming the form has an ID of 'myForm')
    $("#myForm").validate({
        submitHandler: function(form) {
            return false;
        }
    });

    $.validator.addMethod("chkBranchLimit", function(value, element, p) {
        if ($("#payment_mode").val() == 0 && $("#payment_mode").val() != '') {
            if (parseFloat($('#amount').val()) <= 10000) {
                $.validator.messages.chkBranchLimit = "";
                result = true;
            } else {
                $.validator.messages.chkBranchLimit =
                    "Cash Limit is 10000.00Rs. You can not pay more than 10000.00Rs.";
                result = false;
            }
        } else {
            $.validator.messages.chkBranchLimit = "";
            result = true;
        }
        return result;
    }, "");
    $.validator.addMethod("ChkEliBalance", function(value, element, p) {
        if ($("#payment_mode").val() == 3) {
            if (parseFloat($('#eli_balance').val()) >= parseFloat($('#amount').val())) {
                $.validator.messages.ChkEliBalance = "";
                result = true;
            } else {
                $.validator.messages.ChkEliBalance =
                    "Amount must be less than or equal to Eli Balance ";
                result = false;
            }
        } else {
            $.validator.messages.ChkEliBalance = "";
            result = true;
        }
        return result;
    }, "");
    $('#vendor_payment').validate({
        ignore: [],
        rules: {
           
            payment_mode: "required",
            ref_no: "required",
            amount_paid: "required",
            payment_date: {
                required: true,
                dateDdMm: true,
            },
            amount: {
                required: true,
                decimal: true,
                zero1: true,
                chkamount_excess: true,

                neft: function(element) {
                    if (($("#payment_mode").val() == 1)) {
                        return true;
                    } else {
                        return false;
                    }
                },
                chkBranch: function(element) {
                    if (($("#payment_mode").val() == 0)) {
                        return true;
                    } else {
                        return false;
                    }
                },

                chkBranchLimit: function(element) {
                    if (($("#payment_mode").val() == 0)) {
                        return true;
                    } else {
                        return false;
                    }
                },
                ChkEliBalance: function(element) {
                    if (($("#payment_mode").val() == 3)) {
                        return true;
                    } else {
                        return false;
                    }
                },

            },
            amount_excess: {
                required: true,
                decimal: true,
                zero1: true,
            },
            branch_id: {
                required: function(element) {
                    if (($("#payment_mode").val() == 0)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            branch_balance: {
                required: function(element) {
                    if (($("#payment_mode").val() == 0)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            bank_id: {
                required: function(element) {
                    if (($("#payment_mode").val() == 1) || ($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            bank_ac: {
                required: function(element) {
                    if (($("#payment_mode").val() == 1) || ($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            bank_balance: {
                required: function(element) {
                    if (($("#payment_mode").val() == 1) || ($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
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
            utr_no: {
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
            },
            
        },
        messages: {
            payment_mode: "Please select payment mode.",
            ref_no: "Please enter reference.",
            amount_paid: "Please enter amount.",
            payment_date: "Please select date.",
            amount: {
                required: "Please enter amount.",
            },
            amount_excess: {
                required: "Please enter amount.",
            },
            branch_id: {
                "required": "Please select branch.",
            },
            bank_id: {
                "required": "Please select bank.",
            },
            bank_ac: {
                "required": "Please select bank account.",
            },
            utr_no: {
                "required": "Please enter utr no.",
            },
            neft_charge: {
                "required": "Please enter neft charge.",
            },
            branch_balance: {
                "required": "Please enter branch balance.",
            },
            bank_balance: {
                "required": "Please enter bank balance.",
            },
            cheque_id: {
                "required": "Please select cheque.",
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
        },
        submitHandler: function() {
            $('button[type="submit"]').prop('disabled', true);
            return true;
        }

    });
    $('#branch').change(function() {
        var branch_id = $('#branch').val();
        $(this).closest("tr").find(".t_amount").prop("readonly", false);
        $(".billDetailaget").each(function() {
            if ($(this).closest("tr").find(".bill_branch_id").val() != branch_id) {
                $(this).closest("tr").find(".t_amount").prop("readonly", true);
                $(this).closest("tr").find(".t_amount").val(0.00);
            } else {
                $(this).closest("tr").find(".t_amount").prop("readonly", false);
            }
        });
        var sum = 0;
        $('.t_amount').each(function() {
            if ($(this).val() == 0 || $(this).val() > 0) {
                sum += Number($(this).val());
            }
        });
        $('#total_amount').val(parseFloat(sum).toFixed(2));

        $('#amount_used').val(parseFloat(sum).toFixed(2));
        amount_excess_calculate();
    });

    $('#payment_date').change(function() {
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var f2 = moment($('#payment_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var payment_date = new Date(Date.parse(f2));
        $(".billDetailaget").each(function() {
            var f1 = moment($(this).closest("tr").find(".bill_date").val() + ' 00:00',
                moment.defaultFormat).toDate();
            var billDate = new Date(Date.parse(f1));
            if (billDate > payment_date) {
                $(this).closest("tr").find(".t_amount").prop("readonly", true);
                $(this).closest("tr").find(".t_amount").val(0.00);
            } else {
                $(this).closest("tr").find(".t_amount").prop("readonly", false);
            }
        });
        var sum = 0;
        $('.t_amount').each(function() {
            if ($(this).val() == 0 || $(this).val() > 0) {
                sum += Number($(this).val());
            }
        });
        $('#total_amount').val(parseFloat(sum).toFixed(2));

        $('#amount_used').val(parseFloat(sum).toFixed(2));
        amount_excess_calculate();
    });
})


function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
        return false;
    return true;
}

function amount_excess_calculate() {
    var amount_paid = $('#amount_paid').val();
    var amount_used = $('#amount_used').val();
    if (amount_paid == '') {
        amount_paid = 0.00;
    }
    if (amount_used == '') {
        amount_used = 0.00;
    }
    var amount_excess_show = parseFloat(amount_paid) - parseFloat(amount_used);
    var amount_excess = parseFloat(amount_excess_show).toFixed(2)
    $('#amount_excess_show').text('Rs. ' + amount_excess_show);
    $('#amount_excess').val(amount_excess);
}
</script>