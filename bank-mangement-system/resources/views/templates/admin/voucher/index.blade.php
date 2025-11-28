@extends('templates.admin.master')
@section('content')
@section('css')
    <style>
        .hideTableData {
            display: none;
        }
    </style>
@endsection
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Payment type </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="payment_type" name="payment_type">
                                            <option value="">Select Payment Type</option>
                                            <option value="0">Cash</option>
                                            <option value="1">Cheque</option>
                                            <option value="2">Online</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Head </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="account_head" name="account_head">
                                            <option value="">Select Head</option>
                                            <option value="32">Penal Interest</option>
                                            <option value="96">Eli Loan</option>
                                            <option value="122">Investment Plan Stationery Charge</option>
                                            <option value="86">Indirect Expense</option>
                                            <option value="87">Commission</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> </label>
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="emp_export" id="emp_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                            onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Receive Voucher List</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                            style="float: right;">Export xslx</button>
                    </div>
                    <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                    <input type="hidden" class="form-control create_application_date "
                        name="create_application_date" id="create_application_date">
                </div>
                <table id="voucher_listing" class="table table-flush">
                    <thead class="">
                        <tr>
                            <th>S/N</th>
                            <th>Company Name</th>
                            <th>BR Name</th>
                            <!-- <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                            <th>Date</th>
                            <th>Receive Mode</th>
                            <th>Receive Amount</th>
                            <th>Account Head</th>
                            <th>Account Sub Head</th>
                            <th>Director</th>
                            <th>Shareholder</th>
                            <th>Emp Code / Member Id / Associate Id</th>
                            <th>Emp Name / Member Name / Associate Name</th>
                            <th>Bank Name</th>
                            <th>Bank A/c </th>
                            <th>Eli Loan</th>
                            <th>Cheque No.</th>
                            <th>Cheque Date</th>
                            <th>UTR/Transaction No.</th>
                            <th>Transaction Date</th>
                            <th>Party Bank Name</th>
                            <th>Party Bank A/c</th>
                            <th>Receive Bank</th>
                            <th>Receive Bank A/c</th>
                            <th>Transaction Slip</th>
                            <!--  <th>Created At</th>-->
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.admin.voucher.partials.script_list')
@endsection