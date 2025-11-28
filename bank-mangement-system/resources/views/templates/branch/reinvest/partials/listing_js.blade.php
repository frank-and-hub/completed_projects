<script type="text/javascript"> 
    var member_transaction;
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

  
var member_id='{{$memberDetail->id}}'; 

     memberTableLoan = $('#member_loan').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings (); 
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member_loan_listing') !!}",
            "type": "POST",
            data: {'member_id':member_id},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'}, 
            {data: 'loan_name', name: 'loan_name'},
            {data: 'amount', name: 'amount',
                "render":function(data, type, row){
                if ( row.amount ) {
                    return row.amount+ " <img src='http://mobile.host4india.in/micro_finance/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
            {data: 'branch', name: 'branch'}, 
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTableLoan.table().container()).removeClass( 'form-inline' );

memberTableLoanGroup = $('#member_group_loan').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings (); 
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member_grouploan_listing') !!}",
            "type": "POST",
            data: {'member_id':member_id},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'}, 
            {data: 'loan_name', name: 'loan_name'},
            {data: 'leader', name: 'leader'},
            {data: 'amount', name: 'amount',
                "render":function(data, type, row){
                if ( row.amount ) {
                    return row.amount+ " <img src='http://mobile.host4india.in/micro_finance/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
            {data: 'total_amount', name: 'total_amount',
                "render":function(data, type, row){
                if ( row.total_amount ) {
                    return row.total_amount+ " <img src='http://mobile.host4india.in/micro_finance/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
            {data: 'branch', name: 'branch'}, 
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'}, 
            {data: 'memberNo', name: 'memberNo'},
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTableLoanGroup.table().container()).removeClass( 'form-inline' );
    
memberTableInvestment = $('#member_Investment').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings (); 
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.member_investment') !!}",
            "type": "POST",
            data: {'member_id':member_id},
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'}, 
            {data: 'branch', name: 'branch'},
            {data: 'plan', name: 'plan'},
            {data: 'amount', name: 'amount',
                "render":function(data, type, row){
                if ( row.amount ) {
                    return row.amount+ " <img src='http://mobile.host4india.in/micro_finance/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
            {data: 'rate', name: 'rate'},
            {data: 'mamount', name: 'mamount',
                "render":function(data, type, row){
                if ( row.mamount ) {
                    return row.mamount+ " <img src='http://mobile.host4india.in/micro_finance/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            }, 
             {data: 'tenure', name: 'tenure'},
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'},            
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTableInvestment.table().container()).removeClass( 'form-inline' );



    member_transaction = $('#member_transaction').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        "fnRowCallback" : function(nRow, aData, iDisplayIndex) {      
            var oSettings = this.fnSettings (); 
           $('html, body').stop().animate({
            scrollTop: ($('#member_transaction').offset().top)
        }, 1000);
            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
            return nRow;
        },
        ajax: {
            "url": "{!! route('admin.transactions_lists') !!}",
            "type": "POST",
            "data":function(d) {d.searchform=$('form#fillter').serializeArray()}, 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'date', name: 'date'},
            {data: 'member', name: 'member'},
            {data: 'member_id', name: 'member_id'},
            {data: 'tran_type', name: 'tran_type'},  
            {data: 'amount', name: 'amount',
                "render":function(data, type, row){
                if ( row.amount ) {
                    return row.amount+ " <img src='http://mobile.host4india.in/micro_finance/asset/images/rs.png' width='7'>";
                } else {
                    return "";
                }
                }
            },
            {data: 'payment_type', name: 'payment_type'},
            {data: 'payment_mode', name: 'payment_mode'},
            {data: 'account_no', name: 'account_no'}, 
        ]
    });
    
    $(member_transaction.table().container()).removeClass( 'form-inline' );


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
    member_transaction.draw();
}
function resetForm()
{
    $('#is_search').val("yes");
    
    $('#end_date').val('');
    $('#start_date').val('');

    member_transaction.draw();
}
</script>