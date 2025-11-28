<script type = "text/javascript" >
    $(document).on('keyup', '#account_number', function() {
        $('#loan_plan_transferdetail').hide();
        $('#loan_plan_transferdetail').html('');
        var code = $(this).val();
        //var type = $("#loan_id").val();


        if (code != '') {
            $.ajax({
                type: "POST",
                url: "{!! route('admin.loan.plantransfer.loan_plantransferdataget') !!}",
                dataType: 'JSON',
                data: {
                    'code': code,
                    'type': 'type'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    //  alert(response.msg_type);
                    if (response.msg_type == "success") {
                        $('#loan_plan_transferdetail').show();
                        $('#loan_plan_transferdetail').html(response.view);

                    } else {

                        if (response.msg_type == "error_cleargoup") {
                            $('#loan_plan_transferdetail').show();
                            $('#loan_plan_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>You can not transfer plan because in this group loan accounts are already cleared!</strong> </div>');
                        } else if (response.msg_type == "error_clear") {
                          //alert('dfdf');
                            $('#loan_plan_transferdetail').show();
                            $('#loan_plan_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>You can not transfer plan because account has been already cleared!</strong> </div>');
                        } else {
                            $('#loan_plan_transferdetail').show();
                            $('#loan_plan_transferdetail').html('<div class="alert alert-danger alert-block">  <strong>Account not found!</strong> </div>');
                        }


                    }

                }
            });
        }

    });

    $(document).on('change', '#plan_id', function(){
        var loanplan = $(this).val();
        var sanctioned_amt = $("#sanctioned_amt").val();
        //alert(loanplan);

        if(loanplan !=''){
            $.ajax({
               type:"POST",
               url:"{!! route('admin.loan.plantransfer.new_loanplan_detailget')!!}",
               datatype: "JSON",
               data : {
                    'loanplan':loanplan,
                    'sanctioned_amt':sanctioned_amt,
               },
               headers :{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function(response){
                    if (response.msg_type == "success") {
                        $('#new_loanplan_detail').show();
                        $('#new_loanplan_detail').html(response.view);

                    }else{
                        $('#new_loanplan_detail').show();
                        $('#new_loanplan_detail').html('<div class="alert alert-danger alert-block"> <strong>Plan Cannot Transfer!</strong> </div>');
                    }
               }     
            });
        }
    });
    
    $('#filter').validate({
        rules: {
            account_number: {
                number: true,
                required: true,
                minlength: 12,
                maxlength: 12,
            },
            plan_id: {
                required: true,
            },
            reason_id: {
                required: true,
                minlength: 50
            },
            loan_id: {

                required: true,
            },


        },
        messages: {
            account_number: {
                required: "Please enter Account Number.",
                number: "Please enter  valid number.",
            },
            plan_id: {
                required: "Please enter plan name.",
            },
            reason_id: {
                required: "Please enter reason for change plan type.",
            },
            loan_id: {
                required: "Please enter loan type.",

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
    $(document).ajaxStart(function() {
        $(".loader").show();
    });

    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });

    function resetForm() {
        var validator = $("#filter").validate();
        validator.resetForm();
        $('#loan_plan_transferdetail').hide();
        $('#account_number').val('');
    } 
</script>