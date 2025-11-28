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
   $('.exportMembership').on('click',function(){
    var extension = $(this).attr('data-extension');
    $('#export').val(extension);
    $('form#filter').attr('action',"{!! route('admin.balance_sheet.branch_wise.membership.export') !!}");
    $('form#filter').submit();
    return true;
});

    member_ship = $('#member_ship').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $('html, body').stop().animate({

            scrollTop: ($('#member_ship').offset().top)

}, 10);
            $("td:nth-child(0)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.balance-sheet.get_member_ship_report_data') !!}",
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
               
                var page = ($('#member_ship').DataTable().page.info());
                var currentPage  = page.page+1;
                d.pages = currentPage,
                d.searchform=$('form#filter').serializeArray(),
                d.date= $('#start_date').val(),
                d.branch= $('#branch_filter').val(),
                d.date= $('#date').val(),
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
        {data: 'member_id', name: 'member_id'},
        {data: 'member_name', name: 'member_name'},
        {data: 'credit', name: 'credit'},
        {data: 'debit', name: 'debit'}, 
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
function searchmember_shipForm()
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
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/membership_fee?"+queryParams;
    }
    
function resetmember_shipForm()
{
    // location.reload();
    $('#is_search').val("no");  
   
    // var branch_id=$('#branch').val(); 
    var is_search=$('#is_search').val(); 
    var start_date=$('#default_date').val(); 
    var end_date=$('#default_end_date').val(); 
    var head= $('#head_id').val();

    var queryParams = new URLSearchParams(window.location.search);
    console.log(start_date,end_date);
    // Set new or modify existing parameter value. 
    queryParams.set("date", start_date);
    queryParams.set("end_date", end_date);
    queryParams.set("head", head);


    // Replace current querystring with the new one.
    window.location.href = "{{url('/')}}/admin/balance-sheet/current_liability/branch_wise/membership_fee?"+queryParams; 
}                                     
</script>
