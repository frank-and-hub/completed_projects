


<script type="text/javascript">
$(document).ready(function () {



  $('#employee_termination').validate({
      rules: { 
          
          employee_code: "required",
          company_name: "required",
          employee_name: "required", 
          employee_id: "required",
          branch: "required",
          branch_id: "required",  
          remark: "required",  
        },
      messages: { 
          employee_code: "Please enter employee code.",
          employee_name: "Please enter employee name.",
          company_name: "Please enter company name.", 
          employee_id: "Please enter employee id.",
          branch: "Please enter branch name.",
          branch_id: "Please enter branch id.",  
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
             // alert(response.msg);
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
                  $('#company_id').val(response.data.company.id);
                  $('#company_name').val(response.data.company.name);
                }
                else
                {
                  $('.show_emp_detail').hide();
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
            $('#company_id').val('');
            $('#company_name').val('');
          }
      });


</script>