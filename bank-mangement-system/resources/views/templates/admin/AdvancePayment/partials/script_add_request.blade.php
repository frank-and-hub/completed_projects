<script type="text/javascript">
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
$(document).on('click', '#tasubmit', function() {
    $('#addrequest').submit();
});
var demandAdviceTable;
// Using for hide and show section according to payment type
$('#paymentType').on('change', function() {
    var selected = $('option:selected', this).val();
    if (selected == '') {
        $('h3').text("Advance Payment");
        $(".taadvance").addClass('d-none');
        $("form :input:not(:checkbox,:radio,:submit,:select)").val("");

        $("#date").val("");
    }

    if (selected == 2) {
        $(".taadvance").removeClass('d-none');
        $('.taadvance').show();
        $('#ta_employee_code').val('');
        $('#advanced_rent_party_name').val('');
        $('#ename').val('');
        $('#narration').val('');
        $('#aamount').val('');
        $('#advanced_salary_mobile_number2').val('');
        $('#advanced_salary_bank_name2').val('');
        $('#advanced_salary_bank_account_number2').val('');
        $('#advanced_salary_ifsc_code2').val('');
        $('#ssbno').val('');
        $('#payment_mode').val('');
        $('#transfer_mode').val('');
        $('#bank_balance').val('');
        $('#account_id').val('');
        $('#bank_id').val('');
        $('#utr_tran').val('');
        $('#neft_charge').val('');
        $('#cheque_id').val('');
        $('#tmode').hide('');
        $('.paymentmode').hide();
        $('#bankss').hide();
        $('#accourid').hide();
        $('#bankbalance').hide();
        $('#cheque').hide();
        $('.utrnumber').hide();
        $('.rtgsnumber').hide();

        $(".employeecode").show();
        $(".employeename").show();
        $(".amount").html('Advance Amount<sup class="required">*</sup>');
        $(".ownerlist").hide();
        $('h3').text("Advance TA /Imprest Payment");
    } else if (selected == 1) {
        $(".taadvance").removeClass('d-none');
        $('.taadvance').show();
        $('#ta_employee_code').val('');
        $('#advanced_rent_party_name').val('');
        $('#ename').val('');
        $('#narration').val('');
        $('#aamount').val('');
        $('#advanced_salary_mobile_number2').val('');
        $('#advanced_salary_bank_name2').val('');
        $('#advanced_salary_bank_account_number2').val('');
        $('#advanced_salary_ifsc_code2').val('');
        $('#ssbno').val('');
        $('#payment_mode').val('');
        $('#transfer_mode').val('');
        $('#bank_balance').val('');
        $('#account_id').val('');
        $('#bank_id').val('');
        $('#utr_tran').val('');
        $('#neft_charge').val('');
        $('#cheque_id').val('');
        $('#tmode').hide('');
        $('.paymentmode').hide();
        $('#bankss').hide();
        $('#accourid').hide();
        $('#bankbalance').hide();
        $('#cheque').hide();
        $('.utrnumber').hide();
        $('.rtgsnumber').hide();


        $(".amount").html('Advance Salary<sup class="required">*</sup>');
        $(".employeecode").show();
        $(".employeename").show();
        $(".ownerlist").hide();
        $('h3').html('Advance Salary Payment');
    } else if (selected == 0) {
        $(".taadvance").removeClass('d-none');
        $('.taadvance').show();
        $('#ta_employee_code').val('');
        $('#advanced_rent_party_name').val('');
        $('#ename').val('');
        $('#narration').val('');
        $('#aamount').val('');
        $('#advanced_salary_mobile_number2').val('');
        $('#advanced_salary_bank_name2').val('');
        $('#advanced_salary_bank_account_number2').val('');
        $('#advanced_salary_ifsc_code2').val('');
        $('#ssbno').val('');
        $('#payment_mode').val('');
        $('#transfer_mode').val('');
        $('#bank_balance').val('');
        $('#account_id').val('');
        $('#bank_id').val('');
        $('#utr_tran').val('');
        $('#neft_charge').val('');
        $('#cheque_id').val('');
        $('#tmode').hide('');
        $('.paymentmode').hide();
        $('#bankss').hide();
        $('#accourid').hide();
        $('#bankbalance').hide();
        $('#cheque').hide();
        $('.utrnumber').hide();
        $('.rtgsnumber').hide();


        $(".amount").html('Advance Rent<sup class="required">*</sup>');
        $(".employeecode").hide();
        $(".employeename").hide();
        $(".ownerlist").show();
        $('h3').text("Advance Rent Payment");
    } else {

    }


});

