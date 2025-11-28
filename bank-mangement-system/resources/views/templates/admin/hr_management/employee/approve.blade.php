@extends('templates.admin.master')

@section('content')
<?php
$employee_code='';
if(isset($_GET['employee']))
{
  $employee_code=$_GET['employee'];
}
$salary =number_format((float)$employee->salary, 2, '.', '');

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

        <form action="{!! route('admin.hr.employee_approve') !!}" method="post" enctype="multipart/form-data" id="employee_approve" name="employee_approve"  >
    @csrf
      <div class="row">
        <div class="col-lg-12"> 
      
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Employee Application Approve</h3>
              <div class="row">
                <div class="col-lg-12 "><h6 class=" mb-3">Employee Information</h6></div>
                
                <div class="col-lg-12" id="error_msg_emp"></div>
                  <div class="col-lg-6 show_emp_detail" >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Employee Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="employee_name" id="employee_name" class="form-control" readonly value="{{$employee->employee_name}}">
                         <input type="hidden" name="employee_id" id="employee_id" class="form-control" readonly value="{{$employee->id}}">
                         <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                          <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >

                          <input type="hidden" name="id" id="id" class="form-control" readonly value="{{$application->id}}">
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 show_emp_detail">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Recommendation Employee Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="rec_employee_name" id="rec_employee_name" class="form-control" readonly value="{{$employee->recommendation_employee_name}}"> 
                      </div>
                    </div> 
                  </div>
                  
                  <!-- <div class="col-lg-6 show_emp_detail">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Employee Code<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="employee_code" id="employee_code" class="form-control" > 
                      </div>
                    </div> 
                  </div> -->

                  <div class="col-lg-6 show_emp_detail" >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Recommendation Employee Designation<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="rec_employee_designation" id="rec_employee_designation" class="form-control" readonly value="{{$employee->recom_employee_designation}}"> 
                      </div>
                    </div> 
                  </div>

                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Category<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg"> 
                          <?php
                            if($employee->category==2)
                            {
                              $cat='Contract';
                              $cat_id=2;
                            }
                            else{
                              $cat='On - rolled';
                              $cat_id=1;
                            }
                          ?>
                          <input type="text"  class="form-control" name="category_name" id="category_name" value="{{$cat}}" readonly>
                          <input type="hidden"  class="form-control" name="category" id="category" value="{{$cat_id}}">
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Designation<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="designation" id="designation" class="form-control" >  
                            <option value="">Select Designation</option> 
                          
                        </select>
                      </div>
                    </div> 
                  </div>
                  <div class="col-lg-6  "  >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Gross Salary<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="salary" id="salary" class="form-control"  value="{{ $salary }}"> 
                      </div>
                    </div> 
                  </div> 
                  <div class="col-lg-6  "  >
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-4">Approve Date<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="select_date" id="select_date" class="form-control  " readonly value="{{ date('d/m/Y', strtotime($employee->created_at)) }}"> 
                        <input type="hidden" name="created_date" id="created_date" class="form-control  " readonly value="{{ date('d/m/Y', strtotime($employee->created_at)) }}">
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
@include('templates.admin.hr_management.employee.script_approve')
@stop