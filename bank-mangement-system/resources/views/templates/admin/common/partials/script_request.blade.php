<script type="text/javascript">
    var CorrectionRequestTable;
    $(document).ready(function() {

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
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.correctionrequest.list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#correctionfilter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'correction_type_Id',
                    name: 'correction_type_Id'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'uid',
                    name: 'uid'
                },
                {
                    data: 'field_name',
                    name: 'field_name'
                },
                {
                    data: 'old_value',
                    name: 'old_value'
                },
                {
                    data: 'new_value',
                    name: 'new_value'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'user',
                    name: 'user'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'status_date',
                    name: 'status_date'
                },
                {
                    data: 'status_remark',
                    name: 'status_remark'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        $(CorrectionRequestTable.table().container()).removeClass('form-inline');

        $(document).on('click', '.approve', function() {
            const userId = $(this).data('id');
            const correction_id = $(this).data('correctionid');

            $('#csid').val(userId);
            $('#corr_id').val(correction_id);

        })
        // Show loading image
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        // Hide loading image
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });

        $('.exportcorrection').on('click', function(e) {
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
            } else {
                $('#correction_export').val(extension);

                $('form#correctionfilter').attr('action', "{!! route('correction.export.request') !!}");

                $('form#correctionfilter').submit();
            }

        });


        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize, type) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('correction.export.request') !!}",
                data: formData,
                success: function(response) {
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


        jQuery.fn.serializeObject = function() {
            var o = {};
            var a = this.serializeArray();
            jQuery.each(a, function() {
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
                },
                branch_id: {
                    required: true,
                },

            },
            messages: {
                company_id: {
                    "required": "Please select company.",
                },
                branch_id: {
                    "required": "Please select Branch.",
                },


            }
        })


        $(document).on('click', '.correction-view-button', function() {
            var corrections = $(this).attr('data-correction-details');
            $('.form-corrections').html('')
            $('.form-corrections').html(corrections)
        });

        $(document).on('click', '.correction-reject-button', function() {
            var correctionsId = $(this).attr('data-correction-id');
            $('#correction_id').val(correctionsId)
        });



        $('#correction-reject-form').validate({ // initialize the plugin
            rules: {
                'rejection': 'required',
            },
        });

        $(document).on('click', '.approve', function() {
            var correction_id = $(this).attr('data-correctionid');
            var created_at = $('.created_at').val();
            swal({
                    title: "Are you sure?",
                    text: "Do you want to approve this record?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary ",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger ",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: "{!! route('correction.approve.request') !!}",
                            dataType: 'JSON',
                            data: {
                                'correction_id': correction_id,
                                'created_at': created_at
                            },
                            success: function(response) {
                                if (response == 'success') {
                                    CorrectionRequestTable.draw();
                                    swal('Success!', "Data updatede Sucessfully", 'success');
                                } else {
                                    swal('Warning!', "There was some error", 'warning');
                                }
                            }
                        });
                    }
                });

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