@extends('templates.admin.master')
<?php
    $dropDown = $company;
    $filedTitle = 'Company';
    $name = 'company_id';
?>
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
                    {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','name'=>'filter','id'=>'filter'])}}
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
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            {{--@include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])--}}
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Select Investment Plans </label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="plan_id" name="plan_id">
                                        <option value="">Please Select Investment Plans</option>
                                        @foreach( $plans as $k => $v )
                                        <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Scheme Account Number </label>
                                <div class="col-lg-12 error-msg">
                                    {{Form::text('scheme_account_number','',['id'=>'scheme_account_number','class'=>'form-control'])}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Member Name </label>
                                <div class="col-lg-12 error-msg">
                                    {{Form::text('name','',['id'=>'name','class'=>'form-control'])}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Member ID </label>
                                <div class="col-lg-12 error-msg">
                                    {{Form::text('member_id','',['id'=>'member_id','class'=>'form-control'])}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Customer ID </label>
                                <div class="col-lg-12 error-msg">
                                    {{Form::text('customer_id','',['id'=>'customer_id','class'=>'form-control'])}}
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
                        <div class="col-md-12">
                            <div class="form-group text-right">
                                <div class="col-lg-12 page">
                                    {{Form::hidden('investment_listing_currentdate',$startDatee,['id'=>'investment_listing_currentdate','class'=>'create_application_date'])}}    
                                    {{Form::hidden('is_search','no',['id'=>'is_search','class'=>''])}}
                                    {{Form::hidden('investments_export','',['id'=>'investments_export','class'=>''])}}
                                    <button type="submit" id="submit-button" class="btn btn-dark legit-ripple" onclick="searchForm()">Submit</button>
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
                <h3 class="mb-0 text-dark">Investments</h3>
                <div class="">
                    <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="investment-listing" class="table table-flush">
                    <thead class="">
                        <tr>
                            <th>S/N</th>
                            <th>A/C Opening Date</th>
                            <th>Form No</th>
                            <th>Plan</th>
                            <th>Company</th>
                            <th>BR Name</th>
                            <th>Member</th>
                            <th>Customer Id</th>
                            <th>Member Id</th>
                            <th>Member Mobile Number</th>
                            <th>Associate Code</th>
                            <th>Associate Name</th>
                            <th>Collector Code</th>
                            <th>Collector Name</th>
                            <th>Account Number</th>
                            <th>Tenure</th>
                            <th>Balance</th>
                            <th>ELI Amount</th>
                            <th>Deposite Amount</th>
                            <th>Address</th>
                            <th>State</th>
                            <th>District</th>
                            <th>City</th>
                            <th>Village Name</th>
                            <th>Pin Code</th>
                            <th>First ID Proof</th>
                            <th>Second ID Proof</th>
                            <th>Transaction</th>
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
@include('templates.admin.investment_management.partials.script')
@stop