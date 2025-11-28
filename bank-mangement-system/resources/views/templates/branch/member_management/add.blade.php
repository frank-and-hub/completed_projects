@extends('layouts/branch.dashboard')
@section('css')
  <style>
  .select2-container--default .select2-selection--single{
    height: 50px;
    
}
.hideTableData {
 display: none;
}

    </style>
@endsection
@section('content')

<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
          <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                <h3 class="">Member Registration</h3>
                <a href="{!! route('branch.member_list') !!}" style="float:right" class="btn btn-secondary">Back</a> 
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
    <form action="{!! route('branch.member_save') !!}" method="post" enctype="multipart/form-data" id="member_register" name="member_register">
    @csrf
    <input type="hidden" name="created_at" id="created_at" value="">
      <div class="row">
        <div class="col-lg-8">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Member Form Information</h3>
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group row">
                  <label class="col-form-label col-lg-12">Form No<sup class="required">*</sup></label>
                  <div class="col-lg-12 error-msg">
                    <input type="text" name="form_no" id="form_no" class="form-control removeSpaceInput" value="{{ old('form_no') }}"  >
                  </div>
                </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group row">
                  <label class="col-form-label col-lg-12">Application Date<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                    </span>
                    @php
                      $stateid = getBranchStateByManagerId(Auth::user()->id);
                    @endphp
                     <input type="text" class="form-control" name="application_date" id="application_date"  readonly value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}  ">
                   </div>
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
                <label class="col-form-label col-lg-3">First Name<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="first_name" id="first_name" class=" form-control" value="{{ old('first_name') }}" >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Last Name </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="last_name" id="last_name" class="form-control"  value="{{ old('last_name') }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Email Id</label>
                <div class="col-lg-9 error-msg">
                  <input type="email" name="email" id="email" class="form-control removeSpaceInput"  value="{{ old('email') }}" >
                  <span class="error invalid-feedback" id="email_msg"></span>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup></label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="mobile_no" id="mobile_no" class="form-control removeSpaceInput"  >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Date of Birth/Age<sup class="required">*</sup> </label>
                <div class="col-lg-6 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                    </span>
                     <input type="text" class="form-control " readonly name="dob" id="dob" readonly>
                   </div>
                </div>
                
                <div class="col-lg-3">
                  <span id="age_display" class='age_show '> </span>
                  <input type="hidden" class="form-control " name="age" id="age" >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Gender<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3 ">
                        <input type="radio" id="gender_male" name="gender" class="custom-control-input" value="1">
                        <label class="custom-control-label" for="gender_male">Male</label>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="gender_female" name="gender" class="custom-control-input" value="0">
                        <label class="custom-control-label" for="gender_female">Female</label>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              <div class="form-group row ">
                <label class="col-form-label col-lg-3">Occupation</label>
                <div class="col-lg-9 error-msg">
                  <select class="form-control select" name="occupation" id="occupation" >  
                    <option value="">Select Occupation</option>
                    @foreach( $occupation as $val )
                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Annual Income </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="annual_income" id="annual_income" class="form-control"  >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Mother Name<sup class="required">*</sup></label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="mother_name" id="mother_name" class="form-control"  >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Father/ Husband's Name<sup class="required">*</sup></label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="f_h_name" id="f_h_name" class="form-control"  >
                </div>
              </div>

              <div class="form-group row">
                <label class="col-form-label col-lg-3">Marital status<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <div class="row">
                    <div class="col-lg-5">
                      <div class="custom-control custom-radio mb-3 ">
                        <input type="radio" id="married" name="marital_status" class="custom-control-input m-status" value="1">
                        <label class="custom-control-label" for="married">Married</label>
                      </div>
                    </div>
                    <div class="col-lg-5">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="un_married" name="marital_status" class="custom-control-input m-status" value="0">
                        <label class="custom-control-label" for="un_married">Un Married</label>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              <div class="form-group row anniversary-date-box">
                <label class="col-form-label col-lg-3">Anniversary Date </label>
                <div class="col-lg-9 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                    </span>
                     <input type="text" class="form-control " readonly name="anniversary_date" id="anniversary_date" >
                   </div>
                </div>
              </div>
              <div class="form-group row ">
                <label class="col-form-label col-lg-3">Religion</label>
                <div class="col-lg-9 error-msg">
                  <select class="form-control select" name="religion" id="religion" >  
                    <option value="0">Select Religion</option>
                    @foreach( $religion as $val )
                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group row ">
                <label class="col-form-label col-lg-3">Categories</label>
                <div class="col-lg-9 error-msg">
                  <select class="form-control select" name="special_category" id="special_category" >  
                    <option value="0">General Category</option>
                    @foreach( $specialCategory as $val )
                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> @endforeach
                  </select>
                </div>
              </div>

            </div>
          </div>

          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Bank Information </h3>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Bank Name</label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="bank_name" id="bank_name" class=" form-control"  >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Branch Name</label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="bank_branch_name" id="bank_branch_name" class=" form-control"  >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Bank A/C No</label>
                <div class="col-lg-9 error-msg">
                  <input type="password" name="bank_account_no" id="bank_account_no" class="removeSpaceInput form-control"  >
                </div>
              </div>
              <div class="form-group row">
                  <label class="col-form-label col-lg-3">Confirm Bank A/C No</label>
                  <div class="col-lg-9 error-msg">
                      <input type="text" name="cbank_account_no" id="cbank_account_no" class="removeSpaceInput form-control" value="" >
                  </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">IFSC Code</label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="bank_ifsc" id="bank_ifsc" class="removeSpaceInput form-control"  >
                </div>
              </div>
              <div class="form-group row ">
                <label class="col-form-label col-lg-3">Branch Address</label>
                <div class="col-lg-9 error-msg">
                  <textarea name="bank_branch_address" id="bank_branch_address" class="form-control"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3">Nominee Information </h3>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Full Name<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="nominee_first_name" id="nominee_first_name" class=" form-control"  >
                </div>
              </div> 
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Relationship </label>
                <div class="col-lg-9 error-msg">
                  
                  <select name="nominee_relation" id="nominee_relation" class=" form-control">
                    <option value="">Select Relation</option>
                    @foreach ($relations as $val)
                        <option value="{{ $val->id }}">{{ $val->name }}</option>
                     @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Gender<sup class="required">*</sup></label>
                <div class="col-lg-9 error-msg">
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3 ">
                        <input type="radio" id="nominee_gender_male" name="nominee_gender" class="custom-control-input" value="1">
                        <label class="custom-control-label" for="nominee_gender_male">Male</label>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="custom-control custom-radio mb-3  ">
                        <input type="radio" id="nominee_gender_female" name="nominee_gender" class="custom-control-input" value="0">
                        <label class="custom-control-label" for="nominee_gender_female">Female</label>
                      </div>
                    </div>
                  </div>
                </div> 
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Date of Birth<sup class="required">*</sup></label>
                <div class="col-lg-6 error-msg">
                  <div class="input-group">
                    <span class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                    </span>
                     <input type="text" class="form-control  " name="nominee_dob" id="nominee_dob" readonly >
                   </div>
                </div>
                <div class="col-lg-3">
                  <span id="nominee_age_display" class='nominee_age_show'> </span>
                  <input type="hidden" class="form-control " name="nominee_age" id="nominee_age" >
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="nominee_mobile_no" id="nominee_mobile_no" class="removeSpaceInput form-control"  >
                </div>
              </div>
              <div class="form-group row" id="minor_hide">
                <label class="col-form-label col-lg-3">If Minor </label>
                <div class="col-lg-9 error-msg">
                  <div class="row">
                    <div class="col-lg-9">
                      <div class="custom-control custom-checkbox mb-3 ">
                        <input type="checkbox" id="is_minor" name="is_minor" class="custom-control-input" value="1" >
                        <label class="custom-control-label" for="is_minor" value="on" checked onclick="return false;">Yes</label>
                      </div>
                    </div> 
                  </div>
                </div> 
              </div>
              <div id="nominee_parent_detail" style="display: none">
                <div class="form-group row">
                  <label class="col-form-label col-lg-3">Parent Name <sup class="required">*</sup> </label>
                  <div class="col-lg-9 error-msg">
                    <input type="text" name="parent_nominee_name" id="parent_nominee_name" class=" form-control"  >
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-form-label col-lg-3">Parent Mobile No<sup class="required">*</sup> </label>
                  <div class="col-lg-9 error-msg">
                    <input type="text" name="parent_nominee_mobile_no" id="parent_nominee_mobile_no" class=" form-control"  >
                  </div>
                </div>

              </div>

            </div>
          </div>

          <div class="card bg-white" >            
            <div class="card-body">
              <h3 class="card-title mb-3">Associate Details </h3>
              <div class="form-group row ">
                <label class="col-form-label col-lg-3">Associate Code<sup class="required">*</sup></label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="associate_code" id="associate_code" class="removeSpaceInput form-control"  >
                  <span class="error invalid-feedback" id="associate_msg"></span>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Associate Name<sup class="required">*</sup></label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="associate_name" id="associate_name" class="form-control" readonly >
                  <input type="hidden" name="associate_id" id="associate_id" class="form-control" readonly >
                </div>
              </div>
              <div class="form-group row " id="hide_carder">
                <label class="col-form-label col-lg-3">Associate Carder</label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="associate_carder" id="associate_carder" class="form-control" readonly disabled="" > 
                </div>
              </div>

            </div>
          </div>
          {{--
          <div class="card bg-white table-section-onlypancard hideTableData" >            
            <div class="card-body">
              <h3 class="card-title mb-3">Update Form 15G/15H</h3>
              <div class="form-group row ">
                <label class="col-form-label col-lg-4">Year</label>
                  <div class="col-lg-8 error-msg">
				          <!--
                    <select name = "year" id="year" class="form-control">
                      <option value="">Select Year</option>
                        {{ $last= date('Y')-10 }}
                        {{ $now = date('Y') }}

                        @for ($i = $now; $i >= $last; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                  -->
                    <input type="text" name="year" id="year"  class="form-control" readonly value="{{ date('Y') }}"> 
                  </div>
                  
              </div>
              <div class="form-group row ">
                <label class="col-form-label col-lg-4">Upload File </label>
                <div class="col-lg-8 error-msg">
                  <input type="file" name="file" id="file" class="form-control" >
                </div>
              </div>
            </div>
          </div>
          --}}
        </div>
        <div class="col-lg-4">
          <div class="card bg-white" >
            <div class="card-body">
              <h3 class="card-title mb-3"> Upload Photo  </h3>
              <div class="form-group row">
                <div class="col-lg-12 ">                  
                  <span class="text-center rounded-circle w-100">
                    <img alt="Image placeholder" id="photo-preview" src="{{url('/')}}/asset/images/user.png">
                  </span>
                </div>
                <div class="custom-file error-msg">
                  <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*">
                  <label class="custom-file-label" for="photo">Select photo</label>
                  <span class="form-text text-muted">Accepted formats:png, jpg.</span>
                </div>
              </div>
            </div>
          </div>
          <div class="card bg-white" >            
            <div class="card-body">
              <h3 class="card-title mb-3"> Upload Signature  </h3>
              <div class="form-group row"> 
                <div class="col-lg-12 ">
                  <span class="text-center">
                    <img alt="Signature placeholder" id="signature-preview" src="{{url('/')}}/asset/images/signature-logo-design.png" style="width:100%">
                  </span>
                </div>
                <div class="custom-file  error-msg">
                  <input type="file" class="custom-file-input" id="signature" name="signature" accept="image/*">
                  <label class="custom-file-label" for="signature">Select document</label>
                  <span class="form-text text-muted">Accepted formats:png, jpg.</span>
                </div>
              </div>
            </div>
          </div>
          <div class="card" >            
            <div class="card-body">
              <h3 class="card-title mb-3">Residence Address</h3>
              
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">Address<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <textarea name="address" id="address" class="form-control"></textarea>
                </div>
              </div>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">State<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <select class="form-control select" name="state_id" id="state_id"
                  >  
                    <option value="">Select State</option>
                    @foreach( $state as $val )
                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">District<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <select class="form-control select" name="district_id" id="district_id">
                    <option value="">Select District</option>
                  </select>
                </div>
              </div>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">City<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <select class="form-control select" name="city_id" id="city_id">
                    <option value="">Select City</option>
                  </select>
                </div>
              </div>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">Village Name</label>
                <div class="col-lg-12 error-msg">
                  <input type="text" name="village_name" id="village_name" class="form-control"  >
                </div>
              </div>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">Pin Code<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <input type="text" name="pincode" id="pincode" class="form-control"  >
                </div>
              </div>

            </div>
          </div>

          <div class="card bg-white" >            
            <div class="card-body">
              <h3 class="card-title mb-3">ID Proof </h3>
              
              <h5 class="card-title mb-3">ID Proof </h5>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">ID Proof Document Type <sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <select class="form-control select" name="first_id_type" id="first_id_type"
                  >  
                    <option value="">Select Id Type</option>
                    @foreach( $idTypes as $val )
                    @if( $val->id != 7)
                    <option value="{{ $val->id }}"  >{{ $val->name }}</option>
                    @endif
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group  ">
                <label class="col-form-label col-lg-12">ID No<sup class="required">*</sup></label>
                <div class="col-lg-12  error-msg">
                  <div class="row">                  
                  <div class="col-lg-10 ">
                    <input type="text" name="first_id_proof_no" id="first_id_proof_no" class="form-control">
                    
                  </div>
                  <div class="col-lg-2 ">
                    <button type="button" id="first_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
                    </button>
                  </div>
                  </div>
                </div>
              </div>

              <div class="form-group  ">
                <div class="col-lg-12 ">
                  <div class="row">
                <label class="col-form-label col-lg-8">Address same as above</label>
                <div class="col-lg-4  error-msg">
                  <div class="custom-control custom-checkbox mb-3 col-form-label">
                        <input type="checkbox" id="first_same_as" name="first_same_as" class="custom-control-input">
                        <label class="custom-control-label" for="first_same_as">Yes</label>
                      </div>
                </div></div>
              </div>
              </div> 
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">Address<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <textarea name="first_address_proof" id="first_address_proof" class="form-control"></textarea>
                </div>
              </div>

              <h5 class="card-title mb-3">Address Proof</h5>
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">ID Proof Document Type <sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <select class="form-control select" name="second_id_type" id="second_id_type"
                  >  
                    <option value="">Select Id Type</option>
                    @foreach( $idTypes as $val )
                    @if($val->id != 5)
                    <option value="{{ $val->id }}"  >{{ $val->name }}</option>@endif @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group  ">
                <label class="col-form-label col-lg-12">ID No<sup class="required">*</sup></label>

                <div class="col-lg-12  error-msg">
                  <div class="row">
                  <div class="col-lg-10 ">
                    <input type="text" name="second_id_proof_no" id="second_id_proof_no" class="form-control"  >
                  </div>
                  <div class="col-lg-2 ">
                    <button type="button" id="second_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
                    </button>
                  </div>
                  </div>
                </div>
              </div>

               <div class="form-group  ">
                <div class="col-lg-12 ">
                  <div class="row">
                <label class="col-form-label col-lg-8">Address same as above</label>
                <div class="col-lg-4  error-msg">
                  <div class="custom-control custom-checkbox mb-3 col-form-label">
                        <input type="checkbox" id="second_same_as" name="second_same_as" class="custom-control-input">
                        <label class="custom-control-label" for="second_same_as">Yes</label>
                      </div>
                </div></div>
              </div>
              </div>  
              <div class="form-group  ">
                <label class="col-form-label col-lg-12">Address<sup class="required">*</sup></label>
                <div class="col-lg-12 error-msg">
                  <textarea name="second_address_proof" id="second_address_proof" class="form-control"></textarea>
                </div>
              </div>             

            </div>
          </div>

          
          

        </div>
        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
              <button type="submit" class="btn btn-primary" id="member_register_btn">Submit<i class="icon-paperplane ml-2"></i></button>
             {{-- <a href="{!! route('branch.member_list') !!}" class="btn btn-secondary">Back</a>--}}
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

@include('templates.branch.member_management.partials.script')
@stop