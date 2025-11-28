@extends('layouts/branch.dashboard')
@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col-lg-12">
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
            <form action="#" method="post" enctype="multipart/form-data" id="addrequest" name="fillter">
                @csrf
                <input type="hidden" name="created_at" class="created_at">
                @php
                $stateid = getBranchState(Auth::user()->username);
                @endphp
                <input type="hidden" class="form-control" name="current_date" id="current_date" value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}  ">
                <input type="hidden" class="form-control" name="create_application_date" id="create_application_date" value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}  ">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card bg-white">
                            <div class="card-body">
                                <h3 class="card-title mb-3 maintital">Correction Request</h3>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Correction type<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="correctionType" id="correctionType" class="form-control" aria-invalid="false">
                                                    <option value="">Please Select</option>
                                                    <option data-val="0" value="Customer Details">Customer</option>
                                                    <option data-val="1" value="Associate Details">Associate</option>
                                                    <option data-val="2" value="Investment Details">Investment
                                                    </option>
                                                </select>
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Field name<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="fields" id="fields" class="form-control input">
                                                    <option value="">Please Select</option>
                                                </select>
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-none" id="input_val">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12" id="input_id"><sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="user_info" class="form-control input">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" id="cmsubmit" class="btn btn-primary text-right float-right">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form id="update_form" class="d-none form_comp">
                <div class="row">
                    <input type="hidden" name="branch_id" id="branch" value="{{ $branch_id }}">
                    <input type="hidden" name="company_id" id="company_id">
                    <input type="hidden" name="correction_type_Id" id="correction_id">
                    <input type="hidden" name="type_id" id="type_id">
                    <div class="col-lg-12">
                        <div class="card bg-white">
                            <div class="card-body">
                                <h3 class="card-title mb-3 maintital">TO UPDATE</h3>
                                <div class="row">
                                    <div class="col-md-4 form_comp old_vallue">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Old Value</label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="card-body d-none old_pic p-0">
                                                    <div class="form-group row">
                                                        <div class="col-lg-12 ">
                                                            <span class="text-center rounded-circle w-100">
                                                            <img class="col-lg-12 img-fluid d-none old_pic" style="width: 310px;" id="old_pic" src="">
                                                            </span>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                                <input type="text" id="old_value" name="old_value" readonly class="form-control input">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="normal_case">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="new_value" id="new_value" class="form-control input update_form">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="number">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="new_number" id="new_number" minlength="10" class="form-control input update_form">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="dob">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="new_dob" id="new_dob" class="form-control input update_form">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="pphoto">
                                        <div class="card bg-white">
                                            <div class="card-body">
                                                <h3 class="card-title mb-3"> Upload New Photo </h3>
                                                <div class="form-group row">
                                                    <div class="col-lg-12 ">
                                                        <span class="text-center rounded-circle w-100">
                                                            <img alt="Image placeholder" id="photo-preview" src="{{ url('/') }}/asset/images/user.png">
                                                        </span>
                                                    </div>
                                                    <div class="custom-file error-msg">
                                                        <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*">
                                                        <label class="custom-file-label" for="photo">Select
                                                            photo</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-4 new_value" id="photo">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="file" accept="image/*" name="image" disabled id="image" class="form-control input update_form">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="col-md-4 new_value" id="gender">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="gender_new" id="gender_new" class="form-control dataval update_form" aria-invalid="false">
                                                <option value="">Please Select Gender</option>
                                                <option data-val="0" value="Female">Female</option>
                                                <option data-val="1" value="Male">Male</option>
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="marital_status">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="marital_status_new" id="marital_status_new" class="form-control dataval update_form" aria-invalid="false">
                                                <option value="">Please Select</option>
                                                <option data-val="0" value="Unmarried">Unmarried</option>
                                                <option data-val="1" value="Married">Married</option>
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="relation">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="relation_new" id="relation_new" class=" dataval form-control update_form">
                                                <option value="">Select Relation</option>
                                                @foreach ($relations as $val)
                                                <option data-val="{{ $val->id }}" value="{{ $val->name }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="idTypes">
                                        <label class="col-form-label col-lg-12">Id Type<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="idType" id="idType" class=" form-control update_form">
                                                <option value="">Select Id</option>
                                                @foreach ($idTypes as $val)
                                                <option data-val="{{ $val->id }}" value="{{ $val->name }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="idno">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Id<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="actual_value" class="update_form form-control" id="actual_value">
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="Religions">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control  dataval select update_form" name="religion" id="religion">
                                                <option value="0">Select Religion</option>
                                                @foreach ($religion as $val)
                                                <option data-val="{{ $val->id }}" value="{{ $val->name }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="category">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control select update_form  dataval" name="special_category" id="special_category">
                                                <option data-val="" value=""> </option>
                                                <option data-val="0" value="General">General </option>
                                                @foreach ($specialCategory as $val)
                                                <option data-val="{{ $val->id }}" value="{{ $val->name }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="state">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control select update_form dataval" name="state_id" id="state_id">
                                                <option value="">Select State</option>
                                                @foreach ($state as $val)
                                                <option data-val="{{ $val->id }}" value="{{ $val->name }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value" id="occupation_id">
                                        <label class="col-form-label col-lg-12">New Value<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control select update_form dataval" name="occupation" id="occupation">
                                                <option value="">Select Occupation</option>
                                                @foreach ($occupation as $val)
                                                <option data-val="{{ $val->id }}" value="{{ $val->name }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 new_value form_comp">
                                        <label class="col-form-label col-lg-12">Reason<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <textarea name="description" id="description" class="form-control update_form" cols="4" rows="1"></textarea>
                                            <div class="input-group">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="tochk">
                                </div>
                                <button type="submit" id="cmsubmit2" class="btn btn-primary text-right float-right">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.branch.CorrectionManagement.partials.script_add_request')
@stop