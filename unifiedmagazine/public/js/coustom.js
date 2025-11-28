$((e) => {
var baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');
$('#send_email_btn').on('click', function(e) {
    e.preventDefault();
    var msg = $('#message').html(); // Get the HTML content of the #message element
    $('#hidden_message').val(msg); // Set the obtained HTML content to the hidden input field
    $('#registrationForm').submit(); // Submit the form
});
    $('#registrationForm').validate({
        rules: {
            app_password: {
                required: true
            },
            to: {
                required: true,
            },
            subject: {
                required: true
            },
            message: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please enter your app password."
            },
            to: {
                required: "Please select email user file.",
            },
            subject: {
                required: "Please enter your email subject."
            },
            message: {
                required: "Please enter your email message."
            }
        },
        submitHandler: function (form) {
            $(form).find('button[type="submit"]').prop('disabled', true);
            form.submit();
            // Get form data
            var formData = $(form).serialize();

            // AJAX request
            let url = baseUrl + '/emails/store';
            $.post(url, formData, function (e) {
                // Handle success response
                alert('Email Send Successfully !');
            }, 'JSON').fail(function (xhr, status, error) {
                // error handling
                console.log("Error:", textStatus, errorThrown);
            });
        }
    });
    $('input, select, textarea, button').css('box-shadow', 'none');

    $('#userregistrationForm').validate({
        rules: {
            name: {
                required: true
            },
            email: {
                required: true,
            },
            password: {
                required: true
            },
            cpassword: {
                required: true
            }
        }
        /*
        submitHandler: function(form) {
            $(form).find('button[type="submit"]').prop('disabled', true);
            form.submit();
            // Get form data
            var formData = $(form).serialize();

            // AJAX request
            $.post("{{route('user.store')}}", formData,function(e) {
                    // Handle success response
                    alert('User created Successfully !');
                },'JSON').fail(function(xhr, status, error) {
                    // error handling
                    console.log("Error:", textStatus, errorThrown);
                });
        }*/
    });
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
});

