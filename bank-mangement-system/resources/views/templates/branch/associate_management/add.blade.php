@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">


    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body page-title"> 
                        <h3 class="">Associate Registration</h3>
            <a href="{!! route('branch.associate_list') !!}" style="float:right" class="btn btn-secondary">Back</a>
          </div>
        </div>
      </div>
    </div>
    <form action="{!! route('branch.associate_save') !!}" method="post" enctype="multipart/form-data" id="associate_register" name="associate_register">
    @csrf
    <input type="hidden" name="created_at" id="created_at" value="">
      <div class="row">
        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <div class="col-lg-12" id="form1error">
              </div>
              <h3 class="card-title mb-3">Member's Detail</h3>
              
                <div class="  row">
                  <label class="col-form-label col-lg-3"></label>
                  <div class="col-lg-9 error-msg">
                    <h4 class="card-title mb-3 ">Search Member</h4>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-form-label col-lg-3">Member Id<sup class="required">*</sup></label>
                  <div class="col-lg-9 error-msg"> 
                    <input type="text" name="member_id" id="member_id" class="form-control"  >
                    <input type="hidden" name="id" id="id" class="form-control"   >
                  </div>
                </div>
                <div id="show_mwmber_detail">
            
                </div>
            </div>
          </div>
          
        </div>

        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Associate Form Information</h3>
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group row">
                  <label class="col-form-label col-lg-4">Form No<sup class="required">*</sup></label>
                  <div class="col-lg-8 error-msg">
                    <input type="text" name="form_no" id="form_no" class="form-control"  >
                  </div>
                </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group row">
                    <label class="col-form-label col-lg-4">Application Date<sup class="required">*</sup></label>
                    <div class="col-lg-8 error-msg">
                      <div class="input-group">
                        <span class="input-group-prepend">
                          <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                        </span>
                        @php
                          $stateid = getBranchState(Auth::user()->username);
                        @endphp
                         <input type="text" class="form-control  " name="application_date" id="application_date"  readonly value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
                       </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group  row">
                    <label class="col-form-label col-lg-4">Senior Code</label>
                    <div class="col-lg-8 error-msg">
                      <input type="text" name="senior_code" id="senior_code" class="form-control"  >
                      <input type="hidden" name="senior_id" id="senior_id" class="form-control"  >
                      <span class="error invalid-feedback" id="associate_msg"></span>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class=" row  form-group ">
                    <label class=" col-lg-4">Name</label>
                    <div class="col-lg-8 " > 
                      <input type="text" name="senior_name" id="senior_name" class="form-control" readonly >
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="row form-group  ">
                    <label class=" col-lg-4">Mobile No</label>
                    <div class="col-lg-8 " id="">
                      <input type="text" name="senior_mobile_no" id="senior_mobile_no" class="form-control" readonly >
                      <input type="hidden" name="seniorcarder_id" id="seniorcarder_id" class="form-control"  >
                      <span class="error invalid-feedback" ></span>
                    </div>
                  </div> 
                </div>


                <div class="col-lg-6">
                  <div class="form-group row">
                    <label class="col-form-label col-lg-4">Assign Carder<sup class="required">*</sup></label>
                    <div class="col-lg-8 error-msg">
                      <select class="form-control select" name="current_carder" id="current_carder"
                      >  
                        <option value="">Select Carder</option>
                       <!-- @foreach( $carder as $val )
                        <option value="{{ $val->id }}"  >{{ $val->name }}({{ $val->short_name }})</option> @endforeach-->
                      </select>
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
              <h3 class="card-title mb-3">Guarantor Details </h3>
              <h5 class="card-title mb-3">1<sup>st</sup>Guarantor Detail </h5>
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Full Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="first_g_first_name" id="first_g_first_name" class="form-control"  readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="first_g_Mobile_no" id="first_g_Mobile_no" class="form-control"  readonly>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Address<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <textarea id="first_g_address" name="first_g_address" class="form-control" readonly></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              <h5 class="card-title mb-3">2<sup>nd</sup>Guarantor Detail </h5>
              <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Full Name</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="second_g_first_name" id="second_g_first_name" class="form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Mobile No</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="second_g_Mobile_no" id="second_g_Mobile_no" class="form-control"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Address</label>
                      <div class="col-lg-9 error-msg">
                        <textarea id="second_g_address" name="second_g_address" class="form-control" ></textarea> 
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
              <h3 class="card-title mb-3">Details of Associate's  dependents  </h3> 
                <div class="row"  >
       <div   class="col-lg-12" id="add_dependent">
        <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group row">
                          <div class="col-lg-12"> 

                          <button type="button" class="btn btn-primary" id="btnAdd">Add More </button>  
                          </div>
                        </div>
                    </div>

                  <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Full Name</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="dep_first_name" id="dep_first_name" class="form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Age</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="dep_age" id="dep_age" class="form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Relation</label>
                      <div class="col-lg-8 error-msg"> 
                        <select name="dep_relation" id="dep_relation" class="form-control"  >
                          <option value="">Select Relation</option>
                          @foreach ($relations as $val)
                              <option value="{{ $val->id }}">{{ $val->name }}</option>
                           @endforeach
                        </select>

                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Per month income</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="dep_income" id="dep_income" class="form-control"  >
                      </div>
                    </div>
                    
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Gender</label>
                      <div class="col-lg-8 error-msg">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3 ">
                              <input type="radio" id="dep_gender_male" name="dep_gender" class="custom-control-input" value="1">
                              <label class="custom-control-label" for="dep_gender_male">Male</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="dep_gender_female" name="dep_gender" class="custom-control-input" value="0"  checked="checked">
                              <label class="custom-control-label" for="dep_gender_female">Female</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Marital status</label>
                      <div class="col-lg-8 error-msg">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3 ">
                              <input type="radio" id="dep_married" name="dep_marital_status" class="custom-control-input" value="1">
                              <label class="custom-control-label" for="dep_married">Married</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="dep_unmarried" name="dep_marital_status" class="custom-control-input" value="0"  checked="checked">
                              <label class="custom-control-label" for="dep_unmarried">Un Married</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Living with Associate</label>
                      <div class="col-lg-8 error-msg">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3 ">
                              <input type="radio" id="dep_living_yes" name="dep_living" class="custom-control-input" value="1">
                              <label class="custom-control-label" for="dep_living_yes">Yes</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="dep_living_no" name="dep_living" class="custom-control-input" value="0"  checked="checked">
                              <label class="custom-control-label" for="dep_living_no">No</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Dependent Type</label>
                      <div class="col-lg-8 error-msg">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3 ">
                              <input type="radio" id="dep_type_fully" name="dep_type" class="custom-control-input" value="1">
                              <label class="custom-control-label" for="dep_type_fully">Fully</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="dep_type_partially" name="dep_type" class="custom-control-input" value="0" checked="checked">
                              <label class="custom-control-label" for="dep_type_partially">Partially</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                    </div>
                  </div>
                  <div class="col-lg-12">
                  <hr>
                </div>
        </div>
      </div>
                  <div class="col-lg-12">
                    <div class="form-group row text-center"> 
                      <div class="col-lg-12 ">
                        <button type="submit" class="btn btn-primary">Next<i class="icon-paperplane ml-2"></i></button>
                        
                      </div>
                    </div>
                  </div>


                </div>
              </div>
            </div>
          </div>
        </div> 
      </div> 
    </form>
    <form  method="post" enctype="multipart/form-data" id="associate_register_next" name="associate_register_next" style="display:none;">
    @csrf
      <div class="row">
        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <div class="col-lg-12" id="form2error">
              </div>
              <h3 class="card-title mb-3">SSB Account Investment Plan Detail </h3>
                <div class="form-group row">
                      <label class="col-form-label col-lg-3">SSB Account opened<sup class="required">*</sup> </label>
                      <div class="col-lg-5 error-msg">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3 ">
                              <input type="radio" id="ssb_account_yes" name="ssb_account" class="custom-control-input" value="1">
                              <label class="custom-control-label" for="ssb_account_yes">Yes</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="ssb_account_no" name="ssb_account" class="custom-control-input" value="0">
                              <label class="custom-control-label" for="ssb_account_no">No</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                </div>
                <div style="display: none;" id="ssb_account_detail">
                  <h3 class="card-title mb-3">SSB Account Detail</h3>
                  <div class="form-group row"> 
                      <div class="col-lg-4 error-msg">
                        <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control"  readonly="" placeholder="SSB Account Number">
                      </div>
                      <div class="col-lg-4 error-msg">
                        <input type="text" name="ssb_account_name" id="ssb_account_name" class="form-control"  readonly="" placeholder="Account Holder Name">
                      </div>
                      <div class="col-lg-4 error-msg">
                        <div class="rupee-img"></div>
                        <input type="text" name="ssb_account_amount" id="ssb_account_amount" class="form-control"  readonly="" placeholder="Current Balance">
                      </div>
                    </div>
                </div>
