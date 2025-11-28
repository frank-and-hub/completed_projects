<script type="text/javascript">
    var memberTable;
    $('#company_id').parent('.col-lg-12').siblings('.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
    $('#company_id').parent('.col-lg-12').addClass('col-lg-8').removeClass('col-lg-12');
    $('#branch').parent().parent('.col-lg-12').siblings('.col-lg-12').addClass('col-lg-4').removeClass('col-lg-12');
    $('#branch').parent().parent('.col-lg-12').addClass('col-lg-8').removeClass('col-lg-12');
    $(document).ready(function() {

       
        $("#adj_amount").on("input", function() {
        $(this).val($(this).val().replace(/[^0-9.-]/g, ""));
        });

        var itemsoptions = $('#item_id0 option.expenseOption').clone();
        let company_id = $('#company_id option:selected').val();
        $('.item_id option.expenseOption').each(function() {
            var option = $(this);
            var dataCompanyId = option.data('companyid');
            if (Number(dataCompanyId) != Number(company_id)) {
                option.remove();
            }
        });
        $('#company_id').on('change', function() {
            let company_id = $('#company_id option:selected').val();
            $.ajax({
                type: "POST",
                url: "{{ route('admin.vendor.companydate') }}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                },
                success: function(response) {
                    $('#companyDate').val(response);
                    // $('#bill_date').datepicker('setDate', response);
                    // $('#bill_date').datepicker('setStartDate', response);
                }
            });
            var html = "<option value=''>Choose...</option>";
            var filteredOptions = itemsoptions.filter(function() {
                if (Number($(this).data('companyid')) == Number(company_id)) {
                    html +=
                        `<option value="${$(this).val()}" data-companyid="${$(this).data('companyid')}" style="display:none;">${$(this).text()}</option>`;
                }
                return Number($(this).data('companyid')) == Number(company_id);
            });
            $('.item_id').html(html).trigger('change');
            $('.td_remove').hide();
        })
        $('.item_id').select2({
            width: '100%',
            placeholder: 'Select or Add',
            language: {
                noResults: function() {
                    var validator = $("#item_add").validate();
                    validator.resetForm();
                    $(".form-control").removeClass("is-invalid");
                    $(".custom-control-input").removeClass("is-invalid");
                    return '<button id="no-results-btn" data-toggle="modal" data-target="#modal-form" class="btn btn-primary additme" >Add Item</a>';
                },
            },
            escapeMarkup: function(markup) {
                return markup;
            },
        });
        $(document).on('change', '.item_id', function() {
            var currentItemID = $(this).val();
            var currentItemRow = $(this).attr("data-row-id");
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $('.item_id').val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_item_details') !!}",
                dataType: 'JSON',
                data: {
                    'item_id': currentItemID,
                    'currentItemRow': currentItemRow,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == 'success') {
                        $("#trRow" + currentItemRow).find('.td_remove').remove();
                        $("#tdRow" + currentItemRow).after(response.view);
                        var newRowItem = parseInt(currentItemRow) + 1;
                        //	$("#itemCount").val(newRowItem); 
                        $(".account_head_item").trigger("change");
                        if ($("#discount").val() == 2) {
                            $(".tran_div_discount").css("display", "block");
                            $(".discountField").css("display", "none");
                            $('.discount_item').val(0);
                        } else {
                            $(".tran_div_discount").css("display", "none");
                            $(".discountField").css("display", "block");
                        }
                        total_calculateSub();
                    } else {
                        swal("Error!", "" + response.view + "", "error");
                    }
                }
            });
        })
        $(document).on('change', '.additme', function() {
            $(".select2-container").addClass("select2-container--close");
            $(".select2-container").removeClass("select2-container--open");
        });
        $(document).on('click', '.addNewRow', function() {
            tid = $('#bill-expense-table tr:last').attr('value');
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                return false;
            }
            var newRowItem = $("#itemCount").val();
            var currentItemRow = tid;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_items') !!}",
                dataType: 'JSON',
                data: {
                    'newRowItem': newRowItem,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == 'success') {
                        $("#trRow" + currentItemRow).after(response.view);
                        $('.item_id').select2({
                            width: '100%',
                            placeholder: 'Select or Add',
                            language: {
                                noResults: function() {

                                    var validator1 = $("#item_add").validate();
                                    validator1.resetForm();
                                    $(".form-control").removeClass(
                                        "is-invalid");
                                    $(".custom-control-input").removeClass(
                                        "is-invalid");
                                    return '<button id="no-results-btn" data-toggle="modal" data-target="#modal-form" class="btn btn-primary additme" >Add Item</a>';
                                },
                            },
                            escapeMarkup: function(markup) {
                                return markup;
                            },
                        });
                        var newRowItem1 = parseInt(newRowItem) + 1;
                        $("#itemCount").val(newRowItem1);
                        $("#itemCount_old").val(parseInt(newRowItem));
                    } else {
                        swal("Error!", "" + response.view + "", "error");
                    }
                }
            });
        })
        $(document).on('change', '#discount', function() {
            var id = $(this).val();
            $('#total_discount_val').val(0);
            $('.discount_item').val(0);
            $(".quntity_item").trigger("keyup");
            $(".quntity_item").trigger("keypress");

            if (id == 2) {
                $(".tran_div_discount").css("display", "block");

                $(".discountField").css("display", "none");
                $('.discount_item').val(0);
            } else {
                $(".tran_div_discount").css("display", "none");
                $(".discountField").css("display", "block");
            }
            $(".quntity_item").trigger("keyup");
            $(".quntity_item").trigger("keypress");
            totalDiscountGet();
            total_calculateSub();
        });



        $(document).on('change', '.account_head_item', function() {
            var id = $(this).val();
            var index1 = $(this).attr('data-value');
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $(".account_head_item").val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_subhead1_item_' + index1).find('option').remove();
                    $('#account_subhead1_item_' + index1).append(
                        '<option value="">Select sub head1</option>');
                    $.each(response.account_heads, function(index, value) {
                        $('#account_subhead1_item_' + index1).append(
                            "<option value='" + value.head_id + "'>" + value
                            .sub_head + "</option>");
                    });
                    if ($('#h_' + index1).val() == id) {
                        $('#account_subhead1_item_' + index1).val($('#h1_' + index1).val());
                        $(".account_subhead1_item").trigger("change");
                    }

                }
            })
        });

        $(document).on('change', '.account_subhead1_item', function() {
            var id = $(this).val();
            var index1 = $(this).attr('data-value');
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $(".account_subhead1_item").val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_subhead2_item_' + index1).find('option').remove();
                    $('#account_subhead2_item_' + index1).append(
                        '<option value="">Select sub head1</option>');
                    $.each(response.account_heads, function(index, value) {
                        $('#account_subhead2_item_' + index1).append(
                            "<option value='" + value.head_id + "'>" + value
                            .sub_head + "</option>");
                    });
                    if ($('#h1_' + index1).val() == id) {
                        $('#account_subhead2_item_' + index1).val($('#h2_' + index1).val());
                        $(".account_subhead2_item").trigger("change");
                    }
                }
            })
        });

        $(document).on('change', '.account_subhead2_item', function() {
            var id = $(this).val();
            var index1 = $(this).attr('data-value');
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $(".account_subhead2_item").val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_subhead3_item_' + index1).find('option').remove();
                    $('#account_subhead3_item_' + index1).append(
                        '<option value="">Select sub head1</option>');
                    $.each(response.account_heads, function(index, value) {
                        $('#account_subhead3_item_' + index1).append(
                            "<option value='" + value.head_id + "'>" + value
                            .sub_head + "</option>");
                    });
                    if ($('#h2_' + index1).val() == id) {
                        $('#account_subhead3_item_' + index1).val($('#h3_' + index1).val());
                    }
                }
            })
        });








        var date = new Date();

        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        $('#bill_date').hover(function() {
            var startdate = $('#vendor_create_date').val();
            var globalDatee = $('#bill_reate_application_date').val();
            $('#bill_date').datepicker('setStartDate', startdate);
            $('#bill_date').datepicker('setEndDate', globalDatee);
        })

        $(document).on('keyup', '.quntity_item', function() {
            var index1 = $(this).attr('data-value');
            amount_change(index1);
        });
        $(document).on('keyup', '.rate_item', function() {
            var index1 = $(this).attr('data-value');
            amount_change(index1);
        });

        $(document).on('keyup', '.discount_item', function() {
            var index1 = $(this).attr('data-value');
            amount_change(index1);
        });
        $(document).on('change', '.discount_item_type', function() {
            var index1 = $(this).attr('data-value');
            amount_change(index1);
        });

        $(document).on('keyup', '.gst_item', function() {
            var index1 = $(this).attr('data-value');
            if ($('#gst_item_' + index1).val() != '') {
                $('#igst_item_' + index1).attr('readonly', true);
            } else {
                $('#gst_item_' + index1).attr('readonly', false);
                $('#igst_item_' + index1).attr('readonly', false);
            }
            gst_divide(index1);
        });






        $(document).on('keyup', '.igst_item', function() {

            var index1 = $(this).attr('data-value');

            if ($('#igst_item_' + index1).val() != '') {
                $('#gst_item_' + index1).attr('readonly', true);
            } else {
                $('#gst_item_' + index1).attr('readonly', false);
                $('#igst_item_' + index1).attr('readonly', false);
            }

            total_calculate(index1);
        });

        $(document).on('change', '.gst_item_type', function() {
            var index1 = $(this).attr('data-value');
            gst_divide(index1);
        });

        $(document).on('change', '.igst_item_type', function() {
            var index1 = $(this).attr('data-value');
            total_calculate(index1);
        });


        $(document).on('keyup', '#total_discount_val', function(event) {

            totalDiscountGet();
        });
        $(document).on('change', '#total_discount_type', function() {
            totalDiscountGet();
        });

        $(document).on('change', '#tds_head', function() {
            $("#tds_head_div").css("display", "none");

            tds_head = $('#tds_head').val();
            //alert(tds_head);
            if (tds_head > 0) {
                $("#tds_head_div").css("display", "block");
            }

            if (tds_head == '') {
                $('#tds_per').val('');
            }

            tdsGet();
        })

        $(document).on('keyup', '#tds_per', function() {
            tdsGet();
        });
        $(document).on('keyup', '#adj_amount', function() {
            adj_amount = $('#adj_amount').val();
            $('#final_adj_amount').val(adj_amount);
            //alert('7');
            totalPayAmount();
        });



        /**************************************************************************** */
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


        $.validator.addClassRules({
            item_id: {
                item_idRequired: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("item_idRequired", $.validator.methods.required, "Please select item.");

        $.validator.addClassRules({
            account_head_item: {
                account_head_itemR: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("account_head_itemR", $.validator.methods.required,
            "Please select account head.");
        /*
        	$.validator.addClassRules({ 
        		account_subhead1_item:{  account_subhead1_itemR:  true,},
        			submitHandler: function (form) {   return false;   }  
        	});
        	$.validator.addMethod("account_subhead1_itemR", $.validator.methods.required,"Please select sub head1.");

        	$.validator.addClassRules({ 
        		account_subhead2_item:{  account_subhead2_itemR:  true,},
        			submitHandler: function (form) {   return false;   }  
        	});
        	$.validator.addMethod("account_subhead2_itemR", $.validator.methods.required,"Please select sub head2.");

        	$.validator.addClassRules({ 
        		account_subhead3_item:{  account_subhead3_itemR:  true,},
        			submitHandler: function (form) {   return false;   }  
        	});
        	$.validator.addMethod("account_subhead3_itemR", $.validator.methods.required,"Please select sub head3.");
        */


        $.validator.addClassRules({
            hsn_code_item: {
                hsn_code_itemR: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("hsn_code_itemR", $.validator.methods.required, "Please enter hsn code.");

        $.validator.addClassRules({
            quntity_item: {
                quntity_itemR: true,
                zero: true,
                number: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("quntity_itemR", $.validator.methods.required, "Please enter quntity.");

        $.validator.addClassRules({
            rate_item: {
                rate_itemR: true,
                zero: true,
                decimal: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("rate_itemR", $.validator.methods.required, "Please enter item rate.");

        $.validator.addClassRules({
            amount_item: {
                amount_itemR: true,
                zero: true,
                decimal: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("amount_itemR", $.validator.methods.required, "Please enter amount.");

        $.validator.addClassRules({
            taxable_item: {
                taxable_itemR: true,
                zero: true,
                decimal: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("taxable_itemR", $.validator.methods.required, "Please enter taxable value.");


        $.validator.addClassRules({
            total_amount_pay: {
                total_amount_payR: true,
                zero: true,
                decimal: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("total_amount_payR", $.validator.methods.required, "Please enter total amount.");

        $.validator.addClassRules({
            discount_item: {
                discount_itemR: true,
                decimal: true,
            },
            submitHandler: function(form) {
                return false;
            }
        });
        $.validator.addMethod("discount_itemR", $.validator.methods.required, "Please enter discount.");



        $.validator.addMethod("chk_dis100", function(value, element, p) {

            d_type1 = $('#total_discount_type').val();
            d_val1 = $('#total_discount_val').val();

            if (d_type1 == 0) {
                $.validator.messages.chk_dis100 = "";
                result = true;
            } else {
                if (d_val1 > 100) {
                    $.validator.messages.chk_dis100 = "Percentage should not be greater than 100";
                    result = false;
                } else {
                    $.validator.messages.chk_dis100 = "";
                    result = true;
                }

            }
            return result;
        }, "");


        $.validator.addMethod("itemgst100", function(value, element, p) {

            d_val1 = $('#gst').val();
            d_type1 = $('#gst_type').val();

            if (d_type1 == 0) {
                $.validator.messages.itemgst100 = "";
                result = true;
            } else {
                if (d_val1 > 100) {
                    $.validator.messages.itemgst100 = "Percentage should not be greater than 100";
                    result = false;
                } else {
                    $.validator.messages.itemgst100 = "";
                    result = true;
                }

            }
            return result;
        }, "");


        $.validator.addMethod("itemigst100", function(value, element, p) {

            d_val1 = $('#igst').val();
            d_type1 = $('#igst_type').val();

            if (d_type1 == 0) {
                $.validator.messages.itemigst100 = "";
                result = true;
            } else {
                if (d_val1 > 100) {
                    $.validator.messages.itemigst100 = "Percentage should not be greater than 100";
                    result = false;
                } else {
                    $.validator.messages.itemigst100 = "";
                    result = true;
                }

            }
            return result;
        }, "");
        /**************************************************************************** */
        $('#billPay').validate({
            rules: {
                vendor_id: "required",
                bill: "required",
                bill_date: "required",
                branch_id: "required",
                discount: "required",
                tds_per: {
                    required: function(element) {
                        if ($("#tds_head").val() > 0) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    decimal: true,
                    zero1: true,
                },
                total_discount_val: {
                    required: function(element) {
                        if ($("#discount").val() == 2) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    decimal: true,
                    zero: true,
                    chk_dis100: true,
                },
                adj_amount:{
                    decimal: true,
                },
            },
            messages: {
                vendor_id: "Please select vendor.",
                bill: "Please enter bill number.",
                bill_date: "Please select Date.",
                branch_id: "Please select branch.",
                discount: "Please select discount.",
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
            submitHandler: function(form) {
                $('#save_bill').prop('disabled', true);
                return true;
            }
        });
        $("#bill-expense-table").on('click', '.remCF', function() {
            //alert($(this).val());
            $(this).parent().parent().remove();
            total_calculateSub();
        });
        $('#item_add').validate({
            rules: {
                name: "required",
                hsn_code: "required",
                type: "required",
                cost_pirce: {
                    required: true,
                    decimal: true,
                    zero1: true,
                },
                account_head: "required",
                //account_subhead1: "required", 
                //account_subhead2: "required", 
                //account_subhead3: "required", 
                description: "required",
                gst: {
                    decimal: true,
                    itemgst100: true,
                },
                igst: {
                    decimal: true,
                    itemigst100: true,
                },
            },
            messages: {
                name: "Please enter name.",
                hsn_code: "Please enter hsn code.",
                type: "Please select type.",
                cost_pirce: {
                    required: "Please enter cost price.",
                },
                account_head: "Please select Account Head.",
                //account_subhead1: "Please select Account Subhead1.", 
                //account_subhead2: "Please select Account Subhead2.", 
                //account_subhead3: "Please select Account Subhead3.", 
                description: "Please enter description.",
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
        $('#item_add').submit(function() {
            if ($('#item_add').valid()) {
                $('#modal-form').modal('hide');
                let company_id = $('#company_id option:selected').val();
                if (company_id == "") {
                    swal('Warning', "Please select the company first", 'warning');
                    return false;
                }
                var formData = new FormData(document.forms['item_add']);
                formData.append('company_id', company_id);
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.bill.item_create') !!}",
                    dataType: 'JSON',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.msg_type == 'success') {

                            a = $("#itemCount").val();
                            $('#item_id' + a).find('option').remove();
                            $('#item_id' + a).append(
                                '<option value="">--- Select Item ---</option>');
                            $.each(response.data, function(index, value) {
                                $("#item_id" + a).append("<option value='" + value
                                    .id + "'>" + value.name + "</option>");
                            });
                            swal("Success!", "Item Create Successfully", "success");
                            $('#item_id' + a).val(response.id);
                            $('#item_id' + a).trigger("change");
                            location.reload();
                        } else {
                            swal("Error!", "Something wrong! " + " " + response.error,
                                "error");
                        }
                    }
                });
            }
            return false;
        });
        $("#account_head").change(function() {
            var id = $(this).val();
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $("#account_head").val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_subhead1').find('option').remove();
                    $('#account_subhead1').append(
                        '<option value="">Select sub head1</option>');
                    $.each(response.account_heads, function(index, value) {
                        $('#account_subhead1').append("<option value='" + value
                            .head_id + "'>" + value.sub_head + "</option>");
                    });
                }
            })
        });
        $("#account_subhead1").change(function() {
            var id = $(this).val();
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $("#account_subhead1").val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_subhead2').find('option').remove();
                    $("#account_subhead2").append(
                        '<option value="">Select sub head2</option>');
                    $.each(response.account_heads, function(index, value) {
                        $("#account_subhead2").append("<option value='" + value
                            .head_id + "'>" + value.sub_head + "</option>");
                    });
                }
            })
        });
        $("#account_subhead2").change(function() {
            var id = $(this).val();
            let company_id = $('#company_id option:selected').val();
            if (company_id == "") {
                swal('Warning', 'Please select the company first', 'warning');
                $("#account_subhead2").val('');
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#account_subhead3').find('option').remove();
                    $('#account_subhead3').append(
                        '<option value="">Select sub head3</option>');
                    $.each(response.account_heads, function(index, value) {
                        $('#account_subhead3').append("<option value='" + value
                            .head_id + "'>" + value.sub_head + "</option>");
                    });
                }
            })
        });
        $("#gst").keyup(function() {
            var gst_val = $('#gst').val();
            cgst = sgst = 0;
            if (parseFloat(gst_val) > 0) {
                cgst = sgst = parseFloat(gst_val / 2).toFixed(2);
            } else {
                cgst = sgst = parseFloat(0).toFixed(2);
            }
            $('#cgst').val(cgst);
            $('#sgst').val(sgst);

            if ($('#gst').val() != '') {
                $('#igst').attr('readonly', true);
            } else {
                $('#igst').attr('readonly', false);
                $('#gst').attr('readonly', false);
            }
        });
        $("#igst").keyup(function() {
            if ($('#igst').val() != '') {
                $('#gst').attr('readonly', true);
            } else {
                $('#igst').attr('readonly', false);
                $('#gst').attr('readonly', false);
            }
        });
        itemGet('{{ count($billItem) }}');
    });

    function itemGet(length) {
        for (let i = 0; i < length; i++) {
            var currentItemID = $('#item_id' + i).val();
            var itemIdrow = $('#itemIdrow' + i).val();
            var currentItemRow = i;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_item_details_edit') !!}",
                dataType: 'JSON',
                data: {
                    'item_id': currentItemID,
                    'currentItemRow': currentItemRow,
                    'itemIdrow': itemIdrow
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response.msg_type);
                    if (response.msg_type == 'success') {
                        $("#trRow" + i).find('.td_remove').remove();
                        $("#tdRow" + i).after(response.view);
                        var newRowItem = parseInt(i) + 1;
                        //	$("#itemCount").val(newRowItem); 
                        $(".account_head_item").trigger("change");
                        if ($("#discount").val() == 2) {
                            $(".tran_div_discount").css("display", "block");
                            $(".discountField").css("display", "none");
                            $('.discount_item').val(0);
                        } else {
                            $(".tran_div_discount").css("display", "none");
                            $(".discountField").css("display", "block");
                        }
                        total_calculateSub();
                    } else {
                        swal("Error!", "" + response.view + "", "error");
                    }
                }
            });
        }
    }

    function amount_change(index_val) {
        quntity = $('#quntity_item_' + index_val).val();
        rate = $('#rate_item_' + index_val).val();
        val_amount = parseFloat(quntity * rate).toFixed(2);
        $('#amount_item_' + index_val).val(val_amount);
        amount = $('#amount_item_' + index_val).val();
        taxable_amount = $('#taxable_item_' + index_val).val();
        discount = $('#discount').val();
        if (discount == 1) {
            d_type = $('#discount_item_type_' + index_val).val();
            d_val = $('#discount_item_' + index_val).val();

            if (d_type == 1) {

                discount_amount = parseFloat(amount * d_val / 100).toFixed(2);

            } else {
                discount_amount = parseFloat(d_val).toFixed(2);
            }
        } else {
            discount_amount = parseFloat(0).toFixed(2);
        }
        tax_amount = parseFloat(parseFloat(amount).toFixed(2) - parseFloat(discount_amount).toFixed(2)).toFixed(2);
        $('#taxable_item_' + index_val).val(tax_amount);
        gst_divide(index_val)
    }

    function gst_divide(index_val) {
        var gst_type = $('#gst_item_type_' + index_val).val();
        var gst_val = $('#gst_item_' + index_val).val();
        if (gst_val > 0) {
            cgst = sgst = parseFloat(gst_val / 2).toFixed(2);
            if (cgst != NaN && cgst > 0) {
                $('#cgst_item_' + index_val).val(cgst);
                $('#sgst_item_' + index_val).val(sgst);
                total_calculate(index_val);
            }
        } else {
            cgst = sgst = parseFloat(0).toFixed(2);
            $('#cgst_item_' + index_val).val(cgst);
            $('#sgst_item_' + index_val).val(sgst);
            total_calculate(index_val);
        }
    }

    function total_calculate(index_val) {
        tax_amount = $('#taxable_item_' + index_val).val();
        gst_type = $('#gst_item_type_' + index_val).val();
        gst_val = $('#gst_item_' + index_val).val();
        if (gst_type == 1) {
            gst_amount = tax_amount * gst_val / 100;
        } else {
            gst_amount = gst_val;
        }
        var igst_type = $('#igst_item_type_' + index_val).val();
        var igst_val = $('#igst_item_' + index_val).val();
        if (igst_type == 1) {
            igst_amount = tax_amount * igst_val / 100;
        } else {
            igst_amount = igst_val;
        }
        tax = parseFloat(0).toFixed(2);
        if (gst_amount > 0) {
            tax = parseFloat(gst_amount).toFixed(2);
        }
        if (igst_amount > 0) {
            tax = parseFloat(igst_amount).toFixed(2);
        }
        total_pay1 = parseFloat(parseFloat(tax_amount) + parseFloat(tax)).toFixed(2);
        $('#total_amount_pay_' + index_val).val(total_pay1);
        total_calculateSub();
    }

    function total_calculateSub() {
        var total = 0;
        let taxable_amount = 0;
        $(".taxable_item").each(function() {
            taxable_amount += parseFloat($(this).val());
        });
        $('#total_taxable_value').val(taxable_amount);
        $(".total_amount_pay").each(function() {
            total += parseFloat($(this).val());
        });
        if (total == 0) {
            $('#total_sub').val('0.00');
        } else {
            total1 = parseFloat(total).toFixed(2);
            $('#total_sub').val(total1);
        }
        totalDiscountGet();
        tdsGet();
        //totalPayAmount();   
    }

    function totalDiscountGet() {
        discount = $('#discount').val();
        sub_total = $('#total_taxable_value').val();
        if (discount == 2) {
            d_type1 = $('#total_discount_type').val();
            d_val1 = $('#total_discount_val').val();
            if (d_type1 == 1) {
                discount_amount = parseFloat(sub_total * d_val1 / 100).toFixed(2);
            } else {
                discount_amount = parseFloat(d_val1).toFixed(2);
            }
            if (discount_amount > 0 && discount_amount != NaN) {
                $('#total_dis_amt').val(discount_amount);
            } else {
                $('#total_dis_amt').val(0)
            }
        } else {
            discount_amount = parseFloat(0).toFixed(2);
            $('#total_dis_amt').val(discount_amount)
        }
        totalPayAmount();
    }

    function tdsGet() {
        tds_per = $('#tds_per').val();
        let parentId = $('#tds_head option:selected').attr('data-parentId');
        console.log(parentId);
        if (tds_per > 100) {
            $('#msg3').html('Percentage should not be greater than 100');
            $('#tds_per').val('');
            return false;
        }
        tds_amt = parseFloat(sub_total * tds_per / 100).toFixed(2);
        $('#tds_amt').val('-' + tds_amt);
        if (parentId == '261') {
            $('#tds_amt').val('+' + tds_amt);
        } else {
            $('#tds_amt').val('-' + tds_amt);
        }
        $('#tds_amt_final').val(tds_amt);
        totalPayAmount();
    }

    function totalPayAmount() {
        sub_total = $('#total_taxable_value').val();
        tds_amt = $('#tds_amt').val();
        final_adj_amount = $('#final_adj_amount').val();
        if (discount == 2) {
            total_dis_amt = $('#total_dis_amt').val();
        } else {
            total_dis_amt = 0;
        }
        substring = "-";
        sub_total1 = $('#total_sub').val();
        tt = parseFloat(sub_total1) + parseFloat(tds_amt) - parseFloat(total_dis_amt) + parseFloat(final_adj_amount);
        tt = parseFloat(tt).toFixed(2);
        if (tt == 'NaN') {
            $('#total_amountPay').val(0);
        } else {
            $('#total_amountPay').val(tt);
        }
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
            return false;
        return true;
    }

    function integerTrue(evt) {
        var integerPattern = /^-?\d+$/;
        return integerPattern.test(evt.key);
    }
</script>
