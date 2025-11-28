@extends('templates.admin.master')

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
		  <form action="{!! route('admin.associate.save') !!}" method="post" enctype="multipart/form-data" id="associate_register" name="associate_register">
		  @csrf
			  <input type="hidden" name="created_at" class="created_at">
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
			</form>	
</div>
@include('templates.admin.member.partials.blacklist_listing_script')
@stop