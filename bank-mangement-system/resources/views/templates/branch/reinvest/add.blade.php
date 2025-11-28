@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
  <div class="container-fluid mt--6">
    <div class="content-wrapper">
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
          <div class="card col-lg-12" >
            <div class="card-body">
              <div class="form-group row">
                <label class="col-form-label col-lg-6"><h3>Reinvest Account Number<sup class="required">*</sup></h3></label>
                <div class="col-lg-6 error-msg">
                  <input type="text" name="investAccountNumber" id="investAccountNumber" class="form-control" placeholder="Invest Account Number" required=""
                         autocomplete="off"><span id="accountErrorName"></span>
                </div>
              </div>
            </div>
          </div>
          <form id="member_register">
            @csrf
            <input type="hidden" name="oldMemberId" id="old_member_id" value="">
            <input type="hidden" name="oldAccountNumber" id="old_account_number" value="">
            <input type="hidden" name="plan_id" id="plan_id" value="">
            <input type="hidden" name="from_type" value="reinvest-member">
            <input type="hidden" id="oldCId" name="oldCId" value="">
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
                            <input type="text" name="form_no" id="form_no" class="form-control" value="{{ old('form_no') }}"  >
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6"></div>
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-12">Account Opening Date<sup class="required">*</sup></label>
                          <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                    <span class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    </span>
                              <input type="text" class="form-control renewal_date " name="account_application_date" id="account_application_date"  value="{{ date('d/m/Y') }}">
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-12">Application Date<sup class="required">*</sup></label>
                          <div class="col-lg-12 error-msg">
                            <div class="input-group">
                      <span class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                      </span>
                              <input type="text" class="form-control  " name="application_date" id="application_date"  readonly value="{{ date('d/m/Y') }}">
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
                        <input type="text" name="email" id="email" class="form-control"  value="{{ old('email') }}" >
                        <span class="error invalid-feedback" id="email_msg"></span>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="mobile_no" id="mobile_no" class="form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Date of Birth/Age<sup class="required">*</sup> </label>
                      <div class="col-lg-6 error-msg">
                        <div class="input-group">
                      <span class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                      </span>
                          <input type="text" class="form-control " name="dob" id="dob" >
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
                          @foreach( App\Models\Occupation::pluck('name', 'id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Annual Income <sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="annual_income" id="annual_income" class="form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Mother Name</label>
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
                      <label class="col-form-label col-lg-3">Marital status<sup class="required">*</sup></label>
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
                      <label class="col-form-label col-lg-3">Anniversary Date<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <div class="input-group">
                      <span class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
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
                          @foreach( App\Models\Religion::pluck('name','id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-group row ">
                      <label class="col-form-label col-lg-3">Categories</label>
                      <div class="col-lg-9 error-msg">
                        <select class="form-control select" name="special_category" id="special_category" >
                          <option value="0">General Category</option>
                          @foreach( App\Models\SpecialCategory::where([['status', '=', '1'],['is_deleted', '=', '0'],])->pluck('name', 'id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option> @endforeach
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
                        <input type="hidden" name="bank_id" id="bank_id" class=" form-control"  value="0"  >
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
                        <input type="text" name="bank_account_no" id="bank_account_no" class=" form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">IFSC Code</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="bank_ifsc" id="bank_ifsc" class=" form-control"  >
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
                        <input type="hidden" name="nominee_id" id="nominee_id" class=" form-control"  value="0"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Relationship </label>
                      <div class="col-lg-9 error-msg">

                        <select name="nominee_relation" id="nominee_relation" class=" form-control">
                          <option value="">Select Relation</option>
                          @foreach ( App\Models\Relations::pluck('name', 'id') as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
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
                        <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                      </span>
                          <input type="text" class="form-control  " name="nominee_dob" id="nominee_dob" >
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
                        <input type="text" name="nominee_mobile_no" id="nominee_mobile_no" class=" form-control"  >
                      </div>
                    </div>
                    <div class="form-group row" id="minor_hide">
                      <label class="col-form-label col-lg-3">If Minor </label>
                      <div class="col-lg-9 error-msg">
                        <div class="row">
                          <div class="col-lg-9">
                            <div class="custom-control custom-checkbox mb-3 ">
                              <input type="checkbox" id="is_minor" name="is_minor" class="custom-control-input" value="1" >
                              <label class="custom-control-label" for="is_minor" value="1">Yes</label>
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
                        <input type="text" name="associate_code" id="associate_code" class="form-control"  >
                        <span class="error invalid-feedback" id="associate_msg"></span>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Associate Name</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="associate_name" id="associate_name" class="form-control" readonly disabled="">
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
                          @foreach( App\Models\States::pluck('name','id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option>
                          @endforeach
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
                        <input type="text" name="pincode" id="pincode" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">ID Proof </h3>

                    <h5 class="card-title mb-3">First ID Proof </h5>
                    <div class="form-group  ">
                      <label class="col-form-label col-lg-12">ID Proof Document Type <sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select class="form-control select" name="first_id_type" id="first_id_type">
                          <option value="">Select Id Type</option>
                          @foreach( App\Models\IdType::where('status',1)->pluck('name','id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option>
                          @endforeach
                        </select>
                        <input type="hidden" name="IdProof_id" id="IdProof_id" class=" form-control"  value="0"  >
                      </div>
                    </div>

                    <div class="form-group  ">
                      <label class="col-form-label col-lg-12">ID No<sup class="required">*</sup></label>
                      <div class="col-lg-12  error-msg">
                        <div class="row">
                          <div class="col-lg-10 ">
                            <input type="text" name="first_id_proof_no" id="first_id_proof_no" class="form-control"  >
                          </div>
                          <div class="col-lg-2 ">
                            <button type="button" id="first_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="" data-original-title="Enter id proof number"><i class="fas
                      fa-exclamation-circle text-secondary"></i>
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

                    <h5 class="card-title mb-3">Second ID Proof </h5>
                    <div class="form-group  ">
                      <label class="col-form-label col-lg-12">ID Proof Document Type <sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select class="form-control select" name="second_id_type" id="second_id_type"
                        >
                          <option value="">Select Id Type</option>
                          @foreach( App\Models\IdType::where('status',1)->pluck('name','id') as $key => $val )
                            <option value="{{ $key }}"  >{{ $val }}</option>
                          @endforeach
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
                            <button type="button" id="second_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title=""><i class="fas fa-exclamation-circle text-secondary"></i>
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
                      <input type="button" id="reinvest12" class="btn btn-primary" value="Next"/>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </form>
          <form id="reinvest_plane" style="display:none; width:100%;">
            @csrf
            <input type="hidden" name="memberid" id="member_id_for_reinvest" value="">
            <input type="hidden" name="memberAutoId" id="reinvest_member_id" value="">
            <input type="hidden" name="associatemid" id="associatemid" value="">
            <input type="hidden" name="member_name" id="member_name" value="">
            <input type="hidden" name="from_type" value="reinvest_plane">
            <input type="hidden" name="occountNumber" id="old_account_number" value="">
            <input type="hidden" name="oldAccountNumber" id="old_account_number_plan" value="">
            <div class="row">
              <div class="col-lg-12">
                <div class="card bg-white" >
                  <div class="card-body">
                    <h3 class="card-title mb-3">Member Information </h3>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Member Id</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="member_id_for_reinvest" id="member_id_for_reinvest_new" class=" form-control" value="" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Reinvest Account Number </label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="account_number_for_reinvest" id="account_number_for_reinvest" class="form-control" value="" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Opening Date</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="open-date" id="open-date" class="form-control"  value="">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Plan Name</label>
                      <div class="col-lg-9 error-msg">
                        <input type="hidden" name="investmentplan" id="plan-id" class="form-control">
                        <input type="hidden" name="plan_type" id="plan-type" class="form-control">
                        <input type="text" name="plan-name" id="plan-name" class="form-control" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Associate Id</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="associateid" id="associateid" class="form-control valid" placeholder="Agent Code" required="" autocomplete="off" aria-invalid="false">
                      </div>
                    </div>
                    <div class="alert alert-danger alert-block associate-not-found" style="display: none;">  <strong>Agent not found</strong> </div>
                    <h3 class="associate-member-detail" style="display: none;">Agent Details</h3>
                    <div class="form-group row associate-member-detail" style="display: none;">
                      <div class="col-lg-4">
                        <div class="input-group">
                          <input type="text" name="associate_name" id="associate_name_reinvest" placeholder="Name" class="form-control" disabled="">
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="input-group">
                          <input type="text" name="associate_mobile" id="associate_mobile" placeholder="Mobile Number" class="form-control" disabled="">
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="input-group">
                          <input type="text" name="associate_carder" id="associate_carder_reinvest" placeholder="Associate Carder" class="form-control" disabled="">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12">
                <div class="card bg-white" >
                  <div class="card-body">
                    <div class="form-group row select-plan" style="">
                      <label class="col-form-label col-lg-2">Investment Plan<sup>*</sup></label>
                      <div class="col-lg-4">
                        <select name="investmentplan" id="investmentplan" class="form-control valid" title="Please select something!" aria-invalid="false">
                          <option value="">Select Plan</option>
                          <option data-val="saving-account" value="1">Saving Account</option>
                          <option data-val="samraddh-kanyadhan-yojana" value="2">Samraddh Kanyadhan Yojana</option>
                          <option data-val="special-samraddh-money-back" value="3">Special Samraddh Money Back</option>
                          <option data-val="flexi-fixed-deposit" value="4">Flexi Fixed Deposit</option>
                          <option data-val="fixed-recurring-deposit" value="5">Flexi Recurring Deposit</option>
                          <option data-val="samraddh-jeevan" value="6">Samraddh JEEVAN</option>
                          <option data-val="daily-deposit" value="7">Daily Deposit</option>
                          <option data-val="monthly-income-scheme" value="8">Monthly Income scheme</option>
                          <option data-val="fixed-deposit" value="9">Fixed Deposit</option>
                          <option data-val="recurring-deposit" value="10">Recurring Deposit</option>
                          <option data-val="samraddh-bhavhishya" value="11">Samraddh Bhavhishya</option>
                        </select>
                        <input type="hidden" name="investmentplan" id="investmentplanHidden" value="">
                      </div>
                      <label class="col-form-label col-lg-2">Form Number<sup>*</sup></label>
                      <div class="col-lg-4">
                        <input type="text" name="form_number" id="form_number" class="form-control">
                      </div>
                    </div>
                    <div id="plan-content-div">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card bg-white">
              <div class="card-body">
                <div class="text-center">
                  <input type="button" id="create-investment-plan" class="btn btn-primary" value="Next"/>
                </div>
              </div>
            </div>
          </form>
          <form id="reinvest_transaction" style="display:none; width:100%;">
            @csrf
            <div class="card bg-white">
              <input type="hidden" name="member_auto_id" id="transaction_member_auto_id" value="">
              <input type="hidden" name="plan_id_transaction" id="plan_id_transaction" value="">
              <input type="hidden" name="from_type" value="reinvest_transaction">
              <input type="hidden" name="oldAccountNumber" id="old_account_number_transaction" value="">
              <div class="card-body">
                <h3 class="card-title mb-3">Amount Details </h3>
                <div class="form-group row">
                  <div class="col-md-6">
                    <label class="col-form-label col-lg-12">Eli closing balance  Amount<sup class="required">*</sup></label>
                    <div class="col-lg-9 error-msg">
                      <input type="text" name="closing_Balance_reinvest" id="closing_Balance_reinvest" class="form-control" value="">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="col-form-label col-lg-12">Opening balance</label>
                    <div class="col-lg-9 error-msg">
                      <input type="text" name="opening_Balance_reinvest" id="opening_Balance_reinvest" class="form-control" value="" readonly>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-title mb-3" id="renewal_transaction">
              <div class="card bg-white" >
                <div class="card-body">
                  <div class="rdfrd-renew-investment-table renew-account-table" id="renew" style="">
                    <table class="table table-flush" id="add-transaction" >
                      <thead class="">
                      <tr>
                        <th>S.No.</th>
                        <th>Date</th>
                        <th>Deposit Amount</th>
                      </tr>
                      </thead>
                      <tbody class="input-number">
                      <tr><td>1.</td>
                        <td><input type="text" name="renewal_date[1]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" id="opening-balance" data-val="1" name="amount[1]" class="form-control
                         rupee-txt deposit-amount amount-1" readonly></div></td>
                      </tr>
                      <tr><td>2.</td>
                        <td><input type="text" name="renewal_date[2]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="2" name="amount[2]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>3.</td>
                        <td><input type="text" name="renewal_date[3]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="3" name="amount[3]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>4.</td>
                        <td><input type="text" name="renewal_date[4]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="4" name="amount[4]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>5.</td>
                        <td><input type="text" name="renewal_date[5]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="5" name="amount[5]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>6.</td>
                        <td><input type="text" name="renewal_date[6]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="6" name="amount[6]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>7.</td>
                        <td><input type="text" name="renewal_date[7]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="7" name="amount[7]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>8.</td>
                        <td><input type="text" name="renewal_date[8]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="8" name="amount[8]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>9.</td>
                        <td><input type="text" name="renewal_date[9]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="9" name="amount[9]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      <tr><td>10.</td>
                        <td><input type="text" name="renewal_date[10]" id="renewal_date" class="form-control renewal_date" value=""></td>
                        <td><div class="col-lg-12"><div class="rupee-img"></div><input type="text" data-val="10" name="amount[10]" class="form-control rupee-txt
                        rdfrd-renew-amount deposit-amount rdfrd-renew-amount-3"></div></td>
                      </tr>
                      </tbody>
                    </table>
                    <button type="button" class=" btn btn-primary legitRipple" onclick="addNewRow()">Add Row</button>
                    <div id="delete-row" style="float:right"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card bg-white" >
              <div class="card-body">
                <h3 class="card-title mb-3">Total Amount Details </h3>
                <div class="form-group row">
                  <div class="col-md-4">
                    <label class="col-form-label col-lg-12">Total Amount</label>
                    <div class="col-lg-12 error-msg">
                      <div class="rupee-img"></div><input type="text" id="total_reinvest_amount" name="total_reinvest_amount" class="form-control rupee-txt amount amount" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="col-form-label col-lg-12">Collection Amount</label>
                    <div class="col-lg-12 error-msg">
                      <div class="rupee-img"></div><input type="text" id="collection_reinvest_amount" name="collection_reinvest_amount" class="form-control rupee-txt amount amount">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="col-form-label col-lg-12">Payment Mode<sup class="required">*</sup></label>
                    <div class="col-lg-12 error-msg">
                      <select name="payment_mode" id="payment_mode" class="form-control" title="Please select something!">
                       <!-- <option value="">Select Mode</option>-->
                        <option data-val="cash" value="0">Cash</option>
                      <!--  <option data-val="cheque-mode" value="1">Cheque</option>
                        <option data-val="ssb-account" value="2">DD</option>
                        <option data-val="online-transaction-mode" value="3">Online transaction</option>
                        <option data-val="ssb" value="4">SSB</option>-->
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-title mb-3">
              <div class="card bg-white" >
                <div class="card-body">
                  <div class="text-center">
                    <input type="button" name="submitform" value="Submit" id="create-reinvestment-transaction" class="btn btn-primary">
                  </div>
                </div>
              </div>
            </div>
          </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="saving-account-modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-body p-0">
          <div class="card bg-white border-0 mb-0">
            <div class="card-header bg-transparent pb-2ß">
              <div class="text-dark text-center mt-2 mb-3">Open Saving Account</div>
            </div>
            <div class="card-body px-lg-5 py-lg-5">
              <form action="{{route('investment.opensavingaccount')}}" method="post" id="saving-account-form" name="register-plan">
                @csrf
                <input type="hidden" name="saving_account_m_id" id="saving_account_m_id">
                <input type="hidden" name="saving_account_m_name" id="saving_account_m_name">
                <input type="hidden" name="nominee_form_class" id="nominee_form_class">
                <input type="hidden" name="account_box_class" id="account_box_class">
                <input type="hidden" name="current_plan_id" id="current_plan_id">
                <div class="form-group row">
                  <label class="col-form-label col-lg-2">Amount</label>
                  <div class="col-lg-4">
                    <div class="rupee-img">
                    </div>
                    <input type="text" name="ssbamount" id="ssbamount" class="form-control rupee-txt" value="100" readonly="">
                  </div>

                  <label class="col-form-label col-lg-2">Form Number</label>
                  <div class="col-lg-4">
                    <input type="text" name="f_number" id="f_number" class="form-control">
                  </div>
                </div>  

                <h3 class="">First Nominee</h3>  
                <div class="custom-control custom-checkbox mb-3 col-form-label">
                  <input type="checkbox" id="same_as_registered_ssb_nominee" name="same_as_registered_ssb_nominee" class="custom-control-input">
                  <label class="custom-control-label" for="same_as_registered_ssb_nominee">Yes</label>
                </div>

                <div class="form-group row">
                  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_fn_first_name" id="ssb_fn_first_name" class="form-control">
                  </div>
                  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="ssb_fn_relationship" id="ssb_fn_relationship" class="form-control" title="Please select something!">
                      <option value="0">Select Relation</option>
                      @foreach( App\Models\Relations::pluck('name', 'id') as $key => $val )
                        <option value="{{ $key }}"  >{{ $val }}</option> 
                      @endforeach
                    </select>
                  </div>
                </div>                

                <div class="form-group row">
                  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
                  <div class="col-lg-4">
                    <div class="input-group nominee-dob">
                      <span class="input-group-prepend">
                        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                      </span>
                       <input type="text" name="ssb_fn_dob" id="ssb_fn_dob" class="fn_dateofbirth form-control" data-val="ssb_fn_age">
                    </div>
                  </div>
                  <label class="col-form-label col-lg-2">Age</label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_fn_age" id="ssb_fn_age" class="form-control">
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
                  <div class="col-lg-4 error-msg">
                    <div class="row">
                      <div class="col-lg-4">
                        <div class="custom-control custom-radio mb-3 CustomAddGender">
                          <input type="radio" id="ssb_fn_gender_male" name="ssb_fn_gender" class="custom-control-input" value="1">
                          <label class="custom-control-label" for="ssb_fn_gender_male">Male</label>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="custom-control custom-radio mb-3 CustomAddGender">
                          <input type="radio" id="ssb_fn_gender_female" name="ssb_fn_gender" class="custom-control-input" value="0">
                          <label class="custom-control-label" for="ssb_fn_gender_female">Female</label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_fn_percentage" id="ssb_fn_percentage" data-id="ssb_sn_percentage" class="form-control sa-nominee-percentage">
                    <label id="ssb-percentage-error" class="error"></label>
                  </div>
                </div>

                <h3 class="">Second Nominee</h3> 
                <input type="button" name="second_nominee" data-val="sa_second_nominee_add" data-class="sa-second-nominee" id="second_nominee" value="Add Nominee" class="btn btn-primary valid second-nominee-input add-second-nominee sa-second-nominee-botton" aria-invalid="false">
                <input type="hidden" name="sa_second_nominee_add" id="sa_second_nominee_add" value="0">

                <div class="form-group row sa-second-nominee" style="display: none;">
                  <label class="col-form-label col-lg-2">Full Name<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_sn_first_name" id="ssb_sn_first_name" class="form-control">
                  </div>
                  <label class="col-form-label col-lg-2">Relationship<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="ssb_sn_relationship" id="ssb_sn_relationship" class="form-control" title="Please select something!">
                      <option value="">Select Relation</option>
                      @foreach( App\Models\Relations::pluck('name', 'id') as $key => $val )
                        <option value="{{ $key }}"  >{{ $val }}</option> 
                      @endforeach
                    </select>
                  </div>
                  <!-- <label class="col-form-label col-lg-2">Last Name<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_sn_second_name" id="ssb_sn_second_name" class="form-control">
                  </div> -->
                </div> 

                <div class="form-group row sa-second-nominee" style="display: none;">
                  <label class="col-form-label col-lg-2">Nominee (D.O.B.)<sup>*</sup></label>
                  <div class="col-lg-4">
                    <div class="input-group nominee-dob">
                    <span class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                    </span>
                     <input type="text" name="ssb_sn_dob" id="ssb_sn_dob" class="sn_dateofbirth form-control" data-val="ssb_sn_age">
                   </div>
                  </div>
                  <label class="col-form-label col-lg-2">Age</label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_sn_age" id="ssb_sn_age" class="form-control" readonly="">
                  </div>
                </div>

                <div class="form-group row sa-second-nominee" style="display: none;">
                  <label class="col-form-label col-lg-2">Gender<sup>*</sup></label>
                  <div class="col-lg-4 error-msg">
                    <div class="row">
                      <div class="col-lg-4">
                        <div class="custom-control custom-radio mb-3 CustomAddGender">
                          <input type="radio" id="ssb_sn_gender_male" name="ssb_sn_gender" class="custom-control-input" value="1">
                          <label class="custom-control-label" for="ssb_sn_gender_male">Male</label>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="custom-control custom-radio mb-3 CustomAddGender">
                          <input type="radio" id="ssb_sn_gender_female" name="ssb_sn_gender" class="custom-control-input" value="0">
                          <label class="custom-control-label" for="ssb_sn_gender_female">Female</label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <label class="col-form-label col-lg-2">Percentage<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="ssb_sn_percentage" id="ssb_sn_percentage" data-id="ssb_fn_percentage" class="form-control sa-nominee-percentage">
                  </div>
                </div>

                <div class="form-group row sa-second-nominee" style="display: none;">
                  
                </div>
                <div class="text-right">
                  <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@include('templates.branch.reinvest.partials.script')
@stop