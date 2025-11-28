<script type="text/javascript">
$(document).ready(function() {
    $("#select_date").hover(function() {
        var edate = $('#create_application_date').val();
        var date = $('#company_register_date').val();

        // $('#select_date').datepicker('destroy'); // Destroy any existing datepicker instance

        $('#select_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
        });

        // Set the startDate and endDate options dynamically
        $('#select_date').datepicker('setStartDate', date);
        $('#select_date').datepicker('setEndDate', edate);
    });

    $('#category').select2({
        width: '100%',
        placeholder: 'Select Calegory ',
        language: {
            noResults: function() {
                return '<button id="no-results-btn add_category"  data-toggle="modal" data-target="#categoryForm" class="btn btn-primary" > Add Category</a>';
            },
        },
        escapeMarkup: function(markup) {
            return markup;
        },
    });

    $('#closed_cat').on("click", function() {
        $('#cat_name').val(' ');
        $('#cat_name_error').html(' ');
    });

    $('#category_save').on("click", function() {
        $('#cat_name_error').html('');
        var selected = [];
        for (var option of document.getElementById('category').options) {
            if (option.selected) {
                selected.push(option.value);
            }
        }
        if ($('#cat_name').val() != '') {
            $('#categoryForm').modal('hide');
            $.ajax({
                type: "POST",
                url: "{!! route('admin.vendor_category_add') !!}",
                dataType: 'JSON',
                data: {
                    'name': $('#cat_name').val(),
                    'created_at': $('#created_at').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == 'success') {
                        $('#category').find('option').remove();
                        $('#category').append(
                            '<option value="">--- Select Category ---</option>');
                        $.each(response.data_cat, function(index, value) {
                            $("#category").append("<option value='" + value.id +
                                "'>" + value.name + "</option>");
                        });
                        selected.push(response.id);
                        $("#category").val(selected);
                        $('#cat_name').val('');

                    } else {
                        swal("Error!", "Something wrong! " + " " + response.error,
                            "error");
                    }
                }
            });
        } else {
            $('#cat_name_error').html('Please enter name');
        }
    });
    $('#state').on("change", function() {
        var state = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.citylists') !!}",
            dataType: 'JSON',
            data: {
                'district_id': state
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#city').find('option').remove();
                $('#city').append('<option value="">Select city</option>');
                $.each(response.city, function(index, value) {
                    $("#city").append("<option value='" + value.id + "'>" +
                        value
                        .name + "</option>");
                });
            }
        });
    });
    $.validator.addMethod("checkIdNumber", function(value, element, p) {
        if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value) == true) {
            result = true;
        } else {
            $.validator.messages.checkIdNumber = "Please enter valid pan card no";
            result = false;
        }

        return result;
    }, "");
    $.validator.addMethod("chk_created", function(value, element, p) {
        moment.defaultFormat = "DD/MM/YYYY HH:mm";
        var f1 = moment($('#select_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var f2 = moment($('#ssb_account_date').val() + ' 00:00', moment.defaultFormat).toDate();
        var from = new Date(Date.parse(f2));
        var to = new Date(Date.parse(f1));
        result = false;
        if ($('#ssb_account_date').val() != '') {
            if (to >= from) {
                $.validator.messages.chk_created = "";
                result = true;
            } else {
                $.validator.messages.chk_created =
                    "Register date  must be greater than or equal to SSB account date.";
                result = false;
            }
        }
        return result;
    }, "")
    // disable copy paste function
    $('#account_no , #confirm_account_no').on('cut copy paste', function(event) {
        event.preventDefault();
    });

    // end copy paste


    $('#searchCompanyId').on('change', function() {
        var company_id = $(this).val();
        console.log(company_id);
        $.ajax({
            type: "POST",
            url: "{{route('admin.vendor.companydate')}}",
            dataType: 'JSON',
            data: {
                'company_id': company_id,
            },
            success: function(response) {
                $('#company_register_date').val(response);
            }
        });
    });


    $('#ssb_account').on('change', function() {
        var ssb_account = $(this).val();
        var name = $('#name').val().toLowerCase();
        var company_id = $('#searchCompanyId').val();
        if (company_id == '') {
            swal("Warning!", "Please Select Company First", "warning");
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{!! route('admin.employ.check.ssb.account') !!}",
            data: {
                ssb_account: ssb_account,
                comapny_id: company_id
            },
            dataType: "JSON",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.resCount == 1) {
                    if (company_id == response.account_no.company_id) {
                        if (ssb_account == response.account_no.account_no && $.trim(
                                name) == $.trim(response.name.toLowerCase())) {
                            $('#ssb_account').val(response.account_no.account_no);
                            $('#ssb_account_id').val(response.account_no.id);
                            $('#ssb_account_date').val(response.ssbDate);
                            $('#select_date').val(response.ssbDate);

                        } else {
                            swal("Error!", "Contact  name or ssb account holder name(" +
                                response.name.toLowerCase() + ") not match!",
                                "error");
                            $('#ssb_account').val('');
                            $('#ssb_account_id').val('');
                            $('#ssb_account_date').val('');
                        }
                    } else {
                        swal("Error!",
                            " This ssb Account does not belongs to selected company!",
                            "error");
                        $('#ssb_account').val('');
                        $('#ssb_account_id').val('');
                        $('#ssb_account_date').val('');
                    }
                } else {
                    swal("Error!", " SSB account not found!", "error");
                    $('#ssb_account').val('');
                    $('#ssb_account_id').val('');
                    $('#ssb_account_date').val('');

                }
            }
        })

        $('#searchCompanyId').on('change', function() {
            $('#ssb_account').val('');
            $('#ssb_account_id').val('');
            $('#ssb_account_date').val('');
            $('#name').val('');
            $('#company_register_date').val('');
        })
    })
    $('#name').on('keyup', function() {
        if ($("#ssb_account").val() != '') {
            $("#ssb_account").trigger("change");
        }
    })
    $('#gst_treatment').on('change', function() {
        var gst_val = $("#gst_treatment").val()
        if (gst_val == 3) {
            $("#gst_no_div").hide();
        } else {
            $("#gst_no_div").show();
        }
    })
    $('#type').on('change', function() {
        var gst_val = $("#type").val()
        if (gst_val == 1) {
            $("#cat_div_sh").hide();
        } else {
            $("#cat_div_sh").show();
        }
    })
    $.validator.addMethod("dateDdMm", function(value, element, p) {
        if (this.optional(element) ||
            /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g
            .test(value) == true) {
            $.validator.messages.dateDdMm = "";
            result = true;
        } else {
            $.validator.messages.dateDdMm = "Please enter valid date.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("needsSelection", function(value, element) {
        alert('hhh');
        var count = $(element).find('option:selected').length;
        result = false;

        if (count > 0) {
            $.validator.messages.needsSelection = "";
            result = TRUE;
        } else {
            $.validator.messages.needsSelection = "Please select category.";
            result = false;
        }
        return result;
    }, "");
    $.validator.addMethod("checkGst", function(value, element, p) {
        if (this.optional(element) ||
            /[0-9]{2}[a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}[1-9A-Za-z]{1}[Z]{1}[0-9a-zA-Z]{1}/g.test(
                value) ==
            true) {
            $.validator.messages.checkGst = "";
            result = true;
        } else {
            $.validator.messages.checkGst = "Please enter valid gst no.";
            result = false;
        }
        return result;
    }, "");
    $('#vendor').validate({
        rules: {
            select_date: {
                required: true,
                dateDdMm: true,
            },
            type: "required",
            name: "required",
            company_name: "required",
            email: {
                email: function(element) {
                    if ($("#email").val() != '') {
                        return true;
                    } else {
                        return false;
                    }
                },
            },
            mobile: {
                required: true,
                number: true,
                minlength: 10,
                maxlength: 12
            },
            gst_treatment: "required",
            searchCompanyId: "required",
            gst_no: {
                required: true,
                checkGst: '#gst_no',
            },
            pan_card: {
                required: true,
                checkIdNumber: '#pan_card',
            },
            category: {
                // required: true,
                needsSelection: true,
            },
            state: "required",
            city: "required",
            zip_code: {
                required: true,
                number: true,
                minlength: 6,
                maxlength: 6
            },
            bank_name: "required",
            account_no: {
                required: true,
                number: true,
                minlength: 8,
                maxlength: 16,

            },
            confirm_account_no: {
                required: true,
                number: true,
                minlength: 8,
                maxlength: 16,
                equalTo: '#account_no'
            },
            ifsc_code: {
                required: true,

            },
            ssb_account: {
                number: true,
            },
            ssb_account_date: {
                chk_created: function(element) {
                    if ($("#ssb_account").val() != '') {
                        return true;
                    } else {
                        return false;
                    }
                },
            },

        },
        messages: {
            select_date: {
                required: "Please  select register date.",
            },

            type: "Please select type",
            name: "Please enter name.",
            company_name: "Please enter company name.",



            email: {
                email: "Please enter valid email id.",
            },
            mobile: {
                required: "Please enter mobile number.",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  10 or maximum 12 digit.",
                maxlength: "Please enter minimum  10 or maximum 12 digit."
            },
            gst_treatment: "Please select GST Treatment",
            gst_no: {
                required: "Please enter gst number.",
                checkGst: "Please enter valid gst no.",
            },
            pan_card: {
                required: "Please enter pan number.",
                checkIdNumber: '#pan_card',
            },
            category: "Please select category",
            state: "Please select state",
            city: "Please select city",
            zip_code: {
                required: "Please enter zip code",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  6 digit.",
                maxlength: "Please enter maximum  6 digit."
            },
            bank_name: "Please enter bank name",
            account_no: {
                required: "Please enter account number",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  8 digit.",
                maxlength: "Please enter maximum  16 digit."
            },
            confirm_account_no: {
                required: "Please confirm account number",
                number: "Please enter valid number.",
                minlength: "Please enter minimum  8 digit.",
                maxlength: "Please enter maximum  16 digit.",
                equalTo: 'Please enter the same account number as above'
            },
            ifsc_code: "Please enter IFSC code",


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
            $('#vendor_add').prop('disabled', true);
            return true;
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