


<script type="text/javascript">
$(document).ready(function () {

  // $('#deposit_cheque_date').datepicker( {
  //       format: "dd/mm/yyyy", 
  //       todayHighlight: true,     
  //       startDate: '-3m',
  //       endDate: new Date()
          
  //       });

  $("#deposit_cheque_date").hover(function(){
      var date=$('#deposit_cheque_date').val();
 
     $('#deposit_cheque_date').datepicker( {
        format: "dd/mm/yyyy", 
        todayHighlight: true,     
        startDate: '-3m',
        endDate: date  
        });
      });

      $("#cheque_date").hover(function(){
      var date=$('#cheque_date').val();

        $('#cheque_date').datepicker( {
        format: "dd/mm/yyyy", 
        todayHighlight: true,     
        startDate: '-3m',
        endDate: date  
        });
   }) ;


    // Get the current date
var currentDate = new Date();

// Subtract 4 months from the current date
var date = new Date();


  $('#cheque_date').datepicker({
    format: 'dd/mm/yyyy',
    startDate: '-3m',
    endDate: date
    
  });

 
 



        // $('#cheque_date').datepicker( {
        // format: "dd/mm/yyyy",
       
        // endDate: date,

  //       $( "#datepicker" ).datepicker({ 
  //    dateFormat: "dd/mm/yy",
  //    changeMonth: true,
  //    changeYear: true,
  //    yearRange: "-0:+1",
  //   
    
  //   });
  // });




      
        // });




  $(document).on('change','#branch_id',function(){
          var bId = $('option:selected', this).attr('data-val');
          var sbId = $( "#hbranchid option:selected" ).val();  
          if(bId != sbId){
            $('#branch_id').val('');
            swal("Warning!", "Branch does not match from top dropdown state", "warning");
          }  
    });
 
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
          deposit_cheque_date: {
            required: true,
            dateDdMm : true,
          },
          cheque_date: {
            required: true,
            dateDdMm : true,
          },
          branch: "required",
          bank_name: "required",  
          bank_branch_name: "required",
          cheque_number: {
            required: true, 
            //number: true, 
           // minlength: 6,
           // maxlength: 6,
          },
          account_no: {
            required: true, 
            number: true, 
            minlength: 8,
            maxlength: 20
          }, 
          account_holder: "required",
          amount: {
            required: true, 
            decimal: true, 
          },
          account_id: "required", 
          bank_id: "required",   
          company_id: "required",
      },
      messages: { 
          cheque_date: {
            required: "Please enter date",
            date : "Please enter a valid date",
          }, 
          deposit_cheque_date: {
            required: "Please enter date",
            date : "Please enter a valid date",
          },
          branch_id: "Please select branch",
          bank_name: "Please enter cheque bank name",
          bank_branch_name: "Please enter cheque branch name",
          cheque_number: {
            required: "Please enter cheque number.",
            number : "Please enter a valid number.",
            minlength: "Please enter minimum  6 or maximum 6 digit.",
            maxlength: "Please enter minimum  6 or maximum 6 digit.",
          },
          account_no: {
            required: "Please enter cheque account number.",
            number : "Please enter a valid number.",
            minlength: 'Please enter minimum 8 digit number',
            maxlength: 'Please enter maximum 20 digit number',
          },
          account_holder: "Please enter account holder name",          
          amount: {
            required: "Please enter amount number.", 
          },
          bank_id: "Please select bank name",
          account_id: "Please select account number",
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

  

 });
</script>