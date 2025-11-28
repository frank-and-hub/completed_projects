<script type="text/javascript">
    "use strict";
    var gstPayableListing;
    var gsttransferListing;
    $(document).ready(function() {
        
        $(document).on('click','.download_data',function(){
            var path = $(this).data('path');
            var name = $(this).data('name');
            // Send the POST request
            $.ajax({
                url: "{{ route('admin.gst_payable_chalan.download') }}",
                method: 'POST',
                data: {'name': name, 'path': path},
                // xhrFields: {
                //     responseType: 'blob'
                // },
                success: function (data, status, xhr) {
                    var blob = new Blob([data], { type: xhr.getResponseHeader('content-type') });
                    var url = URL.createObjectURL(blob);

                    var link = document.createElement('a');
                    link.href = url;
                    link.download = name;

                    document.body.appendChild(link);
                    link.click();

                    URL.revokeObjectURL(url);
                    document.body.removeChild(link);
                },
                error: function (xhr, status, error) {
                    console.log('Error downloading file:', error);
                }
            });
            return false;
        });
        
        $(document).on('click','.view_data',function() {
            var imageName = $(this).data('name');
            var imagePath = $(this).data('path');
            var imageUrl = "{{-- route('admin.gst_payable_chalan.view') --}}?image=" + imageName + "&path=" + imagePath;
            window.open(imageUrl, '_blank');
        });
        var created_at = $('.created_at').val();
        var date = new Date();
        jQuery.validator.addMethod("greaterThan",
            function(value, element, params) {
                if (!/Invalid|NaN/.test(new Date(value))) {
                    return new Date(value) > new Date($(params).val());
                }
                return isNaN(value) && isNaN($(params).val()) ||
                    (Number(value) > Number($(params).val()));
            }, 'Must be greater than {0}.');
        $.validator.addMethod("gratedZero", function(value, element, params) {
            // Regular expression to match positive decimal numbers greater than zero
            var floatValue = parseFloat(value);
            // Check if the value is greater than zero
            return floatValue > -1;
            }, "values must be greater than zero."
        );
        $.validator.addMethod("transactionNumberCheck", function(value, element, params) {
            // Regular expression to match positive decimal numbers greater than zero
            var number = parseFloat(value);
            // Check if the value is greater than zero
            let response = null;
            $.post("{{route('admin.gst.transactionNumberCheck')}}",{'number':number},function(e){
                response = e;
            })
            return response = 0;
            }, "Transaction Number alrady existe !."
        );
        function createValidationRule(fieldName) {
            return {
                gratedZero: function(element) {
                    return (parseFloat($('#${fieldName}').val()) == 0);
                },
            };
        };
        $('#gst_transfer_payable_from').validate({ // initialize the plugin
            rules: {
                'payable_start_date': 'required',
                'payable_end_date': 'required',
                'company_id': 'required',
                'state': 'required',
            },submitHandler: function(form) {
                $('.submit-payable').prop('disabled',true);
                form.submit();
            },
        });
        $('#gst_transfer_filter').validate({ // initialize the plugin
            rules: {
                'company_id': 'required',
                'is_paid': 'required',
            }
        });
        @if($view == 0)
        $('#company_id').on('change',function(){
            stateChange();
        })
        @endif
        $('#gst_payable_from').validate({ // initialize the plugin
            rules: {
                'payable_start_date': 'required',
                'payable_end_date': {
                    required: true
                },

                'payable_igst_amount': {
                    required: true,
                    number: true,
                    gratedZero: function() {
                        return parseFloat($('#payable_igst_amount').val()) > 0;
                    },
                },
                'payable_cgst_amount': {
                    required: true,
                    number: true,
                    gratedZero: function() {
                        return parseFloat($('#payable_cgst_amount').val()) > 0;
                    },
                },
                'payable_sgst_amount': {
                    required: true,
                    number: true,
                    gratedZero: function() {
                        return parseFloat($('#payable_sgst_amount').val()) > 0;
                    },
                },
                'payable_payment_date': {
                    required: true
                },
                'payable_paid_amount': {
                    required: true,
                    number: true
                },
                'bank_id': 'required',
                'account_id': 'required',
                'upload_challan': { required : true,
                    accept: "image/jpeg, image/png, image/jpg, image/ico, image/gif, image/svg+xml, application/pdf, image/webp",
                },
                'remark': 'required',
                'id': 'required',
                'daybook_diff': 'required',
                'transaction_number': {
                    required: true,
                    // transactionNumberCheck: true,
                },
                'neft_charge': {
                    number: true
                },
                'payable_late_panelty': {
                    required: true,
                    number: true
                },
                'total_paid_amount': {
                    required: true,
                    number: true,
                    gratedZero:true
                },
                'company_id': 'required',
                'final_payable_amount': {
                    required : true,
                    gratedZero : true,
                }
            }, 
        messages: {
            upload_challan: {
                required: "Please select a file.",
                accept: "Only image files - jpeg, png, jpg, ico, gif, svg, pdf, webp) are allowed."
            },
        },
        submitHandler: function(form) {
                $('.submit-payable').prop('disabled',true);
                form.submit();
            },
        });

        $('#start_date,#end_date,#payable_start_date,#payable_end_date').hover( function() {
            var created_at = $('.created_at').val();
            // var createdDate = new Date(created_at);
            console.log(created_at);
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
        @if($view == 1)
            $('input').prop('disabled', true).css('color', '#333');
            $('select').css({'-webkit-appearance': 'none','-moz-appearance': 'none','appearance': 'none','color' :'#333'}).prop('disabled', true);
            $('sup').html('');
        @endif
        // $('label').html(function(index, currentText) {
            // return currentText.toUpperCase();
        // }); 
        gstPayableListing = $('#gst_payable_listing').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#filter').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.gst_payable_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                {data: 'DT_RowIndex',name: 'DT_RowIndex'},
                {data: 'created_date',name: 'created_date'},
                {data: 'company',name: 'company'},
                {data: 'branch',name: 'branch'},
                {data: 'head',name: 'head'},
                {data: 'name',name: 'name'},
                {data: 'customer_id',name: 'customer_id'},
                {data: 'dr_entry',name: 'dr_entry'},
                {data: 'cr_entry',name: 'cr_entry'},
                {data: 'balance',name: 'balance'},
            ],"ordering": false,
        });
        $(gstPayableListing.table().container()).removeClass('form-inline');
        $('#bank_id').on('change', function() {
            var bankId = $(this).val();
            $('#account_id').val('');
            $('.bank-account').hide();
            $('.' + bankId + '-bank-account').show();
        });
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
        $('#gst_payable_filter').validate({
            rules: {
                branch_id: {
                    required: true,
                },
            },
            messages: {
                branch_id: {
                    required: 'Please Select Branch.'
                },
            },
            messages: {
                member_id: {
                    number: 'Please enter valid member id.'
                },
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
        gsttransferListing = $('#gst_transfer_list').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $('html, body').stop().animate({
                    scrollTop: ($('#gst_transfer_list').offset().top)
                }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.gst_transfer_listing') !!}",
                "type": "POST",
                "data": function(d) {
                    // d.searchform = $('form#gst_payable_filter').serializeArray()
                    d.searchform = $('form#gst_transfer_filter').serializeArray()
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
                { data: 'DT_RowIndex'},
                { data: 'transfer_date'},
                { data: 'date_range'},
                { data: 'state'},
                { data: 'igst_amt'},
                { data: 'cgst_amt'},
                { data: 'sgst_amt'},
                { data: 'set_off_Igst'},
                { data: 'set_off_cgst'},
                { data: 'set_off_sgst'},
                { data: 'final_igst'},
                { data: 'final_cgst'},
                { data: 'final_sgst'},
                { data: 'transfer_amount'},
                { data: 'penalty_amount'},
                { data: 'neft_charge'},
                { data: 'late_panelty'},
                { data: 'total_payable_amount'},
                { data: 'payment_date'},
                { data: 'to_paid'},
                { data: 'company'},
                { data: 'file'},
                { data: 'action'}
            ],"ordering": false,
        });
        $(gsttransferListing.table().container()).removeClass('form-inline');
        $('.export_gst_payable').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#gst_payable_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#filter').serializeObject();
                var chunkAndLimit = 1000;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#gst_payable_export').val(extension);
                $('form#filter').attr('action', "{{ route('admin.gst_payable.export_gst_payable') }}");
                $('form#filter').submit();
            }
        });
        $('.export_gst_transafer').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#gst_transfer_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#gst_transfer_filter').serializeObject();
                var chunkAndLimit = 1000;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport2(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#gst_transfer_export').val(extension);
                $('form#gst_transfer_filter').attr('action', "{!! route('admin.gst_transafer.export_gst_transafer') !!}");
                $('form#gst_transfer_filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('admin.gst_payable.export_gst_payable') }}",
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
        function doChunkedExport2(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.gst_transafer.export_gst_transafer') !!}",
                data: formData,
                success: function(response) {
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExport(start, limit, formData, chunkSize);
                        $(".loaders").text(response.percentage + "%");
                    } else {
                        var csv = response.fileName;
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

        $(document).on('change', '#payable_start_date,#payable_end_date,#company_id,#state', function() {
            if($('#gst_transfer_payable_from').valid()){
                loadAmount();
            }
        });
        $(document).ajaxStart(function() {
            $(".loader").show();
        });
        $(document).ajaxComplete(function() {
            $(".loader").hide();
        });
        @if (isset($id))
            if ($('#id').val() != null) {
                loadAmount();
            }
            $('#company_id,#state').on('mousedown keydown', function(event) {
                event.preventDefault();
            });
        @endif
        $('#account_id,#payable_payment_date').on('change',function(){            
            bank_available_balance();
        });
        $('#neft_charge,#payable_late_panelty,#payable_late_panelty,#payable_igst_amount,#payable_cgst_amount,#payable_sgst_amount').on('keyup hover click', function() {
            alertAmount();
            const gst = parseFloat($(this).data('gst_amt'));
            var name = $(this).data('name');
            var igst_amt = parseFloat($('#payable_igst_amount').val());
            var cgst_amt = parseFloat($('#payable_cgst_amount').val());
            var sgst_amt = parseFloat($('#payable_sgst_amount').val());
            var paid_amount = ((igst_amt??0) + (cgst_amt??0) + (sgst_amt??0));
            if((gst > 0) && (gst)){
                if(gst < parseFloat($(this).val())){
                    $(this).val(gst);
                    $('#paid_amount').val(paid_amount ? paid_amount.toFixed(2) : 0.00);
                    // $(this).val(This.toString().slice(0, -1));
                    swal('Warning!',  name.toUpperCase()  +' Payable amount must equal or less then ' + gst + ' ' + name.toUpperCase() + ' transfer request amount','warning');
                    $('#payable_late_panelty').val({{$late_panelty??0}});
                    $('#neft_charge').val({{$neft_charge??0}});
                    $('#final_payable_amount').val({{(($payable_igst_amount ?? 0) + ($payable_cgst_amount ?? 0) + ($payable_sgst_amount ?? 0) + ($late_panelty??0) + ($neft_charge??0))}});                    
                    alertAmount();
                    return false;
                }
            }
            if(gst == 0){
                $(this).prop('readonly', true);
            }
        });
        $('#filtersubmit').on('click',function(e){
            e.preventDefault();
            if($('#gst_transfer_filter').valid()){
                $('#is_search').val('yes');
                gsttransferListing.draw();
                $('.table-section').removeClass('hideTableData');
            }else{
                $('.table-section').hasClass('hideTableData').removeClass('hideTableData');
            }
            
        })
    });
    function bank_available_balance(){
        const accountId = $('#account_id').val();
        const bankId = $('#bank_id').val();
        const companyId = $('#company_id').val();
        const payable_payment_date = $('#payable_payment_date').val();
        if(payable_payment_date){
            $.ajax({
                url: '{{ route('admin.get_bank_balance') }}',
                method: 'POST',
                data: {'account_id': accountId, 'bank_id': bankId, 'company_id'  : companyId ,'entry_date' : payable_payment_date},
                success: function (data, status, xhr) {
                    $('#bank_available_balance').val(data);
                },
                error: function (xhr, status, error) {
                    console.log('Error downloading file:', error);
                }
            });
            console.log(payable_payment_date);
        }else{
            $('#bank_available_balance').val('0');
        }
    }
    function alertAmount(){
        var late_panelty = parseFloat($('#payable_late_panelty').val());
            var igst_amt = parseFloat($('#payable_igst_amount').val());
            var neft_charge = parseFloat($('#neft_charge').val());
            var cgst_amt = parseFloat($('#payable_cgst_amount').val());
            var amount = parseFloat($('#amt').val());
            var sgst_amt = parseFloat($('#payable_sgst_amount').val());
            var paid_amount = ((igst_amt??0) + (cgst_amt??0) + (sgst_amt??0));
            var total_paid_amount = ((late_panelty??0) + (igst_amt??0) + (cgst_amt??0) + (sgst_amt??0));
            var final_payable_amount = ((late_panelty??0) + (neft_charge??0) + (igst_amt??0) + (cgst_amt??0) + (sgst_amt??0));
            $('#total_paid_amount').val(total_paid_amount ? total_paid_amount.toFixed(2) : 0.00);
            $('#paid_amount').val(paid_amount ? paid_amount.toFixed(2) : 0.00);
            $('#final_payable_amount').val(final_payable_amount ? final_payable_amount.toFixed(2) : 0.00);
            // var This = parseFloat($(this).val());
            var bank_available_balance = $('#bank_available_balance').val();
            if(!(bank_available_balance) && (bank_available_balance == 0)){
                $('#total_paid_amount').val('');
                swal('Warning!','please select bank and Account first !','warning');
                $('#payable_late_panelty').val('0.00');
                $('#neft_charge').val('0.00');
                return false;
            }else{
                if(bank_available_balance < total_paid_amount){
                    $('#total_paid_amount').val('');
                    swal('Warning!','Total Paid Amount should less then Bank Available Balance','warning');
                    $('#payable_late_panelty').val('0.00');
                    $('#neft_charge').val('0.00');
                    return false;
                }
            }
    }
    function stateChange(){
        return false;
        /*
        let Id ;
            let Name ;
            const companyId = $('#company_id').val();
            $.post('{{route("admin.compay_to_state")}}',{'companyId':companyId},function(e){
                $('#state').find('option').remove();
                $('#state').append('<option value="">---- Please Select ----</option>');               
                if(companyId > 0){
                    $.each(e, function (i, v) {
                        if (v) {
                            $.each(v, function (ind, val) {
                                Id = val.id;
                                Name = val.name;
                                if (!$.isEmptyObject(val.branch)) {
                                    $.each(val.branch, function (index, value) {
                                        if (value?.companybranchs.company_id == companyId) {
                                            $("#state").append("<option value=" + Id + "  >" + Name + " - " + Id + "</option>");
                                            return false;
                                        }else{
                                            console.log('no relation exist with company branch')
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            },'JSON');
        */
    }

    function loadAmount() {
        var startDate = $('#payable_start_date').val();
        var endDate = $('#payable_end_date').val();
        var id = $('#id').val();
        var companyId = $('#company_id').val();
        // var headId = $('#payable_head_id').val();
        var stateId = $('#state').val();
        var payableLatePenalty = $('#payable_late_panelty').val();
        var totalPaidAmount = $('#total_paid_amount').val();
        var date1 = startDate.split('/');
        var newStartDate = date1[2] + '-' + date1[1] + '-' + date1[0];
        var date2 = endDate.split('/');
        var newEndDateDate = date2[2] + '-' + date2[1] + '-' + date2[0];
        if (newStartDate > newEndDateDate) {

            $('#payable_end_date').val('');
            swal("Warning!", "End date must be greater than from start date");
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{{ route('admin.gstpayableamount') }}",
            dataType: 'JSON',
            data: {
                'startDate': startDate,
                'endDate': endDate,
                // 'headId': headId,
                'companyId': companyId,
                'stateId': stateId,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(e) {
                @if($view == 0)
                if(!id){
                    if(e.checkGstStartDate > 0 || e.checkGstEndDate > 0){
                        $('#state').val('');
                        swal("Warning!", "Gst Transfer request Already Created on selected Date");
                        return false;
                    }else{
                        $('#payable_total_dr_amount').val(e.totalDr); 
                        $('#payable_total_cr_amount').val(e.totalCr); 
                        $('#total_paid_amount').val('');
                        $('#payable_igst_amount_cr').val(e.amtIgstCr);
                        $('#payable_igst_amount_dr').val(e.amtIgstDr);
                        $('#payable_cgst_amount_cr').val(e.amtCgstCr);
                        $('#payable_cgst_amount_dr').val(e.amtCgstDr);
                        $('#payable_sgst_amount_cr').val(e.amtSgstCr);
                        $('#payable_sgst_amount_dr').val(e.amtSgstDr);
                    }
                }else{
                    if (e.checkStartDate > 0 || e.checkEndDate > 0 ) {                        
                        $('#state').val('');
                        swal("Warning!", "Gst Transfer payable Already Created on selected Date");
                        return false;
                    } else {
                        $('#payable_total_dr_amount').val(e.totalDr); 
                        $('#payable_total_cr_amount').val(e.totalCr); 
                        $('#total_paid_amount').val('');
                        $('#payable_igst_amount_cr').val(e.amtIgstCr);
                        $('#payable_igst_amount_dr').val(e.amtIgstDr);
                        $('#payable_cgst_amount_cr').val(e.amtCgstCr);
                        $('#payable_cgst_amount_dr').val(e.amtCgstDr);
                        $('#payable_sgst_amount_cr').val(e.amtSgstCr);
                        $('#payable_sgst_amount_dr').val(e.amtSgstDr);
                    }
                }
                @else
                    $('#payable_total_dr_amount').val(e.totalDr); 
                    $('#payable_total_cr_amount').val(e.totalCr); 
                    $('#payable_igst_amount_cr').val(e.amtIgstCr);
                    $('#payable_igst_amount_dr').val(e.amtIgstDr);
                    $('#payable_cgst_amount_cr').val(e.amtCgstCr);
                    $('#payable_cgst_amount_dr').val(e.amtCgstDr);
                    $('#payable_sgst_amount_cr').val(e.amtSgstCr);
                    $('#payable_sgst_amount_dr').val(e.amtSgstDr);
                @endif
            }
        });
    }

    function searchForm() {
        if ($('#filter').valid()) {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var branchId = $('#branch').val();
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            gstPayableListing.draw();
        }
    }
    function resetForm() {
        var form = $("#gst_payable_filter"),
            validator = form.validate();
        // validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#start_date').val('');
        $('#company_id').val('0');
        $('#end_date').val('');
        $('#is_paid').val('');
        $('#state').val('');
        $('#branch').val('');
        $(".table-section").addClass("hideTableData");
        gstPayableListing.draw();
    }
    function zero_gst_amount(){
        swal('Warning','For Gst Payable, Gst amount must be greater than 0','warning');return false;
    }
</script>
