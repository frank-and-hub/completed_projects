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

        <form action="{!! route('admin.member.save') !!}" method="post" enctype="multipart/form-data" id="member_register" name="member_register">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Customer Form Information</h3>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Form No<sup
                                                class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="form_no" id="form_no" class="form-control"
                                                value="{{old('form_no', $memberDetail->form_no)}}">
                                                <input type="hidden" name="getType" id="getType" class="form-control"
                                                value="{{ $_GET['type']}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Application Date<sup
                                                class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <span class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fa fa-calendar"
                                                            aria-hidden="true"></i></span>
                                                </span>
                                                <input type="text" class="form-control  " name="application_date"
                                                    id="application_date" readonly
                                                    value="{{ old('application_date', date('d/m/Y', strtotime($memberDetail->re_date))) }}"
                                                    disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Customer ID<sup
                                                class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " name="member_id" id="member_id"
                                                readonly value="{{  $memberDetail->member_id  }}" disabled>
                                            <input type="hidden" class="form-control  " name="id" id="id" readonly
                                                value="{{  $memberDetail->id  }}">
                                            <input type="hidden" class="form-control  " name="action" id="action"
                                                readonly value="{{  Request::get('action')  }}">
                                            <input type="hidden" class="form-control  " name="requestid" id="requestid"
                                                readonly value="{{  Request::get('request-id')  }}">
                                        </div>
                                    </div>
                                </div>
                                @if($_GET['type']==0)  
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID<sup
                                                class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " name="memberCompanyID" id="memberCompanyID"
                                                readonly value="{{$membercompany->member_id}}" disabled> 
                                        </div>
                                    </div>
                                </div>
                                @endif 
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch<sup
                                                class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control select" name="branch_id" id="branch_id" readonly
                                                disabled="disabled">
                                                <option value="">Select branch</option>
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}" @if(old('branch_id',$memberDetail->branch_id) == $val->id ) selected @endif >{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @if($memberDetail->employee_id)
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Is Employee<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control " name="is_employee" id="is_employee" readonly value="{{$memberDetail->is_employee ? 'Yes' : 'No'}}" disabled>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($memberDetail->employee_id)
                                <div class="col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Code<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="employee_code" value="@if($memberDetail->employee_code != '') {{$memberDetail->employee_code}}@else N/A @endif" id="employee_code" class="form-control" readonly disabled>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Personal Information </h3>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">First Name<sup class="required">*</sup> </label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="first_name" id="first_name" class=" form-control"
                                        value="{{ old('first_name',$memberDetail->first_name) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Last Name </label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="last_name" id="last_name" class="form-control"
                                        value="{{ old('last_name',$memberDetail->last_name) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Email Id</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="email" name="email" id="email" class="form-control"
                                        value="{{ old('email',$memberDetail->email) }}">
                                    <span class="error invalid-feedback" id="email_msg"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup></label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="mobile_no" id="mobile_no" class="form-control"
                                        value="{{ old('mobile_no',$memberDetail->mobile_no)  }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Date of Birth/Age<sup class="required">*</sup>
                                </label>
                                <div class="col-lg-6 error-msg">
                                    <div class="input-group">
                                        <span class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-calendar"
                                                    aria-hidden="true"></i></span>
                                        </span>
                                        <input type="text" class="form-control " name="dob" id="dob"
                                            value="{{ old('application_date', date('d/m/Y', strtotime(convertdate($memberDetail->dob)))) }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <span id="age_display" class='age_show '>{{ $memberDetail->age??calculateAge(date('Y-m-d', strtotime(convertdate($memberDetail->dob))))}}
                                        @if($memberDetail->age>1) years @else Year @endif </span>
                                    <input type="hidden" class="form-control " name="age" id="age" value="{{ old('age',($memberDetail->age??calculateAge(date('Y-m-d', strtotime(convertdate($memberDetail->dob))))) ) }}" readonly="readonly">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Gender<sup class="required">*</sup> </label>
                                <div class="col-lg-9 error-msg">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="gender_male" name="gender"
                                                    class="custom-control-input" value="1"
                                                    @if(old('gender',$memberDetail->gender) ==1) checked @endif>
                                                <label class="custom-control-label" for="gender_male">Male</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="gender_female" name="gender"
                                                    class="custom-control-input" value="0"
                                                    @if(old('gender',$memberDetail->gender) ==0) checked @endif>
                                                <label class="custom-control-label" for="gender_female">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-form-label col-lg-3">Occupation</label>
                                <div class="col-lg-9 error-msg">
                                    <select class="form-control select" name="occupation" id="occupation">
                                        <option value="">Select Occupation</option>
                                        @foreach( $occupation as $val )
                                        <option value="{{ $val->id }}" @if(old('occupation',$memberDetail->
                                            occupation_id) == $val->id ) selected @endif >{{ $val->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Annual Income <sup
                                        class="required">*</sup></label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="annual_income" id="annual_income" class="form-control"
                                        value="{{ old('annual_income',$memberDetail->annual_income) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Mother Name</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="mother_name" id="mother_name" class="form-control"
                                        value="{{ old('mother_name',$memberDetail->mother_name) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Father/ Husband's Name<sup
                                        class="required">*</sup></label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="f_h_name" id="f_h_name" class="form-control"
                                        value="{{ old('f_h_name',$memberDetail->father_husband) }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Marital status </label>
                                <div class="col-lg-9 error-msg">
                                    <div class="row">
                                        <div class="col-lg-5">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="married" name="marital_status"
                                                    class="custom-control-input m-status" value="1"
                                                    @if(old('marital_status',$memberDetail->marital_status) ==1) checked
                                                @endif>
                                                <label class="custom-control-label" for="married">Married</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="un_married" name="marital_status"
                                                    class="custom-control-input m-status" value="0"
                                                    @if(old('marital_status',$memberDetail->marital_status) ==0) checked
                                                @endif>
                                                <label class="custom-control-label" for="un_married">Un Married</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row anniversary-date-box" @if( $memberDetail->marital_status == 0 )
                                style="display:none" @endif >
                                <label class="col-form-label col-lg-3">Anniversary Date</label>
                                <div class="col-lg-9 error-msg">
                                    <div class="input-group">
                                        <span class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-calendar"
                                                    aria-hidden="true"></i></span>
                                        </span>
                                        <input type="text" class="form-control " name="anniversary_date" readonly
                                            id="anniversary_date" value="@if ( $memberDetail->anniversary_date != null ) {{ old('anniversary_date', date('d/m/Y', strtotime
                     ($memberDetail->anniversary_date))) }} @endif" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-form-label col-lg-3">Religion</label>
                                <div class="col-lg-9 error-msg">
                                    <select class="form-control select" name="religion" id="religion">
                                        <option value="0">Select Religion</option>
                                        @foreach( $religion as $val )
                                        <option value="{{ $val->id }}" @if(old('religion',$memberDetail->religion_id) ==
                                            $val->id ) selected @endif >{{ $val->name }}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-form-label col-lg-3">Categories</label>
                                <div class="col-lg-9 error-msg">
                                    <select class="form-control select" name="special_category" id="special_category">
                                        <option value="0">General Category</option>
                                        @foreach( $specialCategory as $val )
                                        <option value="{{ $val->id }}" @if(old('special_category',$memberDetail->
                                            special_category_id) == $val->id ) selected @endif >{{ $val->name }}
                                        </option> @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                    @php
                    $bank_name = "";
                    $bank_account_no = "";
                    $bank_branch_name = "";
                    $bank_ifsc = "";
                    $bank_branch_address = "";
                    $bank_id = 0;

                    @endphp
                    @if($bankDetail)
                    @php
                    $bank_name = $bankDetail->bank_name;
                    $bank_account_no = $bankDetail->account_no;
                    $bank_branch_name = $bankDetail->branch_name;
                    $bank_ifsc = $bankDetail->ifsc_code;
                    $bank_branch_address = $bankDetail->address;
                    $bank_id = $bankDetail->id;
                    @endphp
                    @endif

                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Bank Information </h3>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Bank Name</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="bank_name" id="bank_name" class=" form-control"
                                        value="{{ old('bank_name',$bank_name) }}">
                                    <input type="hidden" name="bank_id" id="bank_id" class=" form-control"
                                        value="{{ $bank_id }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Branch Name</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="bank_branch_name" id="bank_branch_name"
                                        class=" form-control" value="{{ old('bank_branch_name',$bank_branch_name) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Bank A/C No</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="password" name="bank_account_no" id="bank_account_no" class=" form-control"
                                        value="{{ old('bank_account_no',$bank_account_no) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Confirm Bank A/C No</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="cbank_account_no" id="cbank_account_no" class=" form-control" value="{{ old('bank_account_no',$bank_account_no) }}" >
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">IFSC Code</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="bank_ifsc" id="bank_ifsc" class=" form-control"
                                        value="{{ old('bank_ifsc',$bank_ifsc) }}">
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-form-label col-lg-3">Branch Address</label>
                                <div class="col-lg-9 error-msg">
                                    <textarea name="bank_branch_address" id="bank_branch_address"
                                        class="form-control"> {{ old('bank_branch_address',$bank_branch_address) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Nominee Information </h3>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Full Name<sup class="required">*</sup> </label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="nominee_first_name" id="nominee_first_name"
                                        class=" form-control"
                                        value="{{ old('nominee_first_name',$nomineeDetail->name ) }}">
                                    <input type="hidden" name="nominee_id" id="nominee_id" class=" form-control"
                                        value="{{ $nomineeDetail->id }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Relationship <sup
                                        class="required">*</sup></label>
                                <div class="col-lg-9 error-msg">

                                    <select name="nominee_relation" id="nominee_relation" class=" form-control">
                                        <option value="">Select Relation</option>
                                        @foreach ($relations as $val)
                                        <option value="{{ $val->id }}" @if(old('nominee_relation',$nomineeDetail->
                                            relation) == $val->id ) selected @endif >{{ $val->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Gender</label>
                                <div class="col-lg-9 error-msg">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3 ">
                                                <input type="radio" id="nominee_gender_male" name="nominee_gender"
                                                    class="custom-control-input" value="1"
                                                    @if(old('nominee_gender',$nomineeDetail->gender) == 1) checked
                                                @endif>
                                                <label class="custom-control-label"
                                                    for="nominee_gender_male">Male</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="custom-control custom-radio mb-3  ">
                                                <input type="radio" id="nominee_gender_female" name="nominee_gender"
                                                    class="custom-control-input" value="0"
                                                    @if(old('nominee_gender',$nomineeDetail->gender) == 0) checked
                                                @endif>
                                                <label class="custom-control-label"
                                                    for="nominee_gender_female">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Date of Birth</label>
                                <div class="col-lg-6 error-msg">
                                    <div class="input-group">
                                        <span class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-calendar"
                                                    aria-hidden="true"></i></span>
                                        </span>
                                        <input type="text" class="form-control  " name="nominee_dob" id="nominee_dob" readonly
                                            value="{{ old('nominee_dob', date('d/m/Y', strtotime(convertdate($nomineeDetail->dob)))) }}">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <span id="nominee_age_display" class='nominee_age_show'>{{ $nomineeDetail->age??calculateAge(date('Y-m-d', strtotime(convertdate($nomineeDetail->dob))))}}
                                        @if($nomineeDetail->age>1) years @else Year @endif</span>
                                    <input type="hidden" class="form-control " name="nominee_age" id="nominee_age"
                                        value="{{ old('nominee_age',($nomineeDetail->age??calculateAge(date('Y-m-d', strtotime(convertdate($nomineeDetail->dob))))) ) }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup> </label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="nominee_mobile_no" id="nominee_mobile_no"
                                        class=" form-control"
                                        value="{{ old('nominee_mobile_no',$nomineeDetail->mobile_no ) }}">
                                </div>
                            </div>
                            <div class="form-group row" id="minor_hide">
                                <label class="col-form-label col-lg-3">If Minor </label>
                                <div class="col-lg-9 error-msg">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <div class="custom-control custom-checkbox mb-3 ">
                                                <input type="checkbox" id="is_minor" name="is_minor"
                                                    class="custom-control-input" value="1"
                                                    @if(old('is_minor',$nomineeDetail->is_minor) == 1) checked @endif>
                                                <label class="custom-control-label" for="is_minor" value="1">Yes</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="nominee_parent_detail" @if(old('is_minor',$nomineeDetail->is_minor) == 1)
                                style="display: block" @else style="display: none" @endif>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-3">Parent Name <sup class="required">*</sup>
                                    </label>
                                    <div class="col-lg-9 error-msg">
                                        <input type="text" name="parent_nominee_name" id="parent_nominee_name"
                                            class=" form-control"
                                            value="{{ old('parent_nominee_name',$nomineeDetail->parent_name ) }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-3">Parent Mobile No<sup class="required">*</sup>
                                    </label>
                                    <div class="col-lg-9 error-msg">
                                        <input type="text" name="parent_nominee_mobile_no" id="parent_nominee_mobile_no"
                                            class=" form-control"
                                            value="{{ old('parent_nominee_mobile_no',$nomineeDetail->parent_no ) }}">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Associate Details </h3>
                            <div class="form-group row ">
                                <label class="col-form-label col-lg-3">Associate Code<sup
                                        class="required">*</sup></label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="associate_code" id="associate_code" class="form-control"
                                        value="{{ old('associate_code',$memberDetail->associate_code ) }}" readonly>
                                    <span class="error invalid-feedback" id="associate_msg"></span>
                                </div>
                            </div>
                            @php
                            $associate_name =getSeniorData($memberDetail->associate_id,'first_name').'
                            '.getSeniorData($memberDetail->associate_id,'last_name');
                            @endphp
                            <div class="form-group row">
                                <label class="col-form-label col-lg-3">Associate Name</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="associate_name" id="associate_name" class="form-control"
                                        readonly disabled="" value="{{ old('associate_name',$associate_name) }}">
                                    <input type="hidden" name="associate_id" id="associate_id" class="form-control"
                                        readonly value="{{ old('associate_id',$memberDetail->associate_id) }}">
                                </div>
                            </div>
                            <div class="form-group row " id="hide_carder">
                                <label class="col-form-label col-lg-3">Associate Carder</label>
                                <div class="col-lg-9 error-msg">
                                    <input type="text" name="associate_carder" id="associate_carder"
                                        class="form-control" readonly disabled=""
                                        value="{{ old('associate_carder',getCarderName(getSeniorData($memberDetail->associate_id,'current_carder_id'))) }}">

                                </div>
                            </div>

                        </div>
                    </div>





                </div>
                <div class="col-lg-4">
                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3"> Upload Photo </h3>
                            <div class="form-group row">
                                <div class="col-lg-12 ">
                                    <span class="text-center rounded-circle w-100">
                                        {{--
                                        @if($memberDetail->photo=='')
                                        <img class="img-fluid" id="photo-preview" src="{{url('/')}}/asset/images/user.png">
                                        @else
                                        <img class="img-fluid" id="photo-preview" src="{{ImageUpload::generatePreSignedUrl('profile/member_avatar/'.$memberDetail->photo)}}">
                                        @endif
                                        --}}
                                        @if($memberDetail->photo=='')
                                        <img class="img-fluid" id="photo-preview" src="{{url('/')}}/asset/images/user.png">
                                        @else
                                        <?php
                                        $folderName = 'profile/member_avatar/' . $memberDetail->photo;
                                        if (ImageUpload::fileExists($folderName) && $memberDetail->photo != '') {
                                            $photo_url = ImageUpload::generatePreSignedUrl($folderName);
                                        } else {
                                            $photo_url = url('/')."/asset/images/user.png";
                                        }
                                        ?>
                                        <img class="img-fluid" id="photo-preview" src="{{$photo_url}}">
                                        @endif
                                    </span>
                                </div>
                                <div class="custom-file error-msg">
                                    <input type="file" class="custom-file-input" id="photo" name="photo"
                                        accept="image/*">
                                    <label class="custom-file-label" for="photo">Select photo</label>
                                    <span class="form-text text-muted">Accepted formats:png, jpg.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="created_at" class="created_at">
                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3"> Upload Signature </h3>
                            <div class="form-group row">
                                <div class="col-lg-12 ">
                                    <span class="text-center">
                                        @if($memberDetail->signature=='')
                                        <img class="img-fluid" id="signature-preview" src="{{url('/')}}/asset/images/signature-logo-design.png">
                                        @else
                                        {{--<img class="img-fluid" id="signature-preview" src="{{url('/')}}/asset/profile/member_signature/{{ $memberDetail->signature }}">--}}
                                        <img class="img-fluid" id="signature-preview" src="{{ImageUpload::generatePreSignedUrl('profile/member_signature/'.$memberDetail->signature)}}">
                                        @endif
                                    </span>
                                </div>
                                <div class="custom-file  error-msg">
                                    <input type="file" class="custom-file-input" id="signature" name="signature"
                                        accept="image/*">
                                    <label class="custom-file-label" for="signature">Select document</label>
                                    <span class="form-text text-muted">Accepted formats:png, jpg.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Residence Address</h3>

                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">Address<sup class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <textarea name="address" id="address"
                                        class="form-control">{{ old('address', $memberDetail->address) }}</textarea>
                                </div>
                            </div>
                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">State<sup class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control select" name="state_id" id="state_id">
                                        <option value="">Select State</option>
                                        @foreach( $state as $val )
                                        <option value="{{ $val->id }}" @if(old('state_id',$memberDetail->state_id) ==
                                            $val->id ) selected @endif >{{ $val->name }}</option> @endforeach
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
                                    <input type="text" name="village_name" id="village_name" class="form-control"
                                        value="{{ old('village_name', $memberDetail->village) }}">
                                </div>
                            </div>
                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">Pin Code<sup class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <input type="text" name="pincode" id="pincode" class="form-control"
                                        value="{{ old('pin_code', $memberDetail->pin_code) }}">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3">ID Proof </h3>

                            <h5 class="card-title mb-3">First ID Proof </h5>
                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">ID Proof Document Type <sup
                                        class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control select" name="first_id_type" id="first_id_type">
                                        <option value="">Select Id Type</option>
                                        @foreach( $idTypes as $val )
                                        @if( $val->id != 7)
                                        <option value="{{ $val->id }}" @if(old('first_id_type',$idProofDetail->
                                            first_id_type_id) == $val->id ) selected @endif >{{ $val->name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="IdProof_id" id="IdProof_id" class=" form-control"
                                        value="{{ $idProofDetail->id }}">
                                </div>
                            </div>

                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">ID No<sup class="required">*</sup></label>
                                <div class="col-lg-12  error-msg">
                                    <div class="row">
                                        <div class="col-lg-10 ">
                                            <input type="text" name="first_id_proof_no" id="first_id_proof_no"
                                                class="form-control"
                                                value="{{ old('first_id_proof_no', $idProofDetail->first_id_no) }}">
                                        </div>
                                        <div class="col-lg-2 ">
                                            <button type="button" id="first_id_tooltip" class="btn btn-sm btn-default"
                                                data-toggle="tooltip" data-placement="top"
                                                title="Enter Id proof number."><i
                                                    class="fas fa-exclamation-circle text-secondary"></i>
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
                                                <input type="checkbox" id="first_same_as" name="first_same_as"
                                                    class="custom-control-input">
                                                <label class="custom-control-label" for="first_same_as">Yes</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">Address<sup class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <textarea name="first_address_proof" id="first_address_proof"
                                        class="form-control">{{ old('first_address_proof', $idProofDetail->first_address) }}</textarea>
                                </div>
                            </div>

                            <h5 class="card-title mb-3">Second ID Proof (Address Proof)</h5>
                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">ID Proof Document Type <sup
                                        class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control select" name="second_id_type" id="second_id_type">
                                        <option value="">Select Id Type</option>
                                        @foreach( $idTypes as $val )
                                        @if($val->id != 5)
                                        <option value="{{ $val->id }}" @if(old('second_id_type',$idProofDetail->
                                            second_id_type_id) == $val->id ) selected @endif >{{ $val->name }}</option>
                                        @endif @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">ID No<sup class="required">*</sup></label>

                                <div class="col-lg-12  error-msg">
                                    <div class="row">
                                        <div class="col-lg-10 ">
                                            <input type="text" name="second_id_proof_no" id="second_id_proof_no"
                                                class="form-control"
                                                value="{{ old('second_id_proof_no', $idProofDetail->second_id_no) }}">
                                        </div>
                                        <div class="col-lg-2 ">
                                            <button type="button" id="second_id_tooltip" class="btn btn-sm btn-default"
                                                data-toggle="tooltip" data-placement="top"
                                                title="Enter Id proof number."><i
                                                    class="fas fa-exclamation-circle text-secondary"></i>
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
                                                <input type="checkbox" id="second_same_as" name="second_same_as"
                                                    class="custom-control-input">
                                                <label class="custom-control-label" for="second_same_as">Yes</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group  ">
                                <label class="col-form-label col-lg-12">Address<sup class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <textarea name="second_address_proof" id="second_address_proof"
                                        class="form-control">{{ old('second_id_proof_no', $idProofDetail->second_address) }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>




                </div>
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="text-center">
                                <button type="submit" class="btn bg-blue">Update</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@include('templates.admin.member.partials.edit_script')
@stop