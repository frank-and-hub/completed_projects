<script type="text/javascript">
$(document).ready(function() {
    var customAssociateRegister = $('form[name="customAssociateRegister"]')[0];
    var customAssociateRegisterNext = $('form[name="customAssociateRegisterNext"]')[0];
    var date = new Date();
    var today = new Date(date.getFullYear() - 18, date.getMonth(), date.getDate());
    var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    $('#rd_online_date').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true
    });
    $('#ssb_first_dob').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true
    }).on('change', function() {
        var age = getAge(this);
        $('#ssb_first_age').val(age);
        $('.datepicker-dropdown').hide();
    });
    $(".numbersOnly").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    $('#ssb_second_dob').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true 
    }).on('change', function() {
        var age = getAge(this);
        $('#ssb_second_age').val(age);
        $('.datepicker-dropdown').hide();
    });
    $('#rd_first_dob').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true
    }).on('change', function() {
        var age = getAge(this);
        $('#rd_first_age').val(age);
        $('.datepicker-dropdown').hide();
    });
    $('#rd_second_dob').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        endDate: date,
        autoclose: true
    }).on('change', function() {
        var age = getAge(this);
        $('#rd_second_age').val(age);
        $('.datepicker-dropdown').hide();
    });
    function getAge(dateVal) {
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var birthday = moment('' + dateVal.value + ' 00:00', moment.defaultFormat).toDate(),
            today = new Date();
        var year = today.getYear() - birthday.getYear();
        console.log("Date today", today, "birthday", birthday);
        var m = today.getMonth() - birthday.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthday.getDate())) {
            year--;
        }
        return Math.floor(year);
    }
    // validator start
    $.validator.addMethod("greaterThanZero", function(value, element) {
        return this.optional(element) || (parseFloat(value) > 0);
    }, "Amount must be greater than Zero");
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
    $.validator.addMethod("number", function(value, element, p) {
        if (this.optional(element) || /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/g.test(
                value) == true) {
            $.validator.messages.number = "";
            result = true;
        } else {
            $.validator.messages.number = "Please enter valid number.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("customerId", function(value, element, p) {
            var newValue = "";
            for (var i = 0; i < value.length; i++) {
                newValue += value.charAt(i).toUpperCase();
            }
            if (this.optional(element) || /^[0-9]{4}CI[0-9]{6}$/.test(newValue)) {
                $.validator.messages.customerId = "";
                result = true;
            } else {
                $.validator.messages.customerId = "Please enter a valid Customer Id.";
                result = false;
            }
            return result;
    }, "");
    $.validator.addMethod("check_per", function(value, element, p) {
        var val1 = $('#ssb_first_percentage').val();
        var val2 = $('#ssb_second_percentage').val();
        $.validator.messages.check_per = "";
        var sum1 = parseInt(val1) + parseInt(val2);
        if ($('#ssb_second_validate').val() > 0) {
            var sum = parseInt(val1) + parseInt(val2);
            if (sum > 100) {
                result = false;
                $.validator.messages.check_per = "SSB percentage not greater than 100";
            } else {
                result = true;
                $.validator.messages.check_per = "";
            }
        } else {
            if (val1 != 100) {
                result = false;
                $.validator.messages.check_per = "SSB percentage is not less than  or greater than 100";
            } else {
                result = true;
                $.validator.messages.check_per = "";
            }
        }
        return result;
    }, "");
    $.validator.addMethod("check_per_rd", function(value, element, p) {
        var inputValue = parseInt(value) || 0;
        var errorMsg = "";
        var result = true;

        if(inputValue > 100){
            errorMsg = "RD percentage not greater than 100";
            $.validator.messages.check_per_rd = "";
            result = false;
        }

        if(0 < inputValue < 100){
            errorMsg = "RD percentage is not less than or greater than 100";
            $.validator.messages.check_per_rd = "";
            result = false;
        }

        if(inputValue == 100){
            errorMsg  = '';
            result = true;
        }
        $.validator.messages.check_per_rd = errorMsg;
        return result;
    }, "");
    
    $.validator.addMethod("check_ssb_account", function(value, element, p) {
        var member_id = $('#id').val();
        $.ajax({
            type: "POST",
            url: "{!! route('branch.associatessbaccountcheck') !!}",
            dataType: 'JSON',
            data: {
                'account_no': value,
                'member_id': member_id
            },
            success: function(e) {
                if (e.resCount == 0) {
                    re_ssb = false;
                    $.validator.messages.check_ssb_account = "SSB account number wrong";
                } else {
                    re_ssb = true;
                    $.validator.messages.check_ssb_account = "";
                }
            }
        });
        return re_ssb;
    }, "");
    $.validator.addMethod("dateDdMm", function(value, element, p) {
        if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g
            .test(value) == true) {
            $.validator.messages.dateDdMm = "";
            result = true;
        } else {
            $.validator.messages.dateDdMm = "Please enter valid date";
            result = false;
        }
        return result;
    }, "");
    // first form js validator
    $('#customerNameForm').validate({
        rules:{
            customer_id: {required: true,customerId: true},
        },messages:{
            customer_id: {
                required: "Please enter Customer id.",
            },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).addClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).removeClass('is-invalid');
                });
            }
        }
    });
    $('#customAssociateRegister').validate({
        rules: {
            created_at: {required: true},
            id: {required: true},
            form_no: {required: true },
            senior_code: { required: true},
            current_carder: { required: true },
            payment_mode: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_form_no: {required: e => ($('#payment_mode').val() == '') ? false : true,greaterThanZero: true},
            tenure: {required: e => ($('#payment_mode').val() == '') ? false : true,number: true},
            rd_first_first_name: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_second_first_name: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_first_gender: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_second_gender: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_first_relation: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_second_relation: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_first_age: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_second_age: {required: e => ($('#payment_mode').val() == '') ? false : true},
            rd_first_dob: {required: e => ($('#payment_mode').val() == '') ? false : true,dateDdMm: true,},
            rd_second_dob: {required: e => ($('#payment_mode').val() == '') ? false : true,dateDdMm: true,},
            rd_first_mobile_no: {required: e => ($('#payment_mode').val() == '') ? false : true,number: true},
            rd_second_mobile_no: {required: e => ($('#payment_mode').val() == '') ? false : true,number: true},
            rd_first_percentage: {
                required: e => ($('#payment_mode').val() == '') ? false : true,
                greaterThanZero: e => ($('#payment_mode').val() == '') ? false : true,
                check_per_rd: e => ($('#payment_mode').val() == '') ? false : true,
            },
            rd_second_percentage: {
                    required: e => ($('#payment_mode').val() == '') ? false : true,
                    greaterThanZero: e => ($('#payment_mode').val() == '') ? false : true,
                    check_per_rd: e => ($('#payment_mode').val() == '') ? false : true,                
            },
            ssb_amount: {required: e => ($("#ssb_account").val() > 0) ? true : false,},
            ssb_form_no: {required: e => ($("#ssb_account").val() > 0) ? false : true,greaterThanZero: true},
            ssb_first_first_name: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_second_first_name: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_first_gender: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_second_gender: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_first_relation: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_second_relation: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_first_age: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_second_age: {required: e => ($("#ssb_account").val() > 0) ? false : true},
            ssb_first_dob: {required: e => ($("#ssb_account").val() > 0) ? false : true,dateDdMm: true,},
            ssb_second_dob: {required: e => ($("#ssb_account").val() > 0) ? false : true,dateDdMm: true,},
            ssb_first_mobile_no: {required: e => ($("#ssb_account").val() > 0) ? false : true,number: true},
            ssb_second_mobile_no: {required: e => ($("#ssb_account").val() > 0) ? false : true,number: true},
            ssb_first_percentage: {required: e => ($("#ssb_account").val() > 0) ? false : true,greaterThanZero: true,check_per: true},
            ssb_second_percentage: {required: e => ($("#ssb_account").val() > 0) ? false : true,greaterThanZero: true,check_per: true},
            rd_online_bank_id: {required: e => ($("#payment_mode").val() == 2 && $("#rd_account").val() == '0') ? true : false,},
            rd_online_id: {required: e => ($("#payment_mode").val() == 2 && $("#rd_account").val() == '0') ? true : false,},
            rd_online_date: {required: e => ($("#payment_mode").val() == 2 && $("#rd_account").val() == '0') ? true : false,dateDdMm: true,},
            rd_online_bank_ac_id: {required: e => ($("#payment_mode").val() == 2 && $("#rd_account").val() == '0') ? true : false,},
            cheque_id: {required: e => ($("#payment_mode").val() == 1 && $("#rd_account").val() == '0') ? true : false,},
            rd_ssb_account_number: {required: e => ($("#payment_mode").val() == 3 && $("#rd_account").val() == '0') ? true : false,},
            rd_ssb_account_amount: {required: e => ($("#payment_mode").val() == 3 && $("#rd_account").val() == '0') ? true : false,},
        },
        messages: {
            customer_id: {
                required: "Please enter Customer id.",
            },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.error-msg').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).addClass('is-invalid');
                });
            }
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if ($(element).attr('type') == 'radio') {
                $(element.form).find("input[type=radio]").each(function(which) {
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).removeClass('is-invalid');
                });
            }
        }
    });
    $(document).on('change', '#rd_online_bank_id', function () {
      var account = $('option:selected', this).val();
      $('#rd_online_bank_ac_id').val('');
      $('.bank-account').hide();
      $('.' + account + '-bank-account').show();
    });
    // save first form 
    $('#nextButton').on('click', function() {
        var form = $(this).attr('data-form');
        // return false;
        if($('#customerNameForm').valid()){
            if (form == 1) {
                if ($('#customAssociateRegister').valid()) {
                    var formData = new FormData(document.forms['customAssociateRegister']);                   
                    $.ajax({
                        type: "POST",
                        url: "{!! route('branch.associate.store.customer') !!}",
                        dataType: 'JSON',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(e) {
                            console.log(e?.dataMsg,'sucess');
                            if (e?.dataMsg?.msg_type == "success") {
                                $('#nextButton').attr('data-form','2').text('Submit');
                                swal("Success!", "" + e?.dataMsg?.msg + "", "success");
                                $('#senior_code').val(e?.dataMsg?.senior_code);
                                $('#form_no').val(e?.dataMsg?.form_no);
                                $('#ssb_form_no_form').val(e?.dataMsg?.ssb_form_no);
                                $('#recipt_id').val(e?.dataMsg?.recipt_id??'');
                                $('#current_carder2').val(e?.dataMsg?.current_carder);
                                $('#senior_code').keyup();
                                $('#receipt_id').val(e?.dataMsg?.receipt_id);
                                // $('#ssbAccountForm').addClass('d-none');
                                $('#rdAccountInvestment').toggleClass('d-none');
                                $('#customAssociateRegisterNext').toggleClass('d-none');
                            } 
                            if(e?.dataMsg?.msg_type == "error") {
                                $('#nextButton').attr('data-form','1');
                                if (e?.dataMsg?.form > 0) { 
                                    $('#formError').html('<div class="alert alert-danger alert-block">  <strong>' + e?.dataMsg?.errormsg + '</strong> </div>');
                                }
                                swal("Error!", "" + e?.dataMsg?.msg + "", "error");
                            }
                        },error: function(ev){
                            console.log(ev,'error');
                            return false;
                        }
                    });
                }
            }
            if(form == 2){
                $('#current_carder').val(1);
                if ($('#customAssociateRegister').valid()) {
                    var formData2 = new FormData(document.forms['customAssociateRegister']); // with the file input
                    var poData = jQuery(document.forms['customAssociateRegisterNext']).serializeArray();
                    for (var i=0; i<poData.length; i++)
                    formData2.append(poData[i].name, poData[i].value);                    
                    $.ajax({
                    type: "POST",
                        url: "{!! route('branch.associate.dependents.customer') !!}",
                        dataType: 'JSON',
                        data: formData2,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(e) {
                            if (e.msg_type == "success") {
                                var receipt_id = e.receipt_id;
                                customAssociateRegister.reset();
                                customAssociateRegisterNext.reset();
                                $('#nextButton').data('form','1').text('Next'); 
                                $('#rdAccountInvestment').toggleClass('d-none');
                                $('#customAssociateRegisterNext').toggleClass('d-none');
                                swal({
                                    title:'Success',
                                    text: e.msg,
                                    icon:'success',
                                },function(env){
                                    if(env.isConfirmed){
                                        if (receipt_id > 0) {                                            
                                            window.location.href = "{{url('/branch/associate/receipt')}}/" + receipt_id;
                                        } else {
                                            window.location.href = " {!! route('branch.associate_list') !!}";
                                        }
                                    }else{
                                        // window.location.href = " {!! route('branch.associateregistercompany.index') !!}";
                                        window.location.href = " {!! route('branch.associate_list') !!}";
                                    }
                                });
                            } 
                            if (e.msg_type == "error") {
                                $('#nextButton').data('form','2').text('Submit')
                                if (e.form > 0) {
                                    $('#formError').html('<div class="alert alert-danger alert-block">  <strong>' + e.errormsg + '</strong> </div>');
                                }
                                swal("Error!", "" + e.msg + "", "error");
                            }
                        },error : function(e){
                            return false;
                            
                        } 
                    });
                }
                $('#associateFormInformation,#rdAccountInvestment,#ssbAccountForm,#customAssociateRegisterNext').toggleClass('d-none');
            }
        }
        return false;
    });
    // check customer Id and details on entering customer Id
    $(document).on('keyup', '#customer_id', function() {
        $('#show_customer_detail').html('');
        $('#formError').html('');
        var code = $(this).val();
        if (code != '') {
            $.post("{!! route('branch.customerDataGet') !!}", {
                'code': code
            }, function(e) {
                console.log(e);
                if (e.msg_type == "error2") {
                    $('#show_customer_detail').html(
                        '<div class="alert alert-danger alert-block">  <strong>Customer blocked!</strong> </div>'
                    );
                } else {
                    if (e.msg_type == "success") {
                        $('#show_customer_detail').html(e.view);
                        $('#id,#customerRegisterId').val(e.id);
                        $('#ssb_first_first_name_old').val(e.nomineeDetail.name);
                        $('#ssb_first_relation_old').val(e.nomineeDetail.relation);
                        $('#ssb_first_dob_old').val(e.nomineeDOB);
                        $('#ssb_first_age_old').val(e.nomineeDetail.age);
                        $('#ssb_first_mobile_no_old').val(e.nomineeDetail.mobile_no);
                        $('#ssb_first_gender_old').val(e.nomineeDetail.gender);
                        $('#ssb_account').val(e.haveSsbAccount);
                        $('#rd_account').val(e.Rd_Account_Investment);
                        console.log(e.rd_account_number??'');
                        $('#ssbAccountForm').toggleClass('d-none', e.haveSsbAccount > 0);
                        $('#rdAccountInvestment').toggleClass('d-none', e.Rd_Account_Investment > 0);
                        $('#associateFormInformation,#associate').toggleClass('d-none', e?.associate > 0);
                        $('#form_no').val(e?.details?.form_no??'');
                        $('#current_carder2').val(e?.details?.current_carder??'');
                        $('#roi').val(e?.details?.roi??'');
                        $('#senior_code').val(e?.details?.senior_code??'').keyup();
                        $('#tenure').val(e.tenure);
                        $('#tenure').on('change',function(){rdmaturity();});
                        $('#rdPlanCode').val(e.rdPlanCode);
                        $('#rdPlanId').val(e?.rdPlanId);
                        $('#receipt_id').val(e?.details?.receipt_id??'');
                        $('.tenuremonths').text(e.tenure + ' Months');
                        if (e.haveSsbAccount > 0 && e.Rd_Account_Investment > 0) {
                            $('#nextButton').attr('data-form','2').text('Submit');
                            $('#first_g_address').val(e?.details?.address??'');
                            $('#first_g_Mobile_no').val(e?.details?.mobile_no??'');
                            $('#first_g_first_name').val(e?.details?.senior_name??'');
                            $('#customAssociateRegisterNext').removeClass('d-none');
                        } else if (e.haveSsbAccount > 0 || e.Rd_Account_Investment > 0) {
                            $('#nextButton').attr('data-form','1').text('Next');
                            $('#customAssociateRegisterNext').addClass('d-none');
                        } else {
                            $('#nextButton').attr('data-form','1').text('Next');
                            $('#customAssociateRegisterNext').addClass('d-none');
                        }
                    } else if (e.msg_type == "error1") {
                        $('#show_customer_detail').html(
                            '<div class="alert alert-danger alert-block">  <strong>Customer already associate!</strong> </div>'
                        );
                        return false;
                    } else if (e.msg_type == "error3") {
                        $('#customer_id').val('');
                        customAssociateRegister.reset();
                        customAssociateRegisterNext.reset();
                        $('#ssb_account').val('');
                        $('#rd_account').val('');
                        swal('Error','For Associate Creation, it is compulsory to have an SSB Plan in every Company. Please contact the Administrator !','error');
                        return false;
                    } else {
                        $('#show_customer_detail').html(
                            '<div class="alert alert-danger alert-block">  <strong>'+e.view+'</strong> </div>'
                        );
                        return false;
                    }
                }
            }).always(function(){
                check_ssb();
            });
        } else {
            $('#customAssociateRegisterNext').toggleClass('d-none',code == '');
            $('#ssbAccountForm').toggleClass('d-none',code == '');
            $('#rdAccountInvestment').toggleClass('d-none',code == '');
            $('#associateFormInformation').toggleClass('d-none',code == '');
            return false;
        }
    });
    var re_ssb = '';
    $(document).on('keyup', '#rd_account_number', function() {
        var account_no = $(this).val();
        var member_id = $('#id').val();
        $.ajax({
            type: "POST",
            url: "{!! route('branch.associatessbaccountcheck') !!}",
            dataType: 'JSON',
            data: {
                'account_no': account_no,
                'member_id': member_id
            },
            success: function(e) {
                if (e.resCount == 0) {
                    return false;
                }
            }
        })
    });
    $(document).on('keyup', '#ssb_first_percentage', function() {
        var val = $('#ssb_first_percentage').val();
        if (val == '') {
            $('#ssb_second_percentage').val(0);
        } else {
            if (val == 100) {
                $('#second_nominee_ssb').prop("disabled", true);
                $('#ssb_second_percentage').val(0);
            } else {
                $('#second_nominee_ssb').prop("disabled", false);
                var otherVal = parseInt(100 - parseInt(val));
                $('#ssb_second_percentage').val(otherVal);
            }
        }
    });
    $(document).on('change', '#cheque_id', function () {
      $('#rd_cheque_no').val('');
      $('#rd_branch_name').val('');
      $('#rd_bank_name').val('');
      $('#rd_cheque_date').val('');
      $('#cheque-amt').val('');
      var cheque_id = $('#cheque_id').val();
      $.ajax({
        type: "POST",
        url: "{!! route('branch.approve_cheque_detail') !!}",
        dataType: 'JSON',
        data: { 'cheque_id': cheque_id },
        success: function (e) {
          // alert(e.id);
          $('#rd_cheque_no').val(e.cheque_no);
          $('#rd_branch_name').val(e.branch_name);
          $('#rd_bank_name').val(e.bank_name);
          $('#rd_cheque_date').val(e.cheque_create_date);
          $('#cheque-amt').val(parseFloat(e.amount).toFixed(2));
          $('#deposit_bank_name').val(e.deposit_bank_name);
          $('#deposit_bank_account').val(e.deposite_bank_acc);
          $('#cheque_detail').show();
        }
      });
    });
    $(document).on('change', '#payment_mode', function() {
        var val = $('#payment_mode').val();
        $('#payment_mode_cheque').hide();
        $('#payment_mode_online').hide();
        $('#payment_mode_ssb').hide();
        $('#cheque-number').val();
        $('#bank-name').val();
        $('#branch-name').val();
        $('#cheque-date').val();
        if (val == 1) {
            $('#payment_mode_cheque').show();
            $.post("{!! route('branch.approve_recived_cheque_list') !!}", {
                'data': ''
            }, function(e) {
                $('#cheque_id').find('option').remove();
                $('#cheque_id').append('<option value="">Select cheque number</option>');
                $.each(e.cheque, function(index, value) {
                    $("#cheque_id").append("<option value='" + value.id + "'>" + value
                        .cheque_no + "  ( " + parseFloat(value.amount).toFixed(2) +
                        ")</option>");
                });
            }, 'JSON');
        }
        if (val == 2) {
            $('#payment_mode_online').show();
        }
        if (val == 3) {
            $('#payment_mode_ssb').show();
            $('#rd_ssb_account_number').val('');
            $('#rd_ssb_account_amount').val('');
            var rd_amount = $('#rd_amount').val();
            var customerId = $('#id').val();
            $.post("{!! route('branch.associateSsbAccountGet.customer') !!}", {
                'customerId': customerId
            }, function(e) {
                if (e.resCount == 1) {
                    $('#rd_ssb_account_number').val(e.account_no);
                    $('#rd_ssb_account_amount').val(e.balance);
                    if (Math.round(rd_amount) > Math.round(e.balance)) {
                        $('#payment_mode').val('');
                        swal("Error!", "Your SSB account does not have a sufficient balance.",
                            "error");
                        return false;
                    }
                } else {
                    swal("Error!", "Customer SSB account not found!", "error");
                    return false;
                }
            }, 'JSON');
        }
    });
    $(document).on('click', '#second_nominee_ssb', function() {
        $('#ssb_second_no_div').show();
        $('#second_nominee_ssb_remove').show();
        $('#ssb_second_validate').val(1);
        $('#second_nominee_ssb').hide();
    });
    $(document).on('click', '#second_nominee_ssb_remove', function() {
        $('#ssb_second_no_div').hide();
        $('#second_nominee_ssb_remove').hide();
        $('#ssb_second_validate').val(0);
        $('#second_nominee_ssb').show();
    });
    $(document).on('click', '#second_nominee_rd', function() {
        $('#rd_second_no_div').show();
        $('#second_nominee_rd_remove').show();
        $('#rd_second_validate').val(1);
        $('#second_nominee_rd').hide();
    });
    $(document).on('click', '#second_nominee_rd_remove', function() {
        $('#rd_second_no_div').hide();
        $('#second_nominee_rd_remove').hide();
        $('#rd_second_validate').val(0);
        $('#second_nominee_rd').show();
    });
    $(document).on('click', '#old_ssb_no_detail', function() {
        if ($('#old_ssb_no_detail').prop("checked") == true) {
            $('#ssb_first_first_name').val($('#ssb_first_first_name_old').val());
            $('#ssb_first_relation').val($('#ssb_first_relation_old').val());
            $('#ssb_first_dob').val($('#ssb_first_dob_old').val());
            $('#ssb_first_age').val($('#ssb_first_age_old').val());
            $('#ssb_first_mobile_no').val($('#ssb_first_mobile_no_old').val());
            if ($('#ssb_first_gender_old').val() == 1) {
                $('#ssb_first_gender_male').prop("checked", true);
                $('#ssb_first_gender_female').attr('disabled', true);
            } else {
                $('#ssb_first_gender_female').prop("checked", true);
                $('#ssb_first_gender_male').attr('disabled', true);
            }
            $('#ssb_first_first_name').attr('readonly', 'true');
            $('#ssb_first_relation').css('pointer-events', 'none');
            $('#ssb_first_dob').css('pointer-events', 'none');
            $('#ssb_first_relation').attr('readonly', 'true');
            $('#ssb_first_dob').attr('readonly', 'true');
            $('#ssb_first_age').attr('readonly', 'true');
            $('#ssb_first_mobile_no').attr('readonly', 'true');
            $('#ssb_first_gender_male').attr('readonly', 'true');
            $('#ssb_first_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_first_first_name').val('');
            $('#ssb_first_relation').val('');
            $('#ssb_first_dob').val('');
            $('#ssb_first_age').val('');
            $('#ssb_first_mobile_no').val('');
            $('#ssb_first_gender_male').prop("checked", false);
            $('#ssb_first_gender_female').prop("checked", false);
            $('#ssb_first_first_name').removeAttr('readonly');
            $('#ssb_first_relation').css('pointer-events', '');
            $('#ssb_first_dob').css('pointer-events', '');
            $('#ssb_first_relation').removeAttr('readonly');
            $('#ssb_first_dob').removeAttr('readonly');
            $('#ssb_first_age').removeAttr('readonly');
            $('#ssb_first_mobile_no').removeAttr('readonly');
            $('#ssb_first_gender_male').removeAttr('disabled', false);
            $('#ssb_first_gender_female').removeAttr('disabled', false);
        }
    });
    $(document).on('click', '#old_rd_no_detail', function() {
        if ($('#old_rd_no_detail').prop("checked") == true) {
            $('#rd_first_first_name').val($('#ssb_first_first_name_old').val());
            $('#rd_first_relation').val($('#ssb_first_relation_old').val());
            $('#rd_first_dob').val($('#ssb_first_dob_old').val());
            $('#rd_first_age').val($('#ssb_first_age_old').val());
            $('#rd_first_mobile_no').val($('#ssb_first_mobile_no_old').val());
            if ($('#ssb_first_gender_old').val() == 1) {
                $('#rd_first_gender_male').prop("checked", true);
                $('#rd_first_gender_female').attr('disabled', true);
            } else {
                $('#rd_first_gender_female').prop("checked", true);
                $('#rd_first_gender_male').attr('disabled', true);
            }
            $('#rd_first_first_name').attr('readonly', 'true');
            $('#rd_first_relation').css('pointer-events', 'none');
            $('#rd_first_dob').css('pointer-events', 'none');
            $('#rd_first_relation').attr('readonly', 'true');
            $('#rd_first_dob').attr('readonly', 'true');
            $('#rd_first_age').attr('readonly', 'true');
            $('#rd_first_mobile_no').attr('readonly', 'true');
            $('#rd_first_gender_male').attr('readonly', 'true');
            $('#rd_first_gender_female').attr('readonly', 'true');
        } else {
            $('#rd_first_first_name').val('');
            $('#rd_first_relation').val('');
            $('#rd_first_dob').val('');
            $('#rd_first_age').val('');
            $('#rd_first_mobile_no').val('');
            $('#rd_first_gender_male').prop("checked", false);
            $('#rd_first_gender_female').prop("checked", false);
            $('#rd_first_first_name').removeAttr('readonly');
            $('#rd_first_relation').css('pointer-events', '');
            $('#rd_first_dob').css('pointer-events', '');
            $('#rd_first_relation').removeAttr('readonly');
            $('#rd_first_dob').removeAttr('readonly');
            $('#rd_first_age').removeAttr('readonly');
            $('#rd_first_mobile_no').removeAttr('readonly');
            $('#rd_first_gender_male').removeAttr('disabled', false);
            $('#rd_first_gender_female').removeAttr('disabled', false);
        }
    });
    $(document).on('change', '#dep_relation', function() {
        var ids = new Array();
        var ids = ['2', '3', '6', '7', '8', '10', '11'];
        $('#dep_gender_male').removeAttr('disabled', false);
        $('#dep_gender_female').removeAttr('disabled', false);
        $('#dep_gender_male').prop("checked", false);
        $('#dep_gender_female').prop("checked", false);
        $('#dep_gender_male').removeAttr('readonly');
        $('#dep_gender_female').removeAttr('readonly');
        if (ids.includes($(this).val())) {
            $('#dep_gender_male').attr('disabled', true);
            $('#dep_gender_female').prop("checked", true);
            $('#dep_gender_female').attr('readonly', 'true');
        } else {
            $('#dep_gender_female').attr('disabled', true);
            $('#dep_gender_male').prop("checked", true);
            $('#dep_gender_male').attr('readonly', 'true');
        }
    });
    $(document).on('change', '#ssb_first_relation', function() {
        var ids = new Array();
        var ids = ['2', '3', '6', '7', '8', '10', '11'];
        $('#ssb_first_gender_male').removeAttr('disabled', false);
        $('#ssb_first_gender_female').removeAttr('disabled', false);
        $('#ssb_first_gender_male').prop("checked", false);
        $('#ssb_first_gender_female').prop("checked", false);
        $('#ssb_first_gender_male').removeAttr('readonly');
        $('#ssb_first_gender_female').removeAttr('readonly');
        if (ids.includes($(this).val())) {
            $('#ssb_first_gender_male').attr('disabled', true);
            $('#ssb_first_gender_female').prop("checked", true);
            $('#ssb_first_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_first_gender_female').attr('disabled', true);
            $('#ssb_first_gender_male').prop("checked", true);
            $('#ssb_first_gender_male').attr('readonly', 'true');
        }
    });
    $(document).on('change', '#ssb_second_relation', function() {
        var ids = new Array();
        var ids = ['2', '3', '6', '7', '8', '10', '11'];
        $('#ssb_second_gender_male').removeAttr('disabled', false);
        $('#ssb_second_gender_female').removeAttr('disabled', false);
        $('#ssb_second_gender_male').prop("checked", false);
        $('#ssb_second_gender_female').prop("checked", false);
        $('#ssb_second_gender_male').removeAttr('readonly');
        $('#ssb_second_gender_female').removeAttr('readonly');
        if (ids.includes($(this).val())) {
            $('#ssb_second_gender_male').attr('disabled', true);
            $('#ssb_second_gender_female').prop("checked", true);
            $('#ssb_second_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_second_gender_female').attr('disabled', true);
            $('#ssb_second_gender_male').prop("checked", true);
            $('#ssb_second_gender_male').attr('readonly', 'true');
        }
    });
    $(document).on('change', '#rd_first_relation', function() {
        var ids = new Array();
        var ids = ['2', '3', '6', '7', '8', '10', '11'];
        $('#rd_first_gender_male').removeAttr('disabled', false);
        $('#rd_first_gender_female').removeAttr('disabled', false);
        $('#rd_first_gender_male').prop("checked", false);
        $('#rd_first_gender_female').prop("checked", false);
        $('#rd_first_gender_male').removeAttr('readonly');
        $('#rd_first_gender_female').removeAttr('readonly');
        if (ids.includes($(this).val())) {
            $('#rd_first_gender_male').attr('disabled', true);
            $('#rd_first_gender_female').prop("checked", true);
            $('#rd_first_gender_female').attr('readonly', 'true');
        } else {
            $('#rd_first_gender_female').attr('disabled', true);
            $('#rd_first_gender_male').prop("checked", true);
            $('#rd_first_gender_male').attr('readonly', 'true');
        }
    });
    $(document).on('change', '#rd_second_relation', function() {
        var ids = new Array();
        var ids = ['2', '3', '6', '7', '8', '10', '11'];
        $('#rd_second_gender_male').removeAttr('disabled', false);
        $('#rd_second_gender_female').removeAttr('disabled', false);
        $('#rd_second_gender_male').prop("checked", false);
        $('#rd_second_gender_female').prop("checked", false);
        $('#rd_second_gender_male').removeAttr('readonly');
        $('#rd_second_gender_female').removeAttr('readonly');
        if (ids.includes($(this).val())) {
            $('#rd_second_gender_male').attr('disabled', true);
            $('#rd_second_gender_female').prop("checked", true);
            $('#rd_second_gender_female').attr('readonly', 'true');
        } else {
            $('#rd_second_gender_female').attr('disabled', true);
            $('#rd_second_gender_male').prop("checked", true);
            $('#rd_second_gender_male').attr('readonly', 'true');
        }
    });
    $(document).on('keyup', '#senior_code', function() {
        $('#senior_id,#senior_name,#senior_mobile_no,#seniorcarder_id').val('');
        $('#associate_msg').text('');
        var code = $(this).val();        
        if (code != 0)
            $.ajax({
                type: "POST",
                url: "{!! route('branch.seniorDetail.customer') !!}",
                dataType: 'JSON',
                data: {
                    'code': code
                },
                success: function(e) {
                    $('#associate_msg').text('');
                    if (e.resCount > 0) {
                        if (e.msg == 'block') {
                            $('#senior_name,#senior_id').val('');
                            $('#associate_msg').text('Associate Blocked.');
                            $('.invalid-feedback').show();
                        } else {
                            if (e.msg == 'InactiveAssociate') {
                                $('#senior_name,#senior_id').val('');
                                $('#associate_msg').text('Associate Inactive.');
                                $('.invalid-feedback').show();
                            } else {
                                $.each(e.data, function(index, value) {
                                    $('#senior_name,#first_g_first_name').val(value
                                        .first_name + ' ' + value.last_name ?? '');
                                    $('#senior_id').val(value.id);
                                    $('#senior_mobile_no,#first_g_Mobile_no').val(value
                                        .mobile_no);
                                    $('#seniorcarder_id').val(e.carder_id);
                                    $('#first_g_address').val(value.address ?? 'N/A');
                                });
                                var current_carder2 = $('#current_carder2').val();
                                console.log(current_carder2);
                                $.post("{!! route('branch.getCarderAssociate.customer') !!}", {'id': e.carder_id},function(e) {
                                        $('#current_carder').find('option').remove();
                                        $('#current_carder').append('<option value="">Select Carder</option>');
                                        $.each(e.carde, function(index, value) {
                                            $("#current_carder").append("<option value='" + value.id +"'>" + value.name + "(" + value.short_name + ")</option>");
                                        });
                                    }, 'JSON').always(function(){
                                        if(current_carder2 != 0){
                                            $('#current_carder').val(current_carder2);
                                        }
                                    });
                            }
                        }
                    } else {
                        $('#associate_msg').text('No match found');
                        $('.invalid-feedback').show();
                    }
                    $('#senior_name').trigger('keypress');
                    $('#senior_name').trigger('keyup');
                }
            });
    });
    $(document).ajaxStart(function() {
        $(".loader").show();
    });
    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });
});
var a = 0;
$("#btnAdd").on("click", function() {
    var div = jQuery("<div  class='row remove_div' />");
    div.html(GetDynamicTextBox(""));
    $("#add_dependent").append(div);
});
$("body").on("click", ".remove", function() {
    $(this).closest('.remove_div').remove();
});
function GetDynamicTextBox(value) {
    a++;
    id = a;
    return '<div class="col-lg-12"> <div class="form-group row"> <div class="col-lg-12">  <button type="button" class="btn btn-primary remove" >Remove</button>    </div> </div> </div><div class="col-lg-6"> <div class="form-group row"> <label class="col-form-label col-lg-4">Full Name</label>  <div class="col-lg-8 error-msg">  <input type="text" name="dep_first_name1[' +
        id + ']" id="dep_first_name' + id +
        '" class="form-control dep_name_class"  > </div>  </div> <div class="form-group row"> <label class="col-form-label col-lg-4">Age</label> <div class="col-lg-8 error-msg"> <input type="text" name="dep_age1[' +
        id + ']" id="dep_age' + id +
        '" class="form-control dep_age_class"  > </div> </div> <div class="form-group row">  <label class="col-form-label col-lg-4">Relation</label> <div class="col-lg-8 error-msg">  <select name="dep_relation1[' +
        id + ']" id="dep_relation' + id + '" class="form-control dep_relation_class"  onchange="genderchange(' + id +
        ')"> <option value="">Select Relation</option> @foreach ($relations as $val)  <option value="{{ $val->id }}">{{ $val->name }}</option>  @endforeach  </select> </div>  </div> <div class="form-group row"> <label class="col-form-label col-lg-4">Per month income</label> <div class="col-lg-8 error-msg"> <input type="text" name="dep_income1[' +
        id + ']" id="dep_income' + id +
        '" class="form-control dep_income_class"  > </div> </div> </div> <div class="col-lg-6"> <div class="form-group row">  <label class="col-form-label col-lg-4">Gender</label>  <div class="col-lg-8 error-msg">  <div class="row"> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3 ">  <input type="radio" id="dep_gender_male' +
        id + '" name="dep_gender1[' + id +
        ']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_gender_male' + id +
        '">Male</label> </div>  </div> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="dep_gender_female' +
        id + '" name="dep_gender1[' + id +
        ']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_gender_female' +
        id +
        '">Female</label> </div> </div>  </div>  </div>   </div> <div class="form-group row"> <label class="col-form-label col-lg-4">Marital status</label> <div class="col-lg-8 error-msg"> <div class="row"> <div class="col-lg-4">  <div class="custom-control custom-radio mb-3 ">  <input type="radio" id="dep_married' +
        id + '" name="dep_marital_status1[' + id +
        ']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_married' + id +
        '">Married</label> </div> </div> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="dep_unmarried' +
        id + '" name="dep_marital_status1[' + id +
        ']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_unmarried' +
        id +
        '">Un Married</label> </div> </div> </div> </div>  </div>  <div class="form-group row"> <label class="col-form-label col-lg-4">Living with Associate</label>  <div class="col-lg-8 error-msg"> <div class="row"> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3 "> <input type="radio" id="dep_living_yes' +
        id + '" name="dep_living1[' + id +
        ']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_living_yes' + id +
        '">Yes</label>  </div>  </div> <div class="col-lg-4">  <div class="custom-control custom-radio mb-3  ">  <input type="radio" id="dep_living_no' +
        id + '" name="dep_living1[' + id +
        ']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_living_no' +
        id +
        '">No</label> </div> </div> </div>  </div>  </div>  <div class="form-group row">  <label class="col-form-label col-lg-4">Dependent Type</label>  <div class="col-lg-8 error-msg"> <div class="row"> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3 "> <input type="radio" id="dep_type_fully' +
        id + '" name="dep_type1[' + id +
        ']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_type_fully' + id +
        '">Fully</label>  </div> </div> <div class="col-lg-4">  <div class="custom-control custom-radio mb-3  "> <input type="radio" id="dep_type_partially' +
        id + '" name="dep_type1[' + id +
        ']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_type_partially' +
        id + '">Partially</label> </div>  </div> </div> </div>  </div> </div>';
}
function printDiv(elem) {
    $("#" + elem).print({
        //Use Global styles
        globalStyles: false,
        //Add link with attrbute media=print
        mediaPrint: false,
        //Custom stylesheet
        stylesheet: "{{url('/')}}/asset/print.css",
        //Print in a hidden iframe
        iframe: false,
        //Don't print this
        noPrintSelector: ".avoid-this",
        //Add this at top
        //     prepend : "<span class='tran_account_number' style='padding-left: 40px;line-height: 50px;'>A/C No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+no+"</span>",
        //Add this on bottom
        // append : "<span><br/>Buh Bye!</span>",
        header: null, // prefix to html
        footer: null,
        //Log to console when printing is done via a deffered callback
        deferred: $.Deferred().done(function() {
            console.log('Printing done', arguments);
        })
    });
}
function rd_check(){
    var member_id=$('#id').val();
        $.post("{!! route('branch.associateRdAccounts') !!}",{'member_id':member_id}, function(e) {
                if ( $.isEmptyObject(e) ) {
                    // swal("Error!", "RD account not found!", "error");
                    return false;
                } else {
                    console.log(e);
                }
            },'JSON'
        );
        var rdAccountId = e.account_id;
        $('#rd_account_number').val(e.account_id);
        $('#rd_account_name').val(e.name);
        $('#rd_account_amount').val(e.amount);
        return false;
        // $.ajax({
        //     type: "POST",
        //     url: "{!! route('branch.associateRdAccountGet') !!}",
        //     dataType: 'JSON',
        //     data: {'rdAccountId':rdAccountId},
        //     headers: {
        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     },
        //     success: function(e) {
        //         console.log(e, typeof (e));
        //         if(e.account_id)
        //         {
        //             $('#rd_account_number').val(e.account_id);
        //             $('#rd_account_name').val(e.name);
        //             $('#rd_account_amount').val(e.amount);
        //         }
        //         else
        //         {
        //             swal("Error!", "RD account not found!", "error");
        //             return false;
        //         }
        //     }
        // })
    }
