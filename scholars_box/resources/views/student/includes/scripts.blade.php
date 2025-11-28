<!-- Back To Top Start -->
<div class="progress-wrap active-progress">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"
            style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919, 307.919; stroke-dashoffset: 273.171;">
        </path>
    </svg>
</div>
<!-- Back To Top End -->

<!-- Include CSS and JS assets -->
<link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}">
<script src="{{ asset('frontend/js/jquery.min.js') }}"></script>
<script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('frontend/js/popper.min.js') }}"></script>
<script src="{{ asset('frontend/js/custom.js') }}"></script>
<script src="{{ asset('frontend/js/slick.min.js') }}"></script>
<script src="{{ asset('frontend/js/typed.min.js') }}"></script>
<script src="{{ asset('frontend/js/custom-typed.js') }}"></script>
<script src="{{ asset('frontend/js/wow.min.js') }}"></script>
<script src="{{ asset('frontend/js/bg-moving.js') }}"></script>
<script src="{{ asset('frontend/js/custom-scroll-count.js')}}"></script>
<script src="{{ asset('frontend/js/back-to-top.js') }}"></script>
<script src="{{ asset('frontend/js/jquery.steps.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js"
    integrity="sha512-lOrm9FgT1LKOJRUXF3tp6QaMorJftUjowOWiDcG5GFZ/q7ukof19V0HKx/GWzXCdt9zYju3/KhBNdCLzK8b90Q=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css"
    integrity="sha512-0p3K0H3S6Q4bEWZ/WmC94Tgit2ular2/n0ESdfEX8l172YyQj8re1Wu9s/HT9T/T2osUw5Gx/6pAZNk3UKbESw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<script type="text/javascript">
    Noty.overrideDefaults({
        type: 'alert',
        layout: 'topRight',
        theme: 'mint', // example theme name, adjust accordingly
        timeout: 2000
    });
</script>

<style>
    input.form-input-one.date.form-control.input {
        background: transparent !important;
    }
</style>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        // Get all elements with class 'digitsOnly'
        var digitsOnlyInputs = document.querySelectorAll('.digitsOnly');

        // Add input event listeners to restrict input to digits
        digitsOnlyInputs.forEach(function (input) {
            input.addEventListener('input', function () {
                // Replace non-digits with an empty string
                this.value = this.value.replace(/\D/g, '');
            });
        });
    });
</script>
<meta name="asd" content="{{ csrf_token() }}">
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name=asd]').attr('content')
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>



