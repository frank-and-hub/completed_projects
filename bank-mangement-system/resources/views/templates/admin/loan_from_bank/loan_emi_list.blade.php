@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<?php
$startDatee = '';
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <!-- <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Filter</h6>
                </div> -->
                <div class="card-header header-elements-inline">
<h6 class="card-title font-weight-semibold">Loan Emi Filter</h6>
<div class="">
    <a class="font-weight-semibold" href="{!! route('admin.loan_emi') !!}"><i class="icon-file-plus mr-2"></i>Loan Emi</a>
</div>
</div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                        <div class="row">
                            {{--<div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Company Name <sup>*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="company_id" name="company_id">
                                            <option value="">---Please Select Company---</option>
                                            @foreach($company as $cmpny)
                                            <option value="{{$cmpny->id}}">{{$cmpny->name}}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group">
                                        </div>
                                    </div>
                                </div>
                            </div>--}}
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true,'branchShow'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Loan Account <sup>*</sup></label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="loanAccount" name="loanAccount">
                                            <option value="">---Please Select Account Number---</option>
                                            @foreach($loan as $loanAccount)
                                                <option value="{{$loanAccount->id}}">{{$loanAccount->loan_account_number}}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <button type="button" class=" btn bg-dark legitRipple" onclick="searchForm()">Submit</button>
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="export" id="export">
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
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
                <!-- <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Loan Emi List</h6>
                    <div class="">
                        <a class="font-weight-semibold" href="{!! route('admin.loan_emi') !!}"><i class="icon-file-plus mr-2"></i>Loan Emi</a>
                    </div>
                </div> -->
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Loan Emi List</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple loan_emi_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="loan_emi_listing" class="table datatable-show-all ">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Bank Name</th>
                                <th>Loan Account</th>
                                <th>Loan Amount</th>
                                <th>Emi Date</th>
                                <th>Emi Number</th>
                                <th>Emi Amount</th>
                                <th>Emi Principal</th>
                                <th>Emi Interest</th>
                                <th>Bank </th>
                                <th>Bank A/c No.</th>
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
@include('templates.admin.loan_from_bank.partials.script')
@stop