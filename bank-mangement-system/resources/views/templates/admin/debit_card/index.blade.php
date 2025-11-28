@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="col-md-12">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Search Filter</h6>
				</div>
				<div class="card-body">
					<form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
						@csrf
						<input type="hidden" name="ssb_id" value="<?php if(isset($ssb_id)) echo $ssb_id; ?>" />
						<div class="row">
							<div class="col-md-3">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Card Number </label>
									<div class="col-lg-12 error-msg">
										<input type="text" name="card_no" id="card_no" class="form-control"  > 
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Member SSB A/C </label>
									<div class="col-lg-12 error-msg">
										<input type="text" name="ssb_ac" id="ssb_ac1" class="form-control"  > 
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Branch </label>
									<div class="col-lg-12 error-msg">
										<select class="form-control" id="branch_id" name="branch_id">
											<option value="">All Branch</option>
											@foreach( $branch as $k =>$val )
												<option value="{{ $val->id }}">
													{{$val->name}}
												</option> 
											@endforeach
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Status </label>
									<div class="col-lg-12 error-msg">
										<select class="form-control" id="status" name="status">
											<option value="">Select Status</option>
											<option value="0">Pending</option>
											<option value="1">Approved</option>
											<option value="2">Rejected</option>
											<option value="3">Blocked</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group row"> 
									<div class="col-lg-12 text-right" >
										<input type="hidden" name="is_search" id="is_search" value="no">
										<button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
										<button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
			<div class="card table-section hideTableData">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Debit Cards List</h6>
					<div class="">
					<?php if(check_my_permission( Auth::user()->id,"1001") == "1"){ ?>
						<a href="admin/debit-card/create"><button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Add Debit Card</button></a>
					<?php } ?>
					</div>
				</div>
				<div class="">
					<table id="debit_card_listing" class="table datatable-show-all">
						<thead>
							<tr>
								<th>S/N</th> 
								<th>Issue Date</th>
								<th>Card No.</th>
								<th>BR Name</th>
								<th>BR Code</th>
								<th>Card Type</th>
								<th>Valid From</th>
								<th>Valid To</th>
								<th>Member SSB Account</th>
								<th>Memeber Name</th>
								<th>Approve/Reject/Block Date</th>
								<th>Reference No.</th>
								<th>Employee Code</th>
								<th>Employee Name</th>
								<th>Status</th>
								<th class="text-center">Action</th>
							</tr>
						</thead>                    
					</table>
				</div>
			</div>
		</div>
	</div>
</div>



<div class="modal fade" id="reason-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
	<div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 460px;">
		<div class="modal-content">
			<div class="modal-body p-0">
				<div class="card bg-white border-0 mb-0">
					<div class="card-header bg-transparent pb-2�">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<div class="text-dark text-center mt-2 mb-3">
							<h4>Reason For <span id="reason_text"></span></h4>
						</div>
					</div>
					<div class="card-body px-lg-5 py-lg-2">
						<form action="javascript:void(0)" method="post" id="comment-form" name="comment-form">
							@csrf
							<input type="hidden" name="debit_card_id" id="debit_card_id">
							<input type="hidden" name="type" id="type">
							<div class="form-group row">
								<div class="col-lg-12">
									<textarea name="reason" name="reason" id="reason" rows="4" class="form-control" placeholder="Comments"></textarea>
								</div>
							</div>  
						
							<div class="text-right">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								<input type="submit" name="submitform" value="Submit" class="btn btn-primary action_reject_block">
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>	
@include('templates.admin.debit_card.script_list')
@stop