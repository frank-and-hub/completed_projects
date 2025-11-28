


<script type="text/javascript">
 

$(document).ready(function () {
  



   
$.validator.addClassRules({ 
      salary_transferSum :{ checkedCount:  true,},
        submitHandler: function (form) {    return false;   }  
});
$.validator.addMethod("checkedCount", function(value, element,p) {  
var $checkboxes = $('input[type="checkbox"]'); 

      if(($checkboxes.filter(':checked').length)>0)
      {
        $.validator.messages.checkedCount = "";
        result = true;
      }else{
        $.validator.messages.checkedCount = "Please select list.";
        result = false;  
      }
    
    return result;
  }, "");


  $('#salary_transfer').validate({
      rules: { 
          
          amount_mode: "required", 

      },
      messages: {  
          amount_mode: "Please select amount mode.", 

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

$(document).on('click','.salary_transferSum',function(){ 
    
var total = 0;
var id='';
        $(".salary_transferSum:checked").each(function() {
            
            
            if($('#amount_mode').val()==1)
            {
              if($(this).closest("tr").find(".ssb").val()=='')
              {
                $(this).prop("checked", false);
                swal("alert", "This employee have not SSB account.", "error");
              }
              else
              {
                total += parseFloat($(this).val());
                id = $(this).closest("tr").find(".id_get").val()+','+id;
                $('#select_id').val(id);
              }
            }
            else
            {
              total += parseFloat($(this).val());
              id = $(this).closest("tr").find(".id_get").val()+','+id;
              $('#select_id').val(id);
            }
            
        }); 
        if (total == 0) {
            $('#total_transfer_amount').html('<strong>0</strong>');
            //$('#total_transfer').val('');
        } else {
            total1=parseFloat(total).toFixed(2);
            $('#total_transfer_amount').html('<strong>'+total1+'</strong>');
            //$('#total_transfer').val(total1);
        }

        if($(".salary_transferSum").length == $(".salary_transferSum:checked").length) { 
                 //if the length is same then untick 
                $("#salary_transfer_all").prop("checked", true);
            }else {
                //vise versa
                $("#salary_transfer_all").prop("checked", false);            
            }
           

  });

$('#salary_transfer_all').click(function(){
            if($('#salary_transfer_all').prop("checked")) {
                $(".salary_transferSum").prop("checked", true);
            } else {
                $(".salary_transferSum").prop("checked", false);
            } 

            var total = 0;
var id='';
        $(".salary_transferSum:checked").each(function() {
             
            if($('#amount_mode').val()==1)
            {
              if($(this).closest("tr").find(".ssb").val()=='')
              {
                $(this).prop("checked", false);
                swal("alert", "This employee have not SSB account.", "error");
              }
              else
              {
                total += parseFloat($(this).val());
                id = $(this).closest("tr").find(".id_get").val()+','+id;
              $('#select_id').val(id);
              }
            }
            else
            {
              total += parseFloat($(this).val());
              id = $(this).closest("tr").find(".id_get").val()+','+id;
              $('#select_id').val(id);
            }
            
        }); 

        if (total == 0) {
            $('#total_transfer_amount').html('<strong>0</strong>');
          //  $('#total_transfer').val('');
        } else {
          total1=parseFloat(total).toFixed(2);
            $('#total_transfer_amount').html('<strong>'+total1+'</strong>');
           // $('#total_transfer').val(total1);
        }


        if($(".salary_transferSum").length == $(".salary_transferSum:checked").length) { 
                 //if the length is same then untick 
                $("#salary_transfer_all").prop("checked", true);
            }else {
                //vise versa
                $("#salary_transfer_all").prop("checked", false);            
            }
                           
  });

$('#amount_mode').change(function(){
  var total = 0;
var id='';
        $(".salary_transferSum:checked").each(function() { 
            if($('#amount_mode').val()==1)
            {
              if($(this).closest("tr").find(".ssb").val()=='')
              {
                $(this).prop("checked", false);
                swal("alert", "This employee have not SSB account.", "error");
              }
              else
              {
                total += parseFloat($(this).val());
                id = $(this).closest("tr").find(".id_get").val()+','+id;
              $('#select_id').val(id);
              }
            }
            else
            {
              total += parseFloat($(this).val());
              id = $(this).closest("tr").find(".id_get").val()+','+id;
              $('#select_id').val(id);
            }
            
        }); 
        if (total == 0) {
            $('#total_transfer_amount').html('<strong>0</strong>');
            //$('#total_transfer').val('');
        } else {
            total1=parseFloat(total).toFixed(2);
            $('#total_transfer_amount').html('<strong>'+total1+'</strong>');
            //$('#total_transfer').val(total1);
        }

        if($(".salary_transferSum").length == $(".salary_transferSum:checked").length) { 
                 //if the length is same then untick 
                $("#salary_transfer_all").prop("checked", true);
            }else {
                //vise versa
                $("#salary_transfer_all").prop("checked", false);            
            }
       
 });

        });


     
</script>