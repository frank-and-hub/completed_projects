<script type="text/javascript">
    var loanFromBankListing;
    var loan_from_bank_ledger_listing;
    $(document).ready(function() {
        $("#start_date1").hover(function() {
            $('#start_date1').datepicker({
                format: "dd/mm/yyyy",
                startHighlight: true,
                autoclose: true,
                endDate: $('#create_application_date').val(),
                startDate: '01/04/2021',
            })
        })
        $("#end_date1").hover(function() {
            $('#end_date1').datepicker({
                format: "dd/mm/yyyy",
                startHighlight: true,
                autoclose: true,
                startDate: $('#start_date1').val(),
                endDate: $('#create_application_date').val(),
                startDate: '01/04/2021',
            })
        })
        $('#date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
            startDate: '01/04/2021',
        })
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
            startDate: '01/04/2021',
        })
        $('#end_date').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            autoclose: true,
            startDate: '01/04/2021',
        })
        var selected_account = $('#selectedOption').val();
        $.validator.addMethod("decimal", function(value, element, p) {
            if (this.optional(element) || $.isNumeric(value) == true)
            {
                $.validator.messages.decimal = "";
                result = true;
            } else {
                $.validator.messages.decimal = "Please enter valid numeric number.";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("bchk", function(value, element, p) {
            emi_principal_amount = $('#emi_principal_amount').val();
            current_balance = $('#current_loan_amount').val();
            if (parseFloat(emi_principal_amount) <= parseFloat(current_balance))
            {
                $.validator.messages.bchk = "";
                result = true;
            } else
            {
                $.validator.messages.bchk = "Amount Should be less than or equal to current balance";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("dateCheck", function(value, element, p) {
            startDate = $('#start_date').val();
            endDate = $('#end_date').val();
            if (startDate <= endDate)
            {
                $.validator.messages.dateCheck = "";
                result = true;
            } else
            {
                $.validator.messages.dateCheck = "End Date Should be valid";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("zero", function(value, element, p) {
            if (value >= 0)
            {
                $.validator.messages.zero = "";
                result = true;
            } else {
                $.validator.messages.zero = "Amount must be greater than 0.";
                result = false;
            }
            return result;
        }, "");
        $('#filter').validate({
            rules: {
                loanAccount: {
                    required: true,
                },
                company_id: {
                    required: true,
                }
            }
        })
        $('#loan_from_bank').validate({
            rules: {
                bank_name: {
                    required: true,
                },
                branch_name: {
                    required: true,
                },
                loan_amount: {
                    required: true,
                    number: true,
                    decimal: true,
                    zero: true,
                },
                remark: {
                    required: true,
                },
                loan_account_number: {
                    required: true,
                    number: true,
                    decimal: true,
                    zero: true,
                },
                loan_interest_rate: {
                    required: true,
                    number: true,
                    decimal: true,
                    zero: true,
                },
                no_of_emi: {
                    required: true,
                    number: true,
                    zero: true,
                },
                received_bank_name: {
                    required: true,
                },
                received_bank_account: {
                    required: true,
                },
                address: {
                    required: true,
                },
                emi_amount: {
                    bchkLoan: true,
                    required: true,
                },
                start_date: {
                    required: true,
                },
                end_date: {
                    required: true,
                    dateCheck: true,
                }
            },
            messages: {
                bank_name:
                {
                    "required": "Please enter bank name.",
                },
                branch_name: {
                    "required": "Please enter branch name.",
                },
                loan_amount: {
                    "required": "Please enter loan amount.",
                },
                remark: {
                    "required": "Please enter remark.",
                },
                loan_account_number: {
                    "required": "Please enter account number.",
                },
                loan_interest_rate: {
                    "required": "Please enter interest rate.",
                },
                no_of_emi: {
                    "required": "Please enter number of emi.",
                },
                received_bank_name: {
                    "required": "Please enter received bank name."
                },
                received_bank_account: {
                    "required": "Please enter received bank account."
                },
                address: {
                    "required": "Please enter address."
                },
                emi_amount: {
                    "required": "Please enter emi amount."
                },
                start_date: {
                    "required": "Please enter from date."
                },
                end_date: {
                    "required": "Please enter end date."
                }
            }
        })
        $('#loan_emi').validate({
            rules: {
                loan_account_number: {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 16
                },
                bank_name: {
                    required: true,
                },
                date: {
                    required: true,
                },
                emi_number: {
                    required: true,
                },
                emi_principal_amount: {
                    required: true,
                    number: true,
                    decimal: true,
                    zero: true,
                    bchk: true,
                },
                emi_interest_rate: {
                    required: true,
                    number: true,
                    decimal: true,
                    zero: true,
                },
                loan_amount: {
                    required: true,
                    number: true,
                    decimal: true,
                    zero: true,
                },
                received_bank_name: {
                    required: true,
                },
                received_bank_account: {
                    required: true,
                },
                current_loan_amount: {
                    required: true,
                },
            },
            messages: {
                bank_name:
                {
                    "required": "Please enter bank name.",
                },
                branch_name: {
                    "required": "Please enter branch name.",
                },
                emi_principal_amount: {
                    "required": "Please enter principal emi amount.",
                },
                loan_amount: {
                    "required": "Please enter loan amount.",
                },
                loan_account_number: {
                    "required": "Please enter account number.",
                },
                emi_interest_rate: {
                    "required": "Please enter interest rate.",
                },
                emi_number: {
                    "required": "Please enter emi number.",
                },
                received_bank_name: {
                    "required": "Please enter received bank name."
                },
                received_bank_account: {
                    "required": "Please enter received bank account."
                },
                current_loan_amount: {
                    "required": "Please enter current loan  amount.",
                }
            }
        })
        //  $(document).on('change','#received_bank_name', function () {
        //     var account = $('option:selected', this).val();
        //     $('#from_Bank_account_no').val('');
        //     $('.from-bank-account').hide();
        //     $('.'+account+'-from-bank-account').show();
        // });
        $('#company_id').on('change', function() {
            var company_id = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.bank_account_no') !!}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loanAccount').find('option').remove();
                    $('#loanAccount').append('<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#loanAccount").append("<option value='" + value.id + "'>" + value.loan_account_number + "</option>");
                    });
                }
            });
        })
        $('#received_bank_name').on('change', function(selected_account) {
            var bank_id = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.bank_account_list') !!}",
                dataType: 'JSON',
                data: {
                    'bank_id': bank_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#received_bank_account').find('option').remove();
                    $('#received_bank_account').append('<option value="">Select account number</option>');
                    $.each(response.account, function(index, value) {
                        $("#received_bank_account").append("<option value='" + value.id + "'>" + value.account_no + "</option>");
                    });
                }
            });
        })
        // $('#loan_account_number').on('change',function(){
        //     var loan_account_number_id=$(this).val();
        //   $.ajax({
        //       type: "POST",  
        //       url: "{!! route('admin.get_loan_account_detail') !!}",
        //       dataType: 'JSON',
        //       data: {'id':loan_account_number_id},
        //       headers: {
        //           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //       },
        //       success: function(response) { 
        //           $('#bank_name').val(response.loanData.bank_name);
        //           $('#loan_amount').val(parseFloat(response.loanData.loan_amount).toFixed(2));
        //           $('#current_loan_amount').val(parseFloat(response.loanData.current_balance).toFixed(2));
        //           $('#account_head_id').val(response.loanData.account_head_id);
        //           $('#loan_from_bank_id').val(response.loanData.id);
        //       }
        //   });
        // })
        loanEmiListing = $('#loan_emi_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#loan_emi_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan_from_bank.loan_emi.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
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
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'bank_account',
                    name: 'bank_account'
                },
				{
                    data: 'loan_amount',
                    name: 'loan_amount'
                },
                {
                    data: 'emi_date',
                    name: 'emi_date'
                },
                {
                    data: 'emi_number',
                    name: 'emi_number'
                },
				{
                    data:'new_emi_amount',
                    name:'new_emi_amount'
                },
                {
                    data: 'emi_principal',
                    name: 'emi_principal'
                },
                {
                    data: 'emi_interest_rate',
                    name: 'emi_interest_rate'
                },
                {
                    data: 'received_bank',
                    name: 'received_bank'
                },
                {
                    data: 'received_bank_account',
                    name: 'received_bank_account'
                },
            ],"ordering": false,
        });
        $(loanEmiListing.table().container()).removeClass('form-inline');
        loanFromBankListing = $('#loan_from_bank_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#loan_from_bank_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan_from_bank.report.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'loan_type',
                    name: 'loan_type'
                },
                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'branch_name',
                    name: 'branch_name'
                },
                {
                    data: 'loan_amount',
                    name: 'loan_amount'
                },
                {
                    data: 'current_balance',
                    name: 'current_balance'
                },
                {
                    data: 'loan_account_number',
                    name: 'loan_account_number'
                },
                {
                    data: 'loan_interest_rate',
                    name: 'loan_interest_rate'
                },
                {
                    data: 'number_of_emi',
                    name: 'number_of_emi'
                },
                {
                    data: 'received_type',
                    name: 'received_type'
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
                    data: 'vendor_name',
                    name: 'vendor_name'
                },
                {
                    data: 'remark',
                    name: 'remark'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],"ordering": false,
        });
        $(loanFromBankListing.table().container()).removeClass('form-inline');
        loan_from_bank_ledger_listing = $('#loan_from_bank_ledger_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#loan_from_bank_ledger_listing').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loanFromBank.ledger_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter_ledger').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'amount1',
                    name: 'amount1'
                },
                {
                    data: 'interest',
                    name: 'interest'
                },
                {
                    data: 'amount2',
                    name: 'amount2'
                },
                {
                    data: 'payment_type',
                    name: 'payment_type'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'received_bank',
                    name: 'received_bank'
                },
                {
                    data: 'received_bank_account',
                    name: 'received_bank_account'
                },
            ],"ordering": false,
        });
        $(loan_from_bank_ledger_listing.table().container()).removeClass('form-inline');
        // $('.ledger_export').on('click', function() {
        //     var extension = $(this).attr('data-extension');
        //     $('#ledger_export').val(extension);
        //     $('form#filter_ledger').attr('action', "{!! route('admin.loanFromBank.ledger_listing_export') !!}");
        //     $('form#filter_ledger').submit();
        //     return true;
        // });
        $('.ledger_export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            //$('#report_export').val(extension);
            var formData = {}
            // formData['start_date'] = jQuery('#start_date').val();
            // formData['end_date'] = jQuery('#end_date').val();
            // formData['plan'] = jQuery('#plan').val();
            // formData['branch_id'] = jQuery('#branch_id').val();
            // formData['status'] = jQuery('#status').val();
            // formData['application_number'] = jQuery('#application_number').val();
            // formData['member_id'] = jQuery('#member_id').val();
            // formData['is_search'] = jQuery('#is_search').val();
            // formData['export'] = jQuery('#export').val();
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
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
                url: "{!! route('admin.loanFromBank.ledger_listing_export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
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
        $('.loan_export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = {}
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExportl(0, chunkAndLimit, formData, chunkAndLimit, 1);
            $("#cover").fadeIn(100);
        });
        // function to trigger the ajax bit
        function doChunkedExportl(start, limit, formData, chunkSize, page) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loanFromBank.loan_listing_export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExportl(start, limit, formData, chunkSize, page);
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
        $('.loan_emi_export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            var formData = {}
            var chunkAndLimit = 50;
            $(".spiners").css("display", "block");
            $(".loaders").text("0%");
            doChunkedExporte(0, chunkAndLimit, formData, chunkAndLimit, 1);
            $("#cover").fadeIn(100);
        });
        // function to trigger the ajax bit
        function doChunkedExporte(start, limit, formData, chunkSize, page) {
            formData['start'] = start;
            formData['limit'] = limit;
            formData['page'] = page;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loanFromBank.loan_emi_listing_export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        page = page + 1;
                        doChunkedExporte(start, limit, formData, chunkSize, page);
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
        $.validator.addMethod("bchkLoan", function(value, element, p) {
            loan_amount = $('#loan_amount').val();
            emi_amount = $('#emi_amount').val();
            if (parseFloat(emi_amount) < parseFloat(loan_amount)) {
                $.validator.messages.bchkLoan = "";
                result = true;
            } else {
                $.validator.messages.bchkLoan = "Emi amount should be less than loan amount.";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("dateCheck", function(value, element, p) {
            startDate = $('#start_date').val();
            endDate = $('#end_date').val();
            if (startDate < endDate) {
                $.validator.messages.dateCheck = "";
                result = true;
            } else {
                $.validator.messages.dateCheck = "End date must be greater than start date.";
                result = false;
            }
            return result;
        }, "");
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        $(document).on('click', '.delete_expense', function() {
            var expense_id = $(this).attr("data-row-id");
            swal({
                    title: "Are you sure?",
                    text: "Do you want to delete this Loan From Bank?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: "POST",
                            url: "{!! route('admin.loanFromBank.delete') !!}",
                            dataType: 'JSON',
                            data: {
                                'id': expense_id
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.status == "1") {
                                    swal("Good job!", response.message, "success");
                                    location.reload();
                                } else {
                                    swal("Warning!", response.message, "warning");
                                    return false;
                                }
                            }
                        });
                    }
                });
        })
    });
    function statusUpdate(headId)
    {
        swal({
                title: "Are you sure?",
                text: "Do want to Change Status?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-primary",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                cancelButtonClass: "btn-danger",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!!route('admin.update.status.loan_from_bank')!!}",
                        dataType: 'JSON',
                        data: {
                            'head_id': headId
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response)
                            {
                                loanFromBankListing.draw();
                                swal("Success", "Update Status successfully!", "success");
                            } else
                            {
                                swal("Error", "Something went wrong.Try again!", "warning");
                            }
                        }
                    });
                }
            });
    }
    function searchForm()
    {
        if ($('#filter').valid())
        {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            loanEmiListing.draw();
        }
    }
    function searchLoanForm()
    {
        if ($('#filter').valid())
        {
            $('#is_search').val("yes");
            $(".loan_list_report").removeClass('d-none');
            loanFromBankListing.draw();
        }
    }
    function resetForm()
    {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        $("#filter")[0].reset();
        form.find(".error").removeClass("error");
        $(".table-section").addClass("hideTableData");
        loanEmiListing.draw();
    }
    function resetLoanForm()
    {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        $("#filter")[0].reset();
        form.find(".error").removeClass("error");
        $(".table-section").addClass("hideTableData");
        $(".loan_list_report").addClass("d-none");
        loanFromBankListing.draw();
    }
    function searchForm_ledger() {
        loan_from_bank_ledger_listing.draw();
    }
    function resetForm_ledger() {
        var date = $('#start_date1').val();
        $('#start_date1').val(date);
        $('#end_date1').val($('#create_application_date').val());
        loan_from_bank_ledger_listing.draw();
    }
</script>