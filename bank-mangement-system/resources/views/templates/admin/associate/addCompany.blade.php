@extends('templates.admin.master')
@section('content')
    <div class="content">
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
            {!!Form::open(['url'=>'#','method'=>'post','id'=>'customAssociateRegister','name'=>'customAssociateRegister','class'=>'col-lg-12','enctype'=>'multipart/form-data'])!!}  
                <!---- SSB Investment Associate Form Start -------------------------------------->
                @include('templates.admin.associate.companyscript.associateFormInformation')
                <!---- SSB Investment Associate Form End -------------------------------------->
                <!---- SSB Investment Form Start -------------------------------------->
                @include('templates.admin.associate.companyscript.ssbAccountForm')
                <!---- SSB Investment Form End -------------------------------------->
                <!---- RD Investment Form Start -------------------------------------->
                {{--@include('templates.admin.associate.companyscript.rdAccountInvestment')--}}
                <!---- RD Investment Form End -------------------------------------->
            {!! Form::close() !!}
            {!!Form::open(['url'=>'#','method'=>'post','id'=>'customAssociateRegisterNext','name'=>'customAssociateRegisterNext','class'=>'col-lg-12 d-none','enctype'=>'multipart/form-data'])!!}
                @include('templates.admin.associate.companyscript.guarantorDetails')
                @include('templates.admin.associate.companyscript.associateDependents')
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
@include('templates.admin.associate.companyscript.script')
@stop