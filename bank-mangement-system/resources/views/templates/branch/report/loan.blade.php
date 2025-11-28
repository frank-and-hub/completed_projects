@extends('layouts/branch.dashboard')
@php
$dropDown = $company;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
@section('content')
@section('css')
<style>
    .datatable {
        display: none;
    }
</style>
@endsection
@php
$stateid = getBranchState(Auth::user()->username);
@endphp
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">Loan Details</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="start_date" id="start_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="end_date" id="end_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               {{--
                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Loan Type </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="plan" name="plan">
                                                    <option value="">----Select----</option>
                                                    @foreach( App\Models\Loans::pluck('name', 'id') as $key => $val )
                                                    @if($key != 3)
                                                    <option value="{{ $key }}">{{ $val }}</option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               --}}
                               @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>false,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch','allOptionShow'=>true])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Loan Type </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="plan" name="plan">
                                                    <option value="">----Select Loan Type----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select status</option>
                                                <option value="0">Pending</option>
                                                <option value="1">Approved</option>
                                                <option value="3">Completed</option>
                                                <option value="4">ONGOING</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Account Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " name="application_number" id="application_number">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="member_id" id="member_id" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <input type="hidden" name="branch_id" id="branch_id" value="{{getUserBranchId(Auth::user()->id)->id}}">
                                            <input type="hidden" name="branch_report_currentdate" value="{{ date('d/m/Y',strtotime(checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid))) }}" class="branch_report_currentdate">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn  btn-secondary legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 table-section datatable">
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Loan Details</h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="loan_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Staus</th>
                                    <th>Applicant Name</th>
                                    <th>Customer Id</th>
                                    <th>Company</th>
                                    <th>Applicant Phone Number</th>
                                    <th>Account No.</th>
                                    <th>Branch</th>
                                    <th>Sector Branch</th>
                                    <th>Member Id</th>
                                    <th>Sanctioned Amount</th>
                                    <th>Sanctioned Date</th>
                                    <th>EMI Amount</th>
                                    <th>No. of Installments</th>
                                    <th>Loan Mode</th>
                                    <th>Loan Type</th>
                                    <th>Loan Issued Date</th>
                                    <th>Loan Issued Mode</th>
                                    <th>Cheque No.</th>
                                    <th>Total Recovery Amt</th>
                                    <!-- <th>Total Recovery EMI Till Date</th> -->
                                    <th>Closing Amount</th>
                                    <th>Balance EMI</th>
                                    <th>EMI Should be received till date</th>
                                    <th>Future EMI Due Till Date(Total)</th>
                                    <th>Date</th>
                                    <th>Co-Applicant Name</th>
                                    <th>Co-Applicant Number</th>
                                    <th>Guarantor Name</th>
                                    <th>Guarantor Number</th>
                                    <th>Applicant Address</th>
                                    <th>First EMI Date</th>
                                    <th>Loan End Date</th>
                                    <!-- <th>Total Deposit Till Date</th> -->
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
    @include('templates.branch.report.partials.loan')
    @stop