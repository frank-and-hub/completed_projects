@extends('templates.admin.master')

@section('content')
@section('css')
<style>
.hideTableData {
    display: none;
}
</style>
@endsection
<div class="content">
    <div class="row">  
        <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','id'=>'filter','name'=>'filter','enctype'=>'multipart/form-data'])}}
                        {{Form::hidden('slug',$slug,['id'=>'slug','class'=>'form-control'])}}      
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12"> Date</label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off'])}}    
                                                </div>
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
                                        <label class="col-form-label col-lg-12">Associate Code </label>
                                        <div class="col-lg-12 error-msg">
                                            {{Form::text('associate_code','',['id'=>'associate_code','class'=>'form-control'])}}    
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                       <label class="col-form-label col-lg-12">Branch</label>
                                        <div class="col-lg-12 error-msg">
                                           
                                            <select class="form-control" id="branch" name="branch">
                                                 <option value="">---Please Select Branch --- </option>
                                                @foreach($branches as $branch)
                                                <option value="{{$branch->id}}">{{$branch->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @if($slug == 'monthly')
                                <div class="col-md-4">
                                    <div class="form-group row">
                                       <label class="col-form-label col-lg-12">Plan Name</label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="plan" id="plan" class="form-control">
                                                <option value="">---Please Select Plan --- </option>
                                                @foreach($plan as $val)
                                                <option value="{{$val->id}}">{{$val->name}}</option>
                                                @endforeach
                                             </select>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}    
                                            {{Form::hidden('investments_export','',['id'=>'investments_export','class'=>'form-control'])}}
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
        </div>
        <div class="col-lg-12 table-section hideTableData">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Investments</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="investment_report_isting" class="table table-flush">
                       <thead class="">
                            <tr>
                            <th>S/N</th>
                            <th>BR Name</th>
                            <th>BR Code</th>
                            <th>Opening Date</th>
                            <th>Current Date</th>
                            <th>Member</th>
                            <th>Member Id</th>
                            <th>Mobile No</th>
                            <th>Associate Code</th>
                            <th>Associate Name</th>
                            <th>Account Number</th>
                            <th>Plan Name</th>
                            <th>Tenure</th>
                            <th>Deno Amount</th>
                            <th>Due Emi</th>
                            <th>Due Emi Amount</th>                                  
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
@include('templates.admin.investment_management.partials.report_script')
@stop
