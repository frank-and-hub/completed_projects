<div class="col-md-12">
	<div class="card">
		<div class="card-header header-elements-inline">
			<h6 class="card-title font-weight-semibold">Search Filter</h6>
		</div>
		<div class="card-body">
			<form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">						
				<input type="hidden" name="globalDate" class="create_application_date" id="globalDate">
				<input type="hidden" name="created_at" class="created_at" id="created_at">
				@csrf
				<div class="row">
					@if(Auth::user()->branch_id < 1)
						<div class="col-md-4">
							<div class="form-group row">
								<label class="col-form-label col-lg-12">Branch </label>
								<div class="col-lg-12 error-msg">
									<select class="form-control" id="branch_id" name="branch_id">
										<option value="">Select Branch</option>
										@foreach( $branch as $val )
											<option value="{{ $val->id }}"  >{{ $val->name }}  ({{$val->branch_code}})</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					@else
					  <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">
					@endif
					<div class="col-md-4">
						<div class="form-group row">
							<label class="col-form-label col-lg-12">Loan Plans Type</label>
							<div class="col-lg-12 error-msg">
								<select class="form-control" id="loan_type_id" name="loan_type_id">
									<option value="">Select Loan Plan Type</option>
									@foreach( $loan_plan_type as $val )
										<option value="{{ $val->id }}"  >{{ $val->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div> 
					<!-- 
					<div class="col-md-4 d-none">
						<div class="form-group row">
							<label class="col-form-label col-lg-12">Plans </label>
							<div class="col-lg-12 error-msg">
								<select class="form-control" id="plan_id" name="plan_id">
									<option value="">Select Plans</option>
									@foreach( $plans as $val )
										<option value="{{ $val->id }}"  >{{ $val->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					-->
					<div class="col-md-12">
						<div class="form-group row">
							<div class="col-lg-12 text-right" >
								<input type="hidden" name="is_search" id="is_search" value="no">
								<input type="hidden" name="export" id="export" value="">
								<!-- <input type="hidden"  name="created_at" class="create_application_date" id="created_at" value="{{$startDatee}}"> -->
								<button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
								<button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>