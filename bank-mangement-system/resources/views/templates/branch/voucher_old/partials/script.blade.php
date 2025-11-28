<script type="text/javascript">
$(document).ready(function() {
    var date = new Date();
    $('#head').on('change', function() {
        $('#payment_mode').val('');
        $('#director_id').val(' ');
        $('#shareholder_id').val('');
        $('#eli_loan_id').val('');
        $('#emp_code').val('');
        $('#bank_id').val('');
        $('#bank_account').val('');
        $('#bank_balance').val('');
        $("#bank_id").trigger("change");
        $("#payment_mode").trigger("change");
        var headId = $('option:selected', this).val();
        $('#payment_mode option[value=1]').show();
        $('#payment_mode option[value=2]').show();
        $('#eli_loan').hide();
        if (headId == 19) {
            $('#director').show();
            $('.bank').hide();
            $('.penal').hide();
            $('#shareholder').hide();
            $.ajax({
                url: "{!!route('branch.account_head_get_branch')!!}",
                type: "POST",
                dataType: 'JSON',
                data: {
                    'headId': headId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#director_id').find('option').remove();
                    $('#director_id').append(
                        '<option value="">--- Select Director ---</option>');
                    $.each(response.data, function(index, value) {
                        $("#director_id").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            })
        } else if (headId == 15) {
            $('#shareholder').show();
            $('#director').hide();
            $('.bank').hide();
            $('.penal').hide();
            $.ajax({
                url: "{!!route('branch.account_head_get_branch')!!}",
                type: "POST",
                dataType: 'JSON',
                data: {
                    'headId': headId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#shareholder_id').find('option').remove();
                    $('#shareholder_id').append(
                        '<option value="">--- Select Shareholder ---</option>');
                    $.each(response.data, function(index, value) {
                        $("#shareholder_id").append("<option value='" + value
                            .head_id + "'>" + value.sub_head + "</option>");
                    });
                }
            })
        } else if (headId == 32) {
            $('.penal').show();
            $('#shareholder').hide();
            $('#director').hide();
            $('.bank').hide();
        } else if (headId == 27) {
            $('.bank').show();
            $('#shareholder').hide();
            $('#director').hide();
            $('.penal').hide();
            $('#payment_mode option[value=1]').hide();
            $('#payment_mode option[value=2]').hide();
        } else if (headId == 96) {
            $('#eli_loan').show();
            $('#shareholder').hide();
            $('#director').hide();
            $('.penal').hide();
            $('.bank').hide();
            $.ajax({
                url: "{!!route('branch.account_head_get_branch')!!}",
                type: "POST",
                dataType: 'JSON',
                data: {
                    'headId': headId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#eli_loan_id').find('option').remove();
                    $('#eli_loan_id').append(
                        '<option value="">---Select Eli Loan---</option>');
                    $.each(response.data, function(index, value) {
                        $("#eli_loan_id").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            })
        }
    })
    $('#payment_mode').on('change', function() {
        $('#utr_date').datepicker({
            format: "dd/mm/yyyy",
            endDateHighlight: true,
            endDate: $('.create_application_date').val(),
            autoclose: true
        });
        $('#branch_total_balance').val('');
        $('#daybook').val(0);
        $('#cheque_no').val();
        $('#online_bank').val('');
        $('#online_bank_ac').val('');
        $('#utr_date').val('');
        $('#utr_no').val('');
        $('#transaction_bank').val('');
        $('#transaction_bank_ac').val('');
        $('.cheque').hide();
        $('.payment_mode_cash').hide();
        $('.payment_mode_cheque').hide();
        $('.payment_mode_online').hide();
        var mode = $('option:selected', this).val();
        //alert(mode);
        if (mode == 0 && mode != '') {
            $('.payment_mode_cash').show();
            $('.payment_mode_cheque').hide();
            $('.payment_mode_online').hide();
            $("#daybook").trigger("change");
        } else if (mode == 2) {
            $('.payment_mode_cheque').hide();
            $('.payment_mode_cash').hide();
            $('.payment_mode_online').show();
        } else if (mode == 1) {
            $('.payment_mode_cheque').show();
            $('.payment_mode_cash').hide();
            $('.payment_mode_online').hide();
            $.ajax({
                type: "POST",
                url: "{!!route('branch.approve_recived_cheque_list')!!}",
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#cheque_no').find('option').remove();
                    $('#cheque_no').append(
                    '<option value="">Select cheque number</option>');
                    $.each(response.cheque, function(index, value) {
                        $("#cheque_no").append("<option value='" + value.id + "'>" +
                            value.cheque_no + "  ( " + parseFloat(value.amount)
                            .toFixed(2) + ")</option>");
                    });
                }
            });
        }
    })
    $(document).on('change', '#cheque_no', function() {
        $('.cheque').hide();
        $('#rd_cheque_no').val('');
        $('#rd_branch_name').val('');
        $('#rd_bank_name').val('');
        $('#rd_cheque_date').val('');
        $('#cheque-amt').val('');
        var cheque_no = $('#cheque_no').val();
        $.ajax({
            type: "POST",
            url: "{!!route('branch.approve_cheque_detail')!!}",
            dataType: 'JSON',
            data: {
                'cheque_id': cheque_no
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#cheque_number').val(response.cheque_no);
                $('#cheque_party_bank').val(response.bank_name);
                // $('#rd_branch_name').val(response.branch_name);
                $('#cheque_deposit_date').val(response.cheque_deposite_date);
                $('#cheque_amount').val(parseFloat(response.amount).toFixed(2));
                $('#cheque_deposit_bank').val(response.deposit_bank_name);
                $('#cheque_deposit_bank_ac').val(response.deposite_bank_acc);
                $('#cheque_party_name').val(response.user_name);
                $('#cheque_party_bank_ac').val(response.bank_ac);
                $('.cheque').show();
            }
        });
    });
    $('#branch_id').on('change', function() {
        var branch_code = $('option:selected', this).attr('data-value');
        $('#branch_code').val(branch_code);
        $("#daybook").trigger("change");
    });
    $('#bank_id').on('change', function() {
        $('#bank_balance').val('0.00');
        var bank_id = $(this).val();
        $.ajax({
            url: "{!!route('branch.bank_account_list')!!}",
            type: "POST",
            dataType: 'JSON',
            data: {
                'bank_id': bank_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#bank_account').find('option').remove();
                $('#bank_account').append(
                '<option value="">Select account number</option>');
                $.each(response.account, function(index, value) {
                    $("#bank_account").append("<option value='" + value.id + "'>" +
                        value.account_no + "</option>");
                });
            }
        })
    })
    $('#bank_account').on('change', function() {
        var bank_id = $('#bank_id').val();
        var account_id = $('#bank_account').val();
        var entrydate = $('#created_at').val();
        $('#bank_balance').val('0.00');
        $.ajax({
            type: "POST",
            url: "{!!route('branch.bankChkbalanceBranch')!!}",
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
    })
    $('#daybook').on('change', function() {
        var daybook = $('#daybook').val();
        var branch_id = $('#branch_id').val();
        var entrydate = $('#created_at').val();
        $('#branch_total_balance').val('0.00');
        if (branch_id > 0) {
            /*
			$.ajax({
                type: "POST",
                url: "{!! route('branch.branchChkbalanceBranch') !!}",
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
                    // alert(response.balance);
                    $('#branch_total_balance').val(response.balance);
                }
            });
			*/			
			$.ajax({
				type: "POST", 
				url: "{!! route('branch.branchBankBalanceAmount') !!}",
				dataType: 'JSON',
				data: {'branch_id':branch_id,'entrydate':entrydate},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) { 
					$('#branch_total_balance').val(response.balance);  
				}
			});
        }
    })
    $('#online_bank').on('change', function() {
        var bank_id = $(this).val();
        $.ajax({
            url: "{!!route('branch.bank_account_list')!!}",
            type: "POST",
            dataType: 'JSON',
            data: {
                'bank_id': bank_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#online_bank_ac').find('option').remove();
                $('#online_bank_ac').append(
                    '<option value="">Select account number</option>');
                $.each(response.account, function(index, value) {
                    $("#online_bank_ac").append("<option value='" + value.id +
                        "'>" + value.account_no + "</option>");
                });
            }
        })
    })
    $('#emp_code').on('change', function() {
        $('#emp_id').val('');
        $('#emp_name').val('');
        var emp_code = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!!route('branch.empCheck')!!}",
            data: {
                employee_code: emp_code
            },
            dataType: "JSON",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.resCount == 1) {
                    $('#emp_code').val(response.emp.employee_code);
                    $('#emp_id').val(response.emp.id);
                    $('#emp_name').val(response.emp.employee_name);
                } else if (response.resCount == 2) {
                    swal("Error!", " Employee Inactive", "error");
                    $('#emp_code').val('');
                    $('#emp_id').val('');
                    $('#emp_name').val('');
                } else {
                    swal("Error!", " Employee code not found!", "error");
                    $('#emp_code').val('');
                    $('#emp_id').val('');
                    $('#emp_name').val('');
                }
            }
        })
    })
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
    $.validator.addMethod("chkbank", function(value, element, p) {
        if ($("#head").val() == 27) {
            if (parseFloat($('#bank_balance').val()) >= parseFloat($('#amount').val())) {
                $.validator.messages.chkbank = "";
                result = true;
            } else {
                $.validator.messages.chkbank =
                    "Bank available balance must be grather than or equal to  received amount";
                result = false;
            }
        } else {
            $.validator.messages.chkbank = "";
            result = true;
        }
        return result;
    }, "");
    $.validator.addMethod("chkbankCheque", function(value, element, p) {
        if ($("#payment_mode").val() == 1) {
            if (parseFloat($('#cheque_amount').val()) >= parseFloat($('#amount').val())) {
                $.validator.messages.chkbankCheque = "";
                result = true;
            } else {
                $.validator.messages.chkbankCheque =
                    "Cheque amount must be grather than or equal to  received amount";
                result = false;
            }
        } else {
            $.validator.messages.chkbankCheque = "";
            result = true;
        }
        return result;
    }, "");
    $('#voucher_save').validate({
        rules: {
            branch_id: "required",
            date: "required",
            head: "required",
            director_id: {
                required: function(element) {
                    if (($("#head").val() == 19)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            shareholder_id: {
                required: function(element) {
                    if (($("#head").val() == 15)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            bank_id: {
                required: function(element) {
                    if (($("#head").val() == 27)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            bank_account: {
                required: function(element) {
                    if (($("#head").val() == 27)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            emp_code: {
                required: function(element) {
                    if (($("#head").val() == 32)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            emp_name: {
                required: function(element) {
                    if (($("#head").val() == 32)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            eli_loan_id: {
                required: function(element) {
                    if (($("#head").val() == 96)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            particular: {
                required: true,
            },
            payment_mode: {
                required: true,
            },
            daybook: {
                required: function(element) {
                    if (($("#payment_mode").val() == 0)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            branch_total_balance: {
                required: function(element) {
                    if (($("#payment_mode").val() == 0)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            online_bank: {
                required: function(element) {
                    if (($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            online_bank_ac: {
                required: function(element) {
                    if (($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            utr_date: {
                required: function(element) {
                    if (($("#payment_mode").val() == 2)) {
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
                number: true,
            },
            transaction_bank: {
                required: function(element) {
                    if (($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            transaction_bank_ac: {
                required: function(element) {
                    if (($("#payment_mode").val() == 2)) {
                        return true;
                    } else {
                        return false;
                    }
                },
                number: true,
                minlength: 8,
                maxlength: 16
            },
            cheque_no: {
                required: function(element) {
                    if (($("#payment_mode").val() == 1)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            amount: {
                required: true,
                decimal: true,
                chkbank: function(element) {
                    if (($("#head").val() == 27)) {
                        return true;
                    } else {
                        return false;
                    }
                },
                chkbankCheque: function(element) {
                    if (($("#payment_mode").val() == 1)) {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
        },
        messages: {
            branch_id: {
                required: "Please select branch",
            },
            date: {
                required: "Please select date",
            },
            head: "Please select account head",
            director_id: {
                required: "Please select director",
            },
            shareholder_id: {
                required: "Please select shareholder",
            },
            bank_id: {
                required: "Please select bank",
            },
            bank_account: {
                required: "Please select bank account",
            },
            emp_code: {
                required: "Please enter employee code",
            },
            emp_name: {
                required: "Please enter employee name",
            },
            eli_loan_id: {
                required: "Please select eli loan",
            },
            particular: {
                required: "Please enter particular",
            },
            payment_mode: {
                required: "Please select mode",
            },
            daybook: {
                required: "Please select daybook",
            },
            branch_total_balance: {
                required: "Please enter branch balance",
            },
            online_bank: {
                required: "Please select bank",
            },
            online_bank_ac: {
                required: "Please select bank account",
            },
            utr_date: {
                required: "Please select date",
            },
            utr_no: {
                required: "Please enter utr/ transaction no.",
            },
            transaction_bank: {
                required: "Please enter transaction bank name",
            },
            transaction_bank_ac: {
                required: "Please enter transaction bank account no.",
                minlength: 'Please enter minimum 8 digit number',
                maxlength: 'Please enter maximum 16 digit number',
            },
            cheque_no: {
                required: "Please select cheque no.",
            },
            amount: {
                required: "Please enter amount",
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
});
</script>