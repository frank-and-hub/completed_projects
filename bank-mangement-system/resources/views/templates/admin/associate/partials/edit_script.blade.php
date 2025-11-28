<script type="text/javascript">
$(document).ready(function () {
  sss(); 
  var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

 /* $('#application_date').datepicker({
  format: "dd/mm/yyyy",
  todayHighlight: true, 
  endDate: date, 
  autoclose: true
  });
 */

 
 jQuery.validator.addMethod("lettersonly", function(value, element) {
      return this.optional(element) || /^[a-z]+$/i.test(value);
    }, "Letters only please");

    jQuery.validator.addMethod("numberonly", function(value, element) {
      return this.optional(element) || /^[0-9]*$/i.test(value);
    }, "Numberonly only please");

    jQuery.extend(jQuery.validator.messages, {
    maxlength: jQuery.validator.format("Only {0} characters Allowed."),
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





 $.validator.addClassRules({ 
      old_dep_first_name_class:{
        old_cRequired:  function(element) {
              if (($('.old_dep_age_class').length >0) || ($('.old_dep_income_class').length >0) || ($('.old_dep_relation_class').length >0)) {
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
$.validator.addMethod("old_cRequired", $.validator.methods.required,"Please enter name Or remove form");

$.validator.addClassRules({

    old_dep_age_class:{
        old_cNumber: true,
        old_cmaxlength: 8
    },
    submitHandler: function (form) { // for demo
       // alert('valid form submitted'); // for demo
        return false; // for demo
    }


});
$.validator.addMethod("old_cNumber", $.validator.methods.number,"Please enter valid number.");
$.validator.addMethod("old_cmaxlength", $.validator.methods.maxlength,"Please enter  maximum 8 digit.");
$.validator.addClassRules({

    old_dep_income_class:{
        old_cdNumber: true,
        old_cdmaxlength: 8
    },
    submitHandler: function (form) { // for demo
       // alert('valid form submitted'); // for demo
        return false; // for demo
    }


});
$.validator.addMethod("old_cdNumber", $.validator.methods.number,"Please enter valid number.");
$.validator.addMethod("old_cdmaxlength", $.validator.methods.maxlength,"Please enter  maximum 12 digit.");





$('#associate_register').validate({
  rules: {
          member_id: "required",
          senior_name: "required",
          form_no: {
            required: true,
            number : true,
           // checkFormNo:true,
          },
          application_date: {
            required: true,
            //dateDdMm : true,
          },
          current_carder: "required",
          senior_code:  "required",  
          first_g_first_name:{
            required: true,
            lettersonly:true
          },
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
          second_g_first_name:{
            lettersonly:true,
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
            lettersonly:true
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
           senior_name: "Please enter Senior name.",
          first_g_first_name:{
            required:"Please enter  name.",
            lettersonly:"Letters only please"
          },
          second_g_first_name:{
            lettersonly:"Letters only please"
          },
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
          dep_first_name:{
            lettersonly:"Letters only please"
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







$(document).on('click','#add_dependents',function(){
   var count = $('input[name*="dep_first_name[]"]').length;
   alert(count);
  });
         


   


      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

});


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



function sss()
{
  var code = $('#senior_code').val();
  if(code!=0)
      $.ajax({
                type: "POST",  
                url: "{!! route('admin.seniorDetails') !!}",
                dataType: 'JSON',
                data: {'code':code},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                  if(response.resCount>0)
                  { 
                    $.ajax({
                          type: "POST",  
                          url: "{!! route('admin.getAssociateCarder') !!}",
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
                              $('#current_carder').val('{{$memberData->current_carder_id}}');   
                              $("#current_carder").prop('disabled', true);
                          }
                      });

                  }
                }
            });
}
function remove_old_dep(id)
{
  $.ajax({
                type: "POST",  
                url: "{!! route('admin.associate.dependent.delete') !!}",
                dataType: 'JSON',
                data: {'id':id},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) { 
                  if(response.resCount==1)
                  { 
                    $('#old_dep_remove'+id).remove();
                    swal("Success!", "Associate dependent deleted successfully!", "success"); 
                  }
                  else
                  {
                    swal("Error!", "Associate dependent not deleted! Try Again", "error"); 
                  }
                }
            });
}
</script>