@extends('templates.admin.master')

@section('content')
<style>
a.add-new {
    margin-top: 10px;
    margin-right: 24px;
}
.right-btn-section {
    display: flex;
}
</style>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-white shadow">

					
					<div class="card-header bg-transparent header-elements-inline">
						<h3 class="mb-0 text-dark">Loan Charges</h3>
						<div class="right-btn-section">
                            @if(check_my_permission( Auth::user()->id,"302") == "1")
							<a class="add-new" title="Create Loan Charge" href="{{route('admin.loan.loansettings.loanchargescreate')}}">
									<i style="font-size:24px" class="fa">&#xf067;</i>
							</a>
                            @endif
							<button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
						</div>		
					</div>
					
                    <div class="table-responsive">
						
                        <table id="loanchargetable" class="table datatable-show-all table-flush" style="width: 100%" >
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Type</th>
                                    <th>Loan Type</th> 
                                    <th>Plan Name</th> 
                                    <th>Tenure</th> 
                                    <th>Minimum amount</th>
                                    <th>Maximum amount</th>
                                    <th>Charge</th>
                                    <th>Charge Type</th>
                                    <th>Status</th>
                                    <th>Effective Date From</th>
                                    <th>Effective Date To</th>
                                    <th>Created By</th>
                                    <th>Created By Username</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.loan.loansettings.partials.loanchargescript')
@stop