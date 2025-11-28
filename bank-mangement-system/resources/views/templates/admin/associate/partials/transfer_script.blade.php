<script type="text/javascript">
  $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    }); 
$(document).ready(function () {
  //ConfirmDialog();

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

  


});

$('#filter').validate({
      rules: {
        start_date:{ 
            dateDdMm : true,
            required: true,
          },
          end_date:{
            dateDdMm : true,
            required: true,
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

function resetForm()
{
  $('#start_date').val('');
  $('#end_date').val('');
  $('#hide_div').hide();
    var validator = $( "#filter" ).validate();
    validator.resetForm();
}

$('#submit_transfer').click(function() {

  start_date=$('#start_date_time').val();
  end_date=$('#end_date_time').val();
  //alert($start_date);
        $.ajax({
              type: "POST",  
              url: "{!! route('admin.laserCheck') !!}",
              dataType: 'JSON',
              data: {'start_date':start_date,'end_date':end_date},
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function(response) { 
                if(response.count==0)
                {
                    swal({
                    title: "Are you sure?",
                    text: "You want to create Ledger payment or amount transfer to associate SSB account",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-primary le_confirm",
                    confirmButtonText: "Yes, Create & Transfer",
                    cancelButtonText: "No",
                    cancelButtonClass: "btn-danger le_cancel",
                    closeOnConfirm: false,
                    closeOnCancel: true
                  },
                  function(isConfirm) {
                    if (isConfirm) {
                      $('#transfer').submit()  ;
                      $('.le_confirm').attr('disabled',true);
                      $('.le_cancel').attr('disabled',true);
                    }
                  });
                }
                else
                {
                  swal("Date Range Exits", "You can use other date range", "warning");
                }

              }
          });


   

});
</script>