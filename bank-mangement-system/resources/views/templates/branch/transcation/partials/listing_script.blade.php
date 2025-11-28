<script type="text/javascript">
    var passbookTable;
$(document).ready(function () {
 
  var date = new Date();
  $('#start_date').datepicker({
    format: "mm/dd/yyyy",
    todayHighlight: true,  
    endDate: date, 
    autoclose: true
  });

  $('#end_date').datepicker({
    format: "mm/dd/yyyy",
    todayHighlight: true, 
    endDate: date,  
    autoclose: true
  });

	

	passbookTable = $('#passbook').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings (); 
           $('html, body').stop().animate({
            scrollTop: ($('#passbook').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('branch.transactions_lists') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#fillter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
              {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'},
            {data: 'branch_name', name: 'branch_name'},  
            {data: 'branch_code', name: 'branch_code'}, 
            {data: 'sector', name: 'sector'},
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'},
            {data: 'member', name: 'member'},
            {data: 'member_id', name: 'member_id'},
            
            {data: 'tran_type', name: 'tran_type'}, 
            {data: 'tran_account', name: 'tran_account'}, 


            {data: 'amount', name: 'amount',
                "render":function(data, type, row){
                if ( row.amount ) {
                    return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
            {data: 'detail', name: 'detail'}, 
            {data: 'payment_type', name: 'payment_type'},
            {data: 'payment_mode', name: 'payment_mode'},
            
            
        ]
    });
    
    $(passbookTable.table().container()).removeClass( 'form-inline' );

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });
    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });

});

function searchForm()
{ 
	$('#is_search').val("yes");
	passbookTable.draw();
}
function resetForm()
{
	$('#is_search').val("yes");
	
    $('#end_date').val('');
    $('#start_date').val('');

	passbookTable.draw();
}

</script>