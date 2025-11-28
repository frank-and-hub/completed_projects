<script type="text/javascript">
$(document).ready(function () { 
  var tot_check = 25;

  $.validator.addClassRules({ 
    rent_transferSum :{ checkedCount:  true,checkedCount1:true,},
    submitHandler: function (form) {    return false;   }  
  });
  $.validator.addMethod("checkedCount", function(value, element,p) {  
    var $checkboxes = $('input[type="checkbox"]'); 

    if(($checkboxes.filter(':checked').length)>0){
      $.validator.messages.checkedCount = "";
      result = true;
    }else{
      $.validator.messages.checkedCount = "Please select list.";
      result = false;  
    }
    return result;
  }, "");


  $.validator.addMethod("checkedCount1", function(value, element,p) {  
    var $checkboxes = $('input[type="checkbox"]'); 

    if(($checkboxes.filter(':checked').length)<=tot_check){
      $.validator.messages.checkedCount1 = "";
      result = true;
    }else{
      $.validator.messages.checkedCount1 = "Please select Only 25 record.";
      result = false;  
    }
    return result;
  }, "");


  $('#transfer').validate({
    rules: { 
          select_id: "required", 
        },
        messages: {  
      select_id: "Please select .", 
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
    },
    submitHandler: function() {
            $('#submit_transfer').prop('disabled', true);
                return true;             
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
var id='';
$('#select_id').val(id); 
        $(".rent_transferSum:checked").each(function() { 
               
                total += parseFloat($(this).val());
                id = $(this).closest("tr").find(".id_get").val()+','+id;
                $('#select_id').val(id);              
            
        }); 
        if (total == 0) { 
           $('#total_transfer_amount').html('<strong>0.00</strong>'); 
        } else {
            total1=parseFloat(total).toFixed(2);
            $('#total_transfer_amount').html('<strong>'+total1+'</strong>'); 
        }
  });
 


 

  var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
      todayHighlight: true,  
      endDate: date,
      orientation: 'bottom',
      autoclose: true
      }).on('change', function(){
      moment.defaultFormat = "DD/MM/YYYY HH:mm"; 
      var birthday = moment(''+$('#start_date').val()+' 00:00', moment.defaultFormat).toDate();
      var date = new Date(birthday), y = date.getFullYear(), m = date.getMonth();
    var lastDay = new Date(y, m + 1, 0);
    var dd = String(lastDay.getDate()).padStart(2, '0');
    var mm = String(lastDay.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = lastDay.getFullYear();
    
    end = dd + '/' + mm + '/' + yyyy;

    $('#end_date').val(end);

  });
});
</script>