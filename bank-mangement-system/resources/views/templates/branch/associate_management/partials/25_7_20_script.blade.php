


<script type="text/javascript">
$(document).ready(function () {

  var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

  $('#rd_online_date').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: date, 
  autoclose: true
  });
  $('#rd_cheque_date').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: date, 
  autoclose: true
  });

  $('#ssb_first_dob').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: date, 
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#ssb_first_age').val(age);
    $('.datepicker-dropdown').hide();

  });
  $('#ssb_second_dob').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: date, 
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#ssb_second_age').val(age);
    $('.datepicker-dropdown').hide();

  });
    $('#rd_first_dob').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: date, 
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#rd_first_age').val(age);
    $('.datepicker-dropdown').hide();

  });
  $('#rd_second_dob').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: date, 
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#rd_second_age').val(age);
    $('.datepicker-dropdown').hide();

  });
  function getAge(dateVal) {
    var birthday = new Date(dateVal.value),
    today = new Date(),
    ageInMilliseconds = new Date(today - birthday),
    years = ageInMilliseconds / (24 * 60 * 60 * 1000 * 365.25 ),
    months = 12 * (years % 1),
    days = Math.floor(30 * (months % 1));
    return Math.floor(years);
  }

  $(document).on('keyup','#member_id',function(){
    $('#show_mwmber_detail').html('');
    var code = $(this).val();
    if (code!='') {
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.memberDataGet') !!}",
              dataType: 'JSON',
              data: {'code':code},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.msg_type=="success")
                {
                  $('#show_mwmber_detail').html(response.view);
                  $('#id').val(response.id); 
                  $('#ssb_first_first_name_old').val(response.nomineeDetail.name);
                  $('#ssb_first_relation_old').val(response.nomineeDetail.relation);
                  $('#ssb_first_dob_old').val(response.nomineeDOB); 
                  $('#ssb_first_age_old').val(response.nomineeDetail.age);
                  $('#ssb_first_mobile_no_old').val(response.nomineeDetail.mobile_no);
                   $('#ssb_first_gender_old').val(response.nomineeDetail.gender); 
                }
                else if(response.msg_type=="error1")
                {
                  $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Member already associate!</strong> </div>');
                }
                else
                {
                  
                  $('#show_mwmber_detail').html('<div class="alert alert-danger alert-block">  <strong>Member not found!</strong> </div>');
                }
              }
          });
    } 
    
  });
  $(document).on('click','#associate_admin',function(){
    $('#associate_msg').text('');
      $('#senior_name').val('');
      $('#senior_mobile_no').val('');
      $('#senior_carder').val('');
    if ($( "#associate_admin" ).prop( "checked")==true) {  
      $('#senior_code').val(0);  
      $('#senior_id').val(0); 
      $("#senior_code").prop("disabled", true);
    } else {
      $('#senior_id').val('');  
      $('#senior_code').val('');  
      $("#senior_code").prop("disabled", false);
    }
    

  });

  $(document).on('click','#second_nominee_ssb',function(){
    $('#ssb_second_no_div').show();
    $('#second_nominee_ssb_remove').show();
    $('#ssb_second_validate').val(1);
    $('#second_nominee_ssb').hide();
  });
  $(document).on('click','#second_nominee_ssb_remove',function(){
    $('#ssb_second_no_div').hide();
    $('#second_nominee_ssb_remove').hide();
    $('#ssb_second_validate').val(0);
    $('#second_nominee_ssb').show();
  });
   $(document).on('click','#second_nominee_rd',function(){
    $('#rd_second_no_div').show();
    $('#second_nominee_rd_remove').show();
    $('#rd_second_validate').val(1);
     $('#second_nominee_rd').hide();
  });
   $(document).on('click','#second_nominee_rd_remove',function(){
    $('#rd_second_no_div').hide();
    $('#second_nominee_rd_remove').hide();
    $('#rd_second_validate').val(0);
    $('#second_nominee_rd').show();
  });

   $(document).on('click','#old_ssb_no_detail',function(){
    if($('#old_ssb_no_detail').prop("checked")==true)
    {
                $('#ssb_first_first_name').val($('#ssb_first_first_name_old').val());
                  $('#ssb_first_relation').val($('#ssb_first_relation_old').val());
                  $('#ssb_first_dob').val($('#ssb_first_dob_old').val()); 
                  $('#ssb_first_age').val($('#ssb_first_age_old').val());
                  $('#ssb_first_mobile_no').val($('#ssb_first_mobile_no_old').val());
            if($('#ssb_first_gender_old').val()==1)
            {
              $('#ssb_first_gender_male').prop("checked", true);
            } 
            else{
              $('#ssb_first_gender_female').prop("checked", true);
            }
    }
    else
    {
                  $('#ssb_first_first_name').val('');
                  $('#ssb_first_relation').val('');
                  $('#ssb_first_dob').val('');
                  $('#ssb_first_age').val('');
                  $('#ssb_first_mobile_no').val('');
                  $('#ssb_first_gender_male').prop("checked", false);
                  $('#ssb_first_gender_female').prop("checked", false);
    }
  });
   $(document).on('click','#old_rd_no_detail',function(){
    if($('#old_rd_no_detail').prop("checked")==true)
    {
                $('#rd_first_first_name').val($('#ssb_first_first_name_old').val());
                  $('#rd_first_relation').val($('#ssb_first_relation_old').val());
                  $('#rd_first_dob').val($('#ssb_first_dob_old').val()); 
                  $('#rd_first_age').val($('#ssb_first_age_old').val());
                  $('#rd_first_mobile_no').val($('#ssb_first_mobile_no_old').val());
            if($('#ssb_first_gender_old').val()==1)
            {
              $('#rd_first_gender_male').prop("checked", true);
            } 
            else{
              $('#rd_first_gender_female').prop("checked", true);
            }
    }
    else
    {
                  $('#rd_first_first_name').val('');
                  $('#rd_first_relation').val('');
                  $('#rd_first_dob').val('');
                  $('#rd_first_age').val('');
                  $('#rd_first_mobile_no').val('');
                  $('#rd_first_gender_male').prop("checked", false);
                  $('#rd_first_gender_female').prop("checked", false);
    }
  });
  

  $(document).on('keyup','#senior_code',function(){  
      $('#senior_id').val('');
      $('#associate_msg').text('');
      $('#senior_name').val('');
      $('#senior_mobile_no').val('');
      $('#seniorcarder_id').val('');
      var code = $(this).val();
      if(code!=0)
      $.ajax({
                type: "POST",  
                url: "{!! route('branch.seniorDetail') !!}",
                dataType: 'JSON',
                data: {'code':code},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                  if(response.resCount>0)
                  {
                    $.each(response.data, function (index, value) { 
                          $('#senior_name').val(value.first_name+' '+value.last_name);
                          $('#senior_id').val(value.id);
                          $('#senior_mobile_no').val(value.mobile_no);
                          $('#seniorcarder_id').val(response.carder_id);
                      });
                    $.ajax({
                          type: "POST",  
                          url: "{!! route('branch.getCarderAssociate') !!}",
                          dataType: 'JSON',
                          data: {'id':response.carder_id},
                          headers: {
                              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                          },
                          success: function(response) { 
                            $('#current_carder').find('option').remove();
                            $('#current_carder').append('<option value="">Select Carder</option>');
                             $.each(response.carde, function (index, value) { 
                                    $("#current_carder").append("<option value='"+value.id+"'>"+value.name+"("+value.short_name+")</option>");
                                }); 

                          }
                      });

                  }
                  else
                  {
                    $('#associate_msg').text('No match found');
                    $('.invalid-feedback').show();
                  }
                  $('#senior_name').trigger('keypress');
                  $('#senior_name').trigger('keyup');
                }
            });
      
  });

  /*$(document).on('keyup','#form_no',function(){
    var form_no = $(this).val();
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associateformnocheck') !!}",
              dataType: 'JSON',
              data: {'form_no':form_no},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount>0)
                {
                  return false;
                }
              }
          })
  });
*/
    $.validator.addMethod("checkFormNo", function(value, element,p) {
  
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associateformnocheck') !!}",
              dataType: 'JSON',
              data: {'form_no':value},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==0)
                {
                  result = true;
                  $.validator.messages.checkFormNo = "";
                }
                else
                {
                  result = false;
                  $.validator.messages.checkFormNo = "Form No already ready exists";
                }
              }
          });


    return result;
  }, "");
