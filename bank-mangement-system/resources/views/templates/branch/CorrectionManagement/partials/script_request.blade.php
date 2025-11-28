<script type="text/javascript">
    var CorrectionRequestTable;
    var correctionfilterrenewalTable;
    $(document).ready(function () {

        var date = new Date();
        $('#correction_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        });

        CorrectionRequestTable = $('#correction_request_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.correctionrequest.list') !!}",
                "type": "POST",
                //"data": {'type':$type,'searchform':$correctionFormData},
                "data": function (d) { d.searchform = $('form#correctionfilter').serializeArray() },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'created_at', name: 'created_at' },
                { data: 'correction_type_Id', name: 'correction_type_Id' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'field_name', name: 'field_name' },
                { data: 'old_value', name: 'old_value' },
                { data: 'new_value', name: 'new_value' },
                { data: 'description', name: 'description' },
                { data: 'branch', name: 'branch' },
                { data: 'company', name: 'company' },
                { data: 'user', name: 'user' },
                { data: 'created_by', name: 'created_by' },
                { data: 'status', name: 'status' },
                { data: 'status_date', name: 'status_date' },
                { data: 'status_remark', name: 'status_remark' },
            ]
        });
        $(CorrectionRequestTable.table().container()).removeClass('form-inline');
        /** renewal listing */
        correctionfilterrenewalTable = $('#correction_renewal_request_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.correctionrequest.renewal.list') !!}",
                "type": "POST",
                //"data": {'type':$type,'searchform':$correctionFormData},
                "data": function (d) { d.searchform = $('form#correctionfilterrenewal').serializeArray() },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'company', name: 'company' },
                { data: 'created_at', name: 'created_at' },
                { data: 'account_no', name: 'account_no' },
                { data: 'name', name: 'name' },
                { data: 'customer_id', name: 'customer_id' },
                { data: 'member_id', name: 'member_id' },
                { data: 'amount', name: 'amount' },
                { data: 'plan', name: 'plan' },
                { data: 'correction_description', name: 'correction_description' },
                { data: 'rejected_correction_description', name: 'rejected_correction_description' },
                { data: 'status', name: 'status' },
            ],'ordering':false
        });
        $(correctionfilterrenewalTable.table().container()).removeClass('form-inline');
        // Show loading image
        $(document).ajaxStart(function () {
            $(".loader").show();
        });

        // Hide loading image
        $(document).ajaxComplete(function () {
            $(".loader").hide();
        });

        $('.exportcorrection').on('click', function (e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var type = $('#type').val();

            $('#correction_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#correctionfilter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, type);
                $("#cover").fadeIn(100);
            }
            else {
                $('#correction_export').val(extension);
                $('form#correctionfilter').attr('action', "{!! route('correction.export.branch') !!}");
                $('form#correctionfilter').submit();
            }

        });
        $('.exportRenewalcorrection').on('click', function (e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var type = $('#type').val();

            $('#correction_export_renewal').val(extension);
            if (extension == 0) {
                var formData = jQuery('#correctionfilterrenewal').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportrenewal(0, chunkAndLimit, formData, chunkAndLimit, type);
                $("#cover").fadeIn(100);
            }
            else {
                $('#correction_export_renewal').val(extension);
                $('form#correctionfilterrenewal').attr('action', "{!! route('correction.export.branch.renewal') !!}");
                $('form#correctionfilterrenewal').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExportrenewal(start, limit, formData, chunkSize, type) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('correction.export.branch.renewal') !!}",
                data: formData,
                success: function (response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportrenewal(start, limit, formData, chunkSize);
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
        function doChunkedExport(start, limit, formData, chunkSize, type) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('correction.export.branch') !!}",
                data: formData,
                success: function (response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExport(start, limit, formData, chunkSize);
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


        jQuery.fn.serializeObject = function () {
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function () {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
        $('#correctionfilter').validate({
            rules: {
                company_id: {
                    required: true,
                }

            },
            messages: {
                company_id: {
                    "required": "Please select company.",
                }


            }
        });
        $('#correctionfilterrenewal').validate({
            rules: {
                company_id: {
                    required: true,
                }
            },
            messages: {
                company_id: {
                    "required": "Please select company.",
                }
            }
        });


    });

    function correctionSearchForm() {
        if ($('#correctionfilter').valid()) {
            $('#is_search').val("yes");
            // $(".datatableblock").removeClass('hideTableData');
            $(".table-section").addClass("show-table");
            CorrectionRequestTable.draw();
        }
    }
    function correctionRenewalSearchForm() {
        if ($('#correctionfilterrenewal').valid()) {
            $('#is_search').val("yes");
            $(".table-section").addClass("show-table");
            correctionfilterrenewalTable.draw();
        }
    }

    function resetRenewalCorrectionForm() {
        var form = $("#correctionfilterrenewal"),
        validator = form.validate();
        validator.resetForm();
        $('#is_search').val("no");
        $('#company_id').val('1');
        $('#company_id').trigger('change');
        $('#account_no').val('');
        $('#member_name').val('');
        $('#status').val('');
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");
        correctionfilterrenewalTable.draw();
    }
    function resetCorrectionForm() {
        var form = $("#correctionfilter"),
        validator = form.validate();
        validator.resetForm();
        $('#is_search').val("no");
        $('#company_id').val(0);
        $('#company_id').trigger('change');
        $('#associate_code').val('');
        $('#customer_id').val('');
        $('#status').val('');
        // $(".datatableblock").addClass("hideTableData");
        $(".table-section").removeClass("show-table");
        $(".table-section").addClass("hide-table");
        CorrectionRequestTable.draw();
    }

</script>