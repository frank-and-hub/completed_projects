<script type="text/javascript">
    if (sessionStorage.getItem('refreshed')) {
        console.log('Page was refreshed!');
        $('#is_staff').prop('checked', false);
    }
    sessionStorage.setItem('refreshed', 'true');
    $(document).ready(function () {
        $('#email').val('');
        $('#bank_account_no').val('');
        /*
        $('#email').on('keyup', function() {
            var code = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.emailcheck') !!}",
                dataType: 'JSON',
                data: {
                    'email': code,
                    'id': 0
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.resCount > 0) {
                        return false;
                    }
                }
            })
        });
        */
        $('#bank_account_no, #cbank_account_no').on('cut copy paste', function (e) {
            e.preventDefault();
        });
        $('#minor_hide').hide();
        // $("#state_id").select2();
        // $("#district_id").select2();
        // $("#city_id").select2();
        var date = new Date();
        var today = new Date(date.getFullYear() - 18, date.getMonth(), date.getDate());
        var hundred_years = 36525;
        var lastday = new Date(date.setDate(date.getDate() - hundred_years));
        var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        $('#dob').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: today,
            startDate: lastday,
            autoclose: true          
        }).on('change', function() {
            var age = getAge(this);
            $('#age').val(age);
            $('#age_display').text(age + ' Years');
            $('.datepicker-dropdown').hide();
        });
        $('#dob').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: "-18y",
            autoclose: true
        });

        $('#application_date').prop('readonly', true);
        $('#nominee_dob').hover(function () {
            const lastdaya = $('.create_application_date').val();
            $('#nominee_dob').datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                startDate: lastday,
                endDate: lastdaya,
                autoclose: true
            }).on('change', function () {
                var age = getAge(this);
                $('#nominee_age').val(age);
                $('#nominee_age_display').text(age + ' Years');
                $('.datepicker-dropdown').hide();
                $('#nominee_parent_detail').hide()
                if (age >= 18) {
                    $('#minor_hide').hide();
                } else {
                    $("#is_minor").prop("checked", true)
                    $('#nominee_parent_detail').show()
                    $('#minor_hide').show();
                }
            })
        });
        $(document).on('select2:select', '#branch_id', function () {
            var bId = $('option:selected', this).attr('data-val');
            var sbId = $("#hbranchid option:selected").val();
            if (bId != sbId) {
                $('#branch_id').val('');
                $('#branch_id').trigger('change');
                swal("Warning!", "Branch does not match from top dropdown state", "warning");
            }
        });
        $('#anniversary_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            startDate: lastday,
            autoclose: true
        });
        
        // $(document).ready(function(){
        //   if ($( "#is_minor" ).prop( "checked")==true) {
        //     $('#nominee_parent_detail').show()
        //   } else {
        //     $('#nominee_parent_detail').hide()
        //   }
        // });
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
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
        $(document).on('change', '#first_id_type', function () {
            console.log("MM", $(this).val());
            if ($(this).val() == 5) {
                $(".table-section-onlypancard").removeClass("hideTableData");
            } else {
                $(".table-section-onlypancard").addClass("hideTableData");
            }
            if ($(this).val() == 1) {
                //$('#first_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
            } else if ($(this).val() == 2) {
                $('#first_id_tooltip').attr('data-original-title',
                    'Enter proper driving licence number. For eg:- HR-0619850034761 Or UP14 20160034761'
                );
            } else if ($(this).val() == 3) {
                $('#first_id_tooltip').attr('data-original-title',
                    'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
            } else if ($(this).val() == 4) {
                $('#first_id_tooltip').attr('data-original-title',
                    'Enter proper passport number. For eg:- A1234567');
            } else if ($(this).val() == 5) {
                $('#first_id_tooltip').attr('data-original-title',
                    'Enter proper pan card number. For eg:- ASDFG9999G');
            } else if ($(this).val() == 6) {
                $('#first_id_tooltip').attr('data-original-title', 'Enter id proof number');
            } else if ($(this).val() == 7) {
                $('#first_id_tooltip').attr('data-original-title',
                    'Enter only digits. For eg:- 2345456567');
            } else {
                $('#first_id_tooltip').attr('data-original-title', 'Enter id proof number');
            }
        });
        $(document).on('change', '#second_id_type', function () {
            if ($(this).val() == 1) {
                //$('#second_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
            } else if ($(this).val() == 2) {
                $('#second_id_tooltip').attr('data-original-title',
                    'Enter proper driving licence number. For eg:- MJ-23456789078656');
            } else if ($(this).val() == 3) {
                $('#second_id_tooltip').attr('data-original-title',
                    'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
            } else if ($(this).val() == 4) {
                $('#second_id_tooltip').attr('data-original-title',
                    'Enter proper passport number. For eg:- A1234567');
            } else if ($(this).val() == 5) {
                $('#second_id_tooltip').attr('data-original-title',
                    'Enter proper pan card number. For eg:- ASDFG9999G');
            } else if ($(this).val() == 6) {
                $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number');
            } else if ($(this).val() == 7) {
                $('#second_id_tooltip').attr('data-original-title',
                    'Enter id proof number. For eg:- 2345678909');
            } else {
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
            var ids = ['2', '3', '6', '7', '8', '10', '11', '8', '16', '14'];
            $('#nominee_gender_male').removeAttr('checked');
            $('#nominee_gender_male').removeAttr('readonly');
            $('#nominee_gender_female').removeAttr('readonly');
            $('#nominee_gender_female').removeAttr('checked');
            $('#nominee_gender_male').attr('disabled', false);
            $('#nominee_gender_female').attr('disabled', false);
            if (ids.includes($(this).val())) {
                $('#nominee_gender_male').attr('disabled', true);
                $('#nominee_gender_female').attr('checked', true);
                $('#nominee_gender_female').attr('readonly', true);
            } else {
                $('#nominee_gender_female').attr('disabled', true);
                $('#nominee_gender_male').attr('checked', true);
                $('#nominee_gender_male').attr('readonly', true);
            }
        });
        //for the admin associate code
        $('#admin_associate_code').on('change', function () {
            $('#associate_name').val('');
            $('#associate_carder').val('');
            $('#associate_msg').text('');
            var code = $(this).val();
            if (code != '') {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.associate_member') !!}",
                    dataType: 'JSON',
                    data: {
                        'code': code
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#associate_msg').text('');
                        if (response.resCount > 0) {
                            if (response.msg == 'block') {
                                $('#senior_name').val('');
                                $('#senior_id').val('');
                                $('#associate_msg').text('Associate Blocked.');
                                $('.invalid-feedback').show();
                            } else {
                                if (response.msg == 'InactiveAssociate') {
                                    $('#associate_name').val('');
                                    $('#associate_id').val('');
                                    $('#associate_msg').text('Associate Inactive.');
                                    $('.invalid-feedback').show();
                                } else {
                                    $.each(response.data, function (index, value) {
                                        // alert(value.first_name);
                                        $('#associate_name').val(value.first_name +
                                            ' ' + value.last_name);
                                        $('#associate_id').val(value.id);
                                        if (value.member_id == '9999999') {
                                            $('#hide_carder').hide();
                                        } else {
                                            $('#hide_carder').show();
                                            $('#associate_carder').val(response
                                                .carder);
                                            $('#carder_id').val(response.carder_id);
                                        }
                                    });
                                }
                            }
                        } else {
                            $('#associate_name').val('');
                            $('#carder_id').val('');
                            $('#associate_msg').text('No match found');
                            $('.invalid-feedback').show();
                        }
                        $('#associate_name').trigger('keypress');
                        $('#associate_name').trigger('keyup');
                    }
                });
            }
        });
        //for the branch associate code
        $('#branch_associate_code').on('change', function () {
            $('#associate_name').val('');
            $('#associate_carder').val('');
            $('#associate_msg').text('');
            var code = $(this).val();
            if (code != '') {
                $.ajax({
                    type: "POST",
                    url: "{!! route('branch.associate_member') !!}",
                    dataType: 'JSON',
                    data: {
                        'code': code
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#associate_msg').text('');
                        if (response.resCount > 0) {
                            if (response.msg == 'block') {
                                $('#senior_name').val('');
                                $('#senior_id').val('');
                                $('#associate_msg').text('Associate Blocked.');
                                $('.invalid-feedback').show();
                            } else {
                                if (response.msg == 'InactiveAssociate') {
                                    $('#associate_name').val('');
                                    $('#associate_id').val('');
                                    $('#associate_msg').text('Associate Inactive.');
                                    $('.invalid-feedback').show();
                                } else {
                                    $.each(response.data, function (index, value) {
                                        // alert(value.first_name);
                                        $('#associate_name').val(value.first_name +
                                            ' ' + value.last_name);
                                        $('#associate_id').val(value.id);
                                        if (value.member_id == '9999999') {
                                            $('#hide_carder').hide();
                                        } else {
                                            $('#hide_carder').show();
                                            $('#associate_carder').val(response
                                                .carder);
                                        }
                                    });
                                }
                            }
                        } else {
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
        $.validator.addMethod("checkIdNumber", function (value, element, p) {
            if ($(p).val() == 1) {
                result = true;
                // if(this.optional(element) || /^([a-zA-Z]){3}([0-9]){7}?$/g.test(value)==true)
                // {
                //   result = true;
                // }else{
                //   $.validator.messages.checkIdNumber = "Please enter valid voter id number";
                //   result = false;  
                // }
            } else if ($(p).val() == 2) {
                if (this.optional(element) ||
                    /^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9][0-9])[0-9]{7}$/.test(
                        value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid driving licence number";
                    result = false;
                }
            } else if ($(p).val() == 3) {
                if (this.optional(element) || /^(\d{12}|\d{16})$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid aadhar card  number";
                    result = false;
                }
            } else if ($(p).val() == 4) {
                if (this.optional(element) || /^[A-Z][0-9]{7}$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid passport  number";
                    result = false;
                }
            } else if ($(p).val() == 5) {
                if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid pan card no";
                    result = false;
                }
            } else if ($(p).val() == 6) {
                if (this.optional(element) || value != '') {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter ID Number";
                    result = false;
                }
            } else if ($(p).val() == 7) {
                if (this.optional(element) || /^(\d{8,14})$/.test(value) == true) {
                    result = true;
                } else {
                    $.validator.messages.checkIdNumber = "Please enter valid bill no";
                    result = false;
                }
            } else {
                $.validator.messages.checkIdNumber = "Please enter ID Number";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("dateDdMm", function (value, element, p) {
            if (this.optional(element) ||
                /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
                $.validator.messages.dateDdMm = "";
                result = true;
            } else {
                $.validator.messages.dateDdMm = "Please enter valid date";
                result = false;
            }
            return result;
        }, "");
        $('#member_register').validate({
            rules: {
                photo: {
                    required: false,
                    extension: "jpg|jpeg|png|pdf"
                },
                signature: {
                    required: false,
                    extension: "jpg|jpeg|png|pdf",
                },
                form_no: {
                    required: true,
                    number: true,
                },
                application_date: {
                    required: true,
                    dateDdMm: true,
                },
                first_name: {
                    required: true,
                },
                email: {
                    email: function (element) {
                        if ($("#email").val() != '') {
                            return true;
                        } else {
                            return false;
                        }
                    },
                },
                // anniversary_date: {
                //     required: function (element) {
                //         if ($("#married").val() == 1) {
                //             return true;
                //         } else {
                //             return false;
                //         }
                //     },
                // },
                bank_ifsc: {
                    checkIfsc: true,
                },
                nominee_relation: {
                    required: true,
                },
                mobile_no: {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 12
                },
                dob: {
                    required: true,
                    dateDdMm: true,
                },
                gender: "required",
                // annual_income: {
                //   required: true,
                //   number: true,
                //   maxlength: 12
                // },
                f_h_name: {
                    required: true,
                },
                marital_status: "required",
                bank_account_no: {
                    minlength: 9,
                    maxlength: 18
                },
                cbank_account_no: {
                    number: true,
                    minlength: 9,
                    maxlength: 18,
                    equalTo: "#bank_account_no"
                },
                nominee_gender_male: "required",
                nominee_dob: {
                    dateDdMm: true,
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
                    minlength: 10,
                    maxlength: 12
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
                associate_id: "required",
                branch_id: "required",
                nominee_first_name: "required",
                // mother_name: "required",
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
                    date: "Please enter a valid date.",
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
                    required: "Please enter date of birth",
                    date: "Please enter valid date.",
                },
                cbank_account_no: {
                    equalTo: "Bank A/C and confirm bank A/C must be same",
                },
                gender: "Please select gender.",
                marital_status: "Please select marital status",
                occupation: "Please select occupation.",
                // annual_income: {
                //   required:"Please enter annual income.",
                //   number: "Please enter valid number.",
                // },
                // mother_name: {
                //     required: "Please enter Mother Name.",
                // },
                f_h_name: {
                    required: "Please enter Father Name.",
                },
                bank_account_no: {
                    number: "Please enter valid number.",
                },
                nominee_first_name: {
                    required: "Please enter nominee name.",
                },
                nominee_relation: "Please select relation.",
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
                branch_id: "Please select branch.",
            },
            errorElement: 'label',
            errorPlacement: function (error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }/*,submitHandler: function(form) {
                var createdAt = $('#createdAt').val();
                $('#someInput').val(createdAt); // Replace #someInput with the actual ID of the input where you want to set the value
                $(form).submit();
            }*/
        });
        //for the admin state
        $(document).on('change', '#admin_state_id', function () {
            var state_id = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.district_lists') !!}",
                dataType: 'JSON',
                data: {
                    'state_id': state_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#district_id').find('option').remove();
                    $('#district_id').append('<option value="">Select District</option>');
                    $.each(response.district, function (index, value) {
                        $("#district_id").append("<option value='" + value.id +
                            "'>" + (value.name).toUpperCase() + "</option>");
                    });
                }
            });
            $.ajax({
                type: "POST",
                url: "{!! route('admin.city_lists') !!}",
                dataType: 'JSON',
                data: {
                    'district_id': state_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#city_id').find('option').remove();
                    $('#city_id').append('<option value="">Select City</option>');
                    $.each(response.city, function (index, value) {
                        $("#city_id").append("<option value='" + value.id + "'>" +
                            value.name.toUpperCase() + "</option>");
                    });
                }
            });
        });
        //for the branch state
        $(document).on('change', '#branch_state_id', function () {
            var state_id = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('branch.district_lists') !!}",
                dataType: 'JSON',
                data: {
                    'state_id': state_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#district_id').find('option').remove();
                    $('#district_id').append('<option value="">Select District</option>');
                    $.each(response.district, function (index, value) {
                        $("#district_id").append("<option value='" + value.id +
                            "'>" + (value.name).toUpperCase() + "</option>");
                    });
                }
            });
            $.ajax({
                type: "POST",
                url: "{!! route('branch.city_lists') !!}",
                dataType: 'JSON',
                data: {
                    'district_id': state_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#city_id').find('option').remove();
                    $('#city_id').append('<option value="">Select City</option>');
                    $.each(response.city, function (index, value) {
                        $("#city_id").append("<option value='" + value.id + "'>" +
                            value.name.toUpperCase() + "</option>");
                    });
                }
            });
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
                    $('#signature-preview').attr('style', 'width:200px; height:200px;');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        $(document).on('change', '#first_id_proof_no, #second_id_proof_no', function () {
            var panel = $(this).data('panel');
            var panel_identify = (panel == ' 1 ') ? "{!! route('admin.check_idProof') !!}" : "{!! route('branch.check_idProof') !!}";
            var Currentsids = $(this).attr('id');
            if (Currentsids == "first_id_proof_no") {
                ///	$("#first_same_as").attr( 'checked', true );
            }
            if (Currentsids == "second_id_proof_no") {
                //	$("#second_same_as").attr( 'checked', true );
            }
            var first_id_type = $('#first_id_type').val();
            var id_proof_no = $(this).val();
            var id = $(this).attr('id');
            var second_id_type = $('#second_id_type').val();
            var second_id_proof_no = $('#second_id_proof_no').val();
            $('span#errormessage').html("");
            $.ajax({
                type: "POST",
                url: panel_identify,
                dataType: 'JSON',
                data: {
                    'id_proof_no': id_proof_no,
                    'first_id_type': first_id_type,
                    'second_id_type': second_id_type,
                    'second_id_proof_no': second_id_proof_no
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $(".loader").hide();
                    if (response.msg == "exists") {
                        var c_id = "on (" + response.c_id + ")";
                        if (response.first_id_type == 'firstidtypechecked' && response
                            .second_id_type == '') {
                            $('#first_id_proof_no').after(
                                "<span class='error' id='errormessage'>Already exists "+c_id+"</span>"
                            );
                        } else if (response.first_id_type == 'firstidtypechecked' &&
                            response.second_id_type == 'secondidtypechecked') {
                            $('#first_id_proof_no').after(
                                "<span class='error' id='errormessage'>Already exists "+c_id+"</span>"
                            );
                            $('#second_id_proof_no').after(
                                "<span class='error' id='errormessage'>Already exists "+c_id+"</span>"
                            );
                        } else if (response.second_id_type == 'secondidtypechecked' &&
                            response.first_id_type == '') {
                            $('#second_id_proof_no').after("<span class='error' id='errormessage'>Already exists "+c_id+"</span>");
                        } else {
                            $('span#errormessage').empty();
                        }
                        // console.log(response);
                    } else {
                        $('span#errormessage').empty();
                    }
                    var errorId = '#' + id;
                    var lableErrorId = id + '-error';
                    // if ( response ) {
                    //     $(errorId).val('');
                    //     $(errorId).addClass('is-invalid');
                    //     $(errorId).parent().find("label").remove();
                    //     $(errorId).after('<label id="'+ lableErrorId + '" class="error" for="'+ id + '" style="">This document already assign to ' + response + '</label>');
                    // } else {
                    //     $(errorId).removeClass('is-invalid');
                    //     $(errorId).parent().find("label#"+lableErrorId).remove();
                    // }
                }
            });
        });
        //Created by Gaurav on 19-12-2023
        //Start from here------------------->
        $('#is_staff').on('change', function (e) {
            $('#emp_code').val('');
            $('#application_date').prop('readonly', true);
            if ($(this).prop('checked')) {
                $('#emp_code_block').removeClass('d-none');
                $('.main_form').addClass('d-none');
            }
            else {
                var form = document.getElementById('member_register');
                // Loop through all form elements
                for (var i = 0; i < form.elements.length; i++) {
                    var element = form.elements[i];
                    // Check if the element is an input field, textarea, or select
                    if (element.type !== 'button' && element.type !== 'submit' && element.type !== 'reset') {
                        // Clear the value of the element
                        element.value = '';
                        // Remove the disabled attribute
                        element.disabled = false;
                        element.readOnly = false;
                        // For select elements, reset the selected option
                        if (element.type === 'select-one' || element.type === 'select-multiple') {
                            element.selectedIndex = -1;
                        }
                    }
                }
                $('.error').remove();
                $('input').removeClass('is-invalid');
                $('select').removeClass('is-invalid');
                $('textarea').removeClass('is-invalid');
                $('#email-error').removeClass('d-none');
                $('#photo-preview').attr('src', "{{url('/')}}/asset/images/user.png");
                $('#signature-preview').attr('src', "{{url('/')}}/asset/images/signature-logo-design.png");
                $('#emp_code_block').addClass('d-none');
                $('.main_form').removeClass('d-none');
                $('#branch_id').prepend($('#branch_id').find('option[value=""]')).val('');
                $('#admin_state_id').prepend($('#admin_state_id').find('option[value=""]')).val('');
                $('#branch_state_id').prepend($('#branch_state_id').find('option[value=""]')).val('');
                $('#district_id').prepend($('#district_id').find('option[value=""]')).val('');
                $('#city_id').prepend($('#city_id').find('option[value=""]')).val('');
                $('#occupation').prepend($('#occupation').find('option[value=""]')).val('');
                $('#second_id_type').prepend($('#second_id_type').find('option[value=""]')).val('');
                $('#emp_second_id_type').val('');
                $('#first_id_type').prepend($('#first_id_type').find('option[value=""]')).val('');
                $('#emp_first_id_type').val('');
                $('#first_same_as').prop('checked', false);
                $('#second_same_as').prop('checked', false);
                $('#married').prop('checked', false);
                $('#un_married').prop('checked', false);
                $('#gender_male').prop('checked', false);
                $('#gender_female').prop('checked', false);
                $('.custom-file').removeClass('d-none');
                $('#form_no').val('');
                $('#submit').removeClass('d-none');
                $('#update').addClass('d-none');
                $('#religion').prepend($('#religion').find('option[value="0"]').prop('selected', true));
                $('#special_category').prepend($('#special_category').find('option[value="0"]').prop('selected', true));
                var admin_gdate = $('.gdate').text();
                var branch_date = $('#gdatetime').val();
                var momentDate = moment(branch_date);
                var branch_gdate = momentDate.format('DD/MM/YYYY');
                (admin_gdate == '')?$('#application_date').val(branch_gdate).attr('readonly',true):$('#application_date').val(admin_gdate).attr('readonly',true);
                $('#married').val('1');
                $('#un_married').val('0');
                $('#dob').datepicker({
                    format: "dd/mm/yyyy",
                    todayHighlight: true,
                    endDate: "-18y",
                    autoclose: true
                }); 
                // $('#application_date').datepicker({
                //     'setDate': new Date((admin_gdate == '') ? branch_gdate : admin_gdate),
                //     'format': "dd/mm/yyyy",
                //     'todayHighlight': true,
                // });
                // $('#application_date').datepicker('destroy');
                $('#nominee_relation').prepend($('#nominee_relation').find('option[value=""]').prop('selected', true));
                $('#nominee_gender_male').attr('checked', false);
                $('#nominee_gender_female').attr('checked', false);
            }
        });
        $('#fetch_data').on('click', function () {
            var emp_code = $('#emp_code').val();
            var panel_identify = $('#emp_code').data('panel');
            var url = (panel_identify == ' 1 ') ? "{{route('admin.member.empDetail')}}" : "{{route('branch.member.empDetail')}}";
            if (emp_code != '') {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        'emp_code': emp_code
                    },
                    success: function (result) {
                        if (result.data == '0') {
                            swal('Warning', 'The employee code has already been assigned to a member..!!', 'error');
                        }
                        else if (result.data != '') {
                            if (result.data['status'] == '0') {
                                swal('Warning', 'Employee is inactive..!!', 'error');
                                $('.main_form').addClass('d-none');
                            }
                            else if (result.data['status'] == '9') {
                                swal('Warning', 'Employee status is deleted..!!', 'error');
                                $('.main_form').addClass('d-none');
                            }
                            else if (result.data['status'] == '1') {
                                $('#branch_state_id').prepend($('#branch_state_id').find('option[value=""]')).val('');
                                $('#district_id').prepend($('#district_id').find('option[value=""]')).val('');
                                $('#city_id').prepend($('#city_id').find('option[value=""]')).val('');
                                $('.error').remove();
                                $('input').removeClass('is-invalid');
                                $('select').removeClass('is-invalid');
                                $('textarea').removeClass('is-invalid');
                                $('#branch_id').prepend($('#branch_id').find('option[value="' + result.data['branch']['id'] + '"]').prop('selected', true));
                                $('#employee_code').val($('#emp_code').val());
                                if (result.data['employee_name'] != '' && result.data['employee_name'] != null) {
                                    var name = result.data['employee_name'].split(' ');
                                    var last_name = name[name.length - 1];
                                    var merge_first_name = name.slice(0, name.length - 1);
                                    var first_name = merge_first_name.join(' ');
                                }
                                else {
                                    var last_name = result.data['last_name']
                                    var first_name = result.data['first_name'];
                                }
                                (result.data['form_no'] != '' && result.data['form_no'] != null) ? $('#form_no').val(result.data['form_no']) : $('#form_no').val('');
                                (result.data['id'] != '' && result.data['id'] != null) ? $('#id').val(result.data['id']) : $('#id').val('0');
                                (result.data['get_employee_details'] != '' && result.data['get_employee_details'] != null) ? $('#emp_id').val(result.data['get_employee_details']['id']) : $('#emp_id').val(result.data['emp_id']);
                                $('#email-error').addClass('d-none');
                                $('.main_form').removeClass('d-none');
                                (first_name != '') ? $('#first_name').val(first_name) : $('#first_name').val('');
                                (last_name != '') ? $('#last_name').val(last_name) : $('#last_name').val('');
                                $('#isEmployee').val('1');
                                (result.data['email'] != '' && result.data['email'] != null) ? $('#email').prop('readonly', true).val(result.data['email']) : $('#email').val('').prop('readonly', false);
                                (result.data['mobile_no'] != '' && result.data['mobile_no'] != null) ? $('#mobile_no').val(result.data['mobile_no']).prop('readonly', true) : $('#mobile_no').val('').prop('readonly', false);
                                if (result.dob != '' && result.dob != null) {
                                    $('#dob').val(result.dob);
                                    $('#dob').prop('readonly', true);
                                    $("#dob").datepicker('destroy', true);
                                    dobInputage(document.getElementById('dob'));
                                }
                                else {
                                    $('#dob').val('');
                                    $('#dob').prop('readonly', false);
                                    $('#dob').datepicker({
                                        format: "dd/mm/yyyy",
                                        todayHighlight: true,
                                        endDate: "-18y",
                                        autoclose: true
                                    });
                                }
                                var application_date_createdDate = new Date(result.data['created_at']);
                                    console.log(result.data['created_at'],'application_date_createdDate');

                                    $('#application_date').datepicker({
                                        format: "dd/mm/yyyy",
                                        todayHighlight: true,
                                        autoclose: true,
                                        showOnFocus: false,
                                    })
                                    // .datepicker('setDate', application_date_createdDate)
                                    .datepicker('setDate', new Date())
                                    ;

                                if (result.data['designations'] != '' && result.data['designations'] != null) {
                                    var check_occupation = $('#occupation').find('option[value="' + result.data['designations']['id'] + '"]').text();
                                    // Move the selected option to the top by prepending and then selecting it
                                    if (check_occupation != '') {
                                        $('#occupation').prepend($('#occupation').find('option[value="' + result.data['designations']['id'] + '"]')).val(result.data['designations']['id']);
                                    }
                                    else {
                                        $('#occupation').prepend($('#occupation').find('option[value="2"]')).val('2');
                                    }
                                    $('#annual_income').val(result.data['salary']);
                                }
                                else {
                                    $('#occupation').prepend($('#occupation').find('option[value="' + result.data['occupation_id'] + '"]')).val(result.data['occupation_id']);
                                    $('#annual_income').val(result.data['annual_income']);
                                }
                                if (result.data['gender'] == '1') {
                                    $('#gender_male').prop('checked', true);
                                    $('#gender_female').prop('disabled', true);
                                }
                                else if (result.data['gender'] == '2') {
                                    $('#gender_female').prop('checked', true);
                                    $('#gender_male').prop('disabled', true);
                                }
                                (result.data['mother_name'] != '' && result.data['mother_name'] != null) ? $('#mother_name').val(result.data['mother_name']).attr('readonly', true) : $('#mother_name').val('').attr('readonly', false);
                                (result.data['father_husband'] != '' && result.data['father_husband'] != null) ? $('#f_h_name').val(result.data['father_husband']).attr('readonly', true) : $('#f_h_name').val(result.data['father_guardian_name']).attr('readonly', false);
                                (typeof result.data['religion_id'] !== 'undefined' && result.data['religion_id'] != '' && result.data['religion_id'] != null) ? $('#religion').prepend($('#religion').find('option[value="' + result.data['religion_id'] + '"]')).val(result.data['religion_id']) : $('#religion').prepend($('#religion').find('option[value="0"]').prop('selected', true));
                                (result.data['special_category_id'] != '' && result.data['special_category_id'] != null) ? $('#special_category').prepend($('#special_category').find('option[value="' + result.data['special_category_id'] + '"]')).val(result.data['special_category_id']) : $('#special_category').prepend($('#special_category').find('option[value="0"]').prop('selected', true));
                                if (result.data['marital_status'] == '1') {
                                    $('#married').prop('checked', true);
                                    $('#un_married').attr('disabled', true);
                                    $('.anniversary-date-box').removeClass('d-none');
                                }
                                else if (result.data['marital_status'] == '0') {
                                    $('#un_married').prop('checked', true);
                                    $('.anniversary-date-box').addClass('d-none');
                                }
                                (result.data['anniversary_date'] != '' && result.data['anniversary_date']) ? $('#anniversary_date').val(result.data['anniversary_date']) : $('#anniversary_date').val('');
                                $('#bank_account_no').val('');
                                if (result.data['bank_details'] != '' && result.data['bank_details'] != null) {
                                    $('#bank_name').val(result.data['bank_details']['bank_name']);
                                    $('#bank_id').val(result.data['bank_details']['id']);
                                    $('#bank_branch_name').val(result.data['branch']['name'] ?? null);
                                    $('#bank_account_no').val(result.data['bank_details']['bank_account_no']);
                                    $('#cbank_account_no').val(result.data['bank_details']['bank_account_no']);
                                    $('#bank_ifsc').val(result.data['bank_details']['ifsc_code']);
                                    $('#bank_branch_address').val(result.data['bank_details']['address']);
                                }
                                else {
                                    (result.data['bank_name'] != null) ? $('#bank_name').val(result.data['bank_name']) : $('#bank_name').val('');
                                    (result.data['branch']['name'] != null) ? $('#bank_branch_name').val(result.data['branch']['name']) : $('#bank_branch_name').val('');
                                    (result.data['bank_account_no'] != null) ? $('#bank_account_no').val(result.data['bank_account_no']) : $('#bank_account_no').val('');
                                    (result.data['bank_account_no'] != null) ? $('#cbank_account_no').val(result.data['bank_account_no']) : $('#cbank_account_no').val('');
                                    (result.data['bank_ifsc_code'] != null) ? $('#bank_ifsc').val(result.data['bank_ifsc_code']) : $('#bank_ifsc').val('');
                                    (result.data['bank_address']) ? $('#bank_branch_address').val(result.data['bank_address']) : $('#bank_branch_address').val('');
                                }
                                if (result.data['member_nominee_details'] != '' && result.data['member_nominee_details'] != null) {
                                    $('#nominee_id').val(result.data['member_nominee_details']['id']);
                                    $('#nominee_first_name').val(result.data['member_nominee_details']['name']);
                                    $('#nominee_relation').val(result.data['member_nominee_details']['relation']);
                                    if (result.data['member_nominee_details']['gender'] == 1) {
                                        $('#nominee_gender_male').attr('checked', true);
                                        $('#nominee_gender_female').attr('disabled', true);
                                    }
                                    else {
                                        $('#nominee_gender_male').attr('disabled', true);
                                        $('#nominee_gender_female').attr('checked', true);
                                    }
                                    (result.data['member_nominee_details']['dob'] != '' && result.data['member_nominee_details']['dob'] != null) ? $('#nominee_dob').val(result.data['member_nominee_details']['dob']) : $('#nominee_dob').val('');
                                    (result.data['member_nominee_details']['age'] != '' && result.data['member_nominee_details']['age'] != null) ? $('#nominee_age').val(result.data['member_nominee_details']['age']) : $('#nominee_age').val('0');
                                    (result.data['member_nominee_details']['mobile_no'] != '' && result.data['member_nominee_details']['mobile_no'] != null) ? $('#nominee_mobile_no').val(result.data['member_nominee_details']['mobile_no']) : $('#nominee_mobile_no').val('');
                                    if (result.data['member_nominee_details']['is_minor'] == '1' && result.data['member_nominee_details']['is_minor'] != null) {
                                        $('#is_minor').attr('checked', true);
                                        $('#nominee_parent_detail').show()
                                        $('#minor_hide').show();
                                    }
                                    else {
                                        $('#is_minor').attr('checked', false);
                                        $('#minor_hide').hide();
                                    }
                                    (result.data['member_nominee_details']['parent_name'] != '' && result.data['member_nominee_details']['parent_name'] != null) ? $('#parent_nominee_name').val(result.data['member_nominee_details']['parent_name']) : $('#parent_nominee_name').val('');
                                    (result.data['member_nominee_details']['parent_no'] != '' && result.data['member_nominee_details']['parent_no'] != null) ? $('#parent_nominee_mobile_no').val(result.data['member_nominee_details']['parent_no']) : $('#parent_nominee_mobile_no').val('');
                                }
                                else {
                                    $('#nominee_id').val('');
                                    $('#nominee_first_name').val('');
                                    $('#nominee_relation').prepend($('#nominee_relation').find('option[value=""]').prop('selected', true));
                                    $('#nominee_gender_male').attr('checked', false);
                                    $('#nominee_gender_female').attr('disabled', false);
                                    $('#nominee_gender_male').attr('disabled', false);
                                    $('#nominee_gender_female').attr('checked', false);
                                    $('#nominee_dob').val('');
                                    $('#nominee_age').val('0');
                                    $('#nominee_mobile_no').val('');
                                    $('#is_minor').attr('checked', false);
                                    $('#parent_nominee_name').val('');
                                    $('#parent_nominee_mobile_no').val('');
                                }
                                if (result.data['associate_code'] != '' && result.data['associate_code'] != null) {
                                    $('#associate_id').val(result.data['associate_code']['id']);
                                    $('#admin_associate_code').val(result.data['associate_code']['associate_no']);
                                    var first_name = result.data['associate_code']['first_name'];
                                    var last_name = result.data['associate_code']['last_name'];
                                    var associate_name = first_name + ' ' + last_name;
                                    $('#associate_name').val(associate_name);
                                    if (result.data['get_carder_name_custom'] != '' && result.data['get_carder_name_custom'] != null) {
                                        var carder_name = (result.data['get_carder_name_custom']['name']) ?? '';
                                        var short_name = (result.data['get_carder_name_custom']['short_name']) ?? '';
                                        var full_carder_name = carder_name + ' (' + short_name + ')';
                                        (result.data['get_carder_name_custom'] != '' && result.data['get_carder_name_custom'] != null) ? $('#associate_carder').val(full_carder_name) : $('#associate_carder').val('');
                                        $('#carder_id').val(result.data['get_carder_name_custom']['id']);
                                    }
                                    else {
                                        $('#carder_id').val(result.data['associate_code']['current_carder_id']);
                                        $('#associate_carder').val(result.carder_name);
                                    }
                                }
                                else {
                                    $('#associate_id').val('');
                                    $('#admin_associate_code').val('');
                                    $('#associate_name').val('');
                                    $('#associate_carder').val('')
                                }
                                $('#first_id_type').prepend($('#first_id_type').find('option[value=""]').prop('selected', true)).prop('readonly', false);
                                $('#second_id_type').prepend($('#second_id_type').find('option[value=""]').prop('selected', true)).prop('readonly', false);
                                $('#first_id_proof_no').val('').prop('readonly', false);
                                $('#second_id_proof_no').val('').prop('readonly', false);
                                $('#first_address_proof').val('');
                                $('#second_address_proof').val('');
                                $('#first_same_as').prop('checked', false);
                                $('#second_same_as').prop('checked', false);
                                $('#village_name').val('');
                                // console.log(result.data); return false;
                                if (
                                    typeof result.data['aadhar_card'] !== 'undefined' && 
                                    typeof result.data['pen_card'] !== 'undefined' && 
                                    typeof result.data['voter_id'] !== 'undefined'
                                    ) {
                                    if (result.data['pen_card'] != '' && result.data['pen_card'] != null) {
                                        $('#first_id_type').prepend($('#first_id_type').find('option[value="5"]').attr('selected', true)).val('5');
                                        $('#first_id_type').prop('readonly', true);
                                        $('#emp_first_id_type').val('5');
                                        $('#first_id_proof_no').val(result.data['pen_card']).prop('readonly', true);
                                        if (result.data['voter_id'] != '' && result.data['voter_id'] != null) {
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="1"]').attr('selected', true)).val('1');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('1');
                                            $('#second_id_proof_no').val(result.data['voter_id']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['permanent_address']);
                                            $('#second_same_as').prop('checked', true);
                                        } else if(result.data['aadhar_card'] != '' && result.data['aadhar_card'] != null){
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="3"]').attr('selected', true)).val('3');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('1');
                                            $('#second_id_proof_no').val(result.data['aadhar_card']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['permanent_address']);
                                            $('#second_same_as').prop('checked', true);
                                        }
                                        $('#first_address_proof').val(result.data['permanent_address']);
                                        $('#first_same_as').prop('checked', true);
                                    } else if (result.data['aadhar_card'] != '' && result.data['aadhar_card'] != null) {
                                        $('#first_id_type').prepend($('#first_id_type').find('option[value="3"]').attr('selected', true)).val('3');
                                        $('#first_id_type').prop('readonly', true);
                                        $('#emp_first_id_type').val('3');
                                        /*
                                        $('#first_id_proof_no').val(result.data['aadhar_card']).prop('readonly', true);
                                        if (result.data['pen_card'] != '' && result.data['pen_card'] != null) {
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="5"]').attr('selected', true)).val('5');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('5');
                                            $('#second_id_proof_no').val(result.data['pen_card']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['permanent_address']);
                                            $('#second_same_as').prop('checked', true);
                                        } else 
                                        */
                                       if (result.data['voter_id'] != '' && result.data['voter_id'] != null) {
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="1"]').attr('selected', true)).val('1');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('1');
                                            $('#second_id_proof_no').val(result.data['voter_id']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['permanent_address']);
                                            $('#second_same_as').prop('checked', true);
                                        }
                                        $('#first_address_proof').val(result.data['permanent_address']);
                                        $('#first_same_as').prop('checked', true);
                                    } else if (result.data['voter_id'] != '' && result.data['voter_id'] != null) {
                                        $('#first_id_type').prepend($('#first_id_type').find('option[value="1"]').attr('selected', true)).val('1');
                                        $('#first_id_type').prop('readonly', true);
                                        $('#emp_first_id_type').val('1');
                                        $('#first_id_proof_no').val(result.data['voter_id']).prop('readonly', true);
                                        $('#first_address_proof').val(result.data['permanent_address']);
                                        $('#first_same_as').prop('checked', true);
                                    }
                                }
                                else {
                                    //at the time of display the data from member table
                                    if (result.data['get_employee_details']['pen_card'] != '' && result.data['get_employee_details']['pen_card'] != null) {
                                        $('#first_id_type').prepend($('#first_id_type').find('option[value="5"]').prop('selected', true)).val('5');
                                        $('#first_id_type').prop('readonly', true);
                                        $('#emp_first_id_type').val('5');
                                        $('#first_id_proof_no').val(result.data['get_employee_details']['pen_card']).prop('readonly', true);
                                        if(result.data['get_employee_details']['aadhar_card'] != '' && result.data['get_employee_details']['aadhar_card'] != null){
                                            $('#second_id_type').prepend($('#first_id_type').find('option[value="3"]').prop('selected', true)).val('3');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('3');
                                            $('#second_id_proof_no').val(result.data['get_employee_details']['aadhar_card']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['address']);
                                            $('#second_same_as').prop('checked', true);
                                        } else if (result.data['get_employee_details']['voter_id'] != '' && result.data['get_employee_details']['voter_id'] != null) {
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="1"]').prop('selected', true)).val('1');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('1');
                                            $('#second_id_proof_no').val(result.data['get_employee_details']['voter_id']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['address']);
                                            $('#second_same_as').prop('checked', true);
                                        }
                                        $('#first_address_proof').val(result.data['address']);
                                        $('#first_same_as').prop('checked', true);
                                    } else if (result.data['get_employee_details']['aadhar_card'] != '' && result.data['get_employee_details']['aadhar_card'] != null) {
                                        $('#first_id_type').prepend($('#first_id_type').find('option[value="3"]').prop('selected', true)).val('3');
                                        $('#first_id_type').prop('readonly', true);
                                        $('#emp_first_id_type').val('3');
                                        $('#first_id_proof_no').val(result.data['get_employee_details']['aadhar_card']).prop('readonly', true);
                                        if (result.data['get_employee_details']['pen_card'] != '' && result.data['get_employee_details']['pen_card'] != null) {
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="5"]').prop('selected', true)).val('5');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('5');
                                            $('#second_id_proof_no').val(result.data['get_employee_details']['pen_card']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['address']);
                                            $('#second_same_as').prop('checked', true);
                                        }
                                        else if (result.data['get_employee_details']['voter_id'] != '' && result.data['get_employee_details']['voter_id'] != null) {
                                            $('#second_id_type').prepend($('#second_id_type').find('option[value="1"]').prop('selected', true)).val('1');
                                            $('#second_id_type').prop('readonly', true);
                                            $('#emp_second_id_type').val('1');
                                            $('#second_id_proof_no').val(result.data['get_employee_details']['voter_id']).prop('readonly', true);
                                            $('#second_address_proof').val(result.data['address']);
                                            $('#second_same_as').prop('checked', true);
                                        }
                                        $('#first_address_proof').val(result.data['address']);
                                        $('#first_same_as').prop('checked', true);
                                    } else if (result.data['get_employee_details']['voter_id'] != '' && result.data['get_employee_details']['voter_id'] != null) {
                                        $('#first_id_type').prepend($('#first_id_type').find('option[value="1"]').prop('selected', true)).val('1');
                                        $('#first_id_type').prop('readonly', true);
                                        $('#emp_first_id_type').val('1');
                                        $('#first_id_proof_no').val(result.data['get_employee_details']['voter_id']).prop('readonly', true);
                                        $('#first_address_proof').val(result.data['address']);
                                        $('#first_same_as').prop('checked', true);
                                    }
                                }
                                const isEmployeeChecked = $('input[name="is_employee"]').prop('checked');
                                if (isEmployeeChecked) {
                                    var f_h_name = $('form[name="member_register"] input[name="f_h_name"]');
                                    f_h_name.prop('readonly', true);
                                }
                                $('#photo').parent().removeClass('d-none');
                                $('#signature').parent().removeClass('d-none');
                                $('#profile_photo').val(result.data['photo']);
                                (result.photo != '' && result.photo != null && result.photo != 'noimage') ? $('#photo-preview').attr('src', result.photo).addClass('w-100') : $('#photo-preview').attr('src', "{{url('/')}}/asset/images/user.png");
                                (result.data['photo'] != '' && result.data['photo'] != null && result.data['photo'] != 'noimage') ? $('#hidden_photo').val(result.data['photo']) : $('#hidden_photo').val('');
                                (result.photo != '' && result.photo != null && result.photo != 'noimage') ? $('#photo').parent().addClass('d-none') : $('#photo').parent().removeClass('d-none');
                                (result.signature != '' && result.signature != null) ? $('#signature-preview').attr('src', result.signature) : $('#signature-preview').attr('src', "{{url('/')}}/asset/images/signature-logo-design.png");
                                (result.signature != '' && result.signature != null) ? $('#signature').parent().addClass('d-none') : $('#signature').parent().removeClass('d-none');
                                if (typeof result.data['permanent_address'] !== 'undefined') {
                                    if (result.data['permanent_address'] != '' && result.data['permanent_address'] != null) {
                                        $('#address').val(result.data['permanent_address']).prop('readonly', true);
                                        var pin_code = result.data['permanent_address'].split('-');
                                        // $('#pincode').val(pin_code[pin_code.length - 1]);
                                        $('#admin_state_id').prepend($('#admin_state_id').find('option[value=""]').prop('selected', true));
                                    }
                                }
                                else {
                                    $('#address').val(result.data['address']).prop('readonly', true);
                                    var pin_code = result.data['address'].split('-');
                                    (result.data['state_id'] != '' && result.data['state_id'] != null) ? $('#admin_state_id').prepend($('#admin_state_id').find('option[value="' + result.data['state_id'] + '"]').prop('selected', true)).val(result.data['state_id']) : '';
                                    // (result.data['pin_code'] != '') ? $('#pincode').val(result.data['pin_code']) : $('#pincode').val(pin_code[pin_code.length - 1]);
                                    (result.data['village'] != '') ? $('#village_name').val(result.data['village']) : '';
                                }
                                (typeof result.data['get_employee_details'] !== 'undefined' && result.data['get_employee_details'] != '' && result.data['get_employee_details'] != null) ? $('#submit').addClass('d-none') : $('#submit').removeClass('d-none');
                                (typeof result.data['get_employee_details'] !== 'undefined' && result.data['get_employee_details'] != '' && result.data['get_employee_details'] != null) ? $('#update').removeClass('d-none') : $('#update').addClass('d-none');
                                $('#bank_account_no').keyup();
                            }
                        }
                        else if (result.data == '') {
                            $('.main_form').addClass('d-none');
                            swal('Warning', result.message, 'error');
                        }
                    }
                });
            }
            else {
                $('.main_form').addClass('d-none');
                swal('Warning', 'Enter Employee Code First..!!', 'error');
            }
        });
        //End here that created by Gaurav 
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
            stylesheet: "{{ url('/') }}/asset/print.css",
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
            deferred: $.Deferred().done(function () {
                console.log('Printing done', arguments);
            })
        });
    }
    $(document).on('keyup', '#emp_code', function () {
        const emp_code = $(this).val();
        if (emp_code.length == 5) {
            $('form[name="member_register"]')[0].reset();
            // $('.main_form').removeClass('d-none');
        } else {
            $('form[name="member_register"]')[0].reset();
            $('.main_form').addClass('d-none');
        }
    });
    function dobInputage(t){
        var age = getAge(t);
        $('#age').val(age);
        $('#age_display').text(age + ' Years');
        $('.datepicker-dropdown').hide();
    }
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
</script>