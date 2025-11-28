<script type="text/javascript">
    var voucher_listing;
    $(document).ready(function() {
        var date = new Date();
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });

        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            endDate: date,
            autoclose: true
        });
        voucher_listing = $('#voucher_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#voucher_listing').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.voucher.list') !!}",
                "type": "POST",
                "data": function(d) {

                    d.searchform = $('form#filter').serializeArray(),
                        d.month = $('#month').val(),
                        d.year = $('#year').val(),
                        d.status = $('#status').val(),
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
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'rv_mode',
                    name: 'rv_mode'
                },
                {
                    data: 'rv_amount',
                    name: 'rv_amount'
                },
                {
                    data: 'account_head',
                    name: 'account_head'
                },
                {
                    data: 'employee_code',
                    name: 'employee_code'
                },
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'bank_account_number',
                    name: 'bank_account_number'
                },
                {
                    data: 'eli_loan',
                    name: 'eli_loan'
                },
                {
                    data: 'cheque_no',
                    name: 'cheque_no'
                },
                {
                    data: 'utr_transaction_number',
                    name: 'utr_transaction_number'
                },
                {
                    data: 'transaction_date',
                    name: 'transaction_date'
                },
                {
                    data: 'party_bank_name',
                    name: 'party_bank_name'
                },
                {
                    data: 'party_bank_account',
                    name: 'party_bank_account'
                },
                {
                    data: 'received_bank',
                    name: 'received_bank'
                },
                {
                    data: 'received_bank_account',
                    name: 'received_bank_account'
                },
                {
                    data: 'bank_slip',
                    name: 'bank_slip'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        $(voucher_listing.table().container()).removeClass('form-inline');
        //Export button code start 
        $('.export').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            var formData = jQuery('#filter').serializeObject();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
            $("#cover").fadeIn(100);
        });
        //Export button code end


        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('branch.voucher.exportList') !!}",
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

        // A function to turn all form data into a jquery object start
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
        // A function to turn all form data into a jquery object end
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });


        $('#filter').validate({
            rules: {
                company_id: "required",

            },
            messages: {
                company_id: "Please select Company name",
            },

            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass(' ');
                element.closest('.error-msg').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(
                            errorClass);
                        $(this).addClass('is-invalid');
                    });
                }
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
                if ($(element).attr('type') == 'radio') {
                    $(element.form).find("input[type=radio]").each(function(which) {
                        $(element.form).find("label[for=" + this.id + "]").addClass(
                            errorClass);
                        $(this).removeClass('is-invalid');
                    });
                }
            }
        });





    });

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $('#table_voucher_listing').show();
            voucher_listing.draw();
        }
    }

    function resetForm() {
        $('#start_date').val('');
        $('#end_date').val('');
        $('#company_id').val('0');
        $('#payment_type').val('');
        $('#account_head').val('');
        $('#is_search').val('no');
        $('#table_voucher_listing').hide();
        voucher_listing.draw();
    }
</script>
