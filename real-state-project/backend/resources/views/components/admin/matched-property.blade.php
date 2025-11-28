<div>
    <div class="card">
        <div class="card-header">
            <h5>Match Properties</h5>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="adminmatchproperty" class="table">
                    <thead>
                        <tr>
                            {{-- <th>Sr.No.</th> --}}
                            <th>Created At</th>
                            <th>Tenant</th>
                            <th>Agent/Landlord Name</th>
                            <th>Title</th>
                            <th>Property Type</th>
                            <th>Property Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


@push('custom-script')
    <script type="text/javascript">
        $(document).ready(function () {
            var adminpropertyreque = $('#adminmatchproperty').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ $route }}",
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    },{
                        data: 'tenant',
                        name: 'tenant'
                    },{
                        data: 'agent',
                        name: 'agent'
                    },{
                        data: 'title',
                        name: 'title',
                        render: function (data, type, row, meta) {
                        return data.length > 30 ?
                                `<span class="short-text">${data.substring(0, 30)}...</span>
                                <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                                <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                data;
                        }
                    },{
                        data: 'property_type',
                        name: 'property_type'
                    },{
                        data: 'property_status',
                        name: 'property_status'
                    },{
                        data: 'action',
                        name: 'action'
                    }
                ],
                order: [
                    [6, 'asc']
                ],
                drawCallback: function (settings, json) {
                    adminpropertyreque.column(1).visible(false);
                    $('[data-toggle=tooltip]').tooltip();
                }
            });
        });
    </script>
@endpush
