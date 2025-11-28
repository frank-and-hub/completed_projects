<script>

    $(document).ready(function() {

        // -----------------------------------------------jquery date picker js start  

        $("#tenure_effective_from").hover(function() {

            $("#tenure_effective_from").datepicker({

                format: "dd/mm/yyyy",

                autoclose: true,

                todayHighlight: true,

                startDate: '+0d'

            }).on("changeDate", function(e) {

                $('#tenure_effective_to').datepicker('setDate', e.date, 'format', "dd/mm/yyyy");

                $('#tenure_effective_to').datepicker('setStartDate', e.date, 'format',

                    "dd/mm/yyyy");

            });

            $("#tenure_effective_to").datepicker({

                format: "dd/mm/yyyy",

                autoclose: true,

                orientation: "bottom",

            });

        })

        $('#tenure_effective_to').hover(function() {

            if ($('#editInput').val() != '') {

                let date = $('#tenure_effective_from').attr('data-val');

                $('#tenure_effective_to').datepicker({

                    format: "dd/mm/yyyy",

                    autoclose: true,

                    todayHighlight: true,

                    startDate: date,

                })

            }

        });



        //------------------------------------------------jquery date picker js end



        //------------------------------------this is validation js that our months always less then or equal to tenure field || start

        $.validator.addMethod('le', function(value, element, param) {

            return this.optional(element) || Number(value) <= Number($(param).val());

        }, 'Months are less then or equal to Tenure');

        $('[name="tenure"]').on('change blur keyup', function() {

            $('[name="months"]').valid(); // <- trigger a validation test

        });

        //--------------------------------------this is validation js that our months always less then or equal to tenure field || end



        //--------------------------------------this is our money back delete funcitonalty || start 

        $(document).on('click', '.delete_btn', function(e) {

            e.preventDefault();

            const id = $(this).data('id');

            swal({

                    title: "Are you sure?",

                    text: "Do you want to delete this money back setting?",

                    type: "warning",

                    showCancelButton: true,

                },

                function(isConfirm) {

                    if (isConfirm) {

                        $.ajax({

                            url: "{{ route('moneyBack.destroy') }}",

                            type: 'POST',

                            data: {

                                'id': id

                            },

                            headers: {

                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                            },

                            success: function(e) {

                                location.reload();

                            },

                            error: function() {

                                console.log('error');

                                return false;

                            }

                        });

                    }



                }



            );

        });

        //----------------------------------------------this is our money back delete funcitonalty || end



        //----------------------------------------------when admin click on add button we need to blank the form || start

        $('#add_btn_span').click(function(e) {

            $('.reqstar').show();

            $('.error').remove();

            $('#moneyForm').attr('action', 'admin/py-plans/money-back');

            $('#moneyForm').trigger('reset');

            $('#months').removeAttr('readonly'); 
            $("form#moneyForm :input:not(.readonly)").each(function() {

                $(this).removeAttr('disabled'); 

            });

        });

        //-----------------------------------------------when admin click on add button we need to blank the form || end



        //-----------------------------------------------when admin click on edit button then current row data fills on the form and form modal is shown || start

        $('.edit_btnn').click(function() {

            $('.reqstar').hide();

            $('.starneed').show();

            $('#moneybackmodel').modal('show');

            $('#moneyForm').attr('action', 'admin/py-plans/money-back/update');

            let id = $(this).data('value');

            $('#tenure').val($('.tenure_' + id).val()).attr('disabled', 'true');

            $('#editInput').val(id);

            $('#months').val($('.months_' + id).val()).attr('readonly', 'true');

            $('#money_percentage').val($('.percentage_' + id).val());

            $('#tenure_effective_from').val($('.effectiveFrom_' + id).val()).attr('disabled', 'true');

            $('#tenure_effective_from').attr('data-val', $('.effectiveFrom_' + id).val());

            $('#tenure_effective_to').val($('.effectiveTo_' + id).val());

        });

        //----------------------------------------------when admin click on edit button then current row data fills on the form and form modal is shown || end



        //----------------------------------------------This is our money back setting add and edit form validation || start 

        $('#moneyForm').validate({

            rules: {

                tenure: {

                    required: true,

                    number: true,

                },

                months: {

                    le: '#tenure',

                    required: true,

                    number: true,

                },

                money_percentage: {

                    required: true,

                    number: true,

                },

                tenure_effective_from: {

                    required: true,

                },



            },

            messages: {

                tenure: {

                    required: "Tenure field is required",

                    number: "Plase enter numbers only",

                },

                months: {

                    required: "Months field is required",

                    number: "Plase enter numbers only",

                },

                money_percentage: {

                    required: "Money percentage field is required",

                    number: "Plase enter numbers only",

                },

                tenure_effective_from: {

                    required: "Date field is required",

                },





            },

            submitHandler: function(form) {

                if ($('#editInput').val() == '') {

                    $.ajax({

                        url: "{{ route('MoneyBack.check') }}",

                        type: 'POST',

                        data: $('#moneyForm').serialize(),

                        headers: {

                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                        },

                        success: function(response) {

                            // $('.error').remove();

                            let data = JSON.parse(response);



                            if (data.dataa != null) {



                                if (data.dataa.effective_to != null) {

                                    let date1 = new Date($('#tenure_effective_from')

                                            .val().split('/').reverse().join('-'))

                                        .getTime();

                                    let date2 = new Date(data.dataa.effective_to)

                                        .getTime();

                                    if (date1 > date2) {

                                        form.submit();

                                    } else {

                                        swal({

                                            title: 'Sorry!',

                                            text: `Please select the date after ${data.dataa.effective_to}`,

                                            type: 'warning'

                                        });

                                        return false;

                                    }

                                } else {

                                    swal({

                                        title: 'Sorry!',

                                        text: 'This Money Back Setting already has taken',

                                        type: 'warning'

                                    });

                                    return false;

                                }

                            } else {

                                form.submit();

                            }



                        },

                        error: function() {

                            return false;

                        }

                    });

                } else {

                    form.submit();

                }



            }

        });

        //-----------------------------------------------------------This is our money back setting add and edit form validation || start 

        $('.status_button').click(function(e) {

            e.preventDefault();

            var src = $(this).attr('href');

            let sw = swal({

                    title: "Are you sure?",

                    text: "Do you want to change the status?",

                    type: "warning",

                    showCancelButton: true,

                },

                function(isConfirm) {

                    if (isConfirm) {

                        window.location.href = src;

                    }



                }

            );



            return false;



        });



    }); //------------------------jquery doc ready end

</script>

