@extends('templates.admin.master')

@section('content')
<style>
span.select2.select2-container.select2-container--default.select2-container--below {
    border: 1px solid #ddd;
    border-radius: 0;
    box-shadow: 0 0 0 0 transparent;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    border-top-color: transparent !important;
    border-left-color: transparent;
    border-right-color: transparent;
}
span.select2.select2-container.select2-container--default{
	border: 1px solid #ddd;
    border-radius: 0;
    box-shadow: 0 0 0 0 transparent;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    border-top-color: transparent !important;
    border-left-color: transparent;
    border-right-color: transparent;
}
.notfoundmsg{
	text-align: center;
    display: inline-block;
}
</style>
    <div class="content">
        <div class="row">
			<div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Company Filter </h6>
                    </div>
					<div class="card-body">
						<form action="#" method="post" enctype="multipart/form-data" id="filter_headlist" name="filter_headlist">
							@csrf                    
							<div class="row">
								<div class="col-md-4">
									<div class="form-group row">
										<label class="col-form-label col-lg-12">Select Company </label>
										<div class="col-lg-12 error-msg">
											
											<div class="input-group">
												<select class="form-control" id="company_list" name="company_list">
													<option value="">----Select Company----</option>
													@foreach($company as $key => $val)
													
													<option value="{{$key}}">{{$val}}</option>
													@endforeach

												</select>
											</div>
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="form-group row">
										<div class="col-lg-12 text-right">
											<input type="hidden" name="is_search" id="is_search" value="yes">
											
											<button type="button" class="btn bg-dark legitRipple" id="searchheadlist">Submit</button>
											<button type="button" class="btn btn-gray legitRipple" id="resetform">Reset </button>
										</div>
									</div>
								</div>
							
							</div>
						</form>
					</div>
                    
                </div>
            </div>
				
            <div class="col-md-12" id="account-head-sec">

            </div>
        </div>
    </div>

@stop
@section('script')
    @include('templates.admin.account_head_report.head.partials.script')
@stop