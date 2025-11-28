<script src="https://momentjs.com/downloads/moment.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
	var date = new Date();
    const currentDate = $('.branch_report_currentdate').val();
	  $('#start_date').datepicker({
		format: "dd/mm/yyyy",
		todayHighlight: true,  
		endDate: date, 
		autoclose: true
	  }).datepicker("setDate", 'currentDate');

	  $('#end_date').datepicker({
		format: "dd/mm/yyyy",
		todayHighlight: true, 
		endDate: date,  
		autoclose: true
	  }).datepicker("setDate", 'currentDate');
    $(document).on('click','#fn_as_minor',function(){
        if ($( "#fn_as_minor" ).prop( "checked")==true) {
        $('#nominee_parent_detail').show()
        } else {
        $('#nominee_parent_detail').hide()
        }
    });
    $(document).on('click','#sn_as_minor',function(){
        if ($( "#sn_as_minor" ).prop( "checked")==true) {
        $('#nominee_parent_second_detail').show()
        } else {
        $('#nominee_parent_second_detail').hide()
        }
    });
  
   $(document).on('click','#fn_as_minor_jeevan',function(){
	   
    if ($( "#fn_as_minor_jeevan" ).prop( "checked")==true) {
      $('#nominee_parent_detail_jeevan').show()
    } else {
      $('#nominee_parent_detail_jeevan').hide()
    }
  });

  $(document).on('click','#nominee_parent_second_detail_jeevan',function(){
    if ($( "#nominee_parent_second_detail_jeevan" ).prop( "checked")==true) {
      $('#nominee_parent_second_detail_jeevan_jeeven').show()
    } else {
      $('#nominee_parent_second_detail_jeevan_jeeven').hide()
    }
  });
    // Investment Form validations
    $('#register-plan').validate({ // initialize the plugin
        rules: {
            'investmentplan' : 'required',
            'memberid' : 'required',
            'form_number' : {required: true, number: true},
            'ssbacount' : 'required',
            'fn_first_name' : 'required',
			'member_dob' : 'required',
			're_guide' : 'required',
			'fn_re_name' : 'required',
            //'fn_second_name' : 'required',
            'fn_relationship' : 'required',
            'fn_dob' : 'required',
            'fn_age' : 'required',
            'fn_age' : 'required',
            'fn_percentage' : 'required',
            'fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'ssb_fn_first_name' : 'required',
            //'ssb_fn_second_name' : 'required',
            'ssb_fn_relationship' : 'required',
            'ssb_fn_dob' : 'required',
            'ssb_fn_age' : 'required',
            'ssb_fn_percentage' : 'required',
            'ssb_fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'sn_first_name' : 'required',
            //'sn_second_name' : 'required',
            'sn_relationship' : 'required',
            'sn_dob' : 'required',
            'sn_age' : 'required',
            'sn_percentage' : 'required',
            'sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            //'ssb_sn_first_name' : 'required',
            //'ssb_sn_second_name' : 'required',
            //'ssb_sn_relationship' : 'required',
            //'ssb_sn_dob' : 'required',
            //'ssb_sn_age' : 'required',
            //'ssb_sn_percentage' : 'required',
            //'ssb_sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            //'guardian-ralationship' : 'required',
            'phone-number' : {number: true,minlength: 10,maxlength:12},
            'monthly-deposite-amount' : {required: true, number: true},
            'daughter-name' : 'required',
            'dob' : 'required',
            'tenure' : 'required',
            'age' : 'required',
            'payment-mode' : 'required',
            'cheque-number' : {required: true},
            'cheque_id' : 'required',
            'bank-name' : 'required',
            'branch-name' : 'required',
            'cheque-date' : 'required',
            'transaction-id' : 'required',
			'rd_online_bank_id':"required",
            'rd_online_bank_ac_id':"required",
            'date' : 'required',
            'fn_gender' : 'required',
            'sn_gender' : 'required',
            'amount' : {required: true, number: true,checkAmount:true},
            'ssbamount' : {required: true, number: true},
            're_member_dob' : 'required',
            'ex_re_age' : {required: true, number: true},
            'ex_re_name' : 'required',
            'ex_re_guardians' : 'required',
            'ex_re_gender' : 'required',
        },
        submitHandler: function() {

            var paymentVal = $( "#payment-mode option:selected" ).val();
            var investmentPlan = $( "#investmentplan option:selected" ).val();
            var ssbAccountAvailability = $('input[name="ssb_account_availability"]:checked').val(); 
            var aviBalance = $('#hiddenbalance').val(); 
            var mAccount = $('#hiddenaccount').val();
            var ssbAccount = $('#ssbacount').val();
            var rdAccount = $('#rdacount').val();
            var depositeBalance = $('#amount').val();
            var fnPercentage = $('#fn_percentage').val();
            var snPercentage = $('#sn_percentage').val();

            if(snPercentage){
                snPercentage = $('#sn_percentage').val();
            }else{
                snPercentage = 0;   
            }

            if(ssbAccountAvailability==0){
                if(investmentPlan == '3' || investmentPlan == '6' || investmentPlan == '8'){
                    if(mAccount != ssbAccount){
                        $('#ssbaccount-error').show();
                        $('#ssbaccount-error').html('SSB Account not match with this member id.');
                        //event.preventDefault();
                        return false;
                    }
                }
            }

             if(paymentVal==1)
            {

                if(parseFloat($('#amount').val()).toFixed(2)!=$('#cheque-amt').val())
                {
                    swal("Error!", "Cheque amount is not equal to investment amount. Please select another cheque", "error");
                    //event.preventDefault();
                    return false;
                    
                }
            }

            if(investmentPlan != '2'){
                if(parseInt(fnPercentage)+parseInt(snPercentage) != 100){
                    $('#percentage-error').show();
                    $('#percentage-error').html('Percentage should be equal to 100.');
                    //event.preventDefault();
                    return false;
                }
            }

            if(paymentVal == '3'){
                if ( parseInt(depositeBalance) > parseInt(aviBalance) ) {
                    $('#balance-error').show();
                    $('#balance-error').html('Sufficient amount not available in your account.');
                    //event.preventDefault();
                    return false;
                }/*else{
                    return true;  
                }*/
            }else{
                $('#balance-error').html('');
                //return true;
            }
            $('.submit-investment').prop('disabled', true);
            return true;

        }
    });

    /*jQuery.validator.addClassRules("dd-tenure", {
      required: true,
      number: true,
      min: 1,
      max: 5,
    });

    jQuery.validator.addClassRules("mis-tenure", {
      required: true,
      number: true,
      min: 5,
      max: 10,
    });*/
    $(document).on('change','#investmentplan',function(){
        const BranchId =  $('#branch_id').val();
        var memberid = $("#memberid").val();
        const companyId = $('option:selected', this).attr('data-company');;
        
        $.ajax({
            type:'POST',
            url:"{!! route('branch.gst.gst_charge') !!}",
            dataType:'JSON',
            data:{branchId : BranchId,memberid:memberid,company_id:companyId},
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(response){
                console.log(response);
                if(response.msg == true)
                {
                    $('.gst_charge').show();
                    if(response.IntraState == true)
                    {
                       
                        $('#cgst_stationary_charge').show();
                        $('.igst_charge').hide();
                        $('#sgst_stationary_charge').show();
                        $('#cgst_stationary_charge').val(response.gstAmount);
                        $('#sgst_stationary_charge').val(response.gstAmount);;
                        $('#igst_stationary_charge').hide();
                        $('.cgst_charge').show();
                    }
                    else{
                       
                        $('.cgst_charge').hide();
                        $('#cgst_stationary_charge').hide();
                        $('#sgst_stationary_charge').hide();
                        $('#igst_stationary_charge').val(response.gstAmount);
                        $('#igst_stationary_charge').show();
                        $('.igst_charge').show();

                    }
                }
                else{
                    $('#cgst_stationary_charge').hide();
                    $('#sgst_stationary_charge').hide();
                    $('#igst_stationary_charge').hide();
                }
            }
        })
    })
    $('#saving-account-form').validate({ // initialize the plugin
        rules: {
            'f_number' : 'required',
            'ssb_fn_first_name' : 'required',
            //'ssb_fn_second_name' : 'required',
            'ssb_fn_relationship' : 'required',
            'ssb_fn_dob' : 'required',
            'ssb_fn_age' : 'required',
            'ssb_fn_percentage' : 'required',
            'ssb_fn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'ssb_sn_first_name' : 'required',
            //'ssb_sn_second_name' : 'required',
            'ssb_sn_relationship' : 'required',
            'ssb_sn_dob' : 'required',
            'ssb_sn_age' : 'required',
            'ssb_sn_percentage' : 'required',
            'ssb_fn_gender' : 'required',
            'ssb_sn_gender' : 'required',
            //'ssb_sn_mobile_number' : {number: true,minlength: 10,maxlength:12},
            'ssbamount' : {required: true, number: true},
        },
        submitHandler: function(form) {
            var fnPercentage = $('#ssb_fn_percentage').val();
            var snPercentage = $('#ssb_sn_percentage').val();
            if(snPercentage){
                snPercentage = $('#ssb_sn_percentage').val();
            }else{
                snPercentage = 0;   
            }
            if(parseInt(fnPercentage)+parseInt(snPercentage) != 100){
                $('#ssb-percentage-error').show();
                $('#ssb-percentage-error').html('Percentage should be equal to 100.');
                //event.preventDefault();
                return false;
            }

            var post_url = $('#saving-account-form').attr("action"); //get form action url
            var request_method = $('#saving-account-form').attr("method"); //get form GET/POST method
            var form_data = $('#saving-account-form').serialize(); //Encode form elements for submission
            $.ajax({
                url : post_url,
                type: request_method,
                data : form_data
            }).done(function(response){ //
                if(response.msg_type=='success'){
                    $("input[name=ssb_account_availability][value=0]").prop('checked', true);
                    $('.'+response.accountInput+'').show();
                    $('#ssbacount').val(response.investmentAccount);
                    $('#hiddenaccount').val(response.investmentAccount);
                    $('#hiddenbalance').val(100);
                    $('#saving-account-modal-form').modal('hide'); 
                    $('.'+response.nomineeForm+'').show();
                    $("#saving-account-form")[0].reset();
                }else if(response.msg_type=='exists'){
                    swal("Error!", "Your saving account already created!", "error");
                }else{
                    alert('Somthing went wrong!');
                }
            });
        }
    });
    
    // Get registered member by id
    $(document).on('change','#memberid',function(){
        var memberid = $(this).val();
        $.ajax({
            type: "POST",  
            url: "{!! route('investment.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log(response.view);
                $('#member_not_found').html(response.view);
                if(response.resCount > 0){
                    $('#newUser').val(response.newUser);
                      
                    (response.newUser == false) && $('.mi-charge').show();
                    $('.member-not-found').hide();
                    $('.member-detail').show();
                    response.member[0].first_name ? $('#firstname').val(response.member[0].first_name) : $('#firstname').val("First Name N/A");
                    response.member[0].last_name ? $('#lastname').val(response.member[0].last_name) : $('#lastname').val("Last Name N/A");
                    response.member[0].mobile_no ? $('#mobilenumber').val(response.member[0].mobile_no) : $('#mobilenumber').val("Mobile Number N/A");
                    response.member[0].address ? $('#address').val(response.member[0].address) : $('#address').val("Address N/A");
                    response.member[0].special_category ? $('#specialcategory').val(response.member[0].special_category) : $('#specialcategory').val("General Category");
                   /* response.member[0].special_category ? $('#specialcategory').val(response.member[0].special_category) : $('#specialcategory').val("Special Category N/A");*/
                    //response.member[0].first_name ? $('#account_holder_name').val(response.member[0].first_name+' '+response.member[0].last_name) : $('#specialcategory').val("Account Holder Name N/A");
                    $('#memberid').val(response.member[0].member_id);
                    $('#memberAutoId').val(response.member[0].id); 
                    $('#saving_account_m_id').val(response.member[0].id); 
                    $('#saving_account_m_name').val(response.member[0].first_name+' '+response.member[0].last_name);
                    response.member[0].first_id_no ?  $('#idproof').val(response.member[0].first_id_no) : $('#idproof').val('ID Proof N/ANNN');
                   
                    // if(response.member[0].saving_account[0]){
                    //     $('#hiddenbalance').val(response.member[0].saving_account[0].balance);    
                    //     $('#hiddenaccount').val(response.member[0].saving_account[0].account_no);  
                    //     //$('#account_n').val(response.member[0].saving_account[0].account_no);
                    //     //$('#account_b').val(response.member[0].saving_account[0].balance);       
                    // }else{
                    //     $('#hiddenbalance').val('');    
                    //     $('#hiddenaccount').val('');  
                    //     //$('#account_n').val("Account Number N/A");
                    //     //$('#account_b').val("Account Balance N/A");      
                    // }
                    $('.select-plan').show();

                    if(response.countInvestment > 0){
                        $("#modal-stationary-charge").modal("show");
                        $('.stationary-charge').show();
                        $('#stationary-charge').val(50);
                    }else{
                        $("#modal-stationary-charge").modal("hide");
                        $('.stationary-charge').hide();
                        $('#stationary-charge').val(0);
                    }
                }else{
                   $('.member-not-found').show();
                   $('.member-detail').hide();
                   $('.select-plan').hide();
                   $('#memberid').val('');
                   $("#modal-stationary-charge").modal("hide");
                   $('.stationary-charge').hide();
                   $('#stationary-charge').val(0);
                } 
            }
        });
    });

    // Get registered member by id
    $(document).on('keyup','#associateid',function(){
        var memberid = $(this).val();
        $.ajax({
            type: "POST",  
            url: "{!! route('investment.associate') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.resCount > 0){
                    $('.associate-not-found').hide();
                    $('.associate-member-detail').show();
                    var ass_name = response.member[0].first_name+' '+response.member[0].last_name;
                    ass_name ? $('#associate_name').val(ass_name) : $('#associate_name').val("Name N/A");
                    response.member[0].mobile_no ? $('#associate_mobile').val(response.member[0].mobile_no) : $('#associate_mobile').val("Mobile Number N/A");
                    response.member[0].carders_name ? $('#associate_carder').val(response.member[0].carders_name) : $('#associate_carder').val("Carder N/A");
                    $('#associatemid').val(response.member[0].id);
                    $("#saving_account_a_id").val(response.member[0].id);
                }else{
                   $('.associate-not-found').show();
                   $('.associate-member-detail').hide();
                   $("#saving_account_a_id").val();
                } 
            }
        });
    });

    // AJAX call for autocomplete 
    $("#member_name").keyup(function(){
        $.ajax({
        type: "POST",
        url: "{!! route('investment.searchmember') !!}",
        data:'keyword='+$(this).val(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $("#search-box").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
        },
        success: function(data){
            $("#suggesstion-box").show();
            $("#suggesstion-box").html(data);
            $("#member_name").css("background","#FFF");
        }
        });
    });

    $(document).on('click','.selectmember',function(){
        var val = $(this).attr('data-val');
        var account = $(this).attr('data-account');
        var id = $(this).attr('value');
        $("#member_name").val(val+' - ('+account+')');
        $("#member_id").val(id);
        $("#suggesstion-box").hide();
    });

    // Show member details
     $(document).on('click','.submitmember',function(){
        var memberid = $('#member_id').val();
        if(memberid > 0){
            $('.member-error').hide();
            $.ajax({
                type: "POST",  
                url: "{!! route('investment.member') !!}",
                dataType: 'JSON',
                data: {'memberid':memberid},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    //$('.modal').modal('toggle');
                    $('#modal-form').modal('hide'); 
                    $('.member-not-found').hide();
                    $('.member-detail').show();
                    response.member[0].first_name ? $('#firstname').val(response.member[0].first_name) : $('#firstname').val("First Name N/A");
                    response.member[0].last_name ? $('#lastname').val(response.member[0].last_name) : $('#lastname').val("Last Name N/A");
                    response.member[0].mobile_no ? $('#mobilenumber').val(response.member[0].mobile_no) : $('#mobilenumber').val("Mobile Number N/A");
                    response.member[0].address ? $('#address').val(response.member[0].address) : $('#address').val("Address N/A");
                    response.member[0].special_category ? $('#specialcategory').val(response.member[0].special_category) : $('#specialcategory').val("Special Category N/A");
                    //response.member[0].first_name ? $('#account_holder_name').val(response.member[0].first_name+' '+response.member[0].last_name) : $('#specialcategory').val("Account Holder Name N/A");

                    $('#memberid').val(response.member[0].member_id);
                    $('#memberAutoId').val(response.member[0].id); 
                    $('#saving_account_m_id').val(response.member[0].id);  
                    $('#saving_account_m_name').val(response.member[0].first_name+' '+response.member[0].last_name);  
                    $('#idproof').val('ID Proof N/A');  
                    // if(response.member[0].saving_account[0]){
                    //     $('#hiddenbalance').val(response.member[0].saving_account[0].balance);    
                    //     $('#hiddenaccount').val(response.member[0].saving_account[0].account_no);  
                    //     //$('#account_n').val(response.member[0].saving_account[0].account_no);
                    //     //$('#account_b').val(response.member[0].saving_account[0].balance);       
                    // }else{
                    //     $('#hiddenbalance').val('');    
                    //     $('#hiddenaccount').val('');  
                    //     //$('#account_n').val("Account Number N/A");
                    //     //$('#account_b').val("Account Balance N/A");      
                    // }
                    /*$('#ssb_fn_first_name').val(response.member[0].member_nominee[0].first_name);
                    $('#ssb_fn_second_name').val(response.member[0].member_nominee[0].last_name);
                    $('#ssb_fn_relationship').val(response.member[0].member_nominee[0].relation);
                    if(response.member[0].member_nominee[0].gender==0){
                        $("input[name=ssb_fn_gender][value='1']").prop('disabled', true);
                    }else{
                        $("input[name=ssb_fn_gender][value='0']").prop('disabled', true);
                    }
                    $("input[name=ssb_fn_gender][value="+response.member[0].member_nominee[0].gender+"]").prop('checked', true);
                    $('#ssb_fn_dob').val(response.member[0].member_nominee[0].dob);
                    $('#ssb_fn_age').val(response.member[0].member_nominee[0].age);*/
                    $('.select-plan').show();
                }
            });       
        }else{
            $('.select-plan').hide();
            $('.member-error').show();
        }
        
    });

    $(document).on('change','#amount',function(){
            const amount = $(this).val();
            const multipleAmount = $('#investmentplan option:selected').attr('data-multiple');
            const category = $('#investmentplan option:selected').attr('data-category');
       
            var checkMultipleAmount = amount / multipleAmount;
            if (checkMultipleAmount % 1 != 0 && category != 'S') {
                $(this).val('');
                swal("Warning!", "Amount should be multiply deno amount!", "warning");
                return false;
               
            }

        })

    // Show investment form according to plan
    $(document).on('change','#investmentplan',function(){
       

        const plan = $('option:selected', this).attr('data-val');
        const planId = $('option:selected', this).val();
        const memberAutoId = $('#memberAutoId').val();
        const planCategory = $('option:selected', this).attr('data-category');
        const minAmount = $('option:selected', this).attr('data-min');
        const maxAmount = $('option:selected', this).attr('data-max');
        const isSSbRequired = $('option:selected',this).attr('data-ssb-required');
        const ssbAccount = $('#hiddenaccount').val();
        const companyId = $('option:selected', this).attr('data-company');;
        const subCategory = $('option:selected',this).attr('data-sub-category');

        $('#plan_type').val(planCategory);
        $('.ssb_child_display').hide();
      
        $.ajax({
            type: "POST",  
            url: "{!! route('investment.planform') !!}",
            data: {'plan':plan,'memberAutoId':memberAutoId,    
                    'planCategory':planCategory,
                    'planId':planId,
                    'companyId':companyId, 
                    'sub_category':subCategory,},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
               
                $(".plan-content-div").html('');  
                $('#plan_sub_category').val(subCategory);
 
                $(".plan-content-div").html(response); 
                      
                $('#company_id').val(companyId);  
                $('#is_ssb_required').val(isSSbRequired);
                (isSSbRequired === '1' ) && $('.ssbRequired').css('display','flex');

                    $('#amount').attr('min',minAmount);
                $('#amount').attr('max',maxAmount);
             
                if(planCategory == 'S'){
                    // $('.ssb_child_display').show();
                    $('#amount').val(minAmount);
                }
                $('.fn_dateofbirth,.sn_dateofbirth,#dob,.re_member_dob').datepicker( {
                   format: "dd/mm/yyyy",
                   orientation: "top",
                   autoclose: true,
                   endDate:date,
                });
                $('#date').datepicker( {
                   format: "dd/mm/yyyy",
                   orientation: "top",
                   autoclose: true,
                   endDate:date,
                   startdate:'01/04/2021',
                }); 
            }
        });   
          $.ajax({
            type: "POST",  
            url: "{!! route('investment.checkmemberExist') !!}",
            data: {'memberAutoId':memberAutoId,    
                    
                    'companyId':companyId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#newUser').val(response.newUser);
                (response.newUser == false) && $('.mi-charge').show();
                if(response.checkCustomerSSb){
                        $('#hiddenbalance').val(response.checkCustomerSSb.account_no.balance);    
                        $('#hiddenaccount').val(response.checkCustomerSSb.account_no);  
                        $('#ssbacount').val(response.checkCustomerSSb.account_no);
                        $('#ssbacount').val(response.checkCustomerSSb.account_no);
                        //$('#account_n').val(response.member[0].saving_account[0].account_no);
                        //$('#account_b').val(response.member[0].saving_account[0].balance);       
                    }else{
                        $('#hiddenbalance').val('');    
                        $('#hiddenaccount').val('');  
                        //$('#account_n').val("Account Number N/A");
                        //$('#account_b').val("Account Balance N/A");      
                    }
               
            }
        });        
        
    });

    // Show ssb aacount box
    $(document).on('click', '.ssb-account-availability', function() {
            var aVal = $(this).val();
            var ssbClass = $(this).attr('data-val');
            var nomineeFormClass = $(this).attr('nominee-form-class');
            var ssbValue = $('#ssbacount').val();
            if (aVal == 0) {
                $('.' + ssbClass + '').show();
                if (ssbValue == '') {
                    $('#ssbacount').val('');
                }
                $('.ssb-show').hide();
            } else {
                $('.ssb-show').show();
                if (ssbValue == '') {
                    $('#ssbacount').val(0);
                }
                // $('#nominee_form_class').val('' + nomineeFormClass + '');
                // $('#account_box_class').val('' + ssbClass + '');
            }
        });
	 $(document).on('change','#rd_online_bank_id', function () {
        var bank_id = $('option:selected', this).val();
       $.ajax({
              type: "POST",  
              url: "{!! route('branch.bank_account_list') !!}",
              dataType: 'JSON',
              data:{bank_id:bank_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                 $('#rd_online_bank_ac_id').find('option').remove();
                 $('#rd_online_bank_ac_id').append('<option value="">Select Deposit Bank Account</option>');
                    $.each(response.account, function (index, value) { 
                    $("#rd_online_bank_ac_id").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
            });     
            }
       })
        });



    
 $(document).on('change','#cheque_id',function(){ 
    var cheque_id=$('#cheque_id').val();
    $('#cheque-number').val('');
                 $('#bank-name').val('');
                 $('#branch-name').val('');
                 $('#cheque-date').val('');
                 $('#cheque-amt').val('');

          $.ajax({
              type: "POST",  
              url: "{!! route('branch.approve_cheque_detail') !!}",
              dataType: 'JSON',
              data: {'cheque_id':cheque_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                //alert(response.id);
                 $('#cheque-number').val(response.cheque_no);
                 $('#bank-name').val(response.bank_name);
                 $('#branch-name').val(response.branch_name);
                 $('#cheque-date').val(response.cheque_create_date);
                 $('#cheque-amt').val(parseFloat(response.amount).toFixed(2));
				 $('#deposit_bank_name').val(response.deposit_bank_name);
                 $('#deposit_bank_account').val(response.deposite_bank_acc);
                 $('#cheque_detail').show();

              }
          });

  });

