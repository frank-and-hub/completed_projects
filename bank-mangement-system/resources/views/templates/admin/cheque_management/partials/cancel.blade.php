


<script type="text/javascript">
$(document).ready(function () {
   


  /*$('#cheque_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    autoclose: true
  }); */

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


  $('#cancel_cheque').validate({
      rules: { 
          cheque_date: {
            required: true,
            dateDdMm : true,
          },
          
          bank_id: "required",
          account_id: "required", 
          cheque_no: "required", 
          issue_bank_id: "required",
          issue_account_id: "required", 
          issue_cheque_no: "required",
          

      },
      messages: { 
          cheque_date: {
            required: "Please enter date.", 
          }, 

          bank_id: "Please select bank name.",
          account_id: "Please select account number.",
          cheque_no: "Please select cheque number.",
          issue_bank_id: "Please select bank name.",
          issue_account_id: "Please select account number.",
          issue_cheque_no: "Please select cheque number.",
          
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
  
  //alert('2');
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
                        $('#account_id').val('{{ $accId }}');
                        $('#account_id').trigger("change");
                    }); 

              }
          });

  });

 $(document).on('change','#issue_bank_id',function(){ 
    var bank_id=$('#issue_bank_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_account_list') !!}",
              dataType: 'JSON',
              data: {'bank_id':bank_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#issue_account_id').find('option').remove();
                $('#issue_account_id').append('<option value="">Select account number</option>');
                 $.each(response.account, function (index, value) { 
                        $("#issue_account_id").append("<option value='"+value.id+"'>"+value.account_no+"</option>");
                    }); 

              }
          });

  });

 $(document).on('change','#issue_account_id',function(){ 
    var account_id=$('#issue_account_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_cheque_list') !!}",
              dataType: 'JSON',
              data: {'account_id':account_id},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#issue_cheque_no').find('option').remove();
                $('#issue_cheque_no').append('<option value="">Select cheque number</option>');
                 $.each(response.chequeListAcc, function (index, value) { 
                        $("#issue_cheque_no").append("<option value='"+value.id+"'>"+value.cheque_no+"</option>");
                    }); 

              }
          });

  });

 $(document).on('change','#account_id',function(){ 
    var account_id=$('#account_id').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.bank_cheque_list_cancel') !!}",
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
                        $('#cheque_no').val('{{ $cheId }}');
                    }); 

              }
          });

  });

   
 $('#bank_id').trigger("change");

      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

 
  

 });
function deleteCheque() {
  if($('#cancel_cheque').valid())
  {
     swal({
                    title: "Are you sure?",
                    text: "You want to cancel cheque & re-issue",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary delete_cheque",
                    confirmButtonText: "Yes, Cancel It & Re-Issue",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger delete_cancel",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm) {
                      $('#cancel_cheque').submit()  ;
                      $('.delete_cancel').attr('disabled',true);
                      $('.delete_cheque').attr('disabled',true);
                    }
                  });
   }
   }
function advice(argument) {
 var bank_id = $("#bank_id").val();
 var account_id = $("#account_id").val();
 var cheque_no = $("#cheque_no").val(); 
 
 if(bank_id == ""){
	swal({
	  title: "Error",
	  text: "Please select bank name",
	  type: "warning",
	  buttons: true,
	  dangerMode: true,
	})
	return false;
 }
 if(account_id == ""){
	swal({
	  title: "Error",
	  text: "Please select account number",
	  type: "warning",
	  buttons: true,
	  dangerMode: true,
	})
	return false;
 }
 if(cheque_no == ""){
	swal({
	  title: "Error",
	  text: "Please select cheque number",
	  type: "warning",
	  buttons: true,
	  dangerMode: true,
	})
	return false;
 } else {
	var url = "/admin/cheque/cancel/"+cheque_no; 
	//window.open(url);
	window.location.href = url;
 }
   
}
</script>