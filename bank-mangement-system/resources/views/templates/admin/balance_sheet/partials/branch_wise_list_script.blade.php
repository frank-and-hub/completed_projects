<script type="text/javascript">
    $('document').ready(function() {
		$(document).on('change', '#company_id', function() {
            $('#company').val($('#company_id').val());
        });
        $('#start_date').datepicker({
            //format: "dd/mm/yyyy",
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
        })

        $('#ends_date').datepicker({
            //format: "dd/mm/yyyy",
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
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

        $('#filter').validate({
            rules: {
                start_date: {
                    // required: true,
                    dateDdMm: true,
                },
                /* branch :{
                  required: true,
                 },*/

            },
            messages: {
                start_date: {
                    required: "Please enter date.",
                },
                branch: {
                    required: 'Please select branch.'
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

        // $('.submit').on('click',function(){
        //   location.reload();
        // })
        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });


        detailList = $('#detailList').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback": function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings();

                $('html, body').stop().animate({

                    scrollTop: ($('#detailList').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);

                return nRow;

            },

            ajax: {

                "url": "{!! route($route) !!}",

                "type": "POST",

                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.head = $('#head').val(),
                        d.label = $('#label').val(),
						d.company_id = $('#company').val(),
                        d.date = $('#date_filter').val(),
                        d.end_date = $('#ends_date_filter').val(),
                        d.branch = $('#branch_filter').val()
                },

            },
            "columnDefs": [{
                "render": function(data, type, full, meta) {

                    return meta.row + 1; // adds id to serial no
                },
                "targets": 0,

            }],
            columns: [
                @foreach ($array as $key => $val)
                    {
                        data: '{{ $val }}',
                        name: '{{ $val }}'
                    },
                @endforeach
            ],"ordering": false,
        });

        $(detailList.table().container()).removeClass('form-inline');




        $('.export_report').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.export') !!}");
            $('form#filter').submit();
            return true;
        });
        $('.export_deposite').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.deposite.export') !!}");
            $('form#filter').submit();
            return true;
        });


        $('.exportTds').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.tds.export') !!}");
            $('form#filter').submit();
            return true;
        });


        $('.exportCaseInHand').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.case_in_hand.export') !!}");
            $('form#filter').submit();
            return true;
        });


        $('.exportAdvancePayment').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.advance_payment.export') !!}");
            $('form#filter').submit();
            return true;
        });

        $('.exportMembership').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.membership.export') !!}");
            $('form#filter').submit();
            return true;
        });

        $('.exportSaving').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.saving.export') !!}");
            $('form#filter').submit();
            return true;
        });




        $('.exportFixedAssets').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet_fixedAssets_export') !!}");
            $('form#filter').submit();
            return true;
        });








        // ...........................................Rent Creditors report Start ...........................//

        rentCrediorsList = $('#rentCreditorsList').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#rentCreditorsList').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_rent_creditors_report_listing') !!}",
                "type": "POST",
                "data": function(d, oSettings) {
                    let totalAmount;
                    if (oSettings.json != null) {

                        totalAmount = oSettings.json.total;
                    } else {
                        totalAmount = 0;
                    }
                    var page = ($('#rentCreditorsList').DataTable().page.info());
                    var currentPage = page.page + 1;
                    d.pages = currentPage,
                        d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.branch = $('#branch_filter').val(),
                        d.head_id = $('#head_id').val(),
						d.company_id = $('#company').val(),
                        d.end_date = $('#ends_date').val(),
                        d.total = totalAmount
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
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'owner_name',
                    name: 'owner_name'
                },
                {
                    data: 'rent_type',
                    name: 'rent_type'
                },
                /*{data: 'rent_amount', name: 'rent_amount'},*/
                //{data: 'transfer_amount', name: 'transfer_amount'},
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
            ],"ordering": false,
        });





        $('.export_report_rent').on('click', function() {
            var extension = $(this).attr('data-extension');
            $('#export').val(extension);
            $('form#filter').attr('action', "{!! route('admin.balance_sheet.branch_wise.rent.export') !!}");
            $('form#filter').submit();
            return true;
        });


        // ...........................................Salary Creditors report Start ...........................//

        salaryCrediorsList = $('#salaryCreditorsList').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#salaryCreditorsList').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_salary_creditors_report_listing') !!}",
                "type": "POST",
                "data": function(d, oSettings) {
                    let totalAmount;
                    if (oSettings.json != null) {

                        totalAmount = oSettings.json.total;
                    } else {
                        totalAmount = 0;
                    }
                    var page = ($('#salaryCreditorsList').DataTable().page.info());
                    var currentPage = page.page + 1;
                    d.pages = currentPage,
                        d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.branch = $('#branch_filter').val(),
                        d.head_id = $('#head_id').val(),
						d.company_id = $('#company').val(),
                        d.end_date = $('#ends_date').val(),
                        d.total = totalAmount
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
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'owner_name',
                    name: 'owner_name'
                },
                {
                    data: 'employee_code',
                    name: 'employee_code'
                },
                /*{data: 'rent_amount', name: 'rent_amount'},*/
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
            ],"ordering": false,
        });






        // ...........................................CASH IN HAND Creditors report Start ...........................//

        caseinhandCreditorsList = $('#caseinhandCreditorsList').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#caseinhandCreditorsList').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_case_in_hand_report_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.end_date = $('#ends_date').val(),
                        d.branch = $('#branch_filter').val(),
						d.company_id = $('#company').val(),
                        d.head_id = $('#head_id').val(),
                        d.total = 0
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            "createdRow": function(row, data, dataIndex) {
                console.log(dataIndex);

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
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'transaction_type',
                    name: 'transaction_type'
                },
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
            ],"ordering": false,
        });



        function searchCashInHandCreditorsForm() {
            caseinhandCreditorsList.ajax.reload();
        }

        function resetCashInHandCreditorsForm() {
            // $('#start_date').val("");
            location.reload();
            caseinhandCreditorsList.ajax.reload();
        }



        // ...........................................FIXED ASSETS Creditors report Start ...........................//

        fixedAssetsCreditorsList = $('#fixedAssetsCreditorsList').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#fixedAssetsCreditorsList').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_fixed_assets_report_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
						d.company_id = $('#company').val(),
                        d.end_date = $('#ends_date').val(),
                        d.branch = $('#branch_filter').val(),
                        d.head_id = $('#head_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                // {data: 'assets_category', name: 'assets_category'},
                // {data: 'assets_subcategory', name: 'assets_subcategory'},
                {
                    data: 'party_name',
                    name: 'party_name'
                },
                // {data: 'mobile_number', name: 'mobile_number'},
                {
                    data: 'voucher_number',
                    name: 'voucher_number'
                },
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },

                {
                    data: 'amount',
                    name: 'amount'
                },
            ],"ordering": false,
        });









        // ...........................................ADVANCE PAYMENT Creditors report Start ...........................//

        advancePaymentCreditorsList = $('#advancePaymentCreditorsList').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#advancePaymentCreditorsList').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_advance_payment_report_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.end_date = $('#ends_date').val(),
                        d.branch = $('#branch_filter').val(),
						d.company_id = $('#company').val(),
                        d.head_id = $('#head_id').val()
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
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
            ],"ordering": false,
        });





        // ...........................................MemberShip report Start ...........................//

        member_ship = $('#member_ship').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({

                    scrollTop: ($('#member_ship').offset().top)

                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_member_ship_report_data') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.branch = $('#branch_filter').val(),
                        d.date = $('#date').val(),
                        d.end_date = $('#ends_date').val(),
						d.company_id = $('#company').val(),
                        d.head_id = $('#head_id').val()

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
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'credit',
                    name: 'credit'
                },
                {
                    data: 'debit',
                    name: 'debit'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
            ],"ordering": false,
        });



        function searchmember_shipForm() {
            $('#is_search').val("yes");


            var is_search = $('#is_search').val();
            var start_date = $('#start_date').val();
            var end_date = $('#ends_date').val();
            var queryParams = new URLSearchParams(window.location.search);

            // Set new or modify existing parameter value.
            queryParams.set("date", start_date);
            queryParams.set("end_date", end_date);

            // Replace current querystring with the new one.
            window.location.href =
                "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/membership_fee?" +
                queryParams;
        }

        function resetmember_shipForm() {
            location.reload();

            $('#is_search').val("no");
            $('#branch').val("");
            $('#start_date').val("");
            var branch = $('#branch').val();
            var is_search = $('#is_search').val();
            var start_date = $('#start_date').val();
            var end_date = $('#ends_date').val();
            var queryParams = new URLSearchParams(window.location.search);

            // Set new or modify existing parameter value.
            queryParams.set("date", start_date);
            queryParams.set("end_date", end_date);

            // Replace current querystring with the new one.
            window.location.href =
                "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/membership_fee?" +
                queryParams;
        }

        // ...........................................Fixed Deposite report Start ...........................//
        fixed_deposit = $('#fixed_deposit').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#fixed_deposit').offset().top)
                }, 10);
                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.balance-sheet.get_fixed_deposite_report_data') !!}",
                "type": "POST",
                "data": function(d, oSettings) {
                    let totalAmount;
                    if (oSettings.json != null) {

                        totalAmount = oSettings.json.total;
                    } else {
                        totalAmount = 0;
                    }
                    var page = ($('#fixed_deposit').DataTable().page.info());
                    var currentPage = page.page + 1;
                    d.pages = currentPage,
                        d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.branch = $('#branch_filter').val(),
                        d.end_date = $('#ends_date').val(),
                        d.head_id = $('#head_id').val(),
                        d.info = $('#head_no').val(),
                        d.total = totalAmount
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
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
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
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
            ]
        });




        // ...........................................Tds report Start ...........................//

        
        // ...........................................Saving report Start ...........................//

        saving = $('#saving_report').DataTable({

            processing: true,

            serverSide: true,

            pageLength: 20,

            lengthMenu: [10, 20, 40, 50, 100],

            "fnRowCallback": function(nRow, aData, iDisplayIndex) {

                var oSettings = this.fnSettings();

                $('html, body').stop().animate({

                    scrollTop: ($('#saving_report').offset().top)

                }, 10);

                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);

                return nRow;

            },

            ajax: {
                "url": "{!! route('admin.balance-sheet.saving_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray(),
                        d.date = $('#start_date').val(),
                        d.branch = $('#branch_filter').val(),
                        d.end_date = $('#ends_date').val(),
						d.company_id = $('#company').val(),
                        d.head_id = $('#head_id').val()
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
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
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
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'cr',
                    name: 'cr'
                },
                {
                    data: 'dr',
                    name: 'dr'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
            ],"ordering": false,


        });

        $(saving.table().container()).removeClass('form-inline');




    });

    function searchFixedAsstesCreditorsForm() {
        fixedAssetsCreditorsList.ajax.reload();
    }

    function resetFixedAsstesCreditorsForm() {
        location.reload();
        fixedAssetsCreditorsList.ajax.reload();
    }

    function searchForm() {

        $('#is_search').val("yes");

        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#start_date').val();
        var end_date = $('#ends_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("branch", branch);
        queryParams.set("end_date", end_date);

        // Replace current querystring with the new one.
        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/{{ $head }}/{{ $label }}?" +
            queryParams;

    }

    function resetForm() {
        location.reload();
        $('#is_search').val("no");
        $('#branch').val("");
        $('#start_date').val("");
        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#default_date').val();
        var end_date = $('#default_end_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("end_date", end_date);
        queryParams.set("branch", branch);

        // Replace current querystring with the new one.
        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/{{ $head }}/{{ $label }}?" +
            queryParams;
    }
    // rENT cREDITOR
    function searchRentCreditorsForm() {
        $('#is_search').val("yes");

        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#start_date').val();
        var end_date = $('#ends_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("branch", branch);
        queryParams.set("end_date", end_date);

        // Replace current querystring with the new one.
        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/rent_creditors?" + queryParams;
    }

    function resetRentCreditorsForm(e) {
        location.reload();
        $('#is_search').val("no");
        $('#branch').val("");
        $('#start_date').val("");
        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#default_date').val();
        var end_date = $('#default_end_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("end_date", end_date);
        queryParams.set("branch", branch);

        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/rent_creditors?" + queryParams;
    }



    function searchsavingForm() {
        saving.ajax.reload();

    }

    function resetsavingForm() {
        location.reload();
        saving.ajax.reload();
    }


    // fIXED dEPOSITE

    function searchfixed_depositForm() {
        $('#is_search').val("yes");

        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#start_date').val();
        var end_date = $('#ends_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("branch", branch);
        queryParams.set("end_date", end_date);

        // Replace current querystring with the new one.
        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/fixed_deposite?" + queryParams;

    }

    function resetfixed_depositForm() {
        location.reload();
        $('#is_search').val("no");
        $('#branch').val("");
        $('#start_date').val("");
        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#default_date').val();
        var end_date = $('#default_end_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("end_date", end_date);
        queryParams.set("branch", branch);

        // Replace current querystring with the new one.
        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/fixed_deposite?" + queryParams;
    }


    // Salary Creditor
    function searchsalartCreditorsForm() {
        $('#is_search').val("yes");

        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#start_date').val();
        var end_date = $('#ends_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("branch", branch);
        queryParams.set("end_date", end_date);

        // Replace current querystring with the new one.
        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/salary_creditors?" + queryParams;
    }


    function resetSalaryCreditorsForm() {
        location.reload();
        $('#is_search').val("no");
        $('#branch').val("");
        $('#start_date').val("");
        var branch = $('#branch').val();
        var is_search = $('#is_search').val();
        var start_date = $('#default_date').val();
        var end_date = $('#default_end_date').val();
        var queryParams = new URLSearchParams(window.location.search);

        // Set new or modify existing parameter value.
        queryParams.set("date", start_date);
        queryParams.set("end_date", end_date);
        queryParams.set("branch", branch);

        window.location.href =
            "{{ url('/') }}/admin/balance-sheet/current_liability/branch_wise/salary_creditors?" + queryParams;
    }


    $('#financial_year').on('change', function() {

        var financialYear = $(this).find('option:selected').val();
        var year = financialYear.split(' - ');

        const d = new Date();
        let curryear = d.getFullYear();

        var minDate = "01/04/" + year[0];
        var startDate = '01/04/' + year[0];
        var endDate = '31/03/' + year[1];
        $('#start_date').val(minDate);
        if (year[1] <= curryear) {
            var maxDate = "31/03/" + year[1];
            $('#ends_date').val(maxDate);
        } else {
            var month = d.getMonth() + 1; // Months start at 0!
            var day = d.getDate();
            var maxDate = day + '/' + month + '/' + curryear;

            $('#ends_date').val(maxDate);
        }
        $("#start_date").datepicker('remove');
        $('#start_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            autoclose: true,
            startDate: startDate,
            endDate: maxDate,
            setDate: new Date()
        });
        $("#ends_date").datepicker('remove');
        $('#ends_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom auto",
            autoclose: true,
            startDate: startDate,
            endDate: maxDate,
            setDate: new Date()
        });
        console.log("TT", minDate, maxDate, curryear, startDate, endDate);


        var headList = $("#filter_data").find("a");
        headList.each(function(index) {
            var link = $(this).attr('href');
            console.log(index + ": " + $(this).attr('href'));
            $(this).attr('href', link + '&financial_year=' + financialYear);
        });
        console.log("AA", headList);
        console.log("TT", minDate, maxDate, curryear, startDate, endDate);
    });
</script>
