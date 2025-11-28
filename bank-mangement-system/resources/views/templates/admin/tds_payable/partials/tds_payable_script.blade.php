<script type="text/javascript">
    var tdsPayableListing;
    var tdstransferListing;

    $(document).on('click','.download_data',function(){
        var path = $(this).data('path');
        var name = $(this).data('name');
        // Send the POST request
        $.ajax({
            url: `{{ route('admin.tds_payable_chalan.download') }}`,
            method: 'POST',
            data: {'name': name, 'path': path},
            xhrFields: {
                eType: 'blob'
            },
            success: function (data, status, xhr) {
                var blob = new Blob([data], { type: xhr.geteHeader('content-type') });
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
                console.error('Error downloading file:', error);
            }
        });

        return false;

    });

    $(document).on('click','.view_data',function() {
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

        jQuery.validator.addMethod("gratedZero", function(value, element, params) {
            var floatValue = parseFloat(value);
            return floatValue > 0;
        }, "values must be greater than zero.");

        $('#tds_transfer_payable_from').validate({ // initialize the plugin
            rules: {
                'payable_start_date': 'required',
                'payable_end_date': {
                    required: true
                },
                'payable_head_id': 'required',
                'payable_tds_amount': {
                    required: true,
                    number: true,
                    gratedZero:true
                },
                'company_id': 'required',
            },
            submitHandler: function(form) {
                $('.submit-payable').prop('disabled',true);
                form.submit();
            },
        });

        $('#account_id,#payable_payment_date').on('change',function(){
            bank_available_balance();
        });

        $('#tds_payable_from').validate({ // initialize the plugin
            rules: {
                'payable_start_date': 'required',
                'payable_end_date': {
                    required: true
                },
                'payable_head_id': 'required',
                'payable_tds_amount': {
                    required: true,
                    number: true
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
                    accept: "image/jpeg, image/png, image/jpg, image/ico, image/gif, image/svg+xml, application/pdf, image/webp"
                },
                'remark': 'required',
                'id': 'required',
                'daybook_diff': 'required',
                'transaction_number': {
                    required: true,
                },
                'neft_charge': {
                    number: true
                },
                'payable_late_penalty': {
                    required: true,
                    number: true,
                },
                'total_paid_amount': {
                    required: true,
                    number: true,
                    gratedZero:true
                },
                'company_id': 'required',
            }, 
            messages: {
                upload_challan: {
                    required: "Please select a file.",
                    accept: "Only image files (jpeg, png, jpg, ico, gif, svg, pdf, webp) are allowed."
                },
            },
            submitHandler: function(form) {
                $('.submit-payable').prop('disabled',true);
                form.submit();
            },
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

        tdsPayableListing = $('#tds_payable_listing').DataTable({
            processing: true,
            searching: false,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            searching:true,
            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
                var oSettings = this.fnSettings ();
                $('html, body').stop().animate({
                scrollTop: ($('form#tds_payable_filter').offset().top)
            }, 1000);
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.tds_payable_listing') !!}",
                "type": "POST",
                "data":function(d) {d.searchform=$('form#tds_payable_filter').serializeArray()}, 
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                    { data: 'DT_RowIndex'},
                    { data: 'created_date'},
                    { data: 'company'},
                    { data: 'branch'},
                    { data: 'tds_head'},
                    { data: 'vendor_name'},
                    { data: 'pan_number'},
                    { data: 'dr_entry'},
                    { data: 'cr_entry'},
                    { data: 'balance'},
            ],"ordering": false,
        });
        $(tdsPayableListing.table().container()).removeClass( 'form-inline' );

        $('#bank_id').on('change', function() {
            var bankId = $(this).val();
            $('#account_id').val('');
            $('.bank-account').hide();
            $('.' + bankId + '-bank-account').show();
        });

        $('#tds_payable_filter').validate({
            rules: {
                start_date: {
                    dateDdMm: true,
                },
                end_date: {
                    dateDdMm: true,
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
            columns: [
                { data: 'DT_RowIndex'},
                { data: 'transfer_date'},
                { data: 'date_range'},
                { data: 'head_name'},
                { data: 'head_amount'},
                { data: 'penalty_amount'},
                { data: 'payment_date'},
                { data: 'to_paid'},
                { data: 'company'},
                { data: 'file'},
                { data: 'action'}
            ],"ordering": false,
        });
        $(tdstransferListing.table().container()).removeClass('form-inline');
        
        $(document).on('click','#sfilter',function(){
            tdstransferListing.draw();
        });
        $(document).on('click','#resetFilter',function(){
            $('#transfer_date').val('');
            $('#tds_head').val('');
            tdstransferListing.draw();
        });

        $('.export_tds_transafer').on('click',function(e){
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#tds_transfer_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#tds_filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport2(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#tds_transfer_export').val(extension);
                $('form#tds_filter').attr('action', "{!! route('admin.export_tds_transafer') !!}");
                $('form#tds_filter').submit();
            }
        });
        
        $('.export_tds_payable').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#tds_payable_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#tds_payable_filter').serializeObject();
                var chunkAndLimit = 1500;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#tds_payable_export').val(extension);
                $('form#tds_payable_filter').attr('action', "{!! route('admin.tds_payable.export_tds_payable') !!}");
                $('form#tds_payable_filter').submit();
            }
        });


        $(document).on('change','#payable_head_id,#payable_start_date,#payable_end_date,#company_id', function() {
            // if($('#tds_transfer_payable_from').valid()){
                loadAmount();
            // }
        });
        
        @if (isset($view))
            if ($('#payable_head_id').val() != null) {
                loadAmount();
            }
        @endif

        @if (isset($head_id))
            $('#company_id').on('mousedown keydown', function(event) {
                event.preventDefault();
            });
        @endif

        $(document).on('keyup', '#payable_late_penalty,#payable_tds_amount', function() {
            // return false;
            let payment_date = parseFloat($('#payable_late_penalty').val());
            let tds_amount = parseFloat($('#payable_tds_amount').val());
            let payable_paid_amount = parseFloat($('#payable_paid_amount').val());
            let total_paid_amount = (tds_amount + (payment_date??0));
            $('#total_paid_amount').val(total_paid_amount ? total_paid_amount.toFixed(2) : 0.00);
           
            const bank_available_balance = $('#bank_available_balance').val();
            if(tds_amount > payable_paid_amount){
                swal('Warning!',"TDS Amount Equal or less then " + payable_paid_amount.toFixed(2),'warning');
                $('#payable_tds_amount').val(payable_paid_amount.toFixed(2));
                $('#total_paid_amount').val(payable_paid_amount.toFixed(2));
                return false;
            }
            if(!(bank_available_balance) && (bank_available_balance == 0)){
                swal('Warning!','please select bank and Account first !','warning');
                $('#payable_late_penalty').val('0.00');
                return false;
            }else{
                if(bank_available_balance < total_paid_amount){
                    swal('Warning!','Total Paid Amount sould less then Bank Available Balance','warning');
                    $('#payable_late_penalty').val('0.00');
                }
            }
        });

        @if($view == 1)
            $('input').prop('disabled', true).css('color', '#333');
            $('select').css({'-webkit-appearance': 'none','-moz-appearance': 'none','appearance': 'none','color' :'#333'}).prop('disabled', true);
            $('sup').html('');
            $('label').html(function(index, currentText) {
                return currentText.toUpperCase();
            });
        @endif
    });

    function bank_available_balance(){
        var accountId = $('#account_id').val();
        var bankId = $('#bank_id').val();
        var companyId = $('#company_id').val();
        var payable_payment_date = $('#payable_payment_date').val();
        $.ajax({
            url: `{{ route('admin.get_bank_balance') }}`,
            method: 'POST',
            data: {'account_id': accountId, 'bank_id': bankId, 'company_id'  : companyId ,'entry_date' : payable_payment_date},
            success: function (data, status, xhr) {
                $('#bank_available_balance').val(data);
            },
            error: function (xhr, status, error) {
                console.log('Error downloading file:', error);
            }
        });
    }
    
    function loadAmount() {
        var startDate = $('#payable_start_date').val();
        var endDate = $('#payable_end_date').val();
        if(startDate){
            var date1 = startDate.split('/');
            var newStartDate = date1[2] + '-' + date1[1] + '-' + date1[0];
            var date2 = endDate.split('/');
            var newEndDateDate = date2[2] + '-' + date2[1] + '-' + date2[0];
            if (newStartDate > newEndDateDate) {
                $('#payable_head_id').val('');
                $('#payable_end_date').val('');
                swal("Warning!", "End date must be greater than from start date");
                return false;
            }
        }
        var headId = $('#payable_head_id').val();
        var companyId = $('#company_id').val();
        var id = $('#id').val();
        var payableLatePenalty = $('#payable_late_penalty').val();
        var totalPaidAmount = $('#total_paid_amount').val();
        
        $.ajax({
            type: "POST",
            url: "{!! route('admin.tdspayableamount') !!}",
            dataType: 'JSON',
            data: {
                'startDate': startDate,
                'endDate': endDate,
                'headId': headId,
                'companyId': companyId
            },
            success: function(e) {
                @if($view == 0)
                if(!id){
                    if(e.checkTDSStartDate > 0 || e.checkTDSEndDate > 0){
                        $('#payable_head_id').val('');
                        swal("Warning!", "TDS Transfer request Already Created on selected Date");
                        return false;
                    }else{
                        $('#payable_tds_amount').val(e.data);
                        $('#payable_paid_amount').val(e.data);
                        $('#total_paid_amount').val(e.data);
                    }                    
                }else{
                    if (e.checkStartDate > 0 || e.checkEndDate > 0 ) {                        
                        $('#payable_head_id').val('');
                        swal("Warning!", "TDS Transfer payable Already Created on selected Date");
                        return false;
                    } else {                       
                        $('#payable_tds_amount').val(e.data);
                        $('#payable_paid_amount').val(e.data); 
                        $('#total_paid_amount').val(e.data);
                    }
                }
                @else
                    $('#payable_tds_amount').val(e.data);
                    $('#payable_paid_amount').val(e.data);
                    // $('#total_paid_amount').val(e.data);
                @endif
            }
        });
    }

    function searchForm() {
        if ($('#tds_payable_filter').valid()) {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var headId = $('#head_id').val();
            var branchId = $('#branch').val();
            var company_id = $('#company_id').val();
            $('#s_date').val(startDate);
            $('#e_date').val(endDate);
            $('#h_id').val(headId);
            $('#b_id').val(branchId);
            $('#isserach').val("yes");
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            tdsPayableListing.draw();
        }
    }
    function resetForm() {
        var form = $("#tds_payable_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#s_date').val('');
        $('#e_date').val('');
        $('#h_id').val('');
        $('#b_id').val('');
        $('#isserach').val("no");
        $('#is_search').val("no");
        $('#start_date').val('');
        $('#end_date').val('');
        $('#head_id').val('');
        $('#branch').val('');
        $('#company_id').val('');
        $(".table-section").addClass("hideTableData");
        tdsPayableListing.draw();
    }

    function doChunkedExport(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.tds_payable.export_tds_payable') !!}",
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

    function doChunkedExport2(start, limit, formData, chunkSize) {
        formData['start'] = start;
        formData['limit'] = limit;
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: "{!! route('admin.export_tds_transafer') !!}",
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