$('#associate_register').submit(function () {
  if($('#associate_register').valid())
  { 
    $('#associate_register').hide();
    $('#associate_register_next').show();
  } 
  return false;
});
 $(document).on('click','#previous_form',function(){
  
    $('#associate_register_next').hide();
    $('#associate_register').show();
    
   
  return false;
});


$('#associate_register_next').submit(function () {
  if($('#associate_register_next').valid())
  { 
    var formData = new FormData(document.forms['associate_register']); // with the file input
    var poData = jQuery(document.forms['associate_register_next']).serializeArray();
    for (var i=0; i<poData.length; i++)
    formData.append(poData[i].name, poData[i].value);

    if($('#ssb_accountexists').val()==0){
        $.ajax({
              type: "POST",  
              url: "{!! route('branch.associate_save') !!}",
              dataType: 'JSON',
              data: formData,
              cache: false,
              contentType: false,
              processData: false,
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {                 

                if(response.msg_type=="success")
                {
                  if(response.reciept_id>0)
                  {
                      var receipt_id=response.reciept_id;
                      window.location.href = "{{url('/branch/associate/receipt')}}/"+receipt_id;
                  }
                  else
                  {
                    window.location.href = " {!! route('branch.associate_list') !!}";
                  }
                  document.getElementById('associate_register').reset();
                  document.getElementById('associate_register_next').reset();
                  swal("Success!", ""+response.msg+"", "success");
                  $('#form1error').html('<div class="alert alert-success alert-block">  <strong>'+response.msg+'</strong> </div>');
                }
                else
                {
                  if(response.form1>0)
                  {
                    $('#associate_register').show();
                    $('#associate_register_next').hide();
                    $('#form1error').html('<div class="alert alert-danger alert-block">  <strong>'+response.errormsg1+'</strong> </div>');
                    if(response.form2>0){
                      $('#form2error').html('<div class="alert alert-danger alert-block">  <strong>'+response.errormsg2+'</strong> </div>');
                    }
                  }else
                  {
                    if(response.form2>0){
                    $('#associate_register').hide();
                    $('#associate_register_next').show();
                    $('#form2error').html('<div class="alert alert-danger alert-block">  <strong>'+response.errormsg2+'</strong> </div>');
                    }
                  }
                  
                  swal("Error!", ""+response.msg+"", "error"); 
                }
              }
          });
      }
      else
      {
          swal("Error!", "SSB account already exists!", "error"); 
      }
  } 
  return false;
});  


