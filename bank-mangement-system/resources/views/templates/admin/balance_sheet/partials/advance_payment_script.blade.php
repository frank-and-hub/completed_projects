<script type="text/javascript">

 $('document').ready(function(){
$('#start_date').datepicker({
        //format: "dd/mm/yyyy",
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
    
    $('#ends_date').datepicker({
        //format: "dd/mm/yyyy",
            format: "dd/mm/yyyy",
            orientation: "bottom",
            autoclose: true,
            setDate: new Date()
    })
// 
$('#filter').validate({
      rules: {
        date:{ 
           required: true,
          },
          to_date :{ 
           required: true,
          },  
      },
      messages: {  
          date:{ 
            required: "Please enter date.",
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

$('.exportAdvancePayment').on('click',function(){
    var extension = $(this).attr('data-extension');
    $('#export').val(extension);
    $('form#filter').attr('action',"{!! route('admin.balance_sheet.branch_wise.advance_payment.export') !!}");
    $('form#filter').submit();
    return true;
});
   
 advancePaymentCreditorsList = $('#advancePaymentCreditorsList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,
                            lengthMenu: [10, 20, 40, 50, 100],
                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                                $('html, body').stop().animate({
                                    scrollTop: ($('#advancePaymentCreditorsList').offset().top)
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },
                            ajax: {
                                "url": "{!! route('admin.balance-sheet.get_advance_payment_report_listing') !!}",
                                "type": "POST",
                                "data":function(d) {
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.branch= $('#branch_filter').val(),
                                    d.head_id= $('#head_id').val()
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'name', name: 'name'},
                            {data: 'code', name: 'code'}, 
                            {data: 'cr', name: 'cr'},  
                            {data: 'dr', name: 'dr'},  
                            {data: 'amount', name: 'amount'},  
                            ],"ordering": false,
                        });     



$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


    
});
function searchAdvacnePaymentCreditorsForm()
{  
    $('#is_search').val("yes");
    var is_search=$('#is_search').val(); 
    var start_date=$('#start_date').val(); 
    var end_date= $('#ends_date').val();
    var queryParams = new URLSearchParams(window.location.search);

    // Set new or modify existing parameter value. 
    queryParams.set("date", start_date);
    queryParams.set("end_date", end_date);

    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/advance_payment?"+queryParams;
    }
    
function resetAdvacnePaymentForm()
{
    location.reload();
    $('#is_search').val("no");  
    $('#branch').val(""); 
    var branch_id=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#default_date').val(); 
    var end_date=$('#default_end_date').val(); 
    var head= $('#head_id').val();
    var queryParams = new URLSearchParams(window.location.search);

    // Set new or modify existing parameter value. 
    queryParams.set("date", start_date);
    queryParams.set("end_date", end_date);
    queryParams.set("branch_id", branch_id);
    queryParams.set("head", head);


    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/advance_payment?"+queryParams; 
}                                     
</script>