<!---- SSB Investment Form Start -------------------------------------->
                <div style="display: none;" id="ssb_account_form">
                  <h3 class="card-title mb-3">SSB Account Form </h3>
                  <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Amount<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <div class="rupee-img"></div>
                            <input type="text" name="ssb_amount" id="ssb_amount" class="form-control rupee-txt" value="500" readonly="readonly">
                            <input type="hidden" name="ssb_accountexists" id="ssb_accountexists" class="form-control rupee-txt" value="0" readonly="readonly">
                          </div>
                          <!-- <div class="col-lg-1 error-msg left-padding0" >
                            <img src="{{url('/')}}/asset/images/rs.png" width="9">
                          </div> -->
                        </div>
                      </div>
                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Form No.<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="ssb_form_no" id="ssb_form_no" class="form-control" value="">
                        </div>
                      </div>
                    </div>
                  </div>
                  <h4 class="card-title mb-3">First Nominee Detail</h4>
                  <div class="row">
                      <div class="col-lg-12">
                        <div class="form-group row"> 
                            <div class="col-lg-9 error-msg">
                              <div class="row">
                                <div class="col-lg-9">
                                  <div class="custom-control custom-checkbox mb-3 ">
                                    <input type="checkbox" id="old_ssb_no_detail" name="old_ssb_no_detail" class="custom-control-input" value="1" >
                                    <label class="custom-control-label" for="old_ssb_no_detail" value="1">Yes</label>
                                  </div>
                                </div> 
                              </div>
                            </div> 
                          </div>
                        </div>
                        <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_first_name" id="ssb_first_first_name" class="form-control"  >
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                          <div class="col-lg-8" class="form-control"  > 

                             <select   name="ssb_first_relation" id="ssb_first_relation" class="form-control" >
                              <option value="">Select Relation</option>
                              @foreach ($relations as $val)
                                  <option value="{{ $val->id }}">{{ $val->name }}</option>
                               @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="input-group">
                              <span class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                              </span>
                               <input type="text" class="form-control " name="ssb_first_dob" id="ssb_first_dob" >
                             </div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_percentage" id="ssb_first_percentage" class="form-control"  >
                          </div>
                        </div>

                      </div>
                      <div class="col-lg-6"> 
                      
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="row">
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3 ">
                                  <input type="radio" id="ssb_first_gender_male" name="ssb_first_gender" class="custom-control-input" value="1">
                                  <label class="custom-control-label" for="ssb_first_gender_male">Male</label>
                                </div>
                              </div>
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3  ">
                                  <input type="radio" id="ssb_first_gender_female" name="ssb_first_gender" class="custom-control-input" value="0">
                                  <label class="custom-control-label" for="ssb_first_gender_female">Female</label>
                                </div>
                              </div>
                            </div>
                          </div> 
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_age" id="ssb_first_age" class="form-control"  readonly="readonly">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_first_mobile_no" id="ssb_first_mobile_no" class="form-control"  >
                          </div>
                        </div>

                    </div>
                    <div class="col-lg-12">
                        <div class="form-group row">
                          <div class="col-lg-12">
                            <input type="hidden" name="ssb_first_first_name_old" id="ssb_first_first_name_old" >
                            <input type="hidden" name="ssb_first_relation_old" id="ssb_first_relation_old" >
                            <input type="hidden" name="ssb_first_dob_old" id="ssb_first_dob_old" >
                            <input type="hidden" name="ssb_first_age_old" id="ssb_first_age_old" >
                            <input type="hidden" name="ssb_first_mobile_no_old" id="ssb_first_mobile_no_old" >
                            <input type="hidden" name="ssb_first_gender_old" id="ssb_first_gender_old" >

                          <button type="button" class="btn btn-primary" id="second_nominee_ssb">Add Nominee </button> 
                          <button type="button" class="btn btn-primary" id="second_nominee_ssb_remove" style="display: none">Remove Nominee</i></button> 
                          </div>
                        </div>
                    </div>
                  </div>
                <DIV id="ssb_second_no_div" style="display: none">
                  <h4 class="card-title mb-3">Second  Nominee Detail</h4>
                  <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_second_first_name" id="ssb_second_first_name" class="form-control"  >
                            <input type="hidden" name="ssb_second_validate" id="ssb_second_validate" class="form-control"  value="0">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                          <div class="col-lg-8"  class="form-control"  >

                            <select   name="ssb_second_relation" id="ssb_second_relation" class="form-control" >
                              <option value="">Select Relation</option>
                              @foreach ($relations as $val)
                                  <option value="{{ $val->id }}">{{ $val->name }}</option>
                               @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="input-group">
                              <span class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                              </span>
                               <input type="text" class="form-control " name="ssb_second_dob" id="ssb_second_dob" >
                             </div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_second_percentage" id="ssb_second_percentage" class="form-control"  >
                          </div>
                        </div>

                      </div>
                      <div class="col-lg-6"> 
                      
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="row">
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3 ">
                                  <input type="radio" id="ssb_second_gender_male" name="ssb_second_gender" class="custom-control-input" value="1">
                                  <label class="custom-control-label" for="ssb_second_gender_male">Male</label>
                                </div>
                              </div>
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3  ">
                                  <input type="radio" id="ssb_second_gender_female" name="ssb_second_gender" class="custom-control-input" value="0">
                                  <label class="custom-control-label" for="ssb_second_gender_female">Female</label>
                                </div>
                              </div>
                            </div>
                          </div> 
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_second_age" id="ssb_second_age" class="form-control"  readonly="readonly">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="ssb_second_mobile_no" id="ssb_second_mobile_no" class="form-control"  >
                          </div>
                        </div>

                    </div>
                </div>
              </div>
              </div>