$.validator.addClassRules({ 
      dep_name_class:{
        cRequired:  function(element) {
              if (($('.dep_age_class').length >0) || ($('.dep_income_class').length >0) || ($('.dep_relation_class').length >0)) {
               return true;
                
              } else {
                return false;
              }
            }
          },
        submitHandler: function (form) { // for demo
           // alert('valid form submitted'); // for demo
            return false; // for demo
        }
  
});
$.validator.addMethod("cRequired", $.validator.methods.required,"Please enter name Or remove form");

$.validator.addClassRules({

    dep_age_class:{
        cNumber: true,
        cmaxlength: 8
    },
    submitHandler: function (form) { // for demo
       // alert('valid form submitted'); // for demo
        return false; // for demo
    }


});
$.validator.addMethod("cNumber", $.validator.methods.number,"Please enter valid number.");
$.validator.addMethod("cmaxlength", $.validator.methods.maxlength,"Please enter  maximum 8 digit.");
$.validator.addClassRules({

    dep_income_class:{
        cdNumber: true,
        cdmaxlength: 8
    },
    submitHandler: function (form) { // for demo
       // alert('valid form submitted'); // for demo
        return false; // for demo
    }


});
$.validator.addMethod("cdNumber", $.validator.methods.number,"Please enter valid number.");
$.validator.addMethod("cdmaxlength", $.validator.methods.maxlength,"Please enter  maximum 12 digit.");
$('#associate_register').validate({
      rules: {
          member_id: "required",
          form_no: {
            required: true,
            number : true,
           // checkFormNo:true,
          },
          application_date: {
            required: true,
            date : true,
          },
          current_carder: "required",
          senior_code:  "required",  
          first_g_first_name:"required",
          first_g_Mobile_no:{
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12 
          }, 
          second_g_Mobile_no:{ 
            number: true,
            minlength: 10,
            maxlength: 12 
          },
          first_g_address:"required",
          dep_age:{
            number: true,
            maxlength: 8 
          },
          dep_income:{
            number: true,
            maxlength: 12 
          },
          dep_first_name:{
            required: function(element) {
              if (($( "#dep_income" ).val()!='') || ($( "#dep_age" ).val()!='') || ($( "#dep_relation" ).val()!='')) {
                return true;
              } else {
                return false;
              }
            },
          }
      },
      messages: {
          member_id: "Please enter member id.",
          form_no: {
            required: "Please enter form number.",
            number : "Please enter a valid number.",
          },
          application_date: {
            required: "Please enter application date.",
            date : "Please enter a valid date.",
          },
          current_carder: "Please select carder",
          senior_code: "Please enter Senior code.",
          first_g_first_name:"Please enter  name.",
          first_g_Mobile_no:{
            required: "Please enter mobile number.",
            number: "Please enter valid number.",
            minlength: "Please enter minimum  10 or maximum 12 digit.",
            maxlength: "Please enter minimum  10 or maximum 12 digit."
          }, 
          first_g_address:"Please enter address.",
          first_g_Mobile_no:{ 
            number: "Please enter valid number.",
            minlength: "Please enter minimum  10 or maximum 12 digit.",
            maxlength: "Please enter minimum  10 or maximum 12 digit."
          },
          dep_age:{ 
            number: "Please enter valid number.",
            maxlength: "Please enter  maximum 8 digit."
          },
          dep_income:{ 
            number: "Please enter valid number.",
            maxlength: "Please enter  maximum 12 digit."
          },
          dep_first_name:"Please enter  name.",
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
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
 $(document).on('click','#ssb_account_yes',function(){ 
    var member_id=$('#id').val(); 
    $('#ssb_account_number').val(''); 
    $('#ssb_account_name').val('');
    $('#ssb_account_amount').val('');
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associateSsbAccountGet') !!}",
              dataType: 'JSON',
              data: {'member_id':member_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==1)
                {
                  $('#ssb_account_number').val(response.account_no);
                  $('#ssb_account_name').val(response.name);
                  $('#ssb_account_amount').val(response.balance);
                }
                else
                {
                  swal("Error!", "Member SSB account not found!", "error"); 
                  return false;
                }
              }
          })
  });

    $(document).on('click','#rd_account_yes',function(){
        var member_id=$('#id').val();
        $.ajax({
            type: "POST",
            url: "{!! route('branch.associateRdAccounts') !!}",
            dataType: 'JSON',
            data: {'member_id':member_id},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if ( $.isEmptyObject(response) ) {
                    swal("Error!", "RD account not found!", "error");
                    return false;
                } else {
                    $('#rd_account_detail').show();
                    $('#rd-account-list').html("");
                    var i = 0;
                    $.each(response, function (key, value) {
                        var li = $('<div class="custom-control custom-radio mb-3 "><input type="radio" class="custom-control-input rd-account-get" ' +
                            'name="rd_account_detail" ' +'id="'+ value +'" ' +
                            'value="' + key +
                            '" ' +
                            ' required/>' +
                            '<label class="custom-control-label" for="' + value + '">'+ value +'</label> </div>');
                        // li.find('label').text(value);
                        //$('#rd-account-list').empty();
                        $('#rd-account-list').append(li);
                        if(i==0){
                            $('.rd-account-get').trigger("click");
                            $('#rd_account_yes').prop("checked", true);
                        }

                        i++
                    });
                }
            }
        })
    });

    $('#rd-account-list').on('click','.rd-account-get',function(){

        var rdAccountId = $(this).val();
        $('#rd_account_number').val('');
        $('#rd_account_name').val('');
        $('#rd_account_amount').val('');
        $.ajax({
            type: "POST",
            url: "{!! route('branch.associateRdAccountGet') !!}",
            dataType: 'JSON',
            data: {'rdAccountId':rdAccountId},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log(response, typeof (response));
                if(response.account_id)
                {
                    $('#rd_account_number').val(response.account_id);
                    $('#rd_account_name').val(response.name);
                    $('#rd_account_amount').val(response.amount);
                }
                else
                {
                    swal("Error!", "RD account not found!", "error");
                    return false;
                }
            }
        })
    });

