<script type="text/javascript">
    var CashinhandTable;
    var currentDate = $("#globalDate").val();
    $(".table-section").hide();
    $(document).ready(function() {
        var date = new Date();
        $('#start_date').datepicker({
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
        }).datepicker('setDate', date);
        

        CashinhandTable = $('#Cashinhand_listing').DataTable({
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
                    scrollTop: ($('#Cashinhand_listing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.report.cashinhandListing') !!}",
                "type": "POST",
                "data": function(d) {
                    // d.searchform=$('form#filter').serializeArray(),
                    d.start_date = $('#start_date').val(),
                        d.globalDate = $('#globalDate').val(),
                        d.end_date = $('#end_date').val(),
                        d.company_id = $('#company_id').val(),
                        d.branch_id = $('#branch').val(),
                        d.is_search = $('#is_search').val(),
                        d.export = $('#export').val()

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
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'opening_cashinhand',
                    name: 'opening_cashinhand'
                },
                {
                    data: 'total_cash_receving',
                    name: 'total_cash_receving'
                },
                {
                    data: 'total_cash_Payment',
                    name: 'total_cash_Payment'
                },
                {
                    data: 'approve_banking',
                    name: 'approve_banking'
                },
                {
                    data: 'unapprove_banking',
                    name: 'unapprove_banking'
                },
                {
                    data: 'closing_cashinhand',
                    name: 'closing_cashinhand'
                },
            ],
        "bDestroy": true,
        });
        $(CashinhandTable.table().container()).removeClass('form-inline');


        $('.export-cash-in-hand').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = {}
            formData['start_date'] = jQuery('#start_date').val();
            formData['start_date'] = jQuery('#end_date').val();
            formData['branch_id'] = jQuery('#branch').val();
            formData['company_id'] = jQuery('#company_id').val();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text(Math.floor(Math.random() * 10));
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit, 1);
            $("#cover").fadeIn(100);
        });

        $('.export-cash-in-handd').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExports(0, chunkAndLimit, formData, chunkAndLimit, 1);
                $("#cover").fadeIn(100);
            } else {
                var start_date = $('#start_date').val();
                $('#from_date').val(start_date);
                $('#start_date').val(start_date);
                $('#export').val(extension);
                $('form#filter').attr('action', "{!! route('admin.maturity.report.export') !!}");
                $('form#filter').submit();
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
                url: "{!! route('admin.cashinhand_demandlist_Export.report.export') !!}",
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
                start_date: {
                    required: true,
                    dateDdMm: true,
                },
                end_date: {
                    required: true,
                    dateDdMm: true,
                }
            },
            messages: {
                start_date: {
                    "required": "Please select date.",
                },
                end_date: {
                    "required": "Please select date.",
                }
            }
        })

        
    
    

    });

    function searchForm() {
        if ($('#filter').valid()) {
            $(".table-section").show();
            $('#is_search').val("yes");
            CashinhandTable.draw();
        }

    }

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        const currentDate = $("#globalDate").val();
        $(".table-section").hide();
        $('#is_search').val("no");
        $('#end_date').val("");
        $('#start_date').val("");
        $('#company_id').val('0');
        $("#company_id").trigger('change');
        CashinhandTable.draw();
    }

    $(document).ajaxStart(function() {
        $(".loader").show();
    });
    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });
    
  
</script>