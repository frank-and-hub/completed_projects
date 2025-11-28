


<script type="text/javascript">
 
  window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});

$(document).ready(function () {



   
$.validator.addClassRules({ 
      rent_transferSum :{ checkedCount:  true,},
        submitHandler: function (form) {    return false;   }  
});
$.validator.addMethod("checkedCount", function(value, element,p) {  
var $checkboxes = $('input[type="checkbox"]'); 

      if(($checkboxes.filter(':checked').length)>0 && $('.rent_transferSum').length>0)
      {
        $.validator.messages.checkedCount = "";
        result = true;
      }else{
        $.validator.messages.checkedCount = "Please select list.";
        result = false;  
      }
    
    return result;
  }, "");


  $('#rent_transfer').validate({
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

$(document).on('click','.rent_transferSum',function(){ 
    
var total = 0;
var settel_amount1 = 0;
var actual_transfer_amount1 = 0;
var id='';
        $(".rent_transferSum:checked").each(function() {
    
            
            
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

                //settel_amount = $(this).closest("tr").find(".settel_amount").val(); 
               // settel_amount1 +=parseFloat(settel_amount);

               // actual_transfer_amount = $(this).closest("tr").find(".actual_transfer_amount").val(); 
               // actual_transfer_amount1 +=parseFloat(actual_transfer_amount);

              }
            }
            else
            {
              total += parseFloat($(this).val());
             // settel_amount = $(this).closest("tr").find(".settel_amount").val(); 
             // settel_amount1 +=parseFloat(settel_amount);

             // actual_transfer_amount = $(this).closest("tr").find(".actual_transfer_amount").val(); 
            // actual_transfer_amount1 +=parseFloat(actual_transfer_amount);

              id = $(this).closest("tr").find(".id_get").val()+','+id;
              $('#select_id').val(id);
            }
            
        }); 
        if (total == 0) {
            //$('#total_transfer_amount').html('<strong>0</strong>');
           // $('#total_settle_amount').html('<strong>0</strong>');
          //  $('#total_actule_amount').html('<strong>0</strong>');
           $('#total_payble').html('<strong>0.00</strong>');
            //$('#total_transfer').val('');
        } else {
            total1=parseFloat(total).toFixed(2);
           // settel_amount2 =parseFloat(settel_amount1).toFixed(4);
          //  actual_transfer_amount2 =parseFloat(actual_transfer_amount1).toFixed(4);
          //  $('#total_transfer_amount').html('<strong>'+total1+'</strong>');
           // $('#total_settle_amount').html('<strong>'+settel_amount2+'</strong>');
          //  $('#total_actule_amount').html('<strong>'+actual_transfer_amount2+'</strong>');
            $('#total_payble').html('<strong>'+total1+'</strong>');
            //$('#total_payble').val(total1);
        }

        if($(".rent_transferSum").length == $(".rent_transferSum:checked").length) { 
                 //if the length is same then untick 
                $("#rent_transfer_all").prop("checked", true);
            }else {
                //vise versa
                $("#rent_transfer_all").prop("checked", false);            
            }
           

  });

$('#rent_transfer_all').click(function(){
            if($('#rent_transfer_all').prop("checked")) {
                $(".rent_transferSum").prop("checked", true);
            } else {
                $(".rent_transferSum").prop("checked", false);
            } 

var total = 0;
var settel_amount1 = 0;
var actual_transfer_amount1 = 0;
var id='';
        $(".rent_transferSum:checked").each(function() {
             
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

           /*     settel_amount = $(this).closest("tr").find(".settel_amount").val(); 
              settel_amount1 +=parseFloat(settel_amount);

              actual_transfer_amount = $(this).closest("tr").find(".actual_transfer_amount").val(); 
              actual_transfer_amount1 +=parseFloat(actual_transfer_amount);*/

              $('#select_id').val(id);
              }
            }
            else
            {
              total += parseFloat($(this).val());
              id = $(this).closest("tr").find(".id_get").val()+','+id;
             /* settel_amount = $(this).closest("tr").find(".settel_amount").val(); 
              settel_amount1 +=parseFloat(settel_amount);

              actual_transfer_amount = $(this).closest("tr").find(".actual_transfer_amount").val(); 
              actual_transfer_amount1 +=parseFloat(actual_transfer_amount);*/

              $('#select_id').val(id);
            }
            
        }); 

        if (total == 0) {
           /* $('#total_transfer_amount').html('<strong>0</strong>');
            $('#total_settle_amount').html('<strong>0</strong>');
            $('#total_actule_amount').html('<strong>0</strong>');*/
            $('#total_payble').html('<strong>0.00</strong>');
          //  $('#total_transfer').val('');
        } else {
          total1=parseFloat(total).toFixed(2);
           /* settel_amount2 =parseFloat(settel_amount1).toFixed(4);
            actual_transfer_amount2 =parseFloat(actual_transfer_amount1).toFixed(4);
            $('#total_transfer_amount').html('<strong>'+total1+'</strong>');
            $('#total_settle_amount').html('<strong>'+settel_amount2+'</strong>');
            $('#total_actule_amount').html('<strong>'+actual_transfer_amount2+'</strong>');*/
            $('#total_payble').html('<strong>'+total1+'</strong>');
           // $('#total_transfer').val(total1);
        }               
  });

$('#amount_mode').change(function(){
var total = 0;
var settel_amount1 = 0;
var actual_transfer_amount1 = 0;
var id='';

        $(".rent_transferSum:checked").each(function() { 
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

           /*     settel_amount = $(this).closest("tr").find(".settel_amount").val(); 
              settel_amount1 +=parseFloat(settel_amount);

              actual_transfer_amount = $(this).closest("tr").find(".actual_transfer_amount").val(); 
              actual_transfer_amount1 +=parseFloat(actual_transfer_amount);*/

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
           /* $('#total_transfer_amount').html('<strong>0</strong>');
            $('#total_settle_amount').html('<strong>0</strong>');
            $('#total_actule_amount').html('<strong>0</strong>');*/
            $('#total_payble').html('<strong>0.00</strong>');
            //$('#total_transfer').val('');
        } else {
            total1=parseFloat(total).toFixed(2);
           /* settel_amount2 =parseFloat(settel_amount1).toFixed(4);
            actual_transfer_amount2 =parseFloat(actual_transfer_amount1).toFixed(4);
            $('#total_transfer_amount').html('<strong>'+total1+'</strong>');
            $('#total_settle_amount').html('<strong>'+settel_amount2+'</strong>');
            $('#total_actule_amount').html('<strong>'+actual_transfer_amount2+'</strong>');*/
            $('#total_payble').html('<strong>'+total1+'</strong>');
            //$('#total_transfer').val(total1);
        }

        if($(".rent_transferSum").length == $(".rent_transferSum:checked").length) { 
                 //if the length is same then untick 
                $("#rent_transfer_all").prop("checked", true);
            }else {
                //vise versa
                $("#rent_transfer_all").prop("checked", false);            
            }
       
 });


 });
</script>