


<script type="text/javascript">
$(document).ready(function () {
   

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

  $('#designation_edit').validate({
      rules: { 
          
          designation_name: "required",
          category: "required", 
          basic_salary: {
            required: true, 
            decimal: true,  
          },
          daily_allowances: { 
            decimal: true,  
          },
          hra: { 
            decimal: true,  
          },
          hra_metro_city: { 
            decimal: true,  
          },
          uma: { 
            decimal: true,  
          },
          convenience_charges: { 
            decimal: true,  
          },
          maintenance_allowance: { 
            decimal: true,  
          },
          communication_allowance: { 
            decimal: true,  
          },
          prd: { 
            decimal: true,  
          },
          ia: { 
            decimal: true,  
          },
          ca: { 
            decimal: true,  
          },
          fa: { 
            decimal: true,  
          },
          pf: { 
            decimal: true,  
          },
          tds: { 
            decimal: true,  
          },  


      },
      messages: { 
          designation_name: "Please enter designation name.",
          category: "Please select category.",
          basic_salary: {
            required: "Please enter basic salary.", 
          },
          
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

   gross_salary_calculate();
 });


function gross_salary_calculate()
{
  var basic_salary=0;
  var daily_allowances=0;
  var hra=0;
  var hra_metro_city=0;
  var uma=0;
  var convenience_charges=0;
  var communication_allowance=0;
  var prd=0;
  var ia=0;
  var ca=0;
  var fa=0;
  var pf=0;
  var tds=0;
  var maintenance_allowance=0;

  if($('#basic_salary').val()!='')
  {
    basic_salary=parseFloat($('#basic_salary').val()).toFixed(2);
  }
  if($('#daily_allowances').val()!='')
  {
     daily_allowances=parseFloat($('#daily_allowances').val()).toFixed(2);
  }
  if($('#hra').val()!='')
  {
    hra=parseFloat($('#hra').val()).toFixed(2);
  }
  if($('#hra_metro_city').val()!='')
  {
    hra_metro_city=parseFloat($('#hra_metro_city').val()).toFixed(2);
  }
  if($('#uma').val()!='')
  {
    uma=parseFloat($('#uma').val()).toFixed(2);
  }
  if($('#convenience_charges').val()!='')
  {
    convenience_charges=parseFloat($('#convenience_charges').val()).toFixed(2);
  }
  if($('#communication_allowance').val()!='')
  {
    communication_allowance=parseFloat($('#communication_allowance').val()).toFixed(2);
  }
  if($('#prd').val()!='')
  {
    prd=parseFloat($('#prd').val()).toFixed(2);
  }
  if($('#ia').val()!='')
  {
    ia=parseFloat($('#ia').val()).toFixed(2);
  }
  if($('#ca').val()!='')
  {
    ca=parseFloat($('#ca').val()).toFixed(2);
  }
  if($('#fa').val()!='')
  {
    fa=parseFloat($('#fa').val()).toFixed(2);
  }
  if($('#pf').val()!='')
  {
    pf=parseFloat($('#pf').val()).toFixed(2);
  }
  if($('#tds').val()!='')
  {
    tds=parseFloat($('#tds').val()).toFixed(2);
  } 
  if($('#maintenance_allowance').val()!='')
  {
    maintenance_allowance=parseFloat($('#maintenance_allowance').val()).toFixed(2);
  } 
   //alert(basic_salary);
   //;alert(parseFloat(basic_salary) + parseFloat(daily_allowances));
    
  sum=parseFloat(maintenance_allowance) + parseFloat(basic_salary) + parseFloat(daily_allowances) + parseFloat(hra) + parseFloat(hra_metro_city) + parseFloat(uma) + parseFloat(convenience_charges) + parseFloat(communication_allowance) + parseFloat(prd) + parseFloat(ia) + parseFloat(ca) + parseFloat(fa);
  deduction=parseFloat(pf) + parseFloat(tds);
  
  total= sum - deduction;

  $('#gross_salary').val(total);


}
</script>