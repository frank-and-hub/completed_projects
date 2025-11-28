<!-- <script type="text/javascript">
    var loantable;
    var  grouploantable;
$(document).ready(function() {
     var currentRequest = null;
     var guarantor = [];
     var groupMemberId = [];
     var today = new Date();
    // Investment Form validations
    $('#register-plan').validate({ // initialize the plugin
        rules: {
            'loan' : 'required',
            'amount' : {required: true, number: true},
            'days' : 'required',
            'months' : 'required',
            'purpose' : 'required',
            'group_activity' : 'required',
            'group_leader_member_id' : 'required',
            'number_of_member' : 'required',
            'salary' : {required: true, number: true},
            //'bank_account' : 'required',
            //'ifsc_code' : 'required',
            //'bank_name' : 'required',
            'acc_member_id' : {required: true, number: true},
            'applicant_id' : {required: true, number: true},
            'applicant_address_permanent' : 'required',
            'applicant_address_temporary' : 'required',
            //'applicant_occupation' : 'required',
            //'applicant_organization' : 'required',
            //'applicant_designation' : 'required',
            'applicant_monthly_income' : {required: true, number: true},
            'applicant_year_from' : {required: true, number: true},
            'applicant_bank_name' : 'required',
            'applicant_bank_account_number' : {required: true, number: true},
            'applicant_ifsc_code' : {required: true},
            'applicant_cheque_number_1' : {required: true, number: true, notEqual: "#applicant_cheque_number_2", notEqual1: "#co-applicant_cheque_number-1", notEqual2: "#co-applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},
            'applicant_cheque_number_2' : {required: true, number: true, notEqual: "#applicant_cheque_number_1", notEqual1: "#co-applicant_cheque_number-1", notEqual2: "#co-applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},
            'applicant_id_proof' : 'required',
            'applicant_id_number' : {required: true, checkIdNumber : '#applicant_id_proof'},
            'applicant_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'applicant_address_id_proof' : 'required',
            'applicant_address_id_number' : {required: true, checkIdNumber : '#applicant_address_id_proof'},
            'applicant_address_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'applicant_income' : 'required',
            'applicant_income_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'applicant_security' : 'required',
            'co-applicant_address_permanent' : 'required',
            'co-applicant_address_temporary' : 'required',
            //'co-applicant_occupation' : 'required',
            //'co-applicant_organization' : 'required',
            //'co-applicant_designation' : 'required',
            'co-applicant_monthly_income' : {required: true, number: true},
            'co-applicant_year_from' : {required: true, number: true},
            /*'co-applicant_bank_name' : 'required',
            'co-applicant_bank_account_number' : {required: true, number: true},
            'co-applicant_ifsc_code' : {required: true},
            'co-applicant_cheque_number_1' : {required: true, number: true},
            'co-applicant_cheque_number_2' : {required: true, number: true},
            'co-applicant_id_proof' : 'required',
            'co-applicant_id_number' : {required: true, checkIdNumber : '#co-applicant_id_proof'},*/
            'co-applicant_id_file' : {extension: "jpf|jpg|pdf|jpeg",required:true},
            /*'co-applicant_address_id_proof' : 'required',
            'co-applicant_address_id_number' : {required: true, checkIdNumber : '#co-applicant_address_id_proof'},
            'co-applicant_address_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'co-applicant_income' : 'required',
            'co-applicant_income_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'co-applicant_security' : 'required',
            'co-applicant_cheque_number_1' : {required: true, number: true, notEqual: "#co-applicant_cheque_number_2", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},
            'co-applicant_cheque_number_2' : {required: true, number: true, notEqual: "#co-applicant_cheque_number-1", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},*/
            'guarantor_member_id' : {required: true, number: true},
            'guarantor_name' : 'required',
            'guarantor_father_name' : 'required',
            'guarantor_dob' : 'required',
            'guarantor_marital_status' : 'required',
            'local_address' : 'required',
            'guarantor_ownership' : 'required',
            'guarantor_temporary_address' : 'required',
            'guarantor_mobile_number' : {required: true,number: true,minlength: 10,maxlength:12},
            'guarantor_educational_qualification' : 'required',
            'guarantor_dependents_number' : {required: true,number: true},
            //'guarantor_occupation' : 'required',
            //'guarantor_organization' : 'required',
            'guarantor_monthly_income' : {required: true, number: true},
            'guarantor_year_from' : {required: true, number: true},
            /*'guarantor_bank_name' : 'required',
            'guarantor_bank_account_number' : {required: true, number: true},
            'guarantor_ifsc_code' : {required: true},
            'guarantor_cheque_number_1' : {required: true, number: true},
            'guarantor_cheque_number_2' : {required: true, number: true},
            'guarantor_id_proof' : 'required',
            'guarantor_id_number' : {required: true, checkIdNumber : '#guarantor_id_proof'},*/
            'guarantor_id_file' : {extension: "jpf|jpg|pdf|jpeg",required:true},
            /*'guarantor_address_id_proof' : 'required',
            'guarantor_address_id_number' : {required: true, checkIdNumber : '#guarantor_address_id_proof'},
            'guarantor_address_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'guarantor_income' : 'required',
            'guarantor_income_file' : {extension: "jpf|jpg|pdf|jpeg"},
            //'guarantor_more_doc_title' : 'required',*/
            'guarantor_more_upload_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'guarantor_security' : 'required',
            'co-applicant_auto_member_id' : 'required',
            'emi_mode_option' : 'required',
            'acc_auto_member_id' : 'required',
            'group_associate_id' : 'required',
            'guarantor_occupation_id' : 'required',
            /*'guarantor_cheque_number_1' : {required: true, number: true, notEqual: "#guarantor_cheque_number_2", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#co-applicant_cheque_number-1", notEqual4: "#co-applicant_cheque_number_2"},
            'guarantor_cheque_number_2' : {required: true, number: true, notEqual: "#guarantor_cheque_number_1", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#co-applicant_cheque_number-1", notEqual4: "#co-applicant_cheque_number_2"},*/
        }
    });

    $('#loan_emi').validate({ // initialize the plugin
        rules: {
            'application_date' : {required: true},
            'loan_associate_code' : {required: true,number: true},
            'loan_emi_payment_mode' : 'required',
            'ssb_account' : {required: true,number: true},
            'deposite_amount' : {required: true,number: true, lessThanEquals: '#closing_amount'},
            'transaction_id' : {required: true,number: true},
            'account_number' : {required: true,number: true},
            'customer_bank_name' : 'required',
            'customer_bank_account_number' : {required: true,number: true},
            'customer_branch_name' : {required: true},
            'customer_ifsc_code' : {required: true},
            'company_bank' : {required: true},
            'company_bank_account_number' : {required: true,number: true},
            'company_bank_account_balance' : {required: true},
            'bank_transfer_mode' : {required: true},
            'utr_transaction_number' : {required: true},
            'online_total_amount' : {required: true},
            'cheque_id' : {required: true},
            'cheque_total_amount' : {required: true},
            'customer_cheque' : {required: true,number: true},
            'bank_account_number' : {required: true},
        },
        submitHandler: function() {
            var paymentModeVal = $( "#loan_emi_payment_mode option:selected").val();
            var depositeAmount = $( "#deposite_amount").val();
            if(paymentModeVal==0){

                var ssbAmount = $( "#ssb_account").val();
                if(parseInt(depositeAmount) > parseInt(ssbAmount)){
                    $('.ssbamount-error').show();
                    $('.ssbamount-error').html('Amount should be less than OR equals current available amounts.');
                    //event.preventDefault();
                    return false;
                }
            }if(paymentModeVal==3){
                var checkAmount = $( "#cheque_amount").val();
                if(parseInt(depositeAmount) != parseInt(checkAmount)){
                    $('.ssbamount-error').show();
                    $('.ssbamount-error').html('Amount should be equal to cheque amounts.');
                    return false;
                }
            }else{
                $('.ssbamount-error').html('');
                //return true;
            }

            $('.payloan-emi').prop('disabled', true);
            return true;

        }
    });

    $.validator.addMethod("lessThanEquals",
    function (value, element, param) {
          var $otherElement = $(param);
          return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
       return value > target.val();
    }, "Amount should be less than OR equals closer amount.");

    jQuery.validator.addMethod("notEqual", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual1", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual2", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual3", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual4", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual5", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    // Get registered member by id
    $(document).on('change','#acc_auto_member_id,#group_associate_id',function(){
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');

        $.ajax({
            type: "POST",
            url: "{!! route('loan.associatemember') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.resCount > 0)
                {
                    $('.'+attVal+'-member-detail-not-found').hide();
                    $('.'+attVal+'-member-detail').show();
                    var firstName = response.member[0].first_name ? response.member[0].first_name : '';
                    var lastName = response.member[0].last_name ? response.member[0].last_name : '';

                    var ass_name = firstName+' '+lastName;
                    ass_name ? $('#acc_name').val(ass_name) : $('#acc_name').val("Name N/A");
                    response.bAccount ? $('.'+attVal+'-bank-account').val(response.bAccount) : $('.'+attVal+'-bank-account').val("");
                    response.bIfsc ? $('.'+attVal+'-ifsc-code').val(response.bIfsc) : $('.'+attVal+'-ifsc-code').val("");
                    response.bName ? $('.'+attVal+'-bank-name').val(response.bName) : $('.'+attVal+'-bank-name').val("");
                    response.member[0].carders_name ? $('#acc_carder').val(response.member[0].carders_name) : $('#acc_carder').val("Carder N/A");
                    $('.ass-member-id').val(response.member[0].id);
                    $('.'+attVal+'-id').val(response.member[0].id);
                    $('.'+attVal+'-name').val(ass_name);
                }
                else
                {
                    $('.'+attVal+'-bank-account').val("");
                    $('.'+attVal+'-ifsc-code').val("");
                    $('.'+attVal+'-bank-name').val("");
                    $('.'+attVal+'-member-detail').hide();
                    $('.'+attVal+'-member-detail-not-found').show();
                    $('#acc_auto_member_id').val('');
                    $('#group_associate_id').val('');
                    $('.ass-member-id').val('');
                }
            }
        });
    });

    // Get registered member by id
    $(document).on('change','#applicant_id',function(){
        $.cookie('planTbaleCounter', '');
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');
        var loantype = $( "#loan option:selected" ).val();
        var type = 'applicant';
        currentRequest = $.ajax({
            type: "POST",
            url: "{!! route('loan.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid,'loantype':loantype,'attVal':attVal,'type':type},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend : function()    {
                if(currentRequest != null) {
                    currentRequest.abort();
                }
            },
            success: function(response) {
                //console.log(response);
                if(response.msg_type=="success")
                {
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
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
                        //$('.guarantor-name-section').hide();
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
                    $('#'+attVal+'_designation').val(response.carderName);

                    $("#"+attVal+"_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.first_id_no+'') ;
                    $("#"+attVal+"_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.second_id_no+'') ;

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
                                            //if(sixMonthsFromNow.toISOString() >= value.created_at){
                                                isRecordExist = true;
                                                $.cookie('planTbaleCounter', key);
                                                var list_fieldHTML = '<tr><td class="plan-name">'+response.planName+'<input type="hidden" name="investmentplanloanid['+key+']" value="'+value.id+'" class="form-control"></td><td class="account-id">'+value.account_number+'</td><td class="open-date">'+newDate+'</td><td class="due-date">'+duenewDate+'</td><td class="deposite-amount">'+value.current_balance+'<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-'+key+' form-control" value="'+value.current_balance+'"></td><td class="plan-months">'+months+'</td><td class="loan-amount-input"><input data-input="'+key+'" type="text" name="ipl_amount['+key+']" class="ipl_amount ipl_amount-'+key+' form-control" style="width: 104px"></td></tr>';
                                                $('.investment-plan-input-number').append(list_fieldHTML);

                                            //}
                                        }

                                    });

                                }

                                i++;
                                /*if(i==invesmentLength && isRecordExist==false){
                                    swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                                    $('.applicant-member-detail').html('');
                                    $('.'+attVal+'-member-detail').html('');
                                    $('#'+attVal+'_member_id').val('');
                                    $('#'+attVal+'_id').val('');
                                }*/
                            });
                        }else{
                            //swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                            /*$('.applicant-member-detail').html('');
                            $('.'+attVal+'-member-detail').html('');
                            $('#'+attVal+'_member_id').val('');
                            $('#'+attVal+'_id').val('');*/
                        }
                    }
                    //$('#amount').val('');
                }

               /* else if(response.msg_type=="warning")
                {
                    $('#'+attVal+'_id').val('');
                    $('.'+attVal+'-member-detail').html('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false;
                }*/
                else
                {
                    $('.'+attVal+'-member-detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>');
                    $('#'+attVal+'_id').val('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    if(attVal=='guarantor'){
                    $('#guarantor_occupation_id').html('');
                    $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                    $('#guarantor_occupation_id').prop('disabled', false);
                    //$('.guarantor-name-section').show();
                    $('.guarantor-member-detail-box').show();
                    }
                    $('.loan-against-investment-plan').hide();
                    $('.investment-plan-input-number').html('');
                }
            }
        });
    });

    // Get registered member by id
    $(document).on('change','#co-applicant_auto_member_id,#guarantor_auto_member_id,#group_auto_member_id',function(){
        $.cookie('planTbaleCounter', '');
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');
        var loantype = $( "#loan option:selected" ).val();
        var type = 'member';
        var associateId = $('#acc_auto_member_id').val();

        if(loantype != 3 && attVal == 'co-applicant'){
            if(memberid != associateId){
                $('#co-applicant_auto_member_id').val('');
                $('.co-applicant-member-detail').hide();
                $('#'+attVal+'_designation').val('');
                $("#"+attVal+"_id_proof").val('') ;
                $("#"+attVal+"_address_id_proof").val('') ;
                $("#"+attVal+"_id_number").val('') ;
                $("#"+attVal+"_address_id_number").val('') ;
                swal("Warning!", "Co applicant and associate must be same!", "warning");
                return false;
            }else{
                $('.co-applicant-member-detail').show();
            }
        }/*else if(loantype == 3 && attVal == 'group-loan'){
            var groupMemberId = $('#group_auto_member_id').val();
            var gassociateId = $('#group_associate_id').val();
            if(groupMemberId != gassociateId){
                $('#group_auto_member_id').val('');
                $('.group-loan-member-detail').hide();
                swal("Warning!", "Co applicant and member must be same!", "warning");
                return false;
            }else{
                $('.group-loan-member-detail').show();
            }
        }*/else if(loantype == 3 && attVal == 'co-applicant'){
            var gassociateId = $('#group_associate_id').val();
            if(memberid != gassociateId){
                $('#co-applicant_auto_member_id').val('');
                $('.co-applicant-member-detail').hide();
                $('#'+attVal+'_designation').val('');
                $("#"+attVal+"_id_proof").val('') ;
                $("#"+attVal+"_address_id_proof").val('') ;
                $("#"+attVal+"_id_number").val('') ;
                $("#"+attVal+"_address_id_number").val('') ;
                swal("Warning!", "Co applicant and associate must be same!", "warning");
                return false;
            }else{
                $('.co-applicant-member-detail').show();
            }
        }

        currentRequest = $.ajax({
            type: "POST",
            url: "{!! route('loan.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid,'loantype':loantype,'attVal':attVal,'type':type},
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
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    if(attVal=='group-loan' && response.member.saving_account.length==0){
                        // $('#group_auto_member_id').val('');
                        // swal("Warning!", "You have not any ssb account", "warning");
                        // $('.'+attVal+'-member-detail').html('');
                        // $('#'+attVal+'_member_id').val('');
                        // $('#'+attVal+'_occupation_name').val('');
                        // $('#'+attVal+'_occupation').val('');
                        // $('.'+attVal+'-occupation-name').val('');
                        // $('.'+attVal+'-occupation').val('');
                        // return false;
                    }else{
                        $('#applicant_designation').val(response.carderName);
                        if(response.member.member_id_proofs){
                            $("#applicant_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.first_id_no+'') ;
                            $("#applicant_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.second_id_no+'') ;
                        }
                    }/*else if(attVal=='group-loan' && jQuery.inArray(memberid, guarantor) == -1){
                        $('#group_auto_member_id').val('');
                        swal("Warning!", "Applicant should be group member!", "warning");
                        $('.'+attVal+'-member-detail').html('');
                        $('#'+attVal+'_member_id').val('');
                        $('#'+attVal+'_occupation_name').val('');
                        $('#'+attVal+'_occupation').val('');
                        $('.'+attVal+'-occupation-name').val('');
                        $('.'+attVal+'-occupation').val('');
                        return false;
                    }*/

                    if(attVal=='guarantor'){
                        //$('.guarantor-name-section').hide();
                        $('#guarantor_occupation_id').html('');
                        $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                        $('#guarantor_occupation_id').append('<option selected value="'+response.occupation_id+'">'+response.occupation+'</option>');
                        $('#guarantor_occupation_id').prop('disabled', true);
                        //$('.guarantor-member-detail-box').hide();

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
                        //$('.guarantor-name-section').hide();

                        var gfirstName = response.member.first_name ? response.member.first_name : '';
                        var glastName = response.member.last_name ? response.member.last_name : '';

                        $('#'+attVal+'_name').val(gfirstName+' '+glastName);
                        $('#'+attVal+'_father_name').val(response.member.father_husband);
                        $('#'+attVal+'_dob').val(moment(response.member.dob).format('DD/MM/YYYY'));
                        $("#guarantor_marital_status option[value="+response.member.marital_status+"]").attr('selected', 'selected');
                        $('#local_address').val(response.member.address);
                        $('#'+attVal+'_mobile_number').val(response.member.mobile_no);
                    }
                    $('.'+attVal+'-member-detail').html(response.view);
                    $('#'+attVal+'_member_id').val(response.id);
                    $('#'+attVal+'_occupation_name').val(response.occupation);
                    $('#'+attVal+'_occupation').val(response.occupation_id);
                    $('.'+attVal+'-occupation-name').val(response.occupation);
                    $('.'+attVal+'-occupation').val(response.occupation_id);
                    $('#'+attVal+'_designation').val(response.carderName);
                    if(response.member.member_id_proofs){
                        $("#"+attVal+"_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.first_id_no+'') ;
                        $("#"+attVal+"_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.second_id_no+'') ;
                    }

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
                                                var list_fieldHTML = '<tr><td class="plan-name">'+response.planName+'<input type="hidden" name="investmentplanloanid['+key+']" value="'+value.id+'" class="form-control"></td><td class="account-id">'+value.account_number+'</td><td class="open-date">'+newDate+'</td><td class="due-date">'+duenewDate+'</td><td class="deposite-amount">'+value.current_balance+'<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-'+key+' form-control" value="'+value.current_balance+'"></td><td class="plan-months">'+months+'</td><td class="loan-amount-input"><input data-input="'+key+'" type="text" name="ipl_amount['+key+']" class="ipl_amount ipl_amount-'+key+' form-control" style="width: 104px"></td></tr>';
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
               /* else if(response.msg_type=="warning")
                {
                    $('#'+attVal+'_id').val('');
                    $('.'+attVal+'-member-detail').html('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false;
                }*/
                else
                {

                    $('.'+attVal+'-member-detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>');
                    $('#'+attVal+'_id').val('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    if(attVal=='guarantor'){
                    $('#guarantor_occupation_id').html('');
                    $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                    $('#guarantor_occupation_id').prop('disabled', false);
                    $('.guarantor-name-section').show();
                    $('.guarantor-member-detail-box').show();
                    $('.guarantor-name-section').show();
                    $('#'+attVal+'_name').val('');
                    $('#'+attVal+'_father_name').val('');
                    $('#'+attVal+'_dob').val('');
                    $("#guarantor_marital_status").val('');
                    $('#local_address').val('');
                    $('#'+attVal+'_mobile_number').val('');
                    }
                    $('.loan-against-investment-plan').hide();
                    $('.investment-plan-input-number').html('');
                }
            }
        });
    });

    // Get registered member by id
    $(document).on('change','#group_leader_member_id',function(){
        var memberid = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('loan.groupmember') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type=="success"){

                    if(response.member.saving_account.length==0){
                            $(this).val('');
                            swal("Warning!", "You have not any ssb account", "warning");
                            $('#group_leader_member_id').val('');
                            $('.group-member-detail').hide();
                            $('.group-member-detail-not-found').hide();
                            $('#group_leader_m_id').val('');
                            $('#group_lm_name').val('');
                            return false;
                    }

                    $('.group-member-detail').show();
                    $('.group-member-detail-not-found').hide();
                    $('#group_leader_m_id').val(response.member.id);

                    var firstName = response.member.first_name ? response.member.first_name : '';
                    var lastName = response.member.last_name ? response.member.last_name : '';
                    var name = firstName+' '+lastName;
                    $('#group_lm_name').val(name);
                }else{
                    $('.group-member-detail').hide();
                    $('.group-member-detail-not-found').show();
                    $('#group_leader_m_id').val('');
                }
            }
        });
    });

    // Datatables
     loantable = $('#loan-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('loan.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
              {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'},
            {data: 'account_number', name: 'account_number'},
            {data: 'member_name', name: 'member_name'},
            {data: 'member_id', name: 'member_id'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'tenure', name: 'tenure'},
            {data: 'emi_amount', name: 'emi_amount'},
            {data: 'transfer_amount', name: 'transfer_amount'},
            {data: 'loan_amount', name: 'loan_amount'},
            {data: 'file_charges', name: 'file_charges'},
{data: 'insurance_charge', name: 'insurance_charge'},
{data: 'file_charges_payment_mode', name: 'file_charges_payment_mode'},
            {data: 'outstanding_amount', name: 'outstanding_amount'},
            {data: 'last_recovery_date', name: 'last_recovery_date'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'total_payment', name: 'total_payment'},
            {data: 'approve_date', name: 'approve_date'},
            {data: 'application_date', name: 'application_date'},
            {data: 'status', name: 'status',
                "render":function(data, type, row){
                    if ( row.status == 0 ) {
                        return 'Pending';
                    } else if(row.status == 1) {
                        return 'Approved';
                    }else if(row.status == 2) {
                        return '<a href="javascript:void(0);"  data-toggle="modal" data-target="#rejection-view" data-rejection="'+row.rejection_description+'" class="view-rejection"><i class="icon-eye-blocked2  mr-2"></i>Rejected</a>';
                    }else if(row.status == 3) {
                        return 'Clear';
                    }else if(row.status == 4) {
                        return 'Due';
                    }

                }
            },

        ]
    });
    $(loantable.table().container()).removeClass( 'form-inline' );

     grouploantable = $('#group-loan-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('loan.group.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchGroupLoanForm=$('form#grouploanfilter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
              {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'},
            {data: 'account_number', name: 'account_number'},
            {data: 'member_name', name: 'member_name'},
            {data: 'member_id', name: 'member_id'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'tenure', name: 'tenure'},
              {data: 'emi_amount', name: 'emi_amount'},
            {data: 'loan_amount', name: 'loan_amount'},
             {data: 'amount', name: 'amount'},
            {data: 'file_charges', name: 'file_charges'},
{data: 'insurance_charge', name: 'insurance_charge'},
            {data: 'file_charges_payment_mode', name: 'file_charges_payment_mode'},
            {data: 'outstanding_amount', name: 'outstanding_amount'},
            {data: 'last_recovery_date', name: 'last_recovery_date'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'total_payment', name: 'total_payment'},
            {data: 'approve_date', name: 'approve_date'},
            {data: 'application_date', name: 'application_date'},
            {data: 'status', name: 'status'},

        ]
    });
    $(grouploantable.table().container()).removeClass( 'form-inline' );

    var loanId = $('#loanId').val();
    var loanType = $('#loanType').val();
    var loanEmiTable = $('#listtansaction').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.loan.emi_list') !!}",
            "type": "POST",
            "data":{'loanId':loanId,'loanType':loanType},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'transaction_id', name: 'transaction_id'},
            {data: 'date', name: 'date'},
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'description', name: 'descsription'},
            {data: 'penalty', name: 'penalty'},
            {data: 'deposite', name: 'deposite'},
            {data: 'jv_amount', name: 'jv_amount'},
            {data: 'balance', name: 'balance'},

            // {data: 'principal_amount', name: 'principal_amount'},
            // {data: 'opening_balance', name: 'opening_balance'},
        ]
    });
    $(loanEmiTable.table().container()).removeClass( 'form-inline' );

    $('.date_of_birth').datepicker( {
        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true,
        endDate: "today",
        maxDate: today
    });

    $('.application_date').datepicker( {
        format: "dd/mm/yyyy",
        orientation: "bottom",
        autoclose: true,
        endDate: "today",
        maxDate: today
    });

    var today = new Date();
    $('.from_date,.to_date').datepicker( {

        format: "dd/mm/yyyy",

        orientation: "top",

        autoclose: true,

        endDate: "today",

        maxDate: today

    });
	 $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');

        $('#loan_recovery_export').val(extension);

        $('form#filter').attr('action',"{!! route('branch.loan_kist_export') !!}");

        $('form#filter').submit();

        return true;

    });

	 $('.export-group-loan').on('click',function(){

        var extension = $(this).attr('data-extension');

        $('#group_loan_recovery_export').val(extension);

        $('form#grouploanfilter').attr('action',"{!! route('branch.group_loan_list_export') !!}");

        $('form#grouploanfilter').submit();

        return true;

    });

    $(document).on('change','.application_date',function(){
       var aDate = $(this).val();
       $('#created_date').val(aDate);
       var associateCode = $('#loan_associate_code').val();
       if(associateCode != ''){
        $('#loan_associate_code').trigger('change');
       }
    });

    $('.applciant-deatils-box').hide();
    $('.coapplciant-deatils-box').hide();
    $('.guarantor-deatils-box').hide();
    $('.group-information').hide();
    $('.staff-loan-section').hide();
    $('.other-loan-section').hide();
    $('.bank-details-section').hide();
    $('.emi-mode-section').hide();
    $('.group-emi-mode-section').hide();
    $(document).on('change','#loan',function(){
        $('#file_charge').val('');
        $('#loan_emi').val('');
        $('.emi_option').val('');
        $('.emi_period').val('');
        $('#loan_amount').val('');
        $('#loan_purpose').val('');
        $('.loanId').val($(this).val());
        $("#register-plan")[0].reset();
        var loan = $('option:selected', this).attr('data-val');
        $('.applicant-member-detail').html('');
        $('.co-applicant-member-detail').html('');
        $('.guarantor-member-detail').html('');
        if(loan == 'personal-loan'){
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').show();
            $('.guarantor-deatils-box').show();
            $('.group-information').hide();
            $('.staff-loan-section').show();
            //$('.personal-loan-section').show();
            $('.other-loan-section').hide();
            $('.bank-details-section').show();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.loan-against-investment-plan').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            $('.group-emi-mode').hide();
            $('.staff-emi-mode').hide();
            $('.investmentloan-emi-mode').hide();
            $('.personal-emi-mode').show();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            $('.cheque-box').show();
            $('.applicant-box').show();
            $('#amount').attr('readonly',false);
        }else if(loan == 'group-loan'){
            $('.group-information').show();
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').show();
            $('.guarantor-deatils-box').show();
            $('.staff-loan-section').hide();
            $('.personal-loan-section').hide();
            $('.other-loan-section').hide();
            $('.bank-details-section').hide();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.loan-against-investment-plan').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            $('.staff-emi-mode').hide();
            $('.investmentloan-emi-mode').hide();
            $('.personal-emi-mode').hide();
            $('.group-emi-mode').show();
            $('.cheque-box').show();
            $('.applicant-box').hide();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            $('#amount').attr('readonly',false);
        }else if(loan == 'staff-loan'){
            $('.group-information').hide();
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').hide();
            $('.guarantor-deatils-box').show();
            $('.staff-loan-section').show();
            $('.personal-loan-section').hide();
            $('.other-loan-section').hide();
            $('.bank-details-section').show();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.loan-against-investment-plan').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').show();
            $('.personal-emi-mode').hide();
            $('.group-emi-mode').hide();
            $('.investmentloan-emi-mode').hide();
            $('.staff-emi-mode').show();
            $('.cheque-box').show();
            $('.applicant-box').show();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            //$('#amount').attr('readonly',true);
        }else if(loan == 'loan-against-investment-plan'){
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').hide();
            $('.guarantor-deatils-box').hide();
            $('.group-information').hide();
            $('.staff-loan-section').show();
            $('.personal-loan-section').hide();
            $('.other-loan-section').hide();
            $('.bank-details-section').show();
            $('.loan-against-investment-plan').hide();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            $('.personal-emi-mode').hide();
            $('.group-emi-mode').hide();
            $('.staff-emi-mode').hide();
            $('.investmentloan-emi-mode').show();
            $('.cheque-box').hide();
            $('.applicant-box').show();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            $('#amount').attr('readonly',true);
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
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
        }
        $('#loan_type').val(loan);
    });

    $('.year').datepicker( {
        format: "yyyy",
        orientation: "top",
        autoclose: true,
        endDate: "today",
        maxDate: today
    });

    $(document).on('change','#applicant_id_proof,#applicant_address_id_proof,#co-applicant_id_proof,#co-applicant_address_id_proof,#guarantor_id_proof,#guarantor_address_id_proof',function(){
        var sectionval = $('option:selected', this).attr('data-val');
        var proofValue = $('option:selected', this).attr('data-proof-val');
        var loanType = $( "#loan option:selected" ).val();
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

        if(sectionval == 'applicant' && loanType == 3){
            if($('#group_auto_member_id').val() != ''){
                $('#applicant_id_number').val(proofValue);
            }else{
                $('#applicant_id_number').val('');
            }
        }

        if(sectionval == 'applicant_address' && loanType == 3){
            if($('#group_auto_member_id').val() != ''){
                $('#applicant_address_id_number').val(proofValue);
            }else{
                $('#applicant_address_id_number').val('');
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

    $(document).on('click','#co_applicant_checkbox',function(){
        $('#co_applicant_checkbox_val').val(1);
        var loanType = $( "#loan option:selected" ).val();
        if(loanType == 3){
            var associateId = $('#group_associate_id').val();
        }else{
            var associateId = $('#acc_auto_member_id').val();
        }
        if ($("input[type=checkbox]").is(":checked")) {
            $( "#co-applicant_auto_member_id" ).val(associateId);
            $( "#co-applicant_auto_member_id" ).trigger( "change" );
        } else {
            $( "#co-applicant_auto_member_id" ).val('');
            $( "#co-applicant_auto_member_id" ).trigger( "change" );
        }
    });

    $(document).on('click','.group-leader-m-id',function(){
        var glAutoId = $(this).val();
        var glID = $(this).attr('data-group');
        $('#group_leader_m_id').val(glAutoId);
        $('#group_leader_member_id').val(glID);
    });

    $(document).on('change','#applicant_income,#co-applicant_income,#guarantor_income',function(){
        var value = $(this).val();
        var section = $('option:selected', this).attr('data-val');
        if(value==2){
            $('.'+section+'-salary-remark').show();
        }else{
            $('.'+section+'-salary-remark').hide();
        }
    });

    $(document).on('change','#guarantor_occupation_id',function(){
        var value = $('option:selected', this).text();
        if(value=='Other'){
            $('.occupation-other-remark').show();
            $('.occupation-fields').hide();
        }else{
            $('.occupation-other-remark').hide();
            $('.occupation-fields').show();
        }
    });

    $(document).on('change','#amount,#emi_mode_option,#salary',function(){
        var value = $('#amount').val();
        var loantype = $( "#loan option:selected" ).val();
        var moPayment = $( "#emi_mode_option option:selected" ).attr('data-val');
        var moPaymentValue = $( "#emi_mode_option option:selected" ).val();
        if(loantype == 1){
            if((value < 10000 || value > 200000) && value!='')  {
                $('#loan-amount-error').show();
                $('#loan-amount-error').html('Please enter amount between 10000 to 200000');
                $('#loan_emi').val('');
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
                return false;
              }else{

                if(value >= 10000 && value <= 25000)  {
                    var fileCharge = 500;
                }else if(value > 25000 && value <= 50000){
                    var fileCharge = 1000;
                }else if(value > 50000){
                    var fileCharge = 2*value/100;
                }else{
                    var fileCharge = '';
                }
                if(moPayment == 'months' && moPaymentValue == 12){
                    var loanEmi = showpay(value,moPaymentValue,39.06,1200);
                    var rate = 39.06;
                }else if(moPayment == 'days' && moPaymentValue == 100){
                    var loanEmi = showpay(value,moPaymentValue,70.20,36500);
                    var rate = 70.20;
                }else if(moPayment == 'days' && moPaymentValue == 200){
                    var loanEmi = showpay(value,moPaymentValue,68.40,36500);
                    var rate = 68.40;
                }else if(moPayment == 'weeks' && moPaymentValue == 24){
                    var loanEmi = showpay(value,moPaymentValue,60.55,5200);
                    var rate = 60.55;
                }else if(moPayment == 'weeks' && moPaymentValue == 26){
                    var loanEmi = showpay(value,moPaymentValue,23,5200);
                    var rate = 23;
                }else if(moPayment == 'weeks' && moPaymentValue == 52){
                    var loanEmi = showpay(value,moPaymentValue,23,5200);
                    var rate = 23;
                }else{
                    var loanEmi = '';
                    var rate = '';
                }
                $('#file_charge').val(fileCharge);
                $('#loan_emi').val(loanEmi);

                if(moPayment == 'weeks' && moPaymentValue == 26){
                    $('.loan-emi-amount').html('');
               }else if(moPayment == 'weeks' && moPaymentValue == 52){
                    $('.loan-emi-amount').html('');
               }else if(loanEmi){
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
               }else{
                    $('.loan-emi-amount').html('');
               }

                /*if(loanEmi){
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
                }else{
                    $('.loan-emi-amount').html('');
                }*/
                $('#loan-amount-error').html('');
                $('#interest-rate').val(rate);
                $('#loan_amount').val(value);
                return true;
              }
        }else if(loantype == 2){

                if(value <= 10000)  {
                    var fileCharge = 0;
                }else if(value >= 10000 && value <= 25000){
                    var fileCharge = 500;
                }else if(value > 25000){
                    var fileCharge = 2*value/100;
                }else{
                    var fileCharge = '';
                }
                if(moPayment == 'months'){
                    var loanEmi = showpay(value,moPaymentValue,20.00,1200);
                    var rate = 20.00;
                }else{
                    var loanEmi = '';
                    var rate = '';
                }
                $('#file_charge').val(fileCharge);
                $('#loan_emi').val(loanEmi);
                if(loanEmi){
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
                }else{
                    $('.loan-emi-amount').html('');
                }
                $('#loan-amount-error').html('');
                $('#interest-rate').val(rate);
                $('#loan_amount').val(value);
                return true;
        }else{
            $('#loan-amount-error').html('');
        }
    });

    $(document).on('change','#emi_mode_option',function(){
        var emiOption = $('option:selected', this).attr('data-val');
        var emiPeriod = $('option:selected', this).val();
        if(emiOption == 'months'){
            $('.emi_option').val(1);
        }else if(emiOption == 'weeks'){
            $('.emi_option').val(2);
        }else if(emiOption == 'days'){
            $('.emi_option').val(3);
        }
        $('.emi_period').val(emiPeriod);
    });

    $(document).on('change','#salary',function(){
        var value = $(this).val();
        var loanAmount = value*25/100;
        $('#amount').val(loanAmount);
        $('.c-amount').val(loanAmount);
        $('#loan_amount').val(loanAmount);
        $('#amount').prop('readonly', true);
    });

    $(document).on('change','#purpose',function(){
        var value = $(this).val();
        $('#loan_purpose').val(value);
    });

    // Get registered member by id
    var x = 0; //Initial field counter
    var list_maxField = 10;
    $("#more-doc-button").click(function() {
        var hiddenDoc = $('.hidden_more_doc').val();
        if(hiddenDoc == 1){
            $('.more-doc').show();
            var countVal = $(this).attr('data-val');
            var increaseVal = countVal+1;

            if(x < list_maxField){
                x++; //Increment field counter
                var list_fieldHTML = '<div class="form-group row flex-grow-1"><label class="col-form-label col-lg-2">Doc Title</label><div class="col-lg-3"><input type="text" name="guarantor_more_doc_title['+x+']" id="guarantor_more_doc_title" class="form-control"></div><label class="col-form-label col-lg-2">Upload File</label><div class="col-lg-4"><input type="file" name="guarantor_more_upload_file['+x+']" id="guarantor_more_upload_file" class="form-control"></div><span><a href="javascript:void(0);" class="remove-doc-button" >Remove</a></span></div>'; //New input field html
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

    $(document).on('change','#number_of_member',function(){
        var mNumber = $(this).val();
        $('.m-input-number').html('');
        $('#group_leader_m_id').val('');
        $('#group_leader_member_id').val('');
        if(mNumber > 0){
            $('.group-loan-member-table').show();
        }else{
            $('.group-loan-member-table').hide();
        }
        var x = 1;
        for (var x = 1; x <= mNumber; x++) {
            var list_fieldHTML = '<tr><td><input data-input="'+x+'" type="text" name="m_id['+x+']" class="g-loan-member-id g-loan-member-id-'+x+' form-control" style="width: 104px"><input data-input="'+x+'" type="hidden" name="m_id['+x+']" class="g-loan-member-id g-loan-hidden-m-id-'+x+'"><input data-input="'+x+'" type="hidden" name="ml_emi['+x+']" class="g-loan-hidden-emi g-loan-hidden-emi-'+x+'"><input data-input="'+x+'" type="hidden" name="ml_file_charge['+x+']" class="g-loan-hidden-file-charge g-loan-hidden-file-charge-'+x+'"><input data-input="'+x+'" type="hidden" name="ml_interest_rate['+x+']" class="g-loan-hidden-interest-rate g-loan-hidden-interest-rate-'+x+'"></td><td><input data-input="'+x+'" type="text" name="m_name['+x+']" class="g-loan-member-name g-loan-member-name-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="f_name['+x+']" class="g-loan-member-fname g-loan-member-fname-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="m_amount['+x+']" class="g-loan-member-amount g-loan-member-amount-'+x+' form-control" style="width: 104px"></td><td><a target="blank" data-input="'+x+'" class="g-loan-member-s g-loan-member-s-'+x+'" href="javascript:void(0);" style="width: 104px"></a></td><td><input data-input="'+x+'" type="text" name="m_bn['+x+']" class="g-loan-member-bn g-loan-member-bn-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="m_bac['+x+']" class="g-loan-member-bac g-loan-member-bac-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="m_bi['+x+']" class="g-loan-member-bi g-loan-member-bi-'+x+' form-control" style="width: 104px" readonly=""></td><td><div class="custom-control custom-radio mb-3 "><input type="radio" id="group-leader-'+x+'" class="custom-control-input group-leader-m-id" name="g_l_m_id" data-group="" value="0"><label class="custom-control-label" for="group-leader-'+x+'">Yes</label></div></td></tr>';
            $('.m-input-number').append(list_fieldHTML); //Add field html
        }
        $('.group-loan-amount').val('');
        $('#loan_amount').val('');
    });

    $('.m-input-number').on('change','.g-loan-member-id',function(){
        var mId = $(this).val();
        var dInput = $(this).attr('data-input');
        guarantor.push(mId);
        $.ajax({
            type: "POST",
            url: "{!! route('loan.groupmember') !!}",
            dataType: 'JSON',
            data: {'memberid':mId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // console.log(response.member.member_bank_details[0].bank_name);
                if(response.msg_type=="success"){
                    //$('.g-loan-member-id-'+dInput+'').val(response.member.id);
                    if(response.member.saving_account.length==0){
                            // $(this).val('');
                            // swal("Warning!", "You have not any ssb account", "warning");
                            // $('.g-loan-member-id-'+dInput+'').val('')
                            // $('.g-loan-member-name-'+dInput+'').val('');
                            // $('.g-loan-member-fname-'+dInput+'').val('');
                            // $('.g-loan-member-bn-'+dInput+'').val('');
                            // $('.g-loan-member-s-'+dInput+'').html('');
                            // $('.g-loan-member-s-'+dInput+'').attr('href','');
                            // $('.g-loan-member-bac-'+dInput+'').val('');
                            // $('.g-loan-member-bi-'+dInput+'').val('');
                            // $('.g-loan-member-id-'+dInput+'').val('');
                            // $('.g-loan-hidden-m-id-'+dInput+'').val('');
                            // $('#group-leader-'+dInput+'').val('');
                            // $('#group-leader-'+dInput+'').attr('data-group','');
                            // return false;
                    }
                    var firstName = response.member.first_name ? response.member.first_name : '';
                    var lastName = response.member.last_name ? response.member.last_name : '';
                    var ass_name = firstName+' '+lastName;
                    $('.g-loan-member-name-'+dInput+'').val(ass_name);
                    $('.g-loan-member-fname-'+dInput+'').val(response.member.father_husband);
                    $('.g-loan-member-s-'+dInput+'').html(response.member.signature);
                    $('.g-loan-member-s-'+dInput+'').attr('href','asset/profile/member_signature/'+response.member.signature);

                    if(response.member.member_bank_details.length > 0 ){
                        $('.g-loan-member-bn-'+dInput+'').val(response.member.member_bank_details[0].bank_name);
                        $('.g-loan-member-bac-'+dInput+'').val(response.member.member_bank_details[0].account_no);
                        $('.g-loan-member-bi-'+dInput+'').val(response.member.member_bank_details[0].ifsc_code);
                    }else{
                        $('.g-loan-member-bn-'+dInput+'').val('');
                        $('.g-loan-member-bac-'+dInput+'').val('');
                        $('.g-loan-member-bi-'+dInput+'').val('');
                    }

                    $('.g-loan-hidden-m-id-'+dInput+'').val(response.member.id);
                    $('#group-leader-'+dInput+'').val(response.member.id);
                    $('#group-leader-'+dInput+'').attr('data-group',mId);

                }else if(response.msg_type=="warning"){
                    var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
                    var moPayment = $( ".group-information option:selected" ).val();
                    $('.g-loan-member-name-'+dInput+'').val('');
                    $('.g-loan-member-fname-'+dInput+'').val('');
                    $('.g-loan-member-bn-'+dInput+'').val('');
                    $('.g-loan-member-bac-'+dInput+'').val('');
                    $('.g-loan-member-s-'+dInput+'').html('');
                    $('.g-loan-member-s-'+dInput+'').attr('href','');
                    $('.g-loan-member-bi-'+dInput+'').val('');
                    $('.g-loan-member-id-'+dInput+'').val('');
                    $('.g-loan-hidden-m-id-'+dInput+'').val('');
                    $('.g-loan-member-amount-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').attr('data-group','');

                    var sum = 0;
                    $(".g-loan-member-amount").each(function(){
                        sum += +$(this).val();
                    });
                    $('.group-loan-amount').val(sum);
                    $('#loan_amount').val(sum);
                    $('.g-loan-hidden-emi-'+dInput+'').val('');
                    $('.g-loan-hidden-file-charge-'+dInput+'').val('');
                    /*if(moPaymentType == 'days' && moPayment == 100){
                        var loanEmi = showpay(value,moPayment,70.20,36500);
                    }else if(moPaymentType == 'days' && moPayment == 200){
                        var loanEmi = showpay(value,moPayment,68.40,36500);
                    }else if(moPaymentType == 'weeks' && moPayment == 12){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }else if(moPaymentType == 'weeks' && moPayment == 24){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }
                    $('#loan_emi').val(loanEmi);
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);

                    if(sum >= 10000 && sum <= 25000)  {
                        $('#file_charge').val(500);
                    }else if(sum > 25000 && sum <= 50000){
                        $('#file_charge').val(1000);
                    }else if(sum > 50000){
                        var fileCharge = 2*sum/100;
                        $('#file_charge').val(fileCharge);
                    }*/

                    /*swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false; */
                }else{
                    var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
                    var moPayment = $( ".group-information option:selected" ).val();
                    //$('.g-loan-member-id-'+dInput+'').val('');
                    $('.g-loan-member-name-'+dInput+'').val('');
                    $('.g-loan-member-fname-'+dInput+'').val('');
                    $('.g-loan-member-bn-'+dInput+'').val('');
                    $('.g-loan-member-bac-'+dInput+'').val('');
                    $('.g-loan-member-s-'+dInput+'').html('');
                    $('.g-loan-member-s-'+dInput+'').attr('href','');
                    $('.g-loan-member-bi-'+dInput+'').val('');
                    $('.g-loan-member-id-'+dInput+'').val('');
                    $('.g-loan-hidden-m-id-'+dInput+'').val('');
                    $('.g-loan-member-amount-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').attr('data-group','');
                    var sum = 0;
                    $(".g-loan-member-amount").each(function(){
                        sum += +$(this).val();
                    });
                    $('.group-loan-amount').val(sum);
                    $('#loan_amount').val(sum);
                    $('.g-loan-hidden-emi-'+dInput+'').val('');
                    $('.g-loan-hidden-file-charge-'+dInput+'').val('');
                    /*if(moPaymentType == 'days' && moPayment == 100){
                        var loanEmi = showpay(value,moPayment,70.20,36500);
                    }else if(moPaymentType == 'days' && moPayment == 200){
                        var loanEmi = showpay(value,moPayment,68.40,36500);
                    }else if(moPaymentType == 'weeks' && moPayment == 12){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }else if(moPaymentType == 'weeks' && moPayment == 24){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }
                    $('#loan_emi').val(loanEmi);
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);

                    if(sum >= 10000 && sum <= 25000)  {
                        $('#file_charge').val(500);
                    }else if(sum > 25000 && sum <= 50000){
                        $('#file_charge').val(1000);
                    }else if(sum > 50000){
                        var fileCharge = 2*sum/100;
                        $('#file_charge').val(fileCharge);
                    }*/

                    swal("Error!", "Member ID does not exists!", "error");
                }
            }
        });

    });

    $('.m-input-number').on('change','.g-loan-member-amount',function(){
        var dInput = $(this).attr('data-input');
        var fAamount = $('.group-loan-amount').val();
        var mId = $('.g-loan-member-id-'+dInput+'').val();
        var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
        var moPayment = $( ".group-information option:selected" ).val();
        var value = $('.g-loan-member-amount-'+dInput+'').val();
        if(mId != ''){
            if($(this).val() > 9999){
                var sum = 0;
                $(".g-loan-member-amount").each(function(){
                    sum += +$(this).val();
                });
                if(sum <= 200000){
                    $('.group-loan-amount').val(sum);
                 }
                 //else{
                //    swal("Error!", "Total amount should be between 20,000 to 2,00,000", "error");
                //    $('.g-loan-member-amount-'+dInput+'').val('');
                //    var sum = 0;
                //     $(".g-loan-member-amount").each(function(){
                //         sum += +$(this).val();
                //     });
                //     $('#loan_amount').val(sum);
                //     $('.group-loan-amount').val(sum);
                //     $('#loan_emi').val('');
                //     //$('.loan-emi-amount').html('Loan EMI: ');
                //     $('.loan-emi-amount').html('');
                // }
            }else{
                $('.g-loan-member-amount-'+dInput+'').val('');
                var sum = 0;
                $(".g-loan-member-amount").each(function(){
                    sum += +$(this).val();
                });
                $('.group-loan-amount').val(sum);
                $('#loan_amount').val(sum);
                swal("Error!", "Enter amount greater than 10000", "error");
                $('#loan_emi').val('');
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
            }

            if(moPaymentType == 'days' && moPayment == 100){
                var loanEmi = showpay(value,moPayment,70.20,36500);
                var rate = 70.20;
            }else if(moPaymentType == 'days' && moPayment == 200){
                var loanEmi = showpay(value,moPayment,68.40,36500);
                var rate = 68.40;
            }else if(moPaymentType == 'weeks' && moPayment == 12){
                var loanEmi = showpay(value,moPayment,107.94,5200);
                var rate = 107.94;
            }else if(moPaymentType == 'weeks' && moPayment == 24){
                var loanEmi = showpay(value,moPayment,60.55,5200);
                var rate = 60.55;
            }else if(moPaymentType == 'weeks' && moPayment == 26){
                var loanEmi = showpay(value,moPayment,23,5200);
                var rate = 23;
            }else if(moPaymentType == 'weeks' && moPayment == 52){
                var loanEmi = showpay(value,moPayment,23,5200);
                var rate = 23;
            }

            if(value >= 10000 && value <= 25000)  {
                var fileCharge = 500;
            }else if(value > 25000 && value <= 50000){
                var fileCharge = 1000;
            }else if(value > 50000){
                var fileCharge = 2*value/100;
            }

            $('.g-loan-hidden-file-charge-'+dInput+'').val(fileCharge);
            $('.g-loan-hidden-emi-'+dInput+'').val(loanEmi);
            $('.g-loan-hidden-interest-rate-'+dInput+'').val(rate);

            //$('.loan-emi-amount').html('Loan EMI: '+loanEmi);
        }
    });

    $(document).on('change','.group-information',function(){
        var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
        var moPayment = $( ".group-information option:selected" ).val();
        var sum = 0;
        $(".g-loan-member-amount").each(function(){
            sum += +$(this).val();
        });
        $('.g-loan-member-amount').val();
        $('#amount').val();
        $('#loan_amount').val();
        /*if(moPaymentType == 'days' && moPayment == 100){
            var loanEmi = showpay(sum,moPayment,70.20,36500);
        }else if(moPaymentType == 'days' && moPayment == 200){
            var loanEmi = showpay(sum,moPayment,68.40,36500);
        }else if(moPaymentType == 'weeks' && moPayment == 12){
            var loanEmi = showpay(sum,moPayment,107.94,5200);
        }else if(moPaymentType == 'weeks' && moPayment == 24){
            var loanEmi = showpay(sum,moPayment,107.94,5200);
        }

        if(sum >= 10000 && sum <= 25000)  {
            var fileCharge = 500;
        }else if(sum > 25000 && sum <= 50000){
            var fileCharge = 1000;
        }else if(sum > 50000){
            var fileCharge = 2*sum/100;
        }

        $('#file_charge').val(fileCharge);
        $('#loan_emi').val(loanEmi);
        $('.loan-emi-amount').html('Loan EMI: '+loanEmi);*/
    });

    $('.investment-plan-input-number').on('change','.ipl_amount',function(){
        var dataval = $(this).attr('data-input');
        var dAmount = $('.hidden_deposite_amount-'+dataval+'').val();
        var apporveAmount = (dAmount*80)/100;
        var rAmount = $(this).val();
         if(apporveAmount < rAmount){
             swal("Error!", "Wrong Amount!", "error");
             $(this).val('');
         }
        var sum = 0;
        $(".ipl_amount").each(function(){
            sum += +$(this).val();
        });
        $('#amount').val(sum);
        $('.c-amount').val(sum);
        $('#loan_amount').val(sum);
        $('#amount').prop('readonly', true);
        //if(sum >= 10000){
            var loanEmi = showpay(sum,12,28.40,1200);
            var rate = 28.40;
            $('#loan_emi').val(loanEmi);

            if(loanEmi){
                $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
            }else{
                $('.loan-emi-amount').html('');
            }
            $('#interest-rate').val(rate);
        //}
    });


    $(document).on('click','.submit-loan-form',function(){
        var loantype = $( "#loan option:selected" ).val();
        var aDate = $( "#created_date" ).val();
        if(loantype == '' || aDate == ''){
            swal("Error!", "Please select a plan and application date first!", "error");
            return false;
        }else{
            return true;
        }
    });

    $(document).on('click','.view-rejection',function(){
        var corrections = $(this).attr('data-rejection');
        $('.loan-rejected-description').html('')
        $('.loan-rejected-description').html(corrections)
    });

    $('#loan_emi_payment_mode').on('change',function(){
        var paymentMode = $('option:selected', this).val();
        var paymentMode = $('option:selected', this).val();
        var date = $('.application_date').val();

        if(date == ''){
            var branch = $('#loan_emi_payment_mode').val('');
            swal("Warning!", "Please select a transfer date first!", "warning");
            return false;
        }

        if(paymentMode == 0 && paymentMode != ''){
            $('.ssb-account').show();
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
            $('#total_online_amount').val('');
        }else if(paymentMode == 1 && paymentMode != ''){
            $('.ssb-account').hide();
            $('.other-bank').show();
        }else{
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
            $('#total_online_amount').val('');
        }
        $('.cheque-transaction').hide();
        $('.online-transaction').hide();
    });

    $('#cheque_number').on('change',function(){
        var chequeDate = $('option:selected', this).attr('data-cheque-date');
        var chequeAmount = $('option:selected', this).attr('data-cheque-amount');
        var first_date = moment(""+chequeDate+"").format('DD/MM/YYYY');
        $('#cheque_date').val(first_date);
        $('#cheque_amount').val(chequeAmount);
    });

    $('#bank_name').on('change',function(){
        var accountNumber = $('option:selected', this).attr('data-account-number');
        $('#account_number').val(accountNumber);
    });

    $(document).on('change','#company_bank', function () {
        var account = $('option:selected', this).val();
        $('#company_bank_account_number').val('');
        $('#bank_account_number').val('');
        $('.c-bank-account').hide();
        $('.'+account+'-bank-account').show();
        $('#company_bank_account_balance').val('');
    });

    $('#bank_transfer_mode').on('change',function(){
        var bankTransferMode = $('option:selected', this).val();
        if(bankTransferMode == 0 && bankTransferMode != ''){
            $('.cheque-transaction').show();
            $('.online-transaction').hide();
            $('#utr_transaction_number').val('');
            $('#total_amount').val('');
            $('#rtgs_neft_charge').val('');
            $('#total_online_amount').val('');
        }else if(bankTransferMode == 1 && bankTransferMode != ''){
            $('.online-transaction').show();
            $('.cheque-transaction').hide();
            $('#cheque_id').val('');
            $('#total_amount').val('');
        }else{
            $('.online-transaction').hide();
            $('.cheque-transaction').hide();
            $('#cheque_id').val('');
            $('#total_amount').val('');
             $('#utr_transaction_number').val('');
            $('#total_amount').val('');
            $('#rtgs_neft_charge').val('');
            $('#total_online_amount').val('');
        }
    });

    $(document).on('click', '.pay-emi', function(e){
        var loanId = $(this).attr('data-loan-id');
        var loanEMI = $(this).attr('data-loan-emi');
        var ssbAmount = $(this).attr('data-ssb-amount');
        var ssbId = $(this).attr('data-ssb-id');
        var recoveredAmount = $(this).attr('data-recovered-amount');
        var lastRecoveredAmount = $(this).attr('data-last-recovered-amount');
        var closingAmount = $(this).attr('data-closing-amount');
        var penaltyAmount = $(this).attr('data-penalty-amount');
        var dueAmount = $(this).attr('data-due-amount');
        $('#loan_id').val(loanId);
        $('#loan_emi_amount').val(loanEMI);
        //$('#ssb_account').val(ssbAmount);
        //$('#ssb_id').val(ssbId);
        $('#recovered_amount').val(recoveredAmount);
        $('#closing_amount').val(closingAmount);
        $('#due_amount').val(dueAmount);
        $('#last_recovered_amount').val(lastRecoveredAmount);
        if(penaltyAmount != ''){
            //$('#penalty_amount').val(penaltyAmount);
            //$('#penalty_amount').attr('readonly',false);
        }else{
            $('#penalty_amount').val('');
            $('#penalty_amount').attr('readonly',true);
        }
    })

    $(document).on('keyup','#amount',function(){
        $(".c-amount").val($(this).val());
    });

    $(document).on('keyup','#purpose',function(){
        $(".purpose-loan").val($(this).val());
    });

    $(document).on('change', '#loan_branch', function(e){
        var loanId = $(this).val();
        $('#cheque_number').val('');
        $('#cheque_date').val('');
        $('.branch-cheques').hide();
        $('.'+loanId+'-branch').show();
    })

    $('#loan_associate_code').on('change',function(){
        var associateCode = $(this).val();
        var applicationDate = $('.application_date').val();
        $.ajax({
            type: "POST",
            url: "{!! route('loan.getcollectorassociate') !!}",
            dataType: 'JSON',
            data: {'code':associateCode,'applicationDate':applicationDate},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type=='success'){
                    var firstName = response.collectorDetails.first_name ? response.collectorDetails.first_name : '';
                    var lastName = response.collectorDetails.last_name ? response.collectorDetails.last_name : '';
                    $('#associate_member_id').val(response.collectorDetails.id);
                    $('#loan_associate_name').val(firstName+' '+lastName);
                    $('#ssb_account_number').val(response.collectorDetails.saving_account[0].account_no);
                    $('#ssb_account').val(response.ssbAmount);
                    $('#ssb_id').val(response.collectorDetails.saving_account[0].id);
                }else if(response.msg_type=='error'){
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

    $('#deposite_amount,#penalty_amount').on('change',function(){
        if($('#deposite_amount').val()){
            var depositAmount = $('#deposite_amount').val();
        }else{
            var depositAmount = 0;
        }
        if($('#penalty_amount').val()){
            var penaltyAmount = $('#penalty_amount').val();
        }else{
            var penaltyAmount = 0;
        }
        $('#cheque_total_amount').val(parseInt(depositAmount)+parseInt(penaltyAmount));
        $('#total_online_amount').val(parseInt(depositAmount)+parseInt(penaltyAmount));
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

function showpay(amount,month,rate,divideBy) {
    if ((amount == null || amount.length == 0) || (month == null || month.length == 0) || (rate == null || rate.length == 0))
    {
        var emi = '';
        return emi;
    }
    else
    {
        var princ = amount;
        var term  = month;
        var intr  = rate / divideBy;
        var emi = princ * intr / (1 - (Math.pow(1/(1 + intr), term)));
        return Math.round(emi);
    }
}

function searchForm()

{

    if($('#filter').valid())

    {

        $('#is_search').val("yes");

        loantable.draw();

    }

}

function resetForm()

{

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

    loantable.draw();

}

function searchGroupLoanForm()

{

    if($('#grouploanfilter').valid())

    {

        $('#is_search').val("yes");

        grouploantable.draw();

    }

}

function resetGroupLoanForm()

{

    var form = $("#grouploanfilter"),

    validator = form.validate();

    validator.resetForm();

    form.find(".error").removeClass("error");

    $('#is_search').val("no");

    $('#date').val('');

    $('.from_date').val('');

    $('.to_date').val('');

    $('#loan_account_number').val('');

    $('#member_name').val('');

    $('#member_id').val('');

    $('#associate_code').val('');

    $('#plan').val('');

    $('#status').val('');

    grouploantable.draw();

}


</script> -->

<script type="text/javascript">
    var loantable;
    var  grouploantable;
$(document).ready(function() {
     var currentRequest = null;
     var guarantor = [];
     var groupMemberId = [];
     const today = $('#created_at').val();
    // Investment Form validations
    $('#register-plan').validate({ // initialize the plugin
        rules: {
            'loan' : 'required',
            'amount' : {required: true, number: true,checkAmount:true},
            'days' : 'required',
            'months' : 'required',
            'purpose' : 'required',
            'group_activity' : 'required',
            'group_leader_member_id' : 'required',
            'number_of_member' : 'required',
            'salary' : {required: true, number: true},
            //'bank_account' : 'required',
            //'ifsc_code' : 'required',
            //'bank_name' : 'required',
            'acc_member_id' : {required: true, number: true},
            'applicant_id' : {required: true, number: true},
            'applicant_address_permanent' : 'required',
            'applicant_address_temporary' : 'required',
            //'applicant_occupation' : 'required',
            //'applicant_organization' : 'required',
            //'applicant_designation' : 'required',
            'applicant_monthly_income' : {required: true, number: true},
            'applicant_year_from' : {required: true, number: true},
            'applicant_bank_name' : 'required',
            'applicant_bank_account_number' : {required: true, number: true,minlength: 8,maxlength: 20},
            'applicant_ifsc_code' : {required: true,checkIfsc:true,},
            'applicant_cheque_number_1' : {required: true, number: true, notEqual: "#applicant_cheque_number_2", notEqual1: "#co-applicant_cheque_number-1", notEqual2: "#co-applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},
            'applicant_cheque_number_2' : {required: true, number: true, notEqual: "#applicant_cheque_number_1", notEqual1: "#co-applicant_cheque_number-1", notEqual2: "#co-applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},
            'applicant_id_proof' : 'required',
            'applicant_id_number' : {required: true, checkIdNumber : '#applicant_id_proof'},
            'applicant_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'applicant_address_id_proof' : 'required',
            'applicant_address_id_number' : {required: true, checkIdNumber : '#applicant_address_id_proof'},
            'applicant_address_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'applicant_income' : 'required',
            'applicant_income_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'applicant_security' : 'required',
            'co-applicant_address_permanent' : 'required',
            'co-applicant_address_temporary' : 'required',
            'co-applicant_bank_account_number' : {number: true,minlength: 8,maxlength: 20},
            'co-applicant_ifsc_code' : {checkIfsc:true},
            //'co-applicant_occupation' : 'required',
            //'co-applicant_organization' : 'required',
            //'co-applicant_designation' : 'required',
            'co-applicant_monthly_income' : {required: true, number: true},
            'co-applicant_year_from' : {required: true, number: true},
            /*'co-applicant_bank_name' : 'required',
            'co-applicant_bank_account_number' : {required: true, number: true},
            'co-applicant_ifsc_code' : {required: true},
            'co-applicant_cheque_number_1' : {required: true, number: true},
            'co-applicant_cheque_number_2' : {required: true, number: true},
            'co-applicant_id_proof' : 'required',
            'co-applicant_id_number' : {required: true, checkIdNumber : '#co-applicant_id_proof'},*/
            'co-applicant_id_file' : {extension: "jpf|jpg|pdf|jpeg",required:true},
            /*'co-applicant_address_id_proof' : 'required',
            'co-applicant_address_id_number' : {required: true, checkIdNumber : '#co-applicant_address_id_proof'},
            'co-applicant_address_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'co-applicant_income' : 'required',
            'co-applicant_income_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'co-applicant_security' : 'required',
            'co-applicant_cheque_number_1' : {required: true, number: true, notEqual: "#co-applicant_cheque_number_2", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},
            'co-applicant_cheque_number_2' : {required: true, number: true, notEqual: "#co-applicant_cheque_number-1", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#guarantor_cheque_number_1", notEqual4: "#guarantor_cheque_number_2"},*/
            'guarantor_member_id' : {required: true, number: true},
            'guarantor_name' : 'required',
            'guarantor_father_name' : 'required',
            'guarantor_dob' : 'required',
            'guarantor_marital_status' : 'required',
            'local_address' : 'required',
            'guarantor_ownership' : 'required',
            'guarantor_temporary_address' : 'required',
            'guarantor_mobile_number' : {required: true,number: true,minlength: 10,maxlength:12},
            'guarantor_educational_qualification' : 'required',
            'guarantor_dependents_number' : {required: true,number: true},
            'guarantor_bank_account_number' : {number: true,minlength: 8,maxlength: 20},
            'guarantor_ifsc_code' : {checkIfsc:true},
            //'guarantor_occupation' : 'required',
            //'guarantor_organization' : 'required',
            'guarantor_monthly_income' : {required: true, number: true},
            'guarantor_year_from' : {required: true, number: true},
            /*'guarantor_bank_name' : 'required',
            'guarantor_bank_account_number' : {required: true, number: true},
            'guarantor_ifsc_code' : {required: true},
            'guarantor_cheque_number_1' : {required: true, number: true},
            'guarantor_cheque_number_2' : {required: true, number: true},
            'guarantor_id_proof' : 'required',
            'guarantor_id_number' : {required: true, checkIdNumber : '#guarantor_id_proof'},*/
            'guarantor_id_file' : {extension: "jpf|jpg|pdf|jpeg",required:true},
            /*'guarantor_address_id_proof' : 'required',
            'guarantor_address_id_number' : {required: true, checkIdNumber : '#guarantor_address_id_proof'},
            'guarantor_address_id_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'guarantor_income' : 'required',
            'guarantor_income_file' : {extension: "jpf|jpg|pdf|jpeg"},
            //'guarantor_more_doc_title' : 'required',*/
            'guarantor_more_upload_file' : {extension: "jpf|jpg|pdf|jpeg"},
            'guarantor_security' : 'required',
            'co-applicant_auto_member_id' : 'required',
            'emi_mode_option' : 'required',
            'acc_auto_member_id' : 'required',
            'group_associate_id' : 'required',
            'guarantor_occupation_id' : 'required',
            /*'guarantor_cheque_number_1' : {required: true, number: true, notEqual: "#guarantor_cheque_number_2", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#co-applicant_cheque_number-1", notEqual4: "#co-applicant_cheque_number_2"},
            'guarantor_cheque_number_2' : {required: true, number: true, notEqual: "#guarantor_cheque_number_1", notEqual1: "#applicant_cheque_number_1", notEqual2: "#applicant_cheque_number_2", notEqual3: "#co-applicant_cheque_number-1", notEqual4: "#co-applicant_cheque_number_2"},*/
        }
    });

    $('#loan_emi').validate({ // initialize the plugin
        rules: {
            'application_date' : {required: true},
            'loan_associate_code' : {required: true,number: true},
            'loan_emi_payment_mode' : 'required',
            'ssb_account' : {required: true,number: true},
            'deposite_amount' : {required: true,number: true, lessThanEquals: '#closing_amount',checkAmount:true},
            'transaction_id' : {required: true,number: true},
            'account_number' : {required: true,number: true},
            'customer_bank_name' : 'required',
            'customer_bank_account_number' : {required: true,number: true},
            'customer_branch_name' : {required: true},
            'customer_ifsc_code' : {required: true},
            'company_bank' : {required: true},
            'company_bank_account_number' : {required: true,number: true},
            'company_bank_account_balance' : {required: true},
            'bank_transfer_mode' : {required: true},
            'utr_transaction_number' : {required: true},
            'online_total_amount' : {required: true},
            'cheque_id' : {required: true},
            'cheque_total_amount' : {required: true},
            'customer_cheque' : {required: true,number: true},
            'bank_account_number' : {required: true},
        },
        submitHandler: function() {
            var paymentModeVal = $( "#loan_emi_payment_mode option:selected").val();
            var depositeAmount = $( "#deposite_amount").val();
            if(paymentModeVal==0){

                var ssbAmount = $( "#ssb_account").val();
                if(parseInt(depositeAmount) > parseInt(ssbAmount)){
                    $('.ssbamount-error').show();
                    $('.ssbamount-error').html('Amount should be less than OR equals current available amounts.');
                    //event.preventDefault();
                    return false;
                }
            }if(paymentModeVal==3){
                var checkAmount = $( "#cheque_amount").val();
                if(parseInt(depositeAmount) != parseInt(checkAmount)){
                    $('.ssbamount-error').show();
                    $('.ssbamount-error').html('Amount should be equal to cheque amounts.');
                    return false;
                }
            }else{
                $('.ssbamount-error').html('');
                //return true;
            }

            $('.payloan-emi').prop('disabled', true);
            return true;

        }
    });

    $.validator.addMethod("lessThanEquals",
    function (value, element, param) {
          var $otherElement = $(param);
          return parseInt(value, 10) <= parseInt($otherElement.val(), 10);
       return value > target.val();
    }, "Amount should be less than OR equals closer amount.");

    jQuery.validator.addMethod("notEqual", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual1", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual2", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual3", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual4", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    jQuery.validator.addMethod("notEqual5", function(value, element, param) {
     return this.optional(element) || value != $(param).val();
    }, "Cheque Number should be different");

    // Get registered member by id
    $(document).on('change','#acc_auto_member_id,#group_associate_id',function(){
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');

        $.ajax({
            type: "POST",
            url: "{!! route('loan.associatemember') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.resCount > 0)
                {
                    $('.'+attVal+'-member-detail-not-found').hide();
                    $('.'+attVal+'-member-detail').show();
                    var firstName = response.member[0].first_name ? response.member[0].first_name : '';
                    var lastName = response.member[0].last_name ? response.member[0].last_name : '';

                    var ass_name = firstName+' '+lastName;
                    ass_name ? $('#acc_name').val(ass_name) : $('#acc_name').val("Name N/A");
                    response.bAccount ? $('.'+attVal+'-bank-account').val(response.bAccount) : $('.'+attVal+'-bank-account').val("");
                    response.bIfsc ? $('.'+attVal+'-ifsc-code').val(response.bIfsc) : $('.'+attVal+'-ifsc-code').val("");
                    response.bName ? $('.'+attVal+'-bank-name').val(response.bName) : $('.'+attVal+'-bank-name').val("");
                    response.member[0].carders_name ? $('#acc_carder').val(response.member[0].carders_name) : $('#acc_carder').val("Carder N/A");
                    $('.ass-member-id').val(response.member[0].id);
                    $('.'+attVal+'-id').val(response.member[0].id);
                    $('.'+attVal+'-name').val(ass_name);
                }
                else
                {
                    $('.'+attVal+'-bank-account').val("");
                    $('.'+attVal+'-ifsc-code').val("");
                    $('.'+attVal+'-bank-name').val("");
                    $('.'+attVal+'-member-detail').hide();
                    $('.'+attVal+'-member-detail-not-found').show();
                    $('#acc_auto_member_id').val('');
                    $('#group_associate_id').val('');
                    $('.ass-member-id').val('');
                }
            }
        });
    });

    // Get registered member by id
    $(document).on('change','#applicant_id',function(){
        $('#total_deposit').val('');
        var created_date=$('#created_date').val();
        $.cookie('planTbaleCounter', '');
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');
        var loantype = $( "#loan option:selected" ).val();
        var type = 'applicant';
        currentRequest = $.ajax({
            type: "POST",
            url: "{!! route('loan.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid,'loantype':loantype,'attVal':attVal,'type':type,datesys:created_date},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend : function()    {
                if(currentRequest != null) {
                    currentRequest.abort();
                }
            },
            success: function(response) {
                //console.log(response);
                if(response.msg_type=="success")
                {
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
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
                        //$('.guarantor-name-section').hide();
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
                    $('#total_deposit').val(response.totalDeposit);
                    $('#'+attVal+'_member_id').val(response.id);
                    $('#'+attVal+'_occupation_name').val(response.occupation);
                    $('#'+attVal+'_occupation').val(response.occupation_id);
                    $('.'+attVal+'-occupation-name').val(response.occupation);
                    $('.'+attVal+'-occupation').val(response.occupation_id);
                    $('#'+attVal+'_designation').val(response.carderName);

                    $("#"+attVal+"_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.first_id_no+'') ;
                    $("#"+attVal+"_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.second_id_no+'') ;
                    const loanArray = [1, 2];
                    if((jQuery.inArray(loantype, loanArray)) && response.member.saving_account.length==0   )
                    {

                        $('#show_mwmber_detail').hide();
                        $('#applicant_id').val('');
                        $('#total_deposit').val('');
                        swal('Warning','Please open saving account (SSB) then register for loan!','warning');
						$('.loan-against-investment-plan').hide();
                    }
                    if(loantype==4   ){

                        $('.investment-plan-input-number').html('');
                        if(response.member['associate_investment'].length!=0){
                            var count = response.member['associate_investment'].length;
                            //$.cookie('planTbaleCounter', '');
                            var isRecordExist = false;
                            var i = 0;
                            var invesmentLength = response.member['associate_investment'].length;
                            $('.loan-against-investment-plan').show();
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
                                            //if(sixMonthsFromNow.toISOString() >= value.created_at){
                                                isRecordExist = true;
                                                $.cookie('planTbaleCounter', key);
                                                var list_fieldHTML = '<tr><td class="plan-name">'+response.planName+'<input type="hidden" name="investmentplanloanid['+key+']" value="'+value.id+'" class="form-control"></td><td class="account-id">'+value.account_number+'</td><td class="open-date">'+newDate+'</td><td class="due-date">'+duenewDate+'</td><td class="deposite-amount">'+value.current_balance+'<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-'+key+' form-control" value="'+value.current_balance+'"></td><td class="plan-months">'+months+'</td><td class="loan-amount-input"><input data-input="'+key+'" type="text" name="ipl_amount['+key+']" class="ipl_amount ipl_amount-'+key+' form-control" style="width: 104px"></td></tr>';
                                                $('.investment-plan-input-number').append(list_fieldHTML);

                                            //}
                                        }

                                    });

                                }


                                i++;
                                /*if(i==invesmentLength && isRecordExist==false){
                                    swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                                    $('.applicant-member-detail').html('');
                                    $('.'+attVal+'-member-detail').html('');
                                    $('#'+attVal+'_member_id').val('');
                                    $('#'+attVal+'_id').val('');
                                }*/
                            });
                        }else if(response.member.saving_account.length!=0 && response.member['associate_investment'].length!=0){

                                    $('#applicant_id').val('');
                                    $('#show_mwmber_detail').hide();
                                    swal('!Warning','Investment Account Not Found!','warning');
                                    $('.loan-against-investment-plan').hide();

                            //swal("Warning!", "You have not any investment plan in 6 months!", "warning");
                            /*$('.applicant-member-detail').html('');
                            $('.'+attVal+'-member-detail').html('');
                            $('#'+attVal+'_member_id').val('');
                            $('#'+attVal+'_id').val('');*/
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
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    $('#group_auto_member_id').val('');
                    $('#total_deposit').val('');
                    swal("Warning!", response.msg, "warning");

                    return false;
                }
               /* else if(response.msg_type=="warning")
                {
                    $('#'+attVal+'_id').val('');
                    $('.'+attVal+'-member-detail').html('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false;
                }*/
                else
                {
                    $('.'+attVal+'-member-detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>');
                    $('#'+attVal+'_id').val('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    $('#total_deposit').val('');
                    if(attVal=='guarantor'){
                    $('#guarantor_occupation_id').html('');
                    $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                    $('#guarantor_occupation_id').prop('disabled', false);
                    //$('.guarantor-name-section').show();
                    $('.guarantor-member-detail-box').show();
                    }
                    $('.loan-against-investment-plan').hide();
                    $('.investment-plan-input-number').html('');
                }
            }
        });
    });


    $('#penalty_amount').on('change',function(){
       var penaltyAmount = $(this).val();
       var deDate = $('#deposite_date').val();
       var loanId = $('.pay-emi').attr('data-loan-id');
       var type = $('#type').val();

       if(penaltyAmount > 0){
           $.ajax({
           type: "POST",

           url: "{!! route('branch.loan.getgstLatePenalty') !!}",

           dataType: 'JSON',

           data: {'loanId':loanId,'penaltyAmount':penaltyAmount,'loanType':type,'deDate':deDate},

           headers: {

               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

           },

           success: function(response) {

               if(response.gstAmount > 0)
               {
                   if(response.label1 && response.label2)
                   {
                       $('.gst1').show();
                       $('#label1').html(response.label1);
                       $('#label2').html(response.label2);
                       $('#sgst_amount').val(response.gstAmount);
                       $('#cgst_amount').val(response.gstAmount);
                       $('.gst2').hide();
                   }
                   else{
                       $('.gst2').show();
                       $('#label3').html(response.label1);
                       $('#igst_amount').val(response.gstAmount);
                       $('.gst1').hide();
                   }




               }else{
                       $('.gst1').hide();
                       $('.gst2').hide();
                       $('#sgst_amount').hide();
                       $('#cgst_amount').hide();
                       $('#igst_amount').hide();
               }

           }

       })
       }else{
                       $('.gst1').hide();
                       $('.gst2').hide();
                       $('#sgst_amount').hide();
                       $('#cgst_amount').hide();
                       $('#igst_amount').hide();
               }

   })
    // Get registered member by id
    $(document).on('change','#co-applicant_auto_member_id,#guarantor_auto_member_id,#group_auto_member_id',function(){
        $.cookie('planTbaleCounter', '');
        var memberid = $(this).val();
        var attVal = $(this).attr('data-val');
        var loantype = $( "#loan option:selected" ).val();
        var type = 'member';
        var associateId = $('#acc_auto_member_id').val();

        if(loantype != 3 && attVal == 'co-applicant'){
            if(memberid != associateId){
                $('#co-applicant_auto_member_id').val('');
                $('.co-applicant-member-detail').hide();
                $('#'+attVal+'_designation').val('');
                $("#"+attVal+"_id_proof").val('') ;
                $("#"+attVal+"_address_id_proof").val('') ;
                $("#"+attVal+"_id_number").val('') ;
                $("#"+attVal+"_address_id_number").val('') ;
                swal("Warning!", "Co applicant and associate must be same!", "warning");
                return false;
            }else{
                $('.co-applicant-member-detail').show();
            }
        }/*else if(loantype == 3 && attVal == 'group-loan'){
            var groupMemberId = $('#group_auto_member_id').val();
            var gassociateId = $('#group_associate_id').val();
            if(groupMemberId != gassociateId){
                $('#group_auto_member_id').val('');
                $('.group-loan-member-detail').hide();
                swal("Warning!", "Co applicant and member must be same!", "warning");
                return false;
            }else{
                $('.group-loan-member-detail').show();
            }
        }*/else if(loantype == 3 && attVal == 'co-applicant'){
            var gassociateId = $('#group_associate_id').val();
            if(memberid != gassociateId){
                $('#co-applicant_auto_member_id').val('');
                $('.co-applicant-member-detail').hide();
                $('#'+attVal+'_designation').val('');
                $("#"+attVal+"_id_proof").val('') ;
                $("#"+attVal+"_address_id_proof").val('') ;
                $("#"+attVal+"_id_number").val('') ;
                $("#"+attVal+"_address_id_number").val('') ;
                swal("Warning!", "Co applicant and associate must be same!", "warning");
                return false;
            }else{
                $('.co-applicant-member-detail').show();
            }
        }

        currentRequest = $.ajax({
            type: "POST",
            url: "{!! route('loan.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid,'loantype':loantype,'attVal':attVal,'type':type},
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
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    if(attVal=='group-loan' && response.member.saving_account.length==0){
                        $('#group_auto_member_id').val('');
                        swal("Warning!", "Please open saving account (SSB) then register for loan", "warning");
                        $('.'+attVal+'-member-detail').html('');
                        $('#'+attVal+'_member_id').val('');
                        $('#'+attVal+'_occupation_name').val('');
                        $('#'+attVal+'_occupation').val('');
                        $('.'+attVal+'-occupation-name').val('');
                        $('.'+attVal+'-occupation').val('');
                        return false;
                    }else{
                        $('#applicant_designation').val(response.carderName);
                        if(response.member.member_id_proofs){
                            $("#applicant_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.first_id_no+'') ;
                            $("#applicant_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.second_id_no+'') ;
                        }
                    }/*else if(attVal=='group-loan' && jQuery.inArray(memberid, guarantor) == -1){
                        $('#group_auto_member_id').val('');
                        swal("Warning!", "Applicant should be group member!", "warning");
                        $('.'+attVal+'-member-detail').html('');
                        $('#'+attVal+'_member_id').val('');
                        $('#'+attVal+'_occupation_name').val('');
                        $('#'+attVal+'_occupation').val('');
                        $('.'+attVal+'-occupation-name').val('');
                        $('.'+attVal+'-occupation').val('');
                        return false;
                    }*/

                    if(attVal=='guarantor'){
                        //$('.guarantor-name-section').hide();
                        $('#guarantor_occupation_id').html('');
                        $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                        $('#guarantor_occupation_id').append('<option selected value="'+response.occupation_id+'">'+response.occupation+'</option>');
                        $('#guarantor_occupation_id').prop('disabled', true);
                        //$('.guarantor-member-detail-box').hide();

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
                        //$('.guarantor-name-section').hide();

                        var gfirstName = response.member.first_name ? response.member.first_name : '';
                        var glastName = response.member.last_name ? response.member.last_name : '';

                        $('#'+attVal+'_name').val(gfirstName+' '+glastName);
                        $('#'+attVal+'_father_name').val(response.member.father_husband);
                        $('#'+attVal+'_dob').val(moment(response.member.dob).format('DD/MM/YYYY'));
                        $("#guarantor_marital_status option[value="+response.member.marital_status+"]").attr('selected', 'selected');
                        $('#local_address').val(response.member.address);
                        $('#'+attVal+'_mobile_number').val(response.member.mobile_no);
                    }
                    $('.'+attVal+'-member-detail').html(response.view);
                    $('#'+attVal+'_member_id').val(response.id);
                    $('#'+attVal+'_occupation_name').val(response.occupation);
                    $('#'+attVal+'_occupation').val(response.occupation_id);
                    $('.'+attVal+'-occupation-name').val(response.occupation);
                    $('.'+attVal+'-occupation').val(response.occupation_id);
                    $('#'+attVal+'_designation').val(response.carderName);
                    if(response.member.member_id_proofs){
                        $("#"+attVal+"_id_proof option[value=" + response.member.member_id_proofs.first_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.first_id_no+'') ;
                        $("#"+attVal+"_address_id_proof option[value=" + response.member.member_id_proofs.second_id_type_id +"]").attr("data-proof-val",''+response.member.member_id_proofs.second_id_no+'') ;
                    }

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
                                                var list_fieldHTML = '<tr><td class="plan-name">'+response.planName+'<input type="hidden" name="investmentplanloanid['+key+']" value="'+value.id+'" class="form-control"></td><td class="account-id">'+value.account_number+'</td><td class="open-date">'+newDate+'</td><td class="due-date">'+duenewDate+'</td><td class="deposite-amount">'+value.current_balance+'<input type="hidden" name="hidden_deposite_amount"  class="hidden_deposite_amount-'+key+' form-control" value="'+value.current_balance+'"></td><td class="plan-months">'+months+'</td><td class="loan-amount-input"><input data-input="'+key+'" type="text" name="ipl_amount['+key+']" class="ipl_amount ipl_amount-'+key+' form-control" style="width: 104px"></td></tr>';
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
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    $('#group_auto_member_id').val('');
                    swal("Warning!", response.msg, "warning");
                    return false;
                }
               /* else if(response.msg_type=="warning")
                {
                    $('#'+attVal+'_id').val('');
                    $('.'+attVal+'-member-detail').html('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false;
                }*/
                else
                {

                    $('.'+attVal+'-member-detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found</strong> </div>');
                    $('#'+attVal+'_id').val('');
                    $('#'+attVal+'_member_id').val('');
                    $('#'+attVal+'_occupation_name').val('');
                    $('#'+attVal+'_occupation').val('');
                    $('.'+attVal+'-occupation-name').val('');
                    $('.'+attVal+'-occupation').val('');
                    $('#'+attVal+'_designation').val('');
                    $("#"+attVal+"_id_proof").val('') ;
                    $("#"+attVal+"_address_id_proof").val('') ;
                    $("#"+attVal+"_id_number").val('') ;
                    $("#"+attVal+"_address_id_number").val('') ;
                    if(attVal=='guarantor'){
                    $('#guarantor_occupation_id').html('');
                    $('#guarantor_occupation_id').append('<option value="">Select Type</option><option value="1">Government Employee</option><option value="2">Private Employee</option><option value="3">Self Employees</option><option value="4">Other</option>');
                    $('#guarantor_occupation_id').prop('disabled', false);
                    $('.guarantor-name-section').show();
                    $('.guarantor-member-detail-box').show();
                    $('.guarantor-name-section').show();
                    $('#'+attVal+'_name').val('');
                    $('#'+attVal+'_father_name').val('');
                    $('#'+attVal+'_dob').val('');
                    $("#guarantor_marital_status").val('');
                    $('#local_address').val('');
                    $('#'+attVal+'_mobile_number').val('');
                    }
                    $('.loan-against-investment-plan').hide();
                    $('.investment-plan-input-number').html('');
                }
            }
        });
    });

    // Get registered member by id
    $(document).on('change','#group_leader_member_id',function(){
        var memberid = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('loan.groupmember') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type=="success"){

                    if(response.member.saving_account.length==0){
                            $(this).val('');
                            swal("Warning!", "You have not any ssb account", "warning");
                            $('#group_leader_member_id').val('');
                            $('.group-member-detail').hide();
                            $('.group-member-detail-not-found').hide();
                            $('#group_leader_m_id').val('');
                            $('#group_lm_name').val('');
                            return false;
                    }

                    $('.group-member-detail').show();
                    $('.group-member-detail-not-found').hide();
                    $('#group_leader_m_id').val(response.member.id);

                    var firstName = response.member.first_name ? response.member.first_name : '';
                    var lastName = response.member.last_name ? response.member.last_name : '';
                    var name = firstName+' '+lastName;
                    $('#group_lm_name').val(name);
                }else{
                    $('.group-member-detail').hide();
                    $('.group-member-detail-not-found').show();
                    $('#group_leader_m_id').val('');
                }
            }
        });
    });

    // Datatables
     loantable = $('#loan-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('loan.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
              {data: 'branch', name: 'branch'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            {data: 'account_number', name: 'account_number'},
            {data: 'member_name', name: 'member_name'},
            {data: 'member_id', name: 'member_id'},
            {data: 'total_deposit', name: 'total_deposit'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'tenure', name: 'tenure'},
            {data: 'emi_amount', name: 'emi_amount'},
            {data: 'transfer_amount', name: 'transfer_amount'},
            {data: 'loan_amount', name: 'loan_amount'},
            {data: 'file_charges', name: 'file_charges'},
            {data: 'insurance_charge', name: 'insurance_charge'},
            {data: 'file_charges_payment_mode', name: 'file_charges_payment_mode'},
            {data: 'outstanding_amount', name: 'outstanding_amount'},
            {data: 'last_recovery_date', name: 'last_recovery_date'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'bank_name', name: 'bank_name'},
            {data: 'bank_account_number', name: 'bank_account_number'},
            {data: 'ifsc_code', name: 'ifsc_code'},
            {data: 'total_payment', name: 'total_payment'},

            {data: 'approved_date', name: 'approved_date'},
            {data: 'sanction_date', name: 'sanction_date'},
            {data: 'application_date', name: 'application_date'},
            {data: 'collectorcode', name: 'collectorcode'},
            {data: 'collectorname', name: 'collectorname'},
            {data: 'reason', name: 'reason'},
            {data: 'status', name: 'status',
                "render":function(data, type, row){
                    if ( row.status == 0 ) {
                        return 'Pending';
                    } else if(row.status == 1) {
                        return 'Approved';
                    }else if(row.status == 2) {
                        return '<a href="javascript:void(0);"  data-toggle="modal" data-target="#rejection-view" data-rejection="'+row.rejection_description+'" class="view-rejection"><i class="icon-eye-blocked2  mr-2"></i>Rejected</a>';
                    }else if(row.status == 3) {
                        return 'Clear';
                    }else if(row.status == 4) {
                        return 'Due';
                    }

                }
            },

        ]
    });
    $(loantable.table().container()).removeClass( 'form-inline' );

     grouploantable = $('#group-loan-listing').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('loan.group.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchGroupLoanForm=$('form#grouploanfilter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
              {data: 'branch', name: 'branch'},
            // {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            {data: 'account_number', name: 'account_number'},
			{data: 'group_loan_common_id', name: 'group_loan_common_id'},
            {data: 'member_name', name: 'member_name'},
            {data: 'member_id', name: 'member_id'},
            {data: 'total_deposit', name: 'total_deposit'},
            {data: 'plan_name', name: 'plan_name'},
            {data: 'tenure', name: 'tenure'},
            {data: 'emi_amount', name: 'emi_amount'},
            {data: 'loan_amount', name: 'loan_amount'},
            {data: 'amount', name: 'amount'},
            {data: 'file_charges', name: 'file_charges'},
			{data: 'insurance_charge', name: 'insurance_charge'},
            {data: 'file_charges_payment_mode', name: 'file_charges_payment_mode'},
            {data: 'outstanding_amount', name: 'outstanding_amount'},
            {data: 'last_recovery_date', name: 'last_recovery_date'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'bank_name', name: 'bank_name'},

            {data: 'bank_account_number', name: 'bank_account_number'},

            {data: 'ifsc_code', name: 'ifsc_code'},
            {data: 'total_payment', name: 'total_payment'},
            {data: 'approve_date', name: 'approve_date'},
            {data: 'sanction_date', name: 'sanction_date'},
            {data: 'application_date', name: 'application_date'},
            {data: 'collector_code', name: 'collector_code'},
            {data: 'collector_name', name: 'collector_name'},
            {data: 'reason', name: 'reason'},
            {data: 'status', name: 'status'},

        ]
    });
    $(grouploantable.table().container()).removeClass( 'form-inline' );

    var loanId = $('#loanId').val();
    var loanType = $('#loanType').val();
    var loanEmiTable = $('#listtansaction').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.loan.emi_list') !!}",
            "type": "POST",
            "data":{'loanId':loanId,'loanType':loanType},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'transaction_id', name: 'transaction_id'},
            {data: 'date', name: 'date'},
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'description', name: 'descsription'},
            {data: 'penalty', name: 'penalty'},
            {data: 'deposite', name: 'deposite'},
            {data: 'jv_amount', name: 'jv_amount'},
            {data: 'igst_charge', name: 'igst_charge'},
            {data: 'cgst_charge', name: 'cgst_charge'},
            {data: 'sgst_charge', name: 'sgst_charge'},
            {data: 'balance', name: 'balance'},

            // {data: 'principal_amount', name: 'principal_amount'},
            // {data: 'opening_balance', name: 'opening_balance'},
        ]
    });
    $(loanEmiTable.table().container()).removeClass( 'form-inline' );

    $('.date_of_birth').datepicker( {
        format: "dd/mm/yyyy",
        orientation: "top",
        autoclose: true,
        endDate: "today",
        maxDate: today
    });




//     $(".application_date").hover(function(){
//         var date = $('#created_at').val();


//       $('.application_date').datepicker({
//           format:"dd/mm/yyyy",
//             endHighlight: true,
//             autoclose:true,
//             orientation:"bottom",
//             endDate:date,
//             startDate: date,


//           })
//    })

   $(".application_date").hover(function(){
            const EndDate = $('#created_at').val();
            $('.application_date').datepicker({
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            endDate: EndDate,
            startDate: EndDate,
        });
    })

    $('.from_date,.to_date').datepicker( {

        format: "dd/mm/yyyy",

        orientation: "top",

        autoclose: true,

        endDate: "today",

        maxDate: today

    });
	/*
     $('.export').on('click',function(){

        var extension = $(this).attr('data-extension');

        $('#loan_recovery_export').val(extension);

        $('form#filter').attr('action',"{!! route('branch.loan_list_export') !!}");

        $('form#filter').submit();

        return true;

    });
    */
	$('.export').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#loan_recovery_export').val(extension);
		var startdate = $(".from_date").val();
		var enddate = $(".to_date").val();
			if(extension == 0)
		{

			if( startdate =='')
			{
				swal("Error!", "Please select start date, you can export last three months data!", "error");
			return false;
			}

			if( enddate =='')
			{
				swal("Error!", "Please select end date, you can export last three months data!", "error");
				return false;
			}


        var formData = jQuery('#filter').serializeObject();
        var chunkAndLimit = 50;

			$(".spiners").css("display","block");
			$(".loaders").text("0%");

        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#loan_recovery_export').val(extension);

			$('form#filter').attr('action',"{!! route('branch.loan_kist_export') !!}");

			$('form#filter').submit();
		}
	});

	// function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('branch.loan_kist_export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
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

	// A function to turn all form data into a jquery object
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
	/*
     $('.export-group-loan').on('click',function(){

        var extension = $(this).attr('data-extension');

        $('#group_loan_recovery_export').val(extension);

        $('form#grouploanfilter').attr('action',"{!! route('branch.group_loan_list_export') !!}");

        $('form#grouploanfilter').submit();

        return true;

    });
*/
$('.export-group-loan').on('click',function(e){
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#group_loan_recovery_export').val(extension);
		var startdate = $(".from_date").val();
			var enddate = $(".to_date").val();

				if(extension == 0)
		{
				if( startdate =='')
				{
					swal("Error!", "Please select start date, you can export last three months data!", "error");
					return false;
				}

				if( enddate =='')
				{
					swal("Error!", "Please select end date, you can export last three months data!", "error");
					return false;
				}

        var formData = jQuery('#grouploanfilter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExports(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#group_loan_recovery_export').val(extension);

			$('form#grouploanfilter').attr('action',"{!! route('branch.group_loan_list_export') !!}");

			$('form#grouploanfilter').submit();
		}
	});
		  function doChunkedExports(start,limit,formData,chunkSize){
						formData['start']  = start;
						formData['limit']  = limit;
						jQuery.ajax({
							type : "post",
							dataType : "json",
							url :  "{!! route('branch.group_loan_list_export') !!}",
							   headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
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
    $(document).on('change','.application_date',function(){
       var aDate = $(this).val();
       $('#created_date').val(aDate);
       var associateCode = $('#loan_associate_code').val();
       if(associateCode != ''){
        $('#loan_associate_code').trigger('change');
       }
    });


    $('.applciant-deatils-box').hide();
    $('.coapplciant-deatils-box').hide();
    $('.guarantor-deatils-box').hide();
    $('.group-information').hide();
    $('.staff-loan-section').hide();
    $('.other-loan-section').hide();
    $('.bank-details-section').hide();
    $('.emi-mode-section').hide();
    $('.group-emi-mode-section').hide();
    $(document).on('change','#loan',function(){
        $('#file_charge').val('');
        $('#loan_emi').val('');
        $('.emi_option').val('');
        $('.emi_period').val('');
        $('#loan_amount').val('');
        $('#loan_purpose').val('');
        $('.loanId').val($(this).val());
        $("#register-plan")[0].reset();
        var loan = $('option:selected', this).attr('data-val');
        $('.applicant-member-detail').html('');
        $('.co-applicant-member-detail').html('');
        $('.guarantor-member-detail').html('');
        if(loan == 'personal-loan'){
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').show();
            $('.guarantor-deatils-box').show();
            $('.group-information').hide();
            $('.staff-loan-section').show();
            //$('.personal-loan-section').show();
            $('.other-loan-section').hide();
            $('.bank-details-section').show();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.loan-against-investment-plan').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            $('.group-emi-mode').hide();
            $('.staff-emi-mode').hide();
            $('.investmentloan-emi-mode').hide();
            $('.personal-emi-mode').show();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            $('.cheque-box').show();
            $('.applicant-box').show();
            $('#amount').attr('readonly',false);
        }else if(loan == 'group-loan'){
            $('.group-information').show();
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').show();
            $('.guarantor-deatils-box').show();
            $('.staff-loan-section').hide();
            $('.personal-loan-section').hide();
            $('.other-loan-section').hide();
            $('.bank-details-section').hide();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.loan-against-investment-plan').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            $('.staff-emi-mode').hide();
            $('.investmentloan-emi-mode').hide();
            $('.personal-emi-mode').hide();
            $('.group-emi-mode').show();
            $('.cheque-box').show();
            $('.applicant-box').hide();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            $('#amount').attr('readonly',false);
        }else if(loan == 'staff-loan'){
            $('.group-information').hide();
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').hide();
            $('.guarantor-deatils-box').show();
            $('.staff-loan-section').show();
            $('.personal-loan-section').hide();
            $('.other-loan-section').hide();
            $('.bank-details-section').show();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.loan-against-investment-plan').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').show();
            $('.personal-emi-mode').hide();
            $('.group-emi-mode').hide();
            $('.investmentloan-emi-mode').hide();
            $('.staff-emi-mode').show();
            $('.cheque-box').show();
            $('.applicant-box').show();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            //$('#amount').attr('readonly',true);
        }else if(loan == 'loan-against-investment-plan'){
            $('.applciant-deatils-box').show();
            $('.coapplciant-deatils-box').hide();
            $('.guarantor-deatils-box').hide();
            $('.group-information').hide();
            $('.staff-loan-section').show();
            $('.personal-loan-section').hide();
            $('.other-loan-section').hide();
            $('.bank-details-section').show();
            $('.loan-against-investment-plan').hide();
            $('.emi-mode-section').hide();
            $('.group-emi-mode-section').hide();
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            $('.personal-emi-mode').hide();
            $('.group-emi-mode').hide();
            $('.staff-emi-mode').hide();
            $('.investmentloan-emi-mode').show();
            $('.cheque-box').hide();
            $('.applicant-box').show();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
            $('#amount').attr('readonly',true);
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
            $('.group-loan-member-table').hide();
            $('.salary-section').hide();
            //$('.loan-emi-amount').html('Loan EMI: ');
            $('.loan-emi-amount').html('');
        }
        $('#loan_type').val(loan);
    });

    $('.year').datepicker( {
        format: "yyyy",
        orientation: "top",
        autoclose: true,
        endDate: "today",
        maxDate: today
    });
     $(document).on('change','#customer_cheque',function(){
        var cheque_id = $('option:selected', this).val();
        var deposite_amount = $('#deposite_amount').val();

          $.ajax({
              type: "POST",
              url: "{!! route('branch.approve_cheque_detail') !!}",
              dataType: 'JSON',
              data: {'cheque_id':cheque_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {
              console.log(deposite_amount ,parseFloat(response.amount).toFixed(0)) ;
                if(deposite_amount !=  parseFloat(response.amount).toFixed(0))
                {
                    swal('Error!','Cheque Amount Should be Equal to Deposite Amount','error');
                }
                else{
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
$('#bank_transfer_mode').on('change',function(){

        //var branch =$('option:selected', this).val();
        var paymentMode = $('option:selected', '#bank_transfer_mode').val();


        if(paymentMode == '0')
        {
            $.ajax({
                type:'POST',
                url:"{!! route('branch.approve_cheque_branchwise') !!}",
                dataType:'JSON',

                headers:{
                    'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
                },
                success:function(response)
                {
                    $('#customer_cheque').find('option').remove();

                    $('#customer_cheque').append('<option value="">--- Select Cheque ---</option>');

                    if(response.length > 0)
                    {
                         // $('.cheque-transaction').show();

                        var options = $.each(response,function(key,value){
                             $('#customer_cheque').
                                append('<option value="'+value.id+'" id="cheque_no">' +value.cheque_no+'('+value.amount+')'+ '</option>');

                        })

                        // $('#customer_cheque').append(options);

                    }
                    else{

                        var msg = 'No Cheque';
                          $('#cheque-detail-show').hide();
                        var options =
                             $('#customer_cheque').
                                append('<option value="">' + msg +'</option>');

                        swal("Error!", "No Cheque Found!", "error")


                    }
                }

            })
        }


    })
    $(document).on('change','#applicant_id_proof,#applicant_address_id_proof,#co-applicant_id_proof,#co-applicant_address_id_proof,#guarantor_id_proof,#guarantor_address_id_proof',function(){
        var sectionval = $('option:selected', this).attr('data-val');
        var proofValue = $('option:selected', this).attr('data-proof-val');
        var loanType = $( "#loan option:selected" ).val();
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

        if(sectionval == 'applicant' && loanType == 3){
            if($('#group_auto_member_id').val() != ''){
                $('#applicant_id_number').val(proofValue);
            }else{
                $('#applicant_id_number').val('');
            }
        }

        if(sectionval == 'applicant_address' && loanType == 3){
            if($('#group_auto_member_id').val() != ''){
                $('#applicant_address_id_number').val(proofValue);
            }else{
                $('#applicant_address_id_number').val('');
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

    $(document).on('click','#co_applicant_checkbox',function(){
        $('#co_applicant_checkbox_val').val(1);
        var loanType = $( "#loan option:selected" ).val();
        if(loanType == 3){
            var associateId = $('#group_associate_id').val();
        }else{
            var associateId = $('#acc_auto_member_id').val();
        }
        if ($("input[type=checkbox]").is(":checked")) {
            $( "#co-applicant_auto_member_id" ).val(associateId);
            $( "#co-applicant_auto_member_id" ).trigger( "change" );
        } else {
            $( "#co-applicant_auto_member_id" ).val('');
            $( "#co-applicant_auto_member_id" ).trigger( "change" );
        }
    });

    $(document).on('click','.group-leader-m-id',function(){
        var glAutoId = $(this).val();
        var glID = $(this).attr('data-group');
        $('#group_leader_m_id').val(glAutoId);
        $('#group_leader_member_id').val(glID);
    });

    $(document).on('change','#applicant_income,#co-applicant_income,#guarantor_income',function(){
        var value = $(this).val();
        var section = $('option:selected', this).attr('data-val');
        if(value==2){
            $('.'+section+'-salary-remark').show();
        }else{
            $('.'+section+'-salary-remark').hide();
        }
    });

    $(document).on('change','#guarantor_occupation_id',function(){
        var value = $('option:selected', this).text();
        if(value=='Other'){
            $('.occupation-other-remark').show();
            $('.occupation-fields').hide();
        }else{
            $('.occupation-other-remark').hide();
            $('.occupation-fields').show();
        }
    });

    $(document).on('change','#amount,#emi_mode_option,#salary',function(){
        var value = $('#amount').val();
        var loantype = $( "#loan option:selected" ).val();
        var moPayment = $( "#emi_mode_option option:selected" ).attr('data-val');
        var moPaymentValue = $( "#emi_mode_option option:selected" ).val();
        if(loantype == 1 || loantype == 2){
            if((value < 10000 || value > 200000) && value!='')  {
                $('#loan-amount-error').show();
                $('#loan-amount-error').html('Please enter amount between 10000 to 200000');
                $('#loan_emi').val('');
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
                return false;
              }else{

                if(value >= 10000 && value <= 25000)  {
                    var fileCharge = 500;
                }else if(value > 25000 && value <= 50000){
                    var fileCharge = 1000;
                }else if(value > 50000){
                    var fileCharge = 2*value/100;
                }else{
                    var fileCharge = '';
                }
                console.log(moPaymentValue,moPayment);
                if(moPayment == 'months' && moPaymentValue == 12){
                    var loanEmi = showpay(value,moPaymentValue,39.06,1200);
                    var rate = 39.06;
                }else if(moPayment == 'days' && moPaymentValue == 100){
                    var loanEmi = showpay(value,moPaymentValue,70.20,36500);
                    var rate = 70.20;
                }else if(moPayment == 'days' && moPaymentValue == 200){
                    var loanEmi = showpay(value,moPaymentValue,68.40,36500);
                    var rate = 68.40;
                }else if(moPayment == 'weeks' && moPaymentValue == 24){
                    var loanEmi = showpay(value,moPaymentValue,60.55,5200);
                    var rate = 60.55;
                }else if(moPayment == 'weeks' && moPaymentValue == 26){
                    var loanEmi = showpay(value,moPaymentValue,44.857,5200);
                    var rate = 44.857;
                }else if(moPayment == 'weeks' && moPaymentValue == 52){
                    var loanEmi = showpay(value,moPaymentValue,46.69911,5200);
                    var rate = 46.69911;
                }else if(moPayment == 'months' && moPaymentValue == 10){
                   
                    var loanEmi = showpay(value,moPaymentValue,20,1200);
                    var rate = 20;
                }else{
                    var loanEmi = '';
                    var rate = '';
                }
                $('#file_charge').val(fileCharge);
                $('#loan_emi').val(loanEmi);
               
                if(moPayment == 'weeks' && moPaymentValue == 26){
                    $('.loan-emi-amount').html('');
               }else if(moPayment == 'weeks' && moPaymentValue == 52){
                    $('.loan-emi-amount').html('');
               }else if(loanEmi){
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
               }else{
                    $('.loan-emi-amount').html('');
               }

                if(loanEmi){
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
                }else{
                    $('.loan-emi-amount').html('');
                }
                $('#loan-amount-error').html('');
                $('#interest-rate').val(rate);
                $('#loan_amount').val(value);
                return true;
              }
        }
        /*else if(loantype == 2){

                if(value <= 10000)  {
                    var fileCharge = 0;
                }else if(value >= 10000 && value <= 25000){
                    var fileCharge = 500;
                }else if(value > 25000){
                    var fileCharge = 2*value/100;
                }else{
                    var fileCharge = '';
                }
                if(moPayment == 'months'){
                    var loanEmi = showpay(value,moPaymentValue,20.00,1200);
                    var rate = 20.00;
                }else{
                    var loanEmi = '';
                    var rate = '';
                }
                $('#file_charge').val(fileCharge);
                $('#loan_emi').val(loanEmi);
                if(loanEmi){
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
                }else{
                    $('.loan-emi-amount').html('');
                }
                $('#loan-amount-error').html('');
                $('#interest-rate').val(rate);
                $('#loan_amount').val(value);
                return true;
        }*/
        else{
            $('#loan-amount-error').html('');
        }
    });
    loanTransactionTable = $('#loan_transaction_table').DataTable({

processing: true,

serverSide: true,

pageLength: 100,

"fnRowCallback" : function(nRow, aData, iDisplayIndex) {

    var oSettings = this.fnSettings ();

    $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);

    return nRow;

},

ajax: {

    "url": "{!! route('branch.loan.transactionlist') !!}",

    "type": "POST",

    "data":function(d) {d.searchform=$('form#transaction-loan-filter').serializeArray()},

    headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

    },

},

columns: [

        {data: 'DT_RowIndex', name: 'DT_RowIndex'},
        {data: 'created_at', name: 'created_at'},
        {data: 'branch', name: 'branch'},
        {data: 'branch_code', name: 'branch_code'},
        {data: 'sector', name: 'sector'},
        {data: 'region', name: 'region'},
        {data: 'zone', name: 'zone'},
        {data: 'member_id', name: 'member_id'},
        {data: 'account_number', name: 'account_number'},
        {data: 'member_name', name: 'member_name'},
        {data: 'plan_name', name: 'plan_name'},
        {data: 'tenure', name: 'tenure'},
        {data: 'emi_amount', name: 'emi_amount'},
        {data: 'loan_sub_type', name: 'loan_sub_type'},
        {data: 'associate_code', name: 'associate_code'},
        {data: 'associate_name', name: 'associate_name'},
        {data: 'payment_mode', name: 'payment_mode'},

    // {data: 'action', name: 'action',orderable: false, searchable: false},

]

});

$(loanTransactionTable.table().container()).removeClass( 'form-inline' );
    $(document).on('change','#emi_mode_option',function(){
        var emiOption = $('option:selected', this).attr('data-val');
        var emiPeriod = $('option:selected', this).val();
        if(emiOption == 'months'){
            $('.emi_option').val(1);
        }else if(emiOption == 'weeks'){
            $('.emi_option').val(2);
        }else if(emiOption == 'days'){
            $('.emi_option').val(3);
        }
        $('.emi_period').val(emiPeriod);
    });

    $(document).on('change','#salary',function(){
        var value = $(this).val();
        var loanAmount = value*25/100;
        $('#amount').val(loanAmount);
        $('.c-amount').val(loanAmount);
        $('#loan_amount').val(loanAmount);
        $('#amount').prop('readonly', true);
    });

    $(document).on('change','#purpose',function(){
        var value = $(this).val();
        $('#loan_purpose').val(value);
    });

    // Get registered member by id
    var x = 0; //Initial field counter
    var list_maxField = 10;
    $("#more-doc-button").click(function() {
        var hiddenDoc = $('.hidden_more_doc').val();
        if(hiddenDoc == 1){
            $('.more-doc').show();
            var countVal = $(this).attr('data-val');
            var increaseVal = countVal+1;

            if(x < list_maxField){
                x++; //Increment field counter
                var list_fieldHTML = '<div class="form-group row flex-grow-1"><label class="col-form-label col-lg-2">Doc Title</label><div class="col-lg-3"><input type="text" name="guarantor_more_doc_title['+x+']" id="guarantor_more_doc_title" class="form-control"></div><label class="col-form-label col-lg-2">Upload File</label><div class="col-lg-4"><input type="file" name="guarantor_more_upload_file['+x+']" id="guarantor_more_upload_file" class="form-control"></div><span><a href="javascript:void(0);" class="remove-doc-button" >Remove</a></span></div>'; //New input field html
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

    $(document).on('change','#number_of_member',function(){
        var mNumber = $(this).val();
        $('.m-input-number').html('');
        $('#group_leader_m_id').val('');
        $('#group_leader_member_id').val('');
        if(mNumber > 0){
            $('.group-loan-member-table').show();
        }else{
            $('.group-loan-member-table').hide();
        }
        var x = 1;
        for (var x = 1; x <= mNumber; x++) {
            var list_fieldHTML = '<tr><td><input data-input="'+x+'" type="text" name="m_id['+x+']" class="g-loan-member-id g-loan-member-id-'+x+' form-control" style="width: 104px"><input data-input="'+x+'" type="hidden" name="m_id['+x+']" class="g-loan-member-id g-loan-hidden-m-id-'+x+'"><input data-input="'+x+'" type="hidden" name="ml_emi['+x+']" class="g-loan-hidden-emi g-loan-hidden-emi-'+x+'"><input data-input="'+x+'" type="hidden" name="ml_file_charge['+x+']" class="g-loan-hidden-file-charge g-loan-hidden-file-charge-'+x+'"><input data-input="'+x+'" type="hidden" name="ml_interest_rate['+x+']" class="g-loan-hidden-interest-rate g-loan-hidden-interest-rate-'+x+'"></td><td><input data-input="'+x+'" type="text" name="m_name['+x+']" class="g-loan-member-name g-loan-member-name-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="f_name['+x+']" class="g-loan-member-fname g-loan-member-fname-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="m_amount['+x+']" class="g-loan-member-amount g-loan-member-amount-'+x+' form-control" style="width: 104px"></td><td><input data-input="'+x+'" type="text" readonly name="m_total_deposit_amount['+x+']" class="g-loan-member-total-deposit-amount g-loan-member-total-deposit-amount-'+x+' form-control" style="width: 104px"></td><td><a target="blank" data-input="'+x+'" class="g-loan-member-s g-loan-member-s-'+x+'" href="javascript:void(0);" style="width: 104px"></a></td><td><input data-input="'+x+'" type="text" name="m_bn['+x+']" class="g-loan-member-bn g-loan-member-bn-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="m_bac['+x+']" class="g-loan-member-bac g-loan-member-bac-'+x+' form-control" style="width: 104px" readonly=""></td><td><input data-input="'+x+'" type="text" name="m_bi['+x+']" class="g-loan-member-bi g-loan-member-bi-'+x+' form-control" style="width: 104px" readonly=""></td><td><div class="custom-control custom-radio mb-3 "><input type="radio" id="group-leader-'+x+'" class="custom-control-input group-leader-m-id" name="g_l_m_id" data-group="" value="0"><label class="custom-control-label" for="group-leader-'+x+'">Yes</label></div></td></tr>';
            $('.m-input-number').append(list_fieldHTML); //Add field html
        }
        $('.group-loan-amount').val('');
        $('#loan_amount').val('');
    });

    $('.m-input-number').on('change','.g-loan-member-id',function(){
        var mId = $(this).val();
        var dInput = $(this).attr('data-input');
        guarantor.push(mId);
        $.ajax({
            type: "POST",
            url: "{!! route('loan.groupmember') !!}",
            dataType: 'JSON',
            data: {'memberid':mId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type=="success"){
                    //$('.g-loan-member-id-'+dInput+'').val(response.member.id);
                    if(response.member.saving_account.length==0){
                            $(this).val('');
                            swal("Warning!", "Please open saving account (SSB) then register for loan", "warning");
                            $('.g-loan-member-id-'+dInput+'').val('')
                            $('.g-loan-member-name-'+dInput+'').val('');
                            $('.g-loan-member-fname-'+dInput+'').val('');
                            $('.g-loan-member-bn-'+dInput+'').val('');
                            $('.g-loan-member-s-'+dInput+'').html('');
                            $('.g-loan-member-s-'+dInput+'').attr('href','');
                            $('.g-loan-member-bac-'+dInput+'').val('');
                            $('.g-loan-member-bi-'+dInput+'').val('');
                            $('.g-loan-member-id-'+dInput+'').val('');
                            $('.g-loan-hidden-m-id-'+dInput+'').val('');
                            $('#group-leader-'+dInput+'').val('');
                            $('#group-leader-'+dInput+'').attr('data-group','');
                            return false;
                    }
                    var firstName = response.member.first_name ? response.member.first_name : '';
                    var lastName = response.member.last_name ? response.member.last_name : '';
                    var ass_name = firstName+' '+lastName;
                    $('.g-loan-member-name-'+dInput+'').val(ass_name);
                    $('.g-loan-member-fname-'+dInput+'').val(response.member.father_husband);
                    $('.g-loan-member-s-'+dInput+'').html(response.member.signature);
                    $('.g-loan-member-s-'+dInput+'').attr('href','asset/profile/member_signature/'+response.member.signature);

                      if(response.member.member_bank_details.length > 0 && response.member.saving_account.length !=0){
                        $('.g-loan-member-bn-'+dInput+'').val(response.member.member_bank_details[0].bank_name);

                        $('.g-loan-member-bac-'+dInput+'').val(response.member.saving_account[0]['account_no']);
                        $('.g-loan-member-bi-'+dInput+'').val(response.member.member_bank_details[0].ifsc_code);
                    }else{
                        $('.g-loan-member-bn-'+dInput+'').val('');
                   $('.g-loan-member-bac-'+dInput+'').val('');
                        $('.g-loan-member-bi-'+dInput+'').val('');
                    }
                    $('.g-loan-member-total-deposit-amount-'+dInput+'').val(response.totalDeposit)
                    $('.g-loan-hidden-m-id-'+dInput+'').val(response.member.id);
                    $('#group-leader-'+dInput+'').val(response.member.id);
                    $('#group-leader-'+dInput+'').attr('data-group',mId);

                }else if(response.msg_type=="warning"){
                    var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
                    var moPayment = $( ".group-information option:selected" ).val();
                    $('.g-loan-member-name-'+dInput+'').val('');
                    $('.g-loan-member-fname-'+dInput+'').val('');
                    $('.g-loan-member-bn-'+dInput+'').val('');
                    $('.g-loan-member-bac-'+dInput+'').val('');
                    $('.g-loan-member-s-'+dInput+'').html('');
                    $('.g-loan-member-s-'+dInput+'').attr('href','');
                    $('.g-loan-member-bi-'+dInput+'').val('');
                    $('.g-loan-member-id-'+dInput+'').val('');
                    $('.g-loan-hidden-m-id-'+dInput+'').val('');
                    $('.g-loan-member-amount-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').attr('data-group','');
                    swal("Warning!", response.msg, "warning");

                    var sum = 0;
                    $(".g-loan-member-amount").each(function(){
                        sum += +$(this).val();
                    });
                    $('.group-loan-amount').val(sum);
                    $('#loan_amount').val(sum);
                    $('.g-loan-hidden-emi-'+dInput+'').val('');
                    $('.g-loan-hidden-file-charge-'+dInput+'').val('');
                    /*if(moPaymentType == 'days' && moPayment == 100){
                        var loanEmi = showpay(value,moPayment,70.20,36500);
                    }else if(moPaymentType == 'days' && moPayment == 200){
                        var loanEmi = showpay(value,moPayment,68.40,36500);
                    }else if(moPaymentType == 'weeks' && moPayment == 12){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }else if(moPaymentType == 'weeks' && moPayment == 24){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }
                    $('#loan_emi').val(loanEmi);
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);

                    if(sum >= 10000 && sum <= 25000)  {
                        $('#file_charge').val(500);
                    }else if(sum > 25000 && sum <= 50000){
                        $('#file_charge').val(1000);
                    }else if(sum > 50000){
                        var fileCharge = 2*sum/100;
                        $('#file_charge').val(fileCharge);
                    }*/

                    /*swal("Warning!", "Guarantor can not be applicant!!", "warning");
                    return false; */
                }else{
                    var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
                    var moPayment = $( ".group-information option:selected" ).val();
                    //$('.g-loan-member-id-'+dInput+'').val('');
                    $('.g-loan-member-name-'+dInput+'').val('');
                    $('.g-loan-member-fname-'+dInput+'').val('');
                    $('.g-loan-member-bn-'+dInput+'').val('');
                    $('.g-loan-member-bac-'+dInput+'').val('');
                    $('.g-loan-member-s-'+dInput+'').html('');
                    $('.g-loan-member-s-'+dInput+'').attr('href','');
                    $('.g-loan-member-bi-'+dInput+'').val('');
                    $('.g-loan-member-id-'+dInput+'').val('');
                    $('.g-loan-hidden-m-id-'+dInput+'').val('');
                    $('.g-loan-member-amount-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').val('');
                    $('#group-leader-'+dInput+'').attr('data-group','');
                    var sum = 0;
                    $(".g-loan-member-amount").each(function(){
                        sum += +$(this).val();
                    });
                    $('.group-loan-amount').val(sum);
                    $('#loan_amount').val(sum);
                    $('.g-loan-hidden-emi-'+dInput+'').val('');
                    $('.g-loan-hidden-file-charge-'+dInput+'').val('');
                    /*if(moPaymentType == 'days' && moPayment == 100){
                        var loanEmi = showpay(value,moPayment,70.20,36500);
                    }else if(moPaymentType == 'days' && moPayment == 200){
                        var loanEmi = showpay(value,moPayment,68.40,36500);
                    }else if(moPaymentType == 'weeks' && moPayment == 12){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }else if(moPaymentType == 'weeks' && moPayment == 24){
                        var loanEmi = showpay(value,moPayment,107.94,5200);
                    }
                    $('#loan_emi').val(loanEmi);
                    $('.loan-emi-amount').html('Loan EMI: '+loanEmi);

                    if(sum >= 10000 && sum <= 25000)  {
                        $('#file_charge').val(500);
                    }else if(sum > 25000 && sum <= 50000){
                        $('#file_charge').val(1000);
                    }else if(sum > 50000){
                        var fileCharge = 2*sum/100;
                        $('#file_charge').val(fileCharge);
                    }*/

                    swal("Error!", "Member ID does not exists!", "error");
                }
            }
        });

    });

    $('.m-input-number').on('change','.g-loan-member-amount',function(){
        var dInput = $(this).attr('data-input');
        var fAamount = $('.group-loan-amount').val();
        var mId = $('.g-loan-member-id-'+dInput+'').val();
        var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
        var moPayment = $( ".group-information option:selected" ).val();
        var value = $('.g-loan-member-amount-'+dInput+'').val();
        if(mId != ''){
            if($(this).val() > 9999){
                var sum = 0;
                $(".g-loan-member-amount").each(function(){
                    sum += +$(this).val();
                });
                if(sum <= 200000){
                    $('.group-loan-amount').val(sum);
                 }
                 //else{
                //    swal("Error!", "Total amount should be between 20,000 to 2,00,000", "error");
                //    $('.g-loan-member-amount-'+dInput+'').val('');
                //    var sum = 0;
                //     $(".g-loan-member-amount").each(function(){
                //         sum += +$(this).val();
                //     });
                //     $('#loan_amount').val(sum);
                //     $('.group-loan-amount').val(sum);
                //     $('#loan_emi').val('');
                //     //$('.loan-emi-amount').html('Loan EMI: ');
                //     $('.loan-emi-amount').html('');
                // }
            }else{
                $('.g-loan-member-amount-'+dInput+'').val('');
                var sum = 0;
                $(".g-loan-member-amount").each(function(){
                    sum += +$(this).val();
                });
                $('.group-loan-amount').val(sum);
                $('#loan_amount').val(sum);
                swal("Error!", "Enter amount greater than 10000", "error");
                $('#loan_emi').val('');
                //$('.loan-emi-amount').html('Loan EMI: ');
                $('.loan-emi-amount').html('');
            }

            if(moPaymentType == 'days' && moPayment == 100){
                var loanEmi = showpay(value,moPayment,70.20,36500);
                var rate = 70.20;
            }else if(moPaymentType == 'days' && moPayment == 200){
                var loanEmi = showpay(value,moPayment,68.40,36500);
                var rate = 68.40;
            }else if(moPaymentType == 'weeks' && moPayment == 12){
                var loanEmi = showpay(value,moPayment,107.94,5200);
                var rate = 107.94;
            }else if(moPaymentType == 'weeks' && moPayment == 24){
                var loanEmi = showpay(value,moPayment,60.55,5200);
                var rate = 60.55;
            }else if(moPaymentType == 'weeks' && moPayment == 26){
                var loanEmi = showpay(value,moPayment,44.857,5200);
                var rate = 44.857;
            }else if(moPaymentType == 'weeks' && moPayment == 52){
                var loanEmi = showpay(value,moPayment,46.69911,5200);
                var rate = 46.69911;
            }

            if(value >= 10000 && value <= 25000)  {
                var fileCharge = 500;
            }else if(value > 25000 && value <= 50000){
                var fileCharge = 1000;
            }else if(value > 50000){
                var fileCharge = 2*value/100;
            }

            $('.g-loan-hidden-file-charge-'+dInput+'').val(fileCharge);
            $('.g-loan-hidden-emi-'+dInput+'').val(loanEmi);
            $('.g-loan-hidden-interest-rate-'+dInput+'').val(rate);

            //$('.loan-emi-amount').html('Loan EMI: '+loanEmi);
        }
    });

    $(document).on('change','.group-information',function(){
        var moPaymentType = $( ".group-information option:selected" ).attr('data-val');
        var moPayment = $( ".group-information option:selected" ).val();
        var sum = 0;
        $(".g-loan-member-amount").each(function(){
            sum += +$(this).val();
        });
        $('.g-loan-member-amount').val();
        $('#amount').val();
        $('#loan_amount').val();
        /*if(moPaymentType == 'days' && moPayment == 100){
            var loanEmi = showpay(sum,moPayment,70.20,36500);
        }else if(moPaymentType == 'days' && moPayment == 200){
            var loanEmi = showpay(sum,moPayment,68.40,36500);
        }else if(moPaymentType == 'weeks' && moPayment == 12){
            var loanEmi = showpay(sum,moPayment,107.94,5200);
        }else if(moPaymentType == 'weeks' && moPayment == 24){
            var loanEmi = showpay(sum,moPayment,107.94,5200);
        }

        if(sum >= 10000 && sum <= 25000)  {
            var fileCharge = 500;
        }else if(sum > 25000 && sum <= 50000){
            var fileCharge = 1000;
        }else if(sum > 50000){
            var fileCharge = 2*sum/100;
        }

        $('#file_charge').val(fileCharge);
        $('#loan_emi').val(loanEmi);
        $('.loan-emi-amount').html('Loan EMI: '+loanEmi);*/
    });

    $('.investment-plan-input-number').on('change','.ipl_amount',function(){
        var dataval = $(this).attr('data-input');
        var dAmount = $('.hidden_deposite_amount-'+dataval+'').val();
        var apporveAmount = dAmount/2;
        var rAmount = $(this).val();
        // if(apporveAmount < rAmount){
        //     swal("Error!", "Wrong Amount!", "error");
        //     $(this).val('');
        // }
        var sum = 0;
        $(".ipl_amount").each(function(){
            sum += +$(this).val();
        });
        $('#amount').val(sum);
        $('.c-amount').val(sum);
        $('#loan_amount').val(sum);
        $('#amount').prop('readonly', true);
        //if(sum >= 10000){
            var loanEmi = showpay(sum,12,28.40,1200);
            var rate = 28.40;
            $('#loan_emi').val(loanEmi);

            if(loanEmi){
                $('.loan-emi-amount').html('Loan EMI: '+loanEmi);
            }else{
                $('.loan-emi-amount').html('');
            }
            $('#interest-rate').val(rate);
        //}
    });


    $(document).on('click','.submit-loan-form',function(){
        var loantype = $( "#loan option:selected" ).val();
        var aDate = $( "#created_date" ).val();
        var appDate = $( ".application_date" ).val();
        if(loantype == ''){
            swal("Error!", "Please select a plan  first!", "error");
            return false;
        }if(appDate == ''){
            swal("Error!", "Please select a date  first!", "error");
            return false;
        }else{
            return true;
        }
    });

    $(document).on('click','.view-rejection',function(){
        var corrections = $(this).attr('data-rejection');
        $('.loan-rejected-description').html('')
        $('.loan-rejected-description').html(corrections)
    });

    $('#loan_emi_payment_mode').on('change',function(){
        var paymentMode = $('option:selected', this).val();
        var paymentMode = $('option:selected', this).val();
        var date = $('.application_date').val();

        if(date == ''){
            var branch = $('#loan_emi_payment_mode').val('');
            swal("Warning!", "Please select a transfer date first!", "warning");
            return false;
        }

        if(paymentMode == 0 && paymentMode != ''){
            $('.ssb-account').show();
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
            $('#total_online_amount').val('');
        }else if(paymentMode == 1 && paymentMode != ''){
            $('.ssb-account').hide();
            $('.other-bank').show();
            $('#company_bank_detail').hide('');

        }else{
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
            $('#total_online_amount').val('');
        }
        $('.cheque-transaction').hide();
        $('.online-transaction').hide();
    });

    $('#cheque_number').on('change',function(){
        var chequeDate = $('option:selected', this).attr('data-cheque-date');
        var chequeAmount = $('option:selected', this).attr('data-cheque-amount');
        var first_date = moment(""+chequeDate+"").format('DD/MM/YYYY');
        $('#cheque_date').val(first_date);
        $('#cheque_amount').val(chequeAmount);
    });

    $('#bank_name').on('change',function(){
        var accountNumber = $('option:selected', this).attr('data-account-number');
        $('#account_number').val(accountNumber);
    });

    $(document).on('change','#company_bank', function () {
        var account = $('option:selected', this).val();
        $('#company_bank_account_number').val('');
        $('#bank_account_number').val('');
        $('.c-bank-account').hide();
        $('.'+account+'-bank-account').show();
        $('#company_bank_account_balance').val('');
    });

    $('#bank_transfer_mode').on('change',function(){
        var bankTransferMode = $('option:selected', this).val();
        if(bankTransferMode == 0 && bankTransferMode != ''){
            $('.cheque-transaction').show();
            $('.online-transaction').hide();
            $('#utr_transaction_number').val('');
            $('#total_amount').val('');
            $('#rtgs_neft_charge').val('');
            $('#total_online_amount').val('');
            $('#company_bank_detail').hide();

        }else if(bankTransferMode == 1 && bankTransferMode != ''){
            $('.online-transaction').show();
            $('.cheque-transaction').hide();
            $('#cheque_id').val('');
            $('#total_amount').val('');
            $('#company_bank_detail').show();

        }else{
            $('.online-transaction').hide();
            $('.cheque-transaction').hide();
            $('#cheque_id').val('');
            $('#total_amount').val('');
             $('#utr_transaction_number').val('');
            $('#total_amount').val('');
            $('#rtgs_neft_charge').val('');
            $('#total_online_amount').val('');
            $('#company_bank_detail').HIDE();

        }
    });

    $(document).on('click', '.pay-emi', function(e){
        var loanId = $(this).attr('data-loan-id');
        var loanEMI = $(this).attr('data-loan-emi');
        var ssbAmount = $(this).attr('data-ssb-amount');
        var ssbId = $(this).attr('data-ssb-id');
        var ssbAccount= $(this).attr('data-ssb-account');
        var recoveredAmount = $(this).attr('data-recovered-amount');
        var lastRecoveredAmount = $(this).attr('data-last-recovered-amount');
        var closingAmount = $(this).attr('data-closing-amount');
        var penaltyAmount = $(this).attr('data-penalty-amount');
        var dueAmount = $(this).attr('data-due-amount');
        $('#loan_id').val(loanId);
        $('#loan_emi_amount').val(loanEMI);
        $('#ssb_account_number').val(ssbAccount);
       $('#ssb_account').val(ssbAmount);
        $('#recovered_amount').val(recoveredAmount);
        $('#closing_amount').val(closingAmount);
        $('#due_amount').val(dueAmount);
        $('#last_recovered_amount').val(lastRecoveredAmount);
        $('#ssb_id').val(ssbId);
        if(penaltyAmount != ''){
            //$('#penalty_amount').val(penaltyAmount);
            //$('#penalty_amount').attr('readonly',false);
        }else{
            $('#penalty_amount').val('');
            $('#penalty_amount').attr('readonly',true);
        }


    })

    $(document).on('keyup','#amount',function(){
        $(".c-amount").val($(this).val());
    });

    $(document).on('keyup','#purpose',function(){
        $(".purpose-loan").val($(this).val());
    });

    $(document).on('change', '#loan_branch', function(e){
        var loanId = $(this).val();
        $('#cheque_number').val('');
        $('#cheque_date').val('');
        $('.branch-cheques').hide();
        $('.'+loanId+'-branch').show();
    })

    $('#loan_associate_code').on('change',function(){
        var associateCode = $(this).val();
        var applicationDate = $('.application_date').val();
        $.ajax({
            type: "POST",
            url: "{!! route('loan.getcollectorassociate') !!}",
            dataType: 'JSON',
            data: {'code':associateCode,'applicationDate':applicationDate},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.msg_type=='success'){
                    var firstName = response.collectorDetails.first_name ? response.collectorDetails.first_name : '';
                    var lastName = response.collectorDetails.last_name ? response.collectorDetails.last_name : '';
                    $('#associate_member_id').val(response.collectorDetails.id);
                    $('#loan_associate_name').val(firstName+' '+lastName);
                    // $('#ssb_account_number').val(response.collectorDetails.saving_account[0].account_no);
                    // $('#ssb_account').val(response.ssbAmount);
                    //$('#ssb_id').val(response.collectorDetails.saving_account[0].id);
                }else if(response.msg_type=='error'){
                    $('#loan_associate_code').val('');
                    $('#associate_member_id').val('');
                    // $('#loan_associate_name').val('');
                    // $('#ssb_account_number').val('');
                    $('#ssb_account').val('');
                    $('#ssb_id').val('');
                    swal("Error!", "Associate Code does not exists!", "error");
                }
            }
        });
    });

    $('#deposite_amount,#penalty_amount').on('change',function(){
        if($('#deposite_amount').val()){
            var depositAmount = $('#deposite_amount').val();
        }else{
            var depositAmount = 0;
        }
        if($('#penalty_amount').val()){
            var penaltyAmount = $('#penalty_amount').val();
        }else{
            var penaltyAmount = 0;
        }
        $('#cheque_total_amount').val(parseInt(depositAmount)+parseInt(penaltyAmount));
        $('#total_online_amount').val(parseInt(depositAmount)+parseInt(penaltyAmount));
    });

    // Show loading image
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    // Hide loading image
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });




    $('.export-loan-transaction').on('click',function(e){

		e.preventDefault();
		var extension = $(this).attr('data-extension');

        $('#loan_transaction_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#transaction-loan-filter').serializeObject();
        var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExportTransactions(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}else{
			$('#loan_transaction_export').val(extension);

			$('form#transaction-loan-filter').attr('action',"{!! route('branch.loantransaction.export') !!}");

			$('form#transaction-loan-filter').submit();
		}
	});


	// function to trigger the ajax bit
    function doChunkedExportTransactions(start,limit,formData,chunkSize){
        formData['start']  = start;
        formData['limit']  = limit;
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url :  "{!! route('branch.loantransaction.export') !!}",
            data : formData,
			 headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExportTransactions(start,limit,formData,chunkSize);
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


});

function showpay(amount,month,rate,divideBy) {
    if ((amount == null || amount.length == 0) || (month == null || month.length == 0) || (rate == null || rate.length == 0))
    {
        var emi = '';
        return emi;
    }
    else
    {
        var princ = amount;
        var term  = month;
        var intr  = rate / divideBy;
        var emi = princ * intr / (1 - (Math.pow(1/(1 + intr), term)));
        return Math.round(emi);
    }
}

function searchForm()

{

    if($('#filter').valid())

    {

        $('#is_search').val("yes");

        loantable.draw();

    }

}

function resetForm()

{

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

    loantable.draw();

}

function searchGroupLoanForm()

{

    if($('#grouploanfilter').valid())

    {

        $('#is_search').val("yes");

        grouploantable.draw();

    }

}

function resetGroupLoanForm()

{

    var form = $("#grouploanfilter"),

    validator = form.validate();

    validator.resetForm();

    form.find(".error").removeClass("error");

    $('#is_search').val("no");

    $('#date').val('');

    $('.from_date').val('');

    $('.to_date').val('');

    $('#loan_account_number').val('');
	
	$('#group_loan_common_id').val('');
	
    $('#member_name').val('');

    $('#member_id').val('');

    $('#associate_code').val('');

    $('#plan').val('');

    $('#status').val('');

    grouploantable.draw();

}

function loanTransactionSearchForm()

{

    if($('#transaction-loan-filter').valid())

    {

        $('#is_search').val("yes");

        loanTransactionTable.draw();

    }

}


function loanTransactionResetForm()

{


    $("#transaction-loan-filter")[0].reset();


    loanTransactionTable.draw();

}

</script>
