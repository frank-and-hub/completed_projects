<script type="text/javascript">

 $('document').ready(function(){
  var a = $('#t_amount').val();
   var ab = $('#h_amount').val();
   if(ab != 'NULL')
   {
    a = (parseInt(a) + parseInt(ab));
   }
  $('#a').html(a);
  $('#start_date').datepicker({
        format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
  $('#create_application_dates').datepicker({
    format: "dd/mm/yyyy",
        orientation: "bottom"
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

        $('#is_search').val("yes");

    var branch=$('#branch').val();
    var is_search=$('#is_search').val();
    var start_date=$('#start_date').val();
  var end_date=$('#create_application_dates').val();
  var financial_year = $('#financial_year').val();

     var queryParams = new URLSearchParams(window.location.search);
 // alert(start_date,end_date);
// Set new or modify existing parameter value.
queryParams.set("date", start_date);
queryParams.set("end_date", end_date);
queryParams.set("branch", branch);
queryParams.set("financial_year", financial_year);

// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/detail/{{$headDetail->head_id}}/{{$headDetail->labels}}?"+queryParams;
}

function resetForm()
{
  location.reload();
    $('#is_search').val("no");
    $('#branch').val("");
    $('#start_date').val("");
    var branch=$('#branch').val();
    var is_search=$('#is_search').val();
    var default_start_date=$('#default_date').val();
      var default_end_date=$('#default_end_date').val();
    var queryParams = new URLSearchParams(window.location.search);

// Set new or modify existing parameter value.
queryParams.set("date", default_start_date);
queryParams.set("end_date", default_end_date);

queryParams.set("branch", branch);

// Replace current querystring with the new one.
window.location.href = "{{url('/')}}/admin/balance-sheet/detail/{{$headDetail->head_id}}/{{$headDetail->labels}}?"+queryParams;
}


$('.export').on('click',function(){
  var extension = $(this).attr('data-extension');
  $('#export').val(extension);
  $('form#filter').attr('action',"{!! route('admin.balance_sheet_details.report.export') !!}");
  $('form#filter').submit();
});


$('#financial_year').on('change',function(){
      var financialYear = $(this).find('option:selected').val();
      var year = financialYear.split(' - ');

      const d = new Date();
      let curryear = d.getFullYear();

      var minDate = "01/04/"+year[0];
      var startDate = '01/04/'+year[0];
      var endDate = '31/03/'+year[1];
      $('#start_date').val( minDate );
      if ( year[1] <= curryear ) {
        var maxDate = "31/03/"+year[1];
        $('#create_application_dates').val(maxDate);
      } else {
        var month = d.getMonth() + 1; // Months start at 0!
        var day = d.getDate();
        var maxDate = day+'/'+month+'/'+curryear;

        $('#create_application_dates').val(maxDate);
      }
      $("#start_date").datepicker('remove');
      $('#start_date').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        autoclose: true,
        startDate: startDate,
        endDate: maxDate,
        setDate: new Date()
      });
      $("#create_application_dates").datepicker('remove');
      $('#create_application_dates').datepicker({
        format: "dd/mm/yyyy",
        orientation: "bottom auto",
        autoclose: true,
        startDate: startDate,
        endDate: maxDate,
        setDate: new Date()
    });
      console.log("TT", minDate, maxDate, curryear, startDate, endDate );

      var headList = $("#filter_data").find("a");
      headList.each(function( index ) {
        var link = $( this ).attr('href');
        console.log( index + ": " + $( this ).attr('href') );
        $(this).attr('href', link+'&financial_year='+financialYear);
      });
      console.log( "AA", headList);
      console.log("TT", minDate, maxDate, curryear, startDate, endDate );

    });

</script>
