<script type="text/javascript">

 $('document').ready(function(){

$('#start_date').datepicker({
        format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
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

$('#filter').validate({
      rules: {
        start_date:{ 
           // required: true,
            dateDdMm : true,
          }, 
         /* branch :{ 
           required: true,
          },*/  

      },
      messages: {  
          start_date:{ 
            required: "Please enter date.",
          },
          branch:{ 
            required: 'Please select branch.'
          },
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass(' ');
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

// $('.submit').on('click',function(){
//   location.reload();
// })
$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
  })

 function searchForm()
{  
    location.reload();
        $('#is_search').val("yes");

    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val(); 
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("branch", branch);
 
// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/{{$head}}/{{$label}}?"+queryParams;
    
}

function resetForm()
{
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val();  
    var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
queryParams.set("branch", branch);
 
// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/{{$head}}/{{$label}}?"+queryParams;
}

</script>