@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Employee Transfer Listing</h3> 
                    
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
                                                 <input type="text" class="form-control  " name="start_date" id="start_date" readonly > 
                                                 <input type="hidden" name="branch" id="branch" value="{{getUserBranchId(Auth::user()->id)->id}}">
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date" readonly >
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <!---<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch" name="branch">
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}  - {{$val->branch_code}}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>-->
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Category </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="category" name="category">
                                                <option value="">Select Category</option>
                                                <option value="1">On-rolled</option>  
                                                <option value="2">Contract</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Designation </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="designation" name="designation">
                                                <option value="">Select Designation</option>
                                                @foreach( $designation as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->designation_name }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="employee_code" id="employee_code" class="form-control"  > 
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Transfer Time  </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="employee_transfer_time" id="employee_transfer_time" class="form-control" readonly > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Recommendation Employee  Name</label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="reco_employee_name" id="reco_employee_name" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>

                                 
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <label class="col-form-label col-lg-12">  </label>
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="emp_export" id="emp_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-secondary legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
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
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Employee Transfer List</h3>
                            </div>
                            <div class="col-md-4 text-right">

                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            </div>
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                        <table id="emp_transfer_listing" class="table table-flush">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                     <th>Company Name</th>
                                     <th>Old Branch</th>
                                    <th>New Branch</th>
                                    <th>Apply Date</th> 
                                    <th>Employee Code</th>
                                    <th>Employee Name</th>
                                    <th>Designation</th>
                                    <th>Category</th> 
                                                                        
                                    <th>Recommendation Employee Name</th>  
                                    <th>Transferred Date</th> 
                                    
                                    <th>Transferred Designation</th>
                                    <th>Transferred Category</th>
                                    <th>Recommendation Employee Name</th> 
                                    <th>File Link</th>  
                                    <th class="text-center">Action</th>    
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
@include('templates.branch.hr_management.employee.script_transfer_list')
@stop