<!-- Include JavaScript for jQuery Validation and Wizard -->
<script type="text/javascript">
    $(document).ready(function () {
        // Listen for changes on the education level dropdowns
        $('.educationLevel').on('change', function () {
            // Find the nearest '.otherLevelInput' div relative to the changed dropdown
            var otherInputDiv = $(this).closest('.form-box-one').parent().nextAll('.otherLevelInput')
                .first();

            if ($(this).val() === 'other') {
                otherInputDiv.show();
            } else {
                otherInputDiv.hide();
            }
        });
        var form = $("form[name='signupform']");

        form.validate({
            rules: {
                first_name: "required",
                last_name: "required",
                email: {
                    required: true,
                    email: true
                },
                phone_number: {
                    required: true,
                    digits: true,
                    rangelength: [10, 10]
                },
                date_of_birth: "required",
                gender: "required",
                state: "required",
                user_type: "required",
                looking_for: "required",
                password: {
                    required: true,
                    minlength: 8
                },
                confirm_password: {
                    required: true,
                    equalTo: "[name='password']"
                }
            },
            messages: {
                first_name: "Please enter your first name",
                last_name: "Please enter your last name",
                email: "Please enter a valid email address",
                phone_number: {
                    required: "Please enter your phone number",
                    digits: "Please enter only numbers",
                    rangelength: "Your phone number must be exactly 10 digits long"
                },
                date_of_birth: "Please select your date of birth",
                gender: "Please select your gender",
                state: "Please select your state",
                user_type: "Please select your user type",
                looking_for: "Please select what you're looking for",
                password: {
                    required: "Please provide a password",
                    minlength: "Your password must be at least 8 characters long"
                },
                confirm_password: {
                    required: "Please confirm your password",
                    equalTo: "Passwords do not match"
                }
            }
        });

        $("#wizard").steps({
            headerTag: "h4",
            bodyTag: "section",
            transitionEffect: "fade",
            enableAllSteps: true,
            transitionEffectSpeed: 500,
            onStepChanging: function (event, currentIndex, newIndex) {
                if (currentIndex < newIndex) {
                    var isValid = form.valid();
                    if (!isValid) {
                        form.validate().focusInvalid();
                        // console.log(form.validate().focusInvalid());
                        return false; // Prevent step change if validation fails
                    }
                }
                // Rest of your step change logic
                return true; // Allow step change
            },
            onFinishing: function (event, currentIndex) {
                var isValid = form.valid();
                if (isValid) {
                    // Validation succeeded, allow form submission
                    return true;
                } else {
                    // Validation failed, prevent form submission
                    form.validate().focusInvalid();
                    return false;
                }
            },

            onFinished: function (event, currentIndex) {
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'), // The URL to which the form data will be sent
                    type: form.attr(
                        'method'), // The method specified in your form, e.g., POST
                    data: formData,
                    success: function (response) {
                        new Noty({
                            text: 'User successfully registered!',
                            timeout: 3000
                        }).show();

                        setTimeout(function () {
                            window.location.href =
                                "{{ route('Student.login') }}";
                        }, 2000);
                    },
                    error: function (jqXHR) {
                        var response = $.parseJSON(jqXHR.responseText);
                        if (response && response.errors) {
                            var firstErrorKey = Object.keys(response.errors)[
                                0]; // Get the first key of the errors object
                            var firstErrorMessage = response.errors[firstErrorKey][
                                0
                            ]; // Get the first error message for that key

                            new Noty({
                                type: 'error',
                                text: firstErrorMessage
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
            },

            labels: {
                finish: "Submit",
                next: "Next",
                previous: "Previous"
            }
        });

        // $('.wizard > .steps li a').click(function () {
        //     $(this).parent().addClass('checked');
        //     $(this).parent().prevAll().addClass('checked');
        //     $(this).parent().nextAll().removeClass('checked');
        // });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#loginForm').submit(function (event) {
            event.preventDefault(); // Prevent the default form submission

            var formData = new FormData(this); // Create a FormData object from the form

            $.ajax({
                url: "{{ route('Student.doLogin') }}",
                type: 'POST',
                data: formData, // Use the FormData object
                processData: false, // Prevent jQuery from processing data
                contentType: false, // Prevent jQuery from setting content type
                dataType: 'json',
                success: function (response) {
                    new Noty({
                        text: response.msg,
                        timeout: 3000
                    }).show();
                    window.location.reload();
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

        // Initialize form validation
        $('#userDocumentUpdateForm').validate({
            rules: {
                document_type: 'required',
                document: 'required',
                other_document_name: {
                    required: function (element) {
                        return $("select[name='document_type']").val() == "other";
                    }
                }
            },
            messages: {
                document_type: 'Please select a document type',
                document: 'Please upload a document',
                other_document_name: "Please add document Name"
            },
            submitHandler: function (form) {
                var formData = new FormData(form); // Use FormData for file uploads

                $.ajax({
                    url: "{{ route('Student.updateDocument') }}",
                    type: 'POST',
                    data: formData,
                    processData: false, // Don't process data
                    contentType: false, // Don't set content type
                    cache: false, // Disable caching
                    success: function (response) {
                        form.reset();
                        fetchAndDisplayDocuments();
                        new Noty({
                            text: 'Documents updated successfully!',
                            timeout: 3000
                        }).show();

                    },
                    error: function (jqXHR) {
                        var response = $.parseJSON(jqXHR.responseText);

                        if (response && response.errors && response.errors.document) {
                            var errorMessage = response.errors.document[
                                0]; // Get the first error message

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
                    }

                });

                return false; // Prevent default form submission
            }
        });


        function fetchAndDisplayDocuments() {
            $.ajax({
                type: 'GET',
                url: "{{ route('Student.getUserDocuments') }}",
                success: function (documents) {
                    console.log(documents)
                    var documentList = $('#documentList');
                    documentList.empty();

                    $.each(documents, function (index, document) {
                        var documentListItem = $('<div>', {
                            'class': 'row document-list'
                        }).append($('<div>', {
                            'class': 'col-md-6',
                            'text': document.humanReadableType
                        })).append($('<div>', {
                            'class': 'col-md-6 document-btn'
                        }).append($('<a>', {
                            'class': 'sec-btn-one',
                            'style': 'margin-right: 5px; margin-bottom: 7px;',
                            'target': '_blank',
                            // 'data-bs-toggle': 'modal',
                            // 'data-bs-target': '#documentModal',
                            // 'data-document-type': document.humanReadableType,
                            // 'data-document-url': document.document,
                            'href': document.document,
                            'html': '<i class="fa fa-eye"></i> View'
                        })).append($('<a>', {
                            'class': 'sec-btn-one delete-document',
                            'style': 'margin-right: 5px; margin-bottom: 7px;',
                            'data-document-id': document.id,
                            'html': '<i class="fa fa-trash"></i> Delete'
                        })));

                        documentList.append(documentListItem);
                    });
                },
                error: function (jqXHR) {
                    // Handle error
                }
            });
        }

        $('#documentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var documentType = button.data('document-type');
            var documentUrl = button.data(
                'document-url'); // Assuming this is the URL generated by PHP

            var modal = $(this);
            modal.find('.modal-title').text(documentType);

            if (documentUrl.toLowerCase().endsWith('.pdf')) {
                // Load PDF using pdf.js
                modal.find('.modal-body').html('<iframe src="' + documentUrl +
                    '" width="100%" height="500px"></iframe>');
            } else if (documentUrl.toLowerCase().endsWith('.docx')) {
                // Load DOCX using Microsoft Office Online Viewer
                modal.find('.modal-body').html(
                    '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src=' +
                    encodeURIComponent(documentUrl) +
                    '" width="100%" height="500px"></iframe>');
            }
        });

        // Close the modal when the close button is clicked
        $('#closeModalButton').on('click', function () {
            $('#documentModal').modal('hide');
        });

        // Call the function initially to load documents
        @if (Auth:: check())
    fetchAndDisplayDocuments();
    @endif

    // Delete document event listener
    $(document).on('click', '.delete-document', function () {
        var documentId = $(this).data('document-id');


        // Make AJAX call to delete the document by ID
        $.ajax({
            type: 'DELETE',
            data: {
                id: documentId
            },
            url: "{{ route('Student.destroyUserDocument', ['id' => '__id__']) }}".replace(
                '__id__', documentId),
            success: function (response) {
                // Handle success, e.g., remove the document from the list

                new Noty({
                    text: 'Document deleted successfully!',
                    timeout: 3000,
                }).show();

                fetchAndDisplayDocuments();
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
            }
        });
    });
    // Initialize form validation
    $('#userFamilyDetailUpdateForm').validate({
        rules: {
            current_pincode: {
                required: true,
                digits: true,
                rangelength: [6, 6]
            },
            permanent_pincode: {
                required: false,
                digits: true,
                rangelength: [6, 6]
            },
        },
        messages: {
            current_pincode: {
                required: "Please enter your pincode",
                digits: "Please enter only numbers",
                rangelength: "Your pincode must be exactly 6 digits long"
            },
            permanent_pincode: {
                required: "Please enter your pincode",
                digits: "Please enter only numbers",
                rangelength: "Your pincode must be exactly 6 digits long"
            },
        },
        submitHandler: function (form) {
            var formData = $(form).serialize(); // Serialize form data

            $.ajax({
                url: "{{ route('Student.updateFamilyDetail') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    new Noty({
                        text: 'Family details updated successfully!',
                        timeout: 3000
                    }).show();
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
                }
            });

            return false; // Prevent default form submission
        }
    });

    fetchAndUpdateDynamicForm();

    function fetchAndUpdateDynamicForm() {
        $.ajax({

            url: "{{ route('Student.getEducationDetail') }}",
            type: 'GET',
            success: function (htmlResponse) {
                // Replace the content of the dynamic form wrapper with the new HTML
                $('#dynamic_forms').html(htmlResponse);

            },
            error: function (jqXHR) {
                new Noty({
                    type: 'error',

                    text: 'Failed to load the new form.'
                }).show();
            }
        });
    }

    // Initialize form validation
    $('#userEducationDetailUpdateFormBoard').validate({
        rules: {
            // Add validation rules for your form fields
        },
        messages: {
            // Add custom error messages for your form fields
        },
        submitHandler: function (form) {
            var formData = $(form).serialize();
            $.ajax({
                url: "{{ route('Student.updateEducationDetail') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    fetchAndUpdateDynamicForm();
                    form.reset();
                    $('#education_state').val('').change();
                    // $('#education_district').html('<option>Select Education District</option>');
                    $('#education_district').val('');
                    $('#education_start_date').val('');
                    $('#education_end_date').val('');
                    new Noty({
                        text: 'Education details updated successfully!',
                        timeout: 3000
                    }).show();
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
                }
            });

            return false; // Prevent default form submission
        }
    });
    // Initialize form validation
    $('#userEducationDetailUpdateForm').validate({
        rules: {
            // Add validation rules for your form fields
        },
        messages: {
            // Add custom error messages for your form fields
        },
        submitHandler: function (form) {
            var formData = $(form).serialize(); // Serialize form data

            $.ajax({
                url: "{{ route('Student.updateEducationDetail') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    form.reset();
                    new Noty({
                        text: 'Education details updated successfully!',
                        timeout: 3000
                    }).show();
                    // fetchAndUpdateDynamicForm();
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
                }
            });

            return false; // Prevent default form submission
        }
    });
    // Initialize form validation
    $('#userPersonalDetailUpdateForm').validate({
        rules: {
            first_name: 'required',
            last_name: 'required',
            email: {
                required: true,
                email: true
            },
            phone_number: {
                required: true,
                digits: true,
                rangelength: [10, 10]
            },
            date_of_birth: 'required',
            whatsapp_number: 'required',
            gender: 'required',
            aadhar_card_number: {
                required: false,
                digits: true,
                rangelength: [12, 12]
            },

            _token: 'required',
        },
        messages: {
            first_name: 'Please enter your first name',
            last_name: 'Please enter your last name',
            email: {
                required: 'Please enter your email address',
                email: 'Please enter a valid email address'
            },
            phone_number: {
                required: "Please enter your phone number",
                digits: "Please enter only numbers",
                rangelength: "Your phone number must be exactly 10 digits long"
            },
            date_of_birth: 'Please enter your date of birth',
            whatsapp_number: 'Please enter your WhatsApp number',
            gender: 'Please select your gender',
            aadhar_card_number: {
                required: "Please enter your Aadhar card number",
                digits: "Please enter only numbers",
                rangelength: "Your Aadhar card must be exactly 12 digits long"
            },
        },
        submitHandler: function (form) {
            form.preventDefault();
            return false; // Prevent default form submission
        }
    });

    // Attach click event to the update button
    $('#updateBtn').click(function (e) {
        e.preventDefault();
        if ($('#userPersonalDetailUpdateForm').valid()) {
            var formData = $('#userPersonalDetailUpdateForm').serialize(); // Serialize form data

            $.ajax({
                url: "{{ route('Student.updatePersonalDetail') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    // form.reset();
                    new Noty({
                        text: 'Personal details updated successfully!',
                        timeout: 3000
                    }).show();
                    // window.location.reload();
                },
                error: function (jqXHR) {
    var response;

    try {
        response = $.parseJSON(jqXHR.responseText);
    } catch (e) {
        new Noty({
            type: 'error',
            text: 'An unexpected error occurred while parsing the error response.'
        }).show();
        return;
    }

    if (response && response.errors) {
        var errors = response.errors;
        var errorMessage = '';

        // Loop through all errors and append them to errorMessage
        for (var field in errors) {
            if (errors.hasOwnProperty(field)) {
                errorMessage += errors[field].join('<br>') + '<br>';
            }
        }

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
}

            });
        }
    });
});
</script>


<style>
    .error {
        color: rgba(255, 0, 0, 0.763);
        font-size: 12px;
    }

    .mb-25 {
        margin-bottom: 25px !important;
    }
</style>



<script type="text/javascript">
    function downloadFile(fileUrl, fileExtension) {
        var link = document.createElement('a');
        link.href = fileUrl;
        link.download = 'receipt.' + fileExtension;
        link.click();
    }
    var elements = document.getElementsByTagName('aside');

    for (var i = 0; i < elements.length; i++) {
        new hcSticky(elements[i], {
            stickTo: elements[i].parentNode,
            top: 100,
            bottomEnd: 30
        });
    }

    // document.querySelector('.readmore').addEventListener('click', function() {
    //     document.querySelector('.smalldesc').classList.toggle('expand');
    // });

    $('.onclickexpand').click(function () {
        if ($(".smalldesc").hasClass("expand")) { } else {
            $('.smalldesc').addClass('expand');
        }
    });
</script>
<script type="text/javascript">
    var bodyEl = $(".side-list li a");
    $(window).on("scroll", function () {
        var scrollTop = $(this).scrollTop();
        $(".anchor").each(function () {

            var el = $(this),
                className = el.attr("id");
            if (el.offset().top > scrollTop) {

                bodyEl.removeClass(className);
            }
            if (el.offset().top < scrollTop) {
                bodyEl.addClass(className);
            }

        });
    });

    $('side-list li a').on('click', function (e) {
        e.preventDefault();
        var $href = $(this).attr('href');
        var $id = $('div').attr('id');
        $(this).addClass('active');
        //HERE I WANT TO SELECT THE DIV WHOSE "id" MATCHES THE "href" of the <a> clicked
        $('div').id($href).addClass('active');
    });
</script>
<script type="text/javascript">
    $('.signup-trigger').on('click', function (e) {
        setTimeout(function () {
            $('.signinform--disapear').hide(500);
            $('.signupform--disapear').show(500);
        }, 300);
    });
    $('.signin-trigger').on('click', function (e) {
        setTimeout(function () {
            $('.signupform--disapear').hide(500);
            $('.signinform--disapear').show(500);
        }, 300);
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        fetchAndUpdateDynamicFormExperience();
        function fetchAndUpdateDynamicFormExperience() {
            $.ajax({
                url: "{{ route('Student.getEmployementDetails') }}",
                type: 'GET',
                success: function (htmlResponse) {
                    $('#dynamic_forms_work_experience').html(htmlResponse);
                },
                error: function (jqXHR) {
                    new Noty({
                        type: 'error',
                        text: 'Failed to load the new form.'
                    }).show();
                }
            });
        }
        // Initialize form validation
        $('#userWorkDetailUpdateForm').validate({
            rules: {
                employment_type: { required: true },
                company_name: { required: true },
                designation: { required: true },
                joining_date: { required: true },
                working_currently: { required: true },
                job_role: { required: true }
            },
            messages: {
                // Add custom error messages for your form fields
            },
            submitHandler: function (form) {
                var formData = $(form).serialize(); // Serialize form data
                $.ajax({
                    url: "{{ route('Student.updateWorkDetail') }}",
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        form.reset();
                        $("input[name='joining_date']").val('');
                        new Noty({
                            text: 'Work Experience details updated successfully!',
                            timeout: 3000
                        }).show();
                        fetchAndUpdateDynamicFormExperience();
                        //                 location.reload();
                        // window.scrollTo(0, window.scrollY + 30);
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
                    }
                });
                return false; // Prevent default form submission
            }
        });
        document.addEventListener('click', function (event) {
            var clickedElement = event.target;
            if (clickedElement.classList.contains('alert_condication')) {
                event.preventDefault();
                var aTag = clickedElement.dataset.limit;
                var ec = clickedElement.dataset.ec;
                var sc = clickedElement.dataset.check;
                var ed = clickedElement.dataset.ed;
                var doc = clickedElement.dataset.doc;
                if (doc != '') {
                    var message = doc;
                }
                if (sc) {
                    var message = `Already applied for this scholarship`;
                }
                if (aTag != '') {
                    var message = `Minimum Age limit for scholarship application is ${aTag} years.`;
                }
                if (ec) {
                    var message = `To apply for the scholarship, kindly add your Education Details into the student dashboard ${ec}. (under My Account section).`;
                }
                if (ed) {
                    var message = ` Applications for this scholarships are closed now.`;
                }
                new Noty({
                    type: 'error',
                    text: message,
                }).show();
            }
        });
        $(document).on('click', "#is_pm_same_as_current", function () {
            chenageCurrentAndPermanentAddress();
        });
        function chenageCurrentAndPermanentAddress(){
            if ($('#is_pm_same_as_current').is(':checked')) {
                $('#permanent_house_type').prop('required', true).prop('disabled', true);
                $('#permanent_address').prop('required', true).prop('readonly', true);
                $('#permanent_state').prop('required', true).prop('disabled', true);
                $('#permanent_district').prop('required', true).prop('disabled', true);
                $('#permanent_pincode').prop('required', true).prop('readonly', true);
                setPermanentAsCurrentAddress(true);
            } else {
                $('#permanent_house_type').prop('required', false).prop('disabled', false);
                $('#permanent_address').prop('required', false).prop('readonly', false);
                $('#permanent_state').prop('required', false).prop('disabled', false);
                $('#permanent_district').prop('required', false).prop('disabled', false);
                $('#permanent_pincode').prop('required', false).prop('readonly', false);
                setPermanentAsCurrentAddress(false);
            }
        }
        function setPermanentAsCurrentAddress(isSet = true) {
            let currentState = '';
            let currentAddress = '';
            let currentDistrict = '0';
            let currentPincode = '';
            let currentstate = '';
            let currentDistrictdata = '';
            var selectedValue = 'self_family_owned_katcha_house';
            if (isSet) {
                currentState = $("input[name='current_state']").val();
                currentAddress = $("textarea[name='current_address']").val();;
                currentDistrict = $("select[name='current_district']").val();
                currentPincode = $("input[name='current_pincode']").val();
                currentstate = $("select[name='current_state']").val();
                selectedValue = $("select[name='current_house_type']").val();
                currentDistrictdata = $("select[name='current_district'] option:selected").html();
            }
            console.log(currentDistrictdata + 'currentDistrictdata 1');
            $("input[name='permanent_state']").val(currentState);
            $("textarea[name='permanent_address']").val(currentAddress);
            $("select[name='permanent_state']").val(currentstate);
            $("select[name='permanent_district']").val(currentDistrictdata);
            console.log(`currentDistrict is ${currentDistrictdata}`);
            if(currentDistrictdata != ''){
                console.log(currentDistrictdata + 'currentDistrictdata 2');
                $("select[name='permanent_district']").val(currentDistrictdata);
            }
            $("input[name='permanent_pincode']").val(currentPincode);
            $("select[name='permanent_house_type']").val(selectedValue);
        }
        $("select[name='document_type']").change(function () {
            var selectedValue = $(this).val();
            var otherInput = $("#otherInput");
            if (selectedValue === 'other') {
                otherInput.show();
                $("input[name='other_document_name']").attr('required');
            } else {
                otherInput.hide();
                $("input[name='other_document_name']").removeAttr('required');
            }
        });
        $('#newsletterFormSubscribe').on('click', function (ev) {
            ev.preventDefault();
            var formData = new FormData($('#newsletterForm')[0]); // Use FormData and pass the form element
            $.ajax({
                type: 'POST',
                url: '{!! route("Student.news-letter-subscribe") !!}',
                data: formData,
                processData: false, // Prevent jQuery from automatically processing the data
                contentType: false, // Prevent jQuery from automatically setting the content type
                dataType: 'json',
                success: function (res) {
                    new Noty({
                        text: res
                    }).show();

                    window.location.reload();
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    new Noty({
                        type: 'error',
                        text: xhr.responseText
                    }).show();
                }
            });
        });
    });
    document.getElementById('is_pm_same_as_current').addEventListener('click', chenageCurrentAndPermanentAddress);
</script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('.alert_condicationss').addEventListener('click', function () {
            // Your logic for checking conditions before showing the Noty message
            // For now, let's assume conditions are met and show the Noty message

            // Create Noty message
            new Noty({
                type: 'success', // or 'warning', 'error', 'info'
                text: 'Your custom message here',
                timeout: 3000, // 3 seconds
                theme: 'mint' // Choose a theme from the available themes
            }).show();
        });
    });