$(document).on('change','#payment_mode',function(){
var val=$('#payment_mode').val();
  $('#payment_mode_cheque').hide();
  $('#payment_mode_online').hide();
  $('#payment_mode_ssb').hide();
  if (val==1) 
  {
    $('#payment_mode_cheque').show();
  }
  if (val==2) 
  {
     $('#payment_mode_online').show();
  }
  if (val==3) 
  {
     $('#payment_mode_ssb').show();

    $('#rd_ssb_account_number').val('');  
    $('#rd_ssb_account_amount').val('');
    var rd_amount=$('#rd_amount').val();
    var member_id=$('#id').val(); 
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associateSsbAccountGet') !!}",
              dataType: 'JSON',
              data: {'member_id':member_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==1)
                {

                  $('#rd_ssb_account_number').val(response.account_no); 
                  $('#rd_ssb_account_amount').val(response.balance);
                  
                  if(rd_amount>response.balance)
                  {
                    swal("Error!", "Your SSB account does not have a sufficient balance.", "error"); 
                    return false;
                  }
                }
                else
                {
                  swal("Error!", "Member SSB account not found!", "error"); 
                  return false;
                }
              }
          })
  }
});

 $(document).on('click','#ssb_account_no',function(){ 
    var member_id=$('#id').val();  
    $('#ssb_accountexists').val(0);
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associateSsbAccountGet') !!}",
              dataType: 'JSON',
              data: {'member_id':member_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==1)
                {
                  $('#ssb_accountexists').val(1);
                  swal("Error!", "Member already exists SSB account!", "error"); 
                  $( "#ssb_account_no" ).prop("checked", false);
                  $( "#ssb_account_yes" ).prop("checked", true);
                   $('#ssb_account_detail').show();
                    $('#ssb_account_form').hide();
                    $('#ssb_account_number').val(response.account_no);
                  $('#ssb_account_name').val(response.name);
                  $('#ssb_account_amount').val(response.balance);
                  return false;
                } 
              }
          })
  });
