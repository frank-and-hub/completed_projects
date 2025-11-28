<script type="text/javascript">
    var expense;
    $(document).ready(function() {
        var today = new Date();
        $('#expensesDate').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: "today",
            maxDate: today,
            startDate: '01/04/2021',
        })

        $("#bill_date").hover(function() {
            var date = $('#create_application_date').val();
            $('#bill_date').datepicker({
                format: "dd/mm/yyyy",
                endHighlight: true,
                autoclose: true,
                orientation: "bottom",
                endDate: date,
                startDate: '01/04/2021',
            })
        })

        $.validator.addMethod("zero", function(value, element, p) {

            if (Number(value) > 0) {
                $.validator.messages.zero = "";
                result = true;
            } else {
                $.validator.messages.zero = "Amount must be greater than 0.";
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

            $.validator.addMethod("perCheck", function(value, element, p) {
                if (value <= 100) {
                    $.validator.messages.perCheck = "";
                    result = true;
                } else {
                    $.validator.messages.perCheck = "Division should not greater than 100%";
                    result = false;
                }
                return result;

            }, "");
            return result;
        }, "");

        $.validator.addClassRules({
            amount: {
                amountRequired: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });

        $.validator.addMethod("amountRequired", $.validator.methods.required, "Please Enter Amount.");
        //------------------------------------

        $.validator.addClassRules({
            particular_more: {
                particularRequired: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });

        $.validator.addMethod("particularRequired", $.validator.methods.required, "Please enter particular.");

        //Account Head More
        $.validator.addClassRules({
            account_head_more: {
                account_headMoreRequired: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });

        $.validator.addMethod("account_headMoreRequired", $.validator.methods.required,
            "Please Select Account Head.");
        //Amount More
        $.validator.addClassRules({
            amount_more: {
                amountMoreRequired: true,
                decimal: true,
                number: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });

        $.validator.addMethod("amountMoreRequired", $.validator.methods.required, "Please Enter Amount.");

        //Bill Date More
        $.validator.addClassRules({
            bill_date_more: {
                bill_date_moreRequired: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });

        $.validator.addMethod("bill_date_moreRequired", $.validator.methods.required,
            "Please Select Bill Date.");


        $('#branch').on('change', function() {
            var daybook = 0;
            var branch_id = $('#branch').val();
            var entrydate = $('#created_at').val();
            var company_id = $('#company_id').val();

            $('#branch_total_balance').val('0.00');
            if (branch_id > 0) {
                if (entrydate == '') {
                    swal("Warning!", "Please select  payment date", "warning");
                    $('#branch_total_balance').val('0.00');
                } else {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.branchBankBalance') !!}",
                        dataType: 'JSON',
                        data: {
                            'branch_id': branch_id,
                            'daybook': daybook,
                            'entrydate': entrydate,
                            'company_id': company_id
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // alert(response.balance);
                            $('#branch_total_balance').val(response.balance);
                        }
                    });
                }
            }
        })


        $.validator.addMethod("amountcheck", function(value, element, p) {
            if (parseFloat($('#branch_total_balance').val()) >= parseFloat($('#total_amount').val())) {
                $.validator.messages.amountcheck = "";
                result = true;
            } else if (parseFloat($('#bank_balance').val()) >= parseFloat($('#total_amount').val())) {
                $.validator.messages.amountcheck = "";
                result = true;
            } else {
                if ($('#branch_total_balance').val() > 0) {
                    $.validator.messages.amountcheck = "";
                    $.validator.messages.amountcheck =
                        "Balance must be greater than or equal to total amount";
                }
                if ($('#bank_balance').val() > 0) {
                    $.validator.messages.amountcheck = "";
                    $.validator.messages.amountcheck =
                        "Bank Balance must be greater than or equal to total amount";
                }

                result = false;
            }
            return result;

        }, "");
        $.validator.addMethod('lettersOnly', function(value, e) {
            return this.optional(e) || /^[a-z ]+$/i.test(value);
        }, "Please Enter Letter Only");
        //-------------------------------------

        $('#expenses').validate({

            rules: {
                branch_id: "required",
                branch_total_balance: "required",
                amountcheck: true,
                date: {
                    required: true,
                },
                bill_date: {
                    required: true,
                },
                particular: {
                    required: true,
                },
                account_head: {
                    required: true,
                },
                amount: {
                    required: true,
                    decimal: true,
                },
                total_amount: {
                    required: true,
                    decimal: true,
                    zero: true,
                    amountcheck: true,
                },
                expensesDate: {
                    required: true,
                },
                payment_mode: {
                    required: true,
                },
                bank_id: {
                    required: true,
                },
                account_id: {
                    required: true,
                },
                cheque_id: {
                    required: true,
                },
                cheque_amount: {
                    required: true,
                    decimal: true,
                },
                utr_no: {
                    required: true,
                },

                neft_charge: {
                    required: true,
                    decimal: true,
                },
                party_bank_name: {
                    required: true,
                },
                party_bank_ac_no: {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 16
                },
                party_bank_ifsc: {
                    required: true,
                    checkIfsc: true
                },
                party_name: {
                    required: true,
                    lettersOnly: true
                },

            },

            messages: {
                party_name: {
                    required: 'Please Enter Party Name.',

                },
                party_bank_name: {
                    required: 'Please Enter Party Bank Name.',
                },
                party_bank_ac_no: {
                    required: 'Please Enter Party Bank Account Number.',
                    number: 'Please enter valid number',
                    minlength: 'Please enter minimum 8 digit number',
                    maxlength: 'Please enter maximum 16 digit number',
                },
                party_bank_ifsc: {
                    required: 'Please Enter Party Bank Ifsc.',
                },
                bill_date: {
                    required: "Please  Select  Bill Date.",
                },
                date: {
                    required: "Please  Select Date.",
                },

                particular: {
                    required: "Please enter particular",
                },

                branch_id: {
                    required: "Please  select branch.",
                },
                branch_total_balance: {
                    required: "Please  Enter total Balance.",
                },

                account_head: {
                    required: "Please  Select Account Head.",
                },
                amount: {
                    required: "Please enter amount.",
                },
                total_amount: {
                    required: "Total Amount is required.",
                },
                expensesDate: {
                    required: "Date is required.",
                },

                payment_mode: {
                    required: "Please Select payment mode.",
                },
                bank_id: {
                    required: "Please Select Bank.",
                },
                account_id: {
                    required: "Please Select A/C.",
                },
                cheque_id: {
                    required: "Please Select Cheque Number.",
                },
                cheque_amount: {
                    required: "Please enter amount.",
                },
                utr_no: {
                    required: "Please enter UTR Number.",
                },
                neft_charge: {
                    required: "Please enter RTGS/NEFT charge.",
                },
            }
       

        });

        $('.particularRow').on('change', function(e) {
            var val = $(this).val();
            var row = $(this).attr('data-row-id');
            if (val == '') {
                $('#msg-' + row).html('Please Enter PArticular');
                $('.submit').prop('disabled', true);
            } else {
                $('#msg-' + row).remove();
                $('.submit').prop('disabled', false);
            }
            return true;
        })
        $('.ac').on('change', function(e) {
            var val = $(this).val();
            var row = $(this).attr('data-row-id');
            if (val == '') {
                $('#acmsg-' + row).html('Please Select Account Head');
                $('.submit').prop('disabled', true);
            } else {
                $('#acmsg-' + row).remove();
                $('.submit').prop('disabled', false);
            }
            return true;
        })

        $('.t_amount').on('change', function(e) {
            var val = $(this).val();
            var row = $(this).attr('data-row-id');
            if (val == '') {
                $('#amtmsg-' + row).html('Please Enter Amount');
                $('.submit').prop('disabled', true);
            } else if (val < 0) {
                $('#amtmsg-' + row).html('Please Enter Valid Amount');
                $('.submit').prop('disabled', true);
            } else {
                $('#amtmsg-' + row).remove();
                $('.submit').prop('disabled', false);
            }
            return true;
        })


        $('.export').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#expense_export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.expense.export') !!}");
            $('form#filter').submit();
            return true;
        });


        var a = 0;
        var b = 0;

        $("#add_row").click(function() {
            var selectedCompanyId = $('#company_id').val();
            b++;

            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense') !!}",
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function(response) {
                    //const obj = JSON.parse(response);
                    //console.log(obj);
                    var len = response.length;
                    //console.log(len);

                    var expendHtml = '<tr>';

                    //expendHtml += '<td > <div class="col-lg-12 error-msg"> <input type="text"  class="form-control bill_date_more" name="bill_date_more['+a+']" id="bill_date_more'+a+'" readonly> </td>';

                    expendHtml +=
                        '<td> <div class="col-lg-12 error-msg"> <input type="text" id="particular_more' +
                        a + '" name="particular_more[' + a +
                        ']" class="form-control particular_more" ></td>';

                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg">  <select name="account_head_more[' +
                        a + ']" id="account_head_' + a +
                        '" class="account_head_more form-control" data-value=' + b +
                        ' > <option value="">Select Account Head</option>';
                    for (var i = 0; i < len; i++) {
                        var head_id = response[i].head_id;
                        var sub_head = response[i].sub_head;
                        var companyArray = response[i].company_id;
                        if (companyArray.includes(Number(selectedCompanyId))) {
                        expendHtml += '<option value="' + head_id + '"  data-companyid="' +
                            companyArray + '" class="head_option">' + sub_head +
                            '</option>';   
                        }

                        if (i == len - 1) {
                            expendHtml += '</select></div>';
                        }
                    }
                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg">  <select name="sub_head1_more[' +
                        a + ']" id="sub_head1' + a + '" class="form-control  ' + b +
                        '-sub_head1_more sub_head1_more" data-value=' + b +
                        ' > <option value="0">Select Sub Head1</option>';
                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg">  <select name="sub_head2_more[' +
                        a + ']" id="sub_head2' + a + '" class="form-control  ' + b +
                        '-sub_head2_more sub_head2_more"  data-value=' + b +
                        ' > <option value="0">Select Sub Head2</option>';
                    expendHtml +=
                        ' <td > <div class="col-lg-12 error-msg"> <input type="text" id="amount_more' +
                        a + '" name="amount_more[' + a +
                        ']" class="form-control amount_more t_amount" ></div> </td><td > <div class="col-lg-12 error-msg"> <input type="file" id="receipt_more' +
                        a + '" name="receipt_more[' + a +
                        ']" class="form-control receipt_more" ></div> </td> <td style=""> <button type="button" class="btn btn-primary remCF"><i class="icon-trash"></i></button> </td></tr>'

                    $("#expense1").append(expendHtml);
                    a++;

                    var date11 = $('#create_application_date').val();

                    $('.bill_date_more').datepicker({
                        format: "dd/mm/yyyy",
                        endHighlight: true,
                        autoclose: true,
                        orientation: "bottom",
                        endDate: date11,
                    })
                }
            })
        });


        $(document).on('change', '.account_head_more', function() {
            var option = '<option value="">Select Sub Head 1</option>';
            var id = $(this).val();
            var index = $(this).attr('data-value');
            $('.' + index + '-sub_head1_more').empty();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var len = response.account_heads.length;
                    if (len > 0) {
                        for (var i = 0; i < len; i++) {
                            var head_id = response.account_heads[i].head_id;
                            var sub_head = response.account_heads[i].sub_head;
                            option += '<option value="' + head_id + '">' + sub_head +
                                '</option>';
                        }
                        $('.' + index + '-sub_head1_more').append(option);
                    } else {
                        $('.' + index + '-sub_head1_more').append(
                            '<option value=0>Select Sub Head 1</option>');
                    }
                }
            })
        });


        $(document).on('change', '.sub_head1_more', function() {
            var option = '<option value="">Select Sub Head 2</option>';
            var id = $(this).val();
            var index = $(this).attr('data-value');
            $('.' + index + '-sub_head2_more').empty();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function(response) {
                    var len = response.account_heads.length;
                    //	alert(response);
                    //	alert(len);

                    if (len > 0) {
                        for (var i = 0; i < len; i++) {
                            var head_id = response.account_heads[i].head_id;
                            var sub_head = response.account_heads[i].sub_head;
                            option += '<option value="' + head_id + '">' + sub_head +
                                '</option>';
                        }
                        //alert(index);
                        //	alert(option);
                        $('.' + index + '-sub_head2_more').append(option);
                    } else {
                        $('.' + index + '-sub_head2_more').append(
                            '<option value="">Select Sub Head 2</option>');
                    }
                }
            })
        });

        $('#neft_charge').on("keyup", function() {
            var neft = $(this).val();
            var sum = 0;
            $('.t_amount').each(function() {

                if ($(this).val() == 0 || $(this).val() > 0) {
                    sum += Number($(this).val());

                }
            });
            sum += Number(neft);
            $('#total_amount').val(sum);
        });
        $('#expense1').on("keyup", ".t_amount", function() {
            var sum = 0;
            $('.t_amount').each(function() {
                if ($(this).val() == 0 || $(this).val() > 0) {
                    sum += Number($(this).val());
                }
            });
            sum += Number($('#neft_charge').val());
            $('#total_amount').val(sum);
        });


        $("#expense").on('click', '.remCF', function() {
            var delteBtn = $(this);
            swal({
                title: "Are you sure?",
                text: 'Do you want to delete this expense?',
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                cancelButtonClass: "btn-danger",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm) {
                if (isConfirm) {
                    $(delteBtn).parent().parent().remove();
                    $(".t_amount").trigger("keyup");
                }
            })

        });


        $('#account_head').on('change', function() {
            var id = $(this).val();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#sub_head1').find('option').remove();
                    $('#sub_head1').append('<option value="">Select sub head1</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#sub_head1").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });

                }
            })
        })


        $('#sub_head1').on('change', function() {
            var id = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#sub_head2').find('option').remove();
                    $('#sub_head2').append('<option value="">Select sub head2</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#sub_head2").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            })
        })



        expense = $('#expense_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#expense_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.expense_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.bill_no = $('#bill_no').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },

                {
                    data: 'bill_date',
                    name: 'bill_date'
                },
                {
                    data: 'payment_date',
                    name: 'payment_date'
                },
                {
                    data: 'account_head',
                    name: 'account_head'
                },
                {
                    data: 'sub_head1',
                    name: 'sub_head1'
                },
                {
                    data: 'sub_head2',
                    name: 'sub_head2'
                },
                {
                    data: 'particular',
                    name: 'particular'
                },
                {
                    data: 'receipt',
                    name: 'receipt '
                },
                {
                    data: 'amount',
                    name: 'amount'
                },

            ],"ordering": false
        })
        $(expense.table().container()).removeClass('form-inline');

        //Bill Expense Listing

        expense = $('#bill_expense_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#bill_expense_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.bill_expense_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'bill_no',
                    name: 'bill_no'
                },
                {
                    data: 'total_expense',
                    name: 'total_expense'
                },
                {
                    data: 'account_head',
                    name: 'account_head'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action'
                }

            ],"ordering": false
        })
        $(expense.table().container()).removeClass('form-inline');



        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });


        $('input.expensesDate').datepicker().on('change', function(ev) {
            var daybook = 0;
            var entrydate = $(this).val();
            var branch_id = $('#branch').val();
            var company_id = $('#company_id').val();
            var payment_mode = $('#payment_mode').val();

            if (branch_id != '' && payment_mode != '') {
                if (payment_mode == 0) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.branchBankBalance') !!}",
                        dataType: 'JSON',
                        data: {
                            'branch_id': branch_id,
                            'daybook': daybook,
                            'entrydate': entrydate,
                            'company_id': company_id
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
        });

        $('#branch').on('change', function() {
            var daybook = 0;
            var branch_id = $('#branch').val();
            var entrydate = $('#expensesDate').val();
            $('.cash_box').hide();

            $('#payment_mode').val('');

            if (branch_id > 0) {
                if (entrydate == '') {
                    $('#branch').val('');
                    swal("Warning!", "Please select  payment date first!!", "warning");
                }
            }
        })


        // $('.cash_box').hide();
        //$('#bank_details').hide();
        $('#payment_mode').on('change', function() {
            var daybook = 0;
            var branch_id = $('#branch').val();
            var company_id = $('#company_id').val();
            var entrydate = $('#expensesDate').val();
            var payment_mode = $('#payment_mode').val();

            // $('.cash_box').hide();
            // $('#bank_details').hide();

            $('#branch_total_balance').val('0.00');
            if (branch_id != '' && entrydate != '') {
                if (branch_id > 0) {
                    if (payment_mode != '') {
                        if (payment_mode == 0) {

                            $('.cash_box').show();
                            $('#cash').show();
                            $('#bank_details').hide();

                            $.ajax({
                                type: "POST",
                                url: "{!! route('admin.branchBankBalanceAmount') !!}",
                                dataType: 'JSON',
                                data: {
                                    'branch_id': branch_id,
                                    'daybook': daybook,
                                    'entrydate': entrydate,
                                    'company_id': company_id
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    // alert(response.balance);
                                    $('#branch_total_balance').val(response.balance);
                                }
                            });
                        } else {
                            $('.cash_box').hide();
                            $('#bank_details').show();
                            $('#account_id').val('');
                            $('#bank_id').val('');
                            $('#bank_balance').val('0.00');
                            if (payment_mode == 1) {
                                $('#chq_details').show();
                                $('#online_details').hide();
                            } else {
                                $('#online_details').show();
                                $('#chq_details').hide();
                            }
                        }
                    }
                }
                var companyId = $('#company_id').val();
                $('#bank_id').html('<option value="">Select Bank</option>');
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
                            $('#bank_id').html(optionBank);

                        }
                    }
                });
            } else {
                swal("Warning!", "Please select  payment date and Branch first", "warning");
                $('#payment_mode').val('');
                return false;
            }
        })

        var paymentMode = $('#payment_mode').val();
        if (paymentMode == 0) {
            $('.cash_box').show();
            $('#bank_details').hide();
            $('#chq_details').hide();
            $('#online_details').hide();
        } else if (paymentMode == 1) {
            $('.cash_box').hide();
            $('#bank_details').show();
            $('#chq_details').show();
            $('#online_details').hide();
        } else if (paymentMode == 2) {
            $('.cash_box').hide();
            $('#bank_details').show();
            $('#chq_details').hide();
            $('#online_details').show();
        }


        $(document).on('change', '#bank_id', function() {
            var bank_id = $('#bank_id').val();
            $('#bank_balance').val('0.00');
            $('#cheque_id').find('option').remove();
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
                    $('#account_id').append(
                        '<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#account_id").append("<option value='" + value.id +
                            "'>" + value.account_no + "</option>");
                    });
                }
            });
        });


        $(document).on('change', '#account_id', function() {
            $('#bank_balance').val('0.00');
            var account_id = $('#account_id').val();
            var bank_id = $('#bank_id').val();
            var entrydate = $('#expensesDate').val();

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
                    $('#bank_balance').val(response.balance);
                }
            });
        });

        $(document).on('click', '.delete_expense', function() {
            var expense_id = $(this).attr("data-row-id");

            swal({
                    title: "Are you sure?",
                    text: "Do you want to delete this expense?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: "POST",
                            url: "{!! route('admin.expense.delete-expense') !!}",
                            dataType: 'JSON',
                            data: {
                                'table_id': expense_id
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.status == "1") {
                                    swal("Good job!", response.message, "success");
                                    location.reload();
                                } else {
                                    swal("Warning!", response.message, "warning");
                                    return false;
                                }
                            }
                        });
                    }
                });
        })
    })
