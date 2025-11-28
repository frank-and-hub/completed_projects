@extends('layouts/branch.dashboard')

@section('content')
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body page-title">
                        <h3 class="">{{ $title }}</h3>
                        <!--<a href="{!! route('branch.fundtransfer.createbanktobank') !!}" style="float:right" class="btn btn-secondary">Add</a>-->
                        <a href="{{ url()->previous() }}" style="float:right" class="btn btn-secondary">Back</a>

                        <!-- Validate error messages -->
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <!-- Validate error messages -->
                    </div>
                    <div class="card-header header-elements-inline">
                        <h5 class="card-title font-weight-semibold">Search Filter</h5>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                       
                            <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Company</label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="company" name="company" readonly disabled>
                                            <option value="{{$companyId['get_company']->id}}">{{$companyId['get_company']->name}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="company_id" value="{{$companyId['get_company']->id}}">
                                @if (Auth::user()->branch_id < 1) <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                @foreach ($branch as $k => $val)
                                                <option value="{{ $val->id }}" @if ($k==0) selected @endif>
                                                    {{ $val->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                            </div>
                            @else
                            <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id }}">
                            @endif
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Party Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="party_name" id="party_name" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Status: </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select Status</option>
                                            <option value="1">Approved</option>
                                            <option value="0">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()">Submit</button>
                                        <input type="hidden" name="expense_export" id="filter_report">
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="card bg-white shadow " id="expense_table" style="display:none;">
        <div class="card-header bg-transparent">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="mb-0 text-dark">Bill Expense List</h3>
                </div>
                <div class="col-md-4 text-right">
                    <button type="button" class="btn btn-primary legitRipple  export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="bill_expense_listing" class="table datatable-show-all">
                <thead>
                    <tr>
                        <th>S.N</th>
                        {{-- <th>Company Name</th> --}}
                        <th>Branch Name</th>
                        {{-- <th>Branch Code</th> --}}
                        <th>Created At</th>
                        <th>Bill Date</th>
                        <th>Bill Number</th>
                        <th>Party Name</th>
                        <th>Party BanK Name</th>
                        <th>Party BanK A/C No.</th>
                        <th>Party Bank Ifsc.</th>
                        <th>Payment Mode</th>
                        <th>Cheque No.</th>
                        <th>Utr No.</th>
                        <th>Neft Charge</th>
                        <th>Total Expense</th>
                        <th>Bill Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
</div>
</div>


@stop
@section('script')
@include('templates.branch.expense.partial.script')
@stop