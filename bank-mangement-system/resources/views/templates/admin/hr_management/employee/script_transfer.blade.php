


<script type="text/javascript">
var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  
$(document).ready(function () {
  // $('#transfer_date').datepicker({
  // format: "dd/mm/yyyy",
  // startDate: dateValue, 
  // autoclose: true
  // }) 
$(document).on('change','#transfer_designation',function(){
    var designation = $(this).val();
    $('#transfer_salary').val();
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationDataGet') !!}",
              dataType: 'JSON',
              data: {'designation':designation,},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                //alert(response.data.basic_salary); 
                if(response.msg==1)
                {
                    $('#transfer_salary').val(parseFloat(response.salary).toFixed(2));
                }
                else
                {
                  swal("Sorry!", "Record not found.Try Again!", "error");
                }
              }
          })
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
        $.validator.messages.dateDdMm = "Please enter valid date.";
        result = false;  
      }
    
    return result;
  }, "");

  $('#employee_transfer').validate({
      rules: { 
          
          employee_code: "required",
          employee_name: "required", 
          employee_id: "required",
          branch: "required",
          branch_id: "required",  
          category: "required",
          category_id: "required", 
          designation_id: "required",
          designation: "required",
          salary: "required",  
          rec_employee_name: "required", 

          transfer_date: {
            required: true,
            dateDdMm : true,
          },
          transfer_rec_employee_name: "required", 
          transfer_rec_employee_designation: "required",  
          transfer_branch: "required",
          transfer_designation: "required", 
          transfer_salary: { 
            required: true, 
            decimal: true,  
          },
          transfer_category: "required",
          file: {
            required:true,
            extension: "jpg|jpeg|png|pdf|docx|rtf|doc|txt",
          }, 

        },
      messages: { 
          employee_code: "Please enter employee code.",
          employee_name: "Please enter employee name.", 
          employee_id: "Please enter employee id.",
          branch: "Please enter branch name.",
          branch_id: "Please enter branch id.",  
          category: "Please select branch",
          category_id: "Please select branch", 
          designation_id: "Please select designation",
          designation: "Please select designation",
          salary: "Please enter salary",  
          rec_employee_name: "Please enter recommendation employee name",   
          transfer_date:{
            required: 'Please enter transfer date.', 
          },
          transfer_rec_employee_name: "Please enter recommendation employee name",
          transfer_rec_employee_designation: "Please enter recommendation employee designation",  
          transfer_branch: "Please select branch",
          transfer_designation: "Please select designation",
          transfer_salary:{
            required: 'Please enter salary.', 
          },
          transfer_category: "Please select category",
          file:{
            required: 'Please select file.',
            extension: "Accept only png,jpg,pdf,or word files(.jpg,.jpeg,.png,.pdf,.docx,.rtf,.doc,.txt)."
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
 


    $(document).on('keyup','#employee_code',function(){
      $('.show_emp_detail').hide();
      $('#error_msg_emp').html(' ');
                  $('#error_msg_emp').hide();
      $('#employee_name').val('');
      $('#employee_id').val('');
      $('#branch').val('');
      $('#branch_id').val('');
      $('#transfer_branch').children("option").show();
      $('#category').val('');
      $('#category_id').val(''); 
      $('#designation_id').val('');
      $('#designation').val('');
      $('#rec_employee_name').val('');
      $('#salary').val('');
      $('#company_name').val('');
      $('#company_id').val('');
    var code = $(this).val();
    $.ajax({
              type: "POST",  
              url: "{!! route('admin.employeeDataGet') !!}",
              dataType: 'JSON',
              data: {'code':code,},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
             $('.show_emp_detail').hide();
      $('#error_msg_emp').html(' ');
                  $('#error_msg_emp').hide();
      $('#employee_name').val('');
      $('#employee_id').val('');
      $('#branch').val('');
      $('#branch_id').val('');
      $('#transfer_branch').children("option").show();
      $('#category').val('');
      $('#category_id').val(''); 
      $('#designation_id').val('');
      $('#designation').val('');
      $('#rec_employee_name').val('');
      $('#salary').val('');
                if(response.msg==1)
                {
                  $('.show_emp_detail').show();
                  $('#employee_name').val(response.data.employee_name);
                  $('#employee_id').val(response.data.id);
                  $('#branch').val(response.data.branch.name);
                  $('#branch_id').val(response.data.branch_id);
                  $('#rec_employee_name').val(response.data.recommendation_employee_name);
                  $('#rec_employee_designation').val(response.data.recom_employee_designation);
                  $('#designation_id').val(response.data.designation_id);
                  $('#salary').val(parseFloat(response.data.salary).toFixed(2));
                  $('#designation').val(response.designation);
                  $('#company_name').val(response.data.company.name);
                  $('#company_id').val(response.data.branch.id);
                  if(response.data.category==1)
                  {
                    $('#category').val('On-rolled');
                    $('#category_id').val(response.data.category);
                  }
                  if(response.data.category==2)
                  {
                    $('#category').val('Contract');
                    $('#category_id').val(response.data.category);
                  }
                  

                  $('#transfer_branch').children("option[value^=" +response.data.branch_id+ "]").hide(); 
                }
                else
                {
                  $('#error_msg_emp').show();
                  $('#error_msg_emp').html('<div class="alert alert-danger alert-block">  <strong>Employee not found.!</strong> </div>');
                }
                
                
              }
          })
  });
    if($('#employee_code').val()!='')
    {
      $('#employee_code').trigger('keyup');
    }



$(document).on('change','#category',function(){ 
    var category=$('#category').val();
    $('#salary').val();

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationByCategory') !!}",
              dataType: 'JSON',
              data: {'category':category},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#designation').find('option').remove();
                $('#designation').append('<option value="">Select Designation</option>');
                 $.each(response.data, function (index, value) { 
                        $("#designation").append("<option value='"+value.id+"'>"+value.designation_name+"</option>");
                    }); 

              }
          });

  });



$(document).on('change','#transfer_category',function(){ 
    var category=$('#transfer_category').val();
    $('#transfer_salary').val('');

          $.ajax({
              type: "POST",  
              url: "{!! route('admin.designationByCategory') !!}",
              dataType: 'JSON',
              data: {'category':category},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                $('#transfer_designation').find('option').remove();
                $('#transfer_designation').append('<option value="">Select Designation</option>');
                 $.each(response.data, function (index, value) { 
                        $("#transfer_designation").append("<option value='"+value.id+"'>"+value.designation_name+"</option>");
                    }); 

              }
          });

  });


 });
 $('#transfer_date').on('mouseenter', function() {
  var currentDate = $('#created_at').val();

  $('#transfer_date').datepicker({
    format: "dd/mm/yyyy",
    startDate: currentDate,
    autoclose: true
  });
  $('#transfer_date').datepicker('setDate', currentDate);
  $('#transfer_date').datepicker('setEndDate', currentDate);

});


</script>