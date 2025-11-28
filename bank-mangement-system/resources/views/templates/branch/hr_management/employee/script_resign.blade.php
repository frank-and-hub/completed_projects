


<script type="text/javascript">
$(document).ready(function () {



  $('#employee_resign').validate({
      rules: { 
          
          employee_code: "required",
          
          employee_name: "required", 
          employee_id: "required",
          branch: "required",
          branch_id: "required", 
          application_file: {
            required:true,
            extension: "jpg|jpeg|png|pdf|docx|rtf|doc|txt",
          }, 
          remark: "required",  
        },
      messages: { 
          employee_code: "Please enter employee code.",
         
          employee_name: "Please enter employee name.", 
          employee_id: "Please enter employee id.",
          branch: "Please enter branch name.",
          branch_id: "Please enter branch id.", 
          application_file:{
            required: 'Please select application file.',
            extension: "Accept only png,jpg,pdf,or word files(.jpg,.jpeg,.png,.pdf,.docx,.rtf,.doc,.txt)."
          },
          remark: "Please enter remark.", 
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
      $('#company_name').val('');
      $('#company_id').val('');
    var code = $(this).val();
    $.ajax({
              type: "POST",  
              url: "{!! route('branch.employeeDataGet') !!}",
              dataType: 'JSON',
              data: {'code':code,},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                // if (response.branch_id != response.data.branch_id) {
                //   swal("Warning!","You Cant only resign from logged in barnch","warning");
                //   $('#employee_code').val();
                //   return false;
                // }
                if(response.data!=null){
                  if (response.branch_id != response.data.branch_id) {
                    swal("Warning!","You can not only resign from logged in barnch","warning");
                    $('#employee_code').val();
                    return false;
                  }
                }else{
                  swal('warning','Emplyee Code is Not valid, Please Check Again.','warning');
                  $('#employee_code').val('');
                  return false;
                }
             $('.show_emp_detail').hide();
      $('#error_msg_emp').html(' ');
      $('#error_msg_emp').hide();
      
      $('#employee_name').val('');
      $('#employee_id').val('');
      $('#branch').val('');
      $('#branch_id').val('');
                if(response.msg==1)
                {
                  $('.show_emp_detail').show();
                  $('#employee_name').val(response.data.employee_name);
                  $('#employee_id').val(response.data.id);
                  $('#branch').val(response.data.branch.name);
                  $('#branch_id').val(response.data.branch_id);
                  $('#company_name').val(response.data.company.name);
                  $('#company_id').val(response.data.company.id);
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

 });
 $("#employee_code").on("change", function() {
            if($('#employee_code').val()=='')
          {
            $('.show_emp_detail').hide();
            $('#employee_name').val('');
      $('#employee_id').val('');
      $('#branch').val('');
      $('#branch_id').val('');
      $('#company_name').val('');
      $('#company_id').val('');
          }
      });


</script>