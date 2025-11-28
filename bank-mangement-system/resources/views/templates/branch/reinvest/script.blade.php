
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>

<script type="text/javascript">
$(document).ready(function () {
  var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  $('#dob').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true,
  endDate: today, 
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#age').val(age);
    $('#age_display').text(age+' Years');
    $('.datepicker-dropdown').hide();



  });
  $('#nominee_dob').datepicker({
  format: "mm/dd/yyyy",
  todayHighlight: true, 
  endDate: ntoday,
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#nominee_age').val(age);
    $('#nominee_age_display').text(age+' Years');
    $('.datepicker-dropdown').hide();
    $( "#is_minor" ).prop( "checked",false)
    $('#nominee_parent_detail').hide()
    if(age>=18)
    {
      $('#minor_hide').hide();      
    }
    else
    {
      $('#minor_hide').show();
    }

  });


  $('#anniversary_date').datepicker({
    format: "mm/dd/yyyy",
    todayHighlight: true,  
    autoclose: true
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

  $(document).on('click','#is_minor',function(){
    if ($( "#is_minor" ).prop( "checked")==true) {
      $('#nominee_parent_detail').show()
    } else {
      $('#nominee_parent_detail').hide()
    }
  });
  $(document).on('click','#first_same_as',function(){
    if ($( "#first_same_as" ).prop( "checked")==true) {  
      $('#first_address_proof').val($('#address').val());     
    } else {
      $('#first_address_proof').val('');

    }
    $('#first_address_proof').trigger('keypress');
     $('#first_address_proof').trigger('keyup');

  });
  $(document).on('click','#second_same_as',function(){
    if ($( "#second_same_as" ).prop( "checked")==true) {  
      $('#second_address_proof').val($('#address').val());  
    } else {
      $('#second_address_proof').val(''); 
     
    }
    $('#second_address_proof').trigger('keypress');
    $('#second_address_proof').trigger('keyup');

  });

  $(document).on('click','.m-status',function(){
    if ($(this).val()=='1') {  
      $('.anniversary-date-box').show();  
    } else {
      $('.anniversary-date-box').hide();  
    }
  });

  
  $(document).on('click','#associate_admin',function(){
    if ($( "#associate_admin" ).prop( "checked")==true) {  
      $('#associate_code').val(0);  
      $('#associate_id').val(0); 
      $('#associate_name').val('Super Admin'); 
    } else {
      $('#associate_code').val('');  
      $('#associate_id').val(''); 
      $('#associate_name').val('');  
     
    }
    

  });

  $(document).on('change','#first_id_type',function(){
    if($(this).val()==1)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
    }
    else if($(this).val()==2)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter proper driving licence number. For eg:- HR-0619850034761 Or UP14 20160034761');
    }
    else if($(this).val()==3)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
    }
    else if($(this).val()==4)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter proper passport number. For eg:- A1234567');
    }
    else if($(this).val()==5)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter proper pan card number. For eg:- ASDFG9999G');
    }
    else if($(this).val()==6)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter id proof number');
    }
    else if($(this).val()==7)
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter only digits. For eg:- 2345456567');
    }
    else
    {
      $('#first_id_tooltip').attr('data-original-title', 'Enter id proof number');
    }

  });
  $(document).on('change','#second_id_type',function(){
    if($(this).val()==1)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
    }
    else if($(this).val()==2)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter proper driving licence number. For eg:- MJ-23456789078656');
    }
    else if($(this).val()==3)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter proper aadhar card number. For eg:- 897898769876(12 or 16 digit number)');
    }
    else if($(this).val()==4)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter proper passport number. For eg:- A1234567');
    }
    else if($(this).val()==5)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter proper pan card number. For eg:- ASDFG9999G');
    }
    else if($(this).val()==6)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number');
    }
    else if($(this).val()==7)
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number. For eg:- 2345678909');
    }
    else
    {
      $('#second_id_tooltip').attr('data-original-title', 'Enter id proof number');
    }

  });

  $(document).on('keyup','#associate_code',function(){
      $('#associate_name').val('');
       $('#associate_carder').val('');
    $('#associate_msg').text('');
    var code = $(this).val();
    if(code!='')
    {
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.getassociatemember') !!}",
              dataType: 'JSON',
              data: {'code':code},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 

                if(response.resCount>0)
                {
                  $.each(response.data, function (index, value) { 
                    // alert(value.first_name);
                        $('#associate_name').val(value.first_name+' '+value.last_name);
                        $('#associate_id').val(value.id);
                        if(value.member_id=='9999999')
                        {
                          $('#hide_carder').hide();
                        }
                        else
                        {
                          $('#hide_carder').show();
                          $('#associate_carder').val(response.carder);
                        }
                        
                        
                    });
                }
                else
                {
                  $('#associate_name').val(''); 
                  $('#associate_msg').text('No match found');
                  $('.invalid-feedback').show();
                }
                $('#associate_name').trigger('keypress');
                $('#associate_name').trigger('keyup');
              }
          });
  }
    
  });
  $(document).on('keyup','#email',function(){
    var code = $(this).val();
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.memberemailcheck') !!}",
              dataType: 'JSON',
              data: {'email':code},
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
$(document).on('keyup','#form_no',function(){
    var form_no = $(this).val();
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.memberformnocheck') !!}",
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


  
  $.validator.addMethod("checkEmail", function(value, element,p) {
    //result = false;
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.memberemailcheck') !!}",
              dataType: 'JSON',
              data: {'email':value},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.resCount==0)
                {
                  result = true;
                  $.validator.messages.checkEmail = "";
                }
                else
                {
                  result = false;
                  $.validator.messages.checkEmail = "Email id already exists";
                }
              }
          });


    return result;
  }, "");
  $.validator.addMethod("checkFormNo", function(value, element,p) {
  //  result = false;
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.memberformnocheck') !!}",
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
                  $.validator.messages.checkFormNo = "Form No already exists";
                }
              }
          });


    return result;
  }, "");


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
    else if($(p).val()==7)
    {
      if(this.optional(element) || /^(\d{8,14})$/.test(value)==true)
      {
        result = true;
      }else{
        $.validator.messages.checkIdNumber = "Please enter valid bill no";
        result = false;  
      }    
    }
    else{
      $.validator.messages.checkIdNumber = "Please enter ID Number";
      result = false; 
    }
    return result;
  }, "");
  $('#member_register').validate({
      rules: {
          photo:{
           // required: true,
            extension: "jpg|jpeg|png|pdf"
          },
          signature:{
           // required: true,
            extension: "jpg|jpeg|png|pdf"
          },
          form_no: {
            required: true,
            number : true,
            //checkFormNo:true,
          },
          application_date: {
            required: true,
            date : true,
          },  
          first_name: "required", 
          //last_name: "required",
          email: {
           // required: true,
            email :  function(element) {
              if ($( "#email" ).val()!='') {
                return true;
              } else {
                return false;
              }
            },
            checkEmail:function(element) {
              if ($( "#email" ).val()!='') {
                return true;
              } else {
                return false;
              }
            }
          },
          mobile_no: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12 
          },
          dob: {
            required: true, 
            date : true,
          },
          gender: "required",
    //      occupation: "required", 
          annual_income: {
            required: true,
            number: true,
          },
   //       mother_name: "required",
          f_h_name: "required",
          bank_account_no: { 
            number: true,
          },
   /*       marital_status: "required",
          bank_name: "required",
          bank_branch_name: "required",
          bank_account_no: "required",
          bank_ifsc: "required",
          bank_branch_address: "required",
    */
          nominee_first_name: "required",
         // nominee_last_name: "required",
     /*     nominee_relation: "required",
          nominee_gender: "required",
          nominee_dob:  {
            required: true, 
            date : true,
          },
      */
          nominee_mobile_no:  {
            required: true, 
            number : true,
            minlength: 10,
            maxlength: 12 
          },
          parent_nominee_name: {
            required: function(element) {
              if ($( "#is_minor" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
          },
          parent_nominee_mobile_no: {
            required: function(element) {
              if ($( "#is_minor" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number : true,
          },
          parent_nominee_mobile_age: {
            required: function(element) {
              if ($( "#is_minor" ).prop( "checked")==true) {
                return true;
              } else {
                return false;
              }
            },
            number : true,
          },
          address: "required",
          state_id: "required",
          city_id: "required",
          district_id: "required",
          pincode:{
            required: true, 
            number : true,
            minlength: 6,
            maxlength: 6 
          },
          first_id_type: "required",
          first_id_proof_no: {
            required: true, 
            checkIdNumber : '#first_id_type',
          },
          first_address_proof: {
            required: function(element) {
              if ($( "#first_same_as" ).prop( "checked")==false) {
                return true;
              } else {
                return false;
              }
            },
          },
          second_id_type: "required",
          second_id_proof_no: {
            required: true, 
            checkIdNumber : '#second_id_type',
          },
          second_address_proof: {
            required: function(element) {
              if ($( "#second_same_as" ).prop( "checked")==false) {
                return true;
              } else {
                return false;
              }
            },
          },
          associate_code: "required",
          associate_name:"required",

      },
      messages: {
          photo:{
            required: 'Please select photo.',
            extension: "Accept only png,jpg or pdf files."
          },
          signature:{
            required: 'Please select signature.',
            extension: "Accept only png,jpg or pdf files."
          },          
          form_no: {
            required: "Please enter form number.",
            number : "Please enter a valid number.",
          },
          application_date: {
            required: "Please enter application date.",
            number : "Please enter a valid date.",
          }, 
          first_name: {
            required: "Please enter first name.",
          },
          last_name: {
            required: "Please enter last name.",
          },
          email: {
            required: "Please enter email id.",
            email : "Please enter valid email id.",
          },
          mobile_no: {
            required: "Please enter mobile number.",
            number: "Please enter valid number.",
            minlength: "Please enter minimum  10 or maximum 12 digit.",
            maxlength: "Please enter minimum  10 or maximum 12 digit." 
          },
          dob: {
            required: "Please enter date of birth date.",
            date : "Please enter valid date.",
          },
          gender: "Please select gender.",
          occupation: "Please select occupation.", 
          annual_income: {
            required:"Please enter annual income.",
            number: "Please enter valid number.",
          },
          mother_name: "Please enter mother name.",
          f_h_name: "Please enter father/husband name.",
          bank_account_no: { 
            number: "Please enter valid number.",
          },
      /*    marital_status: "Please select marital status.",
          bank_name: "Please enter bank name.",
          bank_branch_name: "Please enter branch name.",
          bank_account_no: "Please enter account number.",
          bank_ifsc: "Please enter IFSC code.",
          bank_branch_address: "Please enter address.",
      */
          nominee_first_name: "Please enter nominee name.",
      /*    nominee_last_name: "Please enter nominee last name.",
          nominee_relation: "Please enter nominee relation.",
          nominee_gender: "Please select nominee gender.",
          nominee_dob:  {
            required: "Please enter nominee date of birth.", 
            date : "Please enter valid date.",
          },

        */
          nominee_mobile_no:  {
            required: "Please enter nominee mobile number.",
            number: "Please enter valid number.",
            minlength: "Please enter minimum  10 or maximum 12 digit.",
            maxlength: "Please enter minimum  10 or maximum 12 digit." 
          },
          parent_nominee_name: {
            required: "Please enter nominee parent name.",
          },
          parent_nominee_mobile_no: {
            required: "Please enter nominee parent name.",
            number : "Please enter valid number.",
          },
          parent_nominee_mobile_age: {
            required: "Please enter nominee parent age.",
            number : "Please enter valid number.",
          },
          address: "Please enter address.",
          state_id: "Please select state.",
          city_id: "Please  select city.",
          district_id: "Please select district.",
          pincode:  {
            required: "Please enter pincode.",
            number: "Please enter valid number.",
            minlength: "Please enter minimum  6 or maximum 6 digit.",
            maxlength: "Please enter minimum  6 or maximum 6 digit." 
          },
          first_id_type: "Please select id type.",
          first_id_proof_no: {
            required: "Please enter id number.",  
          },
          first_address_proof: {
            required: "Please enter address.",
          },
          second_id_type: "Please select id type.",
          second_id_proof_no: {
            required: "Please enter id number.",  
          },
          second_address_proof: {
            required: "Please enter address.",
          },
          associate_code: "Please enter associate code.",
          associate_name: "Please enter associate name.",
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      },
      focusInvalid: false,
      invalidHandler: function(form, validator) {
          if (!validator.numberOfInvalids())
              return;

          $('html, body').animate({
              scrollTop: $(validator.errorList[0].element).offset().top
          }, 1000);
      }
  });
  $(document).on('change','#state_id',function(){
          var state_id = $(this).val(); 
          $.ajax({
              type: "POST",  
              url: "{!! route('branch.districtlist') !!}",
              dataType: 'JSON',
              data: {'state_id':state_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {  
                $('#district_id').find('option').remove();
                $('#district_id').append('<option value="">Select district</option>');
                 $.each(response.district, function (index, value) { 
                        $("#district_id").append("<option value='"+value.id+"'>"+value.name+"</option>");
                    });

              }
          }); 

          $.ajax({
              type: "POST",  
              url: "{!! route('branch.citylist') !!}",
              dataType: 'JSON',
              data: {'district_id':state_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#city_id').find('option').remove();
                $('#city_id').append('<option value="">Select city</option>');
                 $.each(response.city, function (index, value) { 
                        $("#city_id").append("<option value='"+value.id+"'>"+value.name+"</option>");
                    }); 

              }
          });

  });
    
        $( document ).ajaxStart(function() {
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

    $(document).on('change','#photo',function(){
        $("#upload_form").submit();
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#photo-preview').attr('src', e.target.result);
                $('#photo-preview').attr('style', 'width:200px; height:200px;');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    $(document).on('change','#signature',function(){
        $("#signature_form").submit();
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#signature-preview').attr('src', e.target.result);
                $('#signature-preview').attr('style', 'width:100%;');
            }

            reader.readAsDataURL(this.files[0]);
        }
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