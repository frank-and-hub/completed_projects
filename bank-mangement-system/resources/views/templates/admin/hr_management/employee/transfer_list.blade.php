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
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control" name="start_date" id="start_date">  
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control" name="end_date" id="end_date">
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->branch_id<1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch" name="branch">
                                                <option value="">All</option> 
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}  - {{$val->branch_code}}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @else
                                  <input type="hidden" name="branch" id="branch" value="{{Auth::user()->branch_id}}">                         
                                @endif
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
                                        <!--        @foreach( $designation as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->designation_name }}</option> 
                                                @endforeach
                                            -->
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
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="emp_export" id="emp_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
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
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Employee Transfer List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                           <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                    </div>
                    <div class="">
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
                                   
                                    <!-- <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                                    <th>Recommendation Employee Name</th>  
                                    <th>Transferred Date</th> 
                                    <!-- <th>Transferred To BR Name</th>
                                    <th>Transferred To BR Code</th>
                                    <th>Transferred To SO Name</th>
                                    <th>Transferred To RO Name</th>
                                    <th>Transferred To ZO Name</th> -->
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
@include('templates.admin.hr_management.employee.script_transfer_list')
@stop