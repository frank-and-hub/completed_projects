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
      <div class="card bg-white" >
        <form action="{!! route('admin.usermanagement.save') !!}" method="post" enctype="multipart/form-data" id="usermanagement_register" name="usermanagement_register"  >
          @csrf
          <input type="hidden" name="created_at" class="created_at">  
          <div class="card-body">
            <div class="form-group row">
              <label class="col-form-label col-lg-3">User Name<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <input type="text" name="username" id="username" class=" form-control" value="{{ (old('username')!='') ? old('username') : ($users->username)  ?? '' }}" >
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-3">Employee Code<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <input type="text" name="employee_code" id="employee_code" class="form-control"  value="{{ (old('employee_code')!='') ? old('employee_code') : ($users->employee_code)  ?? '' }}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-lg-3">Employee Name<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <input type="text" name="employee_name" id="employee_name" class="form-control"  value="{{ (old('employee_name')!='') ? old('employee_name') : ($users->employee_name)  ?? '' }}" readonly>
              </div>
            </div>
            
            <div class="form-group row">
              <label class="col-form-label col-lg-3">Mobile No<sup class="required">*</sup></label>
              <div class="col-lg-9 error-msg">
                <input type="text" name="mobile_number" id="mobile_number" class="form-control" value="{{ (old('mobile_number')!='') ? old('mobile_number') : ($users->mobile_number)  ?? '' }}">
              </div>
            </div>
            
            <div class="form-group row">
              <label class="col-form-label col-lg-3">User Id<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <input type="text" name="user_id" id="user_id" class="form-control"  value="{{ (old('user_id')!='') ? old('user_id') : ($users->user_id)  ?? '' }}">
              </div>
            </div>
      
            <div class="form-group row">
              <label class="col-form-label col-lg-3">Branch<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <select class="form-control" id="bank_id" name="bank_id">
                    <option value="">Select Branch</option>
                    <option value="all" {{(old('bank_id')!='' && old('bank_id') == 'all') ? 'selected' : (isset($users->branch_id)  && ('0' == $users->branch_id) ? 'selected' : '')  }} >All</option>
                      @foreach($branchs as $branch)
                    <option value="{{$branch->id}}" {{((old('bank_id')!='') && (old('bank_id') == $branch->id)) ? 'selected' : ($users->branch_id && ($branch->id == $users->branch_id) ? 'selected' : '' ) }} >{{$branch->name}}</option>					  
                      @endforeach
                </select>
              </div>
            </div>              
            <div class="form-group row">
              <label class="col-form-label col-lg-3">Password<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <input type="password" name="password" id="password" class="form-control"  value="">
              </div>
            </div>
            
            <div class="form-group row">
              <label class="col-form-label col-lg-3">Confirm Password<sup class="required">*</sup> </label>
              <div class="col-lg-9 error-msg">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"  value="">
              </div>
            </div>              
            <div class="text-center">
            <input type="hidden" name="id" id="id" value="{{ $users->id ?? ''}}"/>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>        
</div>
@include('templates.admin.user_management.partials.usermanagement_script')
@stop