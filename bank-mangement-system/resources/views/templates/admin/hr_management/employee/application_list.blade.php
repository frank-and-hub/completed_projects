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
            @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
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
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->branch_id<1)
                                @include('templates.GlobalTempletes.both_company_filter')
                                {{--
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
                                --}}
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
                                                @foreach( $designation as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->designation_name }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="employee_name" id="employee_name" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Application Type</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="row">
                                              <div class="col-lg-4">
                                                <div class="custom-control custom-radio mb-3 ">
                                                  <input type="radio" id="app_type_register" name="app_type" class="custom-control-input" value="1">
                                                  <label class="custom-control-label" for="app_type_register">Registration</label>
                                                </div>
                                              </div>
                                              <div class="col-lg-4">
                                                <div class="custom-control custom-radio mb-3  ">
                                                  <input type="radio" id="app_type_resign" name="app_type" class="custom-control-input" value="2"  >
                                                  <label class="custom-control-label" for="app_type_resign">Resignation</label>
                                                </div>
                                              </div>
                                            </div>
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

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="1">Approved</option>  
                                                <option value="0" >Pending</option> 
                                                <option value="3">Rejected</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <label class="col-form-label col-lg-12">  </label>
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="emp_application_export" id="emp_application_export" value="">
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
                        <h6 class="card-title font-weight-semibold">Employee Application List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>                        
                        </div>
                        <input type="hidden" class="form-control created_at" name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                    </div>
                    <div class="">
                        <table id="emp_application_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Application Type</th>
                                    <th>Designation</th>
                                    <th>Category</th>
                                    <th>BR Name</th>                                   
                                    <th>Recommendation Employee Name</th> 
                                    <th>Employee Name</th>
                                    <th>DOB</th>
                                    <th>Gender</th>
                                    <th>Number</th>
                                    <th>Email Id</th>
                                    <th>Guardian Name</th>
                                    <th>Guardian Number</th>
                                    <th>Mother Name</th>
                                    <th>Pan Card</th>
                                    <th>Aadhar Card</th>
                                    <th>Voter Id</th>
                                    <th>ESI Accounnt No.</th>
                                    <th>UAN/PF  Account No.</th>
                                    <th>Application Status</th>                                                    
                                    <th>Created</th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.hr_management.employee.script_application_list')
@stop