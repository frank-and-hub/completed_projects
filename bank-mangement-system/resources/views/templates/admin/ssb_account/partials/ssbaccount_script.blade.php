


<script type="text/javascript">
$(document).ready(function () {

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

$('#ssbaccount_register').validate({
      rules: {
         
          amount: {
            required: true,
            decimal:true,
          }, 
          plan_type: "required", 
		  user_type: "required", 
          

      },
      messages: {         
          amount: {
            required: "Please enter form amount.",
          }, 
          plan_type: {
            required: "Please enter plan type.",
          },
          user_type: {
            required: "Please enter user type .",
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

  $("#plan_type").change(function() {
    var plan = $(this).val();
    if(plan == 2){
      $("#user_type option[value='2']").remove();
    }
    else{
      var v = $("#user_type option[value='2']").val();
      //console.log(v);
      if(v == undefined){
        $("#user_type option[value='2']").add();
        var $option = $("<option/>", {
              value: 2,
              text: 'Associate'
            });
        $('#user_type').append($option);
      }
    }
  });

});




 
</script>


<script type="text/javascript">
	$(document).ready(function() {
    $("#ssbac_detail").hide();

    $("#search").click(function(){
     
        //event.preventDefault();
        var form_data= $('#filter').serialize();
        $.ajax({
                url:"{!! route('admin.ssbaccount.ssbaccountdetailssearch') !!}",
                type:'POST',
                data:form_data, 
                //data:{'loan_account_number':loan_account_number}, 
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(result){                    
                    if(result == 'error'){
                        $("#ssbac_detail").hide();
                        swal("Warning!", "Recording Not Fount !!", "warning");
                        return false;  
                    }else{                                              
                      $("#ssbac_detail").show();
                      $("#data").html(result);
                    }                  
                }

        });             
    });
  });
</script>