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

        

            <form action="{!! route('admin.associate.update') !!}" method="post" enctype="multipart/form-data" id="associate_register" name="associate_register">
    @csrf
    <input type="hidden" name="created_at" class="created_at">
      <div class="row">
        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <div class="col-lg-12" id="form1error">
              </div>
              <h3 class="card-title mb-3">Member's Detail</h3>
              <div class=" row">
                <div class="col-lg-12 ">
                  <div class=" row">
                    <label class=" col-lg-3">Member Id</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8 ">

                      {{$memberData->member_id}}
                      <input type="hidden" name="id" id="id" value="{{ $memberData->id }}">
                      <input type="hidden" name="member_id" id="member_id" value="{{ $memberData->member_id }}">
                      <input type="hidden" class="form-control  " name="action" id="action"  readonly value="{{  Request::get('action')  }}" >
                     <input type="hidden" class="form-control  " name="requestid" id="requestid"  readonly value="{{  Request::get('request-id')  }}" >
                    </div>
                  </div>
                  <div class=" row">
                    <label class=" col-lg-3">Branch Name</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8 ">
                      {{$memberData->associate_branch->name}}
                      
                    </div>
                  </div>
                  <div class=" row">
                    <label class=" col-lg-3">Branch Code</label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8 ">
                      {{$memberData->associate_branch->branch_code}}
                      
                    </div>
                  </div>
                </div>
              </div>
                 
                 
                <div  >
                  <h5 class="card-title mb-3">Member's Personal Detail</h5>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">First Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->first_name}}
            
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-3">Last Name</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->last_name}} 
          </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Email Id</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->email}}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class="col-lg-3">Mobile No</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->mobile_no}} 
          </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Address</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">
            {{$memberData->address}}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">State </label><label class=" col-lg-1">:</label>
          <div class="col-lg-8  "> {{ getStateName($memberData->state_id) }}   </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">District</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">{{ getDistrictName($memberData->district_id) }}
          </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">City </label><label class=" col-lg-1">:</label>
          <div class="col-lg-8  "> {{ getCityName($memberData->city_id) }}   </div>
        </div>
      </div>
    </div>
    <div class=" row">
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Village</label><label class=" col-lg-1">:</label>
          <div class="col-lg-8 ">{{$memberData->village}}  </div>
        </div>
      </div>
      <div class="col-lg-6 ">
        <div class=" row">
          <label class=" col-lg-3">Pin Code </label><label class=" col-lg-1">:</label>
          <div class="col-lg-8  "> {{$memberData->pin_code}}   </div>
        </div>
      </div>
    </div>

    
    <h5 class="card-title mb-3">Member's Id Proofs </h5>
    <div class="row">
                @if($idProofDetail)
                <div class="col-lg-6">
                  <h6 class="card-title mb-3">First ID Proof </h6>
                  <div class="row">
                    <label class=" col-lg-3">ID Proof Type </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  "> {{ getIdProofName($idProofDetail->first_id_type_id)}} </div>  
                  </div>  
                  <div class="row">
                    <label class=" col-lg-3">ID No </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->first_id_no }}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->first_address }}</div>  
                  </div> 
                </div>
                <div class="col-lg-6">
                  <h6 class="card-title mb-3">Second ID Proof</h6> 
                  <div class="row">
                    <label class=" col-lg-3">ID Proof Type </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ getIdProofName($idProofDetail->second_id_type_id)}}</div>  
                  </div>
                  <div class="row">
                    <label class=" col-lg-3">ID No </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->second_id_no }}</div>  
                  </div> 
                  <div class="row">
                    <label class=" col-lg-3">Address </label><label class=" col-lg-1">:</label>
                    <div class="col-lg-8  ">{{ $idProofDetail->second_address }}</div>  
                  </div> 
                </div>
                @else
                <div class="col-lg-12">
                    <label class=" col-lg-12">No record found!. </label> 
                </div>
                @endif
              </div>
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
                  <label class="col-form-label col-lg-4">Associate Code<sup class="required">*</sup></label>
                  <div class="col-lg-8 error-msg">
                    <input type="text" name="form_no" id="form_no" class="form-control"   value="{{$memberData->associate_no}}" readonly=""  disabled="disabled">
                  </div>
                </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group row">
                  <label class="col-form-label col-lg-4">Form No<sup class="required">*</sup></label>
                  <div class="col-lg-8 error-msg">
                    <input type="text" name="form_no" id="form_no" class="form-control"   value="{{$memberData->associate_form_no}}" >
                  </div>
                </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group row">
                    <label class="col-form-label col-lg-4">Application Date<sup class="required">*</sup></label>
                    <div class="col-lg-8 error-msg">
                      <div class="input-group">
                        <span class="input-group-prepend">
                          <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </span>
                         <input type="text" class="form-control  " name="application_date" id="application_date" readonly  value="{{ date('d/m/Y', strtotime($memberData->associate_join_date)) }}"   >
                       </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group  row">
                    <label class="col-form-label col-lg-4">Senior Code</label>
                    <div class="col-lg-8 error-msg">
                      <input type="text" name="senior_code" id="senior_code" class="form-control"  readonly disabled value="{{ getSeniorData($memberData->associate_senior_id,'associate_no') }}">
                      <input type="hidden" name="senior_id" id="senior_id" class="form-control"  >
                      <span class="error invalid-feedback" id="associate_msg"></span>
                    </div>
                  </div>
                </div>
                @php
                $associate_name =getSeniorData($memberData->associate_senior_id,'first_name').' '.getSeniorData($memberData->associate_senior_id,'last_name');               
         @endphp
                <div class="col-lg-6">
                  <div class=" row  form-group ">
                    <label class=" col-lg-4">Name</label>
                    <div class="col-lg-8 " > 
                      <input type="text" name="senior_name" id="senior_name" class="form-control" readonly disabled value="{{ $associate_name }}">
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="row form-group  ">
                    <label class=" col-lg-4">Mobile No</label>
                    <div class="col-lg-8 " id="">
                      <input type="text" name="senior_mobile_no" id="senior_mobile_no" class="form-control" readonly disabled value="{{ getSeniorData($memberData->associate_senior_id,'mobile_no') }}">
                      <input type="hidden" name="seniorcarder_id" id="seniorcarder_id" class="form-control" value="{{$memberData->associate_senior_id}}" >
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
                      </select>
                    </div>
                  </div>
                </div> 
                <div class="col-lg-6">
                  <div class="form-group row">
                    <label class="col-lg-4">Status</label>
                    <div class="col-lg-8 ">
                      @if($memberData->associate_status==1) Active @else  Inactive @endif
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
                        <input type="text" name="first_g_first_name" id="first_g_first_name" class="form-control"  value="{{$guarantorDetail->first_name}}" >
                        <input type="hidden" name="guarantor_id" id="guarantor_id" class="form-control"  value="{{$guarantorDetail->id}}" >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="first_g_Mobile_no" id="first_g_Mobile_no" class="form-control"    value="{{$guarantorDetail->first_mobile_no}}" >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Address<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <textarea id="first_g_address" name="first_g_address" class="form-control" >{{$guarantorDetail->first_address}}</textarea> 
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
                        <input type="text" name="second_g_first_name" id="second_g_first_name" class="form-control"   value="{{$guarantorDetail->second_name}}" >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Mobile No</label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="second_g_Mobile_no" id="second_g_Mobile_no" class="form-control"   value="{{$guarantorDetail->second_mobile_no}}" >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Address</label>
                      <div class="col-lg-9 error-msg">
                        <textarea id="second_g_address" name="second_g_address" class="form-control" >{{$guarantorDetail->second_address}}</textarea> 
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
                 <input type="hidden" name="old_dep_count" id="old_dep_count" class="form-control "  value="{{count( $dependentDetail )}}"> 
              @if( count( $dependentDetail ) > 0 )
               @foreach($dependentDetail as $val)
               <div class="col-lg-12 row" id="old_dep_remove{{$val->id}}"> 
                    <div class="col-lg-12">
                        <div class="form-group row">
                          <div class="col-lg-12"> 

                          <button type="button" class="btn btn-primary" id="old_remove" onclick="remove_old_dep('{{$val->id}}')">remove </button>  
                          </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Full Name</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="old_dep_first_name[{{$val->id}}]" id="old_dep_first_name{{$val->id}}" class="form-control old_dep_first_name_class"   value="{{$val->name}}">
                        <input type="hidden" name="old_dep_id[{{$val->id}}]" id="old_dep_id" class="form-control "  value="{{$val->id}}"> 
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Age</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="old_dep_age[{{$val->id}}]" id="old_dep_age{{$val->id}}" class="form-control old_dep_age_class"  value="{{$val->age}}">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Relation</label>
                      <div class="col-lg-8 error-msg"> 
                        <select name="old_dep_relation[{{$val->id}}]" id="old_dep_relation{{$val->id}}" class="form-control old_dep_relation_class"  >
                          <option value="">Select Relation</option>
                          @foreach ($relations as $val1)
                              <option value="{{ $val1->id }}" @if($val->relation==$val1->id)  selected @endif >{{ $val1->name }}</option>
                           @endforeach
                        </select>

                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Per month income</label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="old_dep_income[{{$val->id}}]" id="old_dep_income{{$val->id}}" class="form-control old_dep_income_class"   value="{{ round($val->monthly_income) }}">
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
                              <input type="radio" id="old_dep_gender_male{{$val->id}}" name="old_dep_gender[{{$val->id}}]" class="custom-control-input old_dep_gender_class" value="1" @if($val->gender==1)  checked="checked" @endif >
                              <label class="custom-control-label" for="old_dep_gender_male{{$val->id}}">Male</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="old_dep_gender_female{{$val->id}}" name="old_dep_gender[{{$val->id}}]" class="custom-control-input old_dep_gender_class" value="0"  @if($val->gender==0)  checked="checked" @endif >
                              <label class="custom-control-label" for="old_dep_gender_female{{$val->id}}">Female</label>
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
                              <input type="radio" id="old_dep_married{{$val->id}}" name="old_dep_marital_status[{{$val->id}}]" class="custom-control-input old_dep_marital_status_class" value="1" @if($val->marital_status==1)  checked="checked" @endif >
                              <label class="custom-control-label" for="old_dep_married{{$val->id}}">Married</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="old_dep_unmarried{{$val->id}}" name="old_dep_marital_status[{{$val->id}}]" class="custom-control-input old_dep_marital_status_class" value="0"  @if($val->marital_status==0)  checked="checked" @endif >
                              <label class="custom-control-label" for="old_dep_unmarried{{$val->id}}">Un Married</label>
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
                              <input type="radio" id="old_dep_living_yes{{$val->id}}" name="old_dep_living[{{$val->id}}]" class="custom-control-input old_dep_living_class" value="1" @if($val->living_with_associate==1)  checked="checked" @endif >
                              <label class="custom-control-label" for="old_dep_living_yes{{$val->id}}">Yes</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="old_dep_living_no{{$val->id}}" name="old_dep_living[{{$val->id}}]" class="custom-control-input old_dep_living_class" value="0"   @if($val->living_with_associate==0)  checked="checked" @endif>
                              <label class="custom-control-label" for="old_dep_living_no{{$val->id}}">No</label>
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
                              <input type="radio" id="old_dep_type_fully{{$val->id}}" name="old_dep_type[{{$val->id}}]" class="custom-control-input old_dep_type_class" value="1"  @if($val->dependent_type==1)  checked="checked" @endif>
                              <label class="custom-control-label" for="old_dep_type_fully{{$val->id}}">Fully</label>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="custom-control custom-radio mb-3  ">
                              <input type="radio" id="old_dep_type_partially{{$val->id}}" name="old_dep_type[{{$val->id}}]" class="custom-control-input old_dep_type_class" value="0"   @if($val->dependent_type==0)  checked="checked" @endif>
                              <label class="custom-control-label" for="old_dep_type_partially{{$val->id}}">Partially</label>
                            </div>
                          </div>
                        </div>
                      </div> 
                    </div>
                  </div>
              </div>
          @endforeach 
          @endif
<!----------------------------------------------------------------------------->
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
                        <button type="submit" class="btn btn-primary">Update </button>
                        
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
  



    </div>
</div>
@include('templates.admin.associate.partials.edit_script')
@stop