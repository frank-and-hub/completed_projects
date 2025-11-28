<script type="text/javascript">
    var memberTable;
$(document).ready(function () {

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
  
   $('#payment_date').datepicker({
    format: "dd/mm/yyyy",
    todayHighlight: true, 
    endDate: date,  
    autoclose: true
  });
  

   memberTable = $('#tds_payable_listing').DataTable({
        processing: true,
        serverSide: true,
         pageLength: 20,
         lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({
            scrollTop: ($('#tds_payable_listing').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.tds_payable_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'join_date', name: 'join_date'},
            {data: 'branch', name: 'branch'},
            {data: 'branch_code', name: 'branch_code'},
            {data: 'sector_name', name: 'sector_name'},
            {data: 'region_name', name: 'region_name'},
            {data: 'zone_name', name: 'zone_name'}, 
            {data: 'name', name: 'name'}
        ],'ordering':false
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );
 
	$(document).on('click','.export_bill_payment',function(){
        var extension = $(this).attr('data-extension');
        $('#bill_payment_export').val(extension);
        $('form#bill_payment_form').attr('action',"{!! route('admin.bill_payment.export_bill_payment') !!}");
        $('form#bill_payment_form').submit();
        return true;
    });

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
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
        start_date:{ 
            dateDdMm : true,
          },
          end_date:{
            dateDdMm : true,
          },
          head_id :{ 
            number : true,
          },
          branch_id :{ 
            number : true,
          },  

      },
      messages: { 
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


});

function searchForm()
{  
    if($('#filter').valid())
    {
        $('#is_search').val("yes");
        memberTable.draw();
    }
}
function resetForm()
{
    var form = $("#filter"),
    validator = form.validate();
    validator.resetForm();
    form.find(".error").removeClass("error");
    $('#is_search').val("no");
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#head_id').val('');
    $('#start_date').val('');

    memberTable.draw();
}
 
</script>