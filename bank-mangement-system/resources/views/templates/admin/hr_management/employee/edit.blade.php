@extends('templates.admin.master')
@php

$dropDown = $company;

$filedTitle  = 'Company';

$name = 'company_id';

@endphp
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
      <form action="{!! route('admin.hr.employee_update') !!}" method="post" enctype="multipart/form-data" id="employee_edit" name="employee_edit"  >
         @csrf
         <div class="row">
            <div class="col-lg-7">
               <div class="card bg-white" >
                  <div class="card-body">
                     <h3 class="card-title mb-3">Employee Information</h3>
                     <div class="row">

                     @include('templates.GlobalTempletes.new_role_type',[
                        'dropDown'=>$dropDown,
                        'filedTitle'=>$filedTitle,
                        'name'=>$name,'value'=>'selected',
                        'multiselect'=>'false',
                        'design_type'=>6,
                        'branchShow'=>true,
                        'branchName'=>'branch_id',
                        'apply_col_md'=>false,
                        'multiselect'=>false,
                        'placeHolder1'=>'Please Select Company',
                        'placeHolder2'=>'Please Select Branch',
                        'selectedCompany'  =>$employee['company']->id,
                        'selectedBranch'  => $employee->branch_id,
                       
                        
                        ]) 
                              @if($employee->is_employee == 1)
                                    <script>
                                       $(document).ready(function() {
                                       $("#company_id").prop("disabled", true);
                                       $("#branch").prop("disabled", true);
                                       });
                                    </script>
                              @endif

                              <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-12">Register Date<sup class="required">*</sup></label>
                              <div class="col-lg-12 error-msg">
                                 <div class="input-group">
                                    <input type="text" name="select_date" id="select_date" class="form-control  " readonly value="{{ date('d/m/Y', strtotime($employee->created_at)) }}">

                                    </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-12">Category<sup class="required">*</sup></label>
                              <div class="col-lg-12 error-msg">
                                 <select   name="category" id="category" class="form-control" >
                                    <option value="">Select Category</option>
                                    <option value="2" @if($employee->category==2) selected @endif>Contract</option>
                                    <option value="1" @if($employee->category==1) selected @endif >On - rolled</option>                             
                                 </select>
                                 <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                 <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                              </div>
                           </div>
                        </div>
                       
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-12">Designation<sup class="required">*</sup></label>
                              <div class="col-lg-12 error-msg">
                                 <select   name="designation" id="designation" class="form-control" >
                                    <option value="">Select Designation</option>
                                 </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-12">Gross Salary<sup class="required">*</sup></label>
                              <div class="col-lg-12 error-msg">
                                 <input type="text" name="salary" id="salary" class="form-control" value="{{number_format((float)$employee->salary,2, '.', '')}}">
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-12">ESI Account No</label>
                              <div class="col-lg-12 error-msg">
                                 <input type="text" name="esi_account_no" id="esi_account_no" class="form-control" value="{{ $employee->esi_account_no}}">
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-12">UAN/PF  Account No</label>
                              <div class="col-lg-12 error-msg">
                                 <input type="text" name="pf_account_no" id="pf_account_no" class="form-control" value="{{ $employee->pf_account_no}}">
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
                        <label class="col-form-label col-lg-4">Customer ID:</label>
                        <div class="col-lg-8 error-msg">
                        <input type="text" name="customer_id" id="customer_id" class="form-control" @if($member) value="{{$member->member_id}}" @endif readonly>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Recommendation Employee Name<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="recommendation_employee_name" id="recommendation_employee_name" class=" form-control" value="{{ $employee->recommendation_employee_name}}" >

                           <input type="hidden" name="employee_id" id="employee_id" class=" form-control" value="{{ $employee->id}}" >
                           @if(isset($_GET['type']))
                              <input type="hidden" name="app_id" id="app_id" class=" form-control" value="{{ $app_id}}" >
                           @endif
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Recommendation Employee Designation <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="recom_employee_designation" id="recom_employee_designation" class="form-control"  value="{{ $employee->recom_employee_designation }}">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Employee Name<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="applicant_name" id="applicant_name" class="form-control"  value="{{ $employee->employee_name }}">
                        </div>
                     </div>
                     <div class="form-group row anniversary-date-box">
                        <label class="col-form-label col-lg-4">Employee SSB Account </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="ssb_account" id="ssb_account" class="form-control"  value="{{ $employee->ssb_account }}" >
                           <input type="hidden" name="ssb_account_id" id="ssb_account_id" class="form-control"   value="{{ $employee->ssb_id }}">
                        </div>
                     </div>
                     <?php
                           if($employee->ssb_id>0)
                           {
                              $ssbDate=date('d/m/Y', strtotime(getSavingAccountMemberId($employee->ssb_id)->created_at));
                           }
                           else
                           {
                              $ssbDate='';
                           }
                     ?>

              <div class="form-group row ssb_account_date-date-box">
                <label class="col-form-label col-lg-4">Employee SSB Account Date</label>
                <div class="col-lg-8 error-msg">
                  <input type="text" name="ssb_account_date" id="ssb_account_date" class="form-control"  value="{{ $ssbDate }}"> 
                </div>
              </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Date of Birth/Age<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                           <div class="input-group">
                              <span class="input-group-prepend">
                              <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                              </span>
                              <input type="text" class="form-control " name="dob" id="dob" value="{{ date('d/m/Y', strtotime($employee->dob)) }}">
                           </div>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                           <div class="row">
                              <div class="col-lg-4">
                                 <div class="custom-control custom-radio mb-3 ">
                                    <input type="radio" id="gender_male" name="gender" class="custom-control-input gender" value="1" @if($employee->gender==1) checked @endif>
                                    <label class="custom-control-label" for="gender_male">Male</label>
                                 </div>
                              </div>
                              <div class="col-lg-4">
                                 <div class="custom-control custom-radio mb-3  ">
                                    <input type="radio" id="gender_female" name="gender" class="custom-control-input gender" value="2" @if($employee->gender==2) checked @endif>
                                    <label class="custom-control-label" for="gender_female">Female</label>
                                 </div>
                              </div>
                              <div class="col-lg-4">
                                 <div class="custom-control custom-radio mb-3  ">
                                    <input type="radio" id="gender_other" name="gender" class="custom-control-input gender" value="0" @if($employee->gender==0) checked @endif>
                                    <label class="custom-control-label" for="gender_other">Other</label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="mobile_no" id="mobile_no" class="form-control" value="{{$employee->mobile_no}}" >
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Email Id</label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="email" id="email" class="form-control"  value="{{$employee->email}}">
                           <span class="error invalid-feedback" id="email_msg"></span>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Father/Legal Guardian/Husband's Name  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="guardian_name" id="guardian_name" class="form-control" value="{{$employee->father_guardian_name}}">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Father/Legal Guardian/Husband's Number  <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="guardian_number" id="guardian_number" class="form-control" value="{{$employee->father_guardian_number}}">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mother Name<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="mother_name" id="mother_name" class="form-control"  value="{{$employee->mother_name}}">
                        </div>
                     </div>
                     <div class="form-group row">
                        <label class="col-form-label col-lg-4">Marital status <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <div class="row">
                              <div class="col-lg-5">
                                 <div class="custom-control custom-radio mb-3 ">
                                    <input type="radio" id="married" name="marital_status" class="custom-control-input m_status" value="1" @if($employee->marital_status==1) checked @endif>
                                    <label class="custom-control-label" for="married">Married</label>
                                 </div>
                              </div>
                              <div class="col-lg-5">
                                 <div class="custom-control custom-radio mb-3  ">
                                    <input type="radio" id="un_married" name="marital_status" class="custom-control-input m_status" value="0" @if($employee->marital_status==0) checked @endif>
                                    <label class="custom-control-label" for="un_married">Un Married</label>
                                 </div>
                              </div>
                              <div class="col-lg-5">
                                 <div class="custom-control custom-radio mb-3  ">
                                    <input type="radio" id="divorced" name="marital_status" class="custom-control-input m_status" value="2" @if($employee->marital_status==2) checked @endif>
                                    <label class="custom-control-label" for="divorced">Divorced</label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <div class="form-group row anniversary-date-box">
                        <label class="col-form-label col-lg-4">Pan Number</label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="pen_number" id="pen_number" class="form-control"  value="{{ $employee->pen_card }}" >
                        </div>
                     </div>
                     <div class="form-group row anniversary-date-box">
                        <label class="col-form-label col-lg-4">Aadhar Number </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="aadhar_number" id="aadhar_number" class="form-control"  value="{{ $employee->aadhar_card }}" >
                        </div>
                     </div>
                     <div class="form-group row anniversary-date-box">
                        <label class="col-form-label col-lg-4">Voter Id Number  </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="voter_id" id="voter_id" class="form-control"  value="{{ $employee->voter_id }}" >
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
                           {{-- 
                           @if($employee->photo=='')
                           <img alt="Image placeholder" id="photo-preview" src="{{url('/')}}/asset/images/user.png" width="150">
                           @else
                           <img alt="Image placeholder" id="photo-preview" src="{{ImageUpload::generatePreSignedUrl('employee/' . $employee->photo)}}" width="150">
                           @endif
                           --}}
                           @if($employee->photo=='')
                           <?php 
                              $employee_photo_url = url('/') . "/asset/images/user.png";
                              ?>
                           @else
                              <?php
                              $employeefolderName = 'employee/'.$employee->photo;
                              if (ImageUpload::fileExists($employeefolderName) && $employee->photo != '') {
                                 $employee_photo_url = ImageUpload::generatePreSignedUrl($employeefolderName);
                              } else {
                                 $employee_photo_url = url('/') . "/asset/images/user.png";
                              }
                              ?>
                           @endif
                           <img alt="Image placeholder" id="photo-preview" src="{{$employee_photo_url}}" width="150">
                           </span>
                        </div>
                        <div class="custom-file error-msg " style="margin-top: 5px;">
                           <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*">
                           <label class="custom-file-label" for="photo" id="photo_label">Select photo</label>
                          {{-- <span class="form-text text-muted">Accepted formats:png, jpg.</span>--}}
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
                           <textarea name="permanent_address" id="permanent_address" class="form-control">{{ $employee->permanent_address }}</textarea>
                        </div>
                     </div>
                    
                  </div>
               </div>
               <div class="card" >
                  <div class="card-body">
                     <h3 class="card-title mb-3" style="margin-bottom: 0.5rem !important">Current Address</h3>
                     <div class="form-group row ">
                        <label class="col-form-label col-lg-4">Address<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <textarea name="current_address" id="current_address" class="form-control">{{ $employee->current_address }}</textarea>
                        </div>
                     </div>
                  
                  </div>
               </div>
               <div class="card" >
                  <div class="card-body">
                     <h3 class="card-title mb-3" style="margin-bottom: 0.4rem !important " >Language Detail</h3>
                     <div class="form-group row ">
                        <label class="col-form-label col-lg-4">Language Known 1<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="language_known_1" id="language_known_1" class="form-control" value="{{ $employee->language1 }}">
                        </div>
                     </div>
                     <div class="form-group  row">
                        <label class="col-form-label col-lg-4">Language Known 2 </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="language_known_2" id="language_known_2" class="form-control" value="{{ $employee->language2 }}">
                        </div>
                     </div>
                     <div class="form-group  row">
                        <label class="col-form-label col-lg-4">Language Known 3 </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="language_known_3" id="language_known_3" class="form-control" value="{{ $employee->language3 }}">
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
                           <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ $employee->bank_name }}">
                        </div>
                     </div>
                     <div class="form-group  row">
                        <label class="col-form-label col-lg-4">Bank Address </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="bank_address" id="bank_address" class="form-control" value="{{ $employee->bank_address }}">
                        </div>
                     </div>
                     <div class="form-group row ">
                        <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                           <input type="password" name="account_no" id="account_no" class="form-control" value="{{ $employee->bank_account_no }}">
                        </div>
                     </div>
                     <div class="form-group row ">
                        <label class="col-form-label col-lg-4">Confrom Account Number<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="cAccount_no" id="cAccount_no" class="form-control" value="{{ $employee->bank_account_no }}" >
                        </div>
                     </div>
                     <div class="form-group row ">
                        <label class="col-form-label col-lg-4">IFSC Code<sup class="required">*</sup> </label>
                        <div class="col-lg-8 error-msg">
                           <input type="text" name="ifsc_code" id="ifsc_code" class="form-control"  value="{{ $employee->bank_ifsc_code }}">
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
                              @if($qualification)
                              @foreach($qualification as $k=>$val)
                              <tr id="qual_rem_{{$val->id}}">
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="radio" id="qualification_id_{{$k}}" name="qualification_id[{{$k}}]" class="custom-control-input  "value="{{$val->id}}"  >
                                       <div class="row">
                                          <div class="col-lg-6">
                                             <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="examination10_old_{{$k}}" name="examination_old[{{$k}}]" class="custom-control-input examination_more exmore" value="10th" @if($val->exam_name=='10th') checked @endif>
                                                <label class="custom-control-label" for="examination10_old_{{$k}}">10th</label>
                                             </div>
                                          </div>
                                          <div class="col-lg-6">
                                             <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="examination12_old_{{$k}}" name="examination_old[{{$k}}]" class="custom-control-input examination_more exmore" value="12th" @if($val->exam_name=='12th') checked @endif>
                                                <label class="custom-control-label" for="examination12_old_{{$k}}">12th</label>
                                             </div>
                                          </div>
                                          <div class="col-lg-12">
                                             <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="examinationgra_old_{{$k}}" name="examination_old[{{$k}}]" class="custom-control-input examination_more exmore" value="Graduation" @if($val->exam_name=='Graduation') checked @endif>
                                                <label class="custom-control-label" for="examinationgra_old_{{$k}}">Graduation </label>
                                             </div>
                                          </div>
                                          <div class="col-lg-12">
                                             <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="examinationpostgra_old_{{$k}}" name="examination_old[{{$k}}]" class="custom-control-input examination_more exmore" value="Post Graduation" @if($val->exam_name=='Post Graduation') checked @endif>
                                                <label class="custom-control-label" for="examinationpostgra_old_{{$k}}">Post Graduation </label>
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
                                                <input type="radio" id="examination_passed_school_old_{{$k}}" name="examination_passed_old[{{$k}}]" class="custom-control-input examination_passed_more" value="School" @if($val->exam_from=='School') checked @endif>
                                                <label class="custom-control-label" for="examination_passed_school_old_{{$k}}">School</label>
                                             </div>
                                          </div>
                                          <div class="col-lg-12">
                                             <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="examination_passed_college_old_{{$k}}" name="examination_passed_old[{{$k}}]" class="custom-control-input examination_passed_more" value="College" @if($val->exam_from=='College') checked @endif>
                                                <label class="custom-control-label" for="examination_passed_college_old_{{$k}}">College</label>
                                             </div>
                                          </div>
                                          <div class="col-lg-12">
                                             <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="examination_passed_university_old_{{$k}}" name="examination_passed_old[{{$k}}]" class="custom-control-input examination_passed_more" value="University" @if($val->exam_from=='University') checked @endif>
                                                <label class="custom-control-label" for="examination_passed_university_old_{{$k}}">University</label>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="university_name_old[{{$k}}]" id="university_name_old_{{$k}}" class="form-control university_name_more"  value="{{ $val->board_university }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg"> 
                                       <textarea name="subject_old[{{$k}}]" id="subject_old_{{$k}}" class="form-control subject_more">{{ $val->subject }}</textarea>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="division_old[{{$k}}]" id="division_old_{{$k}}" class="form-control division_more"  value="{{ $val->per_division }}">
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <select name="passing_year_old[{{$k}}]" id="passing_year_old_{{$k}}" class="form-control passing_year_more" >
                                          <option value="">Select Passing Year</option>
                                          {{ $last= date('Y')-100 }}
                                          {{ $now = date('Y') }}
                                          @for ($i = $now; $i >= $last; $i--)
                                          <option value="{{ $i }}" @if($val->passing_year==$i) selected @endif >{{ $i }}</option>
                                          @endfor
                                       </select>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <button type="button" class="btn btn-primary remCF_old" id="delete_qualification" value="{{$val->id}}"><i class="icon-trash"></i></button>
                                 </td>
                              </tr>
                              @endforeach 
                              @endif
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
                                    <button type="button" class="btn btn-primary" id="add_qualification"><i class="icon-add"></i></button>
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
                              @if($diploma)
                              @foreach($diploma as $k=>$val)
                              <tr id="diploma_rem_{{$val->id}}">
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="diploma_course_old[{{$k}}]" id="diploma_course_old_{{$k}}" class="form-control diploma_course"  value="{{ $val->course }}" >
                                       <input type="hidden" name="diploma_id_old[{{$k}}]" id="diploma_id_old_{{$k}}" class="form-control "  value="{{ $val->id }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="academy_old[{{$k}}]" id="academy_old_{{$k}}" class="form-control academy"  value="{{ $val->academy }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="diploma_university_name_old[{{$k}}]" id="diploma_university_name_old_{{$k}}" class="form-control diploma_university"  value="{{ $val->board_university }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg"> 
                                       <textarea name="diploma_subject_old[{{$k}}]" id="diploma_subject_old_{{$k}}" class="form-control diploma_subject">{{ $val->subject }}</textarea>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="diploma_division_old[{{$k}}]" id="diploma_division_old_{{$k}}" class="form-control diploma_division"  value="{{ $val->per_division }}">
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <select name="diploma_passing_year_old[{{$k}}]" id="diploma_passing_year_old_{{$k}}" class="form-control  diploma_passing_year " >
                                          <option value="">Select Passing Year</option>
                                          {{ $last= date('Y')-100 }}
                                          {{ $now = date('Y') }}
                                          @for ($i = $now; $i >= $last; $i--)
                                          <option value="{{ $i }}" @if($val->passing_year==$i) selected @endif>{{ $i }}</option>
                                          @endfor
                                       </select>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <button type="button" class="btn btn-primary remDiploma_old" id="delete_diploma" value="{{$val->id}}"><i class="icon-trash"></i></button>
                                 </td>
                              </tr>
                              @endforeach
                              @endif
                              <tr>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="diploma_course" id="diploma_course" class="form-control "  value="{{ old('diploma_course') }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="academy" id="academy" class="form-control "  value="{{ old('academy') }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="diploma_university_name" id="diploma_university_name" class="form-control "  value="{{ old('diploma_university_name') }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg"> 
                                       <textarea name="diploma_subject" id="diploma_subject" class="form-control ">{{ old('diploma_subject') }}</textarea>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="diploma_division" id="diploma_division" class="form-control "  value="{{ old('diploma_division') }}">
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <select name="diploma_passing_year" id="diploma_passing_year" class="form-control " >
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
                                    <button type="button" class="btn btn-primary" id="add_diploma"><i class="icon-add"></i></button>
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
                                 <th style="border: 1px solid #ddd;" >Company Name</th>
                                 <th style="border: 1px solid #ddd; width: 21%">Years</th>
                                 <th style="border: 1px solid #ddd;">Nature Of work</th>
                                 <th style="border: 1px solid #ddd; width: 9%">Salary</th>
                                 <th style="border: 1px solid #ddd; width: 25%">Immediate Supervisor Reference </th>
                                 <th style="border: 1px solid #ddd; width: 3%"></th>
                              </tr>
                           </thead>
                           <tbody>
                  @if($work)
                  @foreach($work as $k=>$val)
                              <tr id="work_rem_{{$val->id}}">
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="company_name_old[{{$k}}]" id="company_name_old_{{$k}}" class="form-control  company_name"  value="{{  $val->company_name  }}" >

                                       <input type="hidden" name="work_id[{{$k}}]" id="work_id{{$k}}" class="form-control  "  value="{{  $val->id  }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 ">
                                       <div class="form-group row">
                                          <label class="col-form-label col-lg-3">From </label>
                                          <div class="col-lg-9 error-msg">
                                             <div class="input-group">
                                                <span class="input-group-prepend" style="margin-right: 0.5rem;">
                                                <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                                </span>
                                                <input type="text" class="form-control  work_start_old" name="work_start_old[{{$k}}]" id="work_start_old_{{$k}}" value="{{  date('d/m/Y', strtotime($val->from_date))  }}">
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-form-label col-lg-3">To </label>
                                          <div class="col-lg-9 error-msg">
                                             <div class="input-group">
                                                <span class="input-group-prepend" style="margin-right: 0.5rem;">
                                                <span class="input-group-text" ><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                                </span>
                                                <input type="text" class="form-control  work_start_old" name="work_end_old[{{$k}}]" id="work_end_old_{{$k}}" value="{{  date('d/m/Y', strtotime($val->to_date))  }}">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="nature_work_old[{{$k}}]" id="nature_work_old_{{$k}}" class="form-control  nature_work"  value="{{  $val->work_nature }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="work_salary_old[{{$k}}]" id="work_salary_old_{{$k}}" class="form-control  work_salary"  value="{{  number_format((float)$val->salary, 2, '.', '')}}">
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd; padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12">
                                       <div class="form-group row">
                                          <label class="col-form-label col-lg-3">Name </label>
                                          <div class="col-lg-9 error-msg">
                                             <input type="text" name="reference_name_old[{{$k}}]" id="reference_name_old_{{$k}}" class="form-control  reference_name"  value="{{  $val->supervisor_name  }}">
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-form-label col-lg-3">No. </label>
                                          <div class="col-lg-9 error-msg">
                                             <input type="text" name="reference_no_old[{{$k}}]" id="reference_no_old_{{$k}}" class="form-control  reference_no"  value="{{ $val->supervisor_number }}">
                                          </div>
                                       </div>
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <button type="button" class="btn btn-primary remWork_old" id="delete_experience" value="{{$val->id}}"><i class="icon-trash"></i></button>
                                 </td>
                              </tr>
                @endforeach
              @endif
                              <tr>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 error-msg">
                                       <input type="text" name="company_name" id="company_name" class="form-control  "  value="{{ old('company_name') }}" >
                                    </div>
                                 </td>
                                 <td style="border: 1px solid #ddd;padding: 0.5rem 0.1rem;">
                                    <div class="col-lg-12 ">
                                       <div class="form-group row">
                                          <label class="col-form-label col-lg-3">From </label>
                                          <div class="col-lg-9 error-msg">
                                             <div class="input-group">
                                                <span class="input-group-prepend" style="margin-right: 0.5rem;">
                                                <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                                </span>
                                                <input type="text" class="form-control  " name="work_start" id="work_start" value="{{ old('work_start') }}">
                                             </div>
                                          </div>
                                       </div>
                                       <div class="form-group row">
                                          <label class="col-form-label col-lg-3">To </label>
                                          <div class="col-lg-9 error-msg">
                                             <div class="input-group">
                                                <span class="input-group-prepend" style="margin-right: 0.5rem;">
                                                <span class="input-group-text" ><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                                </span>
                                                <input type="text" class="form-control  " name="work_end" id="work_end" value="{{ old('work_end') }}">
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
                                    <button type="button" class="btn btn-primary" id="add_experience"><i class="icon-add"></i></button>
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





@include('templates.admin.hr_management.employee.script_edit')
@stop