<script type="text/javascript">

 $('document').ready(function(){
var a = $('#t_amount').val();
var amnt = $('#sta_amnt').val();
  $('#a').html(a);
  $('#stationaty_amnt').html('-&#X20B9;'+amnt);

  var financialYear = $('#financial_year').find('option:selected').val();
      var year = financialYear.split(' - ');

      const d = new Date();
      let curryear = d.getFullYear();

      var minDate = "01/04/"+year[0];
      var startDate = '01/04/'+year[0];
      var endDate = '31/03/'+year[1];
      if ( year[1] <= curryear ) {
        var maxDate = "31/03/"+year[1];
        $('#to_date').val(maxDate);
      } else {
        var month = d.getMonth() + 1; // Months start at 0!
        var day = d.getDate();
        var maxDate = day+'/'+month+'/'+curryear;
      } 

$('#date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom",
        autoclose: true,
        startDate: startDate,
          endDate: maxDate,
        setDate: new Date()
    })
  $('#to_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        autoclose: true,
        startDate: startDate,
        endDate: maxDate,
        setDate: new Date()
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

$('#filter').validate({
      rules: {
        date:{ 
           required: true,
            dateDdMm : true,
          }, 
           to_date :{ 
           required: true,
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


  $('.export').on('click',function(){
  var extension = $(this).attr('data-extension');
  $('#export').val(extension);
  $('form#filter').attr('action',"{!! route('admin.profit-loss.detail.report.export') !!}");
  $('form#filter').submit();
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

    $('#financial_year').on('change',function(){
      var financialYear = $(this).find('option:selected').val();
      var year = financialYear.split(' - ');

      const d = new Date();
      let curryear = d.getFullYear();

    
      $('#date').val( minDate );
   
      $("#date").datepicker('remove');
      $('#date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        autoclose: true,
        startDate: startDate,
        endDate: maxDate,
        setDate: new Date()
      });
      $("#to_date").datepicker('remove');
      $('#to_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        autoclose: true,
        startDate: startDate,
        endDate: maxDate,
        setDate: new Date()
      });
      var headList = $("#filter_data").find("a");
      headList.each(function( index ) {
        var link = $( this ).attr('href');
        console.log( index + ": " + $( this ).attr('href') );
        $(this).attr('href', link+'&financial_year='+financialYear);
      });
      console.log( "AA", headList);
      console.log("TT", minDate, maxDate, curryear, startDate, endDate );
      
    });

  })

 function searchForm()
{  
    
        $('#is_search').val("yes");

    var is_search=$('#is_search').val(); 
    var start_date=$('#date').val(); 
  var to_date=$('#to_date').val(); 
    console.log(start_date);
     var queryParams = new URLSearchParams(window.location.search);
 
// Set new or modify existing parameter value. 
queryParams.set("date", start_date);
 queryParams.set("to_date", to_date);

// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/profit-loss/detail/{{$headDetail->head_id}}/{{$headDetail->labels}}?"+queryParams;
}

function resetForm()
{
    $('#is_search').val("no");  
    var is_search=$('#is_search').val(); 
      var start_date=$('#default_date').val(); 
     var to_date=$('#default_end_date').val();
     var head=$('#head').val();
     var label=$('#labels').val();
     //alert(head);
    var queryParams = new URLSearchParams(window.location.search);
// Set new or modify existing parameter value. 
  queryParams.set("date", start_date);
  queryParams.set("to_date", to_date);
 
// Replace current querystring with the new one.
 window.location.href = "{{url('/')}}/admin/profit-loss/detail/{{$headDetail->head_id}}/{{$headDetail->labels}}?"+queryParams;
   
}

</script>