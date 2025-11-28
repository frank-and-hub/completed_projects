<script type="text/javascript">
    $(document).ready(function () {
        var date = new Date();
        const currentDate = $('#adm_report_currentdate').val();
        $('#maturity_start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: 'bottom'
        });
        //.datepicker('setDate', currentDate).datepicker('fill')
        $('#maturity_end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: 'bottom'
        });
        //.datepicker('setDate', currentDate).datepicker('fill')
        maturityReport = $('#maturity_payment_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            bFilter: false,
            ordering: false,
            lengthMenu: [20, 40, 50, 100],
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.report.maturityPaymentlist') !!}",
                "type": "POST",
                "data": function (d) { d.searchform = $('form#filter').serializeArray() },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'branch', name: 'branch' },
                { data: 'customer_id', name: 'customer_id' },
                { data: 'member_id', name: 'member_id' },
                { data: 'member_name', name: 'member_name' },
                { data: 'account_number', name: 'account_number' },
                { data: 'plan', name: 'plan' },
                { data: 'tenure', name: 'tenure' },
                { data: 'open_date', name: 'open_date' },
                { data: 'maturity_date', name: 'maturity_date' },
                { data: 'payment_date', name: 'payment_date' },
                { data: 'total_deposit', name: 'total_deposit' },
                { data: 'payment_amount', name: 'payment_amount' },
                { data: 'payment_mode', name: 'payment_mode' },
                { data: 'associate_code', name: 'associate_code' },
                { data: 'associate_name', name: 'associate_name' },
            ],
            "ordering": true,
            "bDestroy": true
        });
        $(maturityReport.table().container()).removeClass('form-inline');
        $('.export-maturity-demanad').on('click', function (e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            //$('#report_export').val(extension);
            var formData = {}
            formData['maturity_start_date'] = jQuery('#maturity_start_date').val();
            formData['maturity_end_date'] = jQuery('#maturity_end_date').val();
            formData['plan'] = jQuery('#plan').val();
            formData['branch_id'] = jQuery('#branch_id').val();
            formData['plan'] = jQuery('#plan').val();
            formData['application_number'] = jQuery('#application_number').val();
            formData['member_id'] = jQuery('#member_id').val();
            formData['member_name'] = jQuery('#member_name').val();
            formData['account_no'] = jQuery('#account_no').val();
            formData['associate_code'] = jQuery('#associate_code').val();
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
                url: "{!! route('admin.maturitypaymentlistExport.report.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
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
        $(document).on('change', '#company_id', function () {
            $("#plan").val('');
            $('#plan').find('option').remove();
            $('#plan').append('<option value="">Select Plan</option>');
            var company_id = $('#company_id').val();
            if (company_id != '') {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.report.maturityListing.plans') !!}",
                    dataType: 'JSON',
                    data: { 'company_id': company_id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $.each(response.plans, function (index, value) {
                            $("#plan").append("<option value='" + value.id + "'>" + value.name + "</option>");
                        });
                    }
                });
            }
        });
        $('#filter').validate({
            rules: {
                application_number: {
                    number: true,
                },
                member_id: {
                    number: true,
                },
            },
        })
        $(document).ajaxStart(function () {
            $(".loader").show();
        });
        $(document).ajaxComplete(function () {
            $(".loader").hide();
        });
    });
    // search Filter and Data
    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").addClass("show-table");
            $(".table-section").removeClass("hide-table");
            maturityReport.draw();
        }
    }
    // Form Reset Function start
    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        $('#is_search').val("no");
        $('#maturity_start_date').val('');
        $('#maturity_end_date').val('');
        $('#company_id').val('0');
        $('#company_id').trigger("change");
        $('#status').val('');
        $('#plan').val('');
        $('#branch').val('');
        $('#member_id').val('');
        $('#member_name').val('');
        $('#account_no').val('');
        $('#associate_code').val('');
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");
        maturityReport.draw();
    }
        // Form Reset Function end
</script>