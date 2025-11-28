<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $.validator.addMethod("customDate", function(value, element) {
            if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
            {
                $.validator.messages.dateDdMm = "";
                result = true;
            }else{
                $.validator.messages.dateDdMm = "Please enter valid date";
                result = false;
            }
            return result;
        }, "");

        $.validator.addMethod("checkEmail", function(value, element,p) {
            //result = false;
            $.ajax({
                type: "POST",
                url: "{!! route('branch.memberemailcheck') !!}",
                dataType: 'JSON',
                data: {'email':value},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.resCount==0)
                    {
                        result = true;
                        $.validator.messages.checkEmail = "";
                    }
                    else
                    {
                        result = false;
                        $.validator.messages.checkEmail = "Email id already exists";
                    }
                }
            });


            return result;
        }, "");

        $('#member_register').validate({
            rules: {
                photo: {
                    // required: true,
                    extension: "jpg|jpeg|png|pdf"
                },
                signature: {
                    // required: true,
                    extension: "jpg|jpeg|png|pdf"
                },
                form_no: {
                    required: true,
                    number: true,
                    //checkFormNo:true,
                },
                application_date: {
                    required: true,
                    // date : true,
                },
                first_name: "required",
                //last_name: "required",
                email: {
                    // required: true,
                    email: function (element) {
                        if ($("#email").val() != '') {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    checkEmail: function (element) {
                        if ($("#email").val() != '') {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                mobile_no: {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 12
                },
                dob: {
                    required: true,
                    // customDate: true,
                },
                gender: "required",
                //      occupation: "required",
                annual_income: {
                    required: true,
                    number: true,
                },
                //       mother_name: "required",
                f_h_name: "required",
                bank_account_no: {
                    number: true,
                },
                /*       marital_status: "required",
                       bank_name: "required",
                       bank_branch_name: "required",
                       bank_account_no: "required",
                       bank_ifsc: "required",
                       bank_branch_address: "required",
                 */
                nominee_first_name: "required",
                // nominee_last_name: "required",
                /*     nominee_relation: "required",*/
                nominee_gender: "required",
                nominee_dob: {
                    required: true,
                    //  customDate: true,
                },

                nominee_mobile_no: {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 12
                },
                parent_nominee_name: {
                    required: function (element) {
                        if ($("#is_minor").prop("checked") == true) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                parent_nominee_mobile_no: {
                    required: function (element) {
                        if ($("#is_minor").prop("checked") == true) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    number: true,
                },
                parent_nominee_mobile_age: {
                    required: function (element) {
                        if ($("#is_minor").prop("checked") == true) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    number: true,
                },
                address: "required",
                state_id: "required",
                city_id: "required",
                district_id: "required",
                marital_status: "required",
                anniversary_date: {
                    required: function (element) {
                        if ($("#married").prop("checked") == true) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },



                pincode: {
                    required: true,
                    number: true,
                    minlength: 6,
                    maxlength: 6
                },
                first_id_type: "required",
                first_id_proof_no: {
                    required: true,
                    checkIdNumber: '#first_id_type',
                },
                first_address_proof: {
                    required: function (element) {
                        if ($("#first_same_as").prop("checked") == false) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                second_id_type: "required",
                second_id_proof_no: {
                    required: true,
                    checkIdNumber: '#second_id_type',
                },
                second_address_proof: {
                    required: function (element) {
                        if ($("#second_same_as").prop("checked") == false) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                associate_code: "required",
                associate_name: "required",

            },
            messages: {
                photo: {
                    required: 'Please select photo.',
                    extension: "Accept only png,jpg or pdf files."
                },
                signature: {
                    required: 'Please select signature.',
                    extension: "Accept only png,jpg or pdf files."
                },
                form_no: {
                    required: "Please enter form number.",
                    number: "Please enter a valid number.",
                },
                application_date: {
                    required: "Please enter application date.",
                    number: "Please enter a valid date.",
                },
                first_name: {
                    required: "Please enter first name.",
                },
                last_name: {
                    required: "Please enter last name.",
                },
                email: {
                    required: "Please enter email id.",
                    email: "Please enter valid email id.",
                },
                mobile_no: {
                    required: "Please enter mobile number.",
                    number: "Please enter valid number.",
                    minlength: "Please enter minimum  10 or maximum 12 digit.",
                    maxlength: "Please enter minimum  10 or maximum 12 digit."
                },
                dob: {
                    required: "Please enter date of birth date.",
                    date: "Please enter valid date.",
                },
                marital_status: "Please select marital status",
                gender: "Please select gender.",
                occupation: "Please select occupation.",
                annual_income: {
                    required: "Please enter annual income.",
                    number: "Please enter valid number.",
                },
                mother_name: "Please enter mother name.",
                f_h_name: "Please enter father/husband name.",
                bank_account_no: {
                    number: "Please enter valid number.",
                },
                /*    marital_status: "Please select marital status.",
                    bank_name: "Please enter bank name.",
                    bank_branch_name: "Please enter branch name.",
                    bank_account_no: "Please enter account number.",
                    bank_ifsc: "Please enter IFSC code.",
                    bank_branch_address: "Please enter address.",
                */
                nominee_first_name: "Please enter nominee name.",
                /*    nominee_last_name: "Please enter nominee last name.",
                    nominee_relation: "Please enter nominee relation.",*/
                nominee_gender: "Please select nominee gender.",
                nominee_dob: {
                    required: "Please enter nominee date of birth.",
                },

                nominee_mobile_no: {
                    required: "Please enter nominee mobile number.",
                    number: "Please enter valid number.",
                    minlength: "Please enter minimum  10 or maximum 12 digit.",
                    maxlength: "Please enter minimum  10 or maximum 12 digit."
                },
                parent_nominee_name: {
                    required: "Please enter nominee parent name.",
                },
                parent_nominee_mobile_no: {
                    required: "Please enter nominee parent name.",
                    number: "Please enter valid number.",
                },
                parent_nominee_mobile_age: {
                    required: "Please enter nominee parent age.",
                    number: "Please enter valid number.",
                },
                address: "Please enter address.",
                state_id: "Please select state.",
                city_id: "Please  select city.",
                district_id: "Please select district.",
                pincode: {
                    required: "Please enter pincode.",
                    number: "Please enter valid number.",
                    minlength: "Please enter minimum  6 or maximum 6 digit.",
                    maxlength: "Please enter minimum  6 or maximum 6 digit."
                },
                first_id_type: "Please select id type.",
                first_id_proof_no: {
                    required: "Please enter id number.",
                },
                first_address_proof: {
                    required: "Please enter address.",
                },
                second_id_type: "Please select id type.",
                second_id_proof_no: {
                    required: "Please enter id number.",
                },
                second_address_proof: {
                    required: "Please enter address.",
                },
                associate_code: "Please enter associate code.",
                associate_name: "Please enter associate name.",
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.error-msg').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            focusInvalid: false,
            invalidHandler: function(form, validator) {
                if (!validator.numberOfInvalids())
                    return;

                $('html, body').animate({
                    scrollTop: $(validator.errorList[0].element).offset().top
                }, 1000);
            }
        });

        $('#investAccountNumber').validate({
            rules: {
                investAccountNumber: {
                    required: true,
                },
            },
            messages: {
                investAccountNumber: {
                    required: 'Please enter old account number.',
                },
            }
        });

        $('#reinvest_plane').validate({ // initialize the plugin
            rules: {
                'investmentplan' : 'required',
                'memberid' : 'required',
                'form_number' : {required: true, number: true},
                'ssbacount' : 'required',
                'fn_first_name' : 'required',
                //'fn_second_name' : 'required',
                'fn_relationship' : 'required',
                'fn_dob' : 'required',
                'fn_age' : 'required',
                'fn_age' : 'required',
                'fn_percentage' : 'required',
                'fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
                'ssb_fn_first_name' : 'required',
                //'ssb_fn_second_name' : 'required',
                'ssb_fn_relationship' : 'required',
                'ssb_fn_dob' : 'required',
                'ssb_fn_age' : 'required',
                'ssb_fn_percentage' : 'required',
                'ssb_fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
                'sn_first_name' : 'required',
                //'sn_second_name' : 'required',
                'sn_relationship' : 'required',
                'sn_dob' : 'required',
                'sn_age' : 'required',
                'sn_percentage' : 'required',
                'sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
                //'ssb_sn_first_name' : 'required',
                //'ssb_sn_second_name' : 'required',
                //'ssb_sn_relationship' : 'required',
                //'ssb_sn_dob' : 'required',
                //'ssb_sn_age' : 'required',
                //'ssb_sn_percentage' : 'required',
                //'ssb_sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
                //'guardian-ralationship' : 'required',
                'phone-number' : {number: true,minlength: 10,maxlength:12},
                'monthly-deposite-amount' : {required: true, number: true},
                //'daughter-name' : 'required',
                //'dob' : 'required',
                'tenure' : 'required',
                //'age' : 'required',
                'payment-mode' : {required: true},
                'cheque-number' : {required: true, number: true},
                'bank-name' : 'required',
                'branch-name' : 'required',
                'cheque-date' : 'required',
                'transaction-id' : 'required',
                'date' : 'required',
                'fn_gender' : 'required',
                'sn_gender' : 'required',
                'amount' : {required: true, number: true},
                'ssbamount' : {required: true, number: true},
            },
            submitHandler: function() {

                var paymentVal = $( "#payment-mode option:selected" ).val();
                var investmentPlan = $( "#investmentplan option:selected" ).val();
                var ssbAccountAvailability = $('input[name="ssb_account_availability"]:checked').val();
                var aviBalance = $('#hiddenbalance').val();
                var mAccount = $('#hiddenaccount').val();
                var ssbAccount = $('#ssbacount').val();
                var rdAccount = $('#rdacount').val();
                var depositeBalance = $('#amount').val();
                var fnPercentage = $('#fn_percentage').val();
                var snPercentage = $('#sn_percentage').val();

                if(snPercentage){
                    snPercentage = $('#sn_percentage').val();
                }else{
                    snPercentage = 0;
                }

                if(ssbAccountAvailability==0){
                    if(investmentPlan == '3' || investmentPlan == '6' || investmentPlan == '8'){
                        if(mAccount != ssbAccount){
                            $('#ssbaccount-error').show();
                            $('#ssbaccount-error').html('SSB Account Number does not exists.');
                            //event.preventDefault();
                            return false;
                        }
                    }
                }

                alert(investmentPlan);
                if(investmentPlan != '2'){
                    if(parseInt(fnPercentage)+parseInt(snPercentage) != 100){
                        $('#percentage-error').show();
                        $('#percentage-error').html('Percentage should be equal to 100.');
                        //event.preventDefault();
                        return false;
                    }
                }

                if(paymentVal == '3'){
                    if ( parseInt(depositeBalance) > parseInt(aviBalance) ) {
                        $('#balance-error').show();
                        $('#balance-error').html('Sufficient amount not available in your account.');
                        //event.preventDefault();
                        return false;
                    }/*else{
                    return true;
                }*/
                }else{
                    $('#balance-error').html('');
                    //return true;
                }
                $('.submit-investment').prop('disabled', true);
                return true;

            }
        });

        $('#reinvest_transaction').validate({ // initialize the plugin
            rules: {
                'closing_Balance_reinvest' : {required: true, number: true},
                'collection_reinvest_amount' : {number: true},
                'payment_mode' : {required: true}
            },
            messages: {
                closing_Balance_reinvest: {
                    required: 'Please enter closing balance.',
                },
                collection_reinvest_amount: {
                    required: 'Please enter collection amount.',
                },
                payment_mode: {
                    required: 'Please select payment type.',
                },
            }
        });

        jQuery.validator.addClassRules("deposit-amount[1]", {
              number: true,
        });

        jQuery.validator.addClassRules("renewal_date[1]", {
              required: true,
        });

        $(document).on('change', '#investAccountNumber', function () {
            var investAccountNumber = $(this).val();

            $.ajax({
                type: "POST",
                url: "{!! route('branch.getInvestment') !!}",
                data: {'investAccountNumber': investAccountNumber},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log("response", Object.keys(response).length, typeof (response));
                    if (response.count == 0 ){
                        swal("Warning!", response.message, "warning");
                        return false;
                    }
                    if (response.count == 2 ){
console.log(response.member_detail, response.member_detail.member_id);
                        $('#member_register').attr('style', 'display:none');
                        $('#reinvest_plane').attr('style', 'display:block');
                        $('#reinvest_plane').attr('style', 'width:100%');
                        $('#old_account_number_plan').val(investAccountNumber);
                        $('#member_id_for_reinvest_new').val(response.member_detail.member_id);
                        $('#reinvest_member_id').val(response.member_detail.id);
                        $('#transaction_member_auto_id').val(response.member_detail.id);
                        $('#saving_account_m_id').val(response.member_detail.id);
                        $('#member_name').val(response.member_detail.first_name);
                        $('#account_number_for_reinvest').val('R-'+investAccountNumber);
                        $('#open-date').val(response.account_application_date);
                        $('#plan-id').val(response.plan_id);
                        $('#plan-type').val(response.planSlug);
                        $('#plan-name').val(response.planName);
                        $('#investmentplan option[value="'+response.plan_id+'"]').prop('selected', true);
                        $('#investmentplan').prop('disabled', true);
                        $('#investmentplanHidden').val(response.plan_id);
                        var plan = response.planSlug;
                        //var memberAutoId = response.mId;
                        var memberAutoId = response.member_detail.id;
						var planIDD = response.plan_id;

                        $.ajax({
                            type: "POST",
                            url: "{!! route('reinvestment.planform') !!}",
                            data: {'plan':plan,'memberAutoId':memberAutoId},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $("#plan-content-div").html('');
                                $("#plan-content-div").html(response);
								if(planIDD == "3"){
									$(".ssbAvailableSection").css("display","none");
									$(".samraddh-money-back-nominee-form").css("display","block");
								}
                                $('#payment-mode').find('option').remove();
                                $('#payment-mode').append('<option data-val="cash" value="0">Cash</option>');
                                $('.fn_dateofbirth,.sn_dateofbirth,#dob,#cheque-date,#date').datepicker( {
                                    format: "dd/mm/yyyy",
                                    orientation: "top",
                                    autoclose: true
                                });
                            }
                        });
                    }

                    if (Object.keys(response).length > 0) {
                        $("#old_member_id").val(response.member_id);
                        $("#old_account_number").val(investAccountNumber);
                        $("#old_account_number_reinvest").val(investAccountNumber);
                        $("#account_application_date").val(response.account_application_date);
                        $("#first_name").val(response.first_name);
                        $("#last_name").val(response.last_name);
                        $("#mobile_no").val(response.mobile_no);
                        $("#f_h_name").val(response.f_h_name);
                        $("#nominee_first_name").val(response.nominee_name);
                        $("#dob").val(response.dob);
                        $("#address").val(response.address);
                        $("#age").val(response.age);
                        $("#plan_id").val(response.plan_id);
                        $("#oldCId").val(response.member_id);
                        $("#plan_id_transaction").val(response.plan_id);
                        $("#age_display").html(response.age + ' Years');
                        if ( response.first_id_proof_no ) {
                            $('#first_id_type option[value="5"]').prop('selected', true);
                            $("#first_id_proof_no").val(response.first_id_proof_no);
                        }

                       // $('#first_id_type option[value="' + response.first_id_type + '"]').attr('selected', 'selected');

                    } else {
                        $("#old_member_id").val('');
                        $("#old_account_number").val('');
                        $("#account_application_date").val('');
                        $("#first_name").val('');
                        $("#last_name").val('');
                        $("#mobile_no").val('');
                        $("#f_h_name").val('');
                        $("#nominee_first_name").val('');
                        $("#dob").val('');
                        $("#address").val('');
                        $("#age").val('');
                        $("#plan_id").val('');
                        $("#oldCId").val();
                        $("#age_display").html('');
                        $('#first_id_type option[value=""]').prop('selected', true);
                        $("#first_id_proof_no").val('');
                    }

                    $('.fn_dateofbirth,.sn_dateofbirth,#dob,#cheque-date,#date').datepicker({
                        format: "dd/mm/yyyy",
                        orientation: "top",
                        autoclose: true
                    });
                }
            });
        });

        var date = new Date();
        var today = new Date(date.getFullYear() - 18, date.getMonth(), date.getDate());
        var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        $('#dob').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: today,
            autoclose: true
        }).on('change', function () {

            var age = getAge(this);
            $('#age').val(age);
            $('#age_display').text(age + ' Years');
            $('.datepicker-dropdown').hide();


        });
        $('#nominee_dob').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: ntoday,
            autoclose: true
        }).on('change', function () {

            var age = getAge(this);
            $('#nominee_age').val(age);
            $('#nominee_age_display').text(age + ' Years');
            $('.datepicker-dropdown').hide();
            $("#is_minor").prop("checked", false)
            $('#nominee_parent_detail').hide()
            if (age >= 18) {
                $('#minor_hide').hide();
            }
            else {
                $('#minor_hide').show();
            }

        });


        $('#anniversary_date,#open-date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true
        });

function getAge(dateVal) {

    moment.defaultFormat = "DD/MM/YYYY HH:mm";

    var birthday = moment('' + dateVal.value + ' 00:00', moment.defaultFormat).toDate(),
        today = new Date();
    var year = today.getYear() - birthday.getYear();
    var m = today.getMonth() - birthday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthday.getDate())) {
        year--;
    }
    return Math.floor(year);
}

            $(document).on('click', '#is_minor', function () {
                if ($("#is_minor").prop("checked") == true) {
                    $('#nominee_parent_detail').show()
                } else {
                    $('#nominee_parent_detail').hide()
                }
            });
            $(document).on('click', '#first_same_as', function () {
                if ($("#first_same_as").prop("checked") == true) {
                    $('#first_address_proof').val($('#address').val());
                } else {
                    $('#first_address_proof').val('');

                }
                $('#first_address_proof').trigger('keypress');
                $('#first_address_proof').trigger('keyup');

            });
            $(document).on('click', '#second_same_as', function () {
                if ($("#second_same_as").prop("checked") == true) {
                    $('#second_address_proof').val($('#address').val());
                } else {
                    $('#second_address_proof').val('');

                }
                $('#second_address_proof').trigger('keypress');
                $('#second_address_proof').trigger('keyup');

            });

            $(document).on('click', '.m-status', function () {
                if ($(this).val() == '1') {
                    $('.anniversary-date-box').show();
                } else {
                    $('.anniversary-date-box').hide();
                }
            });

            $(document).on('click', '#associate_admin', function () {
                if ($("#associate_admin").prop("checked") == true) {
                    $('#associate_code').val(0);
                    $('#associate_id').val(0);
                    $('#associate_name').val('Super Admin');
                } else {
                    $('#associate_code').val('');
                    $('#associate_id').val('');
                    $('#associate_name').val('');

                }


            });

            $(document).on('keyup','#associateid',function(){
                var memberid = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('investment.associate') !!}",
                    dataType: 'JSON',
                    data: {'memberid':memberid},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.resCount > 0){
                            $('.associate-not-found').hide();
                            $('.associate-member-detail').show();
                            var ass_name = response.member[0].first_name+' '+response.member[0].last_name;
                            ass_name ? $('#associate_name_reinvest').val(ass_name) : $('#associate_name_reinvest').val("Name N/A");
                            response.member[0].mobile_no ? $('#associate_mobile').val(response.member[0].mobile_no) : $('#associate_mobile').val("Mobile Number N/A");
                            response.member[0].carders_name ? $('#associate_carder_reinvest').val(response.member[0].carders_name) : $('#associate_carder_reinvest').val("Carder N/A");
                            $('#associatemid').val(response.member[0].id);
                        }else{
                            $('.associate-not-found').show();
                            $('.associate-member-detail').hide();
                        }
                    }
                });
            });
            
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })
            $(document).on('change', '#first_id_type', function () {
                console.log("MM", $(this).val());
                if ($(this).val() == 1) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
                }
                else if ($(this).val() == 2) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter proper driving licence number. For eg:- HR-0619850034761 Or UP14 20160034761');
                }
                else if ($(this).val() == 3) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
                }
                else if ($(this).val() == 4) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter proper passport number. For eg:- A1234567');
                }
                else if ($(this).val() == 5) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter proper pan card number. For eg:- ASDFG9999G');
                }
                else if ($(this).val() == 6) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter id proof number');
                }
                else if ($(this).val() == 7) {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter only digits. For eg:- 2345456567');
                }
                else {
                    $('#first_id_tooltip').attr('data-original-title', 'Enter id proof number');
                }

            });
            $(document).on('change', '#second_id_type', function () {
                if ($(this).val() == 1) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
                }
                else if ($(this).val() == 2) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter proper driving licence number. For eg:- MJ-23456789078656');
                }
                else if ($(this).val() == 3) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
                }
                else if ($(this).val() == 4) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter proper passport number. For eg:- A1234567');
                }
                else if ($(this).val() == 5) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter proper pan card number. For eg:- ASDFG9999G');
                }
                else if ($(this).val() == 6) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number');
                }
                else if ($(this).val() == 7) {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number. For eg:- 2345678909');
                }
                else {
                    $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number');
                }
                if ($(this).val() == $('#first_id_type').val()) {
                    console.log("Id Prof", $(this).val(), $('#first_id_type').val());
                    $('#second_id_proof_no').val($('#first_id_proof_no').val());
                    $('#second_id_proof_no').attr('readonly', 'true');
                } else {
                    $('#second_id_proof_no').val('');
                    $('#second_id_proof_no').removeAttr('readonly');
                }
            });
            $(document).on('change', '#nominee_relation', function () {

                var ids = new Array();
                var ids = ['2', '3', '6', '7', '8', '10', '11'];
                $('#nominee_gender_male').removeAttr('checked');
                $('#nominee_gender_male').removeAttr('readonly');
                $('#nominee_gender_female').removeAttr('readonly');
                $('#nominee_gender_female').removeAttr('checked');
                $('#nominee_gender_male').attr('disabled', false);
                $('#nominee_gender_female').attr('disabled', false);

                if (ids.includes($(this).val())) {
                    $('#nominee_gender_male').attr('disabled', true);
                    $('#nominee_gender_female').attr('checked', 'true');
                    $('#nominee_gender_female').attr('readonly', 'true');
                } else {
                    $('#nominee_gender_female').attr('disabled', true);
                    $('#nominee_gender_male').attr('checked', 'true');
                    $('#nominee_gender_male').attr('readonly', 'true');


                }
            });

            $(document).on('keyup', '#associate_code', function () {
                $('#associate_name').val('');
                $('#associate_carder').val('');
                $('#associate_msg').text('');
                var code = $(this).val();
                if (code != '') {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('branch.getassociatemember') !!}",
                        dataType: 'JSON',
                        data: {'code': code},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {

                            if (response.resCount > 0) {
                                $.each(response.data, function (index, value) {
                                    // alert(value.first_name);
                                    $('#associate_name').val(value.first_name + ' ' + value.last_name);
                                    $('#associate_id').val(value.id);
                                    if (value.member_id == '9999999') {
                                        $('#hide_carder').hide();
                                    }
                                    else {
                                        $('#hide_carder').show();
                                        $('#associate_carder').val(response.carder);
                                    }


                                });
                            }
                            else {
                                $('#associate_name').val('');
                                $('#associate_msg').text('No match found');
                                $('.invalid-feedback').show();
                            }
                            $('#associate_name').trigger('keypress');
                            $('#associate_name').trigger('keyup');
                        }
                    });
                }

            });
            $(document).on('keyup', '#email', function () {
                var code = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('branch.memberemailcheck') !!}",
                    dataType: 'JSON',
                    data: {'email': code, 'id': 0},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.resCount > 0) {
                            return false;
                        }
                    }
                })
            });

            $.validator.addMethod("checkIdNumber", function (value, element, p) {
                if ($(p).val() == 1) {
                    if (this.optional(element) || /^([a-zA-Z]){3}([0-9]){7}?$/g.test(value) == true) {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter valid voter id number";
                        result = false;
                    }
                }
                else if ($(p).val() == 2) {
                    if (this.optional(element) || /^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9][0-9])[0-9]{7}$/.test(value) == true) {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter valid driving licence number";
                        result = false;
                    }
                }
                else if ($(p).val() == 3) {
                    if (this.optional(element) || /^(\d{12}|\d{16})$/.test(value) == true) {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter valid aadhar card  number";
                        result = false;
                    }
                }
                else if ($(p).val() == 4) {
                    if (this.optional(element) || /^[A-Z][0-9]{7}$/.test(value) == true) {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter valid passport  number";
                        result = false;
                    }
                }
                else if ($(p).val() == 5) {
                    if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value) == true) {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter valid pan card no";
                        result = false;
                    }
                }
                else if ($(p).val() == 6) {
                    if (this.optional(element) || value != '') {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter ID Number";
                        result = false;
                    }
                }
                else if ($(p).val() == 7) {
                    if (this.optional(element) || /^(\d{8,14})$/.test(value) == true) {
                        result = true;
                    } else {
                        $.validator.messages.checkIdNumber = "Please enter valid bill no";
                        result = false;
                    }
                }
                else {
                    $.validator.messages.checkIdNumber = "Please enter ID Number";
                    result = false;
                }
                return result;
            }, "");

            $.validator.addMethod("dateDdMm", function (value, element, p) {

                if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
                    $.validator.messages.dateDdMm = "";
                    result = true;
                } else {
                    $.validator.messages.dateDdMm = "Please enter valid date";
                    result = false;
                }

                return result;
            }, "");

            $('#reinvest12').on('click', function (event) {
                $('#investAccountNumber').val();
                var accountNumber = $('#investAccountNumber').val();
                console.log("TT", $('#investAccountNumber').val(), accountNumber.trim());
                checkAccountNumberValidation(accountNumber);

               $('#member_register').valid();
               if ( $('#member_register').valid() == false )
               {
                   return false;
               }

                var form_data = new FormData($('#member_register')[0]);

                var account_number = $('#investAccountNumber').val();
                var form_type = 'member_type';
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url: "{!! route('branch.reinvestSave') !!}",
                    type: "POST",
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if ( response.count == 0 ) {
                            swal("Warning!", response.message, "warning");
                            return false;
                        }
                        $('#member_register').attr('style', 'display:none');
                        $('#reinvest_plane').attr('style', 'display:block');
                        $('#reinvest_plane').attr('style', 'width:100%');
                        $('#old_account_number_plan').val(account_number);
                        if ( response.memberId ) {
                            $('#member_id_for_reinvest_new').val(response.memberId);
                            $('#reinvest_member_id').val(response.id);
                            $('#saving_account_m_id').val(response.id);
                            $('#member_name').val(response.first_name);
                            $('#account_number_for_reinvest').val(response.reInvestAccountNumber);
                            $('#open-date').val(response.openDate);
                            $('#plan-id').val(response.planId);
                            $('#plan-type').val(response.planSlug);
                            $('#plan-name').val(response.planName);
                            $('#investmentplan option[value="'+response.planId+'"]').prop('selected', true);
                            $('#investmentplan').prop('disabled', true);
                            $('#investmentplanHidden').val(response.planId);
                            $('#transaction_member_auto_id').val(response.mId);
                            var plan = response.planSlug;
                            var memberAutoId = response.mId;
							var planIDD = response.planId;
                            $.ajax({
                                type: "POST",
                                url: "{!! route('reinvestment.planform') !!}",
                                data: {'plan':plan,'memberAutoId':memberAutoId},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    $("#plan-content-div").html('');
                                    $("#plan-content-div").html(response);
									
									if(planIDD == "3"){
										$(".ssbAvailableSection").css("display","none");
										$(".samraddh-money-back-nominee-form").css("display","block");
									}
									
                                    $('#payment-mode').find('option').remove();
                                $('#payment-mode').append('<option data-val="cash" value="0">Cash</option>');
                                    $('.fn_dateofbirth,.sn_dateofbirth,#dob,#cheque-date,#date').datepicker( {
                                        format: "dd/mm/yyyy",
                                        orientation: "top",
                                        autoclose: true
                                    });
                                }
                            });
                        } else {
                            $('#member_id_for_reinvest').val('');
                            $('#account_number_for_reinvest').val('');
                            $('#open-date').val('');
                            $('#plan-id').val('');
                            $('#plan-type').val('');
                            $('#plan-name').val('');
                        }
                    },
                });
            });

            $('#create-investment-plan').on('click', function (event) {

                $('#reinvest_plane').valid();
                if ( $('#reinvest_plane').valid() == false )
                {
                    return false;
                }

                var form_data = new FormData($('#reinvest_plane')[0]);
                $.ajax({
                    url: "{!! route('reinvestment.saveForm') !!}",
                    type: "POST",
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log("hello", response);
                        $('#reinvest_plane').attr('style', 'display:none');
                        $('#reinvest_transaction').attr('style', 'display:block; width:100%;');
                        $('#old_account_number_transaction').val( $('#old_account_number').val() );
                        console.log( $('#investmentplan').val(), response.data.investmentplan);
                        //var plansId = [2, 3, 5, 6,7, 10]; add renewal
                        var plansId = [ 1, 4,8,9,11];
                        console.log('gh',plansId.indexOf( response.data.investmentplan ));
                        //var pId = response.data.investmentplan;
                        var pId = $('#investmentplan').val();
                        //if ( 2 == response.data.investmentplan ||  plansId.indexOf( response.data.investmentplan ) <= 0 ) {
                        if ( pId == 1 || pId == 4 || pId == 8 || pId == 9 || pId == 11 ) {
                            console.log( $('#investmentplan').val(), response.data.investmentplan );
                            $('#renewal_transaction').remove();
                        }
                        console.log( "asas", $('#investmentplan').val(), plansId.indexOf( response.data.investmentplan ),  response.data.investmentplan );
                        console.log(response);
                    }
                });
            });

            $('#create-reinvestment-transaction').on('click', function (event) {
                $('#reinvest_transaction').valid();
                if ( $('#reinvest_transaction').valid() == false )
                {
                    return false;
                }
                var form_data = new FormData($('#reinvest_transaction')[0]);
                $.ajax({
                    url: "{!! route('reinvestment.saveForm') !!}",
                    type: "POST",
                    data: form_data,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log(response);
                        swal("Success!", "Investment Create Ready for approval!", "success");
                        window.location.href = "/branch/member";
                    }
                });
            });

            $(document).on('click','.add-second-nominee',function(){
                $(this).val('Remove Nominee');
                var inputClass = $(this).attr('data-class');
                var inputValue = $(this).attr('data-val');
                $('.'+inputClass+'').show();
                $('#'+inputValue+'').val(1);
                $('.second-nominee-input').addClass('remove-second-nominee');
                $('.second-nominee-input').removeClass('add-second-nominee');
            });

            $(document).on('click','.remove-second-nominee',function(){
                $(this).val('Add Nominee');
                var inputClass = $(this).attr('data-class');
                var inputValue = $(this).attr('data-val');
                $('.'+inputClass+'').hide();
                $('#'+inputValue+'').val(0);
                $('.second-nominee-input').addClass('add-second-nominee');
                $('.second-nominee-input').removeClass('remove-second-nominee');
            });
            $(document).on('change', '#state_id', function () {
                var state_id = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('branch.districtlist') !!}",
                    dataType: 'JSON',
                    data: {'state_id': state_id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#district_id').find('option').remove();
                        $('#district_id').append('<option value="">Select district</option>');
                        $.each(response.district, function (index, value) {
                            $("#district_id").append("<option value='" + value.id + "'>" + value.name + "</option>");
                        });

                    }
                });

                $.ajax({
                    type: "POST",
                    url: "{!! route('branch.citylist') !!}",
                    dataType: 'JSON',
                    data: {'district_id': state_id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#city_id').find('option').remove();
                        $('#city_id').append('<option value="">Select city</option>');
                        $.each(response.city, function (index, value) {
                            $("#city_id").append("<option value='" + value.id + "'>" + value.name + "</option>");
                        });

                    }
                });

            });

            $(document).on('keyup change','.ffd-tenure,.ffd-amount',function(){
                var tenure = $( ".ffd-tenure option:selected" ).val();
                var principal = $('.ffd-amount').val();
                var time = tenure;
                if(time >= 0 && time <= 36){
                    var rate = 8;
                }else if(time >= 37 && time <= 48){
                    var rate = 8.25;
                }else if(time >= 49 && time <= 60){
                    var rate = 8.50;
                }else if(time >= 61 && time <= 72){
                    var rate = 8.75;
                }else if(time >= 73 && time <= 84){
                    var rate = 9;
                }else if(time >= 85 && time <= 96){
                    var rate = 9.50;
                }else if(time >= 97 && time <= 108){
                    var rate = 10;
                }else if(time >= 109 && time <= 120){
                    var rate = 11;
                }
                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;
                // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                var result =  ( principal*( Math.pow((1 + irate / 100), year)));
                if(Math.round(result) > 0 && tenure <= 120){
                    $('.ffd-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.ffd-maturity-amount-val').val(Math.round(result));
                    $('.ffd-interest-rate').val(rate);
                }else{
                    $('.ffd-maturity-amount').html('');
                    $('.ffd-maturity-amount-val').val('');
                    $('.ffd-interest-rate').val('');
                }

            });

            $(document).on('keyup change','.frd-tenure,.frd-amount',function(){
                var tenure = $( ".frd-tenure option:selected" ).val();
                var principal = $('.frd-amount').val();
                // var principal = $(this).val();
                var time = tenure;
                if(time >= 0 && time <= 12){
                    var rate = 5;
                }else if(time >= 13 && time <= 24){
                    var rate = 6;
                }else if(time >= 25 && time <= 36){
                    var rate = 6.50;
                }else if(time >= 37 && time <= 48){
                    var rate = 7;
                }else if(time >= 49 && time <= 60){
                    var rate = 9;
                }
                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;
                //  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                var freq = 4;

                var maturity=0;
                for(var i=1; i<=time;i++){
                    maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                }

                var result = maturity;

                if(Math.round(result) > 0 && tenure <= 60){
                    $('.frd-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.frd-interest-rate').val(rate);
                    $('.frd-maturity-amount-cal').val( Math.round(result) );
                }else{
                    $('.frd-interest-rate').val('');
                    $('.frd-maturity-amount-cal').val('');
                    $('.frd-maturity-amount').html('');
                }

            });

            $(document).on('keyup change','.dd-tenure,.dd-amount',function(){
                var tenure = $( ".dd-tenure option:selected" ).val();
                var principal = $('.dd-amount').val();
                var time = tenure;
                if(time >= 0 && time <= 12){
                    var rate = 6;
                }else if(time >= 13 && time <= 24){
                    var rate = 6.50;
                }else if(time >= 25 && time <= 36){
                    var rate = 7;
                }else if(time >= 37 && time <= 60){
                    var rate = 7.25;
                }
                var ci = 12;
                var freq = 12;
                var irate = rate / ci;
                var year = time/12;
                var days = time*30;

                var monthlyPricipal = principal*30;
                var maturity=0;
                for(var i=1; i<=time;i++){
                    maturity+=monthlyPricipal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                }
                var result = maturity;
                // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                if(Math.round(result) > 0 && tenure <= 60){
                    $('.dd-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.dd-interest-rate').val(rate);
                    $('.dd-maturity-amount-val').val(Math.round(result));
                }else{
                    $('.dd-maturity-amount').html('');
                    $('.dd-interest-rate').val('');
                    $('.dd-maturity-amount-val').val('');
                }

            });

            $(document).on('keyup change','.mis-tenure,.mis-amount',function(){
                var tenure = $( ".mis-tenure option:selected" ).val();
                var principal = $('.mis-amount').val();
                var time = tenure;
                if(time >= 0 && time <= 60){
                    var rate = 10;
                }else if(time >= 61 && time <= 84){
                    var rate = 10.50;
                }else if(time >= 85 && time <= 120){
                    var rate = 11;
                }
                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;
                //;  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                var result = (((principal * rate ) / 12) / 100 );

                if(Math.round(result) > 0 && tenure <= 120){
                    $('.mis-maturity-amount').html('Maturity Amount :'+Math.round(result)+'/Month');
                    $('.mis-maturity-amount-val').val(Math.round(result));
                    $('.mis-maturity-amount-cal').val( rate );
                }else{
                    $('.mis-maturity-amount').html('');
                    $('.mis-maturity-amount-cal').val('');
                    $('.mis-maturity-amount-val').val('');
                }

            });

            $(document).on('keyup change','.fd-tenure,.fd-amount',function(){
                var tenure = $( ".fd-tenure option:selected" ).val();
                var principal = $('.fd-amount').val();
                var specialCategory = $('#specialcategory').val();

                var time = tenure;

                console.log("time", time);
                if(time >= 0 && time <= 48){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 10;
                    } else {
                        var rate = 10.25;
                    }
                }else if(time >= 49 && time <= 60){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 10.25;
                    } else {
                        var rate = 10.50;
                    }
                }else if(time >= 61 && time <= 72){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 10.50;
                    } else {
                        var rate = 10.75;
                    }
                }else if(time >= 73 && time <= 96){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 10.75;
                    } else {
                        var rate = 11;
                    }
                }else if(time >= 97 && time <= 120){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 11;
                    } else {
                        var rate = 11.25;
                    }
                }
                console.log("rate", rate);
                console.log("specialCategory", specialCategory);


                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;
                // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);

                var result =  ( principal*( Math.pow((1 + irate / 100), year)));
                if(Math.round(result) > 0 && tenure <= 120){
                    $('.fd-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.fd-interest-rate').val(rate);
                    $('.fd-maturity-amount-val').val(Math.round(result));
                }else{
                    $('.fd-maturity-amount').html('');
                    $('.fd-interest-rate').val('');
                    $('.fd-maturity-amount-val').val('');
                }

            });

            $(document).on('keyup change','.rd-tenure,.rd-amount',function(){
                var tenure = $( ".rd-tenure option:selected" ).val();
                var principal = $('.rd-amount').val();
                var specialCategory = $('#specialcategory').val();
                var time = tenure;
                if(time >= 0 && time <= 36){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 8;
                    } else {
                        var rate = 8.50;
                    }

                }else if(time >= 37 && time <= 60){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 9;
                    } else {
                        var rate = 9.50;
                    }
                }else if(time >= 61 && time <= 84){
                    if ( specialCategory == 'Special Category N/A' ) {
                        var rate = 10;
                    } else {
                        var rate = 10.50;
                    }
                }
                console.log("rate RD", rate);
                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;

                var freq = 4;

                var maturity=0;
                for(var i=1; i<=time;i++){
                    maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                }
                // document.getElementById("maturity").innerText=maturity;
                var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                if(Math.round(result) > 0 && tenure <= 84){
                    $('.rd-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.rd-maturity-amount-val').val(Math.round(result));
                    $('.rd-interest-rate').val(rate);
                }else{
                    $('.rd-maturity-amount').html('');
                    $('.rd-maturity-amount-val').val('');
                    $('.rd-interest-rate').val('');
                }

            });

            $(document).on('keyup','.ssmb-amount',function(){
                var principal = $('.ssmb-amount').val();
                var time = 12;
                var tenure = 7;
                var rate = 9;
                var ci = 1;
                var irate = 8 / ci;
                var year = time / 12;
                var freq = 4;
                var perYearSixtyPecent = ((principal * 12)*60/100);
                var carryAmount = 0;
                var carryForwardInterest = 0;
                var oldMaturity = 0;

                var maturity=0;


                // for(var i=1; i<=time;i++){
                //    perYearWithInterest = principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                //     console.log("maturity-before-"+i+"--", perYearWithInterest);
                //     if(i%12 == 0){
                //        maturity+= maturity-perYearSixtyPecent;
                //        carryForwardInterest = ( maturity*( Math.pow((1 + irate / 100), i-1)));
                //     }
                // }


                for(var j=1; j<=tenure;j++){
                    var perYearWithInterest = 0;
                    for(var i=1; i<=time;i++){
                        perYearWithInterest+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                    }
                    if(j > 1){
                        carryForwardInterest = ( oldMaturity*( Math.pow((1 + rate / 100), 1)));
                        console.log("carryForwardInterest", carryForwardInterest);

                        maturity = Math.round(perYearWithInterest + carryForwardInterest);
                        console.log("maturity", maturity);
                        oldMaturity = Math.round(maturity - perYearSixtyPecent);
                        console.log("oldMaturity", oldMaturity);

                    }else{
                        oldMaturity = Math.round(perYearWithInterest-perYearSixtyPecent);
                        maturity+= oldMaturity;
                        console.log("oldMaturity", oldMaturity);
                    }

                }






                // document.getElementById("maturity").innerText=maturity;
                var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                if(Math.round(result) > 0 ){
                    $('.ssmb-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.ssmb-maturity-amount-val').val(Math.round(result));
                    $('.ssmb-interest-rate').val(rate);
                    $('.ssmb-tenure').val(tenure);
                }else{
                    $('.ssmb-maturity-amount').html('');
                    $('.ssmb-maturity-amount-val').val('');
                    $('.ssmb-interest-rate').val('');
                    $('.ssmb-tenure').html('');
                }

            });

            $(document).on('keyup','.sj-amount',function(){
                var principal = $('.sj-amount').val();
                var specialCategory = $('#specialcategory').val();
                var time = 84;
                var rate = 10.50;

                console.log("rate SJ", rate, 'principal', principal);
                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;

                var freq = 1;

                var maturity=0;
                for(var i=1; i<=time;i++){
                    maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                }
                // document.getElementById("maturity").innerText=maturity;
                var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                if(Math.round(result) > 0 ){
                    // $('.sj-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.sj-tenure').val(time);
                    $('.sj-maturity-amount-val').val(Math.round(result));
                    $('.sj-interest-rate').val(rate);
                }else{
                    $('.sj-maturity-amount').html('');
                    $('.sj-tenure').val('');
                    $('.sj-maturity-amount-val').val('');
                    $('.sj-interest-rate').val('');
                }

            });

            $(document).on('keyup','.sb-amount',function(){
                var tenure = 120;
                var principal = $(this).val();
                var time = tenure;
                var rate = 11;
                var ci = 1;
                var irate = rate / ci;
                var year = time / 12;

                var freq = 1;

                var maturity=0;
                for(var i=1; i<=time;i++){
                    maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
                }
                // document.getElementById("maturity").innerText=maturity;
                var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);


                // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                if(Math.round(result) > 0){
                    $('.sb-maturity-amount').html('Maturity Amount :'+Math.round(result));
                    $('.sb-maturity-amount-cal').val(rate);
                    $('.sb-maturity-amount-val').val(Math.round(result));
                    $('.sb-tenure').val(tenure);
                }else{
                    $('.sb-maturity-amount').html('');
                    $('.sb-maturity-amount-cal').val('');
                    $('.sb-maturity-amount-val').val();
                    $('.sb-tenure').val('');
                }

            });

            $(document).on('change','#payment-mode',function(){
                var paymentMode = $('option:selected', this).attr('data-val');
                var accountNumber = $('#hiddenaccount').val();
                var accountBalance = $('#hiddenbalance').val();
                $('.p-mode').hide();
                $('.'+paymentMode+'').show();
                if(paymentMode == 'ssb-account'){
                    accountNumber ? $('#account_n').val(accountNumber) : $('#account_n').val("Account Number N/A");
                    accountBalance ? $('#account_b').val(accountBalance) : $('#account_b').val("Account Balance N/A");
                }
            });

            $(document).ajaxStart(function () {
                $(".loader").show();
            });

            $(document).ajaxComplete(function () {
                $(".loader").hide();
            });

            $(document).on('change', '#photo', function () {
                $("#upload_form").submit();
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#photo-preview').attr('src', e.target.result);
                        $('#photo-preview').attr('style', 'width:200px; height:200px;');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
            $(document).on('change', '#signature', function () {
                $("#signature_form").submit();
                if (this.files && this.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#signature-preview').attr('src', e.target.result);
                        $('#signature-preview').attr('style', 'width:100%;');
                    }

                    reader.readAsDataURL(this.files[0]);
                }
            });

            $(document).on('change', '#first_id_proof_no, #second_id_proof_no', function () {
                var id_proof_no = $(this).val();
                var id = $(this).attr('id');
              /*  $.ajax({
                    type: "POST",
                    url: "{{--{!! route('branch.checkIdProof') !!}--}}",
                    dataType: 'JSON',
                    data: {'id_proof_no': id_proof_no},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $(".loader").hide();
                        var errorId = '#' + id;
                        var lableErrorId = id + '-error';
                        if (response) {
                            $(errorId).val('');
                            $(errorId).addClass('is-invalid');
                            $(errorId).parent().find("label").remove();
                            $(errorId).after('<label id="' + lableErrorId + '" class="error" for="' + id + '" style="">This document already assign to ' + response + '</label>');
                        } else {
                            $(errorId).removeClass('is-invalid');
                            $(errorId).parent().find("label#" + lableErrorId).remove();
                        }
                    }
                });*/
            });
            $(document).on('click','#same_as_registered_nominee',function(){
                var firstName = $('#reg_nom_fn_first_name').val();
                var lastName = $('#reg_nom_fn_second_name').val();
                var relationship = $('#reg_nom_fn_relationship').val();
                var gender = $('#reg_nom_fn_gender').val();
                var dob = $('#reg_nom_fn_dob').val();
                var age = $('#reg_nom_fn_age').val();
                if ($(this).is(":checked")) {
                    $('#fn_first_name').val(firstName);

                    $('#fn_second_name').val(lastName);
                    $('#fn_dob').val(dob);
                    $('#fn_first_name').attr("readonly", "true");
                    $('#fn_dob').attr("readonly", "true");
                    $("#fn_dob").removeClass('fn_dateofbirth');
                    // $("#fn_dob").datepicker('remove');
                    // $("#fn_dob").prop('disabled', true);
                    $('#fn_dob').datepicker('destroy');
                    $('#fn_age').val(age);
                    $("input[name=fn_gender][value="+gender+"]").prop('checked', true);
                    $("input[name=fn_gender][value="+gender+"]").attr("readonly", "true");
                    $("#fn_relationship option[value=" + relationship +"]").prop("selected",true) ;
                    $("#fn_relationship").attr("readonly", "true") ;
                } else {
                    $('#fn_first_name').removeAttr("readonly");
                    $('#fn_first_name').val('');
                    $('#fn_second_name').val('');
                    $("#fn_relationship option[value='0']").prop("selected",true) ;
                    $("#fn_relationship").removeAttr("readonly") ;
                    $("input[name=fn_gender][value='0']").prop('checked', true);
                    $("input[name=fn_gender][value='0']").removeAttr("readonly");
                    $('#fn_dob').val('');
                    $("#fn_dob").addClass('fn_dateofbirth');
                    $('#fn_dob').removeAttr("readonly");
                    $('#fn_age').val('');
                    $("#fn_dob").datepicker({
                        format: "dd/mm/yyyy",
                        orientation: "top",
                        autoclose: true
                    });
                    /*$('.fn_dateofbirth').datepicker( {
                        format: "dd/mm/yyyy",
                        orientation: "top",
                        autoclose: true
                    });*/
                }
            });

        $('.renewal_date').datepicker( {
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        });

        $(document).on('keyup','#closing_Balance_reinvest',function(){
            $('#opening_Balance_reinvest').val($(this).val());
            $('#opening-balance').val($(this).val());
            $('#total_reinvest_amount').val( $(this).val() );
        });
       // var totalAmount = parseFloat('0.00').toFixed(2);
        $(document).on('change','.deposit-amount',function(){
            var amount = $(this).val();
            $('#collection_reinvest_amount').val(amount);

          console.log( "YYY", Number.isInteger(amount), parseInt(amount) );
           var allAmount = $('.deposit-amount');
           console.log(allAmount);

           var totalAmount = parseFloat('0.00').toFixed(2);

            $('.deposit-amount').each(function() {
                if ( $(this).val() ) {
                    totalAmount =  parseFloat(totalAmount) + parseFloat( $(this).val() );
                }
            });

            $('#total_reinvest_amount').val( parseFloat(totalAmount).toFixed(2) );
        })

        $(document).on('keyup','.sa-nominee-percentage',function(){
            var inputId = $(this).attr('data-id');
            var value = $(this).val();
            var check = $('#sa_second_nominee_add').val();
            var buttonClass = $('#sa_second_nominee_add').attr('data-button-class');
            if(check > 0){
                if(value <= 100 && value != ''){
                    var otherVal = 100-parseInt(value);
                    $('#'+inputId+'').val(otherVal);
                }else{
                    $(this).val('');
                    $('#'+inputId+'').val(0);
                }
            }else{
                if(value == 100){
                    $('.sa-second-nominee-botton').prop("disabled",true) ;
                }else{
                    $('.sa-second-nominee-botton').prop("disabled",false) ;
                }
            }
        });

        $(document).on('keyup','.nominee-percentage',function(){
            var inputId = $(this).attr('data-id');
            var value = $(this).val();
            var check = $('#second_nominee_add').val();
            var buttonClass = $('#second_nominee').attr('data-button-class');
            if(check > 0){
                if(value <= 100 && value != ''){
                    var otherVal = 100-parseInt(value);
                    $('#'+inputId+'').val(otherVal);
                }else{
                    $(this).val('');
                    $('#'+inputId+'').val(0);
                }
            }else{
                if(value == 100){
                    $('.second-nominee-botton').prop("disabled",true) ;
                }else{
                    $('.second-nominee-botton').prop("disabled",false) ;
                }
            }
        });

        $(document).on('change','.deposit-amount',function(){ 
            var cValue = $(this).val();
            var depositeAmount = $('#amount').val();
            var rePlan = $('#plan_id').val();
            var floatInteger = cValue/depositeAmount;
            if(floatInteger % 1 != 0 && rePlan != 7 && rePlan != 2){
                $(this).val('');
                swal("Warning!", "Amount should be multiply deno amount!", "warning");
                $(this).val('');
            } 
        });

        $(document).on('click','#same_as_registered_ssb_nominee',function(){ 
            var firstName = $('#reg_nom_fn_first_name').val();
            var lastName = $('#reg_nom_fn_second_name').val();
            var relationship = $('#reg_nom_fn_relationship').val();
            var gender = $('#reg_nom_fn_gender').val();
            var dob = $('#reg_nom_fn_dob').val();
            var age = $('#reg_nom_fn_age').val();
            if ($(this).is(":checked")) {
                $('#ssb_fn_first_name').val(firstName);
                $('#ssb_fn_second_name').val(lastName);
                $('#ssb_fn_dob').val(dob);
                $('#ssb_fn_age').val(age);
                $("input[name=ssb_fn_gender][value="+gender+"]").prop('checked', true);
                $("#ssb_fn_relationship option[value=" + relationship +"]").prop("selected",true);
                $('#ssb_fn_first_name').attr("readonly", "true");
                $('#ssb_fn_dob').attr("readonly", "true");
                $('#ssb_fn_age').attr("readonly", "true");
                $("#ssb_fn_dob").removeClass('fn_dateofbirth');
                $( "#ssb_fn_dob" ).prop('disabled', true);
                $("#ssb_fn_relationship").attr("readonly", "true") ;
            } else { 
                $('#ssb_fn_first_name').val('');
                $('#ssb_fn_second_name').val('');
                $("#ssb_fn_relationship option[value='0']").prop("selected",true) ;
                $("input[name=ssb_fn_gender][value='0']").prop('checked', true);
                $('#ssb_fn_dob').val('');
                $('#ssb_fn_age').val('');
                $("#ssb_fn_relationship").removeAttr("readonly");
                $('#ssb_fn_first_name').removeAttr("readonly");
                $('#ssb_fn_dob').removeAttr("readonly");
                $('#ssb_fn_age').removeAttr("readonly",);
                $("#ssb_fn_dob").addClass('fn_dateofbirth');
                $('.fn_dateofbirth').prop('disabled', false);
            }
        });

        $('#saving-account-form').validate({ // initialize the plugin
            rules: {
                'f_number' : 'required',
                'ssb_fn_first_name' : 'required',
                //'ssb_fn_second_name' : 'required',
                'ssb_fn_relationship' : 'required',
                'ssb_fn_dob' : 'required',
                'ssb_fn_age' : 'required',
                'ssb_fn_percentage' : 'required',
                'ssb_fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
                'ssb_sn_first_name' : 'required',
                //'ssb_sn_second_name' : 'required',
                'ssb_sn_relationship' : 'required',
                'ssb_sn_dob' : 'required',
                'ssb_sn_age' : 'required',
                'ssb_sn_percentage' : 'required',
                'ssb_fn_gender' : 'required',
                'ssb_sn_gender' : 'required',
                //'ssb_sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
                'ssbamount' : {required: true, number: true},
            },
            submitHandler: function(form) {
                var fnPercentage = $('#ssb_fn_percentage').val();
                var snPercentage = $('#ssb_sn_percentage').val();
                if(snPercentage){
                    snPercentage = $('#ssb_sn_percentage').val();
                }else{
                    snPercentage = 0;   
                }
                if(parseInt(fnPercentage)+parseInt(snPercentage) != 100){
                    $('#ssb-percentage-error').show();
                    $('#ssb-percentage-error').html('Percentage should be equal to 100.');
                    //event.preventDefault();
                    return false;
                }

                var post_url = $('#saving-account-form').attr("action"); //get form action url
                var request_method = $('#saving-account-form').attr("method"); //get form GET/POST method
                var form_data = $('#saving-account-form').serialize(); //Encode form elements for submission
                $.ajax({
                    url : post_url,
                    type: request_method,
                    data : form_data
                }).done(function(response){ //
                    if(response.msg_type=='success'){
                        $("input[name=ssb_account_availability][value=0]").prop('checked', true);
                        $('.'+response.accountInput+'').show();
                        $('#ssbacount').val(response.investmentAccount);
                        $('#hiddenaccount').val(response.investmentAccount);
                        $('#hiddenbalance').val(100);
                        $('#saving-account-modal-form').modal('hide'); 
                        $('.'+response.nomineeForm+'').show();
                        $("#saving-account-form")[0].reset();
                    }else if(response.msg_type=='exists'){
                        swal("Error!", "Your saving account already created!", "error");
                        $("input[name=ssb_account_availability][value=0]").prop('checked', true);
                        $('.'+response.accountInput+'').show();
                        $('#ssbacount').val(response.investmentAccount);
                        $('#hiddenaccount').val(response.investmentAccount);
                        $('#hiddenbalance').val(100);
                        $('#saving-account-modal-form').modal('hide'); 
                        $('.'+response.nomineeForm+'').show();
                        $("#saving-account-form")[0].reset();
                    }else{
                        alert('Somthing went wrong!');
                    }
                });
            }
        });

        $(document).on('click','.ssb-account-availability',function(){
            var aVal = $(this).val();
            var ssbClass = $(this).attr('data-val');
            var nomineeFormClass = $(this).attr('nominee-form-class');
            var ssbValue = $('#ssbacount').val();
            var investmentPlan = $( "#investmentplan option:selected" ).val();
            if(aVal == 0){
                $('.'+ssbClass+'').show(); 
                if(ssbValue == ''){
                    $('#ssbacount').val('');
                }    
            }else{
                $('.'+ssbClass+'').hide();
                if(ssbValue == ''){
                    $('#ssbacount').val(0);
                }
                $('#nominee_form_class').val(''+nomineeFormClass+'');
                $('#account_box_class').val(''+ssbClass+'');
                $('#current_plan_id').val(investmentPlan);
            }
        });

        $(document).on('change','.kanyadhan-dob',function(){
        moment.defaultFormat = "DD/MM/YYYY";
        var date1212 = $(this).val();
        var date = moment(date1212, moment.defaultFormat).toDate();
        var inputId = $(this).attr('data-val');

        dob1 = new Date(date);
        var today1 = new Date();
        var dob = moment(dob1, moment.defaultFormat).toDate();
        var today = moment(today1, moment.defaultFormat).toDate();
        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
       
        var tenure = 19-age;
        //alert(age);
        if(age >= 0 && tenure >= 0){
            $('.samraddh-kanyadhan-yojana').val(age);
            $('#tenure').val(19-age); 
            $.ajax({
                type: "POST",  
                url: "{!! route('investment.kanyadhanamount') !!}",
                data: {'fa_code':709,'tenure':tenure},
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.resCount > 0){
                        $('.monthly-deposite-amount').val(response.investmentAmount[0].amount);
                        var tenure = $('.kanyadhan-yojna-tenure').val();
                        var principal = response.investmentAmount[0].amount;
                        if(tenure >= 8 && tenure <= 18){
                            var rate = 11;
                        }else if(tenure >= 6 && tenure <= 7){
                            var rate = 10.50;
                        }else if(tenure < 6){
                            var rate = 10;
                        }
                        var ci = 1;
                        var time = tenure*12;
                        var irate = rate / ci;
                        var year = time / 12;
                        var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                        $('.maturity-amount').html('Maturity Amount :'+Math.round(result));
                    }else{
                       $('.monthly-deposite-amount').val('');
                       $('.maturity-amount').html('');
                    } 
                }
            });
        }else{
            $(this).val('');
            $('.samraddh-kanyadhan-yojana').val('');  
            $('#tenure').val(''); 
            $('.monthly-deposite-amount').val('');
            $('.maturity-amount').html('');
            alert('Please select a valid date');
        } 
    });

    });

    function printDiv(elem) {
        /* printJS({
         printable: elem,
         type: 'html',
         targetStyles: ['*'],
         })*/

        $("#" + elem).print({
            //Use Global styles
            globalStyles: true,
            //Add link with attrbute media=print
            mediaPrint: true,
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
            header: null,               // prefix to html
            footer: null,
            //Log to console when printing is done via a deffered callback
            deferred: $.Deferred().done(function () {
                console.log('Printing done', arguments);
            })
        });

    }
    function addNewRow() {
        var table = document.getElementById("add-transaction");
        console.log(table.rows.length);
        var rowCount = table.rows.length;
        var row = table.insertRow(rowCount);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);
        var cell3 = row.insertCell(2);
        cell1.innerHTML = rowCount+".";
        cell2.innerHTML = '<input type="text" name="renewal_date['+rowCount+']" id="renewal_date" class="form-control renewal_date" value="">';
        cell3.innerHTML = '<div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="'+rowCount+'" ' +
            'name="amount['+rowCount+']" class="form-control rupee-txt deposit-amount amount amount-'+rowCount+'"></div>';
        var deleteRow = document.getElementById('delete-row');
        if (rowCount == 11 ) {
            deleteRow.innerHTML = '<button type="button" class=" btn btn-primary legitRipple" onclick="deleteRow()">Delete Row</button>';
        }

        $('.renewal_date').datepicker( {
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        });
    }
    
    function deleteRow() {
        var table = document.getElementById("add-transaction");
        var rowCount = table.rows.length;
        console.log( rowCount );
        table.deleteRow(rowCount -1);
        var deleteRow = document.getElementById('delete-row');
        if ( rowCount <= 12 ) {
            deleteRow.innerHTML = '';
        }
    }
    function checkAccountNumberValidation(accountNumber) {
        if ( accountNumber.trim() == "" ) {
            $( "#investAccountNumber" ).addClass( "is-invalid" );
            swal("Warning!", "Please enter account number!", "warning");
            return false;
        } else if ( /^[0-9]{12}$/.test(accountNumber) == false ) {
            console.log("TTT",  /^[0-9]{12}$/.test(accountNumber) );
            $( "#investAccountNumber" ).addClass( "is-invalid" );
            swal("Warning!", "Please enter currect number!", "warning");
            return false;
        }
    }
 
</script>
