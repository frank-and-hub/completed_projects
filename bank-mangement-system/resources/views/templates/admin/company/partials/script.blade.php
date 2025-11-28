<script>
    $(document).ready(function() {
        var companies_listing = $('#companies_listing').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            bFilter: false,
            ordering: false,
            lengthMenu: [10, 20, 40, 50, 100],
            "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                var oSettings = this.fnSettings();
                $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                return nRow;
            },
            ajax: {
                "url": "{!! route('admin.companies.listing') !!}",
                "type": "POST",
                "data": function(d) {
                    d.searchform = $('form#filter').serializeArray()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'short_name',
                    name: 'short_name'
                },
                {
                    data: 'mobile_no',
                    name: 'mobile_no'
                },
                {
                    data: 'fa_code_from',
                    name: 'fa_code_from'
                },
                {
                    data: 'fa_code_to',
                    name: 'fa_code_to'
                },
                {
                    data: 'tin_no',
                    name: 'tin_no'
                },
                {
                    data: 'pan_no',
                    name: 'pan_no'
                },
                {
                    data: 'cin_no',
                    name: 'cin_no'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],"ordering": false
        });


        $(document).on('click', ".companyid", function() {
            var data_id = $(this).data("companyid");
            swal({
                title: 'Are you sure, you want to change company status ?',
                text: "",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }, function() {

                $.ajax({
                    url: "{!! route('admin.companies.status') !!}",
                    type: "POST",
                    data: {
                        id: data_id,
                    },
                    dataType: "html",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(e) {
                        let event = JSON.parse(e);
                        if (event) {
                            swal(
                                'Updated!',
                                'Company Status Changed Successfully ',
                                'success'
                            );
                            companies_listing.draw();

                        }
                    },
                    error: function() {
                        swal(
                            'error !',
                            'Company Status Not Changeed !!',
                            'error'
                        );
                    }
                });
            });
        });

    });
</script>