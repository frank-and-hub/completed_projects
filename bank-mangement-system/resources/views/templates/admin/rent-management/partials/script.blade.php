<script type="text/javascript">
    var rentLiability;


    $(document).ready(function() {

        $(document).on('change', '#company_id', function() {
            $('#owner_ssb_account').val('');
            $('#owner_name').val('');
            $('#ssb_date').val('');
            $('#select_date').val('');
            $('#agreement_from').val('');
            $('#employee_code').val('');
            $('#employee_id').val('');
            $('#employee_name').val('');
            $('#employee_designation').val('');
            $('#mobile_number').val('');
            $('#employee_date').val('');

            var company_id = $('#company_id').val();
            var date = $('#create_application_date').val();
            $.ajax({
                type: "POST",
                url: "{{route('admin.vendor.companydate')}}",
                dataType: 'JSON',
                data: {
                    'company_id': company_id,
                },
                success: function(response) {
                    // $('#select_date').datepicker('setDate', response);
                    // $('#select_date').datepicker('format', "dd/mm/yyyy");
                    $('#agreement_from').datepicker('setStartDate', response);
                    $('#agreement_from').datepicker('format', "dd/mm/yyyy");
                    $('#select_date').datepicker({
                        format: "dd/mm/yyyy",
                        todayHighlight: true,
                        autoclose: true,
                        orientation: "bottom",
                        endDate: date
                    });
                    $('#select_date').datepicker('setStartDate', response);
                }
            });
        })
        if ($('#companyDate').val()) {
            $('#agreement_from').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                orientation: "bottom",
                startDate: $('#companyDate').val(),

            })
        }
        // $("#select_date").hover(function() {
        //     var date = $('#create_application_date').val();
        //     var sdate = $('#companyDate').val();
        //     if (sdate == " ") {
        //         var sdate = '01/04/2021';
        //     }
        //     $('#select_date').datepicker({
        //         format: "dd/mm/yyyy",
        //         todayHighlight: true,
        //         autoclose: true,
        //         orientation: "bottom",
        //         endDate: date,
        //         startDate: sdate,

        //     })
        // })


        $.validator.addMethod("zero", function(value, element, p) {
            if (parseFloat(value) >= 0) {
                $.validator.messages.zero = "";
                result = true;
            } else {
                $.validator.messages.zero = "Amount must be greater than or equal to 0.";
                result = false;
            }

            return result;
        }, "");

        $.validator.addMethod("decimal", function(value, element, p) {
            if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
                $.validator.messages.decimal = "";
                result = true;
            } else {
                $.validator.messages.decimal = "Please enter valid numeric number.";
                result = false;
            }

            return result;
        }, "");


        $.validator.addMethod("checkPenCard", function(value, element, p) {
            if (this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value) == true) {
                result = true;
            } else {
                $.validator.messages.checkPenCard = "Please enter valid pan card no.";
                result = false;
            }
            return result;
        }, "");
        $.validator.addMethod("checkAadhar", function(value, element, p) {
            if (this.optional(element) || /^(\d{12}|\d{16})$/.test(value) == true) {
                result = true;
            } else {
                $.validator.messages.checkAadhar = "Please enter valid aadhar card  number.";
                result = false;
            }
            return result;
        }, "");

        $.validator.addMethod("dateDdMm", function(value, element, p) {

            if (this.optional(element) ||
                /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value) == true) {
                $.validator.messages.dateDdMm = "";
                result = true;
            } else {
                $.validator.messages.dateDdMm = "Please enter valid date.";
                result = false;
            }

            return result;
        }, "");

        $.validator.addMethod("check_per", function(value, element, p) {
            var val1 = $('#yearly_increment').val();

            var sum = parseInt(val1);
            if (sum > 100) {
                result = false;
                $.validator.messages.check_per = "Yearly Increment  percentage not greater than 100";

            } else {
                result = true;
                $.validator.messages.check_per = "";
            }



            return result;
        }, "");

        $.validator.addMethod("agrDateVal", function(value, element, p) {

            moment.defaultFormat = "DD/MM/YYYY HH:mm";
            var f1 = moment($('#agreement_from').val() + ' 00:00', moment.defaultFormat).toDate();
            var f2 = moment($('#agreement_to').val() + ' 00:00', moment.defaultFormat).toDate();

            var from = new Date(Date.parse(f1));
            var to = new Date(Date.parse(f2));


            if (to > from) {
                $.validator.messages.agrDateVal = "";
                result = true;
            } else {
                $.validator.messages.agrDateVal = "To date must be greater than from date.";
                result = false;
            }


            return result;
        }, "")

        $.validator.addMethod("chk_created", function(value, element, p) {

            moment.defaultFormat = "DD/MM/YYYY HH:mm";
            var f1 = moment($('#select_date').val() + ' 00:00', moment.defaultFormat).toDate();
            var f2 = moment($('#employee_date').val() + ' 00:00', moment.defaultFormat).toDate();

            var from = new Date(Date.parse(f2));
            var to = new Date(Date.parse(f1));

            console.log(to >= from);
            console.log(to , from);
            console.log( new Date(Date.parse(f2)) >= new Date(Date.parse(f1)));
            if (to >= from) {
                $.validator.messages.chk_created = "";
                result = true;
            } else {
                $.validator.messages.chk_created =
                    "Register date  must be greater than or equal to employee date.";
                result = false;
            }


            return result;
        }, "")
        $.validator.addMethod("chk_created_ssb", function(value, element, p) {

            moment.defaultFormat = "DD/MM/YYYY HH:mm";
            var f1 = moment($('#select_date').val() + ' 00:00', moment.defaultFormat).toDate();
            var f2 = moment($('#ssb_date').val() + ' 00:00', moment.defaultFormat).toDate();

            var from = new Date(Date.parse(f2));
            var to = new Date(Date.parse(f1));

            if ($('#owner_ssb_account').val() != '') {
                if (to >= from) {
                    $.validator.messages.chk_created_ssb = "";
                    result = true;
                } else {
                    $.validator.messages.chk_created_ssb =
                        "Register date  must be greater than or equal to SSB account date.";
                    result = false;
                }
            }

            return result;
        }, "")


        $('#add-rent-liability,#edit-rent-liability').validate({ // initialize the plugin
            rules: {

                'select_date': {
                    required: true,
                    dateDdMm: true,
                },
                'branch': {
                    required: true
                },
                'rentType': {
                    required: true
                },
                'agreement_from': {
                    required: true,
                    dateDdMm: true,
                    agrDateVal: true
                },
                'agreement_to': {
                    required: true,
                    dateDdMm: true,
                    agrDateVal: true
                },
                //'date' : {required: true},
                'place': {
                    required: true
                },
                'owner_name': {
                    required: true
                },
                'owner_mobile_number': {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 12
                },
                'owner_pen_card': {
                    required: true,
                    checkPenCard: true
                },
                'owner_aadhar_card': {
                    required: true,
                    checkAadhar: true
                },
                //  'owner_ssb_id' : {required: true,},
                'bank_name': {
                    required: true
                },
                'bank_account_number': {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                'ifsc_code': {
                    required: true,
                    checkIfsc: true,
                },
                'security_amount': {
                    required: true,
                    decimal: true,
                    zero: true
                },
                'rent': {
                    required: true,
                    decimal: true,
                    zero: true
                },
                'yearly_increment': {
                    required: true,
                    decimal: true,
                    check_per: true,
                    zero: true
                },
                'office_area': {
                    required: true,
                    decimal: true,
                    zero: true
                },
                'employee_id': {
                    required: true
                },
                'employee_code': {
                    required: true
                },
                'employee_name': {
                    required: true
                },
                'employee_designation': {
                    required: true
                },
                'mobile_number': {
                    required: true,
                    number: true
                },
                //'rent_agreement' : {required: true},
                'employee_date': {
                    required: true,
                    chk_created: true
                },
                //Sachin G ne karwaya tha ssb account opening date check htwaya 19-jan-2022 3 bje (aman)
                //'ssb_date':{chk_created_ssb: true},
            },
        });

        $('#owner_name').on('keyup', function() {
            if ($("#owner_ssb_account").val() != '') {
                $("#owner_ssb_account").trigger("change");
            }

        })

        $('#rent_payable_filter').validate({ // initialize the plugin
            rules: {
                'rent_month': {
                    required: true
                },
                'rent_year': {
                    required: true
                },
                'rent_type': {
                    required: true
                },
            },
        });

        /*$('#rent_report_filter').validate({ // initialize the plugin
            rules: {
                'rent_branch' : {required: true},
                'rent_month' : {required: true},
                'rent_year' : {required: true},
                'rent_type' : {required: true},
            },
        });*/

        $('#transferr_rent_amount').validate({ // initialize the plugin
            rules: {
                'owner_ssb_account': {
                    required: true
                },
                'bank': {
                    required: true
                },
                'bank_name': {
                    required: true
                },
                'bank_account_number': {
                    required: true,
                    minlength: 8,
                    maxlength: 20
                },
                'mode': {
                    required: true
                },
                'cheque_number': {
                    required: true
                },
                'utr_number': {
                    required: true
                },
                'amount': {
                    required: true
                },
                'neft_charge': {
                    required: true
                },
                'total_amount': {
                    required: true
                },
            },
        });

        $('#agreement_from,#agreement_to,#date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "top",
            autoclose: true
        });

        // $(document).on('change', '#branch', function() {
        //     var bId = $('option:selected', this).attr('data-val');
        //     var sbId = $("#hbranchid option:selected").val();
        //     if (bId != sbId) {
        //         $('#branch').val('');
        //         swal("Warning!", "Branch does not match from top dropdown state", "warning");
        //     }
        // });

        rentLiability = $('#rent-liabilities-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.rentliabilities.list') !!}",
                "type": "POST",
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
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'rent_type',
                    name: 'rent_type'
                },
                {
                    data: 'period_from',
                    name: 'period_from'
                },
                {
                    data: 'period_to',
                    name: 'period_to'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'owner_name',
                    name: 'owner_name'
                },
                {
                    data: 'owner_mobile_number',
                    name: 'owner_mobile_number'
                },
                {
                    data: 'owner_pen_card',
                    name: 'owner_pen_card'
                },
                {
                    data: 'owner_aadhar_card',
                    name: 'owner_aadhar_card'
                },
                {
                    data: 'owner_ssb_account',
                    name: 'owner_ssb_account'
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
                    data: 'ifsc_code',
                    name: 'ifsc_code'
                },
                {
                    data: 'security_amount',
                    name: 'security_amount',
                    "render": function(data, type, row) {
                        return row.security_amount +
                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'rent',
                    name: 'rent',
                    "render": function(data, type, row) {
                        return row.rent +
                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'yearly_increment',
                    name: 'yearly_increment'
                },
                {
                    data: 'office_area',
                    name: 'office_area'
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
                    data: 'employee_designation',
                    name: 'employee_designation'
                },
                {
                    data: 'mobile_number',
                    name: 'mobile_number'
                },
                {
                    data: 'rent_agreement',
                    name: 'rent_agreement'
                },
                {
                    data: 'agreement_status',
                    name: 'agreement_status'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        $(rentLiability.table().container()).removeClass('form-inline');

        rentPayable = $('#rent-payable-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.rentpayable.list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#rent_payable_filter').serializeArray()
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
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'rent_type',
                    name: 'rent_type'
                },
                {
                    data: 'period_from',
                    name: 'period_from'
                },
                {
                    data: 'period_to',
                    name: 'period_to'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'owner_name',
                    name: 'owner_name'
                },
                {
                    data: 'owner_mobile_number',
                    name: 'owner_mobile_number'
                },
                {
                    data: 'owner_pen_card',
                    name: 'owner_pen_card'
                },
                {
                    data: 'owner_aadhar_card',
                    name: 'owner_aadhar_card'
                },
                {
                    data: 'owner_ssb_account',
                    name: 'owner_ssb_account'
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
                    data: 'ifsc_code',
                    name: 'ifsc_code'
                },
                {
                    data: 'security_amount',
                    name: 'security_amount'
                },
                {
                    data: 'rent',
                    name: 'rent'
                },
                {
                    data: 'yearly_increment',
                    name: 'yearly_increment'
                },
                {
                    data: 'office_area',
                    name: 'office_area'
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
                    data: 'employee_designation',
                    name: 'employee_designation'
                },
                {
                    data: 'mobile_number',
                    name: 'mobile_number'
                },
                {
                    data: 'rent_agreement',
                    name: 'rent_agreement'
                },
                {
                    data: 'agreement_status',
                    name: 'agreement_status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        $(rentPayable.table().container()).removeClass('form-inline');

        rentReport = $('#rent-report-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.rentreport.list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#rent_report_filter').serializeArray()
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
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'rent_type',
                    name: 'rent_type'
                },
                {
                    data: 'period_from',
                    name: 'period_from'
                },
                {
                    data: 'period_to',
                    name: 'period_to'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'owner_name',
                    name: 'owner_name'
                },
                {
                    data: 'owner_mobile_number',
                    name: 'owner_mobile_number'
                },
                {
                    data: 'owner_pen_card',
                    name: 'owner_pen_card'
                },
                {
                    data: 'owner_aadhar_card',
                    name: 'owner_aadhar_card'
                },
                {
                    data: 'owner_ssb_account',
                    name: 'owner_ssb_account'
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
                    data: 'ifsc_code',
                    name: 'ifsc_code'
                },
                {
                    data: 'security_amount',
                    name: 'security_amount',
                    "render": function(data, type, row) {
                        return row.security_amount +
                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'rent',
                    name: 'rent',
                    "render": function(data, type, row) {
                        return row.rent +
                            " <img src='{{ url('/') }}/asset/images/rs.png' width='7'>";
                    }
                },
                {
                    data: 'yearly_increment',
                    name: 'yearly_increment'
                },
                {
                    data: 'office_area',
                    name: 'office_area'
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
                    data: 'employee_designation',
                    name: 'employee_designation'
                },
                {
                    data: 'mobile_number',
                    name: 'mobile_number'
                },
                {
                    data: 'rent_agreement',
                    name: 'rent_agreement'
                },
                {
                    data: 'agreement_status',
                    name: 'agreement_status'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                /*{data: 'action', name: 'action',orderable: false, searchable: false},*/
            ]
        });
        $(rentReport.table().container()).removeClass('form-inline');

        // Handle click on "Select all" control
        $('#select_all').on('click', function() {
            var rows = rentPayable.rows({
                'search': 'applied'
            }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Handle click on checkbox to set state of "Select all" control
        $('#rent-payable-table tbody').on('change', 'input[type="checkbox"]', function() {
            if (!this.checked) {
                var el = $('#select_all').get(0);
                if (el && el.checked && ('indeterminate' in el)) {
                    el.indeterminate = true;
                }
            }
        });



        $(document).on('change', '#select_all,#rent_payable_record', function() {
            var checked = [];
            var unchecked = [];
            $('input[name="rent_payable_record"]:checked').each(function() {
                checked.push(this.value);
            });

            $('input[type=checkbox]:not(:checked)').each(function() {
                if (Math.floor(this.value) == this.value && $.isNumeric(this.value))
                    unchecked.push(this.value);
            });

            $('#selected_records').val(checked);
            $('#pending_records').val(unchecked);
        });

        $(document).on('change', '#amount_mode,#mode', function() {
            var modeValue = $(this).val();
            if (modeValue == 0 && modeValue != '') {
                $('.bank-section').hide();
                $('.cheque-section').hide();
                $('.online-section').hide();
            } else if (modeValue == 1 && modeValue != '') {
                $('.bank-section').show();
            } else if (modeValue == 2 && modeValue != '') {
                $('.cheque-section').show();
                $('.online-section').hide();
            } else if (modeValue == 3 && modeValue != '') {
                $('.online-section').show();
                $('.cheque-section').hide();
            } else {
                $('.online-section').hide();
                $('.cheque-section').hide();
            }
        });

        $(document).on('change', '#bank', function() {
            var title = $('option:selected', this).attr('data-title');
            var accountNumber = $('option:selected', this).attr('data-account-number');
            $('#bank_name').val(title);
            $('#bank_account_number').val(accountNumber);
        });

        $('#rent-payable-transfer-table').DataTable();

        // $('.export').on('click',function(){
        //     $('form#rent_report_filter').attr('action',"{!! route('admin.rentreport.export') !!}");
        //     $('form#rent_report_filter').submit();
        //     return true;
        // });

        $('.export').on('click', function() {
            $('form#filter').attr('action', "{!! route('admin.rent.export') !!}");
            $('form#filter').submit();
            return true;
        });
        /*
	$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        //$('#member_export').val(extension);
		
        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExports(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
	});
	
	
	// function to trigger the ajax bit
    function doChunkedExports(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('admin.rent.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExports(start,limit,formData,chunkSize);
					$(".loaders").text(response.percentage+"%");
                }else{
					var csv = response.fileName;
                    console.log('DOWNLOAD');
					$(".spiners").css("display","none");
					$("#cover").fadeOut(100); 
					window.open(csv, '_blank');
                }
            }
        });
    }
	

    jQuery.fn.serializeObject = function(){
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
*/
        $('.filter-report').on('click', function() {
            var isSearch = $('#is_search').val();
            var rentBranch = $("#rent_branch option:selected").val();
            var rentMonth = $("#rent_month option:selected").val();
            var rentYear = $("#rent_year option:selected").val();
            var rentType = $("#rent_type option:selected").val();

            $.ajax({
                type: "POST",
                url: "{!! route('admin.rentreport.ids') !!}",
                dataType: 'JSON',
                data: {
                    'isSearch': isSearch,
                    'rentBranch': rentBranch,
                    'rentMonth': rentMonth,
                    'rentYear': rentYear,
                    'rentType': rentType
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#rentIds').val(response.resCount);
                }
            });
        });

        function compareDates(gd1, d2) {
            const [day1, month1, year1] = gd1.split('/');
            const [day2, month2, year2] = d2.split('/');
            const date1 = new Date(`${month1}/${day1}/${year1}`);
            const date2 = new Date(`${month2}/${day2}/${year2}`);
            return (date1 > date2) ? true : false;
        }



        $('#owner_ssb_account').on('change', function() {
            var ssb_account = $(this).val();
            var company = $('#company_id option:selected').val();
            var rDate = $('#select_date').val();


            if (company == "") {
                swal('Warning', 'Please select company first', 'warning');
                $('#owner_ssb_account').val('');
                return false;
            }
            // if (rDate == "") {
            //     swal('Warning', 'Please select register Date first', 'warning');
            //     $('#owner_ssb_account').val('');
            //     return false;
            // }

            if (ssb_account != '') {

                var name = $('#owner_name').val().toLowerCase();

                $.ajax({
                    type: "POST",
                    url: "{!! route('admin.rent_ssb_check') !!}",
                    data: {
                        ssb_account: ssb_account
                    },
                    dataType: "JSON",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {


                        if (response.resCount == 1) {

                            if (company != response.account_no.company_id) {
                                swal("Error!", "SSB account not found in selected company",
                                    "error");
                                $('#owner_ssb_account').val('');
                                return false;
                            }
                            if (ssb_account == response.account_no.account_no && $.trim(name) == (response.name.toLowerCase()).replace(/\s+/g, ' ').trim()) {
                                //    $('#ssb_date').val(response.ssbDate);
                                //    if (compareDates(rDate, $('#ssb_date').val())) {
                                $('#owner_ssb_account').val(response.account_no.account_no);
                                $('#owner_ssb_id').val(response.account_no.id);
                                $('#ssb_date').val(response.ssbDate);
                                //    } else {
                                //        swal("Warning!", `Register Date must be greater then Owner SSB account Date(${$('#ssb_date').val()})!`,
                                //         "warning");
                                //         $('#ssb_date').val('');
                                //         $('#owner_ssb_account').val('');
                                //         return false;
                                //    }
                            } else {
                                swal("Error!", "Owner name or SSB account holder name(" +
                                    response.name.toLowerCase() + ") not match!",
                                    "error");
                                $('#owner_ssb_account').val('');
                                $('#owner_ssb_id').val('');
                                $('#ssb_date').val('');
                            }
                        } else {
                            swal("Error!", " SSB account not found!", "error");
                            $('#owner_ssb_account').val('');
                            $('#owner_ssb_id').val('');
                            $('#ssb_date').val('');

                        }

                    }
                })


            }

        })

        $('#employee_code').on('change', function() {
            if ($('#company_id').val() == '') {
                swal("Error!", "Please select company first!", "error");
                $(this).val('');
                return false;
            }
            var employee_code = $(this).val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.rent_employee_check') !!}",
                data: {
                    employee_code: employee_code
                },
                dataType: "JSON",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.resCount == 1 && response.emp.company_id != $('#company_id').val()) {
                        swal("Error!", "Selected company and SSB account do not match!", "error");
                        $('#employee_code').val('');
                        return false;
                    }
                    if (response.resCount == 1) {

                        $('#employee_code').val(response.emp.employee_code);
                        $('#employee_id').val(response.emp.id);
                        $('#employee_name').val(response.emp.employee_name);
                        $('#employee_designation').val(response.designation_name);
                        $('#mobile_number').val(response.emp.mobile_no);
                        $('#employee_date').val(response.register_date);
                    } else if (response.resCount == 2) {

                        swal("Error!", " Employee Inactive", "error");
                        $('#employee_code').val('');
                        $('#employee_id').val('');
                        $('#employee_name').val('');
                        $('#employee_designation').val('');
                        $('#mobile_number').val('');
                        $('#employee_date').val('');
                    } else {
                        swal("Error!", " Employee code not found!", "error");
                        $('#employee_code').val('');
                        $('#employee_id').val('');
                        $('#employee_name').val('');
                        $('#employee_designation').val('');
                        $('#mobile_number').val('');
                        $('#employee_date').val('');

                    }
                }
            })
        })







        $(document).ajaxStart(function() {
            $(".loader").show();
        });

        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
    });

    function searchForm() {
        if ($('#filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            rentLiability.draw();
        }
    }

    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#company_id').val(0);
        $('#company_id').trigger('change');
        $('#rent_type').val('');
        $(".table-section").addClass("hideTableData");
        rentLiability.draw();
    }

    function searchRentPayableForm() {
        if ($('#rent_payable_filter').valid()) {
            $('#is_search').val("yes");
            rentPayable.draw();
        }
    }

    function resetRentPayableForm() {
        var form = $("#rent_payable_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#rent_month').val("");
        $('#rent_year').val('');
        $('#rent_type').val('');
        rentPayable.draw();
    }

    function searchRentReportForm() {
        if ($('#rent_report_filter').valid()) {
            $('#is_search').val("yes");
            rentReport.draw();
        }
    }

    function resetRentReportForm() {
        var form = $("#rent_report_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#rent_branch').val("");
        $('#rent_month').val("");
        $('#rent_year').val('');
        $('#rent_type').val('');
        rentReport.draw();
    }
</script>