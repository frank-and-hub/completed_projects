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
        @include('templates.admin.report.maturity.maturity_overdue_filter')
        <div class="col-md-12 table-section datatable">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Maturity Overdue Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-overdue" data-extension="1">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="overdue_listing" class="table datatable-show-all">
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
                                <th>Total deposit</th>
                                <th>Overdue period (months/days), </th>
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
@include('templates.admin.report.maturity.maturity_overdue_js')

@stop