var re_ssb='';
$.validator.addMethod("check_ssb_account", function(value, element,p) {  
var member_id=$('#id').val();  
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associatessbaccountcheck') !!}",
              dataType: 'JSON',
              data: {'account_no':value,'member_id':member_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==0)
                {
                 re_ssb= false;
                  $.validator.messages.check_ssb_account = "SSB account number wrong";
                  
                }
                else
                {
                  re_ssb= true;
                  $.validator.messages.check_ssb_account = "";
                }
              }
          });
          return re_ssb; 
  }, "");

 $(document).on('keyup','#rd_account_number',function(){
    var account_no = $(this).val();
    var member_id=$('#id').val(); 
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associatessbaccountcheck') !!}",
              dataType: 'JSON',
              data: {'account_no':account_no,'member_id':member_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==0)
                {
                  return false;
                }
              }
          })
  });
 $(document).on('keyup','#ssb_first_percentage',function(){
       
        var val = $('#ssb_first_percentage').val();
        if(val=='')
        {
          $('#ssb_second_percentage').val(0);
        }
        else
        {
          if(val==100)
          {
            $('#second_nominee_ssb').prop("disabled",true) ;
            $('#ssb_second_percentage').val(0);
          }
          else
          {
            $('#second_nominee_ssb').prop("disabled",false) ;
            var otherVal = parseInt(100-parseInt(val));
            $('#ssb_second_percentage').val(otherVal);
          }
        }
    });
 $(document).on('keyup','#ssb_second_percentage',function(){       
        var val = $('#ssb_second_percentage').val(); 
        if(val=='')
        {
            $('#ssb_first_percentage').val(0);
        }
        else
        {
          if(val==100)
          {
            $('#ssb_first_percentage').val(0);
          }
          else
          {
            var otherVal = parseInt(100-parseInt(val));
            $('#ssb_first_percentage').val(otherVal);
          }
        }        
  });
 $(document).on('keyup','#rd_first_percentage',function(){
       
        var val = $('#rd_first_percentage').val();
        if(val=='')
        {
          $('#rd_second_percentage').val(0);
        }
        else
        {
          if(val==100)
          {
            $('#second_nominee_rd').prop("disabled",true) ;
            $('#rd_second_percentage').val(0);
          }
          else
          {
            $('#second_nominee_rd').prop("disabled",false) ;
            var otherVal = parseInt(100-parseInt(val));
            $('#rd_second_percentage').val(otherVal);
          }
        }
    });
 $(document).on('keyup','#rd_second_percentage',function(){       
        var val = $('#rd_second_percentage').val(); 
        if(val=='')
        {
            $('#rd_first_percentage').val(0);
        }
        else
        {
          if(val==100)
          {
            $('#rd_first_percentage').val(0);
          }
          else
          {
            var otherVal = parseInt(100-parseInt(val));
            $('#rd_first_percentage').val(otherVal);
          }
        }        
  }); 
 $.validator.addMethod("check_per_rd", function(value, element,p) {  
      var val1 = $('#rd_first_percentage').val();
      var val2 = $('#rd_second_percentage').val();
      if($('#rd_second_validate').val()>0){ 
      var sum =parseInt(val1)+parseInt(val2);
              if(sum>100)
                {
                  result = false;
                  $.validator.messages.check_per_rd = "RD percentage not greater than 100";
                  
                }
                else
                {
                  result = true;
                  $.validator.messages.check_per_rd = "";
                }
      }
      else
      {
        if(val1!=100)
        {
          result = false;
                  $.validator.messages.check_per_rd = "RD percentage is not less than  or greater than 100";
        }else
                {
                  result = true;
                  $.validator.messages.check_per_rd = "";
                }
      }
    
    return result;
  }, "");
$.validator.addMethod("check_per", function(value, element,p) {  
      var val1 = $('#ssb_first_percentage').val();
      var val2 = $('#ssb_second_percentage').val();
      $.validator.messages.check_per = "";
      var sum1 =parseInt(val1)+parseInt(val2);

      if($('#ssb_second_validate').val()>0){ 
      var sum =parseInt(val1)+parseInt(val2);

              if(sum>100)
                {
                  result = false;
                  $.validator.messages.check_per = "SSB percentage not greater than 100";
                  
                }
                else
                {
                  result = true;
                  $.validator.messages.check_per = "";
                }
          }
      else
      {
        if(val1!=100)
        {
          result = false;
                  $.validator.messages.check_per = "SSB percentage is not less than  or greater than 100";
        }
        else
        {
          result = true;
                  $.validator.messages.check_per = "";
        }
      }
    
    return result;
  }, "");


