<script type="text/javascript">
    $(document).ready(function() {
        var date = $('#create_application_date').val();
        $('#date').on('change', function() {
            $('#payment_mode').val('');
            $('#bank_account').val('');
            $('#bank_balance').val('0.00');
            $("#payment_mode").trigger("change");
            $("#bank_account").trigger("change");
        });
        $('#head').on('change', function() {
            $('#payment_mode').val('');
            $('#director_id').val(' ');
            $('#shareholder_id').val('');
            $('#eli_loan_id').val('');
            $('#emp_code').val('');
            $('#emp_date').val('');
            $('#bank_id').val('');
            $('#bank_account').val('');
            $('#bank_balance').val('');
            $('#bank_register_date').val('');
            $('#register_date_shareholder').val('');
            $('#register_date_director').val('');
            $("#bank_id").trigger("change");
            $("#payment_mode").trigger("change");
            $('.payment-mode-type').show();
            $("#amount").attr("readonly", false);
            $("#amount").val('');
            var headId = $('option:selected', this).val();
            $('#payment_mode option[value=1]').show();
            $('#payment_mode option[value=2]').show();
            $('.eli_loan').hide();
            if (headId == 19) {
                $('.member-section').hide();
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.director').show();
                $('.bank').hide();
                $('.penal').hide();
                $('.shareholder').hide();
                $('.indirect_sub_head').hide();
                $.ajax({
                    url: "{!! route('admin.account_head_get') !!}",
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
                            $("#director_id").append("<option value='" + value
                                .head_id + "'>" + value.sub_head + "</option>");
                        });
                    }
                })
            } else if (headId == 15) {
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.member-section').hide();
                $('.shareholder').show();
                $('.director').hide();
                $('.bank').hide();
                $('.penal').hide();
                $('.indirect_sub_head').hide();
                $.ajax({
                    url: "{!! route('admin.account_head_get') !!}",
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
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.member-section').hide();
                $('.penal').show();
                $('.shareholder').hide();
                $('.director').hide();
                $('.bank').hide();
                $('.receved_mode').hide();
                $('.payment').show();
                $('.indirect_sub_head').hide();

            } else if (headId == 27) {
                $('.member-section').hide();
                $('.bank').show();
                $('.shareholder').hide();
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.director').hide();
                $('.penal').hide();
                $('#payment_mode option[value=1]').hide();
                $('#payment_mode option[value=2]').hide();
                $('.indirect_sub_head').hide();
            } else if (headId == 86) {
                $('.member-section').hide();
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.payment').show();
                let companyIdd = $('#company_id option:selected').val();
                let options = $('#sub_head option.sub_headd');
                $.each(options, function(i, v) {
                    if (v.getAttribute("data-company").includes(Number(companyIdd))) {
                        v.style.display = "block";
                    } else {
                        v.style.display = "none";
                    }
                });
                $('.indirect_sub_head').show();
                $('.shareholder').hide();
                $('.director').hide();
                $('.penal').hide();
                $('#payment_mode option[value=1]').show();
                $('#payment_mode option[value=2]').show();
            } else if (headId == 96) {
                $('.member-section').hide();
                $('.eli_loan').show();
                $('.shareholder').hide();
                $('.director').hide();
                $('.penal').hide();
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.bank').hide();
                $('.indirect_sub_head').hide();
                $.ajax({
                    url: "{!! route('admin.account_head_get') !!}",
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
                            $("#eli_loan_id").append("<option value='" + value
                                .head_id + "'>" + value.sub_head + "</option>");
                        });
                    }
                })
            } else if (headId == 122) {
                let companyId = $('#company_id').val();
                let branchId = $('#branch').val();
                let create_application_date = $('#create_application_date').val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.voucher.checkGstData') !!}",
                    data: {
                        company_id: companyId,
                        branch_id: branchId,
                        create_application_date: create_application_date,
                    },
                    dataType: "JSON",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let amount = (response.gst_percentage * 50) / 100;
                        $('.gst_charge').show();
                        if (response.IntraState == true) {
                            $('#cgst_stationary_charge').show();
                            $('.igst_charge').hide();
                            $('#sgst_stationary_charge').show();
                            $('#cgst_stationary_charge').val(amount / 2);
                            $('#sgst_stationary_charge').val(amount / 2);;
                            $('#igst_stationary_charge').hide();
                            $('.cgst_charge').show();
                            $('.gst_charge').show();
                        } else {
                            $('.cgst_charge').hide();
                            $('#cgst_stationary_charge').hide();
                            $('#sgst_stationary_charge').hide();
                            $('#igst_stationary_charge').val(amount);
                            $('#igst_stationary_charge').hide();
                            $('.igst_charge').hide();
                            $('.gst_charge').hide();
                        }
                    }
                })
                $('.member-section').show();
                $('.eli_loan').hide();
                $('.shareholder').hide();
                $('.director').hide();
                $('.penal').hide();
                $('.bank').hide();
                $('.associate_name').hide();
                $('.associate_code').hide();
                $('.payment-mode-type').hide();
                $("#amount").attr("readonly", true);
                $("#amount").val(50);
                $('.indirect_sub_head').hide();
            } else if (headId == 87) {
                $('.associate_code').show();
                $('.member-section').hide();
                $('.eli_loan').hide();
                $('.shareholder').hide();
                $('.director').hide();
                $('.penal').hide();
                $('.associate_name').hide();
                $('.bank').hide();
                $('.indirect_sub_head').hide();
            }
        })
        $(document).on('change', '#branch_id,#date,#head,#member_id', function() {
            let thiss = $(this);
            let companyId = $('#company_id option:selected').val();
            if (companyId == "") {
                swal('Warning', 'Please select company first', 'warning');
                $(thiss).val('');
                $('.indirect_sub_head').hide();
                return false;
            }
            const BranchId = $('#branch_id').val();
            const head = $('#head').val();
            const date = $('#date').val();
            var memberid = $("#member_id").val();
            const type = "1";
            if (head == 122) {

            } else {
                $('#cgst_stationary_charge').hide();
                $('#sgst_stationary_charge').hide();
                $('#igst_stationary_charge').hide();
                $('.gst_charge').hide();
                $('.igst_charge').hide();
            }
        })
        $('#shareholder_id').on('change', function() {
            var type_id = $(this).val();
            if (type_id > 0) {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.headDetailGetAll') !!}",
                    data: {
                        id: type_id
                    },
                    dataType: "JSON",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#register_date_shareholder').val(response.rgister_date);
                    }
                })
            }
        });
        $('#member_id').on('change', function() {
            var member_id = $(this).val();
            var companyId = $('#company_id').val();
            var branch = $('#branch').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.voucher.memberdetails') !!}",
                data: {
                    'member_id': member_id
                },
                dataType: "JSON",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response, textStatus, jqXHR) {
                    if (textStatus == 'success') {
                        if (Number(response.data.company_id) != Number(companyId)) {
                            console.log('collectorDetails',response.data.company_id,'companyId',companyId);
                            swal('Warning', 'Member Id does not exist in selected company.','warning');
                            $('#member_auto_id').val('');
                            $('#member_id').val('');
                            $('#member_name').val('');
                            $('#member_register_date').val('');
                            return false;
                        }
                        if (Number(response.data.branch_id) != Number(branch)) {
                            console.log('branch_id',response.data.branch_id,'branch',branch);
                            swal('Warning', 'Member Id does not exist in selected branch.','warning');
                            $('#member_auto_id').val('');
                            $('#member_id').val('');
                            $('#member_name').val('');
                            $('#member_register_date').val('');
                            return false;
                        }
                        if (response.collectorDetails.first_name) {
                            var fName = response.collectorDetails.first_name;
                        } else {
                            var fName = '';
                        }
                        if (response.collectorDetails.last_name) {
                            var lName = response.collectorDetails.last_name;
                        } else {
                            var lName = '';
                        }
                        $('#member_name').val(fName + ' ' + lName);
                        $('#member_auto_id').val(response.collectorDetails.id);
                        $('#member_register_date').val(response.createdDate);
                    } else {
                        $('#member_auto_id').val('');
                        $('#member_id').val('');
                        $('#member_name').val('');
                        $('#member_register_date').val('');
                        swal("Warning!", "Member Not Found", "warning");
                        return false;
                    }
                },
                error: function() {
                    swal('Error', 'Member not found', 'error');
                    $('#member_id').val('');
                    $('#member_name').val('');
                    return false;
                },
            })
        });
        $('#director_id').on('change', function() {
            var type_id = $(this).val();
            if (type_id > 0) {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.headDetailGetAll') !!}",
                    data: {
                        id: type_id
                    },
                    dataType: "JSON",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#register_date_director').val(response.rgister_date);
                    }
                })
            }
        });
        $('#eli_loan_id').on('change', function() {
            var type_id = $(this).val();
            if (type_id > 0) {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.headDetailGetAll') !!}",
                    data: {
                        id: type_id
                    },
                    dataType: "JSON",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#eli_loan_date').val(response.rgister_date);
                    }
                })
            }
        });
        $('#payment_mode').on('change', function() {
            let companyId = $('#company_id option:selected').val();
            let dateee = $('#date').val();
            if (companyId == "") {
                swal('Warning', 'Please select company first', 'warning');
                $('#payment_mode').val('');
                return false;
            }
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
            if (mode == 0 && mode != '') {
                $('.payment_mode_cash').show();
                $('.payment_mode_cheque').hide();
                $('.payment_mode_online').hide();
                $("#daybook").trigger("change");

                let branchIdd = $('#branch option:selected').val();
                let creatDate = $('#date').val();
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.branchBankBalanceAmount') }}",
                    data: {
                        'company_id': companyId,
                        'entrydate': creatDate,
                        'branch_id': branchIdd,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var response = JSON.parse(response);
                        var balanceNew = response.balance;
                        $('#branch_total_balance').val(balanceNew);
                    }
                });
            } else if (mode == 2) {
                $('.payment_mode_cheque').hide();
                $('.payment_mode_cash').hide();
                $('.payment_mode_online').show();
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.fetchbranchbycompanyid') }}",
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
                            var optionBank =
                                `<option value="">----Please Select----</option>`;
                            myObj.bank.forEach(element => {
                                optionBank +=
                                    `<option value="${element.id}">${element.bank_name}</option>`;
                            });
                            $('#online_bank').html(optionBank);

                        }
                    }
                });

            } else if (mode == 1) {
                let branch_id = $('#branch option:selected').val();
                $('.payment_mode_cheque').show();
                $('.payment_mode_cash').hide();
                $('.payment_mode_online').hide();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.approve_recived_cheque_lists') !!}",
                    dataType: 'JSON',
                    data: {
                        'companyId': companyId,
                        'branch_id': branch_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#cheque_no').find('option').remove();
                        $('#cheque_no').append(
                            '<option value="">Select cheque number</option>');
                        $.each(response.cheque, function(index, value) {
                            $("#cheque_no").append("<option value='" + value.id +
                                "'>" + value.cheque_no + "  ( " + parseFloat(
                                    value.amount).toFixed(2) + ")</option>");
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
                url: "{!! route('admin.approve_cheque_details') !!}",
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
            $('#bank_register_date').val('');
            var bank_id = $(this).val();
            $.ajax({
                url: "{!! route('admin.bank_account_list') !!}",
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
                        $("#bank_account").append("<option value='" + value.id +
                            "'>" + value.account_no + "</option>");
                    });
                }
            })
        })
        $('#bank_account').on('change', function() {
            var bank_id = $('#bank_id').val();
            var account_id = $('#bank_account').val();
            var entrydate = $('#date').val();
            $('#bank_balance').val('0.00');
            $('#bank_register_date').val('');
            if (entrydate == '') {
                swal("Warning!", "Please select  payment date", "warning");
                $('#bank_balance').val('0.00');
                $('#bank_register_date').val('');
            } else {
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
                        $('#bank_balance').val(response.balance);
                        $('#bank_register_date').val(response.create_date);
                    }
                });
            }
        })
        $('#daybook').on('change', function() {
            var daybook = $('#daybook').val();
            var branch_id = $('#branch_id').val();
            var entrydate = $('#date').val();
            $('#branch_total_balance').val('0.00');
            if (branch_id > 0 && daybook != '') {
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
                            'entrydate': entrydate
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
        $('#online_bank').on('change', function() {
            var bank_id = $(this).val();
            $.ajax({
                url: "{!! route('admin.bank_account_list') !!}",
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
                        "Bank available balance must be greater than or equal to  received amount";
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
                        "Received amount must be same as cheque amount";
                    result = false;
                }
            } else {
                $.validator.messages.chkbankCheque = "";
                result = true;
            }
            return result;
        }, "");
        $.validator.addMethod("maxpDateDirector", function(value, element) {
            if (($("#head").val() == 19)) {
                moment.defaultFormat = "DD/MM/YYYY HH:mm";
                var f1 = moment($('#register_date_director').val() + ' 00:00', moment.defaultFormat)
                    .toDate();
                var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
                var from = new Date(Date.parse(f1));
                var to = new Date(Date.parse(f2));
                if (f2 >= f1)
                    return true;
                return false;
            } else {
                return true;
            }
        }, "Payment date must be greater than  creation date");
        $.validator.addMethod("maxpDate", function(value, element) {
            if (($("#head").val() == 15)) {
                moment.defaultFormat = "DD/MM/YYYY HH:mm";
                var f1 = moment($('#register_date_shareholder').val() + ' 00:00', moment.defaultFormat)
                    .toDate();
                var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
                var from = new Date(Date.parse(f1));
                var to = new Date(Date.parse(f2));
                if (f2 >= f1)
                    return true;
                return false;
            } else {
                return true;
            }
        }, "Payment date must be greater than  creation date");
        $.validator.addMethod("maxpDateEmp", function(value, element) {
            if (($("#head").val() == 32)) {
                moment.defaultFormat = "DD/MM/YYYY HH:mm";
                var f1 = moment($('#emp_date').val() + ' 00:00', moment.defaultFormat).toDate();
                var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
                var from = new Date(Date.parse(f1));
                var to = new Date(Date.parse(f2));
                if (f2 >= f1)
                    return true;
                return false;
            } else {
                return true;
            }
        }, "Payment date must be greater than  creation date");
        $.validator.addMethod("maxpDateBank", function(value, element) {
            if (($("#head").val() == 27)) {
                moment.defaultFormat = "DD/MM/YYYY HH:mm";
                var f1 = moment($('#bank_register_date').val() + ' 00:00', moment.defaultFormat)
                    .toDate();
                var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
                var from = new Date(Date.parse(f1));
                var to = new Date(Date.parse(f2));
                if (f2 >= f1)
                    return true;
                return false;
            } else {
                return true;
            }
        }, "Payment date must be greater than  creation date");
        $.validator.addMethod("maxpDateEli", function(value, element) {
            if (($("#head").val() == 96)) {
                moment.defaultFormat = "DD/MM/YYYY HH:mm";
                var f1 = moment($('#eli_loan_date').val() + ' 00:00', moment.defaultFormat).toDate();
                var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
                var from = new Date(Date.parse(f1));
                var to = new Date(Date.parse(f2));
                if (f2 >= f1)
                    return true;
                return false;
            } else {
                return true;
            }
        }, "Payment date must be greater than  creation date");
        $.validator.addMethod("maxpDateMember", function(value, element) {
            if (($("#head").val() == 122)) {
                moment.defaultFormat = "DD/MM/YYYY HH:mm";
                var f1 = moment($('#member_register_date').val() + ' 00:00', moment.defaultFormat)
                    .toDate();
                var f2 = moment(value + ' 00:00', moment.defaultFormat).toDate();
                var from = new Date(Date.parse(f1));
                var to = new Date(Date.parse(f2));
                if (f2 >= f1)
                    return true;
                return false;
            } else {
                return true;
            }
        }, "Payment date must be greater than  creation date");
        $('#voucher_save').validate({
            rules: {
                branch_id: "required",
                date: {
                    required: true,
                    maxpDateDirector: function(element) {
                        if (($("#head").val() == 19)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    maxpDate: function(element) {
                        if (($("#head").val() == 15)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    maxpDateEmp: function(element) {
                        if (($("#head").val() == 32)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    maxpDateBank: function(element) {
                        if (($("#head").val() == 27)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    maxpDateEli: function(element) {
                        if (($("#head").val() == 96)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    maxpDateMember: function(element) {
                        if (($("#head").val() == 122)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                head: "required",
                sub_head: "required",
                emp_date: {
                    required: function(element) {
                        if (($("#head").val() == 32)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                bank_register_date: {
                    required: function(element) {
                        if (($("#head").val() == 27)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                eli_loan_date: {
                    required: function(element) {
                        if (($("#head").val() == 96)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                member_register_date: {
                    required: function(element) {
                        if (($("#head").val() == 122)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
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
                branch_code: {
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
                emp_date: {
                    required: "Please select date",
                },
                branch_code: {
                    required: "Please select branch name",
                },
                bank_register_date: {
                    required: "Please select date",
                },
                eli_loan_date: {
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
            submitHandler: function() {
                $('button[type="submit"]').prop('disabled', true);
                return true;
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
        $('#emp_code').on('change', function() {
            let companyId = $('#company_id').val();
            let branch = $('#branch').val();
            $('#emp_id').val('');
            $('#emp_name').val('');
            $('#emp_date').val('');
            var emp_code = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.rent_employee_check') !!}",
                data: {
                    employee_code: emp_code
                },
                dataType: "JSON",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (companyId != response.emp.company_id) {
                        swal("Error!", "EMP code does not exist in selected company.",
                            "error");
                        $('#emp_code').val('');
                        $('#emp_id').val('');
                        $('#emp_name').val('');
                    } else if (branch != response.emp.branch_id) {
                        swal("Error!", "EMP code does not exist in selected branch.",
                            "error");
                        $('#emp_code').val('');
                        $('#emp_id').val('');
                        $('#emp_name').val('');
                    } else if (response.resCount == 1) {
                        $('#emp_code').val(response.emp.employee_code);
                        $('#emp_id').val(response.emp.id);
                        $('#emp_name').val(response.emp.employee_name);
                        $('#emp_date').val(response.register_date);
                    } else if (response.resCount == 2) {
                        swal("Error!", " Employee Inactive", "error");
                        $('#emp_code').val('');
                        $('#emp_id').val('');
                        $('#emp_name').val('');
                        $('#emp_date').val('');
                    } else {
                        swal("Error!", " Employee code not found!", "error");
                        $('#emp_code').val('');
                        $('#emp_id').val('');
                        $('#emp_name').val('');
                        $('#emp_date').val('');
                    }
                }
            })
        })
        $("#date").hover(function() {
            var date = $('#create_application_date').val();
            $('#date').datepicker({
                format: "dd/mm/yyyy",
                endHighlight: true,
                autoclose: true,
                orientation: "bottom",
                endDate: date,
                startDate: '01/04/2021',
            })
        })
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $(document).on('change', '#branch', function(e) {
            var selectedOption = $('#branch option:selected');
            var dataCodeValue = selectedOption.attr('data-code');
            $('#branch_code').val(dataCodeValue);
        });
        $('#asso_code').on('blur', function() {
            let associate_code = $(this).val();
            let companyIddd = $('#company_id').val();
            let branchIdd = $('#branch').val();
            if (companyIddd == "" || branchIdd == "") {
                swal('Warning', 'Please select company and branch first', 'warning');
                return false;
            }
            
            $.post("{{ route('admin.voucher.associate') }}", {
                    associate_code: associate_code,
                    company_id: companyIddd,
                    branch_id: branchIdd,
                },
                function(data, textStatus, jqXHR) {
                    if (textStatus == 'success' && data != 'null') {
                        let response = JSON.parse(data);
                        $('.associate_name').show();
                        $('#asso_name').val(`${(response.first_name !== null) ? response.first_name : ''}  ${(response.last_name !== null) ? response.last_name : ''}`);
                        $('#asso_id').val(response.id);
                    } else {
                        swal('Error!','Associate code does not exist !','error');
                        $('#asso_code').val('');
                        $('.associate_name').hide();
                    }

                },
            );
            
        })
        $('#company_id').change(function() {
            $('#head').val('').trigger('change');
            $('#payment_mode').val('').trigger('change');
            $('.indirect_sub_head').hide();
            $('.penal').hide();
            $('.member-section').hide();
            $('#branch_code').val('');
            $('.associate_name').hide();
            $('.associate_code').hide();
        })
        $('#branch').on('change', function() {
            $('#emp_code').val('');
            $('#emp_name').val('');
            $('#emp_date').val('');
            $('#asso_code').val('');
            $('#asso_name').val('');
            $('.associate_name').hide();

        })
    });
</script>
