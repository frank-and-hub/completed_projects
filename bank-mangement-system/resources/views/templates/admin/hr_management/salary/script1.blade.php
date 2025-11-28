


<script type="text/javascript">

  var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());

$(document).ready(function () {
  


$.validator.addClassRules({ 
      deduction:{ deductionRequired:  true,decimal: true, zero: true},
        submitHandler: function (form) {    return false;   }  
});
$.validator.addMethod("deductionRequired", $.validator.methods.required,"Please enter deduction.");

$.validator.addClassRules({ 
      incentive_bonus:{ incentive_bonusR:  true,decimal: true, zero: true},
        submitHandler: function (form) {    return false;   }  
});
$.validator.addMethod("incentive_bonusR", $.validator.methods.required,"Please enter Incentive/Bonus.");

$.validator.addClassRules({ 
      transfer_salary:{  transfer_salaryRequired:  true, decimal: true, zero: true},
        submitHandler: function (form) {   return false;   }  
});
$.validator.addMethod("transfer_salaryRequired", $.validator.methods.required,"Please enter transfer salary.");
$.validator.addClassRules({ 
      salary:{  salaryRequired:  true, decimal: true, zero: true},
        submitHandler: function (form) {   return false;   }  
});
$.validator.addMethod("salaryRequired", $.validator.methods.required,"Please enter salary.");

$.validator.addClassRules({ 
      leave:{ leaveRequired:  true, decimal: true, zero: true},
        submitHandler: function (form) {   return false;   }  
});
$.validator.addMethod("leaveRequired", $.validator.methods.required,"Please enter leave days.");

$.validator.addMethod("decimal", function(value, element,p) {     
      if(this.optional(element) || /^\d*\.?\d*$/g.test(value)==true)
      {
        $.validator.messages.decimal = "";
        result = true;
      }else{
        $.validator.messages.decimal = "Please enter valid numeric number.";
        result = false;  
      }
    
    return result;
  }, "");


$.validator.addMethod("zero", function(value, element,p) {     
      if(value>=0)
      {
        $.validator.messages.zero = "";
        result = true;
      }else{
        $.validator.messages.zero = "Amount must be greater than or equal to 0.";
        result = false;  
      }
    
    return result;
  }, "");


  $('#salary_generate').validate({
      rules: { 
          
          salary_month: "required", 
          salary_year: "required", 
          incentive_bonus: "required", 

      },
      messages: {  
          salary_month: "Please enter month.", 
          salary_year: "Please enter year.", 
          incentive_bonus: "Please enter incentive_bonus.", 

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
   

 
   


      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

$(document).on('keyup','.leave',function(){ 
    id = $(this).attr('id');
    leave  = $(this).val();
    if(leave=='')
    {
      $('#chk').val(''); 
    }
    leaveSalry=0;

    var res = id.substr(6); 

    deduction  = $('#deduction_'+res).val(); 
    if(deduction=='')
    {
      $('#chk').val(''); 
    }
    incentive_bonus  = $('#incentive_bonus_'+res).val(); 
    cal_de_bouns=incentive_bonus-deduction;

    salary  = $('#salary_'+res).val(); 
    total_day='{{$pre_month_days}}';
    onedaySalary=salary/total_day; 
    leaveSalry=onedaySalary*leave;
    currentSalary  = salary-leaveSalry;

    


    total_salary  = currentSalary+cal_de_bouns;
    $('#total_salary_'+res).val(parseFloat(currentSalary).toFixed(2));
    $('#transfer_salary_'+res).val(parseFloat(total_salary).toFixed(2));
     calculateSum();


  });
$(document).on('keyup','.deduction',function(){ 
    id = $(this).attr('id');
    leave  = $('#leave_'+res).val();
    leaveSalry=0;
    if(leave=='')
    {
      $('#chk').val(''); 
    }
 
    var res = id.substr(10);      

    deduction  = $('#deduction_'+res).val(); 

    incentive_bonus  = $('#incentive_bonus_'+res).val(); 
    cal_de_bouns=incentive_bonus-deduction;

    salary  = $('#salary_'+res).val();  
    total_day='{{$pre_month_days}}';
    onedaySalary=salary/total_day; 
    // leaveSalry=0;
    if(leave>0)
    {
      leaveSalry=onedaySalary*leave;
    }  
    currentSalary  = salary-leaveSalry;

    


    total_salary  = currentSalary+cal_de_bouns;
    $('#total_salary_'+res).val(parseFloat(currentSalary).toFixed(2));
    $('#transfer_salary_'+res).val(parseFloat(total_salary).toFixed(2));
     calculateSum();

  });
$(document).on('keyup','.incentive_bonus',function(){ 
    id = $(this).attr('id');
    leave  = $('#leave_'+res).val();
    leaveSalry=0;
 
    var res = id.substr(16);  



    deduction  = $('#deduction_'+res).val(); 
    incentive_bonus  = $('#incentive_bonus_'+res).val(); 
    if(incentive_bonus=='')
    {
      $('#chk').val(''); 
    }
    cal_de_bouns=incentive_bonus-deduction;
          
    salary  = $('#salary_'+res).val();  
    total_day='{{$pre_month_days}}';
    onedaySalary=salary/total_day; 
    // leaveSalry=0;
    if(leave>0)
    {
      leaveSalry=onedaySalary*leave;
    }  
    currentSalary  = salary-leaveSalry;


    total_salary  = currentSalary+cal_de_bouns;
    $('#total_salary_'+res).val(parseFloat(currentSalary).toFixed(2));
    $('#transfer_salary_'+res).val(parseFloat(total_salary).toFixed(2));
     calculateSum();


  });



 });


function calculateSum() {

    var sum = 0;
    //iterate through each textboxes and add the values
    $(".transfer_salary").each(function() {

      //add only if the value is number
      if(!isNaN(this.value) && this.value.length!=0) {
        sum += parseFloat(this.value);
      }

    });
    //.toFixed() method will roundoff the final sum to 2 decimal places
    $("#sum").html(sum.toFixed(2));
  }

  function subSalary()
{  
    if($('#salary_generate').valid())
    {
        //$('#salary_generate').submit(); 
    }
}
</script>