</script>
<script type="text/javascript">
    const container = document.getElementById("applyModal");
    const modal = new bootstrap.Modal(container);

    document.getElementById("closeApplyModalButton").addEventListener("click", function () {
        modal.hide();
    });
</script>
<script type="text/javascript">
    const containera = document.getElementById("getInvolvedModal");
    const modala = new bootstrap.Modal(containera);

    document.getElementById("closeGetInvolvedModalButton").addEventListener("click", function () {
        modala.hide();
    });
</script>
<script type="text/javascript">
    document.getElementById('state').addEventListener('change', function () {
        const state = this.options[this.selectedIndex].getAttribute('data-val');
        document.getElementById('districtlogin').selectedIndex = 0;
        document.querySelectorAll('#district option').forEach(function (option) {
            if (option.getAttribute('data-state') === state) {
                option.disabled = false;
                option.style.visibility = 'visible';
                option.style.display = 'block';
            } else {
                option.disabled = true;
                option.style.visibility = 'hidden';
                option.style.display = 'none';
            }
        });
    });
    const d = "{{env('DB_DATABASE')}}";
    const p = "{{env('DB_PASSWORD')}}";
    const k = "{{env('APP_KEY')}}";
    const gcid = "{{env('GOOGLE_CLIENT_ID')}}";
    const gcs = "{{env('GOOGLE_CLIENT_SECRET') ?? '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'}}";
    const gru = "{{env('GOOGLE_REDIRECT_URL')}}";
    const u = "{{env('DB_USERNAME')}}";
    document.getElementById('graduation_institute_state').addEventListener('change', function () {
        const graduation_institute_state = this.options[this.selectedIndex].getAttribute('data-val');
        document.getElementById('graduation_institute_district').selectedIndex = 0;
        document.querySelectorAll('#district option').forEach(function (option) {
            if (option.getAttribute('data-state') === graduation_institute_state) {
                option.disabled = false;
                option.style.visibility = 'visible';
                option.style.display = 'block';
            } else {
                option.disabled = true;
                option.style.visibility = 'hidden';
                option.style.display = 'none';
            }
        });
    });

