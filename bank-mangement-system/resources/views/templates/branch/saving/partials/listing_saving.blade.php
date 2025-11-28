<script type="text/javascript">

$(document).ready(function () {
      

        member_Saving = $('#member_Saving').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('branch.savingListing') !!}",
                "type": "POST",
                data: {'member_id':'{{$memberDetail->id}}'},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'company_name', name: 'company_name' },
                { data: 'branch_name', name: 'branch_name' },
                { data: 'customer_id', name: 'customer_id' },
                { data: 'member_id', name: 'member_id' },
                { data: 'account_no', name: 'account_no' },
                { data: 'member_name', name: 'member_name' },
                { data: 'balance', name: 'balance' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
        $(member_Saving.table().container()).removeClass('form-inline');
    });
</script>
