@extends('layouts/branch.dashboard')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">Associate Registration</h3>
                        <a href="{{ url()->previous() }} " style="float:right" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>        
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body"> 
                        <div class="col-lg-12" id="formError">
                        </div>
                        <h3 class="card-title mb-3">Customer's Detail</h3>
                        <div class="  row">
                            <label class="col-form-label col-lg-3"></label>
                            <div class="col-lg-9 error-msg">
                                <h4 class="card-title mb-3 ">Search Customer</h4>
                            </div>
                        </div>
                        {!! Form::open(['url'=>'#','name'=>'customerNameForm','id'=>'customerNameForm']) !!}
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Customer Id<sup class="required">*</sup></label>
                            <div class="col-lg-9 error-msg">
                                {!! Form::text('customer_id',old('customer_id'),['class'=>'form-control','id'=>'customer_id']) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                        <div id="show_customer_detail">
                        </div>
                    </div>
                </div>
            </div>
            {!!Form::open(['url'=>'#','method'=>'post','id'=>'customAssociateRegister','name'=>'customAssociateRegister','class'=>'customAssociateRegister col-lg-12','enctype'=>'multipart/form-data'])!!}
            {!! Form::hidden('created_at',null,['id'=>'created_at']) !!}
            {!! Form::hidden('id',null,['id'=>'id','class'=>'form-control']) !!}
            {!! Form::hidden('ssb_account',old('ssb_account'),['id'=>'ssb_account']) !!}
            {!! Form::hidden('rd_account',old('rd_account'),['id'=>'rd_account']) !!}
            {!! Form::hidden('ssb_account_number',old('ssb_account_number'),['id'=>'ssb_account_number']) !!}  
            {!! Form::hidden('ssb_account_name',old('ssb_account_name'),['id'=>'ssb_account_name']) !!}  
            {!! Form::hidden('ssb_account_amount',old('ssb_account_amount'),['id'=>'ssb_account_amount']) !!}
            {!! Form::hidden('associate',old('associate'),['id'=>'associate']) !!}  
                <!---- SSB Investment Associate Form Start -------------------------------------->
                @include('templates.branch.associate_registration_management.associateFormInformation')
                <!---- SSB Investment Associate Form End -------------------------------------->
                <!---- SSB Investment Form Start -------------------------------------->
                @include('templates.branch.associate_registration_management.ssbAccountForm')
                <!---- SSB Investment Form End -------------------------------------->
                <!---- RD Investment Form Start -------------------------------------->
                {{-- @include('templates.branch.associate_registration_management.rdAccountInvestment') --}}
                <!---- RD Investment Form End -------------------------------------->
            {!! Form::close() !!}
            {!!Form::open(['url'=>'#','method'=>'post','id'=>'customAssociateRegisterNext','name'=>'customAssociateRegisterNext','class'=>'customAssociateRegisterNext col-lg-12 d-none','enctype'=>'multipart/form-data'])!!}
                @include('templates.branch.associate_registration_management.guarantorDetails')
                @include('templates.branch.associate_registration_management.associateDependents')
            {!! Form::close() !!}
        </div>
        <div class="col-lg-12">
            <div class="form-group row text-center">
                <div class="col-lg-12 ">                              
                    <button type="button" class="btn btn-primary" id="nextButton" data-form="1">Next<i class="icon-paperplane ml-2"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@stop
@section('script')
@include('templates.branch.associate_registration_management.partials.script')
@stop