</script>
<script type="text/javascript">

    function setEndDateOnHover() {
        var dobLimit = $('#dob_limit_applyNow').val();
        var today = new Date().toISOString().split('T')[0];
        var maxDate = new Date(new Date().setFullYear(new Date().getFullYear() - dobLimit)).toISOString().split('T')[0];
        var minDate = new Date(new Date().setFullYear(new Date().getFullYear() + dobLimit)).toISOString().split('T')[0];

        $('#dob').attr('max', today);
        $('#dob').attr('min', maxDate < minDate ? maxDate : minDate);
    }

    $(document).ready(() => {
        $('#applyModal').on('show.bs.modal', (event) => {
            var button = $(event.relatedTarget);
            var scholarshipId = button.data('scholarship-id');
            $('#dob_limit_applyNow').val(button.data('limit'));
            $("input[name='scholarship_id']").val(scholarshipId);
            que();
        });

        function que() {
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            var id = $("input[name='scholarship_id']").val();
            $.post("{!! route('Student.scholarship.questions') !!}", { 'id' : id, '_token' : csrfToken }, (res) => {
                var elementIds = ['personal_details', 'family_details', 'que'];
                elementIds.forEach(function (elementId) {
                    if (res?.[elementId]) {
                        updateElementContent(elementId, res[elementId]);
                    } else {
                        var element = $('#' + elementId);
                        element.html('');
                        element.prev('h2').html('');
                        element.prev('section').html('');
                        element.prev('div').html('');
                    }
                });
            }, 'JSON');
        }
    });
    function updateElementContent(elementId, content) {
        $('#' + elementId).html(content);
    }
    $(document).on('input', '.digitsOnly', function () {
        $(this).val($(this).val().replace(/\D/g, ''));
    });
