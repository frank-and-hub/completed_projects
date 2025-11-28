@extends('templates.admin.master')
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
<?php
$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
$startDatee = $endDatee = date('d/m/Y', strtotime($startDatee));
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    {{Form::open(['url'=>'#','method'=>'POST','id'=>'filter','name'=>'filter','enctype'=>'multipart/form-data'])}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control'])}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control'])}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate Code </label>
                                    <div class="col-lg-12 error-msg">
                                        {{Form::text('associate_code','',['id'=>'associate_code','class'=>'form-control'])}}
                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])

                            {{--@include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])--}}
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Select Investment Plans </label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="plan_id" name="plan_id">
                                        <option value="">Select Plan</option>
                                        {{-- @foreach( $plans as $plan )
                                                    <option value="{{ $plan->id }}" >{{ $plan->name }}</option>
                                        @endforeach --}}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Select Transaction By </label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="transaction_by" name="transaction_by">
                                        <option>Select Option</option>
                                        <option value="0">Software</option>
                                        <option value="1">Associate</option>
                                        <option value="2">E-Passbook</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Account No</label>
                                <div class="col-lg-12 error-msg">
                                    {{Form::text('account_no','',['id'=>'account_no','class'=>'form-control'])}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <div class="col-lg-12 page">
                                    {{Form::hidden('renewal_listing_currentdate',$startDatee,['id'=>'renewal_listing_currentdate','class'=>'create_application_date'])}}    
                                    {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}
                                    {{Form::hidden('investments_export','investments_export',['id'=>'','class'=>''])}}
                                    <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                </div>
                            </div>
                        </div>
                    </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
    <div class="col-md-12 table-section datatable">
        <div class="card bg-white shadow">
            <div class="card-header bg-transparent header-elements-inline">
                <h3 class="mb-0 text-dark">Renewal List</h3>
                <div class="">
                    <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="renewaldetails-listing" class="table table-flush">
                    <thead class="">
                        <tr>
                            <th>S/N</th>
                            <th>Created Date</th>
                            <th>Transaction By</th>
                            <th>Company</th>
                            <th>BR Name</th>
                            <th>Customer Id</th>
                            <th>Member ID</th>
                            <th>Account Number</th>
                            <th>Member(Account Holder Name)</th>
                            <th>Plan</th>
                            <th>Tenure</th>
                            <th>Amount</th>
                            <th>Associate Code</th>
                            <th>Associate Name</th>
                            <th>Payment Mode</th>
                            <th>Account Opening Date</th>
                            <th>Deno Amount</th>
                            <th>Mother Branch</th>
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
@include('templates.admin.investment_management.partials.renewaldetails_script')
@stop