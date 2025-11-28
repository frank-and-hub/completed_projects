<script type="text/javascript">
    var loanReport;
    $(document).ready(function() {
        var date = new Date();
        const currentDate = $('.branch_report_currentdate').val();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        }).datepicker('setDate', currentDate).datepicker('fill');
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        }).datepicker('setDate', currentDate).datepicker('fill');
        loanReport = $('#loan_list').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.report.loanlist') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'applicant_name',
                    name: 'applicant_name'
                },
                //  {data: 'applicant_id', name: 'applicant_id'},
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'applicant_phone_number',
                    name: 'applicant_phone_number'
                },
                // {data: 'membership_id', name: 'membership_id'},
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'sector',
                    name: 'sector'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'sanctioned_amount',
                    name: 'sanctioned_amount'
                },
                // {data: 'sanctioned_amount', name: 'sanctioned_amount'},
                {
                    data: 'sanction_date',
                    name: 'sanction_date'
                },
                {
                    data: 'emi_rate',
                    name: 'emi_rate'
                },
                {
                    data: 'no_of_installement',
                    name: 'no_of_installement'
                },
                {
                    data: 'loan_mode',
                    name: 'loan_mode'
                },
                {
                    data: 'loan_type',
                    name: 'loan_type'
                },
                {
                    data: 'loan_issue_date',
                    name: 'loan_issue_date'
                },
                {
                    data: 'loan_issue_mode',
                    name: 'loan_issue_mode'
                },
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'total_recovery_amount',
                    name: 'total_recovery_amount'
                },
                // {
                //     data: 'total_recovery_emi_till_date',
                //     name: 'total_recovery_emi_till_date'
                // },
                {
                    data: 'closing_amount',
                    name: 'closing_amount'
                },
                {
                    data: 'balance_emi',
                    name: 'balance_emi'
                },
                {
                    data: 'emi_should_be_received_till_date',
                    name: 'emi_should_be_received_till_date'
                },
                {
                    data: 'future_emi_due_till_date',
                    name: 'future_emi_due_till_date'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'co_applicant_name',
                    name: 'co_applicant_name'
                },
                {
                    data: 'co_applicant_number',
                    name: 'co_applicant_number'
                },
                {
                    data: 'gurantor_name',
                    name: 'gurantor_name'
                },
                {
                    data: 'gurantor_number',
                    name: 'gurantor_number'
                },
                {
                    data: 'applicant_address',
                    name: 'applicant_address'
                },
                {
                    data: 'first_emi_date',
                    name: 'first_emi_date'
                },
                {
                    data: 'loan_end_date',
                    name: 'loan_end_date'
                },
                //{
                //     data: 'total_deposit_till_date',
                //     name: 'total_deposit_till_date'
                // }
            ],
        });
        $(loanReport.table().container()).removeClass('form-inline');
        $('.export').on('click', function(e) {
            // $('form#filter').attr('action', "{!! route('branch.loan.report.export') !!}");
            // $('form#filter').submit();
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            //$('#report_export').val(extension);
            var formData = {}
            formData['start_date'] = jQuery('#start_date').val();
            formData['end_date'] = jQuery('#end_date').val();
            formData['plan'] = jQuery('#plan').val();
            formData['branch_id'] = jQuery('#branch_id').val();
            formData['status'] = jQuery('#status').val();
            formData['application_number'] = jQuery('#application_number').val();
            formData['member_id'] = jQuery('#member_id').val();
            formData['is_search'] = jQuery('#is_search').val();
            formData['export'] = jQuery('#export').val();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1);
            $("#cover").fadeIn(100);
        });
          // function to trigger the ajax bit
          function doChunkedExport(start, limit, formData, chunkSize, page) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('branch.loan.report.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExport(start, limit, formData, chunkSize, page);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
                        console.log('DOWNLOAD');
                        $(".spiners").css("display", "none");
                        $("#cover").fadeOut(100);
                        window.open(csv, '_blank');
                    }
                }
            });
        }
        $('#filter').validate({
            rules: {
                application_number: {
                    number: true,
                },
                member_id: {
                    number: true,
                },
                company_id: {
                    required: true,
                },
            },
        })
        $(document).on('change', "#company_id", function() {
            $('#plan').find('option').remove();
            const company_id = $(this).val();
            jQuery.ajax({
                url: "{!! route('branch.report.companyIdToLoan') !!}",
                type: "POST",
                data: {
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    // get the select element by ID
                    var select2 = $('#plan');
                    var selectsomething = "Select Loan Type";
                    select2.append('<option value="">' + selectsomething + '</option>');
                    $.each(data.loan, function(key, value) {
                        select2.append('<option value="' + key + '">' + value + '</option>');
                    });
                },
            });
        });
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });
    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('datatable');
            loanReport.draw();
        }
    }
    function resetForm()
    {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $('.branch_report_currentdate').val();
        $('#branch_id').val('');
        $('#start_date').val(currentDate);
        $('#end_date').val(currentDate);
        $('#plan').val('');
        $('#status').val('');
        $('#application_number').val('');
        $('#member_id').val('');
        $('#is_search').val("yes");
        $('#company_id').val();
        $('#branch').empty();
        $('#plan').empty();
        $('#plan').append($('<option>', {
        value: '',
        text: 'Please Select Loan Type'
        }));
        // loanReport.draw();
        $(".table-section").addClass("datatable");
    }
</script>