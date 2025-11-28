@extends('templates.admin.master')
@section('content') 
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
                        <h6 class="card-title font-weight-semibold"> Employee Status Active/Inactive</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.ht.employeestatus.show') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">                              
						<div class="row">
							 <div class="col-md-12">
								<div class="form-group row">
									<label class="col-form-label col-lg-3">Employee Code <span class="text-danger">*</span> </label>
									<div class="col-lg-6 error-msg ">
										<input type="text" name="employee_code" id="employee_code" class="form-control">
									</div>
								</div>
							</div>
							<div class="col-md-12" id="employee_detail">
							</div>
							<div class="col-md-12 text-center">
								 <button type="Submit" class=" btn bg-dark legitRipple" id="Change_status" disabled>Change status</button>
									<input type="hidden" id="status" value="" name="status"/>
								 <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
							</div>
						</div> 
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Inactive Employee List</h6>
                        <div class=""> 
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                    </div>
                    <div class="">
                        <table id="employee_status_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Company Name</th>
                                    <th>Branch Name</th>
                                    <th>Employee Name </th>
                                    <th>Employee code</th>                                   
                                    <th>Employee Designation </th>
                                    <th>Status</th>
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.hr_management.employee_status.partials.script')
@stop