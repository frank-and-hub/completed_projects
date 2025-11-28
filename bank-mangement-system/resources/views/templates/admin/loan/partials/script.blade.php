<script type="text/javascript">
    var loanRecoveryTable;
    var loanRequestTable;
    var groupLoanRequestTable;
    var loantable;
    var groupLoanRecoveryTable;
    var loanplantable;
    var member_dob = $('#member_dob').val();
    $(document).ready(function() {

        //jquery for required Asterisk Sign
        $('.required').css('color', 'red');

        $('#ecsRef').validate({
            rules: {
                'ref-text': {
                    required: true,
                    // checkFormatEcs : '#ref-text',    
                    // refNoExist :true,
                    minlength: 20,
                    maxlength: 20,
                    pattern: /^[a-zA-Z]{4}\d{16}$/
                }
            },
            messages: {
                'ref-text': {
                    checkFormatEcs: 'Please enter a valid format like ABCD1234567891011',
                    minlength: 'The value must be 20 characters long.',
                    maxlength: 'The value must be 20 characters long.',
                    pattern: 'The value must start with 4 alphabets followed by 16 digits.'
            }
            
        }
        });


        // $.validator.addMethod("refNoExist", function(value, element, params) {
            
        // var result = true;
        $(document).on('change' , '#ref-text',function(){

        
        var refNo = $('#ref-text').val();
        var loanType = $('#loan_type').val();
        $.ajax({
            type: "POST",  
            url: "{{route('ecs.refNo.exist')}}",  
            data: {
                'refNo': refNo,'loanType':loanType,
            },
            async: false,
            
            success: function (response) {
                console.log(response);
                if(response == 1){

                    swal('Warning','Reference Number Already Exist!','warning');
                    $('#ref-text').val('');
                }
            }
        });

            // return result;
        });


        $.validator.addMethod("checkFormatCustom", function(value, element, p) {
            console.log(value, element, p);
            // Check if the corresponding checkbox is checked
            if ($(p).val() == 1) {
                // Check if the 'element' is defined and has a value
                if (element && element.value !== undefined) {
                    // Validate the format "ABCD1234567891011" without enforcing uppercase
                    if (this.optional(element) || /^[a-zA-Z]{4}\d{16}$/g.test(value)) {
                        return true;
                    } else {
                        $.validator.messages.checkFormatCustom = "Please enter a valid format (4 alphabets followed by 16 digits)";
                        return false;
                    }
                } else {
                    // 'element' is undefined or has no value, consider it invalid
                    return false;
                }
            } else {
                // If the checkbox is not checked, consider it valid
                return true;
            }
        }, "Invalid format");



        // $('.ecsRef').click(function(){
        //     $('#exampleModal').modal('show');
        // });

        // var z = 0;
        $(document).on('click','.ecsRef',function(){
            $('#ref-text').val('');
            $('#old_val').val('');
            var ecsRef = $(this).data('id');
            var refvalue = $(this).data('value');
            if(refvalue){
                $('#ref-text').val(refvalue);
                $('#old_val').val(refvalue);
            }
            // if(z == 0){
            //     z += 1;
                console.log(refvalue,"refvalue");
                $('#ref_id').val(ecsRef);
            // }
        });
        $(document).on('click','.escRefsubmit' , function(){
            const refText = $('#ref-text').val();
            var refId = $('#ref_id').val();
            var createdByName = "Admin"
            var loanType =$('#loan_type').val();
            var oldVal = $('#old_val').val();
            // console.log(refText,refId);
            if($('#ecsRef').valid()){
            $.ajax({
                type:"POST",
                url:"{{ route('admin.loan.refNoStore') }}",
                data:{
                    'refText':refText,
                    'refId':refId,
                    'loanType':loanType,
                    'oldVal':oldVal,
                    'createdByName':createdByName,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res){
                    $('#exampleModal').modal('hide');

                    console.log(res);
                    if(res == "success"){
                        swal('Success!','Reference Number Store Successfully','success');
                    }else{
                        swal('Warning!', 'An error occured');
                    }
                    loanRequestTable.draw();
                }
            }).then((red)=>{
                // if( z > 0){
                //     z -= 1;
                // }
                $('#ref_id').val('');
                $('#ref-text').val('');
            });}
        });
        //Loan Recovery Search Form Validation
        $('#filter').validate({
            rules: {
                'company_id': 'required',
            },
            messages: {
                'company_id': 'Select the company.'
            }
        });

        //Group loans recovery search form validation
        $('#grouploanfilter').validate({
            rules: {
                'company_id': 'required',
            },
            messages: {
                'company_id': 'Select the company.'
            }
        });

        //Group Loan Search Form Validation
        $('#group-loan-filter').validate({
            rules: {
                'company_id': 'required',
                'group_loan_type': 'required'
            },
            messages: {
                'company_id': 'Select the company.',
                'group_loan_type': 'Select the loan type.'
            }
        });

        //Loan Recovery Search Form Validation
        $("#loan_recovery_filter").validate({
            rules: {
                'company_id': 'required',
                'loan_recovery_type': 'required'
            },
            messages: {
                'company_id': 'Select the Company.',
                'loan_recovery_type': 'Select the loan type.'
            }
        });

        //Group Loan Recovery Search Form Validation
        $('#grouploanrecoveryfilter').validate({
            rules: {
                'company_id': 'required',
                'group_loan_recovery_type': 'required'
            },
            messages: {
                'company_id': 'Select the Company.',
                'group_loan_recovery_type': 'Select the loan type.'
            }
        });

        //Loan Transaction Search Form Validation
        $('#transaction-loan-filter').validate({
            rules: {
                'company_id': 'required',
                'transaction_loan_type': 'required'
            },
            messages: {
                'company_id': 'Select the Company.',
                'transaction_loan_type': 'Select the loan type.'
            }
        });

        //Plans fetch as per company id for loan recovery search form
        $(document).on('change', '#company_id', function() {
            // $('#group_loan_recovery_type,#loan_recovery_type,#transaction_loan_type').prop('selectedIndex',-1);
            $('#group_loan_recovery_plan,#loan_recovery_plan,#transaction_loan_plan').empty().append('<option value="">----Select Loan Plan----</option>');
        });

        
        // $(document).on('change','#company_id',function(){ 
        //     $(".loan_plann").val(''); 
            
        //     $('.loan_plann').find('option').remove();
        //     $('.loan_plann').append('<option value="">Select Plan</option>');
        //     var company_id = $(this).val();
        //     var loan_type = $(".loan_typee").find(":selected").val();
        //     if(company_id!='')
        //     {
        //         $.ajax({
        //             type: "POST",  
        //             url: "{!! route('admin.loan.loantype') !!}",
        //             dataType: 'JSON',
        //             data: {'company_id':company_id,
        //             'loan_type':loan_type
        //             },
        //             success: function(response) { 

        //                 $.each(response.loans, function (index, value) { 
        //                     // console.log(response);
        //                         $(".loan_plann").append("<option value='"+value.id+"'>"+value.name+"</option>");
        //                     }); 
        //             }
        //         });
        //     }              
        // });

        $(document).on('change', '.loan_typee', function(){
            $('#company_id').trigger('change');
        });
        //Plans fetch as per company id for Group loan search form
        // $(document).on('change', '#company_id', function() {
        //     $('#group_loan_plan').find('option').remove().end()
        //         .append(' <option value="">----Select Loan Plan----</option>').val('');
        //     var company_id = $('#company_id').val();

        //     $.ajax({
        //         type: "POST",
        //         url: '{{ route('admin.loan.fetch') }}',
        //         dataType: 'JSON',
        //         data: {
        //             'company_id': company_id
        //         },
        //         success: function(e) {
        //             if (e.data != '') {
        //                 $("#group_loan_plan").append(e.data);
        //             }

        //         }
        //     });
        // });

        //Plans fetch as per company id for group loan recovery search form
        // $(document).on('change', '#company_id', function() {
        //     $('#group_loan_recovery_plan').find('option').remove().end()
        //         .append(' <option value="">----Select Loan Plan----</option>').val('');
        //     var company_id = $('#company_id').val();

        //     $.ajax({
        //         type: "POST",
        //         url: '{{ route('admin.loan.fetch') }}',
        //         dataType: 'JSON',
        //         data: {
        //             'company_id': company_id
        //         },
        //         success: function(e) {
        //             if (e.data != '') {
        //                 $("#group_loan_recovery_plan").append(e.data);
        //             }

        //         }
        //     });
        // });

        //Plans fetch as per company id for loan transaction search form
        // $(document).on('change', '#company_id', function() {
        //     $('#transaction_loan_plan').find('option').remove().end()
        //         .append(' <option value="">----Select Loan Plan----</option>').val('');
        //     var company_id = $('#company_id').val();

        //     $.ajax({
        //         type: "POST",
        //         url: '{{ route('admin.loan.fetch') }}',
        //         dataType: 'JSON',
        //         data: {
        //             'company_id': company_id
        //         },
        //         success: function(e) {
        //             if (e.data != '') {
        //                 $("#transaction_loan_plan").append(e.data);
        //             }

        //         }
        //     });
        // });

        $("#loan_type").trigger("change");
        $(function() {
            var today = new Date();
            var member_DOB = new Date(member_dob);
            var difference = today.getFullYear() - member_DOB.getFullYear();
            if (difference > 59) {
                var member_insurance_amount = $('#insurance_amount1').val('0');
                var member_ins_amount = $('#ins_amount').html(difference);
            }
        });
        $.validator.addMethod("lessThanEquals",
            function(value, element, param) {
                var $otherElement = $(param);
                return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
                return value > target.val();
            }, "Amount should be less than OR equals closer amount.");


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




        /**
         * Validator to check input value should be alphabet
         */
        jQuery.validator.addMethod('alphanumeric', function(value, element) {
            return this.optional(element) || value == value.match(/^[a-zA-Z\s]+$/);
        }, "Username must contain only letters.");
        // $('#min_amount').on('keyup',function(){
        //     const maxAmount = $('#max_amount').val();
        //     const MinAmount =  $(this).val();
        //     console.log(MinAmount,maxAmount);
        //     if(parseInt(MinAmount) > parseInt(maxAmount))
        //     {
        //         $('#warning-msg').html('Max Amount Should be Greater Than Minimum Amount');
        //     }
        //     else{
        //         $('#warning-msg').html('');
        //     }
        // })
        $('#loan_emi').validate({ // initialize the plugin
            rules: {
                'application_date': {
                    required: true
                },
                'loan_associate_code': {
                    required: true,
                    number: true
                },
                'loan_emi_payment_mode': 'required',
                'ssb_account': {
                    required: true,
                    number: true
                },
                'deposite_amount': {
                    required: true,
                    number: true,
                    checkAmount: true,
                    lessThanEquals: '#outstanding_amount',
                    decimal:false,
                },
                'transaction_id': {
                    required: true,
                    number: true
                },
                'account_number': {
                    required: true,
                    number: true
                },
                'customer_bank_name': 'required',
                'customer_bank_account_number': {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                'customer_branch_name': {
                    required: true
                },
                'customer_ifsc_code': {
                    required: true,
                    checkIfsc: true
                },
                'company_bank': {
                    required: true
                },
                'company_bank_account_number': {
                    required: true,
                    number: true
                },
                'bank_account_number': {
                    required: true
                },
                'customer_cheque': {
                    required: true,
                    number: true
                },
                'company_bank_account_balance': {
                    required: true
                },
                'bank_transfer_mode': {
                    required: true
                },
                'utr_transaction_number': {
                    required: true
                },
                'online_total_amount': {
                    required: true
                },
                'cheque_id': {
                    required: true
                },
                'cheque_total_amount': {
                    required: true
                },
                'loan_branch': {
                    required: true
                },
                'ssb_account_number':{
                    required: true
                }

            },
            submitHandler: function() {
                // var paymentMode = $('#loan_emi_payment_mode').val();
                // var depositeAmount = $('#deposite_amount').val();
                // var ssbAmount = $("#ssb_account").val();
                // if (paymentMode == 0) {
                //     ssbAmount = ssbAmount - depositeAmount ; 
                //     const mainAmount = 500;                    
                //     if (parseInt(ssbAmount) <= parseInt(mainAmount)) {
                //         $('.ssbamount-error').show();
                //         swal('Warning','Insufficient Saving Balance!','warning');
                //         $("#deposite_amount").val('');
                //         return false;
                //     }
                // }
                var paymentModeVal = $("#loan_emi_payment_mode option:selected").val();
                var depositeAmount = Number($("#deposite_amount").val());
                 var penaltyAmount =0;
                if (paymentModeVal == 0) {
                    var ssbAmount = $("#ssb_account").val();
                    if ((depositeAmount + penaltyAmount) > parseInt(ssbAmount)) {
                        $('.ssbamount-error').show();
                        $('.ssbamount-error').html(
                            'Amount should be less than OR equals current available amounts.');
                        //event.preventDefault();
                        return false;

                    }

                }
                if (paymentModeVal == 3) {
                    var checkAmount = $("#cheque_amount").val();
                    if ((depositeAmount + penaltyAmount) != parseInt(checkAmount)) {
                        $('.ssbamount-error').show();
                        $('.ssbamount-error').html('Amount should be equal to cheque amounts.');
                        return false;
                    }
                } else {
                    $('.ssbamount-error').html('');
                    //return true;
                }
                $('.payloan-emi').prop('disabled', true);
                return true;
            }
        });
        $('#transaction_loan_type').on('change', function() {
            var company_id = $('#company_id').val();
            var loanType = $('#transaction_loan_type').val();
            if (company_id == "") {
                $(this).val('');
                swal('Warning!', 'Please select the company first');
                return false;
            }
            if ($(this).val() == "") {
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('admin.loan.getplanlist') }}",
                dataType: 'JSON',
                data: {
                    'loan_type': loanType,
                    'company_id': company_id,

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // let html = ``;
                    let html = `<option value="">----Select Loan Plan ----</option>`;
                    if (resetForm != "") {
                        response.forEach(element => {
                            html +=
                                `<option value='${element.id}'>${element.name } ( ${element.code} )</option>`;
                        });
                        $("#transaction_loan_plan").html(html);
                    }

                }
            })
            if (loanType != 'G') {
                $('.group_loan_common').hide();
                $('.group_loan_common').val('');
            } else(
                $('.group_loan_common').show()
            )
        })
        $('#loan_recovery_type').on('keyup change', function() {
            var company_id = $('#company_id').val();
            var loanType = $('#loan_recovery_type').val();
            if (company_id == "") {
                $(this).val('');
                swal('Warning!', 'Please select the company first');
                return false;
            }
            if ($(this).val() == "") {
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('admin.loan.getplanlist') }}",
                dataType: 'JSON',
                data: {
                    'loan_type': loanType,
                    'company_id': company_id,

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // let html = ``;
                    let html = `<option value="">----Select Loan Plan----</option>`;
                    if (resetForm != "") {
                        response.forEach(element => {
                            html +=
                                `<option value='${element.id}'>${element.name } ( ${element.code} )</option>`;
                        });
                        $("#loan_recovery_plan").html(html);
                    }

                }
            })
            if (loanType != 'G') {
                $('.group_loan_common').hide();
                $('.group_loan_common').val('');
            } else(
                $('.group_loan_common').show()
            )
        })
        $('#group_loan_recovery_type').on('keyup change', function() {
            var company_id = $('#company_id').val();
            var loanType = $('#group_loan_recovery_type').val();
            if (company_id == "") {
                $(this).val('');
                swal('Warning!', 'Please select the company first');
                return false;
            }
            if ($(this).val() == "") {
                return false;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('admin.loan.getplanlist') }}",
                dataType: 'JSON',
                data: {
                    'loan_type': loanType,
                    'company_id': company_id,

                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // let html = ``;
                    let html = `<option value="">----Select Loan Plan----</option>`;
                    if (resetForm != "") {
                        response.forEach(element => {
                            html +=
                                `<option value='${element.id}'>${element.name } ( ${element.code} )</option>`;
                        });
                        $("#group_loan_recovery_plan").html(html);
                    }

                }
            })
            if (loanType != 'G') {
                $('.group_loan_common').hide();
                $('.group_loan_common').val('');
            } else(
                $('.group_loan_common').show()
            )
        })
        $('.export-loan-transaction').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#loan_transaction_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#transaction-loan-filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportTransactions(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#loan_transaction_export').val(extension);
                $('form#transaction-loan-filter').attr('action', "{!! route('admin.loantransaction.export') !!}");
                $('form#transaction-loan-filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportTransactions(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loantransaction.export') !!}",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportTransactions(start, limit, formData, chunkSize);
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
        $('#filter').validate({ // initialize the plugin
            rules: {
                'loan_account_number': {
                    number: true
                },
                'member_id': {
                    number: true
                },
                'associate_code': {
                    number: true
                },
            }
        });
      
        $('#group-loan-filter').validate({ // initialize the plugin
            rules: {
                'application_number': {
                    number: true,
                    minlength: 8,
                    maxlength: 16
                },
                'group_loan_common_id': {
                    number: true
                },
            }
        });
        $('#grouploanfilter').validate({ // initialize the plugin
            rules: {
                'group_loan_common_id': {
                    number: true
                },
            }
        });
        $('#loan-transfer-form').validate({ // initialize the plugin
            rules: {
                'pay_file_charge': {
                    required: true
                },
                'payment_mode': {
                    required: true
                },
                'ssb_account_number': {
                    required: true,
                    number: true
                },
                'customer_bank_name': {
                    required: true
                },
                'customer_bank_account_number': {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 20
                },
                //'customer_branch_name' : {required: true},
                'customer_ifsc_code': {
                    required: true,
                    checkIfsc: true
                },
                'company_bank': {
                    required: true
                },
                'company_bank_account_number': {
                    required: true
                },
                'company_bank_account_balance': {
                    required: true
                },
                'bank_transfer_mode': {
                    required: true
                },
                'cheque_id': {
                    required: true
                },
                'salary_total_amount': {
                    required: true
                },
                'utr_transaction_number': {
                    required: true
                },
                'rtgs_neft_charge': {
                    required: true
                },
                // 'total_online_amount': {
                //     required: true
                // },
                'customer_bank_account_number': {
                    required: true,
                    number: true
                },
                // 'insurance_amount' : {required: true,number: true},
                // 'date' : {required: true},
            }
        });


        groupLoanRequestTable = $('#group_loan_request_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.grouploan.requestlist') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#group-loan-filter').serializeArray()
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
                    data: 'applicant_id',
                    name: 'applicant_id'
                },
                {
                    data: 'group_loan_id',
                    name: 'group_loan_id'
                },
                {
                    data: 'application_number',
                    name: 'application_number'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'branch_code',
                    name: 'branch_code'
                },
                {
                    data: 'sector',
                    name: 'sector'
                },
                {
                    data: 'region',
                    name: 'region'
                },
                {
                    data: 'zone',
                    name: 'zone'
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
                    data: 'last_recovery_date',
                    name: 'last_recovery_date'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'loan',
                    name: 'loan'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'loan_amount',
                    name: 'loan_amount'
                },
                {
                    data: 'file_charge',
                    name: 'file_charge'
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
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'approve_date',
                    name: 'approve_date'
                },
                {
                    data: 'group_loan_common_id',
                    name: 'group_loan_common_id'
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
            ],"ordering": false,
        });
        $(groupLoanRequestTable.table().container()).removeClass('form-inline');
        $('#loan_branch').on('change', function() {
            $('#bank_transfer_mode').val('');
            $('.cheque-transaction').hide();
        })
        $('#bank_transfer_mode').on('change', function() {
            var companyId = $('#company_id').val();
            var branch = $('option:selected', '#loan_branch').val();
            var paymentMode = $('option:selected', this).val();
            if (paymentMode == '0') {
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.approve_cheque_branchwise') !!}",
                    dataType: 'JSON',
                    data: {
                        'branch_id': branch,'companyId':companyId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response);
                        $('#customer_cheque').find('option').remove();
                        $('#customer_cheque').append(
                            '<option value="">--- Select Cheque ---</option>');
                        if (response.length > 0) {
                            // $('.cheque-transaction').show();
                            var options = $.each(response, function(key, value) {
                                $('#customer_cheque').
                                append('<option value="' + value.id +
                                    '" id="cheque_no">' + value.cheque_no +
                                    '(' + value.amount + ')' + '</option>');
                            })
                            // $('#customer_cheque').append(options);
                        } else {
                            var msg = 'No Cheque';
                            $('#cheque-detail-show').hide();
                            var options =
                                $('#customer_cheque').
                            append('<option value="">' + msg + '</option>');
                            swal("Error!", "No Cheque Found!", "error")
                        }
                    }
                })
            }
        })
        // $('#penalty_amount').on('change', function() {
        //     var penaltyAmount = $(this).val();
        //     var deDate = $('.application_date').val();
        //     var loanId = $('#myID').attr('data-loan-id');
        //     var type = $('#type').val();
        //     if (penaltyAmount > 0) {
        //         $.ajax({
        //             type: "POST",
        //             url: "{!! route('admin.loan.getgstLatePenalty') !!}",
        //             dataType: 'JSON',
        //             data: {
        //                 'loanId': loanId,
        //                 'penaltyAmount': penaltyAmount,
        //                 'loanType': type,
        //                 'deDate': deDate
        //             },
        //             headers: {
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             },
        //             success: function(response) {
        //                 if (response.gstAmount > 0) {
        //                     if (response.label1 && response.label2) {
        //                         $('.gst1').show();
        //                         $('#label1').html(response.label1);
        //                         $('#label2').html(response.label2);
        //                         $('#sgst_amount').val(Math.ceil(response.gstAmount));
        //                         $('#cgst_amount').val(Math.ceil(response.gstAmount));
        //                         $('.gst2').hide();
        //                     } else {
        //                         $('.gst2').show();
        //                         $('#label3').html(response.label1);
        //                         $('#igst_amount').val(Math.ceil(response.gstAmount));
        //                         $('.gst1').hide();
        //                     }
        //                 } else {
        //                     $('.gst1').hide();
        //                     $('.gst2').hide();
        //                 }
        //             }
        //         })
        //     } else {
        //         $('.gst1').hide();
        //         $('.gst2').hide();
        //     }
        // })
        //    $('#date').on('change',function(){
        //        var deDate = $('#date').val();
        //        var loanId = $('.loan_id').val();
        //        var type = $('#type').val();
        //            $.ajax({
        //            type: "POST",
        //            url: "{--!! route('admin.loan.getInsuranceCharge') !!--}",
        //            dataType: 'JSON',
        //            data: {'loanId':loanId,'loanType':type,'deDate':deDate},
        //            headers: {
        //                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //            },
        //            success: function(response) {
        //                 if(response.insurance_charge != null)
        //                 {
        //                     $('#insurance_amount1').val(response.insurance_charge.charge);
        //                     $('#ins_amount').html(Math.ceil(response.insurance_charge.charge));
        //                 }
        //                 else{
        //                     $('#insurance_amount1').val(0);
        //                     $('#ins_amount').html(0);
        //                 }
        //             //    if(response.insurance_charge != null)
        //             //    {
        //             //         $('#insurance_amount').val(response.insurance_charge.charge);
        //             //    }else{
        //             //         $('#insurance_amount').val(0);
        //             //    }
        //            }
        //        })
        //    })
        $('#pay_file_charge').on('change', function() {
            var deDate = $('#date').val();
            const penaltyAmount = $('#insurance_amount1').val() ?? 0;
            var loanId = $('.loan_id').val();
            let gstAmount = 0;
            var gstFileCharge = 0;
            const igstFile = parseFloat($('#igst_file_charge_amount').attr('data') ?? 0).toFixed(2);
            const cgstFile = parseFloat($('#cgst_file_charge_amount').attr('data') ?? 0).toFixed(2);
            const sgstFile = parseFloat($('#sgst_file_charge_amount').attr('data') ?? 0).toFixed(2);
            const igst = parseFloat($('#igst_amount').attr('data') ?? 0).toFixed(2);
            const cgst = parseFloat($('#cgst_amount').attr('data') ?? 0).toFixed(2);
            const sgst = parseFloat($('#sgst_amount').attr('data') ?? 0).toFixed(2);
            var fileChragemethod = $('#pay_file_charge').val() ?? 0;
            var fileChrage = parseFloat($('#file_charge').val()).toFixed(2) ?? 0;
            var transferAmount = parseFloat($('#transfer_amount').attr('data-amount')).toFixed(2) ?? 0;
            if (fileChragemethod == 0 && fileChragemethod.length != '') {
                console.log(transferAmount, fileChrage, penaltyAmount, igst, cgst, sgst, igstFile);
                var newTransferAmount = transferAmount - fileChrage - penaltyAmount - igst - cgst -
                    sgst - igstFile - cgstFile - sgstFile;
            } else if (fileChragemethod == 1) {
                var newTransferAmount = transferAmount;
            } else {
                var newTransferAmount = 0;
            }
            $('#transfer_amount').html(parseFloat(newTransferAmount).toFixed(2));
            //    var type = $('#type').val();
            //        $.ajax({
            //        type: "POST",
            //        url: "{!! route('admin.loan.getgstLatePenalty') !!}",
            //        dataType: 'JSON',
            //        data: {'loanId':loanId,'penaltyAmount':penaltyAmount,'loanType':type,'deDate':deDate},
            //        headers: {
            //            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //        },
            //        success: function(response) {
            //             let gstAmount = 0;
            //             var gstFileCharge = 0;
            //             const igstFile =  parseFloat($('#igst_file_charge_amount').attr('data-amount')).toFixed(2);;
            //             const cgstFile =  parseFloat($('#cgst_file_charge_amount').attr('data-amount')).toFixed(2);;
            //             const sgstFile =  parseFloat($('#sgst_file_charge_amount').attr('data-amount')).toFixed(2);;
            //             var fileChragemethod = $('#pay_file_charge').val();
            //            if(Math.ceil(response.gstAmount) > 0)
            //            {
            //                 let gstAmount = Math.ceil(response.gstAmount);
            //                if(response.label1 && response.label2)
            //                {
            //                     $('.cgst').show();
            //                     $('#cgst_amount').html(Math.ceil(response.gstAmount));
            //                     $('#sgst_amount').html(Math.ceil(response.gstAmount));
            //                     $('#igst_amount').hide();
            //                     $('.igst').hide();
            //                      gstFileCharge = parseFloat($('#cgst_file_charge_amount').attr('data-amount')).toFixed(2);
            //                }
            //                else{
            //                     $('.cgst').hide();
            //                     $('.igst').show();
            //                     $('.icgst').show();
            //                     $('#cgst_amount').hide();
            //                     $('#sgst_amount').hide();
            //                     $('#igst_amount').show();
            //                     $('#igst_amount').html(Math.ceil(response.gstAmount));
            //                      gstFileCharge = parseFloat($('#igst_file_charge_amount').attr('data-amount')).toFixed(2);
            //                }
            //                 var fileChragemethod = $('#pay_file_charge').val();
            //                 var transferAmount = parseFloat($('#transfer_amount').attr('data-amount')).toFixed(2);
            //                 var fileChrage  = parseFloat($('#file_charge').val()).toFixed(2);
            //                 const insuranceAmount = $('#insurance_amount1').val();
            //                 if(fileChragemethod == 0 && fileChragemethod.length != '')
            //                 {
            //                     console.log(transferAmount , fileChrage , insuranceAmount , gstAmount , gstFileCharge);
            //                     var newTransferAmount = transferAmount - fileChrage - insuranceAmount - gstAmount - gstFileCharge;
            //                 }
            //                 else if(fileChragemethod == 1){
            //                     var newTransferAmount = transferAmount;
            //                 }
            //                 else{
            //                     var newTransferAmount = 0;
            //                 }
            //                 $('#transfer_amount').html(parseFloat(newTransferAmount).toFixed(2));
            //            }else if((igstFile != '' || cgstFile != null) && fileChragemethod ==0){
            //             var fileChragemethod = $('#pay_file_charge').val();
            //             var transferAmount = parseFloat($('#transfer_amount').attr('data-amount')).toFixed(2);
            //             var fileChrage  = parseFloat($('#file_charge').val()).toFixed(2);
            //             insuranceAmount = $('#insurance_amount1').val();
            //             if(igstFile != '')
            //             {
            //                 var newTransferAmount = transferAmount - fileChrage - insuranceAmount -igstFile ;
            //             }
            //             else{
            //                 var newTransferAmount = transferAmount - fileChrage - insuranceAmount -cgstFile-sgstFile ;
            //             }
            //             $('#transfer_amount').html(parseFloat(newTransferAmount).toFixed(2));
            //            }else{
            //                 insuranceAmount = $('#insurance_amount1').val();
            //                     $('#cgst_amount').hide();
            //                     $('#sgst_amount').hide();
            //                     $('#igst_amount').hide();
            //                 var transferAmount = parseFloat($('#transfer_amount').attr('data-amount')).toFixed(2);
            //                 var fileChrage  = parseFloat($('#file_charge').val()).toFixed(2);
            //                 if(fileChragemethod == 0 && fileChragemethod.length != '')
            //                 {
            //                     var newTransferAmount = transferAmount - fileChrage - insuranceAmount  ;
            //                 }
            //                 else if(fileChragemethod == 1){
            //                     var newTransferAmount = transferAmount;
            //                 }
            //                 else{
            //                     var newTransferAmount = 0;
            //                 }
            //                 $('#transfer_amount').html(parseFloat(newTransferAmount).toFixed(2));
            //            }
            //        }
            //    })
        })
        loanRecoveryTable = $('#loan_recovery_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.recovery_list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#loan_recovery_filter').serializeArray()
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
                    data: 'branch_code',
                    name: 'branch_code'
                },
                // {
                //     data: 'sector_name',
                //     name: 'sector_name'
                // },
                // {
                //     data: 'region_name',
                //     name: 'region_name'
                // },
                // {
                //     data: 'zone_name',
                //     name: 'zone_name'
                // },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'plan_name',
                    name: 'plan_name'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'transfer_amount',
                    name: 'transfer_amount'
                },
                {
                    data: 'loan_amount',
                    name: 'loan_amount'
                },
                {
                    data: 'file_charge',
                    name: 'file_charge'
                },
                {
                    data: 'igst_file_charge',
                    name: 'igst_file_charge'
                },
                {
                    data: 'cgst_file_charge',
                    name: 'cgst_file_charge'
                },
                {
                    data: 'sgst_file_charge',
                    name: 'sgst_file_charge'
                },
                {
                    data: 'insurance_charge',
                    name: 'insurance_charge'
                },
                {
                    data: 'igst_insurance_charge',
                    name: 'igst_insurance_charge'
                },
                {
                    data: 'cgst_insurance_charge',
                    name: 'cgst_insurance_charge'
                },
                {
                    data: 'sgst_insurance_charge',
                    name: 'sgst_insurance_charge'
                },
                {
                    data: 'file_charges_payment_mode',
                    name: 'file_charges_payment_mode'
                },
                {
                    data: 'outstanding_amount',
                    name: 'outstanding_amount'
                },
                {
                    data: 'last_recovery_date',
                    name: 'last_recovery_date'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'total_payment',
                    name: 'total_payment'
                },
                {
                    data: 'approved_date',
                    name: 'approved_date'
                },
                {
                    data: 'sanction_date',
                    name: 'sanction_date'
                },
                {
                    data: 'application_date',
                    name: 'application_date'
                },
                {
                    data: 'collectorcode',
                    name: 'collectorcode'
                },
                {
                    data: 'collectorname',
                    name: 'collectorname'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],"ordering": false,
        });
        $(loanRecoveryTable.table().container()).removeClass('form-inline');

        //Listing of the Group Loan Recovery Page
        groupLoanRecoveryTable = $('#group_loan_recovery_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.grouploan.recovery_list') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#grouploanrecoveryfilter').serializeArray()
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
                // {
                //     data: 'branch_code',
                //     name: 'branch_code'
                // },
                // {
                //     data: 'sector_name',
                //     name: 'sector_name'
                // },
                // {
                //     data: 'region_name',
                //     name: 'region_name'
                // },
                // {
                //     data: 'zone_name',
                //     name: 'zone_name'
                // },
                {
                    data: 'group_loan_id',
                    name: 'group_loan_id'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'plan_name',
                    name: 'plan_name'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'loan_amount',
                    name: 'loan_amount'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'file_charge',
                    name: 'file_charge'
                },
                {
                    data: 'igst_file_charge',
                    name: 'igst_file_charge'
                },
                {
                    data: 'cgst_file_charge',
                    name: 'cgst_file_charge'
                },
                {
                    data: 'sgst_file_charge',
                    name: 'sgst_file_charge'
                },
                {
                    data: 'insurance_charge',
                    name: 'insurance_charge'
                },
                {
                    data: 'igst_insurance_charge',
                    name: 'igst_insurance_charge'
                },
                {
                    data: 'cgst_insurance_charge',
                    name: 'cgst_insurance_charge'
                },
                {
                    data: 'sgst_insurance_charge',
                    name: 'sgst_insurance_charge'
                },
                {
                    data: 'file_charges_payment_mode',
                    name: 'file_charges_payment_mode'
                },
                {
                    data: 'outstanding_amount',
                    name: 'outstanding_amount'
                },
                {
                    data: 'last_recovery_date',
                    name: 'last_recovery_date'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'total_payment',
                    name: 'total_payment'
                },
                {
                    data: 'approved_date',
                    name: 'approved_date'
                },
                {
                    data: 'sanction_date',
                    name: 'sanction_date'
                },
                {
                    data: 'application_date',
                    name: 'application_date'
                },
                {
                    data: 'collectorcode',
                    name: 'collectorcode'
                },
                {
                    data: 'collectorname',
                    name: 'collectorname'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],"ordering": false,
        });
        $(groupLoanRecoveryTable.table().container()).removeClass('form-inline');
        var loanId = $('#loanId').val();
        var loanType = $('#loanType').val();
        var loanEmiTable = $('#listtansaction').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [100, 200, 300, 400, 500],

            pageLength: 300,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.emi_list') !!}",
                "type": "POST",
                "data": function(d, oSettings) {
                    if (oSettings.json != null) {
                        var am = (oSettings.json.totalAmount);
                        // var total = oSettings.json.total;
                    } else {
                        var am = 0;
                    }
                    var page = ($('#listtansaction').DataTable().page.info());
                    var currentPage = page.page + 1;
                    d.pages = currentPage,
                        d.loanId = loanId,
                        d.loanType = loanType,
                        d.total = am
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
                    data: 'description',
                    name: 'descsription'
                },
                {
                    data: 'sanction_amount',
                    name: 'sanction_amount'
                },
                {
                    data: 'deposite',
                    name: 'deposite'
                },
                {
                    data: 'roi_amount',
                    name: 'roi_amount'
                },
                {
                    data: 'jv_amount',
                    name: 'jv_amount'
                },
                {
                    data: 'principal_amount',
                    name: 'principal_amount'
                },
                {
                    data: 'opening_balance',
                    name: 'opening_balance'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
            ],"ordering": false,
        });
        $(loanEmiTable.table().container()).removeClass('form-inline');
        var loanId = $('#loanId').val();
        var loanType = $('#loanType').val();
        var loanEmiTable = $('#listtansactiondeposit').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [100, 200, 300, 400, 500],

            pageLength: 300,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.deposit.emi_list') !!}",
                "type": "POST",  
                "data": function (d,oSettings) {
						d.loanId = loanId;
						if(oSettings.json != null){
                            totalAmount = oSettings.json.total;
						}else{
							totalAmount = 0;
						}
						d.loanType = loanType;
						var page = ($('#listtansactiondeposit').DataTable().page.info());
						var currentPage  = page.page+1;
						d.pages = currentPage,
						d.total = totalAmount
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
                    data: 'payment_mode',
                    name: 'payment_mode'
                },
                {
                    data: 'description',
                    name: 'descsription'
                },
                {
                    data: 'sanction_amount',
                    name: 'sanction_amount'
                },
                // {
                //     data: 'penalty',
                //     name: 'penalty'
                // },
                {
                    data: 'deposite',
                    name: 'deposite'
                },
                {
                    data: 'jv_amount',
                    name: 'jv_amount'
                },
                {
                    data: 'igst_charge',
                    name: 'igst_charge'
                },
                {
                    data: 'cgst_charge',
                    name: 'cgst_charge'
                },
                {
                    data: 'sgst_charge',
                    name: 'sgst_charge'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
                // {data: 'principal_amount', name: 'principal_amount'},
                // {data: 'opening_balance', name: 'opening_balance'},
            ],"ordering": false,
		});
        $(loanEmiTable.table().container()).removeClass('form-inline');
		/*
		loanEmiTable.on('draw', function() {
			var balanceColumn = loanEmiTable.column('balance:name');
			var lastBalance = balanceColumn.data().toArray().pop();
			$('#total').val(lastBalance);
		});
		*/
        $("#name").keyup(function() {
            var Text = $(this).val();
            Text = Text.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
            $("#slug").val(Text);
        });
        $('#loan-reject-form').validate({ // initialize the plugin
            rules: {
                'rejection': 'required',
            },
        });
        $(document).on('change', '#company_bank', function() {
            var account = $('option:selected', this).val();
            $('#company_bank_account_number').val('');
            $('#bank_account_number').val('');
            $('.c-bank-account').hide();
            $('.' + account + '-bank-account').show();
            $('#company_bank_account_balance').val('');
        });
        $(document).on('change', '#company_bank_account_number', function() {
            var account = $('option:selected', this).attr('data-account');
            $('#cheque_id').val('');
            $('.c-cheque').hide();
            $('.' + account + '-c-cheque').show();
            var date = $('#date').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.loan.getbankdaybookamount') !!}",
                dataType: 'JSON',
                data: {
                    'fromBankId': account,
                    'date': date
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#company_bank_account_balance').val(response.bankDayBookAmount);
                }
            });
        });
        $(document).on('change', '#bank_account_number', function() {
            var account = $('option:selected', this).attr('data-account');
            $('#cheque_id').val('');
            $('.c-cheque').hide();
            $('.' + account + '-c-cheque').show();
        });
        $(document).on('change', '#rtgs_neft_charge', function() {
            var account = $(this).val();
            var fileCharge = $('#file_charge').val();
            var ins = $('#insurance_amount1').val();
            var transferType = $('option:selected', '#pay_file_charge').val();
            var bankTransferMode = $('option:selected', '#bank_transfer_mode').val();
            var tAmount = $('#online_total_amount').val();
            const traAmount = $('#transfer_amount').text();
            if (bankTransferMode == 1) {
                if (transferType == 0) {
                    if (account > 0) {
                        var accountVal = account;
                    } else {
                        var accountVal = 0;
                    }
                    if (tAmount > 0) {
                        var tAmountVal = tAmount;
                    } else {
                        var tAmountVal = 0;
                    }
                    if (fileCharge > 0) {
                        var fileChargeVal = fileCharge;
                    } else {
                        var fileChargeVal = 0;
                    }
                    if (ins > 0) {
                        var insVal = ins;
                    } else {
                        var insVal = 0;
                    }
                    // $('#total_online_amount').val(parseFloat(traAmount));
                } else {
                    if (account > 0) {
                        var accountVal = account;
                    } else {
                        var accountVal = 0;
                    }
                    if (tAmount > 0) {
                        var tAmountVal = tAmount;
                    } else {
                        var tAmountVal = 0;
                    }
                    // $('#total_online_amount').val(parseFloat(traAmount));
                }
            } else {
                // $('#total_online_amount').val(0);
            }
        });
        $(document).on('change', '#pay_file_charge', function() {
            $('#rtgs_neft_charge').trigger('change');
        });
        $(document).on('change', '#insurance_amount1', function() {
            $('#rtgs_neft_charge').trigger('change');
        });
        $('#loan_emi_payment_mode').on('change', function() {
            var paymentMode = $('option:selected', this).val();
            var depositeAmount = $('#deposite_amount').val();
            var ssb_AccountNumber = $('#ssb_account_number').val();
            var date = $('#date').val();
            if (date == '') {
                var branch = $('#loan_emi_payment_mode').val('');
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false;
            }
              
            if (paymentMode == 0 /* && paymentMode != ''*/ ) {
                $('.ssb-account').show();
                $('.other-bank').hide();
                // var ssbAccount = $('#ssbaccount').val();
                $('#ssb_account_number').show();
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
                if (ssb_AccountNumber > 0) {
                    $.ajax({
                        type: "POST",
                        url: "{--!! route('admin.investment.planform_saving_account') !!--}",
                        data: {
                            'account_no': ssb_AccountNumber
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.data == 0) {
                                swal("Warning",
                                    "You can not pay with inactive SSB account, Please select other payment mode for payment!",
                                    "warning");
                                $("#loan_emi_payment_mode option:selected").prop("selected",
                                    false);
                                return false;
                            }
                            if (response.data == 2) {
                                swal("Error", "Member dose not have SSB Account !",
                                    "error");
                                $("#payment-mode option:selected").prop("selected", false);
                            }
                        }
                    });
                }
            } else if (paymentMode == 1 && paymentMode != '') {
                $('.ssb-account').hide();
                $('.other-bank').show();
                $('#company_bank_detail').hide('');
            } else {
                $('.ssb-account').hide();
                $('.other-bank').hide();
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            }
            $('.cheque-transaction').hide();
            $('.online-transaction').hide();
        });
        $(document).on('change', '#date', function() {
            var transferType = $('option:selected', '#payment_mode').val();
            var type = $('#type').val();
            var date = $('#date').val();
            var ssbCreatedDate = $('#ssb_created_date').val();
            if (transferType == 0 && transferType != '') {
                var dString = date.split("/");
                var nDate = dString[2] + '-' + dString[1] + '-' + dString[0];
                if (new Date(ssbCreatedDate) > new Date(nDate)) {
                    $('#payment_mode').val('');
                    $('#ssb_account_number').val('');
                    $('#payment_mode').trigger('change');
                    swal("Warning!", "SSB account not created at this date!", "warning");
                    return false;
                }
            }
        });
        $('#date').on('change', function() {
            const branch = $('#branchid').val();
            const sancDate = $('#date').val();
            const sancAmount = $('#amount').val();
            var payment_mode = $('option:selected', '#payment_mode').val();
            if (payment_mode == 2) {
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.withdraw.getdaybookdata') !!}",
                    dataType: 'JSON',
                    data: {
                        date: sancDate,
                        branchId: branch
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response.microAmount, sancAmount, date, branch);
                        if (response.microAmount < sancAmount) {
                            swal("Warning!", "Insufficient balance!", "warning");
                            $('#date').val('');
                            $('#payment_mode').val('');
                        }
                    }
                })
            }
        })
        $('#payment_mode').on('change', function() {
            var paymentMode = $('option:selected', this).val();
            var date = $('#date').val();
            var ssbCreatedDate = $('#ssb_created_date').val();
            if (date == '') {
                var branch = $('#payment_mode').val('');
                swal("Warning!", "Please select a transfer date first!", "warning");
                return false;
            }
            if (paymentMode == 0 && paymentMode != '') {
                var ssbAccount = $('#ssbaccount').val();
                var dString = date.split("/");
                var nDate = dString[2] + '-' + dString[1] + '-' + dString[0];
                if (new Date(ssbCreatedDate) > new Date(nDate)) {
                    $('#payment_mode').val('');
                    $('.ssb-transfer').hide();
                    $('.other-bank').hide();
                    $('#ssb_account_number').val('');
                    $('#ssb_account_number').attr('readonly', false);
                    $('#customer_bank_name').val('');
                    $('#customer_bank_account_number').val('');
                    $('#customer_branch_name').val('');
                    $('#customer_ifsc_code').val('');
                    $('#company_bank').val('');
                    $('#company_bank_account_number').val('');
                    $('#company_bank_account_balance').val('');
                    $('#bank_transfer_mode').val('');
                    $('#cheque_id').val('');
                    $('#total_amount').val('');
                    $('#utr_transaction_number').val('');
                    $('#total_amount').val('');
                    $('#rtgs_neft_charge').val('');
                    // $('#total_online_amount').val('');
                    $('.cheque-transaction').hide();
                    $('.online-transaction').hide();
                    swal("Warning!", "SSB account not created at this date!", "warning");
                    return false;
                }
                $('#ssb_account_number').val(ssbAccount);
                $('.ssb-transfer').show();
                $('.other-bank').hide();
                $('#ssb_account_number').attr('readonly', true);
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#company_bank_detail').hide();
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            } else if (paymentMode == 1 && paymentMode != '') {
                $('.ssb-transfer').hide();
                $('.other-bank').show();
                $('#company_bank_detail').show();
                $('#ssb_account_number').val('');
                $('#ssb_account_number').attr('readonly', false);
            } else {
                const branch = $('#branchid').val();
                const sancDate = $('#date').val();
                const sancAmount = $('#amount').val();
                $.ajax({
                    type: 'POST',
                    url: "{!! route('admin.withdraw.getdaybookdata') !!}",
                    dataType: 'JSON',
                    data: {
                        date: sancDate,
                        branchId: branch
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response.microAmount, sancAmount, date, branch);
                        if (response.microAmount < sancAmount) {
                            swal("Warning!", "Insufficient balance!", "warning");
                            $('#date').val('');
                            $('#payment_mode').val('');
                        }
                    }
                })
                $('.ssb-transfer').hide();
                $('.other-bank').hide();
                $('#ssb_account_number').val('');
                $('#ssb_account_number').attr('readonly', false);
                $('#company_bank_detail').hide();
                $('#customer_bank_name').val('');
                $('#customer_bank_account_number').val('');
                $('#customer_branch_name').val('');
                $('#customer_ifsc_code').val('');
                $('#company_bank').val('');
                $('#company_bank_account_number').val('');
                $('#company_bank_account_balance').val('');
                $('#bank_transfer_mode').val('');
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            }
            $('.cheque-transaction').hide();
            $('.online-transaction').hide();
        });
        // $('#payment_mode').on('change',function(){
        //     var paymentMode = $('option:selected', this).val();
        //     var date = $('#date').val();
        //     var ssbCreatedDate = $('#ssb_created_date').val();
        //     if(date == ''){
        //         var branch = $('#payment_mode').val('');
        //         swal("Warning!", "Please select a transfer date first!", "warning");
        //         return false;
        //     }
        //     if(paymentMode == 0 && paymentMode != ''){
        //         var ssbAccount = $('#ssbaccount').val();
        //         var dString = date.split("/");
        //         var nDate = dString[2]+'-'+dString[1]+'-'+dString[0];
        //         if(new Date(ssbCreatedDate) > new Date(nDate)){
        //             $('#payment_mode').val('');
        //             $('.ssb-transfer').hide();
        //             $('.other-bank').hide();
        //             $('#ssb_account_number').val('');
        //             $('#ssb_account_number').attr('readonly', false);
        //             $('#customer_bank_name').val('');
        //             $('#customer_bank_account_number').val('');
        //             $('#customer_branch_name').val('');
        //             $('#customer_ifsc_code').val('');
        //             $('#company_bank').val('');
        //             $('#company_bank_account_number').val('');
        //             $('#company_bank_account_balance').val('');
        //             $('#bank_transfer_mode').val('');
        //             $('#cheque_id').val('');
        //             $('#total_amount').val('');
        //             $('#utr_transaction_number').val('');
        //             $('#total_amount').val('');
        //             $('#rtgs_neft_charge').val('');
        //             $('#total_online_amount').val('');
        //             $('.cheque-transaction').hide();
        //             $('.online-transaction').hide();
        //             swal("Warning!", "SSB account not created at this date!", "warning");
        //             return false;
        //         }
        //         $('#ssb_account_number').val(ssbAccount);
        //         $('.ssb-transfer').show();
        //         $('.other-bank').hide();
        //         $('#ssb_account_number').attr('readonly', true);
        //         $('#customer_bank_name').val('');
        //         $('#customer_bank_account_number').val('');
        //         $('#customer_branch_name').val('');
        //         $('#customer_ifsc_code').val('');
        //         $('#company_bank').val('');
        //         $('#company_bank_account_number').val('');
        //         $('#company_bank_account_balance').val('');
        //         $('#bank_transfer_mode').val('');
        //          $('#company_bank_detail').hide();
        //         $('#cheque_id').val('');
        //         $('#total_amount').val('');
        //         $('#utr_transaction_number').val('');
        //         $('#total_amount').val('');
        //         $('#rtgs_neft_charge').val('');
        //         $('#total_online_amount').val('');
        //     }else if(paymentMode == 1 && paymentMode != ''){
        //         $('.ssb-transfer').hide();
        //         $('.other-bank').show();
        //          $('#company_bank_detail').show();
        //         $('#ssb_account_number').val('');
        //         $('#ssb_account_number').attr('readonly', false);
        //     }else{
        //         $('.ssb-transfer').hide();
        //         $('.other-bank').hide();
        //         $('#ssb_account_number').val('');
        //         $('#ssb_account_number').attr('readonly', false);
        //         $('#company_bank_detail').HIDE();
        //         $('#customer_bank_name').val('');
        //         $('#customer_bank_account_number').val('');
        //         $('#customer_branch_name').val('');
        //         $('#customer_ifsc_code').val('');
        //         $('#company_bank').val('');
        //         $('#company_bank_account_number').val('');
        //         $('#company_bank_account_balance').val('');
        //         $('#bank_transfer_mode').val('');
        //         $('#cheque_id').val('');
        //         $('#total_amount').val('');
        //         $('#utr_transaction_number').val('');
        //         $('#total_amount').val('');
        //         $('#rtgs_neft_charge').val('');
        //         $('#total_online_amount').val('');
        //     }
        //     $('.cheque-transaction').hide();
        //     $('.online-transaction').hide();
        // });
        $("#loan-transfer-form").submit(function(event) {
            var transferType = $('option:selected', '#payment_mode').val();
            var cAmount = $('#company_bank_account_balance').val();
            var cBank = $('#company_bank').val();
            if (transferType == 1) {
                var mode = $('option:selected', '#bank_transfer_mode').val();
                if (mode == 0) {
                    var amount = $('#cheque_total_amount').val();
                } else {
                    // var amount = $('#total_online_amount').val();
                }
                // Changes By Anup SIr = 01-09-2022 (Aman jain )
                //https://pm.w3care.com/projects/1892/tasks/45618
                if (cBank != 2) {
                    if (parseInt(amount) > parseInt(cAmount)) {
                        swal("Warning!", "Insufficient balance!", "warning");
                        event.preventDefault();
                    }
                }
            } else {
                return true;
            }
        });
        $('#submit').on('click', function() {
            var ssbAccount = $('#ssb_account_number').val();
            var transferType = $('option:selected', '#payment_mode').val();
            if (ssbAccount == '' && transferType == 0) {
                swal("Warning!", "Please Create SSB Account First!!", "warning");
                return false;
            }
        })
        $("#loan_emi").submit(function(event) {
            var transferType = $('option:selected', '#loan_emi_payment_mode').val();
            var cAmount = $('#company_bank_account_balance').val();
            if (transferType == 1) {
                var mode = $('option:selected', '#bank_transfer_mode').val();
                if (mode == 0) {
                    var amount = $('#cheque_total_amount').val();
                } else {
                    var amount = $('#online_total_amount').val();
                }
                if (parseInt(amount) > parseInt(cAmount)) {
                    swal("Warning!", "Insufficient balance!", "warning");
                    event.preventDefault();
                }
            } else {
                return true;
            }
        });
        $('#deposite_amount').on('change', function() {
            if ($('#deposite_amount').val()) {
                var depositAmount = $('#deposite_amount').val();
            } else {
                var depositAmount = 0;
            }
            // if ($('#penalty_amount').val()) {
            //     var penaltyAmount = $('#penalty_amount').val();
            // } else {
            //     var penaltyAmount = 0;
            // }
            if (depositAmount > 0) {
                var depositAmountVal = depositAmount;
            } else {
                var depositAmountVal = 0;
            }
            // if (penaltyAmount > 0) {
            //     var penaltyAmountVal = penaltyAmount;
            // } else {
                // }
            var penaltyAmountVal = 0;
            $('#cheque_total_amount').val(parseFloat(depositAmountVal) + parseFloat(penaltyAmountVal));
            // $('#total_online_amount').val(parseFloat(depositAmountVal) + parseFloat(penaltyAmountVal));
        });
        $('#bank_transfer_mode').on('change', function() {
            var bankTransferMode = $('option:selected', this).val();
            if (bankTransferMode == 0 && bankTransferMode != '') {
                $('.cheque-transaction').show();
                $('.online-transaction').hide();
                $('#company_bank_detail').hide();
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
            } else if (bankTransferMode == 1 && bankTransferMode != '') {
                let branchId = $('#branch').val();
                            let company_id = $('#companyId').val();

                            let date = $('#date').val();
                            // $.ajax({
                            //     type: "POST",
                            //     url: "{{ route('branch.getbranchbankbalanceamount') }}",
                            //     dataType: 'JSON',
                            //     data: {
                            //         'branch_id': branchId,
                            //         'entrydate': date,
                            //         'company_id': company_id
                            //     },
                            //     headers: {
                            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            //     },
                            //     success: function(response) {
                            //         // alert(response.balance);
                            //         $('#micro_daybook_amount').val(response.balance);
                            //     }
                            // });
                            
                            $.ajax({
                                    type: "POST",
                                    url: "{{ route('admin.fetchbranchbycompanyid') }}",
                                    data: {
                                        'company_id': company_id,
                                        'bank': 'true',
                                        'branch': 'no',
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(response) {
                                        let myObj = JSON.parse(response);
                                        if (myObj.bank) {
                                            var optionBank = `<option value="">----Please Select----</option>`;
                                            myObj.bank.forEach(element => {
                                                optionBank +=
                                                    `<option value="${element.id}">${element.bank_name}</option>`;
                                            });
                                            $('#company_bank').html(optionBank);
                                        }
                                    }
                                });
                $('.online-transaction').show();
                $('.cheque-transaction').hide();
                $('#cheque-detail-show').hide();
                $('#company_bank_detail').show();
                $('#cheque_id').val('');
                $('#total_amount').val('');
            } else {
                $('.online-transaction').hide();
                $('.cheque-transaction').hide();
                $('#cheque_id').val('');
                $('#total_amount').val('');
                $('#utr_transaction_number').val('');
                $('#total_amount').val('');
                $('#rtgs_neft_charge').val('');
                // $('#total_online_amount').val('');
                $('#company_bank_detail').hide();
            }
        });

        $('#company_bank').change(function() {
                            var bankId = $(this).val();
                            $.ajax({
                                type: "POST",
                                url: "{{ route('admin.getBankAccountNos') }}",
                                data: {
                                    'bank_id': bankId,
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    let data = JSON.parse(response)
                                    let html = ` <option value="">---- Please Select----</option>`;
                                    data.forEach(element => {
                                        html +=
                                            `<option value="${element.account_no}">${element.account_no}</option>`;
                                    });
                                    $('#bank_account_number').html(html);
                                    //             $('#from_Bank_account_no').html(`
                    // <option value="">---- Please Select----</option>
                    // <option  value="${response.account_no}">${response.account_no}</option>
                    // `);
                                }
                            });

                        });
        $('#cheque_number').on('change', function() {
            var chequeDate = $('option:selected', this).attr('data-cheque-date');
            var chequeAmount = $('option:selected', this).attr('data-cheque-amount');
            var first_date = moment("" + chequeDate + "").format('DD/MM/YYYY');
            $('#cheque_date').val(first_date);
            $('#cheque_amount').val(chequeAmount);
        });
        $(document).on('change', '#customer_cheque', function() {
            var cheque_id = $('option:selected', this).val();
            var deposite_amount = parseFloat($('#deposite_amount').val()).toFixed(2);
            $.ajax({
                type: "POST",
                url: "{!! route('admin.approve_cheque_details') !!}",
                dataType: 'JSON',
                data: {
                    'cheque_id': cheque_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log(deposite_amount);
                    console.log(parseFloat(response.amount).toFixed(0));
                    console.log(parseFloat(response.amount).toFixed(2));
                    if (deposite_amount != parseFloat(response.amount).toFixed(2)) {
                        swal('Error!', 'Cheque Amount Should be Equal to Deposite Amount',
                            'error');
                        $('#customer_cheque').val('');
                    } else {
                        $('#customer_bank_name').val(response.bank_name);
                        $('#customer_branch_name').val(response.branch_name);
                        $('#cheque-date').val(response.cheque_create_date);
                        $('#cheque-amount').val(parseFloat(response.amount).toFixed(2));
                        $('#cheque_company_bank').val(response.deposit_bank_name);
                        $('#company_bank_account_number').val(response.deposite_bank_acc);
                        $('#cheque-detail-show').show();
                    }
                }
            });
        });
        $('#bank_name').on('change', function() {
            var accountNumber = $('option:selected', this).attr('data-account-number');
            $('#account_number').val(accountNumber);
        });
        $('#loan_associate_code').on('change', function() {
            var associateCode = $(this).val();
            var applicationDate = $('.application_date').val();
            $.ajax({
                type: "POST",
                url: "{!! route('admin.loan.getcollectorassociate') !!}",
                dataType: 'JSON',
                data: {
                    'code': associateCode,
                    'applicationDate': applicationDate
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.msg_type == 'success') {
                        var firstName = response.collectorDetails.first_name ? response
                            .collectorDetails.first_name : '';
                        var lastName = response.collectorDetails.last_name ? response
                            .collectorDetails.last_name : '';
                        $('#associate_member_id').val(response.collectorDetails.id);
                        $('#loan_associate_name').val(firstName + ' ' + lastName);
                        //$('#ssb_account_number').val(response.collectorDetails.saving_account[0].account_no);
                        //$('#ssb_account').val(response.ssbAmount);
                        // $('#ssb_id').val(response.collectorDetails.saving_account[0].id);
                    } else if (response.msg_type == 'error') {
                        $('#loan_associate_code').val('');
                        $('#associate_member_id').val('');
                        $('#loan_associate_name').val('');
                        $('#ssb_account_number').val('');
                        $('#ssb_account').val('');
                        $('#ssb_id').val('');
                        swal("Error!", "Associate Code does not exists!", "error");
                    }
                }
            });
        });
        $('.application_date').on('change', function() {
            var currentEmiDate = $(this).val();
            var emiDate = $('#myID').attr('data-allemidate')
            var emiOpion = $('#myID').attr('emiOption')
            var title = $('#myID').attr('title')
            var emiDate = emiDate.split(',');
            var splitDate = currentEmiDate.split('/');
            var newEmidate = splitDate[2] + '-' + splitDate[1] + '-' + splitDate[0];
            console.log(emiOpion);
            // if(emiOpion == 1)
            // {
            //     if(title !== 'Pay Advanced EMI')
            // {
            //     if(emiDate.includes(newEmidate) == false)
            //     {
            //         swal('Sorry','Please Use Advance Payment or Emi date Should be Correct','error');
            //         $(this).val('');
            //         return false;
            //     }
            // }
            // else{
            //     if(emiDate.includes(newEmidate) == true)
            //     {
            //         swal('Sorry','Please Use  Pay Emi or Emi date Should be Correct','error');
            //         $(this).val('');
            //         return false;
            //     }
            // }
            // }
            // $('#penalty_amount').trigger('change')
        })
        $(document).on('click', '.pay-emi', function(e) {
            $('.gst1').hide();
            $('.gst2').hide();
            var loanId = $(this).attr('data-loan-id');
            var companyId = $(this).attr('data-company-id');
            var emiDates = $(this).attr('data-allemidate');
            var emiOption = $(this).attr('data-emioption');
            var branchId = $(this).attr('data-branch-id');

            var oustandingAmount = $(this).attr('data-outstanding_amount'); 
            console.log(oustandingAmount);
            $('#outstanding_amount').val(oustandingAmount);
            emiDates = emiDates.split('/');
            $('.pay-emi').removeAttr('id');
            $(this).removeAttr('id');
            $(this).attr('id', 'myID');
            $(this).attr('data-allemidate', emiDates);
            $(this).attr('emiOption', emiOption);
            var loanEMI = $(this).attr('data-loan-emi');
            var EmiDatesAll = $(this).attr('data-allemidate');
            var title = $(this).attr('title')
            var ssbAmount = $(this).attr('data-ssb-amount');
            var ssbAccount = $(this).attr('data-ssb-account');
            var ssbId = $(this).attr('data-ssb-id');
            var recoveredAmount = $(this).attr('data-recovered-amount');
            var lastRecoveredAmount = $(this).attr('data-last-recovered-amount');
            var closingAmount = $(this).attr('data-closing-amount');
            var dueAmount = $(this).attr('data-due-amount');
            var penaltyAmount = $(this).attr('data-penalty-amount');
            var EmiAmount = $(this).attr('data-emi-amount');
            var companyId = $(this).attr('data-company-id'); 

            var ecsType = $(this).attr('data-ecs_type');
            $('#ecs_type').val(ecsType === '1' ? 'BANK' : ecsType === '2' ? 'SSB' : ecsType === '0' ? '' : '');

            $.post("{{route('admin.fetchbranchbycompanyid')}}",{'company_id':companyId,'bank':'false','branch':'true'},function(e){
                var branchData = e.branch;
                var currentBranch = branchData.filter(function(branchDetail){
                    
                    return branchDetail['id']== branchId;
                });
                var selectElement = $('#loan_branch');
                selectElement.empty();
                for (var i = 0; i < currentBranch.length; i++) {
                    var option = $('<option></option>');
                    option.val(currentBranch[i].id);
                    option.text(currentBranch[i].name);
                    selectElement.append(option);
                }
            },'JSON');
            $('#deposite_amount').on('change', function() {
                var dAmount = $(this).val();
                var currentEmiDate = $('.application_date').val();
                var splitDate = currentEmiDate.split('/');
                var newEmidate = splitDate[2] + '-' + splitDate[1] + '-' + splitDate[0];
                var emiDate = $('#myID').attr('data-allemidate')
                // if( $(this).attr('data-emi-amount') == 1)
                // {
                //     if(title == 'Pay EMI')
                //     {
                //             if(dAmount > loanEMI)
                //         {
                //             swal('Sorry','Amount Shold be Less Than or Equal to Emi Amount','error');
                //             $(this).val('');
                //         }
                //     }
                //     else{
                //         if(dAmount <= loanEMI && emiDate.includes(newEmidate) == true)
                //         {
                //             swal('Sorry','Amount Shold be Less Than or Equalddd to Emi Amount','error');
                //             $(this).val('');
                //         }
                //     }
                // }
                // $('#ssbaccount').val(ssbAccount);
                $('#ssb_account').val(ssbAmount);
                $('#ssb_id').val(ssbId);
            })
            $('#title').val(title);
            $('#loan_id').val(loanId);
            $('#loan_emi_amount').val(loanEMI);
            $('#deposite_amount').val();
            $('#ssb_account_number').val(ssbAccount);
            $('#ssb_account').val(ssbAmount);
            $('#ssb_id').val(ssbId);
            //$('#ssb_id').val(ssbId);
            $('#recovered_amount').val(recoveredAmount);
            $('#closing_amount').val(closingAmount);
            $('#due_amount').val(dueAmount);
            $('#last_recovered_amount').val(lastRecoveredAmount);
            $('#companyId').val(companyId);

            if (penaltyAmount != '') {
                // $('#penalty_amount').val(penaltyAmount);
                // $('#penalty_amount').attr('readonly', false);
            } else {
                // $('#penalty_amount').val('');
                // $('#penalty_amount').attr('readonly', true);
            }
        })
        // $(document).on('click', '.pay-emi', function(e){
        //     var loanId = $(this).attr('data-loan-id');
        //     var loanEMI = $(this).attr('data-loan-emi');
        //     var ssbAmount = $(this).attr('data-ssb-amount');
        //     var ssbAccount = $(this).attr('data-ssb-account');
        //     var ssbId = $(this).attr('data-ssb-id');
        //     var recoveredAmount = $(this).attr('data-recovered-amount');
        //     var lastRecoveredAmount = $(this).attr('data-last-recovered-amount');
        //     var closingAmount = $(this).attr('data-closing-amount');
        //     var dueAmount = $(this).attr('data-due-amount');
        //     var penaltyAmount = $(this).attr('data-penalty-amount');
        //     $('#loan_id').val(loanId);
        //     $('#loan_emi_amount').val(loanEMI);
        //     $('#ssb_account_number').val(ssbAccount);
        //     $('#ssb_account').val(ssbAmount);
        //     $('#ssb_id').val(ssbId);
        //     $('#recovered_amount').val(recoveredAmount);
        //     $('#closing_amount').val(closingAmount);
        //     $('#due_amount').val(dueAmount);
        //     $('#last_recovered_amount').val(lastRecoveredAmount);
        //     if(penaltyAmount != ''){
        //         $('#penalty_amount').val(penaltyAmount);
        //         $('#penalty_amount').attr('readonly',false);
        //     }else{
        //         $('#penalty_amount').val('');
        //         $('#penalty_amount').attr('readonly',true);
        //     }
        // })
        $(document).on('click', '.reject-loan', function(e) {
            var url = $(this).attr('href');
            e.preventDefault();
            swal({
                title: "Are you sure, you want to delete this loan request?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    location.href = url;
                }
            });
        })
        /*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#loan_recovery_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.loanrecovery.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
        $('.export').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#loan_recovery_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#loan_recovery_filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportmt(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#loan_recovery_export').val(extension);
                $('form#loan_recovery_filter').attr('action', "{!! route('admin.loanrecovery.export') !!}");
                $('form#loan_recovery_filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportmt(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loanrecovery.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportmt(start, limit, formData, chunkSize);
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
        /*
            $('.export-loan').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#loan_details_export').val(extension);
                $('form#loan-filter').attr('action',"{!! route('admin.loandetails.export') !!}");
                $('form#loan-filter').submit();
                return true;
            });
        */
        $('.export-loan').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#loan_details_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#loan-filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportk(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#loan_details_export').val(extension);
                $('form#loan-filter').attr('action', "{!! route('admin.loandetails.export') !!}");
                $('form#loan-filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportk(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.loandetails.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportk(start, limit, formData, chunkSize);
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
        /*
            $('.export-group-loan').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#group_loan_recovery_export').val(extension);
                $('form#grouploanfilter').attr('action',"{!! route('admin.grouploanrecovery.export') !!}");
                $('form#grouploanfilter').submit();
                return true;
                
                "data": function(d) {
                    d.searchform = $('form#grouploanrecoveryfilter').serializeArray()
                },
            });
        */
        $('.export-group-loan').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#group_loan_recovery_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#grouploanrecoveryfilter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExportm(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#group_loan_recovery_export').val(extension);
                $('form#grouploanfilter').attr('action', "{!! route('admin.grouploanrecovery.export') !!}");
                $('form#grouploanfilter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExportm(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.grouploanrecovery.export') !!}",
                data: formData,
                success: function(response) {
                    console.log(response);
                    if (response.result == 'next') {
                        start = start + chunkSize;
                        doChunkedExportm(start, limit, formData, chunkSize);
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
        /*
            $('.export-group-loan-details').on('click',function(){
                var extension = $(this).attr('data-extension');
                $('#group_loan_details_export').val(extension);
                $('form#group-loan-filter').attr('action',"{!! route('admin.grouploandetails.export') !!}");
                $('form#group-loan-filter').submit();
                return true;
            });
        	*/
        loanTransactionTable = $('#loan_transaction_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.loan.transactionlist') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#transaction-loan-filter').serializeArray()
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
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                // {
                //     data: 'branch_code',
                //     name: 'branch_code'
                // },
                // {
                //     data: 'sector',
                //     name: 'sector'
                // },
                // {
                //     data: 'region',
                //     name: 'region'
                // },
                // {
                //     data: 'zone',
                //     name: 'zone'
                // },
                {
                    data: 'customer_id',
                    name: 'customer_id'
                },
                {
                    data: 'member_id',
                    name: 'member_id'
                },
                {
                    data: 'account_number',
                    name: 'account_number'
                },
                {
                    data: 'member_name',
                    name: 'member_name'
                },
                {
                    data: 'plan_name',
                    name: 'plan_name'
                },
                {
                    data: 'tenure',
                    name: 'tenure'
                },
                {
                    data: 'emi_amount',
                    name: 'emi_amount'
                },
                {
                    data: 'loan_sub_type',
                    name: 'loan_sub_type'
                },
                {
                    data: 'associate_code',
                    name: 'associate_code'
                },
                {
                    data: 'associate_name',
                    name: 'associate_name'
                },
                {
                    data: 'payment_mode',
                    name: 'payment_mode'
                }
                // {data: 'action', name: 'action',orderable: false, searchable: false},
            ],
                        "bDestroy": true,"ordering": false,
        });
        $(loanTransactionTable.table().container()).removeClass('form-inline');
        $('.export-group-loan-details').on('click', function(e) {
            e.preventDefault();
            var extension = $(this).attr('data-extension');
            $('#group_loan_details_export').val(extension);
            if (extension == 0) {
                var formData = jQuery('#group-loan-filter').serializeObject();
                var chunkAndLimit = 50;
                $(".spiners").css("display", "block");
                $(".loaders").text("0%");
                doChunkedExport(0, chunkAndLimit, formData, chunkAndLimit);
                $("#cover").fadeIn(100);
            } else {
                $('#group_loan_details_export').val(extension);
                $('form#group-loan-filter').attr('action', "{!! route('admin.grouploandetails.export') !!}");
                $('form#group-loan-filter').submit();
            }
        });
        // function to trigger the ajax bit
        function doChunkedExport(start, limit, formData, chunkSize) {
            formData['start'] = start;
            formData['limit'] = limit;
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: "{!! route('admin.grouploandetails.export') !!}",
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
        $(document).on('click', '.close-loan', function(e) {
            var loan_id = $(this).attr('data-id');
            var ssb_id = $(this).attr('data-ssb_id');
            var branch_id = $(this).attr('data-branch_id');
            var created_at = $('.created_at').val();
            e.preventDefault();
            swal({
                title: "Are you sure, you want to close this loan?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.loan.close') !!}",
                        dataType: 'JSON',
                        data: {
                            'loan_id': loan_id,
                            'ssb_id': ssb_id,
                            'branch_id': branch_id,
                            'created_at': created_at
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            console.log(response);
                            if(response.error){
                                swal('Error!',response.message, "error");
                            }else{
                                location.reload(true);
                            }
                        }
                    });
                }
            });
        });
        $(document).on('click', '.close-group-loan', function(e) {
            var loan_id = $(this).attr('data-id');
            var ssb_id = $(this).attr('data-ssb_id');
            var branch_id = $(this).attr('data-branch_id');
            var created_at = $('.created_at').val();
            e.preventDefault();
            swal({
                title: "Are you sure, you want to close this loan?",
                text: "",
                icon: "warning",
                buttons: [
                    'No, cancel it!',
                    'Yes, I am sure!'
                ],
                dangerMode: true,
            }).then(function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: "POST",
                        url: "{!! route('admin.grouploan.close') !!}",
                        dataType: 'JSON',
                        data: {
                            'loan_id': loan_id,
                            'ssb_id': ssb_id,
                            'branch_id': branch_id,
                            'created_at': created_at
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if(response.error){
                                swal('Error!',response.message, "error");
                            }else{
                                location.reload(true);
                            }
                        }
                    });
                }
            });
        });
        var today = new Date();
        $('.from_date,.to_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: "today",
            maxDate: today
        });
        $(".application_date,#date").hover(function() {
            var EndDate = $('.create_application_date').val();
            $('.application_date,#date').datepicker({
                format: "dd/mm/yyyy",
                orientation: "bottom",
                autoclose: true,
                startDate:EndDate,
                endDate: EndDate,
                maxDate: today
            }).on("changeDate", function(e) {
                // console.log(( e.date));
                $('#due_date').datepicker('setDate', e.date);
            });

            
            $("#due_date").datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                orientation: "bottom",
            });
        });
        $(document).on('change', '.application_date', function() {
            var aDate = $(this).val();
            $('#created_date').val(aDate);
            var associateCode = $('#loan_associate_code').val();
            if (associateCode != '') {
                $('#loan_associate_code').trigger('change');
            }
        });
        $(document).on('change', '#loan_branch', function(e) {
            var loanId = $(this).val();
            $('#cheque_number').val('');
            $('#cheque_date').val('');
            $('.branch-cheques').hide();
            $('.' + loanId + '-branch').show();
        })
       
        $('#demandRejectReason').validate({ // initialize the plugin
            rules: {
                'rejectreason': {
                    required: true
                },
            },
        });
        $(document).on('click', '.reject-demand-advice', function(e) {
            const modalTitle = $(this).attr('modal-title');
            const loanId = $(this).attr('demandId');
            const loanType = $(this).attr('loantype');
            const loanCategory = $(this).attr('loanCategory');
            const status = $(this).attr('status');
            const el = document.createElement("input");
            const statusData = document.createElement("input");
            console.log("status", status);
            $('.dinput').remove();
            $('#demandRejectReason').attr('action', "{!! route('admin.loan.reject_hold') !!}")
            $('#exampleModalLongTitle').html(modalTitle);
            $inputData =
                '<input type="hidden" id="loanCategory" class="dinput" name="loanCategory" value = "' +
                loanCategory +
                '"><input type="hidden" id="loanType" class="dinput" name="loanType" value = "' +
                loanType + '"><input type="hidden" class="dinput" id="status" name="status" value = "' +
                status + '">'
            $('#demandRejectReason').append($inputData);
            $('#demandId').val(loanId);
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
            loanRecoveryTable.draw();
        }
    }

    // function loanSearchForm() {
    //     $('#is_search').val("yes");
    //     $(".table-section").addClass("show-table");
    //     loanRequestTable.draw();
    // }


    function resetForm() {
        var form = $("#filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('.from_date').val('');
        $('.to_date').val('');
        $('#date').val('');
        $('#loan_account_number').val('');
        $('#member_name').val('');
        $('#member_id').val('');
        $('#associate_code').val('');
        $('#plan').val('');
        $('#status').val('');
        loanRecoveryTable.draw();
        $(".table-section").addClass('hideTableData');
    }

    //Loan Transaction Search Button Function 
    function loanTransactionSearchForm() {
        if ($('#transaction-loan-filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            loanTransactionTable.draw();
        }
    }

    //Loan Transaction Reset Button Function 
    function loanTransactionResetForm() {
        var form = $("#transaction-loan-filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#payment_mode').val('');
        $('#company_id').val('');
        $('#company_id').trigger('change');
        $('#date').val('');
        $('.from_date').val('');
        $('.to_date').val('');
        $('#application_number').val('');
        $('#transaction_loan_type').val('');
        $('#member_name').val('');
        $('#member_id').val('');
        $('#customer_id').val('');
        $('#associate_code').val('');
        $('#plan').val('');
        $('#status').val('');
        $(".table-section").addClass("hideTableData");
        loanTransactionTable.draw();
    }

    //Loan Recovery Search Button Function 
    function loanrecoverysearchForm() {
        if ($('#loan_recovery_filter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            loanRecoveryTable.draw();
        }
    }

    //Loan Recovery Reset Button Function
    function loanrecoveryresetForm() {
        var form = $("#loan_recovery_filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#company_id').val("");
        $('#company_id').trigger('change');
        $('.to_date').val("");
        $('.from_date ').val("");
        $('#loan_account_number').val("");
        $('#loan_recovery_type').val("");
        $('#loan_recovery_plan').empty();
        $('#loan_recovery_plan').append(' <option value="">----Select Loan Plan----</option>');
        $('#member_name').val("");
        $('#member_id').val("");
        $('#associate_code').val("");
        $('#group_loan_common_id').val("");
        $(".table-section").addClass('hideTableData');
        loanRecoveryTable.draw();
    }

    //Group Loan Recovery Search Button Function 
    function groupLoanRecoverySearchForm() {
        if ($('#grouploanrecoveryfilter').valid()) {
            $('#is_search').val("yes");
            $(".table-section").removeClass('hideTableData');
            groupLoanRecoveryTable.draw();
        }
    }

    //Group Loan Recovery Reset Button Function
    function groupLoanRecoveryResetForm() {
        var form = $("#grouploanrecoveryfilter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        $('#company_id').val("");
        $('#company_id').trigger('change');
        $('#date_to').val("");
        $('#date_from').val("");
        $('#loan_account_number').val("");
        $('#group_loan_recovery_type').val('');
        $('#group_loan_recovery_plan').empty();
        $('#group_loan_recovery_plan').append(' <option value="">----Select Loan Plan----</option>');
        $('#member_name').val("");
        $('#member_id').val("");
        $('#associate_code').val("");
        $('#group_loan_common_id').val("");
        $('#table-section').addClass("hideTableData");
        groupLoanRecoveryTable.draw();
    }

    //Group Loan Search Button Function 
    function groupLoanSearchForm() {
        if ($('#group-loan-filter').valid()) {
            $('#is_search').val("yes");
            $(".d-none").removeClass('d-none');
            groupLoanRequestTable.draw();
        }
    }

    //Group Loan Reset Button Function
    function groupLoanResetForm() {
        var form = $("#group-loan-filter"),
            validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error");
        $('#is_search').val("no");
        groupLoanRequestTable.draw();
    }
</script>
