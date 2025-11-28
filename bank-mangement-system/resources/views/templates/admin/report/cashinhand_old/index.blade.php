@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    /* .datatable {
        display: none;
    } */
</style>
@endsection
<div class="content">
    <div class="row">
    @include('templates.admin.report.cashinhand.cashinhand_demand_filter')
        <div class="col-md-12 table-section datatable">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Cash In Hand Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-cash-in-hand" data-extension="1">Export Excel</button>
                    </div>
                </div>
                <div class="">
                    <table id="Cashinhand_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Branch Name</th>
                                <th>Branch Code</th>
                                <th>Opening Cash</th>
                                <th>Total Cash Receving</th>
                                <th>Total Cash Payment</th>
                                <th>Approve Banking</th>
                                <th>Unapprove Banking</th>
                                <th>Closing Cash</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@include('templates.admin.report.cashinhand.script')

@stop
