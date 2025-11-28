require('jquery-validation')


$(document).ready(function () {
    $('#adminLoginForm').submit(function (event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = new FormData(this); // Create a FormData object from the form

        $.ajax({
            url: '/admin/dologin',
            type: 'POST',
            data: formData, // Use the FormData object
            processData: false, // Prevent jQuery from processing data
            contentType: false, // Prevent jQuery from setting content type
            dataType: 'json',
            success: function (response) {

                new Noty({
                    text: 'Login successful!'
                }).show();

                if (response) {
                    // Login successful, redirect or show success message
                    window.location.href = 'dashboard';
                }
            },
            error: function (jqXHR) {
                var response = $.parseJSON(jqXHR.responseText);

                if (response && response.errors && response.errors.error) {
                    var errorMessage = response.errors
                        .error; // Access the error message directly

                    new Noty({
                        type: 'error',
                        text: errorMessage
                    }).show();
                } else {
                    // Handle other errors (for cases when the response isn't in the expected format)
                    new Noty({
                        type: 'error',
                        text: 'An unexpected error occurred.'
                    }).show();
                }
            }

        });
    });
});

// Create Scholarship Form Submission /
$(document).ready(function () {
    var form = $("#scholarship_create");

    form.validate({
        // Your Validation Rules
        rules: {
            name_of_csr: {
                required: true,
                minlength: 2
            },
            scholarship_title: {
                required: true,
                minlength: 5
            },
            year: {
                required: true,
                minlength: 4,
                number: true
            },
            end_date: "required",
            country: "required",
            state: "required",
            district: "required",
            "contact_name[]": "required",
            "contact_mobile[]": {
                required: true,
            },
            "contact_email[]": {
                required: true,
                email: true
            },
            "education_level[]": "required",
            about_scholarship: "required",
            brief_about_scholarship: "required",
            brief_about_csr: "required",
            scholarship_amount: "required",
            eligibility_criteria: "required",
            document_required: "required",
            queries_contact: "required"
        },
        // Your Validation Messages
        messages: {
            name_of_csr: {
                required: "Please enter the Name of CSR",
                minlength: "Your Name of CSR must be at least 2 characters long"
            },
            scholarship_title: {
                required: "Please enter the Scholarship Title",
                minlength: "Your Scholarship Title must be at least 5 characters long"
            },
            year: {
                required: "Please enter the Year",
                minlength: "Year must be at least 4 digits",
                number: "Please enter a valid year"
            },
            end_date: "Please select the End Date",
            country: "Please enter the Country",
            state: "Please enter the State",
            district: "Please enter the District",
            "contact_name[]": "Please enter the Contact Name",
            "contact_mobile[]": {
                required: "Please enter the Contact Mobile",
                number: "Please enter a valid mobile number"
            },
            "contact_email[]": {
                required: "Please enter the Contact Email",
                email: "Please enter a valid email"
            },
            "education_level[]": "Please enter the Education Level",
            about_scholarship: "Please provide information about the scholarship",
            brief_about_scholarship: "Please provide a brief description of the scholarship",
            brief_about_csr: "Please provide a brief description of CSR",
            scholarship_amount: "Please enter the scholarship amount",
            eligibility_criteria: "Please describe the eligibility criteria",
            document_required: "Please list the required documents",
            queries_contact: "Please provide contact information for queries"
        },
        // AJAX Submission
        submitHandler: function (form) {

            // Show loader
            $('#loader').show();

            $.ajax({
                type: "POST",
                url: '/admin/scholarship/create',
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function (response) {
                    new Noty({
                        type: 'success',
                        text: 'Form successfully submitted!'
                    }).show();

                    if (response) {
                        window.location.href = '/admin/scholarship/'
                    }
                },
                error: function (jqXHR) {
                    var response = $.parseJSON(jqXHR.responseText);

                    if (response && response.errors && response.errors.error) {
                        var errorMessage = response.errors
                            .error; // Access the error message directly

                        new Noty({
                            type: 'error',
                            text: errorMessage
                        }).show();

                    } else {
                        // Handle other errors (for cases when the response isn't in the expected format)
                        new Noty({
                            type: 'error',
                            text: 'An unexpected error occurred.'
                        }).show();
                    }
                },
                complete: function () {
                    // Hide loader
                    $('#loader').hide();
                }
            });
        }
    });
});

// Update Scholarship Form Submission 
$(document).ready(function () {
    var form = $("#scholarship_update");

    form.validate({
        // Your Validation Rules
        rules: {
            name_of_csr: {
                required: true,
                minlength: 2
            },
            scholarship_title: {
                required: true,
                minlength: 5
            },
            year: {
                required: true,
                minlength: 4,
                number: true
            },
            end_date: "required",
            country: "required",
            state: "required",
            district: "required",
            "contact_name[]": "required",
            "contact_mobile[]": {
                required: true,
            },
            "contact_email[]": {
                required: true,
                email: true
            },
            "education_level[]": "required"
        },
        // Your Validation Messages
        messages: {
            name_of_csr: {
                required: "Please enter the Name of CSR",
                minlength: "Your Name of CSR must be at least 2 characters long"
            },
            scholarship_title: {
                required: "Please enter the Scholarship Title",
                minlength: "Your Scholarship Title must be at least 5 characters long"
            },
            year: {
                required: "Please enter the Year",
                minlength: "Year must be at least 4 digits",
                number: "Please enter a valid year"
            },
            end_date: "Please select the End Date",
            country: "Please enter the Country",
            state: "Please enter the State",
            district: "Please enter the District",
            "contact_name[]": "Please enter the Contact Name",
            "contact_mobile[]": {
                required: "Please enter the Contact Mobile",
                number: "Please enter a valid mobile number"
            },
            "contact_email[]": {
                required: "Please enter the Contact Email",
                email: "Please enter a valid email"
            },
            "education_level[]": "Please enter the Education Level"
        },

        submitHandler: function (form) {
            $('#loader').show();

            var id = $('#scholarship_id').val();

            $.ajax({
                type: "POST",
                url: '/admin/scholarship/update/' + id,
                data: new FormData(form),
                contentType: false,
                processData: false,
                success: function (response) {
                    new Noty({
                        type: 'success',
                        text: 'Successfully updated!'
                    }).show();

                    if (response) {
                        window.location.href = location
                    }
                },
                error: function (jqXHR) {
                    var response = $.parseJSON(jqXHR.responseText);

                    if (response && response.errors && response.errors.error) {
                        var errorMessage = response.errors.error;

                        new Noty({
                            type: 'error',
                            text: errorMessage
                        }).show();
                    } else {
                        new Noty({
                            type: 'error',
                            text: 'An unexpected error occurred.'
                        }).show();
                    }
                },
                complete: function () {
                    // Hide loader
                    $('#loader').hide();
                }
            });
        }
    });
});
