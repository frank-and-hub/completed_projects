@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .datatable {
        display: none;
    }
</style>
@endsection
<div class="content">
    <div class="row">
        @include('templates.admin.report.loan.loan_filter')
        <div class="col-md-12 table-section datatable">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Loan Application Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-loan" data-extension="1">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="loan_application_list" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Branch</th>
                                <th>Customer Id</th>
                                <th>Member Id</th>
                                <th>Member name</th>
                                <th>Application date</th>
                                <th>Plan</th>
                                <th>Tenure</th>
                                <th>Mode</th>
                                <th>Loan amount</th>
                                <th>Staus </th>
                                <th>Loan Account Number</th> 
                                <th>Approval date</th>
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
</div>
@include('templates.admin.report.loan.loan_application_js')
@stop