<script type="text/javascript">
    $(document).ready(function() {       
     
    $(document).on('mouseover', '#start_date', function() {
        var EndDate = $('#create_application_date').val() || $('#gdatetime').val();
        //  start_date datepicker
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: EndDate,
        }).on("changeDate", function(e) {
            var startDate = e.date;
            var endDate = new Date(startDate);
            endDate.setMonth(endDate.getMonth() + 1);
            var EndStart = $('#start_date').val();
            // Destroy any existing end_date datepicker
            $('#end_date').datepicker('remove');
            $('#end_date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate: EndStart,
                endDate: endDate,
            });
            $('#end_date').datepicker('setDate', endDate);
        });
    });
    $('#company_id').on('change', function() {
    var company_id = $(this).val();
    if (company_id != '') {
        $.ajax({
            url: "{!! route('admin.fetchbranch') !!}",
            type: "POST",
            dataType: 'JSON',
            data: {
                'company_id': company_id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                //console.log(response);
                $('#branch').find('option').remove();
                $('#branch').append(
                    '<option value="">Select branch Name</option>');
                $.each(response, function(index, value) {
                    console.log(value);
                    $("#branch").append("<option value='" + value.id +
                        "'>" + value.name + "</option>");
                });
            }
        })
    } else {
        $('#branch').empty().append(
            '<option value="">----Please Select Branch----</option>');
    }
    });
    });

    function searchForm() {
        if ($('#filter').valid()) {
            let company_id = $('#company_id').val();
            let branch = $('#branch').val();
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            $('#report_data').html('');
            if ($('#filter').valid()) {
                reportFile(company_id, branch, start_date, end_date);
            }
        }
    }

    function reportFile(company_id = null, branch = null, start_date = null, end_date = null) {
        $.post("{{route('admin.dayBook.report')}}", {
            'company_id': company_id,
            'start_date': start_date,
            'branch': branch,
            'end_date': end_date
        }, function(e) {
            // console.log(e);
            if (e.msg_type == 'success') {
                console.log(e.view);
                $('#report_data').append(e.view);
            } else {
                swal('Warning!', "" + e.msg_type + "", 'warning');
                $('#reset_log ').click();
            }
            return false;
        }, "JSON");
    }

    function searchFormBranch() {
        let company_id = $('#company_id').val();
        let branch = $('#branch').val();
        let start_date = $('#start_date').val();
        let end_date = $('#end_date').val();
        $('#report_data').html('');
        if ($('#filter').valid()) {
            reportFileBranch(company_id, branch, start_date, end_date);
        }
    }

    function reportFileBranch(company_id = null, branch = null, start_date = null, end_date = null) {
        $.post("{{route('branch.dayBook.report')}}", {
            'company_id': company_id,
            'start_date': start_date,
            'branch': branch,
            'end_date': end_date
        }, function(e) {
            // console.log(e);
            if (e.msg_type == 'success') {
                console.log(e.view);
                $('#report_data').append(e.view);
            } else {
                swal('Warning!', "" + e.msg_type + "", 'warning');
                $('#reset_log ').click();
            }
            return false;
        }, "JSON");
    }

    function resetForm() {
        $('#company_id').val('');
        $('#branch').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        $('#is_search').val('no');
        $('#report_data').html('');
    }

    $('#filter').validate({
        rules: {
            'branch': 'required',
            'company_id': 'required',
            'start_date': 'required',
            'end_date': 'required'

        },
        messages: {
            branch: {
                required: "Please select Branch"
            },
            company_id: {
                required: "Please select Company Name"
            },
            start_date: {
                required: "Please select Start Date"
            },
            end_date: {
                required: "Please select End Date"
            },
        },
    });
</script>