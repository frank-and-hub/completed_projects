<script type="text/javascript">
    var NonPerformingAssetsReport;
    $(document).ready(function() {
        $('.numberonly').bind('keyup blur', function() {
            var node = $(this);
            node.val(node.val().replace(/[^0-9 ]/g, ''));
        });
        let branch_id = $("#branch_id option:selected").val();
        NonPerformingAssetsReport = $('#non_performing_assets_report').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            sorting: false,
            bFilter: false,
            ordering: false,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#non_performing_assets_report').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.report.non_Performing_assets_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                    //   d.branch_id=$('#branch_id').val(),
                    //   d.loan_type_id=$('#loan_type_id').val(),
                    //   d.is_search=$('#is_search').val(),
                    //   d.created_at=$('#created_at').val(),
                    //   d.globalDate=$('#globalDate').val(),
                    //   d.export=$('#export').val(),
                    //   d.page = $('#non_performing_assets_report').DataTable().page.info()
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
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'account_no',
                    name: 'account_no'
                },
                {
                    data: 'loan_plan_name',
                    name: 'loan_plan_name'
                },
                {
                    data: 'lone_sanction_date',
                    name: 'lone_sanction_date'
                },
                {
                    data: 'lone_sanction_amt',
                    name: 'lone_sanction_amt'
                },
                {
                    data: 'emi',
                    name: 'emi'
                },
                {
                    data: 'emi_amt',
                    name: 'emi_amt'
                },
                {
                    data: 'emi_period',
                    name: 'emi_period'
                },
                {
                    data: 'closing_date',
                    name: 'closing_date'
                },
                {
                    data: 'last_recovery_date',
                    name: 'last_recovery_date'
                },
                {
                    data: 'total_recovery_amt',
                    name: 'total_recovery_amt'
                },
                {
                    data: 'over_due_day',
                    name: 'over_due_day'
                },
            ],
            "bDestroy": true,
        });
        $(NonPerformingAssetsReport.table().container()).removeClass('form-inline');
        $('.export-npa').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');

            var formData = jQuery('#filter').serializeObject();

            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text(Math.floor(Math.random() * 10));
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
                url: "{!! route('admin.npa_export.report.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
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
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $(document).on('change', '#company_id', function() {
            $("#loan_type_id").val('');
            $('#loan_type_id').find('option').remove();
            $('#loan_type_id').append('<option value="">Select loan Plan</option>');
            var company_id = $('#company_id').val();
            if (company_id != '') {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.npa.report.loanplans') !!}",
                    dataType: 'JSON',
                    data: {
                        'company_id': company_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $.each(response.plans, function(index, value) {
                            $("#loan_type_id").append("<option value='" + value.id + "'>" + value.name + "</option>");
                        });

                    }
                });
            }

        });
        $.validator.addMethod("dateDdMm", function(value, element, p) {
            if (this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g
                .test(value) == true) {
                $.validator.messages.dateDdMm = "";
                result = true;
            } else {
                $.validator.messages.dateDdMm = "Please enter valid Date.";
                result = false;
            }
            return result;
        }, "");
        /*
    $('#filter').validate({
        rules: {
            branch_id: {
                required: true,
            },
            loan_type_id: {
                required: true,
            },
        },
        messages: {
            branch_id: {
                "required": "Please select Branch.",
            },
            loan_type_id: {
                "required": "Please select Loan Plan type.",
            },
        }
    })
	*/
    });

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            NonPerformingAssetsReport.draw();
            $('.datatable').show();
        }
    }

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#loan_type_id').val('');
        $('#branch_id').val('');
        $('#company_id').val('0');
        $('#customer_id').val('');
        $('#member_id').val('');
        $('#account_no').val('');
        $('#company_id').trigger('change');
        $('#is_search').val("no");
        $('.datatable').hide();
        NonPerformingAssetsReport.draw();
    }
</script>