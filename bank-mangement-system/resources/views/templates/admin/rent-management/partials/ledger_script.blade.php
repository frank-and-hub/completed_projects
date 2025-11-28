<script type="text/javascript">
  window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
  var date = new Date();
  var today = new Date(date.getFullYear()-18, date.getMonth(), date.getDate());
  var ntoday = new Date(date.getFullYear(), date.getMonth(), date.getDate());
$(document).ready(function () {
  calculateSum();
  $('#rent_year').trigger('change');
// $("#select_date").hover(function(){
//       var date=$('#create_application_date').val();
//   var ledgerDate = '01/{{$re_month11}}/{{$re_year1}}';
//       $('#select_date').datepicker({
//           format:"dd/mm/yyyy",
//             endHighlight: true, 
//             autoclose:true,
//             endDate:date, 
//             startDate:ledgerDate,
//           })
//    })
  //  $('#rent_year').on('change',function(){
  //     let year = $(this).val();
  //  }) 
  $('#filter').validate({
      rules: { 
          rent_month: "required",
          rent_year: "required", 
      },
      messages: {  
          rent_month: "Please select month.",
          rent_year: "Please select year.",  
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
jQuery.fn.serializeObject = function(){
        var o = { };
		var a = this.serializeArray();
		jQuery.each(a, function() {
				if (o[this.name] !== undefined) {
					if (!o[this.name].push) {
			o[this.name] = [o[this.name]];
					}
		o[this.name].push(this.value || '');
				} else {
			o[this.name] = this.value || '';
				}
			});
		return o;
    };
$('.ledger_create_export').on('click',function(e){	
        e.preventDefault();		
		var extension = $(this).attr('data-extension');
		var formData = $('#filter').serializeObject();
		var chunkAndLimit = 50;
		$(".spiners").css("display","block");
		$(".loaders").text("0%");
		doChunkedExport(0,chunkAndLimit,formData,chunkAndLimit);
		$("#cover").fadeIn(100);
    });
    // function to trigger the ajax bit
    function doChunkedExport(start,limit,formData,chunkSize){
        formData['start'] = start;
		formData['limit']  = limit;
		formData['company_id']  = $('#company_idd').val();
    
		$.ajax({
			type : "post",
			dataType : "json",
			url :  "{!!route('admin.rent.ledger-export')!!}",
			data : formData,
			success: function(response) {
			console.log(response);
			if(response.result=='next'){
				start = start + chunkSize;
				doChunkedExport(start,limit,formData,chunkSize);
				$(".loaders").text(response.percentage+"%");
			}else{
				var csv = response.fileName;
				console.log('DOWNLOAD');
				$(".spiners").css("display","none");
				$("#cover").fadeOut(100);
				window.open(csv, '_blank');
                }
              }
          });
	}
$.validator.addClassRules({ 
      transfer_amount:{ transfer_amountRequried:  true, decimal: true, zero: true},
        submitHandler: function (form) {   return false;   }  
});
$.validator.addMethod("transfer_amountRequried", $.validator.methods.required,"Please enter transfer amount");
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
$.validator.addMethod("zero", function(value, element,p) {     
      if(value>=0)
      {
        $.validator.messages.zero = "";
        result = true;
      }else{
        $.validator.messages.zero = "Amount must be greater than or equal to 0.";
        result = false;  
      }
    return result;
  }, "");
$.validator.addMethod("chk_tra", function(value, element,p) {     
      id = $(element).attr('id');
    var res = id.substr(16); 
    actual_amount  = $('#rent_amount_'+res).val();  
    if(value>actual_amount)
    {
      $.validator.messages.chk_tra = "Rent amount  must be greater than transfer amount";
        result = false; 
    }
    else
    {
      $.validator.messages.chk_tra = "";
        result = true;
    }
    return result;
  }, "");
  $('#rent_generate').validate({
      rules: { 
          ledger_month: "required",
          ledger_year: "required",
          rent_lib_id: "required", 
          select_date: "required", 
      },
      messages: {  
          ledger_month: "Please select month.",
          ledger_year: "Please select year.",
          rent_lib_id: "Please select rent labilities.", 
          select_date: "Please select Date.",  
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
      submitHandler: function (){
    $('button[type="submit"]').prop('disabled',true);
    return true;
  }

  });
  $('.amount').on('keyup',function(){
    $(this).val($(this).val().replace(/[^0-9.]/g, ""));
      const amount  =  $(this).val();
      const rowIndex  =  $(this).attr('data-index');
      // const tdsApplicable = $('#tdsApplicable_'+rowIndex).val();
      const rentAmount = $('#rent_amount_'+rowIndex).val();
      // const tdsPercentage = $('#tdsPercentage_'+rowIndex).val();
      // const tdsAmount = $('#tds_amount_actual_'+rowIndex).val();
      const transfer_amount = $('#transfer_amount_'+rowIndex).val();
      // const updatedTds = (tdsApplicable == 0) ?   tdsPercentage*amount/100   : 0;    
      const updatedTds = $('#tds_amount_'+rowIndex).val();    
      const updatedTdsAmount =   updatedTds ;
      // const updatedTdsAmount = (rentAmount != amount) ?  updatedTds :  tdsAmount;
      // const totalTds = $('#totalTds').text();
    //  console.log(updatedTdsAmount);
      // $('#tds_amount_'+rowIndex).val(updatedTdsAmount);
      if(Number(updatedTdsAmount) >= Number(amount)){
        swal("Warning!", "Tds cannot be equal or more then amount please correct this first", "warning");
        $("#submit_transfer").prop('disabled',true);
        $('.tds_amount').prop('disabled',true);
        $('.amount').prop('disabled',true);
        $(this).prop('disabled',false);
      } else{
        $("#submit_transfer").prop('disabled',false);
        $('.tds_amount').prop('disabled',false);
        $('.amount').prop('disabled',false);
      }
     
      $('#transfer_amount_'+rowIndex).val(amount-updatedTdsAmount);
      // $('#transfer_amount_'+rowIndex).val(rentAmount-amount);
      calculateSum();
    });
    $('.tds_amount').on('keyup',function(){
      $(this).val($(this).val().replace(/[^0-9.]/g, ""));
     const amount  =  $(this).val();
     const rowIndex  =  $(this).attr('data-index');
     // const tdsApplicable = $('#tdsApplicable_'+rowIndex).val();
     const rentAmount = $('#rent_amount_'+rowIndex).val();
     // const tdsPercentage = $('#tdsPercentage_'+rowIndex).val();
     // const tdsAmount = $('#tds_amount_actual_'+rowIndex).val();
     const transfer_amount = $('#amount_'+rowIndex).val();
     console.log(transfer_amount,amount);
     // const updatedTds = (tdsApplicable == 0) ?   tdsPercentage*amount/100   : 0;    
     // const updatedTdsAmount = (rentAmount != amount) ?  updatedTds :  tdsAmount;
     // const totalTds = $('#totalTds').text();
   //  console.log(updatedTdsAmount);
     // $('#tds_amount_'+rowIndex).val(updatedTdsAmount);
    //  alert(amount);
    //  alert(transfer_amount);
     if(Number(amount) >= Number(transfer_amount)){
        swal("Warning!", "Tds cannot be equal or more then amount please correct this first", "warning");
        $("#submit_transfer").prop('disabled',true);
        $('.tds_amount').prop('disabled',true);
        $('.amount').prop('disabled',true);
        $(this).prop('disabled',false);
        $(this).val(0);
        $('.tds_amount').trigger('keyup');
        // return false;
      } else{
        $("#submit_transfer").prop('disabled',false);
        $('.tds_amount').prop('disabled',false);
        $('.amount').prop('disabled',false);
        $('#transfer_amount_'+rowIndex).val(transfer_amount-amount);
      }
     // $('#transfer_amount_'+rowIndex).val(rentAmount-amount);
     calculateSum();
   });
      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });
       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });
// $(".transfer_amount").each(function() {
//       $(this).keyup(function(){
//         calculateSum();
//       });
//     });
});
// function calculateSum() {
//     var sum = 0;
//     //iterate through each textboxes and add the values
//     $(".transfer_amount").each(function() {
//       //add only if the value is number
//       if(!isNaN(this.value) && this.value.length!=0) {
//         sum += parseFloat(this.value);
//       }
//     });
//     //.toFixed() method will roundoff the final sum to 2 decimal places
//     $("#sum").html(sum.toFixed(2));
//   }
function calculateSum() {
    var sum = 0;
    let transferAmount = 0;
    let rentAmount = 0;
    //iterate through each textboxes and add the values
    $(".tds_amount").each(function() {
      //add only if the value is number
      if(!isNaN(this.value) && this.value.length!=0) {
        sum += parseFloat(this.value);
      }
    });
    $(".transfer_amount").each(function() {
      //add only if the value is number
      if(!isNaN(this.value) && this.value.length!=0) {
        transferAmount += parseFloat(this.value);
      }
    });
    $(".amount").each(function() {
      //add only if the value is number
      if(!isNaN(this.value) && this.value.length!=0) {
        rentAmount += parseFloat(this.value);
      }
    });
    // $(".rent_amount").each(function() {
    //   //add only if the value is number
    //   if(!isNaN(this.value) && this.value.length!=0) {
    //     rentAmount += parseFloat(this.value);
    //   }
    // });
    //.toFixed() method will roundoff the final sum to 2 decimal places
    $("#totalTds").html(sum.toFixed(2));
    $("#sum").html(transferAmount.toFixed(2));
    $("#rentAmount").html(rentAmount.toFixed(2));
  }
  function resetForm()
{
    $('#month').val("");
    $('#year').val(""); 
    $('#company_id').val(""); 
    $('#hide_div').hide();
    window.location.href = window.location.href
}
$("#rent_year").change(function(){
    var thisYear = $(this).val();
    var currentYearr = $('#currentYear').val();
    var currentMonth = $('#currentMonth').val();

    const months = ["none","January","February","March","April","May","June","July","August","September","October","November","December"];
    const d = new Date();//d.getMonth()
    $("#rent_month").html('<option value="">----Select Month----</option>');
        if(thisYear === '2021'){
            for(i = 4; i <= 12; i++){
                $("#rent_month").append('<option value="'+i+'">'+months[i]+'</option>');
            }
        }else if(currentYearr == thisYear){
          for(i = 1; i <= 12; i++){
                $("#rent_month").append('<option value="'+i+'">'+months[i]+'</option>');
                if (currentMonth == i) {
                  break;
                }
            }
        }
        else if(thisYear != ''){
            for(i = 1; i <= 12; i++){
                $("#rent_month").append('<option value="'+i+'">'+months[i]+'</option>');
            }
        }
        $("#rent_month").val('{{$re_month1}}');
})
</script>