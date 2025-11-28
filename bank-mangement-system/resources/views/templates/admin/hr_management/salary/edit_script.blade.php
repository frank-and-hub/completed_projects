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
    esi_amount:{ esi_amount_r:  true,decimal: true, zero: true},
          submitHandler: function (form) {    return false;   }  
  });
  $.validator.addMethod("esi_amount_r", $.validator.methods.required,"Please enter ESI.");
  
  $.validator.addClassRules({ 
    pf_amount:{ pf_amount_r:  true,decimal: true, zero: true},
          submitHandler: function (form) {    return false;   }  
  });
  $.validator.addMethod("pf_amount_r", $.validator.methods.required,"Please enter PF.");
  
  $.validator.addClassRules({ 
    tds_amount:{ tds_amount_r:  true,decimal: true, zero: true},
          submitHandler: function (form) {    return false;   }  
  });
  $.validator.addMethod("tds_amount_r", $.validator.methods.required,"Please enter TDS.");
  
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
            select_date: "required",
        },
        messages: {  
            salary_month: "Please enter month.", 
            salary_year: "Please enter year.", 
            select_date: "Please select Date.", 
        },
        submitHandler: function (){
      $('button[type="submit"]').prop('disabled',true);
      return true;
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
      var data =  $(this).val();
      var comp = $('#final_payable_amount_'+res).val(); 
      if(leave > 30){
        swal("Warning!", "Maximum leaves allowed are 30", "warning");
        $(this).val(0);
      }
      calculatioAmountfinalPay(res);
  
    });
  $(document).on('keyup','.deduction',function(){ 
      id = $(this).attr('id'); 
      var res = id.substr(10);    
      var data =  $(this).val();
      var comp = $('#total_salary_'+res).val(); 
      if(parseFloat(comp) < parseFloat(data)){
        swal("Warning!", "You cannot deduct more than the total salary`", "warning");
        $(this).val(0);
      }
      calculatioAmountfinalPay(res);
  
    });
  $(document).on('keyup','.incentive_bonus',function(){ 
      id = $(this).attr('id'); 
      var res = id.substr(16); 
      var data =  $(this).val();
      var comp = $('#final_payable_amount_'+res).val(); 
      // if(parseFloat(comp) < parseFloat(data)){
      //   swal("Warning!", "Salary cannot be in negative digits", "warning");
      //   $(this).val(0);
      // }
      calculatioAmountfinalPay(res);
    });
  
    $(document).on('keyup','.esi_amount',function(){ 
      id = $(this).attr('id'); 
      var res = id.substr(11);   
      var data =   parseFloat($(this).val());
      var pf =  parseFloat($('#pf_amount_'+res).val());
      // var esi = $('#esi_amount_'+res).val();
      var tds =  parseFloat($('#tds_amount_'+res).val());
      var comp =  parseFloat($('#transfer_salary_'+res).val()); 
      var tocompare = tds + pf + data;
      if(parseFloat(comp) < parseFloat(tocompare)){
        swal("Warning!", "Salary cannot be in negative digits", "warning");
        $(this).val(0);
      }
      calculatioAmountfinalPay(res);
    });
    $(document).on('keyup','.pf_amount',function(){ 
      id = $(this).attr('id'); 
      var res = id.substr(10);   
      var data =  parseFloat($(this).val());
      // var pf = $('#pf_amount_'+res).val();
      var esi = parseFloat($('#esi_amount_'+res).val());
      var tds = parseFloat($('#tds_amount_'+res).val());
      var comp = parseFloat($('#transfer_salary_'+res).val()); 
      var tocompare = tds + esi + data;
      if(parseFloat(comp) < parseFloat(tocompare)){
        swal("Warning!", "Salary cannot be in negative digits", "warning");
        $(this).val(0);
      }
      calculatioAmountfinalPay(res);
    });
    $(document).on('keyup','.tds_amount',function(){ 
      id = $(this).attr('id'); 
      var res = id.substr(11); 
      var data =   parseFloat($(this).val());
      var pf =  parseFloat($('#pf_amount_'+res).val());
      var esi =  parseFloat($('#esi_amount_'+res).val());
      // var tds = $('#tds_amount_'+res).val();
      var comp =  parseFloat($('#transfer_salary_'+res).val()); 
      var tocompare = pf + esi + data;
      if(parseFloat(comp) < parseFloat(tocompare)){
        swal("Warning!", "TDS cannot be equal to or more than the salary", "warning");
        $(this).val(0);
      }
      calculatioAmountfinalPay(res);
    });
  
  
  
  
   });
   function  calculatioAmountfinalPay(id)
   {
    res=id;
    salary  = $('#salary_'+id).val();
    total_day=30;
    leave= $('#leave_'+id).val();
  
    onedaySalary=salary/total_day; 
  
      if(leave>0)
      {
        leaveSalry=onedaySalary*leave;
      } 
      else
      {
        leaveSalry=0;
      } 
      
      currentSalary  = salary-leaveSalry;
      $('#total_salary_'+res).val(parseFloat(currentSalary).toFixed(2));
      
      deduction  = $('#deduction_'+res).val();     
      incentive_bonus  = $('#incentive_bonus_'+res).val(); 
      cal_de_bouns=incentive_bonus-deduction;
      
      total_salary = paybaleAmout = currentSalary+cal_de_bouns; 
  
  
      $('#transfer_salary_'+res).val(parseFloat(paybaleAmout).toFixed(2));
      esi_amount= $('#esi_amount_'+id).val();
      pf_amount= $('#pf_amount_'+id).val();
      tds_amount= $('#tds_amount_'+id).val(); 
  
      finalPaybleAmout=paybaleAmout-esi_amount-pf_amount-tds_amount;
      $('#final_payable_amount_'+res).val(parseFloat(finalPaybleAmout).toFixed(2));
      calculateSum();
      calculateSumEsi();
      calculateSumPF();
      calculateSumTDS();
      calculateSumFinal();
     
   }
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
      $('#salary_to_sum').val(sum.toFixed(2))
    }
  
    function calculateSumEsi() 
    {
      var sum = 0;
      //iterate through each textboxes and add the values
      $(".esi_amount").each(function() {
        //add only if the value is number
        if(!isNaN(this.value) && this.value.length!=0) {
          sum += parseFloat(this.value);
        }
      });
      //.toFixed() method will roundoff the final sum to 2 decimal places
      $("#sum_esi").html(sum.toFixed(2));
      $('#esi_to_sum').val(sum.toFixed(2))
    }
    function calculateSumPF() 
    {
      var sum = 0;
      //iterate through each textboxes and add the values
      $(".pf_amount").each(function() {
        //add only if the value is number
        if(!isNaN(this.value) && this.value.length!=0) {
          sum += parseFloat(this.value);
        }
      });
      //.toFixed() method will roundoff the final sum to 2 decimal places
      $("#sum_pf").html(sum.toFixed(2));
      $('#pf_to_sum').val(sum.toFixed(2))
    }
  
    function calculateSumTDS() 
    {
      var sum = 0;
      //iterate through each textboxes and add the values
      $(".tds_amount").each(function() {
        //add only if the value is number
        if(!isNaN(this.value) && this.value.length!=0) {
          sum += parseFloat(this.value);
        }
      });
      //.toFixed() method will roundoff the final sum to 2 decimal places
      $("#sum_tds").html(sum.toFixed(2));
      $('#tds_to_sum').val(sum.toFixed(2))
    }
    function calculateSumFinal() 
    {
      var sum = 0;
      //iterate through each textboxes and add the values
      $(".final_payable_amount").each(function() {
        //add only if the value is number
        if(!isNaN(this.value) && this.value.length!=0) {
          sum += parseFloat(this.value);
          if (parseFloat(this.value) < 0) {
            swal("Warning!", "Salary is going in negative please re-check your values", "warning");
            $('button[id="submit_transfer"]').prop('disabled',true);
          }else{
            $('button[id="submit_transfer"]').prop('disabled',false);
          }
        }
      });
      //.toFixed() method will roundoff the final sum to 2 decimal places
      $("#sum_transfer").html(sum.toFixed(2));
      $('#transfer_to_sum').val(sum.toFixed(2))
    }
  
  
  
  function resetForm()
  {
      $('#month').val("");
      
      $('#year').val(""); 
      $('#hide_div').hide();
      window.location.href = window.location.href;
  }
  
  </script>