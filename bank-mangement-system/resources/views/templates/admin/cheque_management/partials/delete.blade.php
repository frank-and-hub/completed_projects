


<script type="text/javascript">
$(document).ready(function () {
   


  $('#cheque_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    autoclose: true
  }); 

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


  $('#delete_cheque').validate({
      rules: { 
          cheque_date: {
            required: true,
            dateDdMm : true,
          },
          
          bank_id: "required",
          account_id: "required", 
          cheque_no: "required",
          company_id: "required", 
          

      },
      messages: { 
          cheque_date: {
            required: "Please enter date.", 
          }, 

          bank_id: "Please select bank name.",
          account_id: "Please select account number.",
          cheque_no: "Please select cheque number.",
          company_id:"please select company name",
          
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
                        $("#account_id").val('{{$accId}}');
                        $('#account_id').trigger("change");
                    }); 

              }
          });

  });

  $(document).on('change','#account_id',function(){ 
    var account_id=$('#account_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_account_cheque_list') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#cheque_no').find('option').remove();
                $('#cheque_no').append('<option value="">Select cheque number</option>');
                 $.each(response.chequeListAcc, function (index, value) { 
                        $("#cheque_no").append("<option value='"+value.id+"'>"+value.cheque_no+"</option>");
                        $("#cheque_no").val('{{$cheId}}');
                    }); 

              }
          });

  });

  $(document).on('change','#company_id',function(){ 
    $("#account_id").val('');
    $("#cheque_no").val('');
    var company_id=$('#company_id').val();
    //alert('hh');
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
                    $("#bank_id").val('{{$bankId}}');
                    $('#bank_id').trigger("change"); 

              }
          });

  });

$('#company_id').val('{{$company_id}}');
$('#company_id').trigger("change"); 
  


 
   


      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

 
  

 });
function deleteCheque() {
  if($('#delete_cheque').valid())
  {
     swal({
                    title: "Are you sure?",
                    text: "You want to delete cheque",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary delete_cheque",
                    confirmButtonText: "Yes, Delete It",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger delete_cancel",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm) {
                      $('#delete_cheque').submit()  ;
                      $('.delete_cancel').attr('disabled',true);
                      $('.delete_cheque').attr('disabled',true);
                    }
                  });
   }
   }
</script>