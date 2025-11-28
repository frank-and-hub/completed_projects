<script type="text/javascript">
    //-----------------jquery start----------------------
    $(document).ready(function() {
        var created_at = '2023-07-20 18:40:19';
        var date = new Date(created_at);
        $(".effect_from").datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
            todayHighlight: true,
            startDate: date, // Corrected option name
        });



        //--------------jquery validation on commission form-----------------------


        $.validator.addMethod("tenureToComparison", function(value, element, params) {
            var tenure = $("#tenure").val();
            var tenure_to = $("#tenure_to").val();
            var tenure_type = $('#tenure_type').val();

            if (tenure_type == 0) {
                tenure = (tenure * 12);
            }

            var isValidto = parseFloat(tenure_to) > parseFloat(tenure);

            if (isValidto) {
                $.validator.messages.tenureToComparison =
                    "Please check that tenure to not greater than tenure.";
                return false;
            }
            return true;
        }, "");

        $.validator.addMethod("tenureFromComparison", function(value, element, params) {
            var tenure = $("#tenure").val();
            var tenure_from = $("#tenure_from").val();
            var tenure_type = $('#tenure_type').val();

            if (tenure_type == 0) {
                tenure = (tenure * 12);
            }
            var isValidfrom = parseFloat(tenure_from) > parseFloat(tenure);

            if (isValidfrom) {
                $.validator.messages.tenureFromComparison =
                    "Please check that tenure from not greater than tenure.";
                return false;
            }
            return true;
        }, "");


        $.validator.addMethod("tenure_from", function(value, element, params) {
            var tenure_from = $("#tenure_from").val();
            var tenure_to = $("#tenure_to").val();
            var tenure_type = $('#tenure_type').val();

            var isValid = parseFloat(tenure_from) > parseFloat(tenure_to);

            if (!tenure_to) {
                return true;
            }
            if (!isValid) {
                $.validator.messages.tenure_from =
                    "Please check that tenure from is greater than tenure to.";
                return false;
            }
            return true;
        }, "");
        $.validator.addMethod("tenure_to", function(value, element, params) {
            var tenure_from = $("#tenure_from").val();
            var tenure_to = $("#tenure_to").val();
            var tenure_type = $('#tenure_type').val();

            var isValid = parseFloat(tenure_from) < parseFloat(tenure_to);

            if (!tenure_from) {
                return true;
            }
            if (isValid) {
                $.validator.messages.tenure_to = "Please check that tenure to is greater than tenure from.";
                return false;
            }
            return true;
        }, "");

        $("#loancommissionform").validate({
            rules: {
                'tenure_type': 'required',
                'tenure': {
                    'required': true,
                    'number': true,
                },
                'tenure_to': {
                    'required': true,
                    'number': true,
                    'tenure_to': true,
                    'tenureToComparison': true,
                },
                'tenure_from': {
                    'required': true,
                    'tenure_from': true,
                    'tenureFromComparison': true,
                    'number': true,
                },
                'effect_from': 'required',
            },
            messages: {
                'tenure_type': 'Select tenure type',
                'tenure': {
                    'required': 'Tenure is required',
                    'number': 'Enter only numbers',
                },
                'tenure_to': {
                    'required': 'Tenure To is required',
                    'number': 'Enter only numbers',
                },
                'tenure_from': {
                    'required': 'Tenure From is required',
                    'number': 'Enter only numbers',
                },
                'effect_from': 'Effective From is required',
            },
        });

        /*
        $("#next").click(function(e) {
            e.preventDefault();
            var tenure_from = $("#tenure_from").val();
            var tenure_to = $("#tenure_to").val();
            var tenure_type = $('#tenure_type').val();
            if ($("#loancommissionform").valid()) {
                $.ajax({
                    url: "{{ route('admin.investment.plan.commissionPercentage.tenureCheck') }}",
                    type: 'POST',
                    data: {
                        'tenure_type': tenure_type,
                        'tenure': tenure,
                        'effect_from': $("#effect_from").val(),
                        'tenure_to': tenure_to,
                        'tenure_from': tenure_from,
                        'plan_id': plan_id,
                    },
                    success: function(response) {
                        if (response.data > 0) {
                            
                            if (response.data == 1) {
                                swal({
                                    title: 'Warning',
                                    type: 'warning',
                                    text: 'This data is already exist. Are you sure want to insert the data ?',
                                    showDenyButton: true,
                                    showCancelButton: true
                                }, function(isConfirm) {
                                    if (isConfirm) {
                                        $.ajax({
                                            url: "{{ route('admin.investment.plan.commissionPercentage.oldTenureUpdate') }}",
                                            type: "POST",
                                            data: {
                                                'tenure_type': tenure_type,
                                                'tenure': tenure,
                                                'effect_from': $("#effect_from").val(),
                                                'tenure_to': tenure_to,
                                                'tenure_from': tenure_from,
                                                'plan_id': plan_id,
                                                'created_at': created_at,
                                            },
                                            success: function(e) {
                                                if (e.data > 0) {
                                                    $('#collectorPercentageForm').removeClass('d-none');
                                                    $("#next").addClass('d-none');
                                                }
                                            }
                                        });
                                    } else {
                                        $("#tenure_type").val('');
                                        $("#tenure").val('');
                                        $("#effect_from").val('');
                                        $("#tenure_from").val('');
                                        $("#tenure_to").val('');
                                    }
                                });
                            }
                            if (response.data == 2) {
                                swal({
                                    title: 'Warning',
                                    type: 'warning',
                                    text: 'You have to change the value of EFFECTIVE FROM Date',
                                });
                            }
                        } else {
                            $('#collectorPercentageForm').removeClass('d-none');
                            $("#next").addClass('d-none');
                        }

                    },
                    error: function() {
                        return false;
                    }
                });
            }

        });
        */
        /*Save the data into commission_loan_details
         * table name is commission_loan_details
         */

        $("#next").click(function(e) {
            e.preventDefault();
            var tenure_type = $('#tenure_type').val();
            var tenure_from = $("#tenure_from").val();
            var tenure_to = $("#tenure_to").val();
            var effective_from = $("#effect_from").val();
            var tenure = $("#tenure").val();
            var plan_id = $("#plan_id").val();
            if ($("#loancommissionform").valid()) {
                $.ajax({
                    url: "{{ route('admin.investment.plan.commissionPercentage.tenureCheck') }}",
                    type: 'POST',
                    data: {
                        'tenure_type': tenure_type,
                        'tenure': tenure,
                        'effect_from': effective_from,
                        'tenure_to': tenure_to,
                        'tenure_from': tenure_from,
                        'plan_id': plan_id,
                    },
                    success: function(response) {
                        if (response.data) {
                            swal({
                                title: 'Warning',
                                type: 'warning',
                                text: response.msg,
                            });
                        } else {
                            $('#collectorPercentageForm').removeClass('d-none');
                            $("#next").addClass('d-none');
                        }
                    },
                    error: function() {
                        return false;
                    },
                });
            }

        });
        $("#submit").on('click', function(e) {
            e.preventDefault();
            //form validation
            if ($("#loancommissionform").valid()) {
                $.ajax({
                    url: "{{ route('admin.investment.plan.commissionPercentage.store') }}",
                    type: "POST",
                    data: $('#loancommissionform').serialize(),
                    success: function(e) {
                        if (e.data > 0) {
                            var slug = e.slug;
                            window.location.href = "{{ route('admin.plan.show') }}" + "/" +
                                slug;
                        } else {
                            console.log("Data not inserted.");
                        }
                    },
                });
            }
        });

        var plantable = $('#commissionLoanDetails_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            ordering: false,
            sorting: false,
            searching: false,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{{-- route('admin.loan.commission.listing') --}}",
                "type": "post",
                "data": {
                    'id': $("#loan_type_id").val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'tenure_type',
                    name: 'tenure_type'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'status',
                    name: 'status',
                    "render": function(data, type, row) {
                        if (row.status == 0) {
                            return "<span class='badge badge-danger'>Inactive</span>";
                        } else {
                            return "<span class='badge badge-success'>Active</span>";
                        }
                    }
                },
                // {data: 'head_id', name: 'head_id'},
                {
                    data: 'effective_from',
                    name: 'effective_from'
                },
                {
                    data: 'effective_to',
                    name: 'effective_to'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ]
        });
        $(plantable.table().container()).removeClass('form-inline');

        $("#name").keyup(function() {
            var Text = $(this).val();
            Text = Text.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
            $("#slug").val(Text);
        });

        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        /*
         * List the data on the model 
         */
        $(".edit").on('click', function(e) {
            e.preventDefault();
            $(this).addClass('d-none');
            $(this).next().removeClass('d-none');
            $(this).parent().prev('td').find('input').addClass('effect_from');
            $(this).parent().prev('td').prev('td').find('input').attr('readonly', false);
            $(this).parent().prev('td').find('input').attr('readonly', false);
            $(".effect_from").datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true,
            });
        });

        /*
         * Update the data 
         */

        $(".update").on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var loan_type_id = $(".loan_type_id").val();
            var tenure_type = $(".model_tenure_type").val();
            var tenure = $(".model_tenure").val();
            var carder_id = $(this).parent().prev('td').prev('td').prev('td').find('input').val();
            var collector_per = $(this).parent().prev('td').prev('td').find('input').val();
            var effective_from = $(this).parent().prev('td').find('input').val();

            $.ajax({
                url: "{{-- route('admin.loan.commission.update') --}}",
                type: 'POST',
                data: {
                    'id': id,
                    'loan_type_id': loan_type_id,
                    'carder_id': carder_id,
                    'tenure_type': tenure_type,
                    'tenure': tenure,
                    'collector_per': collector_per,
                    'effective_from': effective_from,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(e) {
                    if (e.data > 0) {
                        $(".update").addClass('d-none');
                        $(".edit").removeClass('d-none');
                        $(".collector_per").prop("readonly", true);
                        $(".effect_from").prop("readonly", true);
                        $(".effect_from").removeClass("effect_from");

                    } else {
                        console.log("not changed");
                    }
                }
            });
        });

        //when click on the close button on model disable everything on model


    });
    //-----------jquery end-----------------
</script>
