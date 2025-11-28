<script type="text/javascript">
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            window.location.reload();
        }
    });

    $(document).ready(function() {
        $('#correctionType').change(function() {
            var correctionType = $('#correctionType').val();
            if (correctionType) {
                $('#input_val').removeClass('d-none');
            } else {
                $('#input_val').addClass('d-none');
            }
            if (correctionType == "Customer Details") {
                $('#input_id').html('Customer Id');
            } else if (correctionType == "Associate Details") {
                $('#input_id').html('Associate Id');
            } else {
                $('#input_id').html('Investment Account Id');
            }
            $.ajax({
                type: "POST",
                url: "{!! route('branch.correctionmanagement.fields') !!}",
                dataType: 'JSON',
                data: {
                    'correctionType': correctionType,
                },
                success: function(response) {
                    var optionHtml = '';
                    for (var i = 0; i < response['fields'].length; i++) {
                        optionHtml += '<option value="' + response['fields'][i].id + '">' + response[
                            'fields'][i].field_name + '</option>';
                    }
                    $('#fields').children('option:not(:first-child)[value!=""]').remove();
                    $('#fields').append(optionHtml);
                },
                error: function(xhr, status, error) {},
            });
        });

        // Array of field names
        var fieldNames = ['occupation', 'special_category', 'new_value', 'new_number', 'new_dob', 'gender_new', 'marital_status_new', 'relation_new', 'idType', 'idno', 'religion'];

        // Dynamic validation rules object
        var dynamicValidationRules = {};
        fieldNames.forEach(function(fieldName) {
            dynamicValidationRules[fieldName] = {
                required: isFieldRequired(fieldName)
            };
        });
        dynamicValidationRules['description'] = {
            required: true
        };
        // Add the dynamic rules to the main rules object
        var validationRules = {
            rules: dynamicValidationRules,
            submitHandler: function(form) {
                var formElement = document.getElementById('update_form');
                var formData = new FormData(formElement);
                $('button[type="submit"]').prop('disabled', true);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('branch.correctionmanagement.save') }}",
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response == 1 || response == '1') {
                            swal({
                                title: "Success",
                                text: "Your application has been submitted sucessfully!",
                                type: "success"
                            }, function() {
                                var redirectURL = "{{route('branch.correctionmanagement.request')}}";
                                window.location = redirectURL;
                            });
                            return false;
                        } else {
                            swal('Warning!', "There was a problem", 'warning');
                            return false;
                        }
                        return false;
                    },
                    error: function(xhr, status, error) {
                        // Handle the error case if needed
                        swal('Warning!', "Selected Details Not Found", 'warning');
                        return false;
                    }
                });

            }
        };
        // Apply validation rules to the form
        $('#update_form').validate(validationRules);
        $('#new_dob').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: false,
            autoclose: true,
        });
        $('#new_dob').hover(function() {
            var formattedDate = $("#create_application_date").val();
            // Parse the original date string
            const [day, month, year] = formattedDate.split('/').map(Number);
            // Subtract 100 years
            const newYear = year - 100;
            // Format the new date as "dd/mm/yyyy"
            const formattedNewDate = `${day.toString().padStart(2, '0')}/${month.toString().padStart(2, '0')}/${newYear}`;
            $('#new_dob').datepicker("setStartDate", formattedNewDate);
            // $('#new_dob').datepicker("setEndDate", formattedDate);
        });


        $('#addrequest').validate({
            rules: {
                'correctionType': {
                    required: true
                },
                'fields': {
                    required: true
                },
                'user_info': {
                    required: true
                }
            },
            submitHandler: function(form) {
                // Prevent the form from submitting via the browser
                event.preventDefault();
                // Serialize the form data
                var formData = $(form).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{!! route('branch.correctionmanagement.details') !!}",
                    data: formData,
                    success: function(response) {
                        $('.update_form').val("");
                        $('.old_pic').addClass('d-none');
                        if (response == 'not') {
                            swal('Warning!', "Selected Details Not Found", 'warning');
                            $('.form_comp').addClass('d-none');
                            return false;
                        }
                        if (response == 'b_issue') {
                            swal('Warning!', "This customer does not belongs from this branch", 'warning');
                            $('.form_comp').addClass('d-none');
                            return false;
                        }
                        $('#company_id').val(response.company_id);
                        $('#correction_id').val(response.correction_id);
                        $('#type_id').val(response.id);
                        if (response.field_slug == 'gender') {
                            $('#gender').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            var gender = response.name;
                            if (gender == 0 || gender == '0') {
                                gender = 'Female';
                            } else {
                                gender = 'Male';
                            }
                            $('#old_value').val(gender);
                            return false;
                        }
                        if (response.field_slug == 'marital_status') {
                            $('#marital_status').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            var marital_status = response.name;
                            if (marital_status == 0 || marital_status == '0') {
                                marital_status = 'Unmarried';
                            } else {
                                marital_status = 'Married';
                            }
                            $('#old_value').val(marital_status);
                            return false;
                        }
                        if (response.field_slug == 'dob' || response.field_slug == 'anniversary_date') {
                            var formattedDate = $("#create_application_date").val();
                            var formattedNewDatee = $("#create_application_date").val();
                            const [day, month, year] = formattedDate.split('/').map(Number);
                            // Subtract 18 years
                            const newYear = year - 18;
                            // Format the new date as "dd/mm/yyyy"
                            const formattedNewDate = `${day.toString().padStart(2, '0')}/${month.toString().padStart(2, '0')}/${newYear}`;
                            if (response.field_slug == 'anniversary_date') {
                                $('#new_dob').datepicker("setEndDate", formattedNewDatee);
                            } else {
                                console.log(formattedNewDate);
                                $('#new_dob').datepicker("setEndDate", formattedNewDate);
                            }
                            $('#dob').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            $('#old_value').val(response.name);
                            return false;
                        }
                        if (response.field_slug == 'relation') {
                            handleField(response, 'relation', 'relation_new', '#relation', '#relation_new');
                            return false;
                        }
                        if (response.field_slug == 'state_id') {
                            handleField(response, 'state_id', 'state_id', '#state', '#state_id');
                            return false;
                        }
                        if (response.field_slug == 'religion_id') {
                            handleField(response, 'religion_id', 'religion', '#Religions', '#Religion');
                            return false;
                        }
                        if (response.field_slug == 'special_category_id') {
                            handleField(response, 'special_category_id', 'special_category', '#category', '#special_category');
                            return false;
                        }
                        if (response.field_slug == 'occupation_id') {
                            handleField(response, 'occupation_id', 'occupation', '#occupation_id', '#occupation');
                            return false;
                        }
                        if (response.field_slug == 'first_id_type_id' || response.field_slug == 'second_id_type_id') {
                            handleField(response, response.field_slug, 'idType', '#idTypes', '#idType');
                            return false;
                        }
                        if (response.field_slug == 'photo' || response.field_slug == 'signature') {
                            $('#tochk').val('pic');
                            $('#pphoto').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp, .old_pic').removeClass('d-none');
                            $('#old_value').val(response.name);
                            $('#old_pic').removeClass('d-none').attr('src', response.img);
                            return false;
                        }
                        if (response.field_slug == 'mobile_no' || response.field_slug == 'parent_no' || response.field_slug == 'associate_code' || response.field_slug == 'first_mobile_no') {
                            handleField(response, response.field_slug, 'new_number', '#number', null);
                            return false;
                        } else {
                            handleField(response, response.field_slug, 'new_value', '#normal_case', null);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle the error case if needed
                        swal('Warning!', "Selected Details Not Found", 'warning');
                    }
                });
            }
        });
        $('#new_number').keyup(function() {
            $(this).val($(this).val().replace(/[^0-9-]/g, ""));
        })
        $(document).on('change', '.dataval', function() {
            var actual_value = $(this).find(":selected").data("val");
            console.log(actual_value);
            $('#actual_value').val(actual_value);
        });
        $(document).on('change', '#idType', function() {
            $('#idno').removeClass('d-none');
        });
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $(document).on('change', '#photo', function() {
            $("#upload_form").submit();
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#photo-preview').attr('src', e.target.result);
                    $('#photo-preview').attr('style', 'width:200px; height:200px;');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    function handleField(response, fieldSlug, tochkValue, targetElement, optionSelector) {
        if (response.field_slug == fieldSlug) {
            $('#tochk').val(tochkValue);
            $(targetElement).removeClass('d-none').siblings('div').addClass('d-none');
            $('.form_comp').removeClass('d-none');
            if (optionSelector != null) {
                let fieldValue = $(optionSelector + ' option[data-val="' + response.name + '"]').val();
                $('#old_value').val(fieldValue);
            } else {
                $('#old_value').val(response.name);
            }
            return false;
        }
    }

    function isFieldRequired(valueToCheck) {
        return function(element) {
            return ($("#tochk").val() == valueToCheck);
        };
    }
</script>