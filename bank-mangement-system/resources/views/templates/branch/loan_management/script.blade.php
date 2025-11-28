<script type="text/javascript">
var investmenttable;
$(document).ready(function() {
    // Investment Form validations
    $('#register-plan').validate({ // initialize the plugin
        rules: {
            'investmentplan' : 'required',
            'memberid' : 'required',
            'form_number' : {required: true, number: true},
            'ssbacount' : 'required',
            'fn_first_name' : 'required',
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
            //'daughter-name' : 'required',
            //'dob' : 'required',
            'tenure' : 'required',
            //'age' : 'required',
            'payment-mode' : 'required',

            'cheque_id' : 'required',
            'cheque-number' : {required: true, number: true},
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
            'branchid' : 'required',
            're_member_dob':'required',
            'ex_re_name':'required',
            'ex_re_guardians':'required',
            'ex_re_gender':'required',
            'fn_parent_nominee_name':'required',
			'fn_parent_nominee_mobile_no':{required: true, number: true,minlength: 10,maxlength:12},
			'sn_parent_nominee_name':'required',
			'sn_parent_nominee_mobile_no':{required: true, number: true,minlength: 10,maxlength:12},
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
    	 $(document).on('click','#fn_edit_as_minor',function(){

    if ($( "#fn_edit_as_minor" ).prop( "checked")==true) {


      $('#nominee_edit_parent_detail').show()
    } else {

        $('#fn_parent_nominee_name').val(' ');
        $('#fn_parent_nominee_mobile_no').val(' ');

      $('#nominee_edit_parent_detail').hide()
    }
  });

   $(document).on('click','#sn_edit_as_minor',function(){

    if ($( "#sn_edit_as_minor" ).prop( "checked")==true) {
      $('#nominee_edit_parent_second_detail').show()
    } else {
      $('#nominee_edit_parent_second_detail').hide()
      $('#sn_parent_nominee_name').val('');
      $('#sn_parent_nominee_mobile_no').val('');
    }
  });




    $(document).on('click','#fn_as_minor',function(){
    if ($( "#fn_as_minor" ).prop( "checked")==true) {
      $('#nominee_parent_detail').show()
    } else {
      $('#nominee_parent_detail').hide()
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

   $(document).on('click','#sn_as_minor',function(){
    if ($( "#sn_as_minor" ).prop( "checked")==true) {
      $('#nominee_parent_second_detail').show()
    } else {
      $('#nominee_parent_second_detail').hide()
    }
  });
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
            'branchid' : 'required'
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
                dataType: 'JSON',
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

    $('#filter').validate({
      rules: {
        member_id :{
            number : true,
        },
        associate_code :{
            number : true,
        },
        scheme_account_number :{
        //    number : true,
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

    $(document).on('change','#branch',function(){
          var bId = $('option:selected', this).attr('data-val');
          var sbId = $( "#hbranchid option:selected" ).val();
          if(bId != sbId){
            $('#branchid').val('');
            swal("Warning!", "Branch does not match from top dropdown state", "warning");
          }
    });

    


    $(document).on('change','#branch',function(){
        const BranchId =  $(this).val();
        var memberid = $("#memberid").val();
        $.ajax({
            type:'POST',
            url:"{!! route('admin.gst.gst_charge') !!}",
            dataType:'JSON',
            data:{branchId : BranchId,memberid:memberid},
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

    // Get registered member by id
    $(document).on('change','#memberid',function(){
      
        var memberid = $(this).val();
        const companyId = $('#company_id option:selected').val();
        const branchId = $('#branch option:selected').val();
        const applicationDate = $('#create_application_date').val();
       if( $('#register-plan').valid()) {
        $.ajax({
            type: "POST",
            url: "{!! route('admin.investment.member') !!}",
            dataType: 'JSON',
            data: {'memberid':memberid,"companyId":companyId,"branchId":branchId,'application_date':applicationDate},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                
                if(response.resCount > 0 && response.member){
                    $('.member-not-found').hide();
                    $('.member-detail').show();
                    response?.member?.first_name ? $('#firstname').val(response?.member?.first_name) : $('#firstname').val("First Name N/A");
                    response?.member?.last_name ? $('#lastname').val(response?.member?.last_name) : $('#lastname').val("Last Name N/A");
                    response?.member?.mobile_no ? $('#mobilenumber').val(response?.member?.mobile_no) : $('#mobilenumber').val("Mobile Number N/A");
                    response?.member?.address ? $('#address').val(response?.member?.address) : $('#address').val("Address N/A");
                    response?.member?.special_category ? $('#specialcategory').val(response?.member?.special_category) : $('#specialcategory').val("General Category");
                    $('#memberid').val(response?.member?.member_id);
                    $('#memberAutoId').val(response?.member?.id);
                    $('#saving_account_m_id').val(response?.member?.id);
                    $('#saving_account_m_name').val(response.member.first_name+' '+response.member.last_name);
                    response?.member?.member_id_proofs?.first_id_no ?  $('#idproof').val(response?.member?.member_id_proofs?.first_id_no) : $('#idproof').val('ID Proof N/ANNN');
                    if(response.member.saving_account[0]){
                        $('#hiddenbalance').val(response?.member?.saving_account[0]?.balance);
                        $('#hiddenaccount').val(response?.member?.saving_account[0]?.account_no);
                     
                    }else{
                        $('#hiddenbalance').val('');
                        $('#hiddenaccount').val('');
                       
                    }
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
                //    $('.member-detail').hide();
                   $('.select-plan').hide();
                   $('#memberid').val('');
                   $("#modal-stationary-charge").modal("hide");
                   $('.stationary-charge').hide();
                   $('#stationary-charge').val(0);
                }
            }
        });
       } 
       
        
    });

    $(document).on('click','.submitstationarycharge',function(){
        $("#modal-stationary-charge").modal("hide");
    });

    // Get registered member by id
    $(document).on('keyup','#associateid',function(){
        var memberid = $(this).val();
        $.ajax({
            type: "POST",
            url: "{!! route('admin.investment.associate') !!}",
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
        url: "{!! route('admin.investment.searchmember') !!}",
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
                url: "{!! route('admin.investment.member') !!}",
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
                    if(response.member[0].saving_account[0]){
                        $('#hiddenbalance').val(response.member[0].saving_account[0].balance);
                        $('#hiddenaccount').val(response.member[0].saving_account[0].account_no);
                        //$('#account_n').val(response.member[0].saving_account[0].account_no);
                        //$('#account_b').val(response.member[0].saving_account[0].balance);
                    }else{
                        $('#hiddenbalance').val('');
                        $('#hiddenaccount').val('');
                        //$('#account_n').val("Account Number N/A");
                        //$('#account_b').val("Account Balance N/A");
                    }
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

    // Show investment form according to plan
    $(document).on('change','#investmentplan',function(){
        var plan = $('option:selected', this).attr('data-val');
        var memberAutoId = $('#memberAutoId').val();
        $('#plan_type').val(plan);
        $.ajax({
            type: "POST",
            url: "{!! route('admin.investment.planform') !!}",
            data: {'plan':plan,'memberAutoId':memberAutoId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $(".plan-content-div").html('');
                $(".plan-content-div").html(response);
                $('.fn_dateofbirth,.sn_dateofbirth,#dob').datepicker( {
                   format: "dd/mm/yyyy",
                   orientation: "top",
                   autoclose: true,
                   endDate: date,
                });
                $('#date').datepicker( {
                   format: "dd/mm/yyyy",
                   orientation: "top",
                   autoclose: true,
                   endDate: date,
                   startDate: '01/04/2021',
                });

            }
        });

    });

    // Show ssb aacount box
    $(document).on('click','.ssb-account-availability',function(){
        var aVal = $(this).val();
        var ssbClass = $(this).attr('data-val');
        var nomineeFormClass = $(this).attr('nominee-form-class');
        var ssbValue = $('#ssbacount').val();
        if(aVal == 0){
            $('.'+ssbClass+'').show();
            if(ssbValue == ''){
                $('#ssbacount').val('');
            }
        }else{
            $('.'+ssbClass+'').hide();
            if(ssbValue == ''){
                $('#ssbacount').val(0);
            }
            $('#nominee_form_class').val(''+nomineeFormClass+'');
            $('#account_box_class').val(''+ssbClass+'');
        }
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
              url: "{!! route('admin.approve_cheque_details') !!}",
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
   $(document).on('change','#rd_online_bank_id', function () {
            var bank_id = $('option:selected', this).val();
           $.ajax({
                  type: "POST",
                  url: "{!! route('admin.bank_account_list') !!}",
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
    // Select payment option
    $(document).on('change','#payment-mode',function(){
        var paymentMode = $('option:selected', this).attr('data-val');
        var accountNumber = $('#hiddenaccount').val();
        var accountBalance = $('#hiddenbalance').val();
        $('#cheque-number').val();
                 $('#bank-name').val();
                 $('#branch-name').val();
                 $('#cheque-date').val();
                 $('#cheque-amt').val('');
                 $('#cheque_detail').hide();

        $('.p-mode').hide();
        $('.'+paymentMode+'').show();
        if(paymentMode=='cheque-mode')
        {


                      $.ajax({
                          type: "POST",
                          url: "{!! route('admin.approve_recived_cheque_lists') !!}",
                          dataType: 'JSON',
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) {
                            $('#cheque_id').find('option').remove();
                            $('#cheque_id').append('<option value="">Select cheque number</option>');
                             $.each(response.cheque, function (index, value) {
                                    $("#cheque_id").append("<option value='"+value.id+"'>"+value.cheque_no+"  ( "+parseFloat(value.amount).toFixed(2)+")</option>");
                                });

                          }
                      });


        }
		  if(paymentMode=='online-transaction-mode')
        {
          $.ajax({
              type: "POST",
              url: "{!! route('admin.getBankList') !!}",
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
                $('.'+divClass+'').show();
            }
        }
    });


    $( "#register-plan" ).submit(function( event ) {

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
            if(investmentPlan == '3' || investmentPlan == '6' || investmentPlan == '8' || investmentPlan == '13'){
                if(mAccount != ssbAccount){
                    $('#ssbaccount-error').show();
                    $('#ssbaccount-error').html('SSB Account not match with this member id');
                    event.preventDefault();
                }
            }
        }

        if(investmentPlan != '2'){
            if(parseInt(fnPercentage)+parseInt(snPercentage) != 100){
                $('#percentage-error').show();
                $('#percentage-error').html('Percentage should be equal to 100.');
                event.preventDefault();
            }
        }

        if(paymentVal == '3'){
            if ( parseInt(depositeBalance) > parseInt(aviBalance) ) {
                $('#balance-error').show();
                $('#balance-error').html('Sufficient amount not available in your account.');
                event.preventDefault();
            }else{
                return true;
            }
        }else{
            $('#balance-error').html('');
            return true;
        }
    });

    // Calculate age from date
    $(document).on('change','.kanyadhan-dob',function(){
        moment.defaultFormat = "DD/MM/YYYY";
        var date1212 = $(this).val();
        var date = moment(date1212, moment.defaultFormat).toDate();
        var inputId = $(this).attr('data-val');

        dob1 = new Date(date);
        var today1 = new Date();
        var dob = moment(dob1, moment.defaultFormat).toDate();
        var today = moment(today1, moment.defaultFormat).toDate();
        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));

        var tenure = 18-age;
        if(age >= 0 && tenure >= 0){
            $('#'+inputId+'').val(age);
            $('#tenure').val(18-age);
            $.ajax({
                type: "POST",
                url: "{!! route('admin.investment.kanyadhanamount') !!}",
                data: {'fa_code':709,'tenure':tenure},
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.resCount > 0){
                        $('.monthly-deposite-amount').val(response.investmentAmount[0].amount);
                        var tenure = $('.kanyadhan-yojna-tenure').val();
                        var principal = response.investmentAmount[0].amount;
                        if(tenure >= 8 && tenure <= 18){
                            var rate = 11;
                        }else if(tenure >= 6 && tenure <= 7){
                            var rate = 10.50;
                        }else if(tenure < 6){
                            var rate = 10;
                        }
                        var ci = 1;
                        var time = tenure*12;
                        var irate = rate / ci;
                        var year = time / 12;
                        var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
                        $('.maturity-amount').html('Maturity Amount :'+Math.round(result));
                    }else{
                       $('.monthly-deposite-amount').val('');
                       $('.maturity-amount').html('');
                    }
                }
            });
        }else{
            $(this).val('');
            $('#'+inputId+'').val('');
            $('#tenure').val('');
            $('.monthly-deposite-amount').val('');
            $('.maturity-amount').html('');
            alert('Please select a valid date');
        }
    });

    $(document).on('change','.fn_dateofbirth,.sn_dateofbirth',function(){
        moment.defaultFormat = "DD/MM/YYYY";
        var today1 = new Date();
        var date11 = $(this).val();
        var date = moment(date11, moment.defaultFormat).toDate();
        var today = moment(today1, moment.defaultFormat).toDate();
        var inputId = $(this).attr('data-val');
        dob = new Date(date);

        var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
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
            $('#fn_age').val(age);
            $("input[name=fn_gender][value="+gender+"]").prop('checked', true);
            $("#fn_relationship option[value=" + relationship +"]").prop("selected",true) ;
        } else {
            $('#fn_first_name').val('');
            $('#fn_second_name').val('');
            $("#fn_relationship option[value='0']").prop("selected",true) ;
            $("input[name=fn_gender][value='0']").prop('checked', true);
            $('#fn_dob').val('');
            $('#fn_age').val('');
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
            $("#ssb_fn_relationship option[value=" + relationship +"]").prop("selected",true) ;
        } else {
            $('#ssb_fn_first_name').val('');
            $('#ssb_fn_second_name').val('');
            $("#ssb_fn_relationship option[value='0']").prop("selected",true) ;
            $("input[name=ssb_fn_gender][value='0']").prop('checked', true);
            $('#ssb_fn_dob').val('');
            $('#ssb_fn_age').val('');
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

    var date = new Date();
   // const currentDate = $("#investment_listing_currentdate").val();
    $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        todayHighlight: true,  
        endDate: date, 
        autoclose: true
    });

    $('#end_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        todayHighlight: true, 
        endDate: date,  
        autoclose: true
    });

    $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('input[name="start_date"]').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
    });

    // Datatables
    investmenttable = $('#investment-listing').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.investment.listing') !!}",
            "type": "POST",
            "data":function(d) {
                d.searchform=$('form#filter').serializeArray(),
                d.start_date=$('#start_date').val(),
                d.end_date=$('#end_date').val(),
                d.branch_id=$('#branch_id').val(),
                d.plan_id=$('#plan_id').val(),
                d.scheme_account_number=$('#scheme_account_number').val(),
                d.name=$('#name').val(),
                d.member_id=$('#member_id').val(),
                d.associate_code=$('#associate_code').val(),
                d.is_search=$('#is_search').val(),
                d.investments_export=$('#investments_export').val()
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'form_number', name: 'form_number'},
            {data: 'plan', name: 'plan'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'},
            {data: 'member', name: 'member'},
            {data: 'member_id', name: 'member_id'},
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},
            {data: 'collectorcode', name: 'collectorcode'},
            {data: 'collectorname', name: 'collectorname'},
            {data: 'account_number', name: 'account_number'},
            {data: 'tenure', name: 'tenure'},
            {data: 'current_balance', name: 'current_balance',
                "render":function(data, type, row){
                 return row.current_balance+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
              },
            {data: 'eli_amount', name: 'eli_amount'},
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
            // {data: 'cover', name: 'cover',orderable: false, searchable: false},
            // {data: 'maturity', name: 'maturity',orderable: false, searchable: false},
            {data: 'transaction', name: 'transaction',orderable: false, searchable: false},
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

    $(document).on('keyup change','.ffd-tenure,.ffd-amount',function(){
        var tenure = $( ".ffd-tenure option:selected" ).val();
        var principal = $('.ffd-amount').val();
		var rate = $( ".ffd-tenure option:selected" ).data('roi');
        var time = tenure;
		/*
        if(time >= 0 && time <= 12){
            var rate = 7;
        }
        else if(time >= 13 && time <= 36){
            var rate = 8;
        }else if(time >= 37 && time <= 48){
            var rate = 8.25;
        }else if(time >= 49 && time <= 60){
            var rate = 8.50;
        }else if(time >= 61 && time <= 72){
            var rate = 8.75;
        }else if(time >= 73 && time <= 84){
            var rate = 9;
        }else if(time >= 85 && time <= 96){
            var rate = 9.50;
        }else if(time >= 97 && time <= 108){
            var rate = 10;
        }else if(time >= 109 && time <= 120){
            var rate = 11;
        }
		*/
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
       // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        var result =  ( principal*( Math.pow((1 + irate / 100), year)));
        if(Math.round(result) > 0 && tenure <= 120){
           $('.ffd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.ffd-maturity-amount-val').val(Math.round(result));
            $('.ffd-interest-rate').val(rate);
        }else{
            $('.ffd-maturity-amount').html('');
            $('.ffd-maturity-amount-val').val('');
            $('.ffd-interest-rate').val('');
        }

    });

    $(document).on('keyup change','.frd-tenure,.frd-amount',function(){
        var tenure = $( ".frd-tenure option:selected" ).val();
        var principal = $('.frd-amount').val();
        var rate = $( ".frd-tenure option:selected" ).data('roi');
        var time = tenure;
        /*
		if(time >= 0 && time <= 12){
            var rate = 5;
        }else if(time >= 13 && time <= 24){
            var rate = 6;
        }else if(time >= 25 && time <= 36){
            var rate = 6.50;
        }else if(time >= 37 && time <= 48){
            var rate = 7;
        }else if(time >= 49 && time <= 60){
            var rate = 9;
        }
		*/
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
      //  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        var freq = 4;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }

        var result = maturity;

        if(Math.round(result) > 0 && tenure <= 60){
            $('.frd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.frd-interest-rate').val(rate);
            $('.frd-maturity-amount-cal').val( Math.round(result) );
        }else{
            $('.frd-interest-rate').val('');
            $('.frd-maturity-amount-cal').val('');
            $('.frd-maturity-amount').html('');
        }

    });

    $(document).on('keyup change','.dd-tenure,.dd-amount',function(){
        var tenure = $( ".dd-tenure option:selected" ).val();
		var rate = $( ".dd-tenure option:selected" ).data('roi');
        var principal = $('.dd-amount').val();
        var time = tenure;
		/*
        if(time >= 0 && time <= 12){
            var rate = 6;
        }else if(time >= 13 && time <= 24){
            var rate = 6.50;
        }else if(time >= 25 && time <= 36){
            var rate = 7;
        }else if(time >= 37 && time <= 60){
            var rate = 7.25;
        }
		*/
        var ci = 12;
        var freq = 12;
        var irate = rate / ci;
        var year = time/12;
        var days = time*30;

        var monthlyPricipal = principal*30;
        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=monthlyPricipal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
        var result = maturity;
        // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 && tenure <= 60){
            $('.dd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.dd-interest-rate').val(rate);
            $('.dd-maturity-amount-val').val(Math.round(result));
        }else{
            $('.dd-maturity-amount').html('');
            $('.dd-interest-rate').val('');
            $('.dd-maturity-amount-val').val('');
        }

    });

    $(document).on('keyup change','.mis-tenure,.mis-amount',function(){
        var tenure = $( ".mis-tenure option:selected" ).val();
        var principal = $('.mis-amount').val();
        var time = tenure;
        if(time >= 0 && time <= 60){
            var rate = 10;
        }else if(time >= 61 && time <= 84){
            var rate = 10.50;
        }else if(time >= 85 && time <= 120){
            var rate = 11;
        }
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
      //;  var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        var result = (((principal * rate ) / 12) / 100 );

        if(Math.round(result) > 0 && tenure <= 120){
            $('.mis-maturity-amount').html('Maturity Amount :'+Math.round(result)+'/Month');
            $('.mis-maturity-amount-val').val(Math.round(result));
            $('.mis-maturity-amount-cal').val( rate );
        }else{
            $('.mis-maturity-amount').html('');
            $('.mis-maturity-amount-cal').val('');
            $('.mis-maturity-amount-val').val('');
        }

    });

    $(document).on('keyup change','.fd-tenure,.fd-amount',function(){
        var tenure = $( ".fd-tenure option:selected" ).val();
        var principal = $('.fd-amount').val();
        var specialCategory = $('#specialcategory').val();

        var time = tenure;

        console.log("time", time);
        if(time >= 0 && time <= 18){
            var rate = 8;
        }else if(time >= 19 && time <= 48){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10;
            } else {
                var rate = 10.25;
            }
        }else if(time >= 49 && time <= 60){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10.25;
            } else {
                var rate = 10.50;
            }
        }else if(time >= 61 && time <= 72){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10.50;
            } else {
                var rate = 10.75;
            }
        }else if(time >= 73 && time <= 96){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10.75;
            } else {
                var rate = 11;
            }
        }else if(time >= 97 && time <= 120){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 11;
            } else {
                var rate = 11.25;
            }
        }
        console.log("rate", rate);
        console.log("specialCategory", specialCategory);


        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;
       // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);

        var result =  ( principal*( Math.pow((1 + irate / 100), year)));
        if(Math.round(result) > 0 && tenure <= 120){
            $('.fd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.fd-interest-rate').val(rate);
            $('.fd-maturity-amount-val').val(Math.round(result));
        }else{
            $('.fd-maturity-amount').html('');
            $('.fd-interest-rate').val('');
            $('.fd-maturity-amount-val').val('');
        }

    });

    $(document).on('keyup change','.rd-tenure,.rd-amount',function(){
        var tenure = $( ".rd-tenure option:selected" ).val();
		var rate = $( ".rd-tenure option:selected" ).data('roi');
        var principal = $('.rd-amount').val();
        var specialCategory = $('#specialcategory').val();
        var time = tenure;
		/*
        if(time >= 0 && time <= 36){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 8;
            } else {
                var rate = 8.50;
            }

        }else if(time >= 37 && time <= 60){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 9;
            } else {
                var rate = 9.50;
            }
        }else if(time >= 61 && time <= 84){
            if ( specialCategory == 'Special Category N/A' ) {
                var rate = 10;
            } else {
                var rate = 10.50;
            }
        }
		*/
        console.log("rate RD", rate);
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 4;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
       // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 && tenure <= 84){
            $('.rd-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.rd-maturity-amount-val').val(Math.round(result));
            $('.rd-interest-rate').val(rate);
        }else{
            $('.rd-maturity-amount').html('');
            $('.rd-maturity-amount-val').val('');
            $('.rd-interest-rate').val('');
        }

    });

    $(document).on('keyup','.ssmb-amount',function(){
        var principal = $('.ssmb-amount').val();
        var time = 12;
        var tenure = 7;
        var rate = 9;
        var ci = 1;
        var irate = 8 / ci;
        var year = time / 12;
        var freq = 4;
        var perYearSixtyPecent = ((principal * 12)*60/100);
        var carryAmount = 0;
        var carryForwardInterest = 0;
       	var oldMaturity = 0;

        var maturity=0;


        // for(var i=1; i<=time;i++){
        //    perYearWithInterest = principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        //     console.log("maturity-before-"+i+"--", perYearWithInterest);
        //     if(i%12 == 0){
        //        maturity+= maturity-perYearSixtyPecent;
        //        carryForwardInterest = ( maturity*( Math.pow((1 + irate / 100), i-1)));
        //     }
        // }


        for(var j=1; j<=tenure;j++){
        	var perYearWithInterest = 0;
        	for(var i=1; i<=time;i++){
	            perYearWithInterest+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
	        }
	        if(j > 1){
	           	carryForwardInterest = ( oldMaturity*( Math.pow((1 + rate / 100), 1)));
	           		console.log("carryForwardInterest", carryForwardInterest);

		       	maturity = Math.round(perYearWithInterest + carryForwardInterest);
		       	console.log("maturity", maturity);
		       	oldMaturity = Math.round(maturity - perYearSixtyPecent);
		       	console.log("oldMaturity", oldMaturity);

	        }else{
	        	oldMaturity = Math.round(perYearWithInterest-perYearSixtyPecent);
	        	maturity+= oldMaturity;
	        	console.log("oldMaturity", oldMaturity);
	        }

        }






        // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 ){
            $('.ssmb-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.ssmb-maturity-amount-val').val(Math.round(result));
            $('.ssmb-interest-rate').val(rate);
            $('.ssmb-tenure').val(tenure);
        }else{
            $('.ssmb-maturity-amount').html('');
            $('.ssmb-maturity-amount-val').val('');
            $('.ssmb-interest-rate').val('');
            $('.ssmb-tenure').html('');
        }

    });

    $(document).on('keyup','.sj-amount',function(){
        var principal = $('.sj-amount').val();
        var specialCategory = $('#specialcategory').val();
        var time = 84;
        var rate = 10.50;

        console.log("rate SJ", rate, 'principal', principal);
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 1;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
        // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0 ){
           // $('.sj-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.sj-tenure').val(time);
            $('.sj-maturity-amount-val').val(Math.round(result));
            $('.sj-interest-rate').val(rate);
        }else{
            $('.sj-maturity-amount').html('');
            $('.sj-tenure').val('');
            $('.sj-maturity-amount-val').val('');
            $('.sj-interest-rate').val('');
        }
        console.log("result", result, 'principal', principal);

    });

    $(document).on('keyup','.sb-amount',function(){
        var tenure = 120;
        var principal = $(this).val();
        var time = tenure;
        var rate = 11;
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 1;

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        }
        // document.getElementById("maturity").innerText=maturity;
        var result = maturity; //( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);


        // var result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        if(Math.round(result) > 0){
            $('.sb-maturity-amount').html('Maturity Amount :'+Math.round(result));
            $('.sb-maturity-amount-cal').val(rate);
            $('.sb-maturity-amount-val').val(Math.round(result));
            $('.sb-tenure').val(tenure);
        }else{
            $('.sb-maturity-amount').html('');
            $('.sb-maturity-amount-cal').val('');
            $('.sb-maturity-amount-val').val();
            $('.sb-tenure').val('');
        }

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
/*
    $('.export').on('click',function(){
        var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
        $('form#filter').attr('action',"{!! route('admin.investment.export') !!}");
        $('form#filter').submit();
        return true;
    });
*/
$('.export').on('click',function(e){

		e.preventDefault();
	    var extension = $(this).attr('data-extension');
        $('#investments_export').val(extension);
		if(extension == 0)
		{
        var formData = jQuery('#filter').serializeObject();
         var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
        doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
		}
		else{
			$('#investments_export').val(extension);

			$('form#filter').attr('action',"{!! route('admin.investment.export') !!}");

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
            url :  "{!! route('admin.investment.export') !!}",
            data : formData,
            success: function(response) {
                console.log(response);
                if(response.result=='next'){
                    start = start + chunkSize;
                    doChunkedExport(start,limit,formData,chunkSize);
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
});

function printDiv(elem) {
    printJS({
    printable: elem,
    type: 'html',
    targetStyles: ['*'],
  })
}

function searchForm()
{
    if($('#filter').valid())
    {
        $('#is_search').val("yes");

        $(".table-section").removeClass('datatable');
        investmenttable.draw();
    }
}


function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    const currentDate = $("#investment_listing_currentdate").val();
    $('#is_search').val("no");
    $('#start_date').val('');
    $('#end_date').val('');
    $('#branch_id').val('');
    $('#plan_id').val('');
    $('#scheme_account_number').val('');
    $('#name').val('');
    $('#member_id').val('');
    $('#associate_code').val('');
    $('#amount_status').val('');
    investmenttable.draw();
    $(".table-section").addClass("datatable");
}
</script>

