@php
    $admin = Auth::user()->role_id != 3 ? true : false;
    $pathLayout = $admin == true ? 'templates.admin.master' : 'layouts.branch.dashboard';
    $pathScrip = $admin == true ? 'templates/CommonViews/Member/script_a' : 'templates/CommonViews/Member/script_b';
@endphp
@extends($pathLayout)
@section('content')    
<div class="content"> 
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
		@if($admin == true)
        <form action="{!! route('admin.associate.save') !!}" method="post" enctype="multipart/form-data" id="associate_register" name="associate_register">
		@else
		<form action="{!! route('branch.save-blacklist-member-on-loan') !!}" method="post" enctype="multipart/form-data" id="associate_register" name="associate_register">
		@endif
		  @csrf
			  <input type="hidden" name="created_at" class="created_at">
				<div class="col-lg-12">
				  <div class="card bg-white" > 
					<div class="card-body">
					  <div class="col-lg-12" id="form1error">
					  </div>
					  <h3 class="card-title mb-3">Customer's Detail</h3>
					  
						<div class="  row">
						  <label class="col-form-label col-lg-3"></label>
						  <div class="col-lg-9 error-msg">
							<h4 class="card-title mb-3 ">Search Customer</h4>
						  </div>
						</div>
						@if($admin == true)
						<div class="form-group row">
						  <label class="col-form-label col-lg-3">Customer Id<sup class="required">*</sup></label>
						  <div class="col-lg-9 error-msg"> 
							<input type="text" name="member_id" id="member_id" class="form-control"  >
							<input type="hidden" name="id" id="id" class="form-control"   >
						  </div>
						</div>						
						@else
						<div class="form-group row">
						  <label class="col-form-label col-lg-3">Customer Id<sup class="required">*</sup></label>
						  <div class="col-lg-9 error-msg"> 
							<input type="text" name="member_id" id="member_id_b" class="form-control"  >
							<input type="hidden" name="id" id="id" class="form-control"   >
						  </div>
						</div>						
						@endif
						<div id="show_mwmber_detail">
					
						</div>
					</div>
				  </div>
				  
				</div>
			</form>	
</div>      
    @section('script')
    @include($pathScrip)
    @stop
@stop