// Branch change get The employee data

$('#branch').on('change', function() {
    $('.taadvance input').val('');
    $('.taadvance select').val('');
    $('.employeecode input').val('');
    $('.employeecode select').val('');
    var branchId = $('#branch').val();
    var company_id = $('#company_id').val();
    $.ajax({
        type: "POST",
        url: "{!! route('admin.advancePayment.getOwnerNames') !!}",
        dataType: 'JSON',
        data: {
            'branch': branchId,
            'company_id': company_id,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true,
        success: function(response) {
            var optionHtml = '';
            for (var i = 0; i < response['ownerDetails'].length; i++) {
                console.log(response[i]);
                optionHtml += '<option value="' + response['ownerDetails'][i].id + '">' + response[
                    'ownerDetails'][i].owner_name + '</option>';
            }
            $('#advanced_rent_party_name').children('option:not(:first-child)[value!=""]').remove();
            $('#advanced_rent_party_name').append(optionHtml);
        },
        error: function(xhr, status, error) {
        },
    });
    $.ajax({
        type: "POST",
        url: "{!! route('admin.advancePayment.getemployeee') !!}",
        dataType: 'JSON',
        data: {
            'branch': branchId,
            'company_id': company_id,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true,
        success: function(response) {
            var optionHtml = '';
            for (var i = 0; i < response['employedata'].length; i++) {
                console.log(response[i]);
                optionHtml += '<option value="' + response['employedata'][i].employee_code + '">' + response[
                    'employedata'][i].employee_name + "  ("+ response['employedata'][i].employee_code + ")" + '</option>';
            }
            $('#ta_employee_code').children('option:not(:first-child)[value!=""]').remove();
            $('#ta_employee_code').append(optionHtml);
        },
        error: function(xhr, status, error) {
        },
    });

});


// Using for date selection date Picker code
$(".date-from,.date-to,#maturity_prematurity_date,#death_help_date,#payment_date").hover(function() {
    var today = $('.create_application_date').val();
    // var todady = $('#created_at').val();
    // var originalDate = todady;
    // var dateParts = originalDate.split(' ')[0].split('-');

    // var year = dateParts[0];
    // var month = dateParts[1];
    // var day = dateParts[2];
    var formattedDate = $('#create_application_date').val();
    $('.date-from,.date-to,#maturity_prematurity_date,#death_help_date,#payment_date,.pDate').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: false,
        endDate: formattedDate,
        autoclose: true,
        orientation: 'bottom',
        startDate: '01/01/2020',
    })
})

// Validation
$('#addrequest').validate({ // initialize the plugin


    rules: {

        'paymentType': {
            required: true
        },

        'advanced_rent_party_name': {
            required: true
        },

        'brnach_id': {
            required: true
        },

        'date': {
            required: true
        },


        'ta_employee_code': {
            required: true
        },

        'ename': {
            required: true
        },

        'particular': {
            required: true
        },
        'file': {
            required: true
        },

        'aamount': {
            required: true,
            digits: true,
            min: 1,
        },


    },
    messages: {
        aamount: {
            required: "Amount is Required",
            digits: "Please enter a valid Amount",
        }
    },

    submitHandler: function(form) {
        $('#addrequest').submit(function(event) {
            // Prevent the form from submitting via the browser
            event.preventDefault();
            // Serialize the form data
            // var formData = $(this).serialize();
            const formData = new FormData(form);
            // console.log(formData);

            var ssbno = $("#ssbno").val();
            var paymenttype = $("#paymentType").val();

            // if (paymenttype == 1) {
            //     if (ssbno == '') {
            //         swal("Warning!", "SSB account number is Required for this Payment", "warning");
            //         return false;
            //     }
            // }

            $('#tasubmit').prop('disabled', true);
            // Send the Ajax request
            $.ajax({
                type: 'POST',
                url: "{!! route('admin.advancePayment.advancerequest') !!}",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    swal({
                        title: 'Successfully!',
                        text: response,
                        type: 'success'
                    }, function(isConfirm) {
                        window.location.href =
                            "{{route('admin.advancePayment.requestList')}}";
                    });
                    // swal("Success", response, "success");

                    $('#tasubmit').prop('disabled', true);
                    // window.location.href = "{{ route('admin.advancePayment.requestList')}}";


                },
                error: function(xhr, status, error) {
                    // Handle Ajax errors
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(formData);

                    $('#tasubmit').prop('disabled', false);
                    if (err.errors.aamount) {
                        swal("Warning!", err.errors.aamount, "warning");
                    }

                    if (err.errors.advanced_salary_bank_name2) {
                        swal("Warning!", err.errors.advanced_salary_bank_name2,
                            "warning");
                    }

                    if (err.errors.paymentType) {
                        swal("Warning!", err.errors.paymentType, "warning");
                    }

                    if (err.errors.date) {
                        swal("Warning!", err.errors.date, "warning");
                    }

                    if (err.errors.branch) {
                        swal("Warning!", err.errors.branch, "warning");
                    }

                    if (err.errors.narration) {
                        swal("Warning!", err.errors.narration, "warning");
                    }

                    if (err.errors.advanced_salary_mobile_number2) {
                        swal("Warning!", err.errors.advanced_salary_mobile_number2,
                            "warning");
                    }

                    if (err.errors.advanced_salary_bank_account_number2) {
                        swal("Warning!", err.errors
                            .advanced_salary_bank_account_number2, "warning");
                    }

                    if (err.errors.advanced_salary_ifsc_code2) {
                        swal("Warning!", err.errors.advanced_salary_ifsc_code2,
                            "warning");
                    }

                    // err.error

                    return false;


                }

            });
        });

    }



});
$(document).ajaxStart(function() {
    $(".loader").show();
});

