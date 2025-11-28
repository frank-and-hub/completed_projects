<script type="text/javascript">
    $(document).ready(function() {
        $('#filter_type').on('change', function() {
            var type_id = $(this).val();
            $.ajax({
                url: "{!! route('admin.planLog.name') !!}",
                type: "POST",
                dataType: 'JSON',
                data: {
                    'type_id': type_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#plan_name').find('option').remove();
                    $('#plan_name').append(
                        '<option value="">Select Plan Name</option>');
                    $.each(response, function(index, value) {
                        $("#plan_name").append("<option value='" + value.id +
                            "'>" + value.name + "</option>");
                    });
                }
            })
        })
    });
    $('#filter').validate({
        rules: {
            filter_type: {
                required: true,
            },
            plan_name:{
                required: true,
            },
        },
        messages: {
            filter_type: {
                required: "Please select a type.",
            },
            plan_name:{
                required: "Please select plan.",
            }
        },
    });



    function resetForm() {
        $('#filter_type').val('');
        $('#plan_name').empty().append('<option value="">--- Please Select Plan ---</option>');
        $('#branchCode').val('');
        $('#is_search').val('no');
        $('#update_log_data').html('');
    }

    function searchForm() {
        let filter_type = $('#filter_type').val();
        let plan_name = $('#plan_name').val();
        $('#update_log_data').html('');
        if ($('#filter').valid()) {
            updateLogFile(filter_type, plan_name);
        }
    }

    function updateLogFile(filter_type = null, plan_name = null) {
        $.post("{{route('admin.Log.detail')}}", {
            'filter_type': filter_type,
            'plan_name': plan_name
        }, function(e) {
            console.log(e);
            if (e.msg_type == 'success') {
                console.log(e.view);
                $('#update_log_data').append(e.view);
            } else {
                swal('Warning!', "" + e.msg_type + "", 'warning');
                $('#reset_log ').click();
            }
            return false;
        }, "JSON");
    }
</script>