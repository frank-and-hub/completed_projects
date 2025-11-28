<script type="text/javascript">
    $(document).ready(function() {
        $("#account_head").select2({
            dropdownAutoWidth: true
        });

        $('#head_type ').on('change', function() {
            $('#details').hide().empty();
                        $('#detailss').hide().empty();
                        $('#account_head').find('option').remove();
                        $('#change_account_head').find('option').remove();
                        $('#headDetails').hide().empty();
                        

            var head_type = $(this).val();
            if (head_type != "") {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.get_sub_head_using_head') !!}",
                    dataType: 'JSON',
                    data: {
                        'head_type': head_type
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#details').hide().empty();
                        $('#detailss').hide().empty();
                        $('#account_head').find('option').remove();
                        $('#change_account_head').find('option').remove();
                        $('#account_head').append(
                            '<option value="">Select Account Head</option>');
                        $.each(response.account_heads, function(index, value) {
                            $("#account_head").select2({
                                dropdownCssClass: 'frm',
                                dropdownCssClass: 'search-table-outter'
                            });
                            $("#account_head").append("<option value='" + value
                                .head_id + "'>" + value.sub_head + "</option>");

                        });
                    }

                });
            } else {
                $('#account_head').find('option').remove();
                $('#account_head').append('<option value="">Select Account Head</option>');
                $('#change_account_head').find('option').remove();
                $('#change_account_head').append('<option value="">Select Account Head</option>');
            }
        });

        $('#parent_account_type ').on('change', function() {
            // $('#details').hide().empty();
                        $('#detailss').hide().empty();
            //             $('#account_head').find('option').remove();
            //             $('#change_account_head').find('option').remove();
            //             $('#headDetails').hide().empty();

            $('#change_account_head').find('option').remove();
                        

            var parent_account_type = $(this).val();
            if (parent_account_type != "") {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.get_sub_head_using_head') !!}",
                    dataType: 'JSON',
                    data: {
                        'head_type': parent_account_type
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                       
                        $('#change_account_head').append(
                            '<option value="">Select Account Head</option>');
                        $.each(response.account_heads, function(index, value) {
                            $("#change_account_head").select2({
                                dropdownCssClass: 'frm',
                                dropdownCssClass: 'search-table-outter'
                            });
                            $("#change_account_head").append("<option value='" + value
                                .head_id + "'>" + value.sub_head + "</option>");

                        });
                    }

                });
            } else {
                $('#change_account_head').find('option').remove();
                $('#change_account_head').append('<option value="">Select Account Head</option>');
                $('#change_account_head').find('option').remove();
                $('#change_account_head').append('<option value="">Select Account Head</option>');
            }
        });

        $('#account_head').on('change', function() {

            var id = $(this).val();
            getComapnyDetails(id);
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_change_sub_head') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#change_account_head').find('option').remove();
                    $('#headDetails').show();
                    $('#detailss').hide();

                    $('#change_account_head').append(
                        '<option value="">Select Account Head</option>');

                    $.each(response.account_heads, function(index, value) {
                        $("#change_account_head").select2({
                            dropdownCssClass: 'frm',
                            dropdownCssClass: 'search-table-outter'
                        });
                        $("#change_account_head").append("<option value='" + value
                            .head_id + "'>" + value.sub_head + "</option>");

                    });
                }

            })
        });

        $('#change_account_head').on('change', function() {

            var id = $(this).val();
            getComapnyDetailsParentHead(id);

        });


        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        $('.reset').on('click', function() {
            $("#account_head, #change_account_head, #details").val("");
            $("#details").hide();
            $("#detailss").hide();
            $('#headDetails').hide();

            $("#change_account_head,#account_head").select2({
                dropdownCssClass: 'frm',
                dropdownCssClass: 'search-table-outter'
            });

        })

        function getComapnyDetails(headId) {
            var head_Id = headId;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_comapny_details') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': head_Id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#details').show().empty();
                    $("#details").append('<b>Companies</b>: ');

                    $.each(response.mainComapny, function(index, value) {

                        $("#details").append(index + 1 + '.');

                        $("#details").append(value.name);
                        $("#details").append(', ');

                    });
                    $("#details").val('');

                    $("#details").append('<b>Parent Head</b>: ' + response.mainHeadId);

                    console.log(response.mainHeadId);
                }

            })
        }

        function getComapnyDetailsParentHead(headId) {
            var head_Id = headId;
            $.ajax({
                type: "POST",
                url: "{!! route('admin.get_comapny_details') !!}",
                dataType: 'JSON',
                data: {
                    'head_id': head_Id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#detailss').show().empty();
                    $("#detailss").append('<b>Companies</b>: ');

                    $.each(response.mainComapny, function(index, value) {

                        $("#detailss").append(index + 1 + '.');

                        $("#detailss").append(value.name);
                        $("#detailss").append(', ');

                    });
                    $("#detailss").val('');

                    $("#detailss").append('<b>Parent Head</b>: ' + response.mainHeadId);

                    console.log(response.mainHeadId);
                }

            })
        }


        $('#submitAccountHead').on('click', function() {
            var account_head = $("#account_head").val();
            var change_account_head = $("#change_account_head").val();


            if (account_head == "") {
                swal("Warning!", "Please Select Account Head", "warning");
                return false;
            }
            if (change_account_head == "") {
                swal("Warning!", "Please Select Parent Account Head", "warning");
                return false;
            }

            var systemdate = $('#create_application_date').val();
            if (account_head != "" && change_account_head != "") {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.change_account_head_position') !!}",
                    dataType: 'JSON',
                    data: {
                        'account_head': change_account_head,
                        'change_account_head': account_head,
                        'systemdate':systemdate
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status == "1") {
                            swal("Success", "Successfully Updated", "success");
                            $("#account_head").val("");
                            $("#change_account_head").val("");
                            $("#head_type").val("");
                            location.reload();
                            $("#account_head").val("");
                            $("#change_account_head").val("");
                            $("#head_type").val("");
                        } else if (response.status == "0") {
                            console.log(response);
                            swal("Warning",
                                "Company of account head not belongs to parent account head!",
                                "warning");

                        } else {
                            swal("Warning!", "Data not updated", "warning");
                            return false;
                        }
                    }

                })
            }
        })


    })
</script>
