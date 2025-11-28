@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
<style>
#form_body{
	display:none;
}
</style>
    <div class="row">
		{{Form::open(['url'=>route('admin.duties_taxes.tds.transfer'),'method'=>'POST','id'=>'tds_transfer_payable_from','name'=>'tds_transfer_payable_from','class'=>'col-md-12','enctype'=>'multipart/form-data'])}}
		<div class="card my-4">
			<div class="card-header header-elements-inline">						
				<div class="card-body">
					<div class="row ">
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Transfer Head Type <sup>*</sup></label>
								<div class="col-lg-8">
									<select class="form-control" id="head_type" name="head_type">
										<option value="">---- Please Select Payable Type----</option>
										@foreach($head_type as $k => $v)
										<option value="{{$k}}">{{ucwords($v)}}</option>
										@endforeach											
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
			<div class="card my-4" id="form_body">	
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Create Transfer Request</h6>
					<div class="col-md-8">
					</div>
				</div>
                    <div class="card-body" id="">						
						<input type="hidden" name="created_at" class="created_at" id="created_at">
						<div class="row ">
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">From Date <sup>*</sup></label>
									<div class="col-lg-8">
										{{Form::text('payable_start_date','',['id'=>'payable_start_date','class'=>'form-control','readonly'=>true])}}
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">To Date <sup>*</sup></label>
									<div class="col-lg-8">
										{{Form::text('payable_end_date','',['id'=>'payable_end_date','class'=>'form-control','readonly'=>true])}}
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Company <sup>*</sup></label>
									<div class="col-lg-8">
											<select class="form-control" id="company_id" name="company_id" required>
												<option value="">---- Please Select Company----</option>
												@foreach($company as $key => $val)
												<option value="{{ $key }}">{{ $val }}</option>
												@endforeach
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Head <sup>*</sup></label>
									<div class="col-lg-8">
											<select class="form-control" id="payable_head_id" name="payable_head_id">
												<option value="">---- Please Select ----</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Amount <sup>*</sup></label>
									<div class="col-lg-8">
										{{Form::text('payable_tds_amount','',['id'=>'payable_tds_amount','class'=>'form-control','readonly'=>true])}}
									</div>
								</div>
							</div>
							{{Form::hidden('payable_paid_amount','',['id'=>'payable_paid_amount','class'=>'form-control'])}}
							{{Form::hidden('tds_transfer_export','',['id'=>'tds_transfer_export','class'=>'form-control'])}}
						</div>	
						<div class="text-right">
							<input type="submit" name="submitform" value="Transfer" class="btn btn-primary submit-payable">
						</div>                        
                    </div>
                <!-- </div> -->
                <!-- /basic layout -->
            </div>
		{{Form::close()}}
		{{--
		<!-- filter -->
		<div class="col-md-12">
            <!-- Basic layout-->
            <div class="card my-4">
					@include('templates.admin.tds_payable.partials.filter')
			</div>
		</div>
		<!-- Table -->
		<div class="col-md-12 table-section hideTableData">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Payable Transaction List</h6>
					<div class="col-md-8">
						<button type="button" class="btn bg-dark legitRipple export_tds_transafer ml-2" data-extension="0" style="float: right;">Export xslx</button>
					</div>
				</div>
				<div class="">
					<table id="tds_transfer_list" class="table datatable-show-all">
						<thead>
							<tr>
								<th >S/N</th>
								<th >Transfer Date</th>
								<th >Date Range</th>
								<th >Head</th>
								<th >Head Amount</th>
								<th >Penalty Amount</th>
								<th >Payment Date</th>
								<th >Is Paid</th>
								<th >Company Name</th>
								<th >Challan Slip</th>
								<th >Action</th>
							</tr>
						</thead>                    
					</table>
				</div>
			</div>
		</div>
    </div>
	--}}
</div>
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.duties_taxes.transfer.partials.script')
@endsection