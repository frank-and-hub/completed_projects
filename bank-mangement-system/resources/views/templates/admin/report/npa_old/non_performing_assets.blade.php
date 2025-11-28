@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .datatable{
    display:none;
}
</style>
@endsection
@php
$startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
$startDatee = $endDatee = date('d/m/Y',strtotime($startDatee));
@endphp
    <div class="content">
        <div class="row">
            @include('templates.admin.report.npa.npa_filter')
            <div class="col-md-12 table-section datatable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Non Performing Assets Report</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export-npa ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="non_performing_assets_report" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Branch Name</th>
                                    <th>Branch Code</th>
									<th>Member  ID</th>
                                    <th>Member Name</th>
									<th>Account No</th>
                                    <th>Loan Plan Name</th>
									<th>Loan Sanction Date</th>
                                    <th>Loan Amount</th>
									<th>EMI Option</th>
                                    <th>EMI Amount</th>
                                    <th>EMI Period</th>
                                    <th>Closing Date</th>
									<th>Last Recovery Date</th>
                                    <th>Total Recovery Amount</th>
                                    <th>Over Due Days</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.report.npa.partials.script')
@stop