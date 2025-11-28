@extends('templates.admin.master')

@section('content')
<?php
$employee_code='';
if(isset($_GET['employee']))
{
  $employee_code=$_GET['employee'];
}


?>

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

        <form action="{!! route('admin.hr.employ.transfer_save') !!}" method="post" enctype="multipart/form-data" id="employee_transfer" name="employee_transfer"  >
    @csrf
      <div class="row">
        <div class="col-lg-12"> 
        <input type="hidden" class="form-control create_application_date  " name="created_at" id="created_at"  >
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Employee Transfer</h3>
              <div class="row">
                <div class="col-lg-12 "><h6 class=" mb-3">Employee Information</h6></div>
                <div class="col-lg-12">
                    <div class="form-group row">                      
                      <label class="col-form-label col-lg-2">Employee Code<sup class="required">*</sup> </label>
                      <div class="col-lg-10 error-msg">
                        <input type="text" name="employee_code" id="employee_code" class=" form-control" value="{{ $employee_code }}" @if(isset($_GET['employee'])) readonly @endif >
                      </div>
                    </div>
                </div>
                <div class="col-lg-12 show_emp_detail" style="display: none;">
                    <div class="form-group row">                      
                      <label class="col-form-label col-lg-2">Company Name<sup class="required">*</sup> </label>
                      <div class="col-lg-10 error-msg">
                        <input type="text" name="company_name" id="company_name" class=" form-control"  readonly >
                        <input type="hidden" name="company_id" id="company_id" class=" form-control">
                      </div>
                    </div>
                </div>
                <div class="col-lg-12" id="error_msg_emp"></div>
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Employee Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="employee_name" id="employee_name" class="form-control" readonly>
                         <input type="hidden" name="employee_id" id="employee_id" class="form-control" readonly>
                         
                          <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Recommendation Employee Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="rec_employee_name" id="rec_employee_name" class="form-control" readonly> 
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Recommendation Employee Designation<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="rec_employee_designation" id="rec_employee_designation" class="form-control" readonly> 
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Branch<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="branch" id="branch" class="form-control" readonly>
                        <input type="hidden" name="branch_id" id="branch_id" class="form-control" >
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Category<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="category" id="category" class="form-control" readonly> 
                        <input type="hidden" name="category_id" id="category_id" class="form-control" readonly> 
                      </div>
                    </div> 
                  </div> 
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Designation<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="designation" id="designation" class="form-control" readonly>
                        <input type="hidden" name="designation_id" id="designation_id" class="form-control" >
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Gross Salary<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">  
                        <input type="text" name="salary" id="salary" class="form-control" readonly> 
                      </div>
                    </div> 
                  </div>
                  
                  <div class="col-lg-12 "><h6 class=" mb-3">Employee Transfer To</h6></div>
                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Transfer Date<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                          <div class="input-group">
                            <span class="input-group-prepend">
                              <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                            </span>
                             <input type="text" class="form-control" name="transfer_date" id="transfer_date" value="{{ old('transfer_date') }}" autocomplete="off" readonly>
                           </div>
                        </div>
                    </div> 
                  </div>

                  <div class="col-lg-6 " >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Recommendation Employee Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="transfer_rec_employee_name" id="transfer_rec_employee_name" class="form-control" value="{{ old('transfer_rec_employee_name') }}"> 
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 " >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Recommendation Employee Designation<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="transfer_rec_employee_designation" id="transfer_rec_employee_designation" class="form-control" value="{{ old('transfer_rec_employee_designation') }}"> 
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Branch<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="transfer_branch" id="transfer_branch" class="form-control" >  
                            <option value="">Select Branch</option> 
                            @foreach ($branch as $val)
                                  <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Category<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="transfer_category" id="transfer_category" class="form-control" >  
                                <option value="">Select Category</option>
                                <option value="2">Contract</option>
                                <option value="1">On - rolled</option>                             
                          </select>
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Designation<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="transfer_designation" id="transfer_designation" class="form-control" >  
                            <option value="">Select Designation</option> 
                        <!--   @foreach ($designation as $val)
                                  <option value="{{ $val->id }}">{{ $val->designation_name }}</option>
                            @endforeach
                        -->
                        </select>
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6  "  >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Gross Salary<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="transfer_salary" id="transfer_salary" class="form-control"  value="{{ old('transfer_salary') }}"> 
                      </div>
                    </div> 
                  </div> 
                  
                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-2"> File<sup class="required">*</sup></label>
                      <div class="col-lg-10 error-msg">
                        <input type="file" name="file" id="file" class="form-control">
                      </div>
                    </div> 
                  </div>


            </div>
          </div>

           

          
        </div> 
        </div> 


        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            </div>
          </div>
        </div>

      </div> 
    </form>
    </div>
</div>
@include('templates.admin.hr_management.employee.script_transfer')
@stop