</script>
<script>
    $(document).on('click', '.reject_block', function() {
        var expense_id = $(this).attr("data-row-id");
        var type = $(this).attr("data-type");
        $('#reason-form').modal();
        $('#expense_id').val(expense_id);
        $('#type').val(type);
        $('#reason').val('');

        var text_type = "";
        if (type == 2) {
            text_type = "Reject";
        } else {
            text_type = "Block";
        }
        $('#reason_text').html(text_type);
    });

    $(document).ready(function() {
        $('#comment-form').validate({ // initialize the plugin
            rules: {
                'reason': 'required',
            },
            messages: {
                reason: "Please enter Comments!",
            },
        });
    });


    $(document).on('click', '.approve_expense', function() {
        var bill_no = $(this).attr("data-row-id");
        var msg = "Do you want to approve this Expense?";

        swal({
                title: "Are you sure?",
                text: msg,
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                cancelButtonClass: "btn-danger",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.expense.approve_expense') !!}",
                        dataType: 'JSON',
                        data: {
                            'bill_no': bill_no
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            //alert(response.status);
                            if (response.status == "1") {
                                swal("Good job!", response.message, "success");
                                location.reload();
                            } else {
                                swal("Warning!", response.message, "warning");
                                return false;
                            }
                        }
                    });
                }
            });
    });
    $(document).ready(function() {
        $(document).on('change', '#company_id', function() {
            $('#payment_mode').val('');
        })
        function isNumber(n) { return /^-?[\d.]+(?:e-?\d+)?$/.test(n); } 
        $(document).on('blur', '.t_amount ', function() {
            $('.amount_more_error').remove();
            $.each($('.t_amount'), function(index, valueOfElement) {
                if (!isNumber($('.t_amount').eq(index).val())) {
                    $('.t_amount').eq(index).after(
                        '<label  class="error amount_more_error" >Please Enter a valid number</label>'
                    );
                    $('#mySubmitBtn').prop('disabled', true);
                    return false;
                } else {
                    if ($('.t_amount').eq(index).val() <= 0) {
                        $('.t_amount').eq(index).after(
                            '<label  class="error amount_more_error" >Amount must be greater than 0.</label>'
                        );
                        $('#mySubmitBtn').prop('disabled', true);
                        return false;
                    } else {
                        $('.amount_more_error').remove();
                        $('#mySubmitBtn').prop('disabled', false);
                    }
                }


            });
        })
    });
</script>
