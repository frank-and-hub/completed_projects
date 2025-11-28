<script type="text/javascript">
    $(document).ready(function() {
        var urlDynimic = $('#url').val();
        if (urlDynimic == 1) {
            var urlset = "{!! route('admin.ecs.ecs.transactions_listing') !!}";
            var exportUrl = "{!! route('admin.ecs.ecs.transactions_export') !!}";
        } else {
            var urlset = "{!! route('branch.ecs.ecs.transactions_listing') !!}";
            var exportUrl = "{!! route('branch.ecs.ecs.transactions_export') !!}";
        }
        $('#ecs_filter').validate({
            rules: {
                 'company_id': 'required',
            },
            messages: {
                customer_id: {
                    required: "Please select company Id."
                },
            },
        });
        $("#from_date, #to_date").hover(function() {
            var current_date = $('#create_application_date').val();
            $('#from_date, #to_date').datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                endDate: current_date,                
            });
        });
        EcsTableListing = $('#loan_ecs_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            searching: false,
            ordering: false,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#loan_ecs_listing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": urlset,
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#ecs_filter').serializeArray(),
                    d.from_date = $('#from_date').val(),
                    d.to_date = $('#to_date').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            searching: true,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'account_no',
                    name: 'account_no'
                },
                {
                    data: 'plan',
                    name: 'plan'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'collector_code',
                    name: 'collector_code'
                },
                {
                    data: 'collector_name',
                    name: 'collector_name'
                },
                {
                    data: 'amount',
                    name: 'amount',
                },
                {
                    data: 'ecs_mode',
                    name: 'ecs_mode'
                },
                {
                    data: 'ecs_status',
                    name: 'ecs_status',
                },
                {
                    data: 'bounc_charge',
                    name: 'bounce_charge',
                },
                {
                    data: 'sgst',
                    name: 'sgst',
                },
                {
                    data: 'cgst',
                    name: 'cgst',
                },
                {
                    data: 'igst',
                    name: 'igst',
                },
            ],
            "bDestroy": true,
        });
        $(EcsTableListing.table().container()).removeClass('form-inline');
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#ecs_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#ecs_filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#ecs_export').val(extension);
                $('form#ecs_filter').attr('action', exportUrl);
                $('form#ecs_filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: exportUrl,
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
    });
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
    function searchForm() {
        if ($('#ecs_filter').valid()) {
            $('#is_search').val("yes");
            $('.d-none').removeClass('d-none');
            EcsTableListing.draw();
        }
    }
    //Reset Button Function
    function resetForm() {
        var form = $("#loan_ecs_listing"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#customer_id').val("");
        $('#company_id').val([0]);
        $('#branch').val("");
        $('#from_date').val("");
        $('#to_date').val("");
        $('#ecs_type').val("");
        $('#account_no').val("");
        $('#ecs_status').val("");
        $('#is_search').val("no");
        $('#data_div').addClass('d-none');
    }
</script>