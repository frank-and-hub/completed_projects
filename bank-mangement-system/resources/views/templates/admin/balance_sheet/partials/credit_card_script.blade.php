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
  $('.exportCreditcard').on('click',function(){
    var extension = $(this).attr('data-extension');
    $('#export').val(extension);
    $('form#filter').attr('action',"{!! route('admin.balance_sheet.credit_card.export') !!}");
    $('form#filter').submit();
    return true;
});
   
    creditcardList = $('#credit_cardList').DataTable({
                            processing: true,
                            serverSide: true,
                            pageLength: 20,

                            lengthMenu: [10, 20, 40, 50, 100],

                            "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
                                var oSettings = this.fnSettings ();
                               
                                $('html, body').stop().animate({
                                    scrollTop: ($('#credit_cardList').offset().top),
                                    
                                }, 10);
                                $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                                return nRow;
                            },

                            ajax: {
                                "url": "{!! route('admin.balance-sheet.credit_card_listing') !!}",
                                "type": "POST",
                                "data":function(d,oSettings) {
                                   if(oSettings.json != null)
                                   {
                                    $('#total_balance').val(oSettings.json.total);
                                    // var total = oSettings.json.total;
                                   }
                                   else{
                                     $('#total_balance').val(0);
                                   }
                                   
                                    var page = ($('#credit_cardList').DataTable().page.info());
                                    var currentPage  = page.page+1;
                                    d.pages = currentPage,
                                    d.searchform=$('form#filter').serializeArray(),
                                    d.date= $('#start_date').val(),
                                    d.end_date= $('#ends_date').val(),
                                    d.head_id= $('#head_id').val(),
                                    d.total=$('#total_balance').val()

                                           
                                },
                                
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                            }, 
                           
                            columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                            {data: 'date', name: 'date'},
                            {data: 'type', name: 'type'},
                             {data: 'narration', name: 'narration'},
                            {data: 'holder_name', name: 'holder_name'},
                            {data: 'card_number', name: 'card_number'},  
                            {data: 'bank_name', name: 'bank_name'},
                            {data: 'bank_ac_number', name: 'bank_ac_number'}, 
                            {data: 'cr', name: 'cr'},
                            {data: 'dr', name: 'dr'},  
                            {data: 'balance', name: 'balance'},

                            ],"ordering": false,
                           
                        }); 
    
   

$( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


    
});
function searchCashInHandCreditorsForm()
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
    window.location.href = "{{url('/')}}/admin/balance-sheet/credit_card?"+queryParams;
    }
    
function resetCashInHandCreditorsForm()
{
    location.reload();
    $('#is_search').val("no");  
    $('#branch').val(""); 
    $('#start_date').val(""); 
    var branch=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#default_date').val(); 
    var end_date=$('#default_end_date').val(); 
    var head= $('#head_id').val();
    var queryParams = new URLSearchParams(window.location.search);

    // Set new or modify existing parameter value. 
    queryParams.set("date", start_date);
    queryParams.set("end_date", end_date);
    queryParams.set("head", head);


    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/credit_card?"+queryParams; 
}                                     
</script>
