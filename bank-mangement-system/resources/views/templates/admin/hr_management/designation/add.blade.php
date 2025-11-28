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
          <h4 class="card-title mb-3">Add Designation</h4>
        </div>
        <div class="card-body">
          <form action="{!! route('admin.hr.designation_save') !!}" method="post" enctype="multipart/form-data" id="designation_add" name="designation_add"  >
            @csrf
            <div class="row">
              <div class="col-lg-12"> 
                <div class="row">
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Designation Name <sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="designation_name" id="designation_name" class="form-control"  >
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Category<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="category" id="category" class="form-control" >  
                          <option value="">Select category</option> 
                          <option value="1">On-rolled</option> 
                          <option value="2">Contract</option> 

                        </select>
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Basic Salary <sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="basic_salary" id="basic_salary" class="form-control"   onkeyup="gross_salary_calculate();">
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Daily Allowances </label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="daily_allowances" id="daily_allowances" class="form-control"   onkeyup="gross_salary_calculate();">
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">HRA</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="hra" id="hra" class="form-control"  onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">HRA Metro City</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="hra_metro_city" id="hra_metro_city" class="form-control"  onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">UMA</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="uma" id="uma" class="form-control"   onkeyup="gross_salary_calculate();">
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Convenience Charges</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="convenience_charges" id="convenience_charges" class="form-control"  onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Maintenance Allowance</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="maintenance_allowance" id="maintenance_allowance" class="form-control"  onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Communication Allowance</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="communication_allowance" id="communication_allowance" class="form-control" onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">PRD</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="prd" id="prd" class="form-control" onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">IA</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="ia" id="ia" class="form-control" onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">CA</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="ca" id="ca" class="form-control" onkeyup="gross_salary_calculate();" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">FA</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="fa" id="fa" class="form-control"  onkeyup="gross_salary_calculate();">
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">PF</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="pf" id="pf" class="form-control"  onkeyup="gross_salary_calculate();">
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">TDS</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="tds" id="tds" class="form-control"  onkeyup="gross_salary_calculate();">
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Gross Salary</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="gross_salary" id="gross_salary" class="form-control"   readonly>
                      </div>
                    </div>
                  </div>
                  
                  
                   

                </div>
              </div>


              <div class="col-lg-12">
                <div class="form-group row text-center"> 
                  <div class="col-lg-12 ">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

        

  </div>
</div>
@include('templates.admin.hr_management.designation.script_add')
@stop