<script type="text/javascript">
var accountHeadLedgerReport;
$(document).ready(function() {
alert("DF");
    accountHeadLedgerReport = $('#account_head_ledger_report').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.account_head.ledger.list') !!}",
            "type": "POST",
            "data":function(d) {

                
                d.searchform=$('form#filter').serializeArray(), 
                
            
            }, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'}, 
            {data: 'branch_name', name: 'branch_name'},
            {data: 'branch_code', name: 'branch_code'}, 
            {data: 'sector', name: 'sector'}, 
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'}, 
            {data: 'type', name: 'type'}, 
            {data: 'description', name: 'description'}
            {data: 'ac', name: 'ac'},           
            {data: 'member_name', name: 'member_name'}, 
            {data: 'associate_name', name: 'associate_name'},
            {data: 'payment_type', name: 'payment_type'}, 
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'voucher_no', name: 'voucher_no'}, 
            {data: 'voucher_date', name: 'voucher_date'},
            {data: 'cheque_no', name: 'cheque_no'}, 
            {data: 'cheque_date', name: 'cheque_date'}
            {data: 'utr_transaction_number', name: 'utr_transaction_number'}, 
            {data: 'transaction_date', name: 'transaction_date'},
            // {data: 'bank_name', name: 'bank_name'}, 
            // {data: 'bank_account_number', name: 'bank_account_number'},
            {data: 'received_bank', name: 'received_bank'}, 
            {data: 'received_bank_account', name: 'received_bank_account'},
            
        ],"ordering": false,
    });
    $(accountHeadLedgerReport.table().container()).removeClass( 'form-inline' );

$('.export').on('click',function(){
        $('form#filter').attr('action',"{!! route('admin.ledger.export') !!}");
        $('form#filter').submit();
        return true;
    });


    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});


</script>