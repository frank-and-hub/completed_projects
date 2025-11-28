<script src="https://momentjs.com/downloads/moment.js"></script>
<script type="text/javascript">
    var passbookTable;
$(document).ready(function () {
    var date11 = new Date();
    moment.defaultFormat = "DD/MM/YYYY";
    var date = moment(date11, moment.defaultFormat).toDate();

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
            "url": "{!! route('branch.passbook_listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#fillter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'},
            {data: 'account_no', name: 'account_no'},
            {data: 'branch_name', name: 'branch_name'},
            {data: 'plan', name: 'plan'},
            {data: 'member', name: 'member'},
            {data: 'member_id', name: 'member_id'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ],"ordering": false
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
	$('#is_search').val("no");
	$('#member_name').val('');
    $('#member_id').val('');
    $('#branch_id').val('');
    $('#end_date').val('');
    $('#end_date').val('');
    $('#start_date').val('');

	passbookTable.draw();
}

</script>