<!---- SSB Investment Form End -------------------------------------->
              <h3 class="card-title mb-3">RD Account Investment Plan Detail</h3>
                <div class="form-group row">
                      <label class="col-form-label col-lg-3">RD Account Available </label>
                      <div class="col-lg-5 error-msg">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3 ">
                              <input type="radio" id="rd_account_yes" name="rd_account" class="custom-control-input" value="1">
                              <label class="custom-control-label" for="rd_account_yes">Yes</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="rd_account_no" name="rd_account" class="custom-control-input" value="0">
                              <label class="custom-control-label" for="rd_account_no">No</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                </div>
              <div class="form-group row " id="rd-account-list">
              </div>
                <div style="display: none;" id="rd_account_detail">
                  <div class="form-group row">
                    <div class="col-lg-4 error-msg">
                      <input type="text" name="rd_account_number" id="rd_account_number" class="form-control"  readonly="" placeholder="RD Account Number">
                    </div>
                    <div class="col-lg-4 error-msg">
                      <input type="text" name="rd_account_name" id="rd_account_name" class="form-control"  readonly="" placeholder="Account Holder Name">
                    </div>
                    <div class="col-lg-4 error-msg">
                      <div class="rupee-img"></div>
                      <input type="text" name="rd_account_amount" id="rd_account_amount" class="form-control"  readonly="" placeholder="Current Balance"
                             style="padding-left:32Px">
                    </div>
                     {{-- <label class="col-form-label col-lg-3">RD account Number<sup class="required">*</sup></label>
                      <div class="col-lg-5 error-msg">
                        <input type="text" name="rd_account_number" id="rd_account_number" class="form-control"  >
                      </div>--}}
                    </div>
                </div>