$(document).ajaxComplete(function() {
    $(".loader").hide();
});


$(document).ready(function() {

    $('#company_id').on('change', function() {
        $('#branch').trigger('change');
        $('#date').val('');
        // var enddatee = $('#create_application_date').val();
        // $('#date').datepicker('setEndDate', enddatee);
        var company_id = $('#company_id').val();
        $.ajax({
            type: "POST",
            url: "{{route('admin.vendor.companydate')}}",
            dataType: 'JSON',
            data: {
                'company_id': company_id,
            },
            success: function(response) {
                $('#companyDate').val(response);
            }
        });
    });
    $(document).on('change', '#file', function() {
			var ext = $(this).val().split('.').pop().toLowerCase();
			if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg','doc','docx','pdf','svg','webp','csv']) == -1) {
				swal({
					title: 'Error!',
					text: 'This file is not accepted!',
					type: 'error'
				});
				$(this).val('');
			}
		});

    // Fetch the employee data with employe code using ajax 
    $(document).on('change', '#ta_employee_code', function() {
        if ($("#branch").val() == "") {
            swal("Warning!", "Please select the Branch First!", "warning");
            $('#ta_employee_code').val('');
            return false;
        }
        var paymenttype = $('#paymentType').val();
        $('#date').val('');
        var employee_code = $(this).val();

        const branchId = $('#branch').val();
        const companyId = $('#company_id').val();
        if (companyId == '') {
            swal("Warning!", "Please select company first!", "warning");
            $('#ta_employee_code').val('');
            return false;
        }
        var classVal = $(this).attr('data-val');

        $.ajax({

            type: "POST",

            url: "{!! route('admin.advancePayment.getemployee') !!}",

            dataType: 'JSON',

            data: {
                'employee_code': employee_code,
            },

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            },
            async: true,

            success: function(response) {


                if (response[0] == null) {
                    $('#ta_employee_code').val('');
                    $('#ename').val('');
                    $('#narration').val('');
                    $('#aamount').val('');
                    $('#advanced_salary_mobile_number2').val('');
                    $('#advanced_salary_bank_name2').val('');
                    $('#advanced_salary_bank_account_number2').val('');
                    $('#advanced_salary_ifsc_code2').val('');
                    $('#ssbno').val('');
                    // swal("Warning!", "Employee Code not found!", "warning");
                    return false;
                }

                if (paymenttype == 2 || paymenttype == 1) {

                    console.log(response[0].status);

                    if (response[0].status == 0) {
                        swal("Warning!", "Employee Code is Inactive!", "warning");
                        return false;
                    }

                    if (response[0].branch_id != branchId) {
                        swal("Warning!",
                            "Employee's Branch and selected Branch does not match!",
                            "warning");
                        $('#ta_employee_code').val('');
                        return false;
                    }
                    if (response[0].company_id != companyId) {
                        swal("Warning!",
                            "Employee's Company and selected Company does not match!",
                            "warning");
                        $('#ta_employee_code').val('');
                        return false;
                    }
                    if (response[0].branch_id == branchId) {
                        $('#ename').val(response[0].employee_name);
                        $('#employee_id').val(response[0].id);
                        $('#advanced_salary_mobile_number2').val(response[0].mobile_no);
                        $('#advanced_salary_bank_name2').val(response[0].bank_name);
                        $('#advanced_salary_bank_account_number2').val(response[0]
                            .bank_account_no);
                        $('#advanced_salary_ifsc_code2').val(response[0].bank_ifsc_code);
                        if (response[0]['get_ssb']) {
                            $('#ssbno').val(response[0]['get_ssb'].account_no);
                        }
                        $('#employee_id').val(response[0].id);
                        $(".loader").hide();
                        $('.paymentmode').show();
                        var date1 = new Date(response[0].employee_date);
                        var date2 = new Date($('#companyDate').val());
                        var formattedDate = $('#create_application_date').val();
                        if (date1 < date2) {
                            var startdatee = date2;
                        } else {
                            var startdatee = date1;
                        }
                        $('#date').datepicker({
                            format: "dd/mm/yyyy",
                            todayHighlight: false,
                            endDate: formattedDate,
                            autoclose: true,
                            orientation: 'bottom',

                        })
                        $('#date').datepicker('setStartDate', startdatee);
                        // swal("Success!", "Employee data retrieved successfully.",
                        //     "success");

                    }

                }



            },

            error: function(xhr, status, error) {
                $('#ta_employee_code').val('');
                $('#ename').val('');
                $('#narration').val('');
                $('#aamount').val('');
                $('#advanced_salary_mobile_number2').val('');
                $('#advanced_salary_bank_name2').val('');
                $('#advanced_salary_bank_account_number2').val('');
                $('#advanced_salary_ifsc_code2').val('');
                $('#ssbno').val('');
                $('#ssb').val('');
                $('#advanced_salary_mobile_number').val('');
                $('#advanced_salary_employee_name').val('');
                $('#advanced_salary_ssb_account').val('');
                $('#advanced_salary_bank_account_number').val('');
                $('#advanced_salary_bank_name').val('');
                $('#advanced_salary_ifsc_code').val('');
                // swal("Warning!", "Employee Code not found!", "warning");
                return false;
            },


        });

    });

    // // Fetch the owner details
    $(document).on('change', '#advanced_rent_party_name', function() {
        var val = $(this).val();
        $('#date').val('');
        var classVal = $(this).attr('data-val');
        $.ajax({
            type: "POST",
            url: "{!! route('admin.demand.getowner') !!}",
            dataType: 'JSON',
            data: {
                'val': val
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.ownerDetails) {
                    console.log(response);
                    $('#advanced_salary_mobile_number2').val(response.ownerDetails
                        .owner_mobile_number);
                    $('#advanced_salary_bank_name2').val(response.ownerDetails
                        .owner_bank_name);
                    $('#advanced_salary_bank_account_number2').val(response.ownerDetails
                        .owner_bank_account_number);
                    $('#advanced_salary_ifsc_code2').val(response.ownerDetails
                        .owner_bank_ifsc_code);
                    $('#ssbno').val(response.ownerDetails.owner_ssb_number);
                    $('.paymentmode').show();
                    var date1 = new Date(response.ownerDetails
                    .agreement_from);
                        var date2 = new Date($('#companyDate').val());
                        var formattedDate = $('#create_application_date').val();
                        if (date1 < date2) {
                            var startdatee = date2;
                        } else {
                            var startdatee = date1;
                        }
                        $('#date').datepicker({
                            format: "dd/mm/yyyy",
                            todayHighlight: false,
                            endDate: formattedDate,
                            autoclose: true,
                            orientation: 'bottom',

                        })
                        $('#date').datepicker('setStartDate', startdatee);
                } else {
                    $('#ta_employee_code').val('');
                    $('#ename').val('');
                    $('#narration').val('');
                    $('#aamount').val('');
                    $('#advanced_salary_mobile_number2').val('');
                    $('#advanced_salary_bank_name2').val('');
                    $('#advanced_salary_bank_account_number2').val('');
                    $('#advanced_salary_ifsc_code2').val('');
                    $('#ssbno').val('');
                    // swal("Warning!", "Owner details not found!", "warning");
                }
            }
        });
    });
});
</script>