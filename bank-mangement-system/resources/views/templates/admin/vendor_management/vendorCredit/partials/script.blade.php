<script type="text/javascript">
	$(document).ready(function(){
		

		

    	$('#credit_list').on("keyup", ".pay_amount", function () {
        	var sum = 0;
	        $('.pay_amount').each(function () {
	          if($(this).val()==0 || $(this).val()>0)
	          {
	            sum += Number($(this).val());
	          }
	        });
	        $('#total_amount').val(sum);

    	});

   
$("#payment_date").hover(function(){
      var date=$('#create_application_date').val(); 
      $('#payment_date').datepicker({
          format:"dd/mm/yyyy",
            todayHighlight: true, 
            autoclose:true, 
            orientation:"bottom",
            endDate:date, 
          }) 
   })



///-----------------------------------------------

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


	$.validator.addMethod("zero", function(value, element,p) {   
      if(parseFloat(value)>=0)
      {
        $.validator.messages.zero = "";
        result = true;
      }else{
        $.validator.messages.zero = "Amount must be greater than or equal to 0.";
        result = false;  
      }  
    return result;
  }, "");

  $.validator.addMethod("zero1", function(value, element,p) {   
      if(parseFloat(value)>0)
      {
        $.validator.messages.zero1 = "";
        result = true;
      }else{
        $.validator.messages.zero1 = "Amount must be greater than 0.";
        result = false;  
      }  
    return result;
  }, "");

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


  $.validator.addMethod("ckhA", function(value, element,p) {   
 // alert(this.optional(element)); 
 // alert(p); 
      if(this.optional(element) || /^\d*\.?\d*$/g.test(value)==true)
      {
        $.validator.messages.ckhA = "";
        result = true;
      }else{
        $.validator.messages.ckhA = "Please enter valid numeric number.";
        result = false;  
      } 
    return result;
  }, "");



  	$.validator.addClassRules({ 
		pay_amount:{  pay_amountR:  true,decimal:true,zero1:true,/*ckhA:true,*/},
			submitHandler: function (form) {   return false;   }  
	});
	$.validator.addMethod("pay_amountR", $.validator.methods.required,"Please enter amount.");


	/**************************************************************************** */
	$('#vendor_credit').validate({

		rules: { 
			v_name: "required", 
			credit_node: "required", 
			order_number: "required", 
			payment_date : {required: true,dateDdMm: true,},
			total_amount: {
				required : true, 
				decimal:true,
				zero1:true,
			},	

			total_amount_bill: {
				required : true, 
				decimal:true,
				zero1:true,
			},


		},
		messages: {  
			v_name: "Please select vendor.", 
			credit_node: "Please enter credit node.", 
			order_number: "Please enter order number.", 
			payment_date: "Please select date.", 
			total_amount: "Please enter  total amount.",
			total_amount_bill: "Please enter  total bill amount.",
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











})


function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : event.keyCode

    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode!=46 )
        return false;
    return true;
} 	


</script>