function check_ssb() {
  var customerId = $('#id').val();
  $('#ssb_account_number').val('');
  $('#ssb_account_name').val(''); 
  $('#ssb_account_amount').val('');
  $.post("{!! route('branch.associateSsbAccountGet.customer') !!}",{ 'customerId': customerId },function (e) {
      if (e.resCount == 1) {
        $('#ssb_account_number,#ssb_account_number_form').val(e.account_no);
        $('#ssb_account_name,#ssb_account_name_form').val(e.name);
        $('#ssb_account_amount,#ssb_account_amount_form').val(e.balance);
      } else {
        $('#ssb_account_number').val('');
        $('#ssb_account_name').val('');
        $('#ssb_account_amount').val('');
        $("#ssb_account").val('0');
        return false;
      }
    },'JSON');
}
function rdmaturity() {
    var tenure = $("#tenure").val();
    var principal = $('#rd_amount').val();
    var time = tenure;
    if (time >= 0 && time <= 36) {
        var rate = 8.50;
    } else if (time >= 37 && time <= 60) {
        var rate = 9.50;
    } else if (time >= 61 && time <= 84) {
        var rate = 10.50;
    } else {
        var rate = 8.50;
    }
    console.log("rate RD", rate);
    var ci = 1;
    var irate = rate / ci;
    var year = time / 12;
    var freq = 4;
    var maturity = 0;
    for (var i = 1; i <= time; i++) {
        maturity += principal * Math.pow((1 + ((rate / 100) / freq)), freq * ((time - i + 1) / 12));
    }
    var result = maturity;
    if (Math.round(result) > 0 && tenure <= 84) {
        $('#maturity').html('Maturity Amount :' + Math.round(result));
        $('#rd_amount_maturity').val(Math.round(result));
        $('#rd_rate').val(rate);
    } else {
        $('#maturity').html('');
        $('#rd_amount_maturity').val('');
        $('#rd_rate').val('');
    }
}
function genderchange(id) {
    //alert(id);
    var ids = new Array();
    var ids = ['2', '3', '6', '7', '8', '10', '11'];
    $('#dep_gender_male' + id).removeAttr('disabled', false);
    $('#dep_gender_female' + id).removeAttr('disabled', false);
    $('#dep_gender_male' + id).prop("checked", false);
    $('#dep_gender_female' + id).prop("checked", false);
    $('#dep_gender_male' + id).removeAttr('readonly');
    $('#dep_gender_female' + id).removeAttr('readonly');
    if (ids.includes($('#dep_relation' + id).val())) {
        $('#dep_gender_male' + id).attr('disabled', true);
        $('#dep_gender_female' + id).prop("checked", true);
        $('#dep_gender_female' + id).attr('readonly', 'true');
    } else {
        $('#dep_gender_female' + id).attr('disabled', true);
        $('#dep_gender_male' + id).prop("checked", true);
        $('#dep_gender_male' + id).attr('readonly', 'true');
    }
}
</script>