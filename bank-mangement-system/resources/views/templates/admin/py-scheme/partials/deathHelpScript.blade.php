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

        //------------------------------------this is validation js that our months from always less then or equal to months to field || start
        $.validator.addMethod('ge', function(value, element, param) {
            return this.optional(element) || Number(value) >= Number($(param).val());
        }, 'Months to must be greater then or equal to months from');
        $('[name="monthsFrom"]').on('change blur keyup', function() {
            $('[name="monthsTo"]').valid(); // <- trigger a validation test
        });
        //--------------------------------------this is validation js that our months from always less then or equal to months to field || end

        //--------------------------------------this is our death Help delete funcitonalty || start 
        $(document).on('click', '.delete_btn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            swal({
                    title: "Are you sure?",
                    text: "Do you want to delete this death help setting?",
                    type: "warning",
                    showCancelButton: true,
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            url: "{{ route('deathHelp.destroy') }}",
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
        //----------------------------------------------this is our death help delete funcitonalty || end

        //----------------------------------------------when admin click on add button we need to blank the form || start
        $('#add_btn_span').click(function(e) {
            $('.reqstar').show();
            $('.error').remove();
            $('#deathHelpForm').attr('action', 'admin/py-plans/death-help');
            $('#deathHelpForm').trigger('reset');
            $("form#deathHelpForm :input:not(.readonly)").each(function() {
                $(this).removeAttr(
                    'disabled');
            });
        });
        //-----------------------------------------------when admin click on add button we need to blank the form || end

        //-----------------------------------------------when admin click on edit button then current row data fills on the form and form modal is shown || start
        $('.edit_btnn').click(function() {
            $('.reqstar').hide();
            $('.starneed').show();
            let id = $(this).data('value');
            $('#deathHelpModel').modal('show');
            $('#deathHelpForm').attr('action', 'admin/py-plans/death-help/update');
            $('#tenure').val($('.tenure_' + id).val()).attr('disabled', 'true');
            $('#editInput').val(id);
            $('#monthsFrom').val($('.months_from_' + id).val()).attr('disabled', 'true');
            $('#monthsTo').val($('.months_to_' + id).val()).attr('disabled', 'true');
            $('#death_help_percentage').val($('.percentage_' + id).val());
            $('#tenure_effective_from').val($('.effectiveFrom_' + id).val()).attr('disabled', 'true');
            $('#tenure_effective_from').attr('data-val', $('.effectiveFrom_' + id).val());
            $('#tenure_effective_to').val($('.effectiveTo_' + id).val());
        });
        //----------------------------------------------when admin click on edit button then current row data fills on the form and form modal is shown || end

        //----------------------------------------------This is our death help setting add and edit form validation || start 
        $('#deathHelpForm').validate({
            rules: {
                tenure: {
                    required: true,
                    number: true,
                },
                monthsFrom: {
                    required: true,
                    number: true,
                },
                monthsTo: {
                    ge: '#monthsFrom',
                    required: true,
                    number: true,
                },
                death_help_percentage: {
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
                monthsFrom: {
                    required: "Months from field is required",
                    number: "Plase enter numbers only",
                },
                monthsTo: {
                    required: "Months to field is required",
                    number: "Plase enter numbers only",
                },
                death_help_percentage: {
                    required: "Death Help field is required",
                    number: "Plase enter numbers only",
                },
                tenure_effective_from: {
                    required: "Date field is required",
                },


            },
            submitHandler: function(form) {
                if ($('#editInput').val() == '') {
                    $.ajax({
                        url: "{{ route('deathHelp.check') }}",
                        type: 'POST',
                        data: $('#deathHelpForm').serialize(),
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
                                        text: 'This death help setting already has taken',
                                        type: 'warning'
                                    });
                                    return false;
                                }
                            }

                            if ((Number($('#monthsTo').val()) > Number(data
                                    .monthfrom)) && (
                                    Number($('#monthsFrom').val()) < Number(data
                                        .monthfrom)
                                )) {
                                let error =
                                    `<label id="monthsTo-error" class="error" for="monthsTo">Months to must be less  then ${data.monthfrom} </label>`;
                                $('#monthsTo').after(error);
                                return false;
                            }
                            if (!(Number($('#monthsFrom').val()) > Number(data
                                    .monthTo)) &&
                                (
                                    Number($('#monthsFrom').val()) > Number(data
                                        .monthfrom))
                            ) {
                                let error =
                                    `<label id="monthsTo-error" class="error" for="monthsTo">Months from must be greater  then ${data.monthTo}</label>`;
                                $('#monthsFrom').after(error);
                                return false;
                            }
                            if (Number($('#monthsTo').val()) > Number($('#tenure')
                                    .val())) {
                                let error =
                                    `<label id="monthsTo-error" class="error" for="monthsTo">Months to must be less  then tenure</label>`;
                                $('#monthsTo').after(error);
                                return false;
                            }
                            form.submit();


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
        //-----------------------------------------------------------This is our death help setting add and edit form validation || end 
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
