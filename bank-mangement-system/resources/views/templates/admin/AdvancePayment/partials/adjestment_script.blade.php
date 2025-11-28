<script type="text/javascript">
    var expense;
    "use strict"

    // $("#submit").on('click', function() {
    // 	$('#form').submit();
    // 	// $(this).attr('disabled');
    // });


    $(document).ready(function() {
        $("#switch").change(function() {
            // You can perform actions when the switch is toggled here
            if ($(this).is(":checked")) {
                // Switch is ON
                $("#full_pay").val(1);
                $(".pDate").val('');
                $(".t_description").val('');
                console.log("Switch is ON");
                adjpaydate();
            } else {
                // Switch is OFF
                $("#full_pay").val(0);
                console.log("Switch is OFF");
            }
            $("#payment_mode option[value='SSB']").addClass('d-none');
            $("#payment_mode").val('');
            $("#transfer_mode").val('');
            $("#tmode").hide();
            $("#tmode").val('');
            $('#utr').hide();
            $('#utr').val('');
            $('#rtgs').hide();
            $('#rtgs').val('');
            $('#tamount').hide();
            $('#tamount').val('');
            $('#bankss').hide();
            $('#bankss').val('');
            $('#accourid').hide();
            $('#accourid').val('');
            $('#bankbalance').hide();
            $('#bankbalance').val('');
            $('#cheque').hide();
            $('#cheque').val('');
            $('#branchBalance2').hide();
            $('#branchBalance2').val('');
            $('.utrnumber').hide();
            $('.utrnumber').val('');
            $('.rtgsnumber').hide();
            $('.rtgsnumber').val('');
            $('#recived_bank').hide();
            $('#r_bank_name').val('');
            $('#r_bank_name').val('');
        });
        $("#switch").click(function() {
            $(".search-table-outter").toggle('slow');
            $(".t_amount").val(0);
            var sum = 0;
            $('.t_amount').each(function() {
                var emp_amount = $('#amount').val();
                if ($(this).val() == 0 || $(this).val() > 0) {
                    sum += Number($(this).val());
                    $('#remaining_amount').val(emp_amount - sum);
                }
                if ($('#remaining_amount').val() < 0) {
                    $('#settle_amt').removeClass('d-none');
                    $('#pay_amount_div').removeClass('d-none');
                    $('#payment_method').text('Pay Payment Mode');
                } else if ($('#remaining_amount').val() > 0) {
                    $('#settle_amt').removeClass('d-none');
                    $('#pay_amount_div').addClass('d-none');
                    $('#payment_method').text('Receive Payment Mode');
                } else if ($('#remaining_amount').val() == 0) {
                    $("#payment_mode").val('');
                }
            });
            $('#total_amount').val(sum);
        })
        const adjdate2 = $("form#adjdate").text();
        $("form#adjdate").val(adjdate2);

        const adjdate = $("#date").val();
        var dateString = adjdate;
        var parts = dateString.split("/");
        var year = parts[2];
        var month = parts[1];
        var day = parts[0];
        var isoDate = year + "/" + month + "/" + day;

        var dd = new Date(isoDate);
        console.log('fas',dd);
        var date11 = $('#adjdate').val();
        var adjestmentdate = $('#adjestmentdate').val();
        // Adjestment date Picker 
        $('#adjdate').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            startDate: dd,
            endDate: date11,
            minDate: 0

        });
        $('#adj_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            startDate: dd,
            endDate: adjestmentdate,
            minDate: 0
        });


        // Initialize the date picker on all instances of .date_more
        $(document).on('focus', '.date_more', function() {
            var today = $('#system_date').val();
            // console.log(today);
            // $('#adjdate').datepicker('setEndDate', today);
            var adjestmentdate = $('#adjestmentdate').val();
            $(this).datepicker({
                format: "dd/mm/yyyy",
                endHighlight: true,
                autoclose: true,
                orientation: "bottom",
                startDate: dd,
                endDate: today,
            });
        });






        // Using for date selection date Picker code
        $("#adjestmentdate").on('mouseover', function() {
            var today = $('.date').val();

            // console.log(today);

            $('#adjestmentdate').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                endDate: today,
                endHighlight: true,
                // maxDate: today,
                startDate: '01/04/2021',
                minDate: 0

            })


        })



        //   
        let Oid = '';
        $('.od_hide').hide();

        $('#adjdate').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            startDate: '01/04/2021',
        });
        $('#adj_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            startDate: '01/04/2021',
        });
        $('#adjdate').hover(function() {
            var today = $('#system_date').val();
            // console.log(today);
            $('#adjdate').datepicker('setEndDate', today);
        });
        $('#adj_date').hover(function() {
            var today = $('#system_date').val();
            // console.log(today);
            $('#adj_date').datepicker('setEndDate', today);
        });
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true

        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true

        });

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



        $('#branch_id').on('change', function() {

            var branch_id = $('#branch_id').val();
            $('#branch_total_balance').val('0.00');
            if (branch_id > 0) {
                var entrydate = $('#adjdate').val();
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
                            'entrydate': $('#created_at').val()
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
            // }



            return result;

        }, "");


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



        // A function to turn all form data into a jquery object
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

        // $.validator.addClassRules("amount_more", {
        // 	required: true,
        // 	number: true
        // });

        //-------------------------------------


        $('#form').validate({ // initialize the plugin

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
                    approveAmountLessThanTotal: false,
                    number: true,
                },
                'account_head_more[0]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'amount_more[0]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    digits: true,
                },
                'description_more[0]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'date_more[0]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'account_head_more[1]': {
                    required: function() {
                        if ($("#account_head_1").data('value') >= 2 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'amount_more[1]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    digits: true,
                },
                'description_more[1]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'date_more[1]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },

                'account_head_more[2]': {
                    required: function() {
                        if ($("#account_head_1").data('value') >= 2 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'amount_more[2]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    digits: true,
                },
                'description_more[2]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'date_more[2]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },

                'account_head_more[3]': {
                    required: function() {
                        if ($("#account_head_1").data('value') >= 2 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'amount_more[3]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    digits: true,
                },
                'description_more[3]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'date_more[3]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },

                'account_head_more[4]': {
                    required: function() {
                        if ($("#account_head_1").data('value') >= 2 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'amount_more[4]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    digits: true,
                },
                'description_more[4]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                // 'img[]': {
                //     required: function() {
                //         if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                //             return true;
                //         } else {
                //             return false;
                //         }
                //     },
                // },
                'date_more[4]': {
                    required: function() {
                        if ($("#account_head_0").data('value') >= 1 || (!$("#switch").is(":checked"))) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                'payment_mode': {
                    required: function(e) {
                        if (($("#remaining_amount").val() < 0)) {
                            return true;
                        } else if (($("#full_pay").val() == 1)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
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
                            return false;
                        }
                    }
                },
                'r_bank_name': {
                    required: function(e) {
                        if (($("#remaining_amount").val() < 0) && ($("#transfer_mode").val() == 1)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },

                'cheque_id': {
                    required: function(element4) {
                        if (($("#transfer_mode").val() == 0)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },

                'utr_tran': {
                    required: function(element4) {
                        if (($("#transfer_mode").val() == 1)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                'r_account_id': {
                    required: function(element4) {
                        if (($("#remaining_amount").val() > 0) && ($("#transfer_mode").val() == 1)) {
                            return true;
                        } else if (($("#full_pay").val() == 1)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    digits: true,
                },
                'r_bank_name': {
                    required: function(element4) {
                        if (($("#remaining_amount").val() > 0) && ($("#transfer_mode").val() == 1)) {
                            return true;
                        } else if (($("#full_pay").val() == 1)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                // 'payment_mode': {
                //     required: function(element4) {
                //         if (($("#full_pay").val() == 1)) {
                //             return true;
                //         } else {
                //             return false;
                //         }
                //     }
                // },

            },

            messages: {
                date: {
                    required: "Please  Select Date.",
                },
                total_amount: {
                    approveAmountLessThanTotal: "Total Amount must be less than or equal to the Approve Amount.",
                }
            },

            submitHandler: function(form) {
                const formData = new FormData(form);

                // Get the image file input
                const imageInput = $('input[name="img"]')[0];
                const imageFiles = imageInput.files;

                // Append the image files to the FormData object
                for (let i = 0; i <= imageFiles.length; i++) {
                    formData.append('images[]', imageFiles[i]);
                }

                // Make the Ajax request
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.advancePaymentAdjestment.save') !!}",
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
                        console.error(error);
                        return false;


                    }

                });


            }

        });


		$(document).on('click', '.remCF', function() {
			// const delid = $(this).data('id');
			// $("#account_head_"+delid).prop('required',true);
			// $("#sub_head1"+delid).prop('required',true);
			// $("#amount_more"+delid).prop('required',true);
			// $("#date_more"+delid).prop('required',true);
			//alert($(this).val());
			$(this).parent().parent().remove();
			$(".t_amount").trigger("keyup");
		});



        var a = 0;
        var b = 0;

        $("#add_row").click(function() {

            b++;

            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.getHeads') !!}",
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function(response) {
                    // const obj = JSON.parse(response);
                    // console.log(obj);
                    var len = response.length;
                    // console.log(response);

                    var expendHtml = '<tr>';

                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg">  <select name="account_head_more[' +
                        a + ']" id="account_head_' + a +
                        '" data-append="no" class="account_head_more form-control" data-value=' +
                        b +
                        ' > <option value="">Select Account Head</option><option value="1" data-name="expence">Expense</option><option value="2" data-name="fixedasset">Assets</option></select></div>';

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
                        '<td > <div class="col-lg-12 error-msg">  <select name="sub_head3_more[' +
                        a + ']" id="sub_head3' + a + '" class="form-control  ' + b +
                        '-sub_head3_more sub_head3_more"  data-value=' + b +
                        ' > <option value="0">Select Sub Head3</option>';

                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg">  <select name="sub_head4_more[' +
                        a + ']" id="sub_head4' + a + '" class="form-control  ' + b +
                        '-sub_head4_more sub_head4_more"  data-value=' + b +
                        ' > <option value="0">Select Sub Head4</option>';

                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg">  <select name="sub_head5_more[' +
                        a + ']" id="sub_head5' + a + '" class="form-control  ' + b +
                        '-sub_head5_more sub_head5_more"  data-value=' + b +
                        ' > <option value="0">Select Sub Head5</option>';

                    expendHtml +=
                        '<td > <div class="col-lg-12 error-msg"> <input type="text" id="amount_more' +
                        a + '" name="amount_more[' + a +
                        ']" class="form-control amount_more t_amount" ></div> </td><td > <div class="col-lg-12 error-msg"> <input type="text" required id="description_more' +
                        a + '" name="description_more[' + a +
                        ']" class="form-control description_more t_description" ></div> </td> <td> <div class="col-lg-12 error-msg"><input type="file" required name="img[' +
                        a +
                        ']" class="form-control t_img frm" /><input type="hidden" name="imgchk[' +
                        a +
                        ']" id="imgchk" class="form-control t_imgchk frm" /></div></td> <td > <div class="col-lg-12 error-msg"> <input readonly type="text" required id="date_more' +
                        a + '"  name="date_more[' + a +
                        ']" class="form-control date_more pDate frm" ></div> </td><td style=""> <button type="button" data-id=' +
                        a +
                        ' class="btn btn-primary remCF ml-3"><i class="icon-trash"></i></button> </td></tr>'

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
        $(document).on('change', '.t_img', function() {
            var ext = $(this).val().split('.').pop().toLowerCase();
            if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'pdf', 'svg', 'webp',
                    'csv'
                ]) == -1) {
                swal({
                    title: 'Error!',
                    text: 'This file is not accepted!',
                    type: 'error'
                });
                $(this).val('');
            }
        });


        $(document).on('change', '.account_head_more', function() {
            var option = '<option value="">Select Sub Head 1</option>';
            var id = $(this).val();
            var index = $(this).attr('data-value');
            var selectedOption = $(this).find(':selected');
            var account_head_name = selectedOption.data('name');
            $('.' + index + '-sub_head1_more').empty();
            var company_id = $('#company_id').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'id': id,
                    'company_id': company_id,
                    'name': account_head_name
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

        $('.valid').on('change', function() {
            var id = $(this).val();

            var account_head_id = $(this).val();
            var selectedOption = $(this).find(':selected');
            var account_head_name = selectedOption.data('name');
            var company_id = $('#company_id').val();


            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'id': account_head_id,
                    'company_id': company_id,
                    'name': account_head_name
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


        $(document).on('change', '.sub_head1_more', function() {
            var option = '<option value="">Select Sub Head 2</option>';
            var id = $(this).val();
            var company_id = $('#company_id').val();
            var index = $(this).attr('data-value');
            $('.' + index + '-sub_head2_more').empty();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id,
                    'company_id': company_id
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
        $(document).on('change', '.sub_head2_more', function() {
            var option = '<option value="">Select Sub Head 3</option>';
            var id = $(this).val();
            var company_id = $('#company_id').val();
            var index = $(this).attr('data-value');
            $('.' + index + '-sub_head3_more').empty();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                    'head_id': id
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
                        $('.' + index + '-sub_head3_more').append(option);
                    } else {
                        $('.' + index + '-sub_head3_more').append(
                            '<option value="">Select Sub Head 3</option>');
                    }
                }
            })
        });
        $(document).on('change', '.sub_head3_more', function() {
            var option = '<option value="">Select Sub Head 4</option>';
            var id = $(this).val();
            var company_id = $('#company_id').val();
            var index = $(this).attr('data-value');
            $('.' + index + '-sub_head4_more').empty();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                    'head_id': id
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
                        $('.' + index + '-sub_head4_more').append(option);
                    } else {
                        $('.' + index + '-sub_head4_more').append(
                            '<option value="">Select Sub Head 4</option>');
                    }
                }
            })
        });
        $(document).on('change', '.sub_head4_more', function() {
            var option = '<option value="">Select Sub Head 5</option>';
            var id = $(this).val();
            var company_id = $('#company_id').val();
            var index = $(this).attr('data-value');
            $('.' + index + '-sub_head5_more').empty();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                    'head_id': id
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
                        $('.' + index + '-sub_head5_more').append(option);
                    } else {
                        $('.' + index + '-sub_head5_more').append(
                            '<option value="">Select Sub Head 5</option>');
                    }
                }
            })
        });

        $('#neft_charge').on("keyup", function() {
            var sum = 0;
            $('.t_amount').each(function() {
                if ($(this).val() == 0 || $(this).val() > 0) {
                    sum += Number($(this).val());
                }
            });
            $('#total_amount').val(sum);
        });

        $('#expense1').on("keyup", ".t_amount", function() {
            var sum = 0;
            $('.t_amount').each(function() {
                var emp_amount = $('#amount').val();
                if ($(this).val() == 0 || $(this).val() > 0) {
                    sum += Number($(this).val());
                    $('#remaining_amount').val(emp_amount - sum);
                }
                if ($('#remaining_amount').val() <= 0) {
                    $('#settle_amt').removeClass('d-none');
                    $('#pay_amount_div').removeClass('d-none');
                    $('#payment_method').text('Pay Payment Mode');
                    $("#payment_mode option[value='SSB']").removeClass('d-none');
                } else if ($('#remaining_amount').val() > 0) {
                    $('#settle_amt').removeClass('d-none');
                    $('#pay_amount_div').addClass('d-none');
                    $('#payment_method').text('Receive Payment Mode');
                    $("#payment_mode option[value='SSB']").addClass('d-none');
                }
            });
            $('#total_amount').val(sum);
            const pay_mod = $("#payment_mode").val();
            if (pay_mod == "") {
                return false;
            } else {
                $("#transfer_mode").val('');
                $("#payment_mode").val('');
                $("#adj_date").val('');
                $("#tmode").hide();
                $("#tmode").val('');
                $('#utr').hide();
                $('#utr').val('');
                $('#rtgs').hide();
                $('#rtgs').val('');
                $('#tamount').hide();
                $('#tamount').val('');
                $('#bankss').hide();
                $('#bankss').val('');
                $('#accourid').hide();
                $('#accourid').val('');
                $('#bankbalance').hide();
                $('#bankbalance').val('');
                $('#cheque').hide();
                $('#cheque').val('');
                $('#branchBalance2').hide();
                $('#branchBalance2').val('');
                $('.utrnumber').hide();
                $('.utrnumber').val('');
                $('.rtgsnumber').hide();
                $('.rtgsnumber').val('');
                $('#recived_bank').hide();
                $('#r_bank_name').val('');
                $('#r_bank_name').val('');
                // $("#payment_mode").trigger('change');
                // $("#transfer_mode").trigger('change');
            }
        });

        $('#sub_head1').on('change', function() {
            var id = $(this).val();
            var company_id = $('#company_id').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
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

        $('#sub_head2').on('change', function() {
            var id = $(this).val();
            var company_id = $('#company_id').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#sub_head3').find('option').remove();
                    $('#sub_head3').append('<option value="">Select sub head3</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#sub_head3").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            })
        })

        $('#sub_head3').on('change', function() {
            var id = $(this).val();
            var company_id = $('#company_id').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#sub_head4').find('option').remove();
                    $('#sub_head4').append('<option value="">Select sub head4</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#sub_head4").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            })
        })

        $('#sub_head4').on('change', function() {
            var id = $(this).val();
            var company_id = $('#company_id').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.advancePayment.get_indirect_expense_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#sub_head5').find('option').remove();
                    $('#sub_head5').append('<option value="">Select sub head5</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#sub_head5").append("<option value='" + value.head_id +
                            "'>" + value.sub_head + "</option>");
                    });
                }
            })
        })

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
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'bill_date',
                    name: 'bill_date'
                },
                {
                    data: 'bill_no',
                    name: 'bill_no'
                },
                {
                    data: 'party_name',
                    name: 'party_name'
                },
                {
                    data: 'party_bank_name',
                    name: 'party_bank_name'
                },
                {
                    data: 'party_bank_ac_no',
                    name: 'party_bank_ac_no'
                },
                {
                    data: 'party_bank_ifsc',
                    name: 'party_bank_ifsc'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'utr_no',
                    name: 'utr_no'
                },
                {
                    data: 'neft_charge',
                    name: 'neft_charge'
                },
                {
                    data: 'total_expense',
                    name: 'total_expense'
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

            ]
        })
        $(expense.table().container()).removeClass('form-inline');



        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });


        $('input.adjdate').datepicker().on('change', function(ev) {
            var daybook = 0;
            var entrydate = $(this).val();
            var branch_id = $('#branch_id').val();

            var payment_mode = $('#payment_mode').val();

            if (branch_id != '' && payment_mode != '') {
                if (payment_mode == 0) {
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
                        }
                    });
                }
            }
        });

        $('#branch_id').on('change', function() {
            var daybook = 0;
            var branch_id = $('#branch_id').val();
            var entrydate = $('#adjdate').val();
            $('.cash_box').hide();

            $('#payment_mode').val('');

            if (branch_id > 0) {
                if (entrydate == '') {
                    $('#branch_id').val('');
                    swal("Warning!", "Please select  payment date first!!", "warning");
                }
            }
        })


        $('.cash_box').hide();
        $('#bank_details').hide();
        $('#payment_mode').on('change', function() {
            $('.od_hide').hide();
            var daybook = 0;
            var branch_id = $('#branch_id').val();
            var entrydate = $('#adj_date').val();
            var payment_mode = $('#payment_mode').val();

            $('.cash_box').hide();
            $('#bank_details').hide();

            $('#branch_total_balance').val('0.00');
            if ($("#remaining_amount").val() == 0) {
                swal("Warning!", "Remaining amount is zero", "warning");
                $('#payment_mode').val('');
                return false;
            }
            if (branch_id != '' && entrydate != '') {
                if (branch_id > 0) {
                    if (payment_mode != '') {
                        if (payment_mode == 0) {
                            $('.cash_box').show();
                            $('#bank_details').hide();

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
                                    // alert(response.balance);
                                    $('#branch_total_balance').val(response.balance);
                                }
                            });
                        } else {
                            $('.cash_box').hide();
                            $('#bank_details').show();
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
            } else {
                swal("Warning!", "Please select payment date first", "warning");
                $('#payment_mode').val('');
                return false;
            }
        });

        $("#transfer_mode").on('change', function() {
            if ($(this).val() == 0 && ($("#remaining_amount").val() < 0)) {
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
            $('#recived_bank').hide();

                $('#cheque').removeAttr('style');
                $('#cheque_detail').hide();
                $('.p-mode').hide();

                // $('#tamount').hide();
            } else if ($(this).val() == 1) {
                $('#cheque_detail').hide();
                $('.p-mode').hide();
                $('#bankss').show();
                $('#bankbalance').show();
                $('.utrnumber').show();
                if ($('#full_pay').val() == 0) {
                    $('.rtgsnumber').show();
                }
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
            } else if ($(this).val() == 0 && ($("#remaining_amount").val() > 0)) {
                $('.p-mode').show();
                $('#ssb').show();
                $('#recived_bank').hide();
                // $("#tmode").hide();
                // $("#tmode").val('');
                $("#bankss").hide();
                $("#accourid").hide();
                $(".bankbalance ").hide();
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();
                $('#tamount').hide();
                // $('#cheque').hide();
                $('#branchBalance2').hide();
                $('#recived_bank').hide();
                var companyId = $('#company_id').val();
                var branchId = $('#branch_id').val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.approve_recived_cheque_lists') !!}",
                    dataType: 'JSON',
                    data: {
                        'companyId': companyId,
                        'branch_id': branchId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#cheque_id_r').find('option').remove();
                        $('#cheque_id_r').append(
                            '<option value="">Select cheque number</option>');
                        $.each(response.cheque, function(index, value) {
                            $("#cheque_id_r").append("<option value='" + value.id +
                                "'>" + value.cheque_no + "  ( " + parseFloat(
                                    value.amount).toFixed(2) + ")</option>");
                        });
                    }
                });
            }
        });
        $(document).on('change', '#cheque_id_r', function() {
            var cheque_id_r = $('#cheque_id_r').val();
            const amount = $('#remaining_amount').val();
            $('#cheque-number').val('');
            $('#bank-name').val('');
            $('#branch-name').val('');
            $('#cheque-date').val('');
            $('#cheque-amt').val('');
            $.ajax({
                type: "POST",
                url: "{!! route('admin.approve_cheques_details') !!}",
                dataType: 'JSON',
                data: {
                    'cheque_id': cheque_id_r
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    //alert(response.id);
                    if (parseFloat(amount).toFixed(2) == parseFloat(response.amount)
                        .toFixed(2)) {
                        $('#cheque-number').val(response.cheque_no);
                        $('#bank-name').val(response.bank_name);
                        $('#bank_name_id').val(response.bank_name_id);
                        $('#bank_ac_id').val(response.bank_ac_id);
                        $('#branch-name').val(response.branch_name);
                        $('#cheque-date').val(response.cheque_create_date);
                        $('#cheque-amt').val(parseFloat(response.amount).toFixed(2));
                        $('#deposit_bank_name').val(response.deposit_bank_name);
                        $('#deposit_bank_account').val(response.deposite_bank_acc);
                        $('#cheque_detail').show();
                    } else {
                        $("#cheque_id_r").val('');
                        swal("Warning!", "Cheque amould Should be same as Deposit Amount!",
                            "warning");
                        return false;
                    }

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
                        $('#cheque_id').append(
                            '<option value="">Select cheque number</option>');
                        $.each(response.chequeListAcc, function(index, value) {
                            $("#cheque_id").append("<option value='" + value.id +
                                "'>" + value.cheque_no + "</option>");
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
                        'companyId': company_id,
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


        $(document).on('change', '#bank_id', function() {
            var bank_id = $('#bank_id').val();
            $('.od_hide').hide();
            $('#bank_balance').val('0.00');
            $('#bank_od_balance').val('0.00');
            // $('#is_od').val('');
            $('#cheque_id').find('option').remove();
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
                    $('#account_id').append(
                        '<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#account_id").append("<option value='" + value.id +
                            "'>" + value.account_no + "</option>");
                    });
                }
            });
        });


        $(document).on('click', '.delete_expense', function() {
            var expense_id = $(this).attr("data-row-id");
            var title = $(this).attr("title");


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
                            url: "{!! route('admin.expense.deleteBill') !!}",
                            dataType: 'JSON',
                            data: {
                                'bill_no': expense_id,
                                'title': title
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
        $("#payment_mode").on('change', function() {
            $('.p-mode').hide();
            $('#recived_bank').hide();
            $('#cheque_detail').hide();
            if ($(this).val() == 'SSB') {
                var ssbno = $('#ssbno').val();

                if (ssbno == '') {
                    swal("Warning!", "SSB account not available please select another method",
                        "warning");
                    $(this).val("");
                    return false;
                }

                $('#ssb').show();
                $("#tmode").hide();
                $("#tmode").val('');
                $("#bankss").hide();
                $("#accourid").hide();
                $(".bankbalance ").hide();
                $('.utrnumber').hide();
                $('.rtgsnumber').hide();
                $('#tamount').hide();
                $('#cheque').hide();
                $('#branchBalance2').hide();
                $('#recived_bank').hide();
                $('#tasubmit').removeClass('d-none');
            } else if ($(this).val() == 'BANK') {
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
        $('#switch').prop('disabled', false);

        // Datepicker According to Head entry
        $(document).on('change', '.pDate', function() {
            adjpaydate();
        });
        function adjpaydate(){
            const allDates = [];
            $(".pDate").each(function() {
                var inputValue = $(this).val();
                if (inputValue != '') {
                    var formattedDate = inputValue.replace(/^(\d{2})\/(\d{2})\/(\d{4})$/, "$2/$1/$3");
                } else {
                    var adjpreviouse = $('#date').val();
                    var formattedDate = adjpreviouse.replace(/^(\d{2})\/(\d{2})\/(\d{4})$/, "$2/$1/$3");
                }
                // var formattedDate = inputValue.replace(/^(\d{2})\/(\d{2})\/(\d{4})$/, "$2/$1/$3");
                allDates.push(formattedDate);
            });
            console.log(allDates);
            // Sort the array in ascending order
            allDates.sort(function(a, b) {
                var dateA = new Date(a);
                var dateB = new Date(b);
                return dateA - dateB;
            });

            console.log(allDates);
            
            
            
            var lastDate = allDates[allDates.length - 1];
            
            // Convert lastDate to "DD-MM-YY" format
            console.log(lastDate);
            var dateParts = lastDate.split("/");
            console.log(dateParts);
            var formattedLastDate = dateParts[1] + '/' + dateParts[0] + '/' + dateParts[2];
            console.log("Last Date:", formattedLastDate);
            // Set the start date for the datepicker with the ID "adj_date"
            $('#adj_date').datepicker('setStartDate', formattedLastDate);
        }




    })
</script>