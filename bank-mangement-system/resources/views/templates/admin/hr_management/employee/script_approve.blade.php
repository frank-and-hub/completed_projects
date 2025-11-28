





<script type="text/javascript">

var date = new Date();

  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());

  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());



$(document).ready(function () {


  $("#select_date").hover(function(){
      var date=$('#create_application_date').val();
  //var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
var date1 = $('#created_date').val();
      $('#select_date').datepicker({
          format:"dd/mm/yyyy",
            startHighlight: true, 
            autoclose:true,
            startDate:date1,
            endDate:date, 
          });
   })




  $('#transfer_date').datepicker({

  format: "dd/mm/yyyy",

  todayHighlight: true,

  startDate: date, 

  autoclose: true

  }) 

$(document).on('change','#designation',function(){

    var designation = $(this).val();

    $('#salary').val();

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

                    $('#salary').val(response.salary);

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





  $('#employee_approve').validate({

      rules: { 

          
          select_date: "required",  
          employee_name: "required", 
        //  employee_code: "required",   
          designation: "required", 
          salary: { 
            required: true, 
            decimal: true,  
          },

          



        },

      messages: { 

          employee_name: "Please enter employee name.", 
        //  employee_code: "Please enter employee code.", 
          select_date: "Please select approve date",   
          designation: "Please select designation", 
          salary:{
            required: 'Please enter salary.', 
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

 





    





$(document).on('keyup','#category_name',function(){ 

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

                        $('#designation').val('{{$employee->designation_id}}');

                         $('#salary').val("{{$salary}}");

                    }); 



              }

          });



  });





 $('#category_name').trigger('keyup')





 });





</script>