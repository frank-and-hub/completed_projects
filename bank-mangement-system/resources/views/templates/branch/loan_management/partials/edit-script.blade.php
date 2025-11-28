<script type="text/javascript">
$(document).ready(function() {
    var currentRequest = null; 
    // Investment Form validations
    $('#register-plan').validate({ // initialize the plugin
        rules: {
            'loan' : 'required',
            'amount' : {required: true, number: true},
            'days' : 'required',
            'months' : 'required',
            'purpose' : 'required',
            'acc_member_id' : {required: true, number: true},
            'applicant_id' : {required: true, number: true},
            'applicant_address_permanent' : 'required',
            'applicant_address_temporary' : 'required',
            'applicant_occupation' : 'required',
            'applicant_organization' : 'required',
            'applicant_designation' : 'required',
            'applicant_monthly_income' : {required: true, number: true},
            'applicant_year_from' : {required: true, number: true},
            'applicant_bank_name' : 'required',
            'applicant_bank_account_number' : {required: true, number: true,minlength: 8,maxlength:20},
            'applicant_ifsc_code' : {required: true},
            'applicant_cheque_number_1' : {required: true},
            'applicant_cheque_number_2' : {required: true},
            'applicant_id_proof' : 'required',
            'applicant_id_number' : {required: true, checkIdNumber : '#applicant_id_proof'},
            'applicant_address_id_proof' : 'required',
            'applicant_address_id_number' : {required: true, checkIdNumber : '#applicant_address_id_proof'},
            'applicant_income' : 'required',
            'applicant_security' : 'required',
            'co-applicant_address_permanent' : 'required',
            'co-applicant_address_temporary' : 'required',
            'co-applicant_occupation' : 'required',
            'co-applicant_organization' : 'required',
            'co-applicant_designation' : 'required',
            'co-applicant_monthly_income' : {required: true, number: true},
            'co-applicant_year_from' : {required: true, number: true},
            'co-applicant_bank_name' : 'required',
            'co-applicant_bank_account_number' : {required: true, number: true,minlength: 8,maxlength:20},
            'co-applicant_ifsc_code' : {required: true},
            'co-applicant_cheque_number_1' : {required: true},
            'co-applicant_cheque_number_2' : {required: true},
            'co-applicant_id_proof' : 'required',
            'co-applicant_id_number' : {required: true, checkIdNumber : '#co-applicant_id_proof'},
            'co-applicant_id_proof' : 'required',
            'co-applicant_address_id_number' : {required: true, checkIdNumber : '#co-applicant_address_id_proof'},
            'co-applicant_income' : 'required',
            'co-applicant_security' : 'required',
            'guarantor_member_id' : 'required',
            'guarantor_name' : 'required',
            'guarantor_father_name' : 'required',
            'guarantor_dob' : 'required',
            'guarantor_marital_status' : 'required',
            'local_address' : 'required',
            'guarantor_ownership' : 'required',
            'guarantor_temporary_address' : 'required',
            'guarantor_mobile_number' : {required: true,number: true,minlength: 10,maxlength:12},
            'guarantor_educational_qualification' : 'required',
            'guarantor_dependents_number' : 'required',
            'guarantor_occupation' : 'required',
            'guarantor_organization' : 'required',
            // 'guarantor_designation' : 'required',
            'guarantor_monthly_income' : {required: true, number: true},
            'guarantor_year_from' : {required: true, number: true},
            'guarantor_bank_name' : 'required',
            'guarantor_bank_account_number' : {required: true, number: true,minlength: 8,maxlength:20},
            'guarantor_ifsc_code' : {required: true},
            'guarantor_cheque_number_1' : {required: true},
            'guarantor_cheque_number_2' : {required: true},
            'guarantor_id_proof' : 'required',
            'guarantor_id_number' : {required: true, checkIdNumber : '#guarantor_id_proof'},
            'guarantor_address_id_number' : {required: true, checkIdNumber : '#guarantor_address_id_proof'},
            'guarantor_income' : 'required',
            'guarantor_more_doc_title' : 'required',
            'guarantor_security' : 'required',
        }
    });
    // Get registered member by id
    var x = $('.count_more_doc').val(); //Initial field counter
    var list_maxField = 10;
    $(document).on('click','#more-doc-button',function(){
        $('.more-doc').show();
        var hiddenDoc = $('.hidden_more_doc').val();
        if(hiddenDoc == 1){
            $('.more-doc').show();
            var countVal = $(this).attr('data-val');
            var increaseVal = countVal+1;
            if(x < list_maxField){ 
                x++; //Increment field counter
                var list_fieldHTML = '<div class="form-group row flex-grow-1"><label class="col-form-label col-lg-2">Doc Title</label><div class="col-lg-3"><input type="text" name="guarantor_more_doc_title['+x+']" id="guarantor_more_doc_title" class="form-control"></div><label class="col-form-label col-lg-2">Upload File</label><div class="col-lg-4"><input type="file" name="guarantor_more_upload_file['+x+']" id="guarantor_more_upload_file" class="form-control"></div><span><input type="hidden" name="hidden_other_doc_file_id['+x+']" id="hidden_other_doc_file_id" value=""><a href="javascript:void(0);" class="remove-doc-button" >Remove</a></span></div>'; //New input field html
                $('.more-doc').append(list_fieldHTML); //Add field html
            }
        }else{
            $('.more-doc').show();
            $('.hidden_more_doc').val(1);
        }
    });
    $('.more-doc').on('click', '.remove-doc-button', function() {
      $(this).closest('div.row').remove(); //Remove field html
           x--; //Decrement field counter
    });
    $('.date_of_birth').datepicker( {
       format: "yyyy-mm-dd",
       orientation: "top",
       autoclose: true
    });
    var loan = $('#loan_type_slug').val();
    /*
    if(loan == 'personal-loan'){
        $('.personal-loan-section').show();
        $('.applciant-deatils-box').show();
        $('.coapplciant-deatils-box').show();
        $('.guarantor-deatils-box').show();
        $('.group-information').hide();
        $('.staff-loan-section').show();
        $('.other-loan-section').hide();
        $('.bank-details-section').show();
        $('.emi-mode-section').hide();
        $('.group-emi-mode-section').hide();
        $('.loan-against-investment-plan').hide();
        $('.loan-against-investment-information').hide();
        $('.applicant-box').show();
        $('.cheque-box').show();
        //$('.group-loan-member-table').hide();
        $('.salary-section').hide();
    }else if(loan == 'group-loan'){
        $('.personal-loan-section').hide();
        $('.group-information').show();
        $('.applciant-deatils-box').show();
        $('.coapplciant-deatils-box').show();
        $('.guarantor-deatils-box').show();
        $('.staff-loan-section').hide();
        $('.other-loan-section').hide();
        $('.bank-details-section').hide();
        $('.emi-mode-section').hide();
        $('.group-emi-mode-section').hide();
        $('.loan-against-investment-plan').hide();
        $('.loan-against-investment-information').hide();
        $('.applicant-box').hide();
        $('.cheque-box').show();
        //$('.group-loan-member-table').hide();
        $('.salary-section').hide();
    }else if(loan == 'staff-loan'){
        $('.personal-loan-section').show();
        $('.group-information').hide();
        $('.applciant-deatils-box').show();
        $('.coapplciant-deatils-box').hide();
        $('.guarantor-deatils-box').show();
        $('.staff-loan-section').show();
        $('.other-loan-section').hide();
        $('.bank-details-section').show();
        $('.emi-mode-section').hide();
        $('.group-emi-mode-section').hide();
        $('.loan-against-investment-plan').hide();
        $('.loan-against-investment-information').hide();
        $('.applicant-box').show();
        $('.cheque-box').show();
        //$('.group-loan-member-table').hide();
        $('.salary-section').show();
    }else if(loan == 'loan-against-investment-plan'){
        $('.personal-loan-section').show();
        $('.applciant-deatils-box').show();
        $('.coapplciant-deatils-box').hide();
        $('.guarantor-deatils-box').hide();
        $('.group-information').hide();
        $('.staff-loan-section').show();
        $('.other-loan-section').hide();
        $('.bank-details-section').show();
        $('.loan-against-investment-plan').hide();
        $('.loan-against-investment-information').show();
        $('.emi-mode-section').hide();
        $('.group-emi-mode-section').hide();
        $('.applicant-box').show();
        $('.cheque-box').hide();
        //$('.group-loan-member-table').hide();
        $('.salary-section').hide();
    }else{
        $('.applciant-deatils-box').hide();
        $('.coapplciant-deatils-box').hide();
        $('.guarantor-deatils-box').hide();
        $('.group-information').hide();
        $('.staff-loan-section').hide();
        $('.other-loan-section').hide();
        $('.bank-details-section').hide();
        $('.emi-mode-section').hide();
        $('.group-emi-mode-section').hide();
        $('.loan-against-investment-plan').hide();
        //$('.group-loan-member-table').hide();
        $('.salary-section').hide();
    }   
    */
    $(document).on('click','#co_applicant_checkbox',function(){
        if ($("input[type=checkbox]").is( 
                      ":checked")) { 
            $('.co-applicant-form').show() 
            $('#co_applicant_checkbox_val').val(1) 
        } else { 
            $('.co-applicant-form').hide() 
            $('#co_applicant_checkbox_val').val(0) 
        }  
    });  
    // Get registered member by id
    $(document).on('change','#applicant_id,#co-applicant_auto_member_id,#guarantor_auto_member_id,#group_auto_member_id',function(){
        $.cookie('planTbaleCounter', ''); 
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');
        var loantype = $( "#loan option:selected" ).val();
        currentRequest = $.ajax({
            type: "POST",  
            url: "{!! route('loan.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid,'loantype':loantype,'attVal':attVal},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend : function()    {           
                if(currentRequest != null) {
                    currentRequest.abort();
                }
            },
            success: function(response) {
                if(response.msg_type=="success")
                {   
                    if(attVal=='group-loan' && response.member.saving_account.length==0){
                        $('#group_auto_member_id').val('');
                        swal("Warning!", "You have not any ssb account", "warning");
                        $('.'+attVal+'-member-detail').html('');
                        $('#'+attVal+'_member_id').val('');
                        $('#'+attVal+'_occupation_name').val('');
                        $('#'+attVal+'_occupation').val('');
                        $('.'+attVal+'-occupation-name').val('');
                        $('.'+attVal+'-occupation').val('');
                        return false;
                    }else if(attVal=='group-loan' && jQuery.inArray(memberid, guarantor) == -1){
                        $('#group_auto_member_id').val('');
                        swal("Warning!", "Applicant should be group member!", "warning");
                        $('.'+attVal+'-member-detail').html('');
                        $('#'+attVal+'_member_id').val('');
                        $('#'+attVal+'_occupation_name').val('');
                        $('#'+attVal+'_occupation').val('');
                        $('.'+attVal+'-occupation-name').val('');
                        $('.'+attVal+'-occupation').val('');
                        return false;
                    }
                    if(attVal=='guarantor'){                       
                        $('.guarantor-name-section').hide();
                        $('#guarantor_occupation_id').html('');
                        $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                        $('#guarantor_occupation_id').append('<option selected value="'+response.occupation_id+'">'+response.occupation+'</option>');
                        $('#guarantor_occupation_id').prop('disabled', true);
                        $('.guarantor-member-detail-box').hide();
                        if(jQuery.inArray(memberid, guarantor) !== -1){
                            $('#guarantor_auto_member_id').val('');
                            swal("Warning!", "Guarantor should not be a group member!", "warning");
                            $('.'+attVal+'-member-detail').html('');
                            $('#'+attVal+'_member_id').val('');
                            $('#'+attVal+'_occupation_name').val('');
                            $('#'+attVal+'_occupation').val('');
                            $('.'+attVal+'-occupation-name').val('');
                            $('.'+attVal+'-occupation').val('');
                            return false;
                        }
                    }
                    $('.'+attVal+'-member-detail').html(response.view);
                    $('#'+attVal+'_member_id').val(response.id);
                    $('#'+attVal+'_occupation_name').val(response.occupation);
                    $('#'+attVal+'_occupation').val(response.occupation_id);
                    $('.'+attVal+'-occupation-name').val(response.occupation);
                    $('.'+attVal+'-occupation').val(response.occupation_id);
                  if(loantype==4){
                        $('.loan-against-investment-plan').show();
                        $('.investment-plan-input-number').html('');
                        if(response.member['associate_investment'].length!=0){
                            var count = response.member['associate_investment'].length;
                            //$.cookie('planTbaleCounter', '');  
                            var isRecordExist = false;
                            var i = 0;
                            var invesmentLength = response.member['associate_investment'].length;
                            $.each( response.member['associate_investment'], function( key, value ) {
                              console.log('ttt', key + ": " + value.id );
                                var months = value.tenure*12;
                                let cdate = new Date(value.created_at)
                                let newDate = cdate.getDate() + "/" + (cdate.getMonth() + 1) + "/" + cdate.getFullYear()
                                var dt = new Date(value.created_at);
                                dt.setMonth(months);
                                let current_datetime = new Date(dt)
                                let duenewDate = current_datetime.getDate() + "/" + (current_datetime.getMonth() + 1) + "/" + current_datetime.getFullYear()
                                if(value.plan_id != 1){
                                    $.ajax({
                                        type: "POST",  
                                        url: "{!! route('loan.getplanname') !!}",
                                        dataType: 'JSON',
                                        async: false,
                                        data: {'planid':value.plan_id},
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },                                    
                                        success: function(response) {
                                            var now = new Date();
                                            var sixMonthsFromNow = new Date(now.setMonth(now.getMonth() - 6));
                                            //alert(sixMonthsFromNow.toISOString());
                                            //alert(value.created_at);
                                            if(sixMonthsFromNow.toISOString() >= value.created_at){      
                                                isRecordExist = true;            
                                                $.cookie('planTbaleCounter', key);  
                                                var list_fieldHTML = '<tr><td class="plan-name">'+response.planName+'<input type="hidden" name="investmentplanloanid['+key+']" value="'+value.id+'" class="form-control"></td><td class="account-id">'+value.account_number+'</td><td class="open-date">'+newDate+'</td><td class="due-date">'+duenewDate+'</td><td class="deposite-amount">'+value.deposite_amount+'<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-'+key+' form-control" value="'+value.deposite_amount+'"></td><td class="plan-months">'+months+'</td><td class="loan-amount-input"><input data-input="'+key+'" type="text" name="ipl_amount['+key+']" class="ipl_amount ipl_amount-'+key+' form-control" style="width: 104px"></td></tr>';
                                                $('.investment-plan-input-number').append(list_fieldHTML);
                                            }
                                        } 
                                    });
                                }  
                                i++;
                                if(i==invesmentLength && isRecordExist==false){
                                    swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                                    $('.applicant-member-detail').html('');
                                    $('.'+attVal+'-member-detail').html('');
                                    $('#'+attVal+'_member_id').val('');
                                    $('#'+attVal+'_id').val('');
                                }
                            });
                        }else{
                            swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                            $('.applicant-member-detail').html('');
                            $('.'+attVal+'-member-detail').html('');
                            $('#'+attVal+'_member_id').val('');
                            $('#'+attVal+'_id').val('');
                        }
                    }
                    //$('#amount').val('');
                }
                else if(response.msg_type=="warning")
                {
                    $('#'+attVal+'_id').val('');
                    $('.'+attVal+'-member-detail').html('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false; 
                }
                else
                {
                    $('.'+attVal+'-member-detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>');
                    $('#'+attVal+'_id').val('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    if(attVal=='guarantor'){
                    $('#guarantor_occupation_id').html('');
                    $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                    $('#guarantor_occupation_id').prop('disabled', false);
                    $('.guarantor-name-section').show();
                    $('.guarantor-member-detail-box').show();
                    }
                    $('.loan-against-investment-plan').hide();
                    $('.investment-plan-input-number').html('');
                }
            }
        }); 
    });
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).on('change','#applicant_id_proof,#applicant_address_id_proof,#co-applicant_id_proof,#co-applicant_address_id_proof,#guarantor_id_proof,#guarantor_address_id_proof',function(){
        var sectionval = $('option:selected', this).attr('data-val');
        var proofValue = $('option:selected', this).attr('data-proof-val');
        if($(this).val()==1)
        {
            $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
        }
        else if($(this).val()==2)
        {
            $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter proper driving licence number. For eg:- HR-0619850034761 Or UP14 20160034761');
        }
        else if($(this).val()==3)
        {
            $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');     
        }
        else if($(this).val()==4)
        {
            $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter proper passport number. For eg:- A1234567');
        }
        else if($(this).val()==5)
        {
            $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter proper pan card number. For eg:- ASDFG9999G');
        }
        else if($(this).val()==6)
        {
             $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter id proof number');
        }
        else
        {
            $('#'+sectionval+'_id_tooltip').attr('data-original-title', 'Enter id proof number');
        }
        if($('#'+sectionval+'_id').val() != ''){
            $('#'+sectionval+'_id_number').val(proofValue);  
        }else{
            $('#'+sectionval+'_id_number').val('');  
        }
        if(sectionval == 'applicant_address'){
            if($('#applicant_id').val() != ''){
                $('#'+sectionval+'_id_number').val(proofValue);  
            }else{
                $('#'+sectionval+'_id_number').val('');  
            } 
        }
        if(sectionval == 'co-applicant' || sectionval == 'co-applicant_address'){
            if($('#co-applicant_auto_member_id').val() != ''){
                $('#'+sectionval+'_id_number').val(proofValue);  
            }else{
                $('#'+sectionval+'_id_number').val('');  
            } 
        }
        if(sectionval == 'guarantor' || sectionval == 'guarantor_address'){
            if($('#guarantor_auto_member_id').val() != ''){
                $('#'+sectionval+'_id_number').val(proofValue);  
            }else{
                $('#'+sectionval+'_id_number').val('');  
            } 
        }
    });
    $.validator.addMethod("checkIdNumber", function(value, element,p) {
        if($(p).val()==1)
        {
          if(this.optional(element) || /^([a-zA-Z]){3}([0-9]){7}?$/g.test(value)==true)
          {
            result = true;
          }else{
            $.validator.messages.checkIdNumber = "Please enter valid voter id number";
            result = false;  
          }
        }
        else if($(p).val()==2)
        {
          if(this.optional(element) || /^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9][0-9])[0-9]{7}$/.test(value)==true)
          {
            result = true;
          }else{
            $.validator.messages.checkIdNumber = "Please enter valid driving licence number";
            result = false;  
          }
        }
        else if($(p).val()==3)
        {
          if(this.optional(element) || /^(\d{12}|\d{16})$/.test(value)==true)
          {
            result = true;
          }else{
            $.validator.messages.checkIdNumber = "Please enter valid aadhar card  number";
            result = false;  
          }
        }
        else if($(p).val()==4)
        {
          if(this.optional(element) || /^[A-Z][0-9]{7}$/.test(value)==true)
          {
            result = true;
          }else{
            $.validator.messages.checkIdNumber = "Please enter valid passport  number";
            result = false;  
          }
        }
        else if($(p).val()==5)
        {
          if(this.optional(element) || /([A-Z]){5}([0-9]){4}([A-Z]){1}$/.test(value)==true)
          {
            result = true;
          }else{
            $.validator.messages.checkIdNumber = "Please enter valid pan card no";
            result = false;  
          }
        }
        else if($(p).val()==6)
        {
            if(this.optional(element) || value!='')
            {
                result = true;
            }else{
                $.validator.messages.checkIdNumber = "Please enter ID Number";
                result = false;  
            }    
        }
        else{
            if(this.optional(element) || value!='')
            {
                result = true;
            }else{
                $.validator.messages.checkIdNumber = "Please enter ID Number";
                result = false;  
            } 
        }
        return result;
    }, "");
    $(document).on('change','#applicant_income,#co-applicant_income,#guarantor_income',function(){     
        var value = $(this).val();
        var section = $('option:selected', this).attr('data-val');
        if(value==2){
            $('.'+section+'-salary-remark').show();
        }else{
            $('.'+section+'-salary-remark').hide();
        }
    });
    $(document).on('change','.form-control',function(){
        $('.edit_reject_request').val(0);
    });
    // Show loading image
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    // Hide loading image
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});
</script>