//Select payment option
    $(document).on('change','#payment-mode',function(){
        var paymentMode = $('option:selected', this).attr('data-val');   
        var accountNumber = $('#hiddenaccount').val();
        var accountBalance = $('#hiddenbalance').val();

        $('#cheque-number').val('');
                 $('#bank-name').val('');
                 $('#branch-name').val('');
                 $('#cheque-date').val('');
                 $('#cheque-amt').val('');

                 $('#cheque_detail').hide();
                 
        $('.p-mode').hide();
        $('.'+paymentMode+'').show();    
        if(paymentMode=='cheque-mode')
        {
            

                      $.ajax({
                          type: "POST",  
                          url: "{!! route('branch.approve_recived_cheque_list') !!}",
                          dataType: 'JSON', 
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                            $('#cheque_id').find('option').remove();
                            $('#cheque_id').append('<option value="">Select cheque number</option>');
                             $.each(response.cheque, function (index, value) { 
                                    $("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+" ( "+parseFloat(value.amount).toFixed(2)+")</option>");
                                }); 

                          }
                      });

               
        }  
		if(paymentMode=='online-transaction-mode')
        {
          $.ajax({
              type: "POST",  
              url: "{!! route('branch.getBankList') !!}",
              dataType: 'JSON', 
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#rd_online_bank_id').find('option').remove();
                $('#rd_online_bank_id').append('<option value="">Select Deposit Bank</option>');
                $.each(response.samraddhBanks, function (index, value) { 
                $("#rd_online_bank_id").append("<option value='"+value.id+"' data-account='"+value.bank_account.account_no+"'>"+value.bank_name+"  </option>");
            }); 
              }
          });
        }   
        if(paymentMode == 'ssb-account'){
            accountNumber ? $('#account_n').val(accountNumber) : $('#account_n').val("Account Number N/A");
            accountBalance ? $('#account_b').val(accountBalance) : $('#account_b').val("Account Balance N/A");
        }
    });

    $(document).on('keyup','#ssbacount',function(){
        var ssbAccountAvailability = $('input[name="ssb_account_availability"]:checked').val(); 
        var mAccount = $('#hiddenaccount').val();
        var ssbAccount = $('#ssbacount').val(); 
        var divClass = $(this).attr('nominee-form-class'); 
        if(ssbAccountAvailability==0){
            if(mAccount != ssbAccount){
                $('.'+divClass+'').hide();
                $('#ssbaccount-error').show();
                $('#ssbaccount-error').html('SSB Account not match with this member id.');
                event.preventDefault();
            }else{
                $('#ssbaccount-error').html('');
                $('.'+divClass+'').show();
            }
        }
    });


    

    // Calculate age from date
    $(document).on('change', '.kanyadhan-dob', function() {
            moment.defaultFormat = "DD/MM/YYYY";
            var date1212 = $(this).val();
            var date = moment(date1212, moment.defaultFormat).toDate();
            var inputId = $(this).attr('data-val');
            var planId = $("#investmentplan option:selected").val();

            dob1 = new Date(date);
            var today1 = new Date();
            var dob = moment(dob1, moment.defaultFormat).toDate();
            var today = moment(today1, moment.defaultFormat).toDate();
            var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
            const applicationDate = $('#created_at').val();
            $('#age').val(age);
            var tenure = 18 - age;
            let investment_tenure;
            if(tenure <= 3)
            {
                tenure =3;
               
               
            }
           
                $('#tenure').val(tenure);
            
            investment_tenure = tenure*12;
            $.ajax({
                    type: "POST",
                    url: "{!! route('investment.kanyadhanamount') !!}",
                    data: {
                        'tenure': investment_tenure,
                        'plan_id':planId,
                        'account_open_date':applicationDate,


                    },
                    dataType: 'JSON',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.resCount > 0) {
                            $('.monthly-deposite-amount').val(response.investmentAmount[0].denomination);
                            var tenure = $('.kanyadhan-yojna-tenure').val();
                            var principal = response.investmentAmount[0].denomination;
                            var rate = response?.investmentAmount[0]?.plan_tenure?.roi;
                            // if (tenure >= 8 && tenure <= 18) {
                            //     var rate = 11;
                            // } else if (tenure >= 6 && tenure <= 7) {
                            //     var rate = 10.50;
                            // } else if (tenure < 6) {
                            //     var rate = 10;
                            // }
                            var ci = 1;
                            var time = investment_tenure ;
                            var irate = rate / ci;
                            var year = time / 12;
                            var result = (principal * (Math.pow((1 + irate / 100), year * ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                            console.log(result);
                            $('.sky-maturity-amount').val(Math.round(result));
                            $('.sky-interest-rate').val(rate)

                            $('.maturity-amount').html('Maturity Amount :' + Math.round(result));
                        } else {
                            $('.monthly-deposite-amount').val('');
                            $('.maturity-amount').html('');
                        }
                    }
                });


            // if (age >= 0 && tenure >= 0) {
            //     $('#' + inputId + '').val(age);
            //     $('#tenure').val(18 - age);
            //     $.ajax({
            //         type: "POST",
            //         url: "{!! route('admin.investment.kanyadhanamount') !!}",
            //         data: {
            //             'tenure': tenure,
            //             'plan_id':planId,
            //             'account_open_date':applicationDate,


            //         },
            //         dataType: 'JSON',
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            //         success: function(response) {
            //             if (response.resCount > 0) {
            //                 $('.monthly-deposite-amount').val(response.investmentAmount[0].denomination);
            //                 var tenure = $('.kanyadhan-yojna-tenure').val();
            //                 var principal = response.investmentAmount[0].denomination;
            //                 if (tenure >= 8 && tenure <= 18) {
            //                     var rate = 11;
            //                 } else if (tenure >= 6 && tenure <= 7) {
            //                     var rate = 10.50;
            //                 } else if (tenure < 6) {
            //                     var rate = 10;
            //                 }
            //                 var ci = 1;
            //                 var time = tenure * 12;
            //                 var irate = rate / ci;
            //                 var year = time / 12;
            //                 var result = (principal * (Math.pow((1 + irate / 100), year * ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            //                 $('.maturity-amount').html('Maturity Amount :' + Math.round(result));
            //             } else {
            //                 $('.monthly-deposite-amount').val('');
            //                 $('.maturity-amount').html('');
            //             }
            //         }
            //     });
            // } else {
            //     $(this).val('');
            //     $('#' + inputId + '').val('');
            //     $('#tenure').val('');
            //     $('.monthly-deposite-amount').val('');
            //     $('.maturity-amount').html('');
            //     alert('Please select a valid date');
            // }
        });


    $(document).on('change','.fn_dateofbirth,.sn_dateofbirth,.re_member_dob',function(){
        moment.defaultFormat = "DD/MM/YYYY";
        var today1 = new Date();
        var date11 = $(this).val();
        var date = moment(date11, moment.defaultFormat).toDate();
        var today = moment(today1, moment.defaultFormat).toDate();
        var inputId = $(this).attr('data-val');
        dob = new Date(date);

        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
		if(age > 18 && inputId == 'ex_re_age'){
			$(".re_member_dob").val("");
			swal("Error!", "Age Limit For Child is Bellow 18 Years", "error");
			return false;
		}
        $('#'+inputId+'').val(age);        
    });

    $(document).on('click','#primary_account',function(){
        //var val = $(this).val();
        if ($("input[type=checkbox]").is( 
                      ":checked")) { 
            $('#hidden_primary_account').val(1) 
        } else { 
            $('#hidden_primary_account').val(0) 
        }  

    });

    $(document).on('click','#same_as_registered_nominee',function(){ 
        var firstName = $('#reg_nom_fn_first_name').val();
        var lastName = $('#reg_nom_fn_second_name').val();
        var relationship = $('#reg_nom_fn_relationship').val();
        var gender = $('#reg_nom_fn_gender').val();
        var dob = $('#reg_nom_fn_dob').val();
        var age = $('#reg_nom_fn_age').val();
        if ($("input[type=checkbox]").is( 
                      ":checked")) { 
            $('#fn_first_name').val(firstName);
            
            $('#fn_second_name').val(lastName);
            $('#fn_dob').val(dob);
            $('#fn_first_name').attr("readonly", "true");
            $('#fn_dob').attr("readonly", "true");
            $("#fn_dob").removeClass('fn_dateofbirth');
           // $("#fn_dob").datepicker('remove');
           // $("#fn_dob").prop('disabled', true);
            $('#fn_dob').datepicker('destroy');
            $('#fn_age').val(age);
            $("input[name=fn_gender][value="+gender+"]").prop('checked', true);
            $("input[name=fn_gender][value="+gender+"]").attr("readonly", "true");
            $("#fn_relationship option[value=" + relationship +"]").prop("selected",true) ;
            $("#fn_relationship").attr("readonly", "true") ;
        } else {
            $('#fn_first_name').removeAttr("readonly");
            $('#fn_first_name').val('');
            $('#fn_second_name').val('');
            $("#fn_relationship option[value='0']").prop("selected",true) ;
            $("#fn_relationship").removeAttr("readonly") ;
            $("input[name=fn_gender][value='0']").prop('checked', true);
            $("input[name=fn_gender][value='0']").removeAttr("readonly");
            $('#fn_dob').val('');
            $("#fn_dob").addClass('fn_dateofbirth');
            $('#fn_dob').removeAttr("readonly");
            $('#fn_age').val('');
            $("#fn_dob").datepicker({
                format: "dd/mm/yyyy",
                orientation: "top",
                autoclose: true
            });
            /*$('.fn_dateofbirth').datepicker( {
                format: "dd/mm/yyyy",
                orientation: "top",
                autoclose: true
            });*/
        }
    });

    $(document).on('click','#same_as_registered_ssb_nominee',function(){ 
        var firstName = $('#reg_nom_fn_first_name').val();
        var lastName = $('#reg_nom_fn_second_name').val();
        var relationship = $('#reg_nom_fn_relationship').val();
        var gender = $('#reg_nom_fn_gender').val();
        var dob = $('#reg_nom_fn_dob').val();
        var age = $('#reg_nom_fn_age').val();
        if ($("input[type=checkbox]").is( 
                      ":checked")) { 
            $('#ssb_fn_first_name').val(firstName);
            $('#ssb_fn_second_name').val(lastName);
            $('#ssb_fn_dob').val(dob);
            $('#ssb_fn_age').val(age);
            $("input[name=ssb_fn_gender][value="+gender+"]").prop('checked', true);
            $("#ssb_fn_relationship option[value=" + relationship +"]").prop("selected",true);
            $('#ssb_fn_first_name').attr("readonly", "true");
            $('#ssb_fn_dob').attr("readonly", "true");
            $('#ssb_fn_age').attr("readonly", "true");
            $("#ssb_fn_dob").removeClass('fn_dateofbirth');
            $( "#ssb_fn_dob" ).prop('disabled', true);
            $("#ssb_fn_relationship").attr("readonly", "true") ;
        } else { 
            $('#ssb_fn_first_name').val('');
            $('#ssb_fn_second_name').val('');
            $("#ssb_fn_relationship option[value='0']").prop("selected",true) ;
            $("input[name=ssb_fn_gender][value='0']").prop('checked', true);
            $('#ssb_fn_dob').val('');
            $('#ssb_fn_age').val('');
            $("#ssb_fn_relationship").removeAttr("readonly");
            $('#ssb_fn_first_name').removeAttr("readonly");
            $('#ssb_fn_dob').removeAttr("readonly");
            $('#ssb_fn_age').removeAttr("readonly",);
            $("#ssb_fn_dob").addClass('fn_dateofbirth');
            $('.fn_dateofbirth').prop('disabled', false);
        }
    });

    $(document).on('click','.add-second-nominee',function(){
        $(this).val('Remove Nominee');
        var inputClass = $(this).attr('data-class');
        var inputValue = $(this).attr('data-val');
        $('.'+inputClass+'').show(); 
        $('#'+inputValue+'').val(1);
        $('.second-nominee-input').addClass('remove-second-nominee');
        $('.second-nominee-input').removeClass('add-second-nominee');
    });

    $(document).on('click','.remove-second-nominee',function(){
        $(this).val('Add Nominee');
        var inputClass = $(this).attr('data-class');
        var inputValue = $(this).attr('data-val');
        $('.'+inputClass+'').hide(); 
        $('#'+inputValue+'').val(0);
        $('.second-nominee-input').addClass('add-second-nominee');
        $('.second-nominee-input').removeClass('remove-second-nominee');
    });

    $(document).on('keyup','.sa-nominee-percentage',function(){
        var inputId = $(this).attr('data-id');
        var value = $(this).val();
        var check = $('#sa_second_nominee_add').val();
        var buttonClass = $('#sa_second_nominee_add').attr('data-button-class');
        if(check > 0){
            if(value <= 100 && value != ''){
                var otherVal = 100-parseInt(value);
                $('#'+inputId+'').val(otherVal);
            }else{
                $(this).val('');
                $('#'+inputId+'').val(0);
            }
        }else{
            if(value == 100){
                $('.sa-second-nominee-botton').prop("disabled",true) ;
            }else{
                $('.sa-second-nominee-botton').prop("disabled",false) ;
            }
        }
    });

    $(document).on('keyup','.nominee-percentage',function(){
        var inputId = $(this).attr('data-id');
        var value = $(this).val();
        var check = $('#second_nominee_add').val();
        var buttonClass = $('#second_nominee').attr('data-button-class');
        if(check > 0){
            if(value <= 100 && value != ''){
                var otherVal = 100-parseInt(value);
                $('#'+inputId+'').val(otherVal);
            }else{
                $(this).val('');
                $('#'+inputId+'').val(0);
            }
        }else{
            if(value == 100){
                $('.second-nominee-botton').prop("disabled",true) ;
            }else{
                $('.second-nominee-botton').prop("disabled",false) ;
            }
        }
    });

    // Datatables
    var investmenttable = $('#investment-listing').DataTable({
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
            "url": "{!! route('investment.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            
            {data: 'form_number', name: 'form_number'},
            {data: 'plan', name: 'plan'},
            {data: 'company', name: 'company'},
             {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            // {data: 'sector_name', name: 'sector_name'},
            // {data: 'region_name', name: 'region_name'},
            // {data: 'zone_name', name: 'zone_name'},
            {data: 'member', name: 'member'},
            {data: 'customer_id', name: 'customer_id'},
            {data: 'member_id', name: 'member_id'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'account_number', name: 'account_number'},
            {data: 'tenure', name: 'tenure'},
          /*  {data: 'current_balance', name: 'current_balance',
                "render":function(data, type, row){
                 return row.current_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },*/
            {data: 'deposite_amount', name: 'deposite_amount',
                "render":function(data, type, row){
                 return row.deposite_amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },

            {data: 'address', name: 'address'},
            {data: 'state', name: 'state'},
            {data: 'district', name: 'district'},
            {data: 'city', name: 'city'},
            {data: 'village', name: 'village'},
            {data: 'pin_code', name: 'pin_code'},
            {data: 'firstId', name: 'firstId'},
            {data: 'secondId', name: 'secondId'},
            {data: 'collectorcode', name: 'collectorcode'},
            {data: 'collectorname', name: 'collectorname'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(investmenttable.table().container()).removeClass( 'form-inline' );

    // Show loading image
    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    // Hide loading image
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

    $(document).on('keyup change', '.ffd-tenure,.ffd-amount', function() {
            var tenure = $(".ffd-tenure option:selected").val();
            var principal = $('.ffd-amount').val();
            var rate = $(".ffd-tenure option:selected").data('roi');
            var time = tenure;
          
            var ci = 1;
            var irate = rate / ci;
            var year = time / 12;
            // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            var result = (principal * (Math.pow((1 + irate / 100), year)));
            if (Math.round(result) > 0 && tenure <= 120) {
                $('.ffd-maturity-amount').html('Maturity Amount :' + Math.round(result));
                $('.ffd-maturity-amount-val').val(Math.round(result));
                $('.ffd-interest-rate').val(rate);
            } else {
                $('.ffd-maturity-amount').html('');
                $('.ffd-maturity-amount-val').val('');
                $('.ffd-interest-rate').val('');
            }

        });

        $(document).on('keyup change', '.frd-tenure,.frd-amount', function() {
            var tenure = $(".frd-tenure option:selected").val();
            var principal = $('.frd-amount').val();
            var rate = $(".frd-tenure option:selected").data('roi');
            var time = tenure;
           
            var ci = 1;
            var irate = rate / ci;
            var year = time / 12;
            //  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            var freq = 4;

            var maturity = 0;
            for (var i = 1; i <= time; i++) {
                maturity += principal * Math.pow((1 + ((rate / 100) / freq)), freq * ((time - i + 1) / 12));
            }

            var result = maturity;

            if (Math.round(result) > 0 && tenure <= 60) {
                $('.frd-maturity-amount').html('Maturity Amount :' + Math.round(result));
                $('.frd-interest-rate').val(rate);
                $('.frd-maturity-amount-cal').val(Math.round(result));
            } else {
                $('.frd-interest-rate').val('');
                $('.frd-maturity-amount-cal').val('');
                $('.frd-maturity-amount').html('');
            }

        });

        $(document).on('keyup change', '.dd-tenure,.dd-amount', function() {
            var tenure = $(".dd-tenure option:selected").val();
            var roi = $(".dd-tenure option:selected").data('roi');
            var principal = $('.dd-amount').val();
            var time = tenure;


            var specialCategory = $('#specialcategory').val();
            // var rate = $('.rd-tenure option:selected').attr('data-roi');
            var sprcialRate = $('.dd-tenure option:selected').attr('data-spe-roi');

            var  rate =  (specialCategory != 'General Category') ? sprcialRate : roi;
        
            var ci = 12;
            var freq = 12;
            var irate = rate / ci;
            var year = time / 12;
            var days = time * 30;

            var monthlyPricipal = principal * 30;
            var maturity = 0;
            for (var i = 1; i <= time; i++) {
                maturity += monthlyPricipal * Math.pow((1 + ((rate / 100) / freq)), freq * ((time - i + 1) / 12));
            }
            var result = maturity;
            // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            if (Math.round(result) > 0) {
                $('.dd-maturity-amount').html('Maturity Amount :' + Math.round(result));
                $('.dd-interest-rate').val(rate);
                $('.dd-maturity-amount-val').val(Math.round(result));
            } else {
                $('.dd-maturity-amount').html('');
                $('.dd-interest-rate').val('');
                $('.dd-maturity-amount-val').val('');
            }

        });

        $(document).on('keyup change', '.mis-tenure,.mis-amount', function() {
            var tenure = $(".mis-tenure option:selected").val();
            var principal = $('.mis-amount').val();
            var time = tenure;
            if (time >= 0 && time <= 60) {
                var rate = 10;
            } else if (time >= 61 && time <= 84) {
                var rate = 10.50;
            } else if (time >= 85 && time <= 120) {
                var rate = 11;
            }
            var ci = 1;
            var irate = rate / ci;
            var year = time / 12;
            //;  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            var result = (((principal * rate) / 12) / 100);

            if (Math.round(result) > 0 && tenure <= 120) {
                $('.mis-maturity-amount').html('Maturity Amount :' + Math.round(result) + '/Month');
                $('.mis-maturity-amount-val').val(Math.round(result));
                $('.mis-maturity-amount-cal').val(rate);
            } else {
                $('.mis-maturity-amount').html('');
                $('.mis-maturity-amount-cal').val('');
                $('.mis-maturity-amount-val').val('');
            }

        });

        $(document).on('keyup change', '.fd-tenure,.fd-amount', function() {
            var tenure = $(".fd-tenure option:selected").val();
            var principal = $('.fd-amount').val();
            var specialCategory = $('#specialcategory').val();
            var roi = $('.fd-tenure option:selected').attr('data-roi');
            var sprcialRate = $('.fd-tenure option:selected').attr('data-spe-roi');
            console.log(sprcialRate);
            var time = tenure;

          var  rate =  (specialCategory != 'General Category') ? sprcialRate : roi;
            // console.log("time", time);
            // if (time >= 0 && time <= 18) {
            //     var rate = 8;
            // } else if (time >= 19 && time <= 48) {
            //     if (specialCategory == 'Special Category N/A') {
            //         var rate = 10;
            //     } else {
            //         var rate = 10.25;
            //     }
            // } else if (time >= 49 && time <= 60) {
            //     if (specialCategory == 'Special Category N/A') {
            //         var rate = 10.25;
            //     } else {
            //         var rate = 10.50;
            //     }
            // } else if (time >= 61 && time <= 72) {
            //     if (specialCategory == 'Special Category N/A') {
            //         var rate = 10.50;
            //     } else {
            //         var rate = 10.75;
            //     }
            // } else if (time >= 73 && time <= 96) {
            //     if (specialCategory == 'Special Category N/A') {
            //         var rate = 10.75;
            //     } else {
            //         var rate = 11;
            //     }
            // } else if (time >= 97 && time <= 120) {
            //     if (specialCategory == 'Special Category N/A') {
            //         var rate = 11;
            //     } else {
            //         var rate = 11.25;
            //     }
            // }
            // console.log("rate", rate);
            console.log("specialCategory", specialCategory);


            var ci = 1;
            var irate = rate / ci;
            var year = time / 12;
            // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);

            var result = (principal * (Math.pow((1 + irate / 100), year)));
            if (Math.round(result) > 0 && tenure <= 120) {
                $('.fd-maturity-amount').html('Maturity Amount :' + Math.round(result));
                $('.fd-interest-rate').val(rate);
                $('.fd-maturity-amount-val').val(Math.round(result));
            } else {
                $('.fd-maturity-amount').html('');
                $('.fd-interest-rate').val('');
                $('.fd-maturity-amount-val').val('');
            }

        });

        $(document).on('keyup change', '.rd-tenure,.rd-amount', function() {
            var tenure = $(".rd-tenure option:selected").val();
            var roi = $(".rd-tenure option:selected").data('roi');
            var principal = $('.rd-amount').val();
           
            var specialCategory = $('#specialcategory').val();
            // var rate = $('.rd-tenure option:selected').attr('data-roi');
            var sprcialRate = $('.rd-tenure option:selected').attr('data-spe-roi');
            var time = tenure;

            var  rate =  (specialCategory != 'General Category') ? sprcialRate : roi;
          
            console.log("rate RD", rate);
            var ci = 1;
            var irate = rate / ci;
            var year = time / 12;

            var freq = 4;

            var maturity = 0;
            for (var i = 1; i <= time; i++) {
                maturity += principal * Math.pow((1 + ((rate / 100) / freq)), freq * ((time - i + 1) / 12));
            }
            // document.getElementById("maturity").innerText=maturity;
            var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
           
            if (Math.round(result) > 0) {
                $('.rd-maturity-amount').html('Maturity Amount :' + Math.round(result));
                $('.rd-maturity-amount-val').val(Math.round(result));
                $('.rd-interest-rate').val(rate);
            } else {
                $('.rd-maturity-amount').html('');
                $('.rd-maturity-amount-val').val('');
                $('.rd-interest-rate').val('');
            }

        });

        $(document).on('keyup', '.ssmb-amount', function() {
            var principal = $('.ssmb-amount').val();
            var time = 12;
            var tenure = 7;
            var rate = 9;
            var ci = 1;
            var irate = 8 / ci;
            var year = time / 12;
            var freq = 4;
            var perYearSixtyPecent = ((principal * 12) * 60 / 100);
            var carryAmount = 0;
            var carryForwardInterest = 0;
            var oldMaturity = 0;

            var maturity = 0;


            // for(var i=1; i<=time;i++){
            //    perYearWithInterest = principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
            //     console.log("maturity-before-"+i+"--", perYearWithInterest);
            //     if(i%12 == 0){
            //        maturity+= maturity-perYearSixtyPecent;
            //        carryForwardInterest = ( maturity*( Math.pow((1 + irate / 100), i-1)));
            //     }
            // }


            for (var j = 1; j <= tenure; j++) {
                var perYearWithInterest = 0;
                for (var i = 1; i <= time; i++) {
                    perYearWithInterest += principal * Math.pow((1 + ((rate / 100) / freq)), freq * ((time - i + 1) / 12));
                }
                if (j > 1) {
                    carryForwardInterest = (oldMaturity * (Math.pow((1 + rate / 100), 1)));
                    console.log("carryForwardInterest", carryForwardInterest);

                    maturity = Math.round(perYearWithInterest + carryForwardInterest);
                    console.log("maturity", maturity);
                    oldMaturity = Math.round(maturity - perYearSixtyPecent);
                    console.log("oldMaturity", oldMaturity);

                } else {
                    oldMaturity = Math.round(perYearWithInterest - perYearSixtyPecent);
                    maturity += oldMaturity;
                    console.log("oldMaturity", oldMaturity);
                }

            }






            // document.getElementById("maturity").innerText=maturity;
            var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            if (Math.round(result) > 0) {
                $('.ssmb-maturity-amount').html('Maturity Amount :' + Math.round(result));
                $('.ssmb-maturity-amount-val').val(Math.round(result));
                $('.ssmb-interest-rate').val(rate);
                $('.ssmb-tenure').val(tenure);
            } else {
                $('.ssmb-maturity-amount').html('');
                $('.ssmb-maturity-amount-val').val('');
                $('.ssmb-interest-rate').val('');
                $('.ssmb-tenure').html('');
            }

        });

        $(document).on('keyup', '.sj-amount', function() {
            var principal = $('.sj-amount').val();
            var specialCategory = $('#specialcategory').val();
            var time = 84;
            var rate = 10.50;

            console.log("rate SJ", rate, 'principal', principal);
            var ci = 1;
            var irate = rate / ci;
            var year = time / 12;

            var freq = 1;

            var maturity = 0;
            for (var i = 1; i <= time; i++) {
                maturity += principal * Math.pow((1 + ((rate / 100) / freq)), freq * ((time - i + 1) / 12));
            }
            // document.getElementById("maturity").innerText=maturity;
            var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
            if (Math.round(result) > 0) {
                // $('.sj-maturity-amount').html('Maturity Amount :'+Math.round(result));
                $('.sj-tenure').val(time);
                $('.sj-maturity-amount-val').val(Math.round(result));
                $('.sj-interest-rate').val(rate);
            } else {
                $('.sj-maturity-amount').html('');
                $('.sj-tenure').val('');
                $('.sj-maturity-amount-val').val('');
                $('.sj-interest-rate').val('');
            }
            console.log("result", result, 'principal', principal);

        });

    $(document).on('change','#fn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#fn_gender_male').attr('disabled', true);
            $('#fn_gender_female').attr('checked', 'true');
            $('#fn_gender_female').attr('readonly', 'true');
        } else {
            $('#fn_gender_male').removeAttr('disabled', false);
            $('#fn_gender_male').removeAttr('checked');
            $('#fn_gender_female').removeAttr('readonly');
            $('#fn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('change','#sn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#sn_gender_male').attr('disabled', true);
            $('#sn_gender_female').attr('checked', 'true');
            $('#sn_gender_female').attr('readonly', 'true');
        } else {
            $('#sn_gender_male').removeAttr('disabled', false);
            $('#sn_gender_male').removeAttr('checked');
            $('#sn_gender_female').removeAttr('readonly');
            $('#sn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('change','#ssb_fn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#ssb_fn_gender_male').attr('disabled', true);
            $('#ssb_fn_gender_female').attr('checked', 'true');
            $('#ssb_fn_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_fn_gender_male').removeAttr('disabled', false);
            $('#ssb_fn_gender_male').removeAttr('checked');
            $('#ssb_fn_gender_female').removeAttr('readonly');
            $('#ssb_fn_gender_female').removeAttr('checked');
        }
    });

    $(document).on('change','#ssb_sn_relationship',function(){
        var ids = new Array();
        var ids = ['2','3','6','7','8','10'];
        if ( ids.includes( $(this).val() ) ) {
            $('#ssb_sn_gender_male').attr('disabled', true);
            $('#ssb_sn_gender_female').attr('checked', 'true');
            $('#ssb_sn_gender_female').attr('readonly', 'true');
        } else {
            $('#ssb_sn_gender_male').removeAttr('disabled', false);
            $('#ssb_sn_gender_male').removeAttr('checked');
            $('#ssb_sn_gender_female').removeAttr('readonly');
            $('#ssb_sn_gender_female').removeAttr('checked');
        }
    });

    $('#member-correction-form').validate({ // initialize the plugin
        rules: {
            'corrections' : 'required',
        },
    });

    $('#filter').validate({
        rules: {
            member_id :{
                number : true,
            },
            associate_code :{
                number : true,
            },
            company_id: {
                       required : true,
                },
        },
        messages: {
            member_id:{
                number: 'Please enter valid member id.'
            },
            associate_code:{
                number: 'Please enter valid associate code.'
            },
            scheme_account_number:{
                number: 'Please enter valid account number.'
            },
            company_id: {
                    required: 'Please select any company*'
                },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass(' ');
            element.closest('.error-msg').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            if($(element).attr('type') == 'radio'){
                $(element.form).find("input[type=radio]").each(function(which){
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).addClass('is-invalid');
                });
            }
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            if($(element).attr('type') == 'radio'){
                $(element.form).find("input[type=radio]").each(function(which){
                    $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
                    $(this).removeClass('is-invalid');
                });
            }
        }
    });
	
	$(document).on('click','.investment_filters',function(){
		
		if($('#filter').valid())
		{
			$('#is_search').val("yes");
            $(".table-section").removeClass('datatable');
			investmenttable.draw();
		}
	})
	
	$(document).on('click','#reset_form',function()
	{
        var form = $("#filter"),
        validator = form.validate();
        validator.resetForm();
        form.find(".error").removeClass("error"); 
        const currentDate = $('.branch_report_currentdate').val();
		$('#is_search').val("yes");
		$('#start_date').val(currentDate);
        $('#customer_id').val('');
        $('#end_date').val(currentDate);
        $('#company_id').val('');
		$('#plan_id').empty();

        $('#plan_id').append($('<option>', {
        value: '',
        text: 'Please select Plan'
        }));

		$('#scheme_account_number').val('');
		$('#name').val('');
		$('#member_id').val('');
		$('#associate_code').val('');
		$('#amount_status').val('');
		investmenttable.draw();
        $(".table-section").addClass("datatable");
	})

    $(document).on('change',"#company_id", function(){
            $('#plan_id').find('option').remove();
            const company_id = $(this).val();
            jQuery.ajax({
                url: "{!! route('branch.getCompanyIdPlans') !!}",
                type: "POST",
                data: {'company_id':company_id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(response){
                   var data = JSON.parse(response);

                   // get the select element by ID
                    var select = $('#plan_id');
                    var selectsomething = "Please Select branch";
                    select.append('<option value="">' + selectsomething + '</option>');
                    // loop through the response data and append each as an option to the select element
                    $.each(data, function(key, value) {
                        select.append('<option value="' + key + '">' + value + '</option>');
                    });
                },
            });
        });
	/*
	$('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
        $('form#filter').attr('action',"{!! route('branch.investment.export') !!}");
        $('form#filter').submit();
        return true;
    });
	*/
	$('.export').on('click',function(e){
		
		e.preventDefault();
		var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
			var startdate = $("#start_date").val();
			var enddate = $("#end_date").val();
			
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
			$('#investments_export').val(extension);

			$('form#filter').attr('action',"{!! route('branch.investment.export') !!}");

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
            url :  "{!! route('branch.investment.export') !!}",
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
	$(document).on('click','.submitstationarycharge',function(){
        $("#modal-stationary-charge").modal("hide");
    });
});

function printDiv(elem) {
    printJS({
    printable: elem,
    type: 'html',
    targetStyles: ['*'], 
  })
}



</script>

