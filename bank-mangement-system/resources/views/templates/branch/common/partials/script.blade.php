<script type="text/javascript">

$(document).ready(function() {

    var type = $('#type').val();
    branchCorrectionRequestTable = $('#branch-correction-listing').DataTable({
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
            "url": "{!! route('branch.correctionrequestlist') !!}",
            "type": "POST", 
            "data": {'type':type},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'created_at', name: 'created_at'},
            {data: 'branch', name: 'branch'},
             {data: 'branch_code', name: 'branch_code'},
            {data: 'sector', name: 'sector'},
            {data: 'regan', name: 'regan'},
            {data: 'zone', name: 'zone'},
            {data: 'in_context', name: 'in_context'},
            {data: 'correction', name: 'correction'},
            {data: 'status', name: 'status'},
        ]
    });
    $(branchCorrectionRequestTable.table().container()).removeClass( 'form-inline' );

    $(document).on('click','.correction-view-button',function(){
        var corrections = $(this).attr('data-correction-details');      
        $('.corrections-rejected').html('')
        $('.corrections-rejected').html(corrections)
    });

});

</script>

