@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .datatable{
    display:none;
}
</style>
@endsection
    <div class="content">
        <div class="row">
            @include('templates.admin.report.loan.loan_filter')
            <div class="col-md-12 table-section datatable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Loan Closed Details</h6>
                    <div class="">                       
                        <button type="button" class="btn bg-dark legitRipple export-loan" data-extension="1">Export xslx</button>
                    </div>
                </div>
                    <div class="">
                        <table id="loan_closed_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company name</th>
                                    <th>Branch</th>
                                    <th>Customer Id </th>
                                    <th>Member Id</th>
                                    <th>Member name</th>
                                    <th>Account number</th>
                                    <th>Issue Date</th>
									<th>Close Date</th>
                                    <th>Plan</th> 
                                    <th>Tenure</th>   
                                    <th>Mode</th>   
                                    <th>Loan Amount</th>                                  
                                    <th>Total Recovery</th>
									<th>Balance</th>
                                    <th>Associate code</th>
                                    <th>Associate name</th>                                                                           
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.report.loan.loan_closed_js')
@stop