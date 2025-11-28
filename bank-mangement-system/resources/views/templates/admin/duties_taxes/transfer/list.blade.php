@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <style>
            .hideTableData{
                display:none
            }
        </style>
        <!-- filter -->
		<div class="col-md-12">
            <!-- Basic layout-->
            <div class="card my-4">
					@include('templates.admin.duties_taxes.transfer.filter')
			</div>
		</div>
		<!-- Table -->
		<div class="col-md-12 table-section hideTableData">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Transfer List</h6>
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
								<th >TDS Head</th>
								<th >Head Amount</th>
								<!-- <th >Penalty Amount</th> -->
								<!-- <th >Payment Date</th> -->
								<!-- <th >Is Paid</th> -->
								<th >Company Name</th>
								<!-- <th >Challan Slip</th> -->
								<!-- <th >Action</th> -->
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
@include('templates.admin.duties_taxes.transfer.partials.script')
@endsection