


<script type="text/javascript">
$(document).ready(function () {
   


  $('#cheque_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    autoclose: true,
    orientation:"bottom",
    startDate: '01/04/2021',

  });
 

  $.validator.addMethod("chequeNoValid", function(value, element,p) {
    if(parseInt($('#cheque_to').val()) < parseInt($('#cheque_from').val()))
    {

        $.validator.messages.chequeNoValid = "Cheque to number must be greater than cheque from number";
        result = false;  
      
    } 
    else{ 
    $.validator.messages.chequeNoValid = " dd";     
      result = true; 
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


  $('#cheque_add').validate({
      rules: { 
        cheque_date: {
            required: true,
            dateDdMm : true,
          },          
          bank_id: "required",
          account_id: "required", 
          cheque_from: {
            required: true, 
            number: true, 
            minlength: 6,
            maxlength: 6, 
          },
          cheque_to: {
            required: true, 
            number: true,
            chequeNoValid: true, 
            minlength: 6,
            maxlength: 6,
            
          },            
           
          total_cheque: {
            required: true,
            number: true,
          },
          company_id: {
            required: true,        
          },

      },
      messages: { 
        cheque_date: {
            required: "Please enter date.", 
          }, 
          cheque_date: "please enter date",
          bank_id: "Please select bank name.",
          account_id: "Please select account number.",
          cheque_from: {
            required: "Please enter  from cheque number.",
            number : "Please enter a valid number.",
            minlength: "Please enter minimum  6 or maximum 6 digit.",
            maxlength: "Please enter minimum  6 or maximum 6 digit.",
          },
          cheque_to: {
            required: "Please enter to cheque number.",
            number : "Please enter a valid number.",
            minlength: "Please enter minimum  6 or maximum 6 digit.",
            maxlength: "Please enter minimum  6 or maximum 6 digit.",
          },
          total_cheque: {
            required: "Please enter total cheque number.",
            number : "Please enter a valid number.",
          },
          company_id: {
            required: "please select company name."
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
  $(document).on('change','#bank_id',function(){ 
    var bank_id=$('#bank_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_account_list') !!}",
              dataType: 'JSON',
              data: {'bank_id':bank_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#account_id').find('option').remove();
                $('#account_id').append('<option value="">Select account number</option>');
                 $.each(response.account, function (index, value) { 
                        $("#account_id").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
                    }); 

              }
          });

  });
  $(document).on('change','#company_id',function(){ 
    $("#account_id").val('');
    var company_id=$('#company_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_list_by_company') !!}",
              dataType: 'JSON',
              data: {'company_id':company_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#bank_id').find('option').remove();
                $('#bank_id').append('<option value="">Select bank</option>');
                 $.each(response.bankList, function (index, value) { 
                        $("#bank_id").append("<option value='"+value.id+"'>"+value.bank_name+"</option>");
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

  $(document).on('keyup','#cheque_from',function(){
    
    var cheque_from = $('#cheque_from').val(); 
    var cheque_to = $('#cheque_to').val(); 
     if(cheque_to!="" && cheque_from!="")
    {
      var cheque_from = parseInt($('#cheque_from').val()); 
    var cheque_to = parseInt($('#cheque_to').val()); 
      var count=parseInt((cheque_to-cheque_from)+1);
      if(count>0)
      {
          $('#total_cheque').val(count);
      }
      else
      {
        $('#total_cheque').val('');
      }
    
    }

    
  }); 
  $(document).on('keyup','#cheque_to',function(){
    
    var cheque_from = $('#cheque_from').val(); 
    var cheque_to = $('#cheque_to').val(); 
     if(cheque_to!="" && cheque_from!="")
    {
      var cheque_from = parseInt($('#cheque_from').val()); 
    var cheque_to = parseInt($('#cheque_to').val()); 
      var count=parseInt((cheque_to-cheque_from)+1);
      if(count>0)
      {
          $('#total_cheque').val(count);
      }
      else
      {
        $('#total_cheque').val('');
      }
    
    }

  }); 

 });
</script>