<!---- RD Investment Form Start -------------------------------------->
                <div style="display: none;" id="rd_account_form">
                  <h3 class="card-title mb-3">RD Investment Form </h3>
                  <div class="row">
                    <div class="col-lg-3">
                      <div class="form-group row">
                          <label class="col-form-label col-lg-5">Amount<sup class="required">*</sup></label>
                          <div class="col-lg-7 error-msg">
                            <div class="rupee-img"></div>
                            <input type="text" name="rd_amount" id="rd_amount" class="form-control rupee-txt" value="500" readonly="readonly">
                          </div>
                          <!-- <div class="col-lg-1 error-msg left-padding0">
                            <img src="{{url('/')}}/asset/images/rs.png" width="9" > 
                          </div> -->
                      </div>
                    </div>
                    <div class="col-lg-5">
                      <div class="form-group row">
                          <label class="col-form-label col-lg-5">Payment Mode<sup class="required">*</sup></label>
                          <div class="col-lg-7 error-msg">
                             <select name="payment_mode" id="payment_mode" class="form-control"  >
                                <option value="">Select Mode</option>
                                <option data-val="cash" value="0">Cash</option>
                                <option data-val="cheque-mode" value="1">Cheque</option>
                                <option data-val="online-transaction-mode" value="2">Online transaction</option>
                                <option data-val="ssb-account" value="3">SSB account</option>
                              </select>
                          </div>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="form-group row">
                          <label class="col-form-label col-lg-4">Tenure<sup class="required">*</sup></label>
                          <div class="col-lg-7 error-msg">
                            <label class="col-form-label col-lg-10">60  Months</label>
                             <input type="hidden" name="tenure" id="tenure" class="form-control" value="60" >
                          </div>
                      </div>
                    </div>
                   {{-- <div class="col-lg-12">--}}
                    <div class="col-lg-3">
                      <span id="maturity" style="padding: 15px"></span>
                    </div>

                    <input type="hidden" name="rd_amount_maturity" id="rd_amount_maturity" class="form-control rupee-txt" value="0" readonly="readonly">
                    <input type="hidden" name="rd_rate" id="rd_rate" class="form-control rupee-txt" value="0" readonly="readonly">
                    <div class="col-lg-5">
                         <div class="form-group row">
                            <label class="col-form-label col-lg-5">Form No<sup class="required">*</sup></label>
                            <div class="col-lg-7 error-msg">
                              <input type="text" name="rd_form_no" id="rd_form_no" class="form-control">
                            </div>
                          </div>
                        </div>
                   {{-- </div>--}}
                  </div>
      <!------- RD account Payment mode detail  start ------------->
        <!------- Payment Mode - Cheque Start  ------------->
                  <div id="payment_mode_cheque" style="display: none">
                    <h4 class="card-title mb-3">Cheque Detail</h4>
                    <div class=" row">
                      <div class="col-lg-6">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Number<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                              <select name="cheque_id" id="cheque_id" class="form-control" title="Please select something!">
                                <option value="">Select Cheque</option> 
                              </select>

                            </div>
                        </div>
                      </div>
                    </div>
                    <div class=" row" style="display: none;" id="cheque_detail">
                      <div class="col-lg-6" >
                       <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Number<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="rd_cheque_no" id="rd_cheque_no" class="form-control" readonly >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Branch Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="rd_branch_name" id="rd_branch_name" class="form-control" readonly >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Amount<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                              <div class="rupee-img"></div>
                               <input type="text" name="cheque-amt" id="cheque-amt" class="form-control rupee-txt"  readonly>
                            </div>
                        </div>
						 <div class="form-group row">
                            <label class="col-form-label col-lg-4">Deposit Bank Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="deposit_bank_name" id="deposit_bank_name" class="form-control"  readonly>
                            </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Bank Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="rd_bank_name" id="rd_bank_name" class="form-control" readonly >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <div class="input-group">
                                <span class="input-group-prepend">
                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                 <input type="text" class="form-control " name="rd_cheque_date" id="rd_cheque_date" readonly>
                               </div>
                            </div>
                        </div>
						<div class="form-group row">
                            <label class="col-form-label col-lg-4">Deposit Bank Account <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="deposit_bank_account" id="deposit_bank_account" class="form-control"  readonly>
                            </div>
                        </div>
                      </div> 
                  </div>
                </div>
        <!------- Payment Mode - Cheque End  ------------->
        <!------- Payment Mode - online transaction Start  ------------->
                  <div id="payment_mode_online" style="display: none">
                    <h4 class="card-title mb-3">Online transaction</h4>
                    <div class=" row">
                      <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Transaction Id<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="rd_online_id" id="rd_online_id" class="form-control" >
                            </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                              <div class="input-group">
                                <span class="input-group-prepend">
                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                 <input type="text" class="form-control " name="rd_online_date" id="rd_online_date" >
                               </div>
                            </div>
                        </div>
                      </div>
					  <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Deposit Bank <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                                <select name="rd_online_bank_id" id="rd_online_bank_id" class="form-control" >
                                    <option value=''>---Please Select---</option>
                                    @foreach($samraddhBanks as $bank)
                                        @if($bank['bankAccount'])
                                           <option value="{{ $bank->id }}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Deposit Bank Account <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg"> 
                                <select name="rd_online_bank_ac_id" id="rd_online_bank_ac_id" class="form-control" >
                                    <option value=''>---Please Select---</option>
                                    @foreach($samraddhBanks as $bank)
                                        @if($bank['bankAccount'])
                                            <option style="display: none;" class="{{ $bank->id }}-bank-account bank-account" value="{{ $bank['bankAccount']->id }}" style="display: none;">
                                             {{ $bank['bankAccount']->account_no }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
          <!------- Payment Mode -  online transaction End  ------------->
          <!------- Payment Mode - SSB Account  ------------->
                  <div id="payment_mode_ssb" style="display: none">
                    <h4 class="card-title mb-3">Account Detail</h4>
                    <div class=" row">
                      <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Number</label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="rd_ssb_account_number" id="rd_ssb_account_number" class="form-control" disabled>
                            </div>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Balance</label>
                            <div class="col-lg-8 error-msg"> 
                               <input type="text" name="rd_ssb_account_amount" id="rd_ssb_account_amount" class="form-control" disabled>
                            </div>
                        </div>
                      </div>
                    </div>
                  </div>
          <!------- Payment Mode -  SSB Account  End  ------------->

      <!------- RD account Payment mode detail  End ------------->
                  <h4 class="card-title mb-3">First Nominee Detail</h4>
                  <div class="row">
                      <div class="col-lg-12">
                        <div class="form-group row"> 
                            <div class="col-lg-9 error-msg">
                              <div class="row">
                                <div class="col-lg-9">
                                  <div class="custom-control custom-checkbox mb-3 ">
                                    <input type="checkbox" id="old_rd_no_detail" name="old_rd_no_detail" class="custom-control-input" value="1" >
                                    <label class="custom-control-label" for="old_rd_no_detail" value="1">Yes</label>
                                  </div>
                                </div> 
                              </div>
                            </div> 
                          </div>
                      </div>
                        <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_first_name" id="rd_first_first_name" class="form-control"  >
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                          <div class="col-lg-8" class="form-control"  > 
                             <select    name="rd_first_relation" id="rd_first_relation" class="form-control">
                              <option value="">Select Relation</option>
                              @foreach ($relations as $val)
                                  <option value="{{ $val->id }}">{{ $val->name }}</option>
                               @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="input-group">
                              <span class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                              </span>
                               <input type="text" class="form-control " name="rd_first_dob" id="rd_first_dob" >
                             </div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_percentage" id="rd_first_percentage" class="form-control"  >
                          </div>
                        </div>

                      </div>
                      <div class="col-lg-6"> 
                      
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="row">
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3 ">
                                  <input type="radio" id="rd_first_gender_male" name="rd_first_gender" class="custom-control-input" value="1">
                                  <label class="custom-control-label" for="rd_first_gender_male">Male</label>
                                </div>
                              </div>
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3  ">
                                  <input type="radio" id="rd_first_gender_female" name="rd_first_gender" class="custom-control-input" value="0">
                                  <label class="custom-control-label" for="rd_first_gender_female">Female</label>
                                </div>
                              </div>
                            </div>
                          </div> 
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_age" id="rd_first_age" class="form-control"  readonly="readonly">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_mobile_no" id="rd_first_mobile_no" class="form-control"  >
                          </div>
                        </div>

                    </div>
                    <div class="col-lg-12">
                        <div class="form-group row">
                          <div class="col-lg-12">
                          <button type="button" class="btn btn-primary" id="second_nominee_rd">Add Nominee </button> 
                          <button type="button" class="btn btn-primary" id="second_nominee_rd_remove" style="display: none">Remove Nominee<!-- <i class="fas fa-trash"></i> --></button> 
                        </div>
                        </div>
                    </div>
                  </div>
                <DIV id="rd_second_no_div" style="display: none">
                  <h4 class="card-title mb-3">Second  Nominee Detail</h4>
                  <div class="row">
                      <div class="col-lg-6">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_second_first_name" id="rd_second_first_name" class="form-control"  >
                            <input type="hidden" name="rd_second_validate" id="rd_second_validate" class="form-control"  value="0">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                          <div class="col-lg-8"  class="form-control"  > 


                            <select   name="rd_second_relation" id="rd_second_relation" class="form-control">
                              <option value="">Select Relation</option>
                              @foreach ($relations as $val)
                                  <option value="{{ $val->id }}">{{ $val->name }}</option>
                               @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="input-group">
                              <span class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                              </span>
                               <input type="text" class="form-control " name="rd_second_dob" id="rd_second_dob" >
                             </div>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_second_percentage" id="rd_second_percentage" class="form-control"  >
                          </div>
                        </div>

                      </div>
                      <div class="col-lg-6"> 
                      
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup> </label>
                          <div class="col-lg-8 error-msg">
                            <div class="row">
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3 ">
                                  <input type="radio" id="rd_second_gender_male" name="rd_second_gender" class="custom-control-input" value="1">
                                  <label class="custom-control-label" for="rd_second_gender_male">Male</label>
                                </div>
                              </div>
                              <div class="col-lg-4">
                                <div class="custom-control custom-radio mb-3  ">
                                  <input type="radio" id="rd_second_gender_female" name="rd_second_gender" class="custom-control-input" value="0">
                                  <label class="custom-control-label" for="rd_second_gender_female">Female</label>
                                </div>
                              </div>
                            </div>
                          </div> 
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_second_age" id="rd_second_age" class="form-control"  readonly="readonly">
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                          <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_second_mobile_no" id="rd_second_mobile_no" class="form-control"  >
                          </div>
                        </div>

                    </div>
                </div>
              </DIV>
              </div>
<!---- RD Investment Form End -------------------------------------->
                <div class="form-group row text-center">
                  <div class="col-lg-12">
                    <button type="button" class="btn btn-default" id="previous_form">Previous<i class="icon-paperplane ml-2"></i></button> 
                   <button type="submit" class="btn btn-primary" id="associate_register_btn">Submit<i class="icon-paperplane ml-2"></i></button>
                   
                 </div>
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

@include('templates.branch.associate_management.partials.script')
@stop
