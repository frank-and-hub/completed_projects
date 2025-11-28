<script type="text/javascript">
   
    window.onload = function() {
      var head = $('#head_id').val();
      if (head != null) {
        $('#formgethead').trigger('click');
      }
    };
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode

        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
            return false;
        return true;
    }

    $(document).ready(function() {

        $('#getHeadList').validate({
            rules: {
                financial_year: {
                    required: true,
                },
                branch: {
                    required: true,
                },
            },
            messages: {
                financial_year: {
                    "required": "Please select financial year."
                },
                branch: {
                    "required": "Please select branch."
                },
            },
        })
        $(document).on('click', '.export', function() {
            var extension = $(this).attr('data-extension');
            if ($('#getHeadList').valid()) {
                $('#export').val(extension);
                $('form#getHeadList').attr('action', "{!! route('admin.trail_balance.export') !!}");
                $('form#getHeadList').submit();
            } else {
                $('#export').val('');
            }
        });


        $(document).on('click', '#myformsubmit', function(e) {

            e.preventDefault();
            var myarray = [];
            $('.aa').each(function() {
                if (!$(this).val()) {
                    $(this).next('.pl-2').html("Please Enter Value");
                    myarray = "error";

                } else if ($.isNumeric($(this).val()) == false) {
                    $(this).next('.pl-2').html("");
                    $(this).next('.pl-2').html("Please Enter Only Number");
                    myarray = "error";
                }
            });

            if (myarray == '' && myarray != "error") {

                swal({
                        title: "Are you sure?",
                        text: "Do you want to Run Cron ?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-primary",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        cancelButtonClass: "btn-danger",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            $.ajax({
                                type: 'POST',
                                url: "{!! route('admin.run_cron') !!}",
                                dataType: 'JSON',
                                data: $("#getHeadList").serialize(),
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(response) {
                                    if (response.msg_type == "success") {


                                        swal('success', 'Cron Update Successfully!',
                                            'success');

                                        location.reload();

                                    } else {

                                        swal('warning', 'Something went wrong!',
                                            'warning');
                                        location.reload();
                                    }

                                }
                            });
                        }
                    });
                //var myform = $("#myform").serialize();
                // $.ajax({
                //         type: "POST",  
                //         url: "{!! route('admin.closing_head.save') !!}",
                //         data: $("#myform").serialize(),
                //         headers: {
                //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                //         },
                //         success: function(response) { 
                //             $("#financial_year").val('');                        
                //             if(response.msg_type=="success")
                //             {


                //                 $('#head_closing_value_show').html('<div class="alert alert-success alert-block"><strong>Amount successfully added </strong></div>');  
                //                 $('html, body').animate({
                //                 scrollTop: $("#head_closing_value_show").offset().top 
                //                 }, 2000);

                //             }
                //             else
                //             {                     

                //                 $('#head_closing_value_show').html('<div class="alert alert-danger alert-block">  <strong>'+response.vew+' </strong></div>');

                //             }

                //         }
                // });
            }


        })


        $('#formgethead').on('click', function() {
            $('#head_closing_value_show').html(' ');
            if ($('#getHeadList').valid()) {
                var financial_year = $('#financial_year').val();
                var branch_id = $('#branch').val();
                var name = $('#name').val();
                var companyId = $('#company_id').val();
                var head_id = $('#head_id').val();
                var child_id = $('#child_id').val();
                var lebel = $('#lebel').val();
                $.post("{!! route('admin.trail_balance.headlist') !!}", {
                    'financial_year': financial_year,
                    'branch': branch_id,
                    'company_id': companyId,
                    'head_id': head_id,
                    'child_id': child_id,
                    'lebel': lebel,
                    'name': name,
                }, function(response) {
                    if (response.msg_type == "success") {
                        $('#head_closing_value_show').html(response.view);
                    } else {
                        $('#head_closing_value_show').html(
                            '<div class="alert alert-danger alert-block"><strong>Record not found!</strong> </div>'
                            );
                    }
                }, 'JSON');
            }
        })
    })

    function resetForm() {
        $('#getHeadList')[0].reset();
        $("#head_closing_value_show").html('');
    }



    var arr = [];
    $(document).on('change', '.notSave', function(e) {
        var id = $(this).attr("data-row-id");
        var val = $(this).val();
        var checkValueInArr = arr.includes(id);

    });
</script>
