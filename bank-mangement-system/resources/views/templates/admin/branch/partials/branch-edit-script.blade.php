<script type="text/javascript" src="https://www.technicalkeeda.com/js/javascripts/plugin/jquery.js"></script>
<script type="text/javascript" src="https://www.technicalkeeda.com/js/javascripts/plugin/jquery.validate.js"></script>
<script type="text/javascript">
        $(document).ready(function() {
            // Branch Form validations
            $('#branch-update').validate({ // initialize the plugin
                rules: {
                    state : 'required',
                    city : 'required',
                    sector : 'required',
                    regan : 'required',
                    zone : 'required',
                    pin_code : {
                        required: true,
                        minlength: 6,
                        maxlength: 6,
                        digits: true
                    },
                    address : 'required',
                    phone : {
                        required: true,
                        minlength: 10,
                        maxlength: 10,
                        digits: true
                    },
                    cash_in_hand:{
                        number:true
                    },
                    password: {
                        minlength : 6
                    },
                    password_confirmation: {
                        minlength : 6,
                        equalTo : "#password"
                    },
                },
                messages: {
                    state:{
                        required: 'Please select a State.',
                    },
                    city:{
                        required: 'Please select a City.',
                    },
                    zone:{
                        required: 'Please enter Zone/Sector.',
                    },
                    pin_code:{
                        required: 'Please enter Postal Code.',
                        minlength: 'Please enter at least 6 characters.',
                        maxlength: 'Please enter no more than 6 characters',
                        digits: 'Please enter only digits',
                    },
                    address:{
                        required: 'Please enter Address.',
                    },
                    phone:{
                        required: 'Please enter valid Phone Number.',
                        minlength: 'Please enter at least 10 characters.',
                        maxlength: 'Please enter no more than 10 characters',
                        digits:  'Please enter only digits'
                    },
                    password:{
                        minlength: 'Please enter at least 6 characters.',
                    },
                    password_confirmation:{
                        minlength: 'Please enter at least 6 characters.',
                        equalTo:  'Password did not matched'
                    },
                }
            });

            /* Branch change password validations */
            function validatePassword() {
                $('#branchChangePassword').validate({
                    rule: {
                        password: {
                            required: true,
                            minlength : 6
                        },
                        password_confirmation: {
                            required: true,
                            minlength : 6,
                            equalTo : '#password'
                        },
                    },
                    message: {
                        password:{
                            minlength: 'Please enter at least 6 characters.',
                        },
                        password_confirmation:{
                            minlength: 'Please enter at least 6 characters.',
                            equalTo:  'Password did not matched'
                        },
                    }
                });
            }

            /* get city from state **/
            $(document).on('change','#state',function(){
                var stateId = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{!! route('cities') !!}" ,
                    data: {'stateId':stateId},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var select = $('.city');
                        select.empty().append(' <option >--Select City--</option>');
                        $.each(response, function(key, value) {
                            $('.city').append($("<option></option>")
                                    .attr("value", key)
                                    .text(value));
                        });
                    }
                });
            });

            function isNumberKey(evt) {
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if (charCode != 46 && charCode > 31 &&
                    (charCode < 48 || charCode > 57))
                    return false;

                return true;
            }
        });
</script>
