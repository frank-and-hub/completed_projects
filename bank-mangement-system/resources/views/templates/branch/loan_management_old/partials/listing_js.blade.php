<script type="text/javascript">
$(document).ready(function () {
    var member_id='{{$memberDetail->id}}';

    var memberTableLoan = $('#member_loan_listing').DataTable({
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
            "url": "{!! route('branch.member_loan_listing') !!}",
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
            {data: 'amount', name: 'amount'},
            {data: 'loan_amount', name: 'loan_amount'},
			
            {data: 'file_charges', name: 'file_charges'},
            {data: 'file_charges_payment_mode', name: 'file_charges_payment_mode'},
            {data: 'branch', name: 'branch'}, 
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'}, 
            {data: 'approve_date', name: 'approve_date'},
            {data: 'status', name: 'status'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTableLoan.table().container()).removeClass( 'form-inline' );

    var memberTableLoan = $('#member_group_loan_listing').DataTable({
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
            "url": "{!! route('branch.member_grouploan_listing') !!}",
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
            {data: 'amount', name: 'amount'},
            {data: 'loan_amount', name: 'loan_amount'},
            {data: 'file_charges', name: 'file_charges'},
            {data: 'file_charges_payment_mode', name: 'file_charges_payment_mode'},
            {data: 'branch', name: 'branch'}, 
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'}, 
            {data: 'approve_date', name: 'approve_date'}, 
            {data: 'status', name: 'status'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTableLoan.table().container()).removeClass( 'form-inline' );

    /*var memberTableLoanGroup = $('#member_loan_listing').DataTable({
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
            "url": "{!! route('branch.member_grouploan_listing') !!}",
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
            {data: 'amount', name: 'amount'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'branch', name: 'branch'}, 
            {data: 'associate_code', name: 'associate_code'},
            {data: 'associate_name', name: 'associate_name'}, 
            {data: 'action', name: 'action',orderable: false, searchable: false},
        ]
    });
    $(memberTableLoanGroup.table().container()).removeClass( 'form-inline' );*/

    $( document ).ajaxStart(function() {
        $( ".loader" ).show();
    });

    $( document ).ajaxComplete(function() {
        $( ".loader" ).hide();
    });
});



 
</script>