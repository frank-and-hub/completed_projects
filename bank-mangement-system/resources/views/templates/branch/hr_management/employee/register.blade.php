@extends('layouts/branch.dashboard')
@php
$dropDown = $company;
$filedTitle  = 'Company Name';
$name = 'company_id';
@endphp
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
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
          <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Register Application</h3> 
                       
                         <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                </div>
                </div>
            </div>
        </div>
@if ($errors->any())
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body"> 
              
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              
          </div>
        </div>
      </div>
    </div>
    @endif
    <form action="{!! route('branch.hr.employee_save') !!}" method="post" enctype="multipart/form-data" id="employee_register" name="employee_register"  >
    @csrf
      <div class="row">
        <div class="col-lg-7">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Employee Information</h3>
              <div class="row">
              @include('templates.GlobalTempletes.new_role_type1',[
							'dropDown'=> $branchCompany[Auth::user()->branches->id],
							'name'=>'company_id',
							'apply_col_md'=>false,
                            'filedTitle' => 'Company Name'
							]) 
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-12">Category<sup class="required">*</sup></label>
                        <div class="col-lg-12 error-msg">
                          <select   name="category" id="category" class="form-control" >  
                                <option value="">Select Category</option>
                                <option value="2">Contract</option>
                                <option value="1">On - rolled</option>                             
                          </select>

                          <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                          <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >

                          <input type="hidden" class="form-control" name="branch" id="branch" value="{{getUserBranchId(Auth::user()->id)->id}}" >
                        </div>
                    </div>
                </div>
               <!-- <div class="col-lg-6">
                  <div class="form-group row">
                      <label class="col-form-label col-lg-12">Branch<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select   name="branch" id="branch" class="form-control" >  
                            <option value="">Select Branch</option> 
                            @foreach ($branch as $val)
                                  <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                      </div>
                    </div>
                </div>-->
                <div class="col-lg-6">
                  <div class="form-group row">
                      <label class="col-form-label col-lg-12">Designation<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select   name="designation" id="designation" class="form-control" >  
                            <option value="">Select Designation</option> 
                        <!--    @foreach ($designation as $val)
                                  <option value="{{ $val->id }}">{{ $val->designation_name }}</option>
                            @endforeach
                        -->
                        </select>
                        <input type="hidden" name="salary" id="salary" class="form-control" value="{{ old('salary') }}">
                      </div>
                    </div>
                </div>
               <!-- <div class="col-lg-6">
                  <div class="form-group row">
                      <label class="col-form-label col-lg-12">Salary<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <input type="hidden" name="salary" id="salary" class="form-control" value="{{ old('salary') }}">
                      </div>
                    </div>
                </div>-->

                <div class="col-lg-6">
                  <div class="form-group row">
                      <label class="col-form-label col-lg-12">ESI Account No</label>
                      <div class="col-lg-12 error-msg">
                        <input type="text" name="esi_account_no" id="esi_account_no" class="form-control" value="{{ old('esi_no') }}">
                      </div>
                    </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group row">
                      <label class="col-form-label col-lg-12">UAN/PF  Account No</label>
                      <div class="col-lg-12 error-msg">
                        <input type="text" name="pf_account_no" id="pf_account_no" class="form-control" value="{{ old('pf_no') }}">
                      </div>
                    </div>
                </div>


                

              </div> 
            </div>
          </div>
      
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Personal Information </h3>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Recommendation Employee Name<sup class="required">*</sup> </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="recommendation_employee_name" id="recommendation_employee_name" class=" form-control" value="{{ old('recommendation_employee_name') }}" >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Recommendation Employee Designation</label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="recommendation_employee_designation" id="recommendation_employee_designation" class=" form-control" value="{{ old('recommendation_employee_designation') }}" >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Applicant Name<sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="applicant_name" id="applicant_name" class="form-control"  value="{{ old('applicant_name') }}">
                </div>
              </div>
              <div class="form-group row anniversary-date-box">
                <label class="col-form-label col-lg-4">Employee SSB Account </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="ssb_account" id="ssb_account" class="form-control"  value="{{ old('ssb_account') }}" >
                  <input type="hidden" name="ssb_account_id" id="ssb_account_id" class="form-control"  >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Date of Birth/Age<sup class="required">*</sup> </label>
                <div class="col-lg-6 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend">
                      <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                    </span>
                     <input type="text" class="form-control " name="dob" id="dob" value="{{ old('dob') }}" placeholder="DD/MM/YYYY" autocomplete="off">
                   </div>
                </div>
                <div class="col-lg-2">
                  <span id="age_display" class='age_show '> </span>
                  <input type="hidden" class="form-control " name="age" id="age" >
                </div>

              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup> </label>
                <div class="col-lg-8 error-msg">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3 ">
                        <input type="radio" id="gender_male" name="gender" class="custom-control-input gender" value="1">
                        <label class="custom-control-label" for="gender_male">Male</label>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="gender_female" name="gender" class="custom-control-input gender" value="2">
                        <label class="custom-control-label" for="gender_female">Female</label>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="gender_other" name="gender" class="custom-control-input gender" value="0">
                        <label class="custom-control-label" for="gender_other">Other</label>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="mobile_no" id="mobile_no" class="form-control"  >
                </div>
              </div>              
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Email Id</label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="email" id="email" class="form-control"  autocomplete="off" >
                  <span class="error invalid-feedback" id="email_msg"></span>
                </div>
              </div> 
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Father/Legal Guardian/Husband's Name  <sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="guardian_name" id="guardian_name" class="form-control" pattern="[A-Za-z ]+"  value="{{ old('guardian_name') }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Father/Legal Guardian/Husband's Number  <sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="guardian_number" id="guardian_number" class="form-control"  value="{{ old('guardian_number') }}" >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Mother Name<sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="mother_name" id="mother_name" class="form-control" pattern="[A-Za-z ]+"  >
                </div>
              </div> 
              <div class="form-group row">
                <label class="col-form-label col-lg-4">Marital status <sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <div class="row">
                    <div class="col-lg-5">
                      <div class="custom-control custom-radio mb-3 ">
                        <input type="radio" id="married" name="marital_status" class="custom-control-input m_status" value="1">
                        <label class="custom-control-label" for="married">Married</label>
                      </div>
                    </div>
                    <div class="col-lg-5">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="un_married" name="marital_status" class="custom-control-input m_status" value="0">
                        <label class="custom-control-label" for="un_married">Un Married</label>
                      </div>
                    </div>
                    <div class="col-lg-5">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="divorced" name="marital_status" class="custom-control-input m_status" value="2">
                        <label class="custom-control-label" for="divorced">Divorced</label>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              
              <div class="form-group row anniversary-date-box">
                <label class="col-form-label col-lg-4">Pan Number</label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="pen_number" id="pen_number" class="form-control"  value="{{ old('pen_number') }}" >
                </div>
              </div>
              <div class="form-group row anniversary-date-box">
                <label class="col-form-label col-lg-4">Aadhar Number </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="aadhar_number" id="aadhar_number" class="form-control"  value="{{ old('aadhar_number') }}" >
                </div>
              </div>
              <div class="form-group row anniversary-date-box">
                <label class="col-form-label col-lg-4">Voter Id Number  </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="voter_id" id="voter_id" class="form-control"  value="{{ old('voter_id') }}" >
                </div>
              </div>




            </div>
          </div>

           

          
        </div>
        <div class="col-lg-5">
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important "> Upload Photo  </h3>
              <div class="form-group row">
                <div class="col-lg-12 text-center ">                  
                  <span class="text-center rounded-circle w-100">
                    <img alt="Image placeholder" id="photo-preview" src="{{url('/')}}/asset/images/user.png" width="150">
                  </span>
                </div>
                <div class="custom-file error-msg " style="margin-top: 5px;">
                  <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*">
                  <label class="custom-file-label" for="photo" id="photo_label">Select photo</label>                  
                </div>
              </div>
            </div>
          </div> 
          <div class="card" >            
            <div class="card-body"> 
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Permanent Address</h3>
              <div class="form-group row ">
                <label class="col-form-label col-lg-4"> Address<sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <textarea name="permanent_address" id="permanent_address" class="form-control">{{ old('permanent_address') }}</textarea>
                </div>
              </div>

              <!--<div class="form-group  ">
                <label class="col-form-label col-lg-12">Pin Code<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <input type="text" name="pincode" id="pincode" class="form-control">
                </div>
              </div>-->
            </div>
          </div>
          <div class="card" >            
            <div class="card-body">
              <h3 class="card-title mb-3" style="margin-bottom: 0.5rem !important">Current Address</h3>
              
              <div class="form-group row ">
                <label class="col-form-label col-lg-4">Address<sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <textarea name="current_address" id="current_address" class="form-control">{{ old('current_address') }}</textarea>
                </div>
              </div>

              <!--<div class="form-group  ">
                <label class="col-form-label col-lg-12">Pin Code<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <input type="text" name="pincode" id="pincode" class="form-control">
                </div>
              </div>-->
            </div>
          </div>
          <div class="card" >            
            <div class="card-body">
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important " >Language Detail</h3>
              
              <div class="form-group row ">
                <label class="col-form-label col-lg-4">Language Known 1<sup class="required">*</sup></label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="language_known_1" id="language_known_1" class="form-control" value="{{ old('language_known_1') }}">
                </div>
              </div>
              <div class="form-group  row">
                <label class="col-form-label col-lg-4">Language Known 2 </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="language_known_2" id="language_known_2" class="form-control" value="{{ old('language_known_2') }}">
                </div>
              </div>
              <div class="form-group  row">
                <label class="col-form-label col-lg-4">Language Known 3 </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="language_known_3" id="language_known_3" class="form-control" value="{{ old('language_known_3') }}">
                </div>
              </div>

            </div>
          </div>
          <div class="card" >            
            <div class="card-body">
              <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important ">Bank Account detail </h3>
              
              <div class="form-group row ">
                <label class="col-form-label col-lg-4">Bank Name<sup class="required">*</sup> </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ old('bank_name') }}">
                </div>
              </div>
              <div class="form-group  row">
                <label class="col-form-label col-lg-4">Bank Address </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="bank_address" id="bank_address" class="form-control" value="{{ old('bank_address') }}">
                </div>
              </div>
              <div class="form-group row ">
                <label class="col-form-label col-lg-4"> Account Number<sup class="required">*</sup> </label>
                <div class="col-lg-8 error-msg">
                  <input type="password" name="account_no" id="account_no" class="form-control" autocomplete="off" >
                </div>
              </div>
              

              <div class="form-group row ">
                <label class="col-form-label col-lg-4"> Conform Account Number<sup class="required">*</sup> </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="cAccount_no" id="cAccount_no" class="form-control"autocomplete="off">
                </div>
              </div>
              
              <div class="form-group row ">
                <label class="col-form-label col-lg-4">IFSC Code<sup class="required">*</sup> </label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="ifsc_code" id="ifsc_code" class="form-control"  >
                </div>
              </div>

             </div>
           </div>
        </div>

        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
                <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important ">Educational Qualification </h3>
                <div class="table-responsive py-4">
                    <table class="table table-flush" id="qualification" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 16%">Examination</th>
                                    <th style="border: 1px solid #ddd; width: 12%">Examination Passed</th>
                                    <th style="border: 1px solid #ddd;">School/University Name </th>
                                    <th style="border: 1px solid #ddd;">Subjects</th>
                                    <th style="border: 1px solid #ddd; width: 9%">Division (%)</th> 
                                    <th style="border: 1px solid #ddd; width: 17%">Passing Year </th> 
                                    <th style="border: 1px solid #ddd; width: 3%"></th> 
                                </tr>
                            </thead> 
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                     
                    <div class="col-lg-12 error-msg">
                      <div class="row">
                        <div class="col-lg-6">
                          <div class="custom-control custom-radio mb-3 ">
                            <input type="radio" id="examination10" name="examination" class="custom-control-input examination" value="10th">
                            <label class="custom-control-label" for="examination10">10th</label>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="custom-control custom-radio mb-3  ">
                            <input type="radio" id="examination12" name="examination" class="custom-control-input examination" value="12th">
                            <label class="custom-control-label" for="examination12">12th</label>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="custom-control custom-radio mb-3  ">
                            <input type="radio" id="examinationgra" name="examination" class="custom-control-input examination" value="Graduation">
                            <label class="custom-control-label" for="examinationgra">Graduation </label>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="custom-control custom-radio mb-3  ">
                            <input type="radio" id="examinationpostgra" name="examination" class="custom-control-input examination" value="Post Graduation">
                            <label class="custom-control-label" for="examinationpostgra">Post Graduation </label>
                          </div>
                        </div>
                      </div>
                    </div> 
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                    <div class="col-lg-12 error-msg">
                      <div class="row">
                        <div class="col-lg-12">
                          <div class="custom-control custom-radio mb-3 ">
                            <input type="radio" id="examination_passed_school" name="examination_passed" class="custom-control-input examination_passed" value="School">
                            <label class="custom-control-label" for="examination_passed_school">School</label>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="custom-control custom-radio mb-3  ">
                            <input type="radio" id="examination_passed_college" name="examination_passed" class="custom-control-input examination_passed" value="College">
                            <label class="custom-control-label" for="examination_passed_college">College</label>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="custom-control custom-radio mb-3  ">
                            <input type="radio" id="examination_passed_university" name="examination_passed" class="custom-control-input examination_passed" value="University">
                            <label class="custom-control-label" for="examination_passed_university">University</label>
                          </div>
                        </div> 
                      </div>
                    </div> 
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="university_name" id="university_name" class="form-control"  value="{{ old('university_name') }}" >
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg"> 
                                          <textarea name="subject" id="subject" class="form-control">{{ old('subject') }}</textarea>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="division" id="division" class="form-control"  value="{{ old('division') }}">
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <select name="passing_year" id="passing_year" class="form-control" >
                                              <option value="">Select Passing Year</option>
                                                {{ $last= date('Y')-100 }}
                                                {{ $now = date('Y') }}

                                                @for ($i = $now; $i >= $last; $i--)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                          </select>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <button type="button" class="btn btn-primary" id="add_qualification"><i class="fas fa-plus-circle"></i></button>
                                    </td>
                                </tr>
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
                <h3 class="card-title mb-3"  style="margin-bottom: 0.4rem !important "> Diploma </h3>
                <div class="table-responsive py-4">
                    <table class="table table-flush" id="diploma" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 16%">Diploma Course</th>
                                    <th style="border: 1px solid #ddd; width: 12%">Academy</th>
                                    <th style="border: 1px solid #ddd;">Institute/University Name </th>
                                    <th style="border: 1px solid #ddd;">Subjects</th>
                                    <th style="border: 1px solid #ddd; width: 9%">Division (%)</th> 
                                    <th style="border: 1px solid #ddd; width: 17%">Passing Year </th> 
                                    <th style="border: 1px solid #ddd; width: 3%"></th> 
                                </tr>
                            </thead> 
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="diploma_course" id="diploma_course" class="form-control  "  value="{{ old('diploma_course') }}" >
                                        </div>
                                    </td>
                                     <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="academy" id="academy" class="form-control  "  value="{{ old('academy') }}" >
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="diploma_university_name" id="diploma_university_name" class="form-control  "  value="{{ old('diploma_university_name') }}" >
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg"> 
                                          <textarea name="diploma_subject" id="diploma_subject" class="form-control  ">{{ old('diploma_subject') }}</textarea>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="diploma_division" id="diploma_division" class="form-control  "  value="{{ old('diploma_division') }}">
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <select name="diploma_passing_year" id="diploma_passing_year" class="form-control  " >
                                              <option value="">Select Passing Year</option>
                                                {{ $last= date('Y')-100 }}
                                                {{ $now = date('Y') }}

                                                @for ($i = $now; $i >= $last; $i--)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                          </select>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <button type="button" class="btn btn-primary" id="add_diploma"><i class="fas fa-plus-circle"></i></button>
                                    </td>
                                </tr>
                            </tbody>                   
                        </table>
                    </div>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
                <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important "> Work Experience </h3>
                <div class="table-responsive py-4">
                    <table class="table table-flush" id="experience" style="margin-bottom: 0.4rem !important ">
                        <thead class="thead-light">
                                <tr>
                                    <th style="border: 1px solid #ddd; width: 15%" >Company Name</th>
                                    <th style="border: 1px solid #ddd; width: 35%">Years</th>
                                    <th style="border: 1px solid #ddd; width: 15%">Nature Of work</th>
                                    <th style="border: 1px solid #ddd; width: 12%">Salary</th>
                                    <th style="border: 1px solid #ddd; width: 20%">Immediate Supervisor Reference </th>   
                                    <th style="border: 1px solid #ddd; width: 3%"></th> 
                                </tr>
                            </thead> 
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="company_name" id="company_name" class="form-control  "  value="{{ old('company_name') }}" >
                                        </div>
                                    </td>
                                     <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 ">
            <div class="form-group row">
                <label class="col-form-label col-lg-2">From </label>
                <div class="col-lg-10 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend" >
                      <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                    </span>
                     <input type="text" class="form-control  " name="work_start" id="work_start" value="{{ old('work_start') }}" placeholder="DD/MM/YYYY">
                   </div>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">To </label>
                <div class="col-lg-10 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend" >
                      <span class="input-group-text" ><i class="fa fa-calendar" aria-hidden="true"></i></span>
                    </span>
                     <input type="text" class="form-control  " name="work_end" id="work_end" value="{{ old('work_end') }}"  placeholder="DD/MM/YYYY">
                   </div>
                </div>
              </div>
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="nature_work" id="nature_work" class="form-control  "  value="{{ old('nature_work') }}" >
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12 error-msg">
                                          <input type="text" name="work_salary" id="work_salary" class="form-control  "  value="{{ old('work_salary') }}">
                                        </div>
                                    </td>
                                    <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                        <div class="col-lg-12">
            <div class="form-group row">
                <label class="col-form-label col-lg-3">Name </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="reference_name" id="reference_name" class="form-control  "  value="{{ old('reference_name') }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">No. </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="reference_no" id="reference_no" class="form-control  "  value="{{ old('reference_no') }}">
                </div>
              </div>
                                        </div>
                                    </td> 
                                    <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                        <button type="button" class="btn btn-primary" id="add_experience"><i class="fas fa-plus-circle"></i></button>
                                    </td>
                                </tr>
                            </tbody>                   
                        </table>
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



     
@stop

@section('script')
@include('templates.branch.hr_management.employee.script_application_register')
@stop