<script type = "text/javascript" >
    $(document).on('keyup', '#associate_code', function() {
        $('#associate_exception_transferdetail').hide();
        $('#associate_exception_transferdetail').html('');
        var code = $(this).val();
        if (code != '') {
            $.ajax({
                type: "POST",
                url: "{!! route('admin.associter_exceptiontransferdataGets') !!}",
                dataType: 'JSON',
                data: {
                    'code': code,
                    'type': 'senior'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == "success") {
                        $('#associate_exception_transferdetail').show();
                        $('#associate_exception_transferdetail').html(response.view);


                    } else {
                        $('#associate_exception_transferdetail').show();
                        if (response.msg_type == "error1") {
                            $('#associate_exception_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Associate Inactive!</strong> </div>');
                        } else if (response.msg_type == "error2") {
                            $('#associate_exception_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Associate Blocked!</strong> </div>');
                        } else {

                            $('#associate_exception_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Associate not found!</strong> </div>');
                        }
                    }
                }
            });
        }

    });


$('#filter').validate({
    rules: {
        associate_code: {
            number: true,
            required: true,
            minlength: 12,
            maxlength: 12,
        },
        month_id: {
            //number : true,
            required: true,
        },
        reason: {
            //number : true,
            required: true,
        },
        type_id: {
            //number : true,
            required: true,
        },


    },
    messages: {
        associate_code: {
            required: "Please enter associate code.",
            number: "Please enter  valid code.",
        },
        month_id: {
            required: "Please enter month name.",

        },
        reason: {
            required: "Please enter reason .",

        },
        type_id: {
            required: "Please select type name.",

        },

    },
    errorElement: 'span',
    errorPlacement: function(error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
    },
    highlight: function(element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
            $(element.form).find("input[type=radio]").each(function(which) {
                $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                $(this).addClass('is-invalid');
            });
        }
    },
    unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if ($(element).attr('type') == 'radio') {
            $(element.form).find("input[type=radio]").each(function(which) {
                $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                $(this).removeClass('is-invalid');
            });
        }
    }
});

// $(document).on('change','#type_id',function() {
//  var value = $('#type_id').val();
//  const month = $('#month_id').val();
//  const year = $('#year_id').val();
//  const associate = $('#associate_code').val();
 

//  $.ajax({
//         type: "POST",
//         url: "{!! route('admin.dailyaccount.check_commission') !!}",
//         dataType: 'JSON',
//         data: {
//             'value': value,
//             'month': month,
//             'year': year,
//             'associate':associate,
//         },
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         success: function(response) {
//             alert(response);
//            if(response==1)
//                 $('#filter')[0].reset();  
//                 $('#associate_exception_transferdetail').hide();
//                 swal('Warning','Already Exist','warning');
//             }
//             });


// });
$(document).ajaxStart(function() {
    $(".loader").show();
});

$(document).ajaxComplete(function() {
    $(".loader").hide();
});

function resetForm() {
    var validator = $("#filter").validate();
    validator.resetForm();
    $('#associate_exception_transferdetail').hide();
    $('#associate_code').val('');



}

function resetFormed() {

    var validator = $("#filter").validate();
    validator.resetForm();
    $('#year_id').val('');
    $('#month_id').val('');

} 


</script>