@extends('templates.admin.master')
@section('content')
<style>
	.hideTableData {
		display: none;
	}
</style>
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
		<div class="col-md-12">
            <!-- Basic layout-->
            <div class="card my-4">
					@include('templates.admin.duties_taxes.payable_listing.filter',['head_type'=>$head_type])
			</div>
		</div>
		<!-- Table -->
		<div class="col-md-12 table-section hideTableData">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Payable Transaction List</h6>
					<div class="col-md-8">
						<button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
					</div>
				</div>
				<div class="">
					<table id="payable_listing" class="table datatable-show-all">
						<thead>
							<tr>
								<th >S/No</th>
								<th >Payable Head Type</th>
								<th >Company Name</th>
								<th >Head</th>
								<th >Payable Amount</th>
								<th >Payment Date</th>
								<th >Bank Name</th>
								<th >Bank Account</th>
								<th >Late Penalty</th>
								<th >Total Paid Amount</th>
								<th >Utr / Transaction Number</th>
								<th >Neft Charges</th>
								<th >Challan</th>
								<th >Remark</th>
							</tr>
						</thead>                    
					</table>
				</div>
			</div>
		</div>
    </div>
</div>
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.duties_taxes.payable_listing.partials.script')
@endsection