$.validator.addMethod("check_rd_account", function(value, element,p) {  
var member_id=$('#id').val();  
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.associatessbaccountcheck') !!}",
              dataType: 'JSON',
              data: {'account_no':value,'member_id':member_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==0)
                {
                  return  false;
                  $.validator.messages.check_rd_account = "RD account number wrong";
                  
                }
                else
                {
                  return  true;
                  $.validator.messages.check_rd_account = "";
                }
              }
          }); 
  }, "");


$.validator.addMethod("check_ssb_balance", function(value, element,p) {  
  if(value=='3')
  {
    var member_id=$('#id').val(); 
    var rd_amount=$('#rd_amount').val(); 
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.checkssbblance') !!}",
              dataType: 'JSON',
              data: {'member_id':member_id,'rd_amount':rd_amount},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==0)
                {
                  return  false;
                  $.validator.messages.check_ssb_balance = "Your SSB account does not have a sufficient balance.";
                  
                }
                else  if(response.resCount==2)
                {
                  return  false;
                  $.validator.messages.check_ssb_balance = "Your SSB account not set for auto debit";
                  
                }
                else
                {
                 return  true;
                  $.validator.messages.check_ssb_balance = "";
                }
              }
          });    
  }
  else
  {
    return true;
  } 
  }, "");


