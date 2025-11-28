<script type="text/javascript">
    var branchTable;
    $(document).ready(function() {
        // Branch Form validations
        $('#branch-create').validate({ // initialize the plugin
            rules: {
                name: 'required',
                state: 'required',
                city: 'required',
                sector: 'required',
                regan: 'required',
                zone: 'required',
                pin_code: {
                    required: true,
                    minlength: 6,
                    maxlength: 6,
                    digits: true
                },
                address: 'required',
                phone: {
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                    digits: true
                },
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirmation: {
                    required: true,
                    minlength: 6,
                    equalTo: "#password"
                },
            },
            messages: {
                name: {
                    required: 'Please enter valid Branch Name.',
                },
                state: {
                    required: 'Please select a State.',
                },
                city: {
                    required: 'Please select a City.',
                },
                zone: {
                    required: 'Please enter Zone/Sector.',
                },
                pin_code: {
                    required: 'Please enter Postal Code.',
                    minlength: 'Please enter at least 6 digit.',
                    maxlength: 'Please enter no more than 6 digit',
                    digits: 'Please enter only digits',
                },
                address: {
                    required: 'Please enter Address.',
                },
                phone: {
                    required: 'Please enter valid Phone Number.',
                    minlength: 'Please enter at least 10 digit.',
                    maxlength: 'Please enter no more than 12 digit',
                    digits: 'Please enter only digits'
                },
                password: {
                    required: 'Please enter Password.',
                    minlength: 'Please enter at least 6 characters.',
                },
                password_confirmation: {
                    required: 'Please enter Password.',
                    minlength: 'Please enter at least 6 characters.',
                    equalTo: 'Password did not matched'
                },
            }
        });
        $('#branch-update').validate({
            rule: {
                phone: {
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                    digits: true
                },
                password: {
                    minlength: 6
                },
                password_confirmation: {
                    minlength: 6,
                    equalTo: "#password"
                },
            },
            message: {
                phone: {
                    required: 'Please enter valid Phone Number.',
                    minlength: 'Please enter at least 10 digit.',
                    maxlength: 'Please enter no more than 12 digit.',
                    digits: 'Please enter only digits'
                },
                password: {
                    minlength: 'Please enter at least 6 characters.',
                },
                password_confirmation: {
                    minlength: 'Please enter at least 6 characters.',
                    equalTo: 'Password did not matched'
                },
            }

        });
        /** Ip Address Validation ******/
        $.validator.addMethod('IP4Checker', function(value) {
            var ip = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
            ip = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
            return value.match(ip);
        }, 'Please enter valid Ip Address.');

        $('#update-ip').validate({ // initialize the plugin
            rules: {
                ip_address: {
                    required: true,
                    IP4Checker: true
                }
            },
            messages: {
                ip_address: {
                    required: 'Please enter Ip Address.',
                },
            }
        });

        $('#add-ip').validate({ // initialize the plugin
            rules: {
                ip_address: {
                    required: true,
                    IP4Checker: true
                }
            },
            messages: {
                ip_address: {
                    required: 'Please enter Ip Address.',
                },
            }
        });
        $("form[name='change-password']").submit(function() {
            return this.some_flag_variable;
        });

        branchTable = $('#branch').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100,200,300],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.listing') !!} ",
                "type": "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            searching: true,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    searchable: false
                },
                {
                    data: 'limit',
                    name: 'limit',
                    searchable: false
                },
                {
                    data: 'balance_amount',
                    name: 'balance_amount',
                    searchable: false
                },
                {
                    data: 'created_at1',
                    name: 'created_at1',
                    searchable: false
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'sector',
                    name: 'sector',
                    searchable: false
                },
                {
                    data: 'regan',
                    name: 'regan',
                    searchable: false
                },
                {
                    data: 'zone',
                    name: 'zone',
                    searchable: false
                },
                {
                    data: 'city_id',
                    name: 'city_id',
                    searchable: false
                },

                {
                    data: 'state_id',
                    name: 'state_id',
                    searchable: false
                },
                {
                    data: 'phone',
                    name: 'phone',
                    searchable: false
                },
                {
                    data: 'email',
                    name: 'email',
                    searchable: false
                },
                {
                    data: 'otp',
                    name: 'otp',
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    searchable: false,
                    "render": function(data, type, row) {
                        if (row.status == 0) {
                            return "<span class='badge badge-danger'>Disabled</span>";
                        } else {
                            return "<span class='badge badge-success'>Active</span>";
                        }
                    }
                },
                {
                    data: 'address',
                    name: 'address'
                },



                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    orderable: false,
                    className: "text-center",
                }
            ],"ordering": false,
        });
        /* get city from state **/
        $(document).on('change', '#state', function() {
            var stateId = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('cities') !!}",
                data: {
                    'stateId': stateId
                },
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

        $(document).on('change', '#branch-name', function() {
            var branchName = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('check.branch') !!}",
                data: {
                    'branchName': branchName
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status == true) {
                        $(this).addClass('error');
                        $('#branch-name').after('<label id="branch-name-error" class="error" for="branch-name">Branch name all ready exist.</label>');
                        $('#branch-name').val('');
                    }
                }
            });
        })


        $("#email").keyup(function() {
            var email = $("#email").val();
            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if (!filter.test(email)) {
                //alert('Please provide a valid email address');
                $("#error_email").show();
                $('label[id="error_email"]').hide();
                $("#error_email").text("Email id is not valid");
                email.focus;
                //return false;
            } else {
                $("#error_email").text("");
            }
        });

        $(document).on('change', '#email', function() {

            var email = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('check.email') !!}",
                data: {
                    'email': email
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status == true) {
                        $(this).addClass('error');
                        $("#error_email").hide();
                        $('label[id="error_email"]').hide();
                        $('#email').after('<label id="email-error" class="error" for="email">Branch Email all ready exist.</label>');
                        $('#email').val('');

                    }
                }
            });
        })

        $(document).on('change', '#phonenumber', function() {
            var phone = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('check.phone') !!}",
                data: {
                    'phone': phone
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status == true) {
                        $(this).addClass('error');
                        $('#phonenumber').after('<label id="phonenumber-error" class="error" for="phone">Branch Phone Number all ready exist.</label>');

                        $('#phonenumber').val('');

                    }
                }
            });
        })

        $(document).on('click', '.assign_company', function() {
            var id = $(this).data('id');
            $('#branch_id').val(id);
            $(".loader").show();
            $('#assignModal').modal('hide');
            $('.modal-backdrop').remove();
            $.ajax({
                type: "POST",
                url: "admin/branch-assigned-model",
                data: {
                    'branch_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status == true) {
                        $('.assigend_model').html(response.html);
                        var numItems = $('.allcompanycheck').length;
                        $('.totallength').val(numItems);
                        $("#assignModal").modal('show');
                        // $(".assigneclick").click();
                        $(".loader").hide();
                        //trigger second button

                    }
                }
            });
        })

        $(document).on('click', '#assign_branch', function() {
           
            const branchId = $(this).attr('data-id');
            
            $('#u_branch_id').val(branchId);
        })
    });


    $(document).on('change', '#checkall', function() {
        if ($(this).is(":checked")) {
            $('.allcompanycheck').prop("checked", true);
        } else {
            $('.allcompanycheck').prop("checked", false);
        }
    });

    $(document).on('click', '.allcompanycheck', function() {
        // var numItems = $('.totallength').val();
         const i = $(this).data('id');
        // for (var i = 1; i < numItems; i++) {
            (function(i) {
                $(document).on('change', '#company_' + i, function() {
                    if ($(this).is(":checked")) {
                        $('.oldcheck_' + i).prop({"checked": true,"disabled": true});
                        $('.newcheck_' + i).prop("checked", true);
                    } else {
                        $('.oldcheck_' + i).prop("checked", false).removeAttr("disabled");
                        $('.newcheck_' + i).prop("checked", false);
                    }
                });
            })(i);
        // }
    });

    $(document).on('click', '.allcompanycheckcreate', function() {
        // var numItems = $('.allcompanycheckcreate').length;
         const i = $(this).data('id');
        // for (var i = 1; i < numItems; i++) {
            (function(i) {
                $(document).on('change', '#company_' + i, function() {
                    if ($(this).is(":checked")) {
                        $('.oldcheckcreate_' + i).prop({'checked': true,'disabled': true});
                        $('.newcheckcreate_' + i).prop("checked", true);
                    } else {
                        $('.oldcheckcreate_' + i).prop("checked", false).removeAttr("disabled");
                        $('.newcheckcreate_' + i).prop("checked", false);
                    }
                });
            })(i);
        // }
    });

    $(document).on('click', '#assignsubmit', function() {
        var branch_id = $('#branch_id').val();
        var primary = $('input[id="primarybox"]:checked').data('id');

        var allcompines = [];
        $(".allcompanycheck:checked").each(function() {
            allcompines.push($(this).attr('data-id'));
        });
        var alloldvaluecheckd = [];
        $(".oldvaluechecked:checked").each(function() {
            alloldvaluecheckd.push($(this).attr('data-id'));
        });
        var allnewvaluecheckd = [];
        $(".newvaluechecked:checked").each(function() {
            allnewvaluecheckd.push($(this).attr('data-id'));
        });
        $(".loader").show();
        if (allcompines.length <= 0) {
            swal({
                title: `Please select at least one record.`,
                text: "",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            $('.swal-button--cancel').addClass('cancelexpired');
            return false;
        }
        var company = allcompines.join(",");
        var oldvalues = alloldvaluecheckd.join(",");
        var newvalues = allnewvaluecheckd.join(",");
        $('.oldbusspopupcheck').removeAttr('disabled');
        $.ajax({
            url: '{{route("admin.branch_assigned")}}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'company': company,
                'oldvalues': oldvalues,
                'newvalues': newvalues,
                'primary': primary,
                'branch_id': branch_id
            },
            success: function(data) {
                if (data['success']) {
                    $(".loader").hide();
                    swal("Success", data['message'], "success")
                    $('.swal-button--cancel').addClass('cancelexpired');
                    $("#master").prop('checked', false);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                }
            },
        });
    });

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode != 46 && charCode > 31 &&
            (charCode < 48 || charCode > 57))
            return false;

        return true;
    }
    $(document).on('click', '#brnach_submit', function() {
        var bname = $('#branch-name').val();
        var allcompines = [];
        $(".allcompanycheckcreate:checked").each(function() {
            allcompines.push($(this).attr('data-id'));
        });
        if ($('#branch-create').valid()) {
            // alert(allcompines.length);
            if (allcompines.length <= 0) {

                swal({
                    title: "Oops!",
                    text: "Please Select at least one company",
                    icon: "error",
                    button: "oh no!",
                });
                $('.swal-button--cancel').addClass('cancelexpired');
                return false;
            } else {
                $('.oldbusscheck').removeAttr('disabled');
            }
        }


    });



    $("#submit_form").on("click", function() {
        var branchName = $('#branch_name').val()
        var branchId = $('#u_branch_id').val();
        $('#msg-branch_name').html('');

        if (branchName == '') {
            $('#msg-branch_name').html('Please Enter Branch Name');
        } else {
            $.ajax({
                url: "{!! route('admin.branchUpdate') !!}",
                type: 'POST',
                data: {
                    'branch_name': branchName,
                    'branch_id': branchId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data == 1) {
                        $('#branchModal').hide();
                        $('.loader').hide();
                        $('.modal-backdrop').hide();
                        branchTable.draw();
                        location.reload();
                        swal('Success!', 'Branch name successfully changed', 'success');
                        $('#branch_name').val('');
                        $('body').removeClass('modal-open');
                    } else if (data == 2) {
                        $('#msg-branch_name').html('Branch name already exists.');
                    } else {
                        $('#branchModal').hide();
                        $('.loader').hide();
                        $('.modal-backdrop').hide();
                        branchTable.draw();
                        swal('Warning!', 'Sorry there was a problem', 'warning');
                    }
                },
            });

        }
    });
    $('#otp_status').on('change', function() {
        var otpValue = $('#otp_status').val()
        if (otpValue == "") {
            return false;
        }
        swal({
            title: 'OTP STATUS',
            type: 'warning',
            text: 'Are you sure you want to change the OTP status?',
            showDenyButton: true,
            showCancelButton: true,
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: "{{ route('admin.branchStatus') }}",
                    type: 'post',
                    data: {
                        'otp_status': otpValue
                    },
                    success: function(e) {
                        if (e.data == true) {
                            $("#branch").DataTable().ajax.reload();
                            swal("Success", "OTP Status changed successfully", "success");
                        } else {
                            swal("Error", "OTP Status not changed", "error");
                        }
                    }
                });
            }
            $('#otp_status').val('')
        });
    });
   

    $('#cash_limit').on('change', function() {
        var cashLimit = $('#cash_limit').val()
        if (cashLimit == "") {
            return false;
        }
        swal({
            title: 'REMOVE CASH',
            type: 'warning',
            text: 'Are you sure want to change the cash balance?',
            showDenyButton: true,
            showCancelButton: true,
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: "{{ route('admin.branchRemoveCash') }}",
                    type: 'post',
                    data: {'cash_limit': cashLimit},
                    success: function(e) {
                        if (e.data == true) {
                            $("#branch").DataTable().ajax.reload();
                            swal("Success", "Cash limit removed successfully", "success");
                        } else {
                            swal("Error", "Cash Balance not changed", "error");
                        }
                    }
                });
            }
            $('#cash_limit').val('')
        });
    });
    $('#branchId').on('change',function(e){
        $('#branchCode').val('');
        $('#branchCode').prop('disabled', true);
    });
    $('#branchCode').on('change',function(e){
        $('#branchId').val('');
        $('#branchId').prop('disabled', true);
    });
     /**
     * branch update log filter for branch dropdown validation by JQuery
     * created by Sourab on 28-09-2023
     */
    $('#branch_log_filter').validate({
        rules:{
            branchId : {required:false},
            branchCode : {
                number:true,
                minlength: 4,
                maxlength: 4,
                required:false,
            },
        },
        messages:{
            branchId : {
                required : 'Please select Branch.'
            },
            branchCode : {
                number : 'Please enter Number Only.'
            },
        }

    });
    /**
     * branch update log default submiting log
     * by sourab on 28-09-2023
     */
    $('#branch_log_filter').submit( (e) => {
        e.preventDefault();
    })
    /**
     * on submit button ajax calling and gettign data on id="update_log_data"
     * created by sourab on 28-09-2023
     */
    function searchReportLogForm(){
        let branchId = $('#branchId').val();
        let branchCode = $('#branchCode').val();
        console.log(branchId);
        console.log(branchCode);
        $('#update_log_data').html('');        
        if($('#branch_log_filter').valid()){
            branchUpdateLog(branchId,branchCode);
        }
    }
    /**
     * reset button function for reset the from on update log
     */
    function resetReportLogForm(){
        $('#branchId').prop('disabled', false);
        $('#branchCode').prop('disabled', false);
        $('#branchId').val('');
        $('#branchCode').val('');
        $('#is_search').val('no');
        $('#update_log_data').html('');
    }
    /**
     * branchUpdateLog function have 2 perameters id and code both can be null
     * after calling this function it will send data to filter function on
     * branchcontroller for varification and responce will append on 
     * update_log_data ID for log details created by Sourab on 28-09-2023
     */
    function branchUpdateLog(id=null,code=null){
        $.post("{{route('admin.branch.logs.filter')}}",{'branchId':id,'branchCode':code},function (e) {
            if(e.msg_type == 'success'){
                $('#update_log_data').append(e.view);
            }else{
                $('#reset_log').click();
                swal('Warning!',""+e.msg+"",'warning');
            }
            return false;
        },"JSON");
    }
   
</script>