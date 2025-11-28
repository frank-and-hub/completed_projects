@extends('templates.admin.master')

@section('content')
<?php
$employee_code='';
$remark='';
if(isset($_GET['employee']))
{
  $employee_code=$_GET['employee'];
}

if(old('employee_code'))
{
  $employee_code=old('employee_code');
}
if(old('remark'))
{
  $remark=old('remark');
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

        <form action="{!! route('admin.hr.resign_save') !!}" method="post" enctype="multipart/form-data" id="employee_resign" name="employee_resign"  >
    @csrf
      <div class="row">
        <div class="col-lg-12"> 
      
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Resign Application</h3>
              <div class="row">
                <div class="col-lg-12 "><h6 class=" mb-3">Employee Information</h6></div>
                <div class="col-lg-12">

                    <div class="form-group row">                      
                      <label class="col-form-label col-lg-2">Employee Code<sup class="required">*</sup> </label>
                      <div class="col-lg-10 error-msg">
                        <input type="text" name="employee_code" id="employee_code" class=" form-control" value="{{ $employee_code }}"  @if(isset($_GET['employee'])) readonly @endif>
                        <input type="hidden" name="employee_id" id="employee_id" class="form-control" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12 show_emp_detail" style="display: none;">

                    <div class="form-group row">                      
                      <label class="col-form-label col-lg-2">Company Name<sup class="required">*</sup> </label>
                      <div class="col-lg-10 error-msg">
                        <input type="text" name="company_name" id="company_name" class=" form-control" readonly>
                        <input type="hidden" name="company_id" id="company_id" class="form-control" readonly>
                      </div>
                    </div>
                    </div>
                   <div class="col-lg-12" style="display: none;" id='error_msg_emp'>   </div>
                  <div class="col-lg-12 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-2">Employee Name<sup class="required">*</sup></label>
                      <div class="col-lg-10 error-msg">
                        <input type="text" name="employee_name" id="employee_name" class="form-control" readonly> 
                         <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >

                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12 show_emp_detail" style="display: none;">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-2">Branch<sup class="required">*</sup></label>
                      <div class="col-lg-10 error-msg">
                        <input type="text" name="branch" id="branch" class="form-control" readonly>
                        <input type="hidden" name="branch_id" id="branch_id" class="form-control" >
                      </div>
                    </div> 
                  </div>
                <div class="col-lg-12 "><h6 class=" mb-3">Resign Details </h6></div> 
                  <div class="col-lg-12 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-2">Application File<sup class="required">*</sup></label>
                      <div class="col-lg-10 error-msg">
                        <input type="file" name="application_file" id="application_file" class="form-control">
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12 ">
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-2">Remark<sup class="required">*</sup></label>
                      <div class="col-lg-10 error-msg">

                        <textarea name="remark" id="remark" class="form-control">{{$remark}}</textarea>
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
@include('templates.admin.hr_management.employee.script_resign')
@stop