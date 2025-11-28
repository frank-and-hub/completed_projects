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
$year=date('Y');
$month=date('m');
?>
    <div class="content">
        <div class="row">
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div> 
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','id'=>'filter','name'=>'filter','class'=>'','enctype'=>'multipart/form-data'])}}
                                           
                        <div class="row">
                          <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Branch </label>
                                    <div class="col-lg-12  error-msg">
                                        <select class="form-control" id="branch" name="branch_id" titlew="Please Select Branch">
                                            <option value="">--Please Select Branch--</option> 
                                            <option value='0'>All Branch</option>
                                            @foreach($branch as $key=>$val)
                                                <option value="{{$key}}">{{$val}}</option> 
                                            @endforeach                                            
                                            <option value="0">Not Achieved</option> 
                                        </select>
                                    </div>
                                </div>
                            </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off','readonly'=>'true'])}}
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','autocomplete'=>'off','readonly'=>'true'])}}
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->branch_id < 1)
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Associate Name </label>
                                            <div class="col-lg-12  error-msg">
                                                {{Form::text('name','',['id'=>'name','class'=>'form-control'])}}
                                            </div>
                                        </div>
                                    </div> 
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Associate code  </label>
                                            <div class="col-lg-12 error-msg">
                                                {{Form::text('associate_code','',['id'=>'associate_code','class'=>'form-control'])}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Senior Code    </label>
                                            <div class="col-lg-12 error-msg">
                                                {{Form::text('sassociate_code','',['id'=>'sassociate_code','class'=>'form-control'])}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Customer Id </label>
                                            <div class="col-lg-12 error-msg">
                                                {{Form::text('customer_id','',['id'=>'customer_id','class'=>'form-control'])}}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{Form::text('branch_id',Auth::user()->branch_id,['id'=>'branch_id','class'=>'form-control'])}}
                                @endif                              
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            {{Form::hidden('is_search','yes',['id'=>'is_search','class'=>'form-control'])}}
                                            {{Form::hidden('member_export','',['id'=>'member_export','class'=>'form-control'])}}
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
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="member_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>                                    
                                    <th>Branch  Name</th>
									<th>Customer ID</th>
                                    <th>Associate ID</th>
									<th>Associate Name</th>
									<th>Joining Date(as associate)</th>
                                    <th>Senior Code</th>   
									<th>Senior Name</th>   
                                    <th>Associate DOB</th>
                                    <th>Nominee Name</th>
									<th>Relation</th>
									<th>Nominee Age</th>
                                    <th>Email ID</th>
                                    <th>Mobile No</th>   
                                    <th>Status</th>
                                    <th>Is Uploaded</th>  
                                    <th>Address</th>
                                    <th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
                                    <th>Village Name</th> 
                                    <th>Pin Code</th>  
                                    <th>First ID Proof</th>                               
                                    <th>Second ID Proof</th> 
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.associate.partials.listing_script')
@stop