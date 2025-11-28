<script type="text/javascript">
$(document).ready(function () {
var member_id='{{$memberDetail->id}}';
const company_id = $('#company_id').val();
var memberTable = $('#member_investment_listing').DataTable({
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
            "url": "{!! route('branch.member_investmentlisting') !!}",
            "type": "POST",
            data: {'member_id':member_id,'company_id':company_id},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'company_name', name: 'company_name'},
            {data: 'date', name: 'date'},
            {data: 'plan', name: 'plan'},
            {data: 'member_id', name: 'member_id'},
            {data: 'member_name', name: 'member_name'},
            {data: 'account_no', name: 'account_no'},
            {data: 'amount', name: 'amount', 
                "render":function(data, type, row){
                    return row.amount+ " <img src='{{url('/')}}/asset/images/rs.png' width='7'>";
                }
            },
            {data: 'tenure', name: 'tenure'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTable.table().container()).removeClass( 'form-inline' );

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });


});



 
</script>