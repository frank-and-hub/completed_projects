    @extends('templates.admin.master')
    @section('content')
    @section('css')
        <style>
            .datatable,.hide-table {
                display: none;
            }
            .show-table{
                display: block;
            }
        </style>
    @endsection
    <div class="content">
        <div class="row ">
            @include('templates.admin.report.maturity.maturity_payment_filter')
            <div class="col-md-12 table-section  hide-table">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Maturity Payment Details</h6>
                        <div class="">
                            <button type="button"
                                class="btn bg-dark legitRipple export-maturity-demanad"
                                data-extension="1">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="maturity_payment_listing" class="table  ">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Branch</th>
                                    <th>Customer Id</th>
                                    <th>Member Id</th>
                                    <th>Member Name</th>
                                    <th>Account number</th>
                                    <th>Plan</th>
                                    <th>Tenure </th>
                                    <th>Open date</th>
                                    <th>Maturity date</th>
                                    <th>Payment date</th>
                                    <th>Total deposit</th>
                                    <th>Payment amount</th>
                                    <th>Payment mode</th>
                                    <th>Associate Code</th>
                                    <th>Associate Name</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('templates.admin.report.maturity.maturity_payment_js')
@stop
