<script type="text/javascript">
    'use strict';
    $('#head_type').on('change', function(event) {
        event.preventDefault();
        formbody();
    });

    var tdsPayableListing;
    var tdstransferListing;

    $(document).on('click', '.download_data', function() {
        var path = $(this).data('path');
        var name = $(this).data('name');
        // Send the POST request
        $.ajax({
            url: `{{ route('admin.tds_payable_chalan.download') }}`,
            method: 'POST',
            data: {
                'name': name,
                'path': path
            },
            xhrFields: {
                eType: 'blob'
            },
            success: function(data, status, xhr) {
                var blob = new Blob([data], {
                    type: xhr.geteHeader('content-type')
                });
                var url = URL.createObjectURL(blob);

                var link = document.createElement('a');
                link.href = url;
                link.download = name;

                document.body.appendChild(link);
                link.click();

                URL.revokeObjectURL(url);
                document.body.removeChild(link);
            },
            error: function(xhr, status, error) {
                console.error('Error downloading file:', error);
            }
        });

        return false;

    });

    $(document).on('click', '.view_data', function() {
        var imageName = $(this).data('name');
        var imagePath = $(this).data('path');
        var imageUrl = "{{ route('admin.tds_payable_chalan.view') }}?image=" + imageName + "&path=" + imagePath;
        window.open(imageUrl, '_blank');
    });

    $(document).ajaxStart(function() {
        $(".loader").show();
    });

    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });

    $(document).ready(function() {

        jQuery.validator.addMethod("greaterThan",
            function(value, element, params) {
                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }
                return isNaN(value) && isNaN($(params).val()) || (Number(value) > Number($(params).val()));
            }, 'Must be greater than {0}.');

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


        jQuery.validator.addMethod("gratedZero", function(value, element, params) {
            var floatValue = parseFloat(value);
            return floatValue > 0;
        }, "values must be greater than zero.");

        $('#tds_transfer_payable_from').validate({ // initialize the plugin
            rules: {
                'payable_start_date': 'required',
                'payable_end_date': 'required',
                'payable_head_id': 'required',
                'payable_tds_amount': {
                    required: true,
                    number: true,
                    gratedZero: true
                },
                'company_id': 'required',
            },
            messages: {
                'payable_start_date': {
                    required: 'Please select a from date.'
                },
                'payable_end_date': {
                    required: 'Please select a to date.'
                },
                'payable_head_id': {
                    required: 'Please select a Transfer Head.'
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

        $('#account_id,#payable_payment_date').on('change', function() {
            bank_available_balance();
        });


        $('#start_date,#end_date,#payable_start_date,#payable_end_date,#transfer_date').hover(function() {
            var created_at = $('#created_at').val();
            $(this).datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                endDate: created_at,
            });
        });

        $('#payable_payment_date').hover(function() {
            var end = new Date($('#payable_end_date').val());
            end.setDate(end.getDate() + 1);
            var start = $('#created_at').val();
            $(this).datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                startDate: end,
                endDate: start,
            });
        });

        tdsPayableListing = $('#payable_listing').DataTable({
            processing: true,
            searching: false,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('form#payable_filter').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.duties_taxes.payable_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#payable_filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex'},
                {data: 'payable_head_type'},
                {data: 'company'},
                {data: 'head'},
                {data: 'payable_amount'},
                {data: 'payament_date'},
                {data: 'bank_name'},
                {data: 'bank_account'},
                {data: 'late_penalty'},
                {data: 'total_paid_amount'},
                {data: 'transaction_number'},
                {data: 'neft_charges'},
                {data: 'challan'},
                {data: 'remark'},
            ],
            "ordering": false,
        });
        $(tdsPayableListing.table().container()).removeClass('form-inline');

        $('#bank_id').on('change', function() {
            var bankId = $(this).val();
            $('#account_id').val('');
            $('.bank-account').hide();
            $('.' + bankId + '-bank-account').show();
        });

        $('#payable_filter').validate({
            rules: {
                start_date: {
                    required: true,
                    dateDdMm: true,
                },
                end_date: {
                    dateDdMm: true,
                    required: true,
                    enddatedateDdMm:true,
                },
                company_id: {
                    required: true,
                }
            },
            messages: {
                company_id: {
                    number: 'Please select Company.'
                },
            }
        });

        tdstransferListing = $('#tds_transfer_list').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#tds_filter').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.tds_transfer_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#tds_filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {
                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0
            }],
            columns: [{
                    data: 'DT_RowIndex'
                },
                {
                    data: 'transfer_date'
                },
                {
                    data: 'date_range'
                },
                {
                    data: 'head_name'
                },
                {
                    data: 'head_amount'
                },
                {
                    data: 'penalty_amount'
                },
                {
                    data: 'payment_date'
                },
                {
                    data: 'to_paid'
                },
                {
                    data: 'company'
                },
                {
                    data: 'file'
                },
                {
                    data: 'action'
                }
            ],
            "ordering": false,
        });
        $(tdstransferListing.table().container()).removeClass('form-inline');

        $(document).on('click', '#sfilter', function() {
            tdstransferListing.draw();
        });
        $(document).on('click', '#resetFilter', function() {
            $('#transfer_date').val('');
            $('#tds_head').val('');
            tdstransferListing.draw();
        });

        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#payable_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#tds_filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#payable_export').val(extension);
                $('form#tds_filter').attr('action', "{!! route('admin.duties_taxes.payable_listing_export') !!}");
                $('form#tds_filter').submit();
            }
        });


        $(document).on('keyup', '#payable_late_penalty,#payable_tds_amount', function() {
            // return false;
            let payment_date = parseFloat($('#payable_late_penalty').val());
            let tds_amount = parseFloat($('#payable_tds_amount').val());
            let payable_paid_amount = parseFloat($('#payable_paid_amount').val());
            let total_paid_amount = (tds_amount + (payment_date ?? 0));
            $('#total_paid_amount').val(total_paid_amount ? total_paid_amount.toFixed(2) : 0.00);

            const bank_available_balance = $('#bank_available_balance').val();
            if (tds_amount > payable_paid_amount) {
                swal('Warning!', "Amount Equal or less then " + payable_paid_amount.toFixed(2),
                    'warning');
                $('#payable_tds_amount').val(payable_paid_amount.toFixed(2));
                $('#total_paid_amount').val(payable_paid_amount.toFixed(2));
                return false;
            }
            if (!(bank_available_balance) && (bank_available_balance == 0)) {
                swal('Warning!', 'please select bank and Account first !', 'warning');
                $('#payable_late_penalty').val('0.00');
                return false;
            } else {
                if (bank_available_balance < total_paid_amount) {
                    swal('Warning!', 'Total Paid Amount sould less then Bank Available Balance',
                        'warning');
                    $('#payable_late_penalty').val('0.00');
                }
            }
        });

    });

    function bank_available_balance() {
        var accountId = $('#account_id').val();
        var bankId = $('#bank_id').val();
        var companyId = $('#company_id').val();
        var payable_payment_date = $('#payable_payment_date').val();
        $.ajax({
            url: `{{ route('admin.get_bank_balance') }}`,
            method: 'POST',
            data: {
                'account_id': accountId,
                'bank_id': bankId,
                'company_id': companyId,
                'entry_date': payable_payment_date
            },
            success: function(data, status, xhr) {
                $('#bank_available_balance').val(data);
            },
            error: function(xhr, status, error) {
                console.log('Error downloading file:', error);
            }
        });
    }

    function searchForm() {
        if ($('#payable_filter').valid()) {
            $('#payable_export').val('1');
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            tdsPayableListing.draw();
        }
    }

    function resetForm() {
        var form = $("#payable_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#payable_export').val('0');
        $('#is_search').val("no");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#head_type').val('');
        $('#head_id').empty();
        $('#head_id').append('<option value="">---- Please Select ----</option>');
        $('#company_id').val('0');
        $(".table-section").addClass("hideTableData");
        tdsPayableListing.draw();
    }

    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.duties_taxes.payable_listing_export') !!}",
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
    
    function formbody() {
        var head_type = $('#head_type').val();
        $('.appendDate').html('');
        $('#form_body').show();
        if (head_type == '') {
            $('#head_id').empty();
            $('#head_id').append('<option value="">---- Please Select ----</option>');
        } else {
            var _token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "POST",
                url: "{!! route('admin.gettdspayable.head_type') !!}",
                dataType: 'JSON',
                data: {
                    'head_type': head_type,
                },
                success: function(res) {
                    $('#head_id').empty();
                    $('#head_id').append('<option value="">---- Please Select ----</option>');
                    for (var key in res.tdsHeads) {
                        if (res.tdsHeads.hasOwnProperty(key)) {
                            var optionText = capitalizeFirstLetter(res.tdsHeads[key]);
                            $('#head_id').append('<option value="' + key + '">' + optionText +
                                '</option>');
                        }
                    }
                },
                error: function(e) {
                    console.log(e);
                }
            });
        }
    }

    function capitalizeFirstLetter(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>
