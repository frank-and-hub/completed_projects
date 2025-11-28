<script type="text/javascript">
    var DepositAmountTable;
    const currentDate = $("#globalDate").val();
    var date = new Date();
    $(document).ready(function() {
        $('#date_range').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: "bottom",
        }).on("changeDate", function(e) {
            $('#end_date').datepicker('setStartDate', e.date);
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
            orientation: "bottom",
        });

        DepositAmountTable = $('#DepositAmountTable_listing').DataTable({
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
                    scrollTop: ($('#DepositAmountTable_listing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },

            ajax: {
                "url": "{!! route('admin.report.deposit_amount_report_listing') !!}",
                "type": "POST",
                // "data": function(d) {
                //        // d.searchform=$('form#filter').serializeArray(),
                // 		d.start_date = $('#date_range').val(),
                //         d.globalDate = $('#globalDate').val(),
                //         d.end_date = $('#end_date').val(),
                //         d.branch_id = $('#branch_id').val(),
                // 		d.plan_id = $('#plan_id').val(),
                //         d.is_search = $('#is_search').val(),
                //         d.export = $('#export').val()
                // },
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
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
                // {
                //     data: 'c_id',
                //     name: 'c_id'
                // },
                {
                    data: 'plan',
                    name: 'plan'
                },
                {
                    data: 'plan_tenure',
                    name: 'plan_tenure'
                },
                {
                    data: 'demo_amount',
                    name: 'demo_amount'
                },
                {
                    data: 'renewal_amount',
                    name: 'renewal_amount'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'maturity_deno',
                    name: 'maturity_deno'
                },
                {
                    data: 'maturity_total_amount',
                    name: 'maturity_total_amount'
                },
            ],
            "bDestroy": true,
        });
        $(DepositAmountTable.table().container()).removeClass('form-inline');


        $('.export-deposit-amount').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');

            var formData = jQuery('#filter').serializeObject();

            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text(Math.floor(Math.random() * 10));
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1);
            $("#cover").fadeIn(100);

        });
        $(document).on('change', '#company_id', function() {
            $("#plan_id").val('');
            $('#plan_id').find('option').remove();
            $('#plan_id').append('<option value="">Select Plan</option>');
            var company_id = $('#company_id').val();
            if (company_id != '') {
                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.deposit_amount_report_Export.report.plans') !!}",
                    dataType: 'JSON',
                    data: {
                        'company_id': company_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $.each(response.plans, function(index, value) {
                            $("#plan_id").append("<option value='" + value.id + "'>" + value.name + "</option>");
                        });

                    }
                });
            }

        });


        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize, page) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;

            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.deposit_amount_report_Export.report.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // console.log(response);
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

        $('#filter').validate({
            rules: {
                date_range: {
                    required: true,
                    dateDdMm: true,
                },
                end_date: {
                    required: true,
                    dateDdMm: true,
                },
            },
            messages: {
                date_range: {
                    "required": "Please select date.",
                },
                end_date: {
                    "required": "Please select date.",
                },
            }
        })




    });

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            DepositAmountTable.draw();
            $('.datatable').show();
        }
    }

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $("#globalDate").val('');
        var $dates = $('#date_range, #end_date').datepicker();
        $dates.datepicker('setDate', date);
        $dates.datepicker('setDate', null);
        $('#date_range').val('');
        $('#end_date').val('');
        $('#branch_id').val('');
        $('#plan_id').val('');
        $('#company_id').val('0');
        $('#company_id').trigger('change');
        $('#is_search').val("no");
        DepositAmountTable.draw();
        $('.datatable').hide();
        // DepositAmountTable.draw();
    }
</script>