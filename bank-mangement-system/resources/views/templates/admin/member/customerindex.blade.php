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
                        {{Form::open(['url'=>'#','method'=>'post','enctype'=>'multipart/form-data','id'=>'filter','name'=>'filter'])}}                        
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 {{Form::text('start_date','',['id'=>'start_date','class'=>'form-control','autocomplete'=>'off'])}}
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 {{Form::text('end_date','',['id'=>'end_date','class'=>'form-control','autocomplete'=>'off'])}}
                                               </div>
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
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            {{Form::hidden('is_search','yes',['id'=>'is_search','class'=>'form-control'])}}
                                            {{Form::hidden('customer_export','',['id'=>'customer_export','class'=>'form-control'])}}
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
                        <h6 class="card-title font-weight-semibold">Customer List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>                           
                        </div>
                    </div>
                    <div class="">
                        <table id="customer_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>                                    
									<th>Join Date</th>
									<th>Member Name</th>
                                    <th>Customer ID</th>
                                    <th>Br Name</th> 
									<th>DOB</th>
                                    <th>Gender</th>
									<th>Mobile</th>
									<th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
									<th>Address</th>
									<th>Village Name</th> 
									<th>Pin Code</th>
									<th>ID Proof</th>                               
                                    <th>Address Proof</th>
									<th>Nominee Name</th>
									<th>Age</th>
									<th>Relation</th>
									<th>Nominee Gender</th>
									<th>Associate Name</th>
                                    <th>Associate Code</th>  
									<th>Status</th> 
									<!-- <th>SSB A/C</th>
                                    <th>Image Uploaded</th> -->
                                   <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.member.partials.customerlisting_script')
@stop