$('#associate_register_next').validate({
      rules: {
          ssb_account:"required",
          rd_account:"required",
          ssb_account_number: {
            required: function(element) {
             if ($( "#ssb_account_yes" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            check_ssb_account:true,
          },
          ssb_amount: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
          },
          ssb_first_first_name: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          ssb_first_relation: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          ssb_first_dob: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            date: true,
          },
          ssb_first_percentage: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
            check_per:true,
          }, 
          ssb_first_gender: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          ssb_first_age: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
          },
          ssb_first_mobile_no: {
            required: function(element) {
              if ($( "#ssb_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          ssb_second_first_name: {
            required: function(element) {
              if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          ssb_second_relation: {
            required: function(element) {
              if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          ssb_second_dob: {
            required: function(element) {
             if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            date: true,
          },
          ssb_second_percentage: {
            required: function(element) {
             if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
            check_per:true,
          }, 
          ssb_second_gender: {
            required: function(element) {
              if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
          },
          ssb_second_age: {
            required: function(element) {
              if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
          },
          ssb_second_mobile_no: {
            required: function(element) {
              if (($( "#ssb_account_no" ).prop( "checked")==true) && ($('#ssb_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_account_number: {
            required: function(element) {
              if ($( "#rd_account_yes" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            check_rd_account:true,
          },
          rd_amount: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
          },
          payment_mode: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            check_ssb_balance:true,
          },
          rd_cheque_no: {
            required: function(element) {
              if ($( "#payment_mode" ).val()== 1 && $( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          number: true,
          },
          rd_branch_name: {
            required: function(element) {
              if ($( "#payment_mode" ).val()==1 && $( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_bank_name: {
            required: function(element) {
              if ($( "#payment_mode" ).val()==1 && $( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_cheque_date: {
            required: function(element) {
              if ($( "#payment_mode" ).val()==1 && $( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            date: true,
          },
          rd_online_id: {
            required: function(element) {
              if ($( "#payment_mode" ).val()==2 && $( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_online_date: {
            required: function(element) {
              if ($( "#payment_mode" ).val()==2 && $( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            date: true,
          },

          rd_first_first_name: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          rd_first_relation: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          rd_first_dob: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            date: true,
          },
          rd_first_percentage: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
            check_per_rd:true,
          }, 
          rd_first_gender: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_first_age: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_first_mobile_no: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_second_first_name: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          rd_second_relation: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            }, 
          },
          rd_second_dob: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            date: true,
          },
          rd_second_percentage: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
            check_per_rd:true,
          }, 
          rd_second_gender: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
          },
          rd_second_age: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
            number: true,
          },
         /* rd_account: {
              required: function(element) {
                  if ($( "#rd_account_yes" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                      return true;
                  } else {
                      return false;
                  }
              },
          },*/
          rd_account_number: {
              required: function(element) {
                  if ($( "#rd_account_yes" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                      return true;
                  } else {
                      return false;
                  }
              },
          },
          rd_second_mobile_no: {
            required: function(element) {
              if ($( "#rd_account_no" ).prop( "checked")==true && ($('#rd_second_validate').val()==1)) {
                return true;
              } else {
                return false;
              }
            },
          },
          

      },
      messages: {
          ssb_account:"Please select  SSB account option.",
          rd_account:"Please select  RD account option.",
          ssb_account_number:{
            required: "Please enter SSB account no.", 
            },
          ssb_amount: {
            required: "Please enter amount.",
            number : "Please enter a valid number.",
          },
          ssb_first_first_name:"Please enter  name.",
          ssb_first_relation:"Please enter relation.", 
          ssb_first_dob: {
            required: "Please select date.",
            number : "Please enter a valid date.",
          },
          ssb_first_percentage: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          }, 
          ssb_first_gender:"Please select gender.", 
          ssb_first_age: {
            required: "Please enter age.",
            number : "Please enter a valid number.",
          },
          ssb_first_mobile_no: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          },
          ssb_second_first_name:"Please enter  name.",
          ssb_second_relation:"Please enter relation.", 
          ssb_second_dob: {
            required: "Please select date.",
            number : "Please enter a valid date.",
          },
          ssb_second_percentage: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          }, 
          ssb_second_gender:"Please select gender.", 
          ssb_second_age: {
            required: "Please enter age.",
            number : "Please enter a valid number.",
          },
          ssb_second_mobile_no: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          },

          rd_account_number:{
            required: "Please enter RD account no.", 
            },
          rd_amount: {
            required: "Please enter amount.",
            number : "Please enter a valid number.",
          }, 

          rd_cheque_no:"Please select payment mode.",
          rd_cheque_no: {
              required: "Please enter cheque number.",
              number : "Please enter a only number.",
          },

          rd_branch_name:"Please enter branch name.",
          rd_bank_name:"Please enter bank name.",
          rd_cheque_date: {
            required: "Please select date.",
            date : "Please enter a valid date.",
          },
          rd_online_id:"Please enter transaction id.",
          rd_online_date: {
            required: "Please select date.",
            date : "Please enter a valid date.",
          },

          rd_first_first_name:"Please enter  name.",
          rd_first_relation:"Please enter relation.", 
          rd_first_dob: {
            required: "Please select date.",
            number : "Please enter a valid date.",
          },
          rd_first_percentage: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          }, 
          rd_first_gender:"Please select gender.", 
          rd_first_age: {
            required: "Please enter age.",
            number : "Please enter a valid number.",
          },
          rd_first_mobile_no: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          },
          rd_second_first_name:"Please enter  name.",
          rd_second_relation:"Please enter relation.", 
          rd_second_dob: {
            required: "Please select date.",
            number : "Please enter a valid date.",
          },
          rd_second_percentage: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          }, 
          rd_second_gender:"Please select gender.", 
          rd_second_age: {
            required: "Please enter age.",
            number : "Please enter a valid number.",
          },
          rd_second_mobile_no: {
            required: "Please enter percentage.",
            number : "Please enter a valid number.",
          },
          
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){ 
            $(this).removeClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid'); 
      }
  });

$(document).on('click','#add_dependents',function(){
   var count = $('input[name*="dep_first_name[]"]').length;
   alert(count);
  });
$(document).on('click','input[name="ssb_account"]',function(){
$('#ssb_account_number').val(''); 
    $('#ssb_account_name').val('');
    $('#ssb_account_amount').val('');
    $('#ssb_accountexists').val(0);
  if ($( "#ssb_account_yes" ).prop( "checked")==true) 
  {
    $('#ssb_account_detail').show();
    $('#ssb_account_form').hide();

  }
  if ($( "#ssb_account_no" ).prop( "checked")==true) 
  {
    $('#ssb_account_detail').hide();
    $('#ssb_account_form').show();
  }
});


    $(document).on('click','#rd_account_yes',function(){
            $('#rd_account_form').hide();
            $('#rd_account_detail').show();
    });
    $(document).on('click','#rd_account_no',function(){
        $('#rd_account_detail').hide();
        $('#rd-account-list').html("");
        rdmaturity();
        $('#rd_account_form').show();
    });

   


      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

});

function printDiv(elem) {
    printJS({
    printable: elem,
    type: 'html',
    targetStyles: ['*'], 
  })
}
var a=0;
$("#btnAdd").on("click", function() {
      
    var div = jQuery("<div  class='row remove_div' />");

     div.html(GetDynamicTextBox(""));
     $("#add_dependent").append(div);
     
 });
    
 $("body").on("click", ".remove", function() {
     
         $(this).closest('.remove_div').remove();
     
});   

function GetDynamicTextBox(value) {
   a++;
   id=a;
     return '<div class="col-lg-12"> <div class="form-group row"> <div class="col-lg-12">  <button type="button" class="btn btn-primary remove" >Remove</button>    </div> </div> </div><div class="col-lg-6"> <div class="form-group row"> <label class="col-form-label col-lg-4">Full Name</label>  <div class="col-lg-8 error-msg">  <input type="text" name="dep_first_name1['+id+']" id="dep_first_name'+id+'" class="form-control dep_name_class"  > </div>  </div> <div class="form-group row"> <label class="col-form-label col-lg-4">Age</label> <div class="col-lg-8 error-msg"> <input type="text" name="dep_age1['+id+']" id="dep_age'+id+'" class="form-control dep_age_class"  > </div> </div> <div class="form-group row">  <label class="col-form-label col-lg-4">Relation</label> <div class="col-lg-8 error-msg">  <select name="dep_relation1['+id+']" id="dep_relation'+id+'" class="form-control dep_relation_class"  > <option value="">Select Relation</option> @foreach ($relations as $val)  <option value="{{ $val->id }}">{{ $val->name }}</option>  @endforeach  </select> </div>  </div> <div class="form-group row"> <label class="col-form-label col-lg-4">Per month income</label> <div class="col-lg-8 error-msg"> <input type="text" name="dep_income1['+id+']" id="dep_income'+id+'" class="form-control dep_income_class"  > </div> </div> </div> <div class="col-lg-6"> <div class="form-group row">  <label class="col-form-label col-lg-4">Gender</label>  <div class="col-lg-8 error-msg">  <div class="row"> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3 ">  <input type="radio" id="dep_gender_male'+id+'" name="dep_gender1['+id+']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_gender_male'+id+'">Male</label> </div>  </div> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="dep_gender_female'+id+'" name="dep_gender1['+id+']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_gender_female'+id+'">Female</label> </div> </div>  </div>  </div>   </div> <div class="form-group row"> <label class="col-form-label col-lg-4">Marital status</label> <div class="col-lg-8 error-msg"> <div class="row"> <div class="col-lg-4">  <div class="custom-control custom-radio mb-3 ">  <input type="radio" id="dep_married'+id+'" name="dep_marital_status1['+id+']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_married'+id+'">Married</label> </div> </div> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3  "> <input type="radio" id="dep_unmarried'+id+'" name="dep_marital_status1['+id+']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_unmarried'+id+'">Un Married</label> </div> </div> </div> </div>  </div>  <div class="form-group row"> <label class="col-form-label col-lg-4">Living with Associate</label>  <div class="col-lg-8 error-msg"> <div class="row"> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3 "> <input type="radio" id="dep_living_yes'+id+'" name="dep_living1['+id+']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_living_yes'+id+'">Yes</label>  </div>  </div> <div class="col-lg-4">  <div class="custom-control custom-radio mb-3  ">  <input type="radio" id="dep_living_no'+id+'" name="dep_living1['+id+']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_living_no'+id+'">No</label> </div> </div> </div>  </div>  </div>  <div class="form-group row">  <label class="col-form-label col-lg-4">Dependent Type</label>  <div class="col-lg-8 error-msg"> <div class="row"> <div class="col-lg-4"> <div class="custom-control custom-radio mb-3 "> <input type="radio" id="dep_type_fully'+id+'" name="dep_type1['+id+']" class="custom-control-input" value="1"> <label class="custom-control-label" for="dep_type_fully'+id+'">Fully</label>  </div> </div> <div class="col-lg-4">  <div class="custom-control custom-radio mb-3  "> <input type="radio" id="dep_type_partially'+id+'" name="dep_type1['+id+']" class="custom-control-input" value="0" checked="checked"> <label class="custom-control-label" for="dep_type_partially'+id+'">Partially</label> </div>  </div> </div> </div>  </div> </div>';
     }

 function rdmaturity()
{

  var tenure = $( "#tenure" ).val();
        var principal = $('#rd_amount').val(); 
        var time = tenure;
        if(time >= 0 && time <= 36){
          var rate = 8.50;
        }else if(time >= 37 && time <= 60){
          var rate = 9.50; 
        }else if(time >= 61 && time <= 84){ 
          var rate = 10.50; 
        }
        else
        {
          var rate = 8.50;           
        }
        console.log("rate RD", rate);
        var ci = 1;
        var irate = rate / ci;
        var year = time / 12;

        var freq = 4; 

        var maturity=0;
        for(var i=1; i<=time;i++){
            maturity+=principal*Math.pow((1+((rate/100)/freq)), freq*((time-i+1)/12));
        } 
        var result = maturity; 
        if(Math.round(result) > 0 && tenure <= 84){
            $('#maturity').html('Maturity Amount :'+Math.round(result));
            $('#rd_amount_maturity').val(Math.round(result));
            $('#rd_rate').val(rate);
        }else{
            $('#maturity').html('');
            $('#rd_amount_maturity').val('');
            $('#rd_rate').val('');
        }

 }
</script>