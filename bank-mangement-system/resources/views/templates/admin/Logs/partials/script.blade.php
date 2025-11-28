<script type="text/javascript">
    $(document).ready(function(){
        loanLogstable = $('#loan_logs').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        lengthMenu: [10, 20, 40, 50, 100],
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {
            var oSettings = this.fnSettings ();
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('loan.logs.listing') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#filter').serializeArray()},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'loan_name', name: 'loan_name'},
            {data: 'status', name: 'status'},
            {data: 'status_changed_date', name: 'status_changed_date'},
            {data: 'changes_by', name: 'changes_by'},

        ],"ordering": false
        });
    $(loanLogstable.table().container()).removeClass( 'form-inline' );
    })    
</script>