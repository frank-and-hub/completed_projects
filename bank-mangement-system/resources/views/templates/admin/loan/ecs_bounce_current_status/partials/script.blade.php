<script type="text/javascript">
    'use strict';
    var bounce_ecs_current_status_listing;

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
        // $('#start_date').change(function() {
        //     var start_date = $(this).val();
        //     $('#end_date').datepicker('setStartDate', start_date);
        // });

        // $('#start_date, #end_date').hover(function() {
        //     var created_at = $('#created_at').val(); 
        //     $(this).datepicker({
        //         format: "dd/mm/yyyy",
        //         todayHighlight: true,
        //         autoclose: true,
        //         endDate: created_at 
        //     });
        // });
        $('#date').hover(function() {
            var created_at = $('#created_at').val(); 
            $(this).datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                endDate: created_at 
            });
        });
        $('#filter').validate({ 
            rules: {               
                'company_id': 'required',
                'date':'required'
            },
            messages: {
                'company_id': {
                    required: 'Please select a Company.'
                },
                'date':{
                    required: 'Please select a date.'
                },
            },
            submitHandler: function(form) {
                $('.submit-payable').prop('disabled', true);
                form.submit();
            },
        });
        bounce_ecs_current_status_listing = $('#bounce_ecs_current_status_listing').DataTable({
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
                "url": "{!! route('admin.loan.ecs.bounce_charge.status.listing') !!}",
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
                {data: 'regan'},
                {data: 'branch'},
                {data: 'date'},
                {data: 'account_number'},
                {data: 'plan'},
                {data: 'customer'},
                {data: 'mobile_no'},
                {data: 'associate_no'},
                {data: 'associate'},
                {data: 'emi_amount'},
                {data: 'mode'},
                {data: 'due_emi'}   
            ],
            "ordering": false,
        });
        $(bounce_ecs_current_status_listing.table().container()).removeClass('form-inline');

        
        $(document).on('click', '#reset_form', function() {
            $('#branch').val('0');
            $('#date').val('');
            $('#company_id').val('0');
            bounce_ecs_current_status_listing.draw();
        });

        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#bounce_ecs_current_status_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#bounce_ecs_current_status_export').val(extension);
                $('form#filter').attr('action', "{!! route('admin.loan.ecs.bounce_charge.status.listing.export') !!}");
                $('form#filter').submit();
            }
        });
    });

    
    function searchForm() {
        if ($('#filter').valid()) {
            $('#bounce_ecs_current_status_export').val('1');
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            bounce_ecs_current_status_listing.draw();
        }
    }

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#branch').val('0');
        $('#date').val('');
        $('#company_id').val('0');
        $(".table-section").addClass("hideTableData");
        bounce_ecs_current_status_listing.draw();
    }

    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.loan.ecs.bounce_charge.status.listing.export') !!}",
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