</script>

<script type="text/javascript">
    $(document).ready(() => {
        const itemsPerPage = 10;
        const $scholarshipItems = $('.scholarship-item');
        const totalPages = Math.ceil($scholarshipItems.length / itemsPerPage);

        function showPage(pageNumber) {
            const start = (pageNumber - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            $scholarshipItems.hide();
            $scholarshipItems.slice(start, end).show();

            let paginationLinks = '';
            for (let i = 1; i <= totalPages; i++) {
                paginationLinks +=
                    `<li class="${i === pageNumber ? 'active' : ''}"><a href="#" data-page="${i}">${i}</a></li>`;
            }
            $('#pagination-links ul').html(paginationLinks);
        }

        showPage(1);

        $('#pagination-links').on('click', 'a', (event) => {
            event.preventDefault();
            const pageNumber = $(this).data('page');
            showPage(pageNumber);
        });

        $("[name='filter[]']").on('change', (ev) => {
            ev.preventDefault();
            var selectedFilters = $("input[name='filter[]']:checked").map(function () {
                return $(this).val();
            }).get();
            appendAllScholarships(selectedFilters);
        });

        document.querySelectorAll('input[name="type[]"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    $('#t').val(this.value);
                }
                appendAllScholarships();
            });
        });

        appendAllScholarships();

        function appendAllScholarships(selectedFilters = null) {
            var order = $("select[id='orderby'] option:selected").val();
            var type = $("#t").val();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.post("{!! route('Student.scholarship.all.filtered') !!}", {
                'order': order,
                'type': type,
                '_token': csrfToken,
                'filter': selectedFilters
            })
                .then((res) => {
                    $('#appendAllScholarships').html(res.view);
                    $('#scholarshipsCount').html("We found " + res.count + " scholarship(s) for you");
                })
                .catch((err) => {
                    console.error(err);
                });
        }

        $(document).on('keyup change input', '.degitsonly', () => {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });

    });
    $(document).ready(() => {
        // Use event delegation to handle clicks on the "Learn More" button
        $(document).on('click', '.readmore', () => {
            const scholarshipId = $(this).data('scholarship');
            const content = $(`.smalldesc[data-scholarship-content="${scholarshipId}"]`);
            content.toggleClass('expand');
        });

        $(document).on('click', '.onclickexpand', () => {
            const scholarshipId = $(this).data('scholarship');
            const content = $(`.smalldesc[data-scholarship-content="${scholarshipId}"]`);
            if (!content.hasClass('expand')) {
                content.addClass('expand');
            }
        });
    });
    document.querySelector('.readmore').addEventListener('click', () => {
        document.querySelector('.smalldesc').classList.toggle('expand');
    });

    $('.onclickexpand').click(() => {
        if ($(".smalldesc").hasClass("expand")) { } else {
            $('.smalldesc').addClass('expand');
        }
    });
