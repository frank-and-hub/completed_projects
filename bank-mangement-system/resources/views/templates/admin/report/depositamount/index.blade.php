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
    @include('templates.admin.report.depositamount.depositamount_filter')
        <div class="col-md-12 table-section datatable">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Deposit Amount</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-deposit-amount" data-extension="1">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="DepositAmountTable_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Branch Name</th>
                                <th>Plan Name</th>
                                <th>Plan Tenure (in months)</th>
                                <th>Deno Amount</th>
                                <th>Renewal Amount</th>
                                <th>Total Amount</th>
                                <th>Maturity Deno</th>
                                <th>Maturity Total Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@include('templates.admin.report.depositamount.partials.script')

@stop
