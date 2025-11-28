<?php
    $start_date='';
    $end_date='';
    if(isset($is_fillter))
    {
        $start_date=$startDate;

        $end_date=$endDate;
    }
?>

<script type="text/javascript">
$(document).ready(function () {
  var a='{{$start_date}}';
if(a!='')
{
$('html, body').stop().animate({
            scrollTop: ($('#member_account_statement').offset().top)
        }, 1000);
}
  $('#start_date').val('{{$start_date}}');
  $('#end_date').val('{{$end_date}}');
  var date = new Date();
  $('#start_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true,  
    endDate: date, 
    autoclose: true
  });

  $('#end_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true, 
    endDate: date,  
    autoclose: true
  });


$.validator.addMethod("dateDdMm", function(value, element,p) {
     
      if(this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/g.test(value)==true)
      {
        $.validator.messages.dateDdMm = "";
        result = true;
      }else{
        $.validator.messages.dateDdMm = "Please enter valid date";
        result = false;  
      }
    
    return result;
  }, "");

  $('#fillter').validate({
      rules: {
        start_date: {
            required: true,
            dateDdMm : true,
          }, 
          end_date: {
            required: true,
            dateDdMm : true,
          },  

      },
      messages: {
          
          start_date: {
            required: "Please enter start date.", 
          },
          end_date: {
            required: "Please enter end date.", 
          },
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.error-msg').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).addClass('is-invalid');
          });
        }
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
        if($(element).attr('type') == 'radio'){
          $(element.form).find("input[type=radio]").each(function(which){
            $(element.form).find("label[for=" + this.id + "]").addClass(errorClass);
            $(this).removeClass('is-invalid');
          });
        }
      }
  });
  
   


      $( document ).ajaxStart(function() { 
          $( ".loader" ).show();
       });

       $( document ).ajaxComplete(function() {
          $( ".loader" ).hide();
       });

});

function printDiv(elem) {
   $("#"+elem).print({
                    //Use Global styles
                    globalStyles : false,
                    //Add link with attrbute media=print
                    mediaPrint : false,
                    //Custom stylesheet
                    stylesheet : "http://mobile.host4india.in/micro_finance/asset/dashboard/vendor/datatables.net-bs4/css/dataTables.bootstrap4.min.css",
                    //Print in a hidden iframe
                    iframe : false,
                    //Don't print this
                    noPrintSelector : ".avoid-this",
                    //Add this at top
                    prepend : "Hello World!!!<br/>",
                    //Add this on bottom
                    append : "<span><br/>Buh Bye!</span>",
                    //Log to console when printing is done via a deffered callback
                    deferred: $.Deferred().done(function() { console.log('Printing done', arguments); })
                });
}
$( "#reset_form" ).click(function() {
  $("#listPassbook").empty(); 
  $('#printBtn').hide();
});
 

 
</script> 