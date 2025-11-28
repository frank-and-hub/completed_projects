<script type="text/javascript">
    'use strict';
    var gst_collection_listing;

    $(document).ajaxStart(function() {
        $(".loader").show();
    });

    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });

    $(document).ready(function() {

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

        jQuery.validator.addMethod("dateDdMm", function(value, element, p) {
            var result = false;
            if (this.optional(element) ||
                /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
                $.validator.messages.dateDdMm = "";
                result = true;
            }
            return result;
        }, "Please enter valid date");

        jQuery.validator.addMethod("enddatedateDdMm", function(value, element, params) {
            var start_date_str = $('input[name="start_date"]').val();
            var end_date_str = value;

            var start_date_obj = parseDate(start_date_str);
            var end_date_obj = parseDate(end_date_str);
            console.log(end_date_obj,"end_date" , start_date_obj,"start_date");
            if (!isValidDate(start_date_obj) || !isValidDate(end_date_obj)) {
                return false;
            }

            return end_date_obj >= start_date_obj;
        }, "End date must not be greater than start date");

        function parseDate(dateStr) {
            var parts = dateStr.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        function isValidDate(date) {
            return date instanceof Date && !isNaN(date);
        }
        $('#start_date').hover(function() {
            var created_at = $('#create_application_date').val();
            $('#start_date').datepicker({
                format: "dd/mm/yyyy",
				orientation: "bottom",
                todayHighlight: true,
                autoclose: true,
                endDate: created_at
            }).on('change',function(){
                var cordate = $(this).val();
                $('#end_date').datepicker({
                    format: "dd/mm/yyyy",
                    todayHighlight: true,
					orientation: "bottom",
                    autoclose: true,
                    endDate: created_at,
                    startDate:cordate
                });
            });
        });
        $('#filter').validate({ 
            rules: {
                'start_date': 'required',
                'end_date': 'required',                
                'company_id': 'required',
            },
            messages: {
                'start_date': {
                    required: 'Please select a from date.'
                },
                'end_date': {
                    required: 'Please select a to date.'
                },
                'company_id': {
                    required: 'Please select a Company.'
                }
            },
            submitHandler: function(form) {
                $('.submit-payable').prop('disabled', true);
                form.submit();
            },
        });
        gst_collection_listing = $('#gst_collection_listing').DataTable({
            processing: true,
            searching: false,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('form#filter').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.duties_taxes.gst.report.collection.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex'},
                {data: 'state'},
                {data: 'branch'},
                {data: 'date'},
                {data: 'customer_id'},
                {data: 'name'},
                {data: 'amount'},
                {data: 'gst'},
                {data: 'head'}
            ],
            "ordering": false,
        });
        $(gst_collection_listing.table().container()).removeClass('form-inline');

        
        $(document).on('click', '#reset_form', function() {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#state').val('');
            $('#company_id').val('');
            gst_collection_listing.draw();
        });

        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#gst_collection_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#gst_collection_export').val(extension);
                $('form#filter').attr('action', "{!! route('admin.duties_taxes.gst.report.collection.listing.export') !!}");
                $('form#filter').submit();
            }
        });
    });

    
    function searchForm() {
        if ($('#filter').valid()) {
            $('#gst_collection_export').val('1');
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            gst_collection_listing.draw();
        }
    }

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#state').val('');
        $('#company_id').val('');
        $(".table-section").addClass("hideTableData");
        gst_collection_listing.draw();
    }

    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.duties_taxes.gst.report.collection.listing.export') !!}",
            data: formData,
            success: function(e) {
                if (e.result == 'next') {
                    start = start + chunkSize;
                    doChunkedExport(start, limit, formData, chunkSize);
                    $(".loaders").text(e.percentage + "%");
                } else {
                    var csv = e.fileName;

                    $(".spiners").css("display", "none");
                    $("#cover").fadeOut(100);
                    window.open(csv, '_blank');
                }
            }
        });
    }    
</script>
