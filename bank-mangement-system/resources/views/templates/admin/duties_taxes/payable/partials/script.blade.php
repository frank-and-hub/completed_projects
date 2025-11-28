<script type="text/javascript">
    'use strict';
    $('#head_type').on('change', function(event) {
        event.preventDefault();
        formbody();
    });
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $(document).on('click hover', '.download_data', function() {
        var path = $(this).data('path');
        var name = $(this).data('name');
        // Send the POST request
        $.ajax({
            url: "{!! route('admin.tds_payable_chalan.download') !!}",
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
        var imageUrl = "{!! route('admin.tds_payable_chalan.view') !!}?image=" + imageName + "&path=" + imagePath;
        window.open(imageUrl, '_blank');
    });
    $(document).ajaxStart(function() {
        $(".loader").show();
    });
    $(document).ajaxComplete(function() {
        $(".loader").hide();
    });
    var start = $('#created_at').val();
    // $(document).on('click', 'input[name="payable_payment_date"]', function() {
    //     var end = new Date($('input[id="payable_end_date"]').val());
    //     if (!end) {
    //         swal('Warning!', "Please Pick End Date first", 'warning');
    //         return false;
    //     }
    //     console.log(end, 'end');
    //     end.setDate(end.getDate() + 1);
    //     var created_at = $('#created_at').val();

    //     $('input[name="payable_payment_date"]').datepicker({
    //         format: "dd/mm/yyyy",
    //         todayHighlight: false,
    //         autoclose: true,
    //         endDate: created_at,
    //     });
    // });
    $('input[name="payable_payment_date"]').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date()  // Set endDate to current date
    });

    $(document).on('change', '#bank_id', function() {
        var bank_id = $(this).val();
        getAccountNo(bank_id, '#account_id');
    });
    $(document).on('change', '#account_id,#payable_payment_date', function() {
        bank_available_balance();
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

        function parseDate(dateString) {
            var parts = dateString.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }

        jQuery.validator.addMethod("paymentDate", function(value, element, params) {
            var floatValue = parseDate($('#payable_payment_date').val());
            var to = parseDate($('#payable_end_date').val());
            var from = parseDate($('#payable_start_date').val());
            console.log(floatValue, "floatValue", to, "to", from, 'from');
            return (from < floatValue && to < floatValue);
        }, "Date must be between the from and to dates, or equal to the to date.");

        $('#start_date,#end_date,#payable_start_date,#payable_end_date,#transfer_date').hover(function() {
            var created_at = $('#created_at').val();
            $(this).datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                endDate: created_at,
            });
        });
        $.validator.addMethod("decimal", function(value, element, p) {                
            var result = false;
            if (this.optional(element) || /^\d*\.?\d*$/g.test(value) == true) {
                $.validator.messages.decimal = "";
                result = true;
            }
            return result;
        }, "Please enter valid numeric number.");
        $(document).on('change','#company_id',function(){ 
            var company_id = $(this).val();
            $.post("{!! route('admin.bank_list_by_company') !!}",{'company_id':company_id,'_token':csrfToken},function(response) {
                    $('#bank_id').find('option').remove();
                    $('#bank_id').append('<option value="">-- Please Select --</option>');
                    $.each(response.bankList, function (index, value) { 
                        $("#bank_id").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
                    });
                        
                },"JSON");
        });
        
        $('#payable_from').validate({
            rules: {
                // 'payable_start_date': {
                //     required: true
                // },
                // 'payable_end_date': {
                //     required: true
                // },
                'payable_head_id': {
                    required: true
                },
                'payable_amount': {
                    required: true,
                    number: true,
                    gratedZero : true,
                    decimal:true
                },
                'payable_payment_date': {
                    required: true,
                    paymentDate : false
                },
                'head_type': {
                    required: true
                },
                'bank_id': {
                    required: true
                },
                'bank_available_balance':{
                    required: true,
                    gratedZero: true,
                },
                'account_id': {
                    required: true
                },
                'upload_challan': {
                    required: true,
                    accept: "image/jpeg, image/png, image/jpg, image/ico, image/gif, image/svg+xml, application/pdf, image/webp"
                },
                'remark': {
                    required: true
                },
                'transaction_number': {
                    required: true,
                },
                'neft_charge': {
                    number: true,
                    decimal: true,
                    maxlength: 5,
                },
                'payable_late_penalty': {
                    required: true,
                    number: true,
                    decimal: true,
                    maxlength: 5,
                },
                'total_paid_amount': {
                    required: true,
                    number: true,
                    gratedZero: true,
                    decimal:true
                },
                'company_id': {
                    required: true
                },
                'final_payable_amount':{
                    required: true,
                    number: true,
                    gratedZero: true,
                    decimal:true
                },
            },
            messages: {
                'payable_start_date':  {
                    required: 'Please select a start date.'
                },
                'head_type': {
                    required: 'Please select a Head Type.'
                },
                'payable_end_date': {
                    required: 'Please select an end date.'
                },
                'payable_head_id': {
                    required:'Please select a Head.'
                },
                'payable_amount': {
                    required: 'Please enter the amount.',
                    number: 'Please enter a valid amount.'
                },
                'payable_payment_date': {
                    required: 'Please select a payment date.'
                },
                'bank_id': {
                    required: 'Please select a payment bank.'
                },
                'account_id': {
                    required: 'Please select a payment bank account.'
                },
                'upload_challan': {
                    required: 'Please upload a challan.',
                    accept: 'Please upload an image file format (jpeg, png, jpg, ico, gif, svg, pdf, webp) are allowed.'
                },
                'remark': {
                    required: 'Please provide a remark.'
                },
                'transaction_number': {
                    required: 'Please enter a transaction number.'
                },
                'neft_charge': {
                    number: 'Please enter a valid NEFT charge.'
                },
                'payable_late_penalty': {
                    required: 'Please enter the late penalty.',
                    number: 'Please enter a valid late penalty.'
                },
                'total_paid_amount': {
                    required: 'Please enter the total paid amount.',
                    number: 'Please enter a valid total paid amount.',
                    gratedZero: 'The total paid amount must be greater than zero.'
                },
                'company_id': {
                    required: 'Please select a company.'
                }
            }
        });
        $(document).on('click','.submit-payable',function (params) {
            params.preventDefault();
            if($('#payable_from').valid()){
                $(this).prop('disabled', true);
                $('#payable_from').submit();
            }else{
                $(this).prop('disabled', false);
            }
        });
        
        $(document).on('change', '#payable_head_id,#payable_start_date,#payable_end_date,#company_id',
            function() {
                // if($('#tds_transfer_payable_from').valid()){
                loadAmount();
                // }
            });
        @if (isset($head_id))
            $('#company_id').on('mousedown keydown', function(event) {
                event.preventDefault();
            });
        @endif
        $(document).on('keyup', '#payable_late_penalty, #payable_amount, #neft_charge', function() {
            var late_penalty = parseFloat($('input[name="payable_late_penalty"]').val()) || 0,
                amount = parseFloat($('input[name="payable_amount"]').val()) || 0,
                neft_charge = parseFloat($('input[name="neft_charge"]').val()) || 0,
                total_paid_amount = amount + late_penalty,
                final_payable_amount = amount + late_penalty + neft_charge;

            $('#total_paid_amount').val(total_paid_amount.toFixed(2));
            $('#final_payable_amount').val(final_payable_amount.toFixed(2));

            const bank_available_balance = parseFloat($('#bank_available_balance').val()) || 0;

            if (bank_available_balance <= 0) {
                swal('Warning!', 'Please select a bank and account first!', 'warning');
                $('#payable_amount, #neft_charge, #payable_late_penalty').val('');
                return false;
            } else if (bank_available_balance < total_paid_amount) {
                swal('Warning!', 'Total Paid Amount should be less than Bank Available Balance', 'warning');
                $('#payable_amount, #neft_charge, #payable_late_penalty').val('');
            }
        });

        @if ($view == 1)
            $('input').prop('disabled', true).css('color', '#333');
            $('select').css({
                '-webkit-appearance': 'none',
                '-moz-appearance': 'none',
                'appearance': 'none',
                'color': '#333'
            }).prop('disabled', true);
            $('sup').html('');
            $('label').html(function(index, currentText) {
                return currentText.toUpperCase();
            });
        @endif
    });

    function bank_available_balance() {
        var accountId = $('#account_id').val(),
            bankId = $('#bank_id').val(),
            companyId = $('#company_id').val(),
            payable_payment_date = $('#payable_payment_date').val();

        // if (!payable_payment_date) {
        //     swal("Warning!", "Please select a payment date first");
        //     return false;
        // }

        if (accountId && bankId && companyId) {
            $.post("{{ route('admin.get_bank_balance') }}",{'account_id': accountId,'bank_id': bankId,'company_id': companyId,'entry_date': payable_payment_date,'_token':csrfToken}, function(data) {
                    $('#bank_available_balance').val(data);
                },'JSON'
            );
        }
    }

    function loadAmount() {
        return false;
        var startDate = $('input[name="payable_start_date"]').val();
        var endDate = $('input[name="payable_end_date"]').val();
        var headId = $('select[name="payable_head_id"]').val();
        var companyId = $('select[name="company_id"]').val();
        var payableLatePenalty = $('input[name="payable_late_penalty"]').val();
        var totalPaidAmount = $('input[name="total_paid_amount"]').val();
        var date1 = startDate.split('/');
        var newStartDate = date1[2] + '-' + date1[1] + '-' + date1[0];
        var date2 = endDate.split('/');
        var newEndDateDate = date2[2] + '-' + date2[1] + '-' + date2[0];
        var type = $('#head_type').val();
        if (newStartDate > newEndDateDate) {
            $('#payable_head_id').val('');
            $('#payable_end_date').val('');
            swal("Warning!", "End date must be greater than from start date");
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{!! route('admin.duties_taxes.payable') !!}",
            dataType: 'JSON',
            data: {
                'startDate': startDate,
                'endDate': endDate,
                'headId': headId,
                'companyId': companyId,
                'type': type
            },
            success: function(e) {
                var id = e.id;
                if(!id){
                    if(e.checkTDSStartDate > 0 || e.checkTDSEndDate > 0){
                        $('#payable_head_id').val('');
                        swal("Warning!", "Transfer request Already Created on selected Date");
                        return false;
                    }else{
                        // $('#payable_head_id').val('');
                        // swal("Warning!", "Transfer Request Not Created on selected Date");
                        // return false;
                        $('#payable_tds_amount').val(e.data);
                        $('#payable_paid_amount').val(e.data); 
                        $('#total_paid_amount').val(e.data);
                    }
                }else{
                    if (e.checkStartDate > 0 || e.checkEndDate > 0 ) {                        
                        $('#payable_head_id').val('');
                        swal("Warning!", "Transfer payable Already Created on selected Date");
                        return false;
                    } else {
                        $('#payable_tds_amount').val(e.data);
                        $('#payable_paid_amount').val(e.data); 
                        $('#total_paid_amount').val(e.data);
                    }
                }
                $('#payable_amount').val(e.data);
                $('#id').val(id);
            },
            error: function(ex) {
                $('.appendDate').html('');
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

    function capitalizeFirstLetter(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    document.getElementById('head_type').addEventListener('change', function() {
        var formBody = document.getElementById('form_body');
        var formElements = formBody.querySelectorAll('input, select');

        formElements.forEach(function(element) {
            if (element.tagName === 'INPUT' && element.type === 'text') {
                element.value = ''; // Reset input field value
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0; // Reset select element value to the first option
            }
        });
    });
    function formbody() {
        var head_type = $('#head_type').val();
        $('.appendDate').html('');
        $('#form_body').show();
        $('#company_id').empty();
        $('#company_id').append(`<option value="">---- Please Select Company----</option>`);
        if (head_type == '') {
            $('#form_body').hide();
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
                    $('#payable_head_id').empty();
                    $('#payable_head_id').append('<option value="">---- Please Select ----</option>');
                    for (var key in res.tdsHeads) {
                        if (res.tdsHeads.hasOwnProperty(key)) {
                            var optionText = capitalizeFirstLetter(res.tdsHeads[key]);
                            $('#payable_head_id').append(`<option value="${key}">${optionText}</option>`);
                        }
                    }                   
                    for (var k in res.company) {
                        if (res.company.hasOwnProperty(k)) {
                            var optionName = capitalizeFirstLetter(res.company[k]);
                            $('#company_id').append(`<option value="${k}">${optionName}</option>`);
                        }
                    }
                },
                error: function(e) {
                    console.log(e);
                }
            });
        }
    }

    function getAccountNo(id, inputId) {
        $.ajax({
            type: "POST",
            url: "{{ route('admin.getBankAccountNos') }}",
            data: {
                'bank_id': id,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                let data = JSON.parse(response)
                let html = ` <option value="">---- Please Select----</option>`;
                data.forEach(element => {
                    html +=
                        `<option value="${element.id}">${element.account_no}</option>`;
                });
                $(inputId).html(html);
            }
        });
    }
</script>
