<script type="text/javascript">
    var billTable;
    $(document).ready(function() {
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
        });
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true
        }).on('changeDate', function(e) {
            $('#end_date').datepicker('setStartDate', e.date);
            // $('#end_date').datepicker({
            //     format: "dd/mm/yyyy",
            //     todayHighlight: true,
            //     autoclose: true,
            //     startDate: e.date,
            //     setDate: e.date,
            // });
        });

        $('#start_date').hover(function() {
            let systemData = $('#system_date').val();
            $('#start_date').datepicker('setEndDate', systemData);
            $('#end_date').datepicker('setEndDate', systemData);
        })
        // $('#company_id').on('change', function() {
        //     let company_id = $(this).val();
        //     if (company_id) {
        //         $.ajax({
        //             type: "POST",
        //             url: "{{ route('admin.vendor.companydate') }}",
        //             dataType: 'JSON',
        //             data: {
        //                 'company_id': company_id,
        //             },
        //             success: function(response) {
        //                 $('#start_date').datepicker('setDate', response);
        //                 $('#start_date').datepicker('setStartDate', response);
        //                 $('#end_date').datepicker('setStartDate', response);
        //             }
        //         });
        //     }
        // })

        $('#company_id').on('change', function() {
            let company_id = $(this).val();
            $('#vendor').append('');
            if (company_id) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.bill_management.vendor_bill') }}",
                    dataType: 'JSON',
                    data: {
                        'company_id': company_id,
                    },
                    success: function(e) {
                        $('#vendor').find('option').remove();
                    $('#vendor').append('<option value="">Select vendor</option>');
                    $.each(e.vendors, function(id, value) {
                        $("#vendor").append("<option value='" + id + "'>" +
                            value
                             + "</option>");
                    });
                    }
                });
            }
        })



        billTable = $('#bill_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#bill_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.bill_management.bill_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#bill_form').serializeArray(),
                        d.start_date = $('#start_date').val(),
                        d.end_date = $('#end_date').val(),
                        d.is_search = $('#is_search').val(),
                        d.branch_id = $('#branch').val(),
                        d.status = $('#status').val(),
                        d.vendor = $('#vendor').val(),
                        d.company_id = $('#company_id option:selected').val()
                },
                beforeSend: function() {
                    $('.loader').show();
                },
                complete: function() {
                    $('.loader').hide();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'ref_number',
                    name: 'ref_number'
                },
                {
                    data: 'vendor_name',
                    name: 'vendor_name'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'due_date',
                    name: 'due_date'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'due_balance',
                    name: 'due_balance'
                },
                {
                    data: 'bill_amount',
                    name: 'bill_amount'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],"ordering": false

        });
        $(billTable.table().container()).removeClass('form-inline');
        $(document).on('click', '.export', function() {
            var extension = $(this).attr('data-extension');
            $('#bill_report_export').val(extension);
            $('form#bill_form').attr('action', "{!! route('admin.bill_management.export_bill_listing') !!}");
            $('form#bill_form').submit();
            return true;
        })

        $.validator.addMethod("dateDdMm", function(value, element, p) {

            if (this.optional(element) ||
                /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
                $.validator.messages.dateDdMm = "";
                result = true;
            } else {
                $.validator.messages.dateDdMm = "Please enter valid date";
                result = false;
            }

            return result;
        }, "");

        $('#bill_form').validate({
            rules: {
                
                associate_code: {
                    number: true,
                },
                

            },
            messages: {
                associate_code: {
                    number: 'Please enter valid associate code.'
                },
                
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
        if ($('#bill_form').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            billTable.draw();
        }
    }

    function resetForm() {

        $('#is_search').val("no");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#vendor').val('');
        $('#company_id').val('0').trigger('change');
        $('#status').val('');
        $(".table-section").addClass("hideTableData");
        billTable.draw();
        $('.loader').hide();

    }

    function printDiv(elem) {
        $("#" + elem).print({
            globalStyles: true,
            mediaPrint: true,
            stylesheet: "{{ url('/') }}/asset/print.css",
            iframe: false,
            noPrintSelector: ".avoid-this",
            header: null,
            footer: null,
            deferred: $.Deferred().done(function() {})
        });
    }
    $(document).on('click', '.printBill', function() {
        var bill_id = $(this).attr("data-row-id");
        $.ajax({
            type: "POST",
            url: "{!! route('admin.bill_management.getBillDetails') !!}",
            data: {
                'bill_id': bill_id
            },
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.bill_records.id > 0) {
                    $(".bill_number").text(data.bill_records.bill_number);
                    $(".companyName").text(data.bill_records.company.name);
                    $(".bill_balance_due").text(parseFloat(data.bill_records.balance).toFixed(2));
                    $(".notes").text(data.bill_records.description);
                    $(".dates").text(data.bill_records.bill_date);
                    $(".amount").text(parseFloat(data.bill_records.payble_amount).toFixed(2));
                    $(".branch").text(data.bill_records.branch_name);
                    $(".sub_amount").text(parseFloat(data.bill_records.sub_amount).toFixed(2));
                    if (data.bill_records.transferd_amount > 0 && data.bill_records
                        .transferd_amount != null && data.bill_records.transferd_amount != "") {
                        $(".transfer_amount").text(parseFloat(data.bill_records.transferd_amount)
                            .toFixed(2));
                        $("#transferAmount").css("display", "revert");
                    } else {
                        $("#transferAmount").css("display", "none");
                    }
                    $("#bodyData").html(data.bill_item_html);
                }

            }
        });
    });
</script>
