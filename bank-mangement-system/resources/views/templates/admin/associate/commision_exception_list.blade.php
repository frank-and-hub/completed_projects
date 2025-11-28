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
			<div class="card">
			
				<div class="card-header header-elements-inline">
					<h3 class="card-title font-weight-semibold">Search Filter</h3>
				<a class="add-new " title=" Add Associate Commision Exception" href="{{route('admin.associate.exception')}}">
							<i style="font-size:24px" class="fa">&#xf067;</i>
						</a>
				</div>
				<div class="card-body">
					<form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
						@csrf
						<div class="row">
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Associate Name </label>
									<div class="col-lg-12  error-msg">
										<input type="text" name="name" id="name" class="form-control">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Associate ID </label>
									<div class="col-lg-12 error-msg">
										<input type="text" name="associate_code" id="associate_code" class="form-control">
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group row">
									<div class="col-lg-12 text-right">
										<input type="hidden" name="is_search" id="is_search" value="no">
										<input type="hidden" name="member_export" id="member_export" value="">
										<button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
										<button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-12 table-section hideTableData">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h3 class="mb-0 text-dark">Associate Exception List</h3>
					<div class="">
						
						<button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
					</div>
				</div>
				<div class="">
					<table class="table table-flush" style="width: 100%" id="exception_listing">
						<thead>
							<tr>
								<th>S/N</th>
								<th>Created Date</th>
								<th>Associate Name</th>
								<th>Associate ID</th>
								<th>Carder</th>
								<th>Created By</th>
								<th>User Name</th>
								<th>Reason</th>
								<th>Commission Status</th>
								<th>Fuel Status</th>
								<th>Action</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

@include('templates.admin.associate.partials.associate_exception_script_list')
@stop