


<script type="text/javascript">
$(document).ready(function () {

  $('#bank_account_no, #cbank_account_no').on('cut copy paste', function(e) {
      e.preventDefault();
  });
  $('#minor_hide').hide();
  $("#state_id").select2();
  $("#district_id").select2();
  $("#city_id").select2();
  var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var hundred_years = 36525;
  var lastday = new Date(date.setDate(date.getDate() - hundred_years));
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  $('#dob').datepicker({
  format: "dd/mm/yyyy",
  todayHighlight: true,
  endDate:today, 
  startDate:lastday,
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#age').val(age);
    $('#age_display').text(age+' Years');
    $('.datepicker-dropdown').hide();



  });
  $('#nominee_dob').hover(function(){  
    const lastdaya = $('.create_application_date').val();
    $('#nominee_dob').datepicker({
  format: "dd/mm/yyyy",
  todayHighlight: true, 
  startDate:lastday,
  endDate: lastdaya,
  autoclose: true
  }).on('change', function(){

    var age = getAge(this);
    $('#nominee_age').val(age);
    $('#nominee_age_display').text(age+' Years');
    $('.datepicker-dropdown').hide();
    $('#nominee_parent_detail').hide()
    if(age>=18)
    {
      $('#minor_hide').hide();      
    }
    else
    {
      $( "#is_minor" ).prop( "checked",true)
      $('#nominee_parent_detail').show()
      $('#minor_hide').show();
    }

  })
 });

  $(document).on('select2:select','#branch_id',function(){
      var bId = $('option:selected', this).attr('data-val');
      var sbId = $( "#hbranchid option:selected" ).val();  
      if(bId != sbId){
        $('#branch_id').val('');
        $('#branch_id').trigger('change');
        swal("Warning!", "Branch does not match from top dropdown state", "warning");
      }  
  });


  $('#anniversary_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true, 
	startDate:lastday,
    autoclose: true
  });
  function getAge(dateVal) {

    moment.defaultFormat = "DD/MM/YYYY HH:mm"; 

    var birthday = moment(''+dateVal.value+' 00:00', moment.defaultFormat).toDate(),
    today = new Date();  
    var year = today.getYear() - birthday.getYear();
    var m = today.getMonth() - birthday.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthday.getDate())) {
        year--;
    } 
    return Math.floor(year);
  }

  // $(document).ready(function(){
  //   if ($( "#is_minor" ).prop( "checked")==true) {
  //     $('#nominee_parent_detail').show()
  //   } else {
  //     $('#nominee_parent_detail').hide()
  //   }
  // });
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
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
  $(document).on('change','#first_id_type',function(){
      console.log( "MM", $(this).val() );
	  
	if($(this).val()==5)
    {  
	  $(".table-section-onlypancard").removeClass("hideTableData");  
	} 
	else
	{
	  $(".table-section-onlypancard").addClass("hideTableData");
	} 
	  
    if($(this).val()==1)
    {
      //$('#first_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
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
      //$('#second_id_tooltip').attr('data-original-title', 'Enter proper voter id number. For eg:- ABE1234566');
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
    if ( $(this).val() == $('#first_id_type').val() ) {
        console.log("Id Prof", $(this).val(), $('#first_id_type').val());
        $('#second_id_proof_no').val( $('#first_id_proof_no').val() );
        $('#second_id_proof_no').attr('readonly', 'true'); 
    } else {
        $('#second_id_proof_no').val('');
        $('#second_id_proof_no').removeAttr('readonly'); 
    }
  });
 $(document).on('change','#nominee_relation',function(){
 
     var ids = new Array();
     var ids = ['2','3','6','7','8','10','11','8','16','14'];
    $('#nominee_gender_male').removeAttr('checked');
    $('#nominee_gender_male').removeAttr('readonly');
    $('#nominee_gender_female').removeAttr('readonly');
    $('#nominee_gender_female').removeAttr('checked');
    $('#nominee_gender_male').attr('disabled', false);
    $('#nominee_gender_female').attr('disabled', false);

     if ( ids.includes( $(this).val() ) ) {
         $('#nominee_gender_male').attr('disabled', true);
         $('#nominee_gender_female').attr('checked', 'true');
         $('#nominee_gender_female').attr('readonly', 'true');
     } else { 
         $('#nominee_gender_female').attr('disabled', true);
         $('#nominee_gender_male').attr('checked', 'true');
         $('#nominee_gender_male').attr('readonly', 'true');

         
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
              url: "{!! route('admin.associatemember') !!}",
              dataType: 'JSON',
              data: {'code':code},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#associate_msg').text('');
                if(response.resCount>0)
                {
                  if(response.msg=='block')
                  {
                    $('#senior_name').val('');
                     $('#senior_id').val('');
                    $('#associate_msg').text('Associate Blocked.');
                    $('.invalid-feedback').show();
                  }
                  else
                  {
                  if(response.msg=='InactiveAssociate')
                  {
                    $('#associate_name').val(''); 
                    $('#associate_id').val('');
                    $('#associate_msg').text('Associate Inactive.');
                    $('.invalid-feedback').show();
                  } 
                  else
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
              }
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
              url: "{!! route('admin.emailcheck') !!}",
              dataType: 'JSON',
              data: {'email':code,'id':0},
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

  $.validator.addMethod("checkIdNumber", function(value, element,p) {
    if($(p).val()==1)
    {
      result = true;
      // if(this.optional(element) || /^([a-zA-Z]){3}([0-9]){7}?$/g.test(value)==true)
      // {
      //   result = true;
      // }else{
      //   $.validator.messages.checkIdNumber = "Please enter valid voter id number";
      //   result = false;  
      // }
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

  $.validator.addMethod("dateDdMm", function(value, element,p) {
     
      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
      {
        $.validator.messages.dateDdMm = "";
        result = true;
      }else{
        $.validator.messages.dateDdMm = "Please enter valid date";
        result = false;  
      }
    
    return result;
  }, "");


  $('#member_register').validate({
      rules: {
          photo:{
            extension: "jpg|jpeg|png|pdf"
          },
          signature:{
            extension: "jpg|jpeg|png|pdf"
          },
          form_no: {
            required: true,
            number : true,
          },
          application_date: {
            required: true,
            dateDdMm : true,
          },  
          first_name: {
            required:true,
            
          },  
          email: {
            email :  function(element) {
              if ($( "#email" ).val()!='') {
                return true;
              } else {
                return false;
              }
            }, 
          },
          anniversary_date: {
              required :  function(element) {
                  if ($( "#married" ).val() == 1) {
                      return true;
                  } else {
                      return false;
                  }
              },
          },
          bank_ifsc:{
            checkIfsc:true,
          },
          nominee_relation:{
              required: true,
          },
          mobile_no: {
            required: true,
            number: true,
            minlength: 10,
            maxlength: 12 
          },
          dob: {
            required: true, 
            dateDdMm : true,
          },
          gender: "required",
          // annual_income: {
          //   required: true,
          //   number: true,
          //   maxlength: 12
          // },
          f_h_name:{
            required:true,
            
          },
          cbank_account_no: {
              number: true,
              minlength: 9,
              maxlength: 18,
              equalTo: "#bank_account_no"
          },
		      marital_status: "required",
          bank_account_no: { 
            number: true,
            minlength: 9,
            maxlength: 18 
          },
          nominee_gender_male: "required",
          nominee_dob: {
              dateDdMm : true,
          },
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
            minlength: 10,
            maxlength: 12 
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
           associate_id:"required",
          branch_id: "required",

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
          cbank_account_no: {
              equalTo: "Bank A/C and confirm bank A/C must be same",
          },
          application_date: {
            required: "Please enter application date.",
            date : "Please enter a valid date.",
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
            required: "Please enter date of birth",
            date : "Please enter valid date.",
          },
          gender: "Please select gender.",
		       marital_status: "Please select marital status",
          occupation: "Please select occupation.", 
          // annual_income: {
          //   required:"Please enter annual income.",
          //   number: "Please enter valid number.",
          // },
          mother_name: {
            required:"Please enter Mother Name.",
          },
          f_h_name:{
            required:"Please enter Father Name.",
          },
          bank_account_no: { 
            number: "Please enter valid number.",
          },    
          nominee_first_name:{
            required: "Please enter nominee name.",
          },
          nominee_relation: "Please select relation.",
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
          branch_id: "Please select branch.",
      },
      errorElement: 'label',
      errorPlacement: function (error, element) {
        error.addClass(' ');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
  });

  $(document).on('change','#state_id',function(){
          var state_id = $(this).val(); 
          $.ajax({
              type: "POST",  
              url: "{!! route('admin.districtlists') !!}",
              dataType: 'JSON',
              data: {'state_id':state_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) {  
                $('#district_id').find('option').remove();
                $('#district_id').append('<option value="">Select district</option>');
                 $.each(response.district, function (index, value) { 
                        $("#district_id").append("<option value='"+value.id+"'>"+(value.name).toUpperCase()+"</option>");
                    });

              }
          }); 

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.citylists') !!}",
              dataType: 'JSON',
              data: {'district_id':state_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#city_id').find('option').remove();
                $('#city_id').append('<option value="">Select city</option>');
                 $.each(response.city, function (index, value) { 
                        $("#city_id").append("<option value='"+value.id+"'>"+value.name.toUpperCase()+"</option>");
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

    $(document).on('change','#first_id_proof_no, #second_id_proof_no',function() {
        var Currentsids = $(this).attr('id');
        if(Currentsids == "first_id_proof_no"){
        ///	$("#first_same_as").attr( 'checked', true );
        }
        if(Currentsids == "second_id_proof_no"){
        //	$("#second_same_as").attr( 'checked', true );
        }
        var first_id_type = $('#first_id_type').val();
        var id_proof_no = $(this).val();
        var id = $(this).attr('id');
        var second_id_type = $('#second_id_type').val();
        var second_id_proof_no = $('#second_id_proof_no').val();
        $('span#errormessage').html("");
        $.ajax({
            type: "POST",
            url: "{!! route('admin.checkIdProof') !!}",
            dataType: 'JSON',
            data: {'id_proof_no': id_proof_no ,'first_id_type':first_id_type,'second_id_type':second_id_type,'second_id_proof_no':second_id_proof_no},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $( ".loader" ).hide();
                if(response.msg =="exists"){
                  if(response.first_id_type =='firstidtypechecked' && response.second_id_type ==''){
                    $('#first_id_proof_no').after("<span class='error' id='errormessage'>Already exists</span>");
                  }
                  else if(response.first_id_type =='firstidtypechecked' && response.second_id_type =='secondidtypechecked'){
                    $('#first_id_proof_no').after("<span class='error' id='errormessage'>Already exists</span>");
                    $('#second_id_proof_no').after("<span class='error' id='errormessage'>Already exists</span>");
                  }
                  else if(response.second_id_type =='secondidtypechecked' && response.first_id_type ==''){
                    $('#second_id_proof_no').after("<span class='error' id='errormessage'>Already exists</span>");
                  }
                  
                  // console.log(response);
                 
                }
                else{
                  $('span#errormessage').empty();
                }
                var errorId = '#'+id;
                var lableErrorId = id+'-error';
                // if ( response ) {
                //     $(errorId).val('');
                //     $(errorId).addClass('is-invalid');
                //     $(errorId).parent().find("label").remove();
                //     $(errorId).after('<label id="'+ lableErrorId + '" class="error" for="'+ id + '" style="">This document already assign to ' + response + '</label>');
                // } else {
                //     $(errorId).removeClass('is-invalid');
                //     $(errorId).parent().find("label#"+lableErrorId).remove();
                // }
            }
        });
    });

});

function printDiv(elem) {
   /* printJS({
    printable: elem,
    type: 'html',
    targetStyles: ['*'], 
  })*/

    $("#"+elem).print({
        //Use Global styles
        globalStyles : true,
        //Add link with attrbute media=print
        mediaPrint : true,
        //Custom stylesheet
        stylesheet : "{{url('/')}}/asset/print.css",
        //Print in a hidden iframe
        iframe : false,
        //Don't print this
        noPrintSelector : ".avoid-this",
        //Add this at top
        //     prepend : "<span class='tran_account_number' style='padding-left: 40px;line-height: 50px;'>A/C No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+no+"</span>",
        //Add this on bottom
        // append : "<span><br/>Buh Bye!</span>",
        header: null,               // prefix to html
        footer: null,
        //Log to console when printing is done via a deffered callback
        deferred: $.Deferred().done(function() { console.log('Printing done', arguments);})
    });

}


 
</script>