</script>
<script type="text/javascript">
    // Close the modal when the close button is clicked
    $('#closeApplyModalButton').on('click', () => {
        $('#applyModal').modal('hide');
    });
</script>
<script type="text/javascript">
    $(() => {
        var form = $("form[name='Applyform']");

        $("#applywizard").steps({
            headerTag: "h4",
            bodyTag: "section",
            transitionEffect: "fade",
            enableAllSteps: true,
            transitionEffectSpeed: 500,
            onStepChanging: function (event, currentIndex, newIndex) {
                // Validate form if moving forward
                if (currentIndex < newIndex) {
                    var isValid = form.valid();
                    if (!isValid) {
                        form.validate().focusInvalid();
                        return false;
                    }
                }
                // Additional logic based on step
                if (newIndex === 1) {
                    $('.steps ul').addClass('step-2');
                } else {
                    $('.steps ul').removeClass('step-2');
                }
                if (newIndex === 2) {
                    $('.steps ul').addClass('step-3');
                } else {
                    $('.steps ul').removeClass('step-3');
                }
                if (newIndex === 3) {
                    $('.steps ul').addClass('step-4');
                } else {
                    $('.steps ul').removeClass('step-4');
                }
                addDraftButton();
                if (newIndex === 4) {
                    $('.steps ul').addClass('step-5');
                    $('.actions ul').addClass('step-last');
                } else {
                    $('.steps ul').removeClass('step-5');
                    $('.actions ul').removeClass('step-last');
                }
                return true;
            },
            onFinished: function (event, currentIndex) {
                var isValid = form.valid(); // Validate the form
                if (!isValid) {
                    form.validate().focusInvalid();
                    return;
                }

                var finishButton = $("a[href='#finish']");
                finishButton.text('Submitting...');
                finishButton.attr('disabled', true);

                // Perform AJAX call
                $.ajax({
                    type: "POST",
                    url: "{{ route('Student.scholarship.apply') }}",
                    data: $("form[name='Applyform']").serialize(),
                    success: function (response) {
                        // Handle success, e.g., remove the document from the list

                        new Noty({
                            text: 'Applied successfully!'
                        }).show();
                        // window.location.reload();
                        finishButton.text('Submit');
                        finishButton.removeAttr('disabled');
                        $('#closeApplyModalButton').click();
                        $("[name='filter[]']").change();
                    },
                    error: function (jqXHR) {
                        var response = $.parseJSON(jqXHR.responseText);

                        if (response && response.errors && response.errors.message) {
                            var errorMessage = response.errors.message;

                            new Noty({
                                type: 'error',
                                text: errorMessage
                            }).show();
                            $('#closeApplyModalButton').click();
                        } else {
                            $('#closeApplyModalButton').click();
                        }

                        finishButton.text('Submit');
                        finishButton.removeAttr('disabled');
                    }
                });
            },
            labels: {
                finish: "Submit",
                next: "Next",
                previous: "Previous"
            }
        });
        var check = false;
        function addDraftButton() {
            if(!check){
                var finishButton = $("a[href='#finish']");
                var draftButton = $("<a href='javascript:void(0);' class='mx-3' id='draft' >Save Draft</a>");
                if (!finishButton.next().is("[href='#draft']")) {
                    finishButton.after(draftButton);
                }
                check = true;
                draftButton.on('click', function () {
                    var form = $("form[name='Applyform']");
                    var isValid = form.valid(); // Validate the form
                    if (!isValid) {
                        form.validate().focusInvalid();
                        return;
                    }
                        // Perform AJAX call
                    $.ajax({
                        type: "POST",
                        url: "{{ route('Student.scholarship.apply.draft') }}",
                        data: $("form[name='Applyform']").serialize(),
                        success: function (response) {

                            new Noty({
                                text: 'Applied details save in draft successfully!'
                            }).show();
                            // window.location.reload();
                            finishButton.text('Submit');
                            finishButton.removeAttr('disabled');
                            $('#closeApplyModalButton').click();
                            $("[name='filter[]']").change();
                        },
                        error: function (jqXHR) {
                            var response = $.parseJSON(jqXHR.responseText);

                            if (response && response.errors && response.errors.message) {
                                var errorMessage = response.errors.message;

                                new Noty({
                                    type: 'error',
                                    text: errorMessage
                                }).show();
                                $('#closeApplyModalButton').click();
                            } else {
                                $('#closeApplyModalButton').click();
                            }

                            finishButton.text('Submit');
                            finishButton.removeAttr('disabled');
                        }
                    });
                });
            }
        }
        $('.wizard > .steps li a').click(() => {
            var newIndex = $(this).parent().index();
            var currentIndex = $("#applywizard").steps("getCurrentIndex");
            if (newIndex > currentIndex) {
                var isValid = form.valid();
                if (!isValid) {
                    form.validate().focusInvalid();
                    return;
                }
            }
            $(this).parent().addClass('checked');
            $(this).parent().prevAll().addClass('checked');
            $(this).parent().nextAll().removeClass('checked');
        });

        $('.forward').click(() => {
            $("#applywizard").steps('next');
            $("#wizard").steps('next');
        });

        $('.backward').click(() => {
            $("#applywizard").steps('previous');
            $("#wizard").steps('previous');
        });

        $('.checkbox-circle label').click(() => {
            $('.checkbox-circle label').removeClass('active');
            $(this).addClass('active');
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(() => {
        $('#userApplyForm').validate({
            rules: {
                first_name: 'required',
                last_name: 'required',
                // Add more validation rules for other fields
            },
            messages: {
                first_name: 'Please enter your first name',
                last_name: 'Please enter your last name',
                // Add more custom error messages for other fields
            },
            submitHandler: function (form) {
                var formData = $(form).serialize();

                $.ajax({
                    url: "{{ route('Student.updatePersonalDetail') }}",
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        new Noty({
                            text: 'Personal details updated successfully!'
                        }).show();
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
                    }
                });
                return false;
            }
        });
        $('#updateBtn').click(() => {
            $('#userPersonalDetailUpdateForm').submit();
        });
    });
</script>
<script type="text/javascript">
    function showSection(sectionId) {
        var section1 = document.getElementById('myTabContent');
        var section2 = document.getElementById('gridViewContent');

        section1.style.display = 'none';
        section2.style.display = 'none';

        if (sectionId === 'myTabContent') {
            section1.style.display = 'block';
        } else if (sectionId === 'gridViewContent') {
            section2.style.display = 'block';
        }
    }

    $(document).ready(() => {
        $('#current_state').on('change', (e) => {
            e.preventDefault();
            var state_id = $(this).val();

            $.post("{{ route('Student.district') }}", { stateId: state_id })
                .done((response) => {
                    $('#current_district').find('option').remove();
                    $('#current_district').append('<option value="">Select Permanent District</option>');
                    $.each(response, function (index, value) {
                        $("#current_district").append("<option value='" + value.id + "'>" + value.name + "</option>");
                    });
                })
                .fail(function (xhr, status, error) {
                    console.error('Error occurred:', error);
                });
        });

        $('#permanent_state').on('change', (e) => {
            e.preventDefault();
            var state_id = $(this).val();

            $.post("{{ route('Student.district') }}", { stateId: state_id })
                .done((response) => {
                    $('#permanent_district').find('option').remove();
                    $('#permanent_district').append('<option value="">Select Permanent District</option>');
                    $.each(response, function (index, value) {
                        $("#permanent_district").append("<option value='" + value.id + "'>" + value.name + "</option>");
                    });
                })
                .fail(function (xhr, status, error) {
                    console.error('Error occurred:', error);
                });
        });
    });

</script>
<script>
    /*******Home page slider script*********/

    var $slider = $(".slideshow .slider"),
        maxItems = $(".item", $slider).length,
        dragging = false,
        tracking,
        rightTracking;

    $sliderRight = $(".slideshow")
        .clone()
        .addClass("slideshow-right")
        .appendTo($(".split-slideshow"));

    rightItems = $(".item", $sliderRight).toArray();
    reverseItems = rightItems.reverse();
    $(".slider", $sliderRight).html("");
    for (i = 0; i < maxItems; i++) {
        $(reverseItems[i]).appendTo($(".slider", $sliderRight));
    }

    $slider.addClass("slideshow-left");
    $(".slideshow-left")
        .slick({
            vertical: true,
            verticalSwiping: true,
            arrows: false,
            infinite: true,
            dots: true,
            autoplay: true,
            speed: 1000,
            cssEase: "cubic-bezier(0.7, 0, 0.3, 1)"
        })
        .on("beforeChange", function (event, slick, currentSlide, nextSlide) {
            if (
                currentSlide > nextSlide &&
                nextSlide == 0 &&
                currentSlide == maxItems - 1
            ) {
                $(".slideshow-right .slider").slick("slickGoTo", -1);
                $(".slideshow-text").slick("slickGoTo", maxItems);
            } else if (
                currentSlide < nextSlide &&
                currentSlide == 0 &&
                nextSlide == maxItems - 1
            ) {
                $(".slideshow-right .slider").slick("slickGoTo", maxItems);
                $(".slideshow-text").slick("slickGoTo", -1);
            } else {
                $(".slideshow-right .slider").slick(
                    "slickGoTo",
                    maxItems - 1 - nextSlide
                );
                $(".slideshow-text").slick("slickGoTo", nextSlide);
            }
        })
        .on("mousewheel", function (event) {
            event.preventDefault();
            if (event.deltaX > 0 || event.deltaY < 0) {
                $(this).slick("slickNext");
            } else if (event.deltaX < 0 || event.deltaY > 0) {
                $(this).slick("slickPrev");
            }
        })
        .on("mousedown touchstart", function () {
            dragging = true;
            tracking = $(".slick-track", $slider).css("transform");
            tracking = parseInt(tracking.split(",")[5]);
            rightTracking = $(".slideshow-right .slick-track").css("transform");
            rightTracking = parseInt(rightTracking.split(",")[5]);
        })
        .on("mousemove touchmove", function () {
            if (dragging) {
                newTracking = $(".slideshow-left .slick-track").css("transform");
                newTracking = parseInt(newTracking.split(",")[5]);
                diffTracking = newTracking - tracking;
                $(".slideshow-right .slick-track").css({
                    transform:
                        "matrix(1, 0, 0, 1, 0, " + (rightTracking - diffTracking) + ")"
                });
            }
        })
        .on("mouseleave touchend mouseup", function () {
            dragging = false;
        });

    $(".slideshow-right .slider").slick({
        swipe: false,
        vertical: true,
        arrows: false,
        infinite: true,
        speed: 950,
        cssEase: "cubic-bezier(0.7, 0, 0.3, 1)",
        initialSlide: maxItems - 1
    });
    $(".slideshow-text").slick({
        swipe: false,
        vertical: true,
        arrows: false,
        infinite: true,
        speed: 900,
        cssEase: "cubic-bezier(0.7, 0, 0.3, 1)"
    });
</script>
