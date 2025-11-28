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
                        console.log(response[i]);
                        optionHtml += '<option value="' + response['fields'][i].id + '">' + response[
                            'fields'][i].field_name + '</option>';
                    }
                    $('#fields').children('option:not(:first-child)[value!=""]').remove();
                    $('#fields').append(optionHtml);
                },
                error: function(xhr, status, error) {},
            });
        });

        $('#update_form').validate({ // initialize the plugin
            rules: {
                'old_value': {
                    required: true
                }
            },
            submitHandler: function(form) {
                // Prevent the form from submitting via the browser
                event.preventDefault();
                // Serialize the form data
                $('button[type="submit"]').prop('disabled',true);
                return true;

                var formData = $(form).serialize();
                $.ajax({
                    type: 'POST',
                    url: "{!! route('branch.correctionmanagement.save') !!}",
                    data: formData,
                    success: function(response) {
                        if (response == 1 || response == '1') {
                            swal('Success!', "Your application has been sent", 'success');
                            $('.update_form').val('');
                            $('#correctionType').val('');
                            $('#correctionType').trigger('change');
                            $('#update_form').addClass('d-none');
                            return false;
                        } else {
                            swal('Warning!', "There was a problem", 'warning');
                            return false;
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle the error case if needed
                        swal('Warning!', "Selected Details Not Found", 'warning');
                    }
                });
            }
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
                        // Handle the Ajax response 
                        if (response == 'not') {
                            swal('Warning!', "Selected Details Not Found", 'warning');
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
                            $('#dob').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            $('#old_value').val(response.name);
                            return false;
                        }
                        if (response.field_slug == 'relation') {
                            $('#relation').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            $('#old_value').val(response.name);
                            return false;
                        }
                        if (response.field_slug == 'state_id') {
                            $('#relation').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            $('#old_value').val(response.name);
                            return false;
                        }
                        if (response.field_slug == 'mobile_no' || response.field_slug == 'parent_no' || response.field_slug == 'associate_code' || response.field_slug == 'first_mobile_no' || response.field_slug == 'first_mobile_no') {
                            $('#number').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            $('#old_value').val(response.name);
                            return false;
                        } else {
                            $('#normal_case').removeClass('d-none').siblings('div').addClass('d-none');
                            $('.form_comp').removeClass('d-none');
                            $('#old_value').val(response.name);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle the error case if needed
                        swal('Warning!', "Selected Details Not Found", 'warning');
                    }
                });
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