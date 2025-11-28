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
  <form action="{!! route('admin.ssbaccount.save') !!}" method="post" enctype="multipart/form-data" id="ssbaccount_register" name="usermanagement_register"  >
    @csrf
    <input type="hidden" name="created_at" class="created_at">  
            <div class="card-body">
              <div class="form-group row">
                <label class="col-form-label col-lg-3">Amount<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <input type="text" name="amount" id="amount" class=" form-control" >
                </div>
              </div>
              
			  <div class="form-group row">
                <label class="col-form-label col-lg-3">Select Plan<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <select class="form-control" id="plan_type" name="plan_type">
					  <option value="">Select Plan</option>
					  <option value="1" >Ssb</option>
					<option value="2" >Ssb Child</option>
					 
				  </select>
                </div>
              </div>
			  
			  	  <div class="form-group row">
                <label class="col-form-label col-lg-3">Select User Type<sup class="required">*</sup> </label>
                <div class="col-lg-9 error-msg">
                  <select class="form-control" id="user_type" name="user_type">
					  <option value="">Select User Type</option>
					  <option value="1" >Member </option>
					<option value="2" >Associate</option>
					 
				  </select>
                </div>
              </div>
              
                     
              <div class="text-center">
			  <input type="hidden" name="id" id="id"/>
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </form>
            </div>
          </div>          
        </div>
        
    </div>
</div>
@include('templates.admin.ssb_account.partials.ssbaccount_script')
@stop