@extends('templates.admin.master')



@section('content')

<style>
.text-center {
	text-align:left !important;
}
#ledger_listing_filter{
	display:none !important;
}
sup{
    color:red;
}
</style>

    <div class="content">

        <div class="row">

            <div class="col-md-12">
                <div class="card">

                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>

                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                            @php
                                $AllCompany = ['id' => 'static_company', 'name' => 'Static Company'];
                                $dropDown = $AllCompany;
                                $filedTitle = 'Company';
                                $name = 'company_id';
                            @endphp
                            @include('templates.GlobalTempletes.both_company_filter',['all'=>true])
                            <!-- @include('templates.GlobalTempletes.both_company_filter') -->
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From  Date<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  readonly> 
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To  Date<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  readonly> 
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                
								<input type="hidden" name="type" id="type" value=""/>

								
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Ledger Type <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="ledger_type" name="ledger_type">
                                                <option value="">Select Type</option>
                                                    <option value="1" disabled>Members Ledgers</option>
                                                    <option value="2">Employee ledgers</option>
                                                    <option value="3" disabled>Associate Ledgers</option>
                                                    <option value="4">Rent parties ledger</option>
                                                    <option value="5">Vendors Ledgers</option>
                                                    <option value="6">Director Ledger</option>
                                                    <option value="7">Share holder ledger</option>
                                                    <option value="8" disabled>Customer Ledger</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

								<div class="col-md-4" id="employeeNameDiv" style="display:none">
									<div class="form-group row">
										<label class="col-form-label col-lg-12">Employee Name </label>
										<div class="col-lg-12 error-msg">
											<select class="form-control employee_id select2" id="employee_id" name="employee_id">
												<option value="">Select Employee</option>
												@foreach( $employee as $val )
													<option class="{{ $val->id }}-head" value="{{ $val->id }}"  >{{ $val->employee_name }}</option>
												@endforeach
											</select>
										</div>
										
									</div>
								</div>
								
								
								<div class="col-md-4" id="memberNameDiv" style="display:none">
									<div class="form-group row">
										<label class="col-form-label col-lg-12">Member ID</label>
										<div class="col-lg-12 error-msg">
											<select class="form-control member_id select2" id="member_id" name="member_id">
												<option value="">Select Member ID</option>
											</select>
										</div>
									</div>
								</div>
								
								<div class="col-md-4" id="memberIDDiv" style="display:none">
									<div class="form-group row">
										<label class="col-form-label col-lg-12">Member Name </label>
										<div class="col-lg-12 error-msg">
											<input type="text" name="memberName" id="memberName" class="form-control" readonly> 
										</div>
									</div>
									<input type="hidden" name="memberID" id="memberID" class="form-control">
								</div>
								
								
								<div class="col-md-4" id="associateDiv" style="display:none">
                                    <div class="form-group row">
										<label class="col-form-label col-lg-12">Associate Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control associate_id select2" id="associate_id" name="associate_id">
                                                <option value="">Select Associate</option>
                                                @foreach( $associate as $val )
                                                    <option class="{{ $val->id }}-head" value="{{ $val->id }}"  >{{ $val->first_name }} {{ $val->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
										
                                    </div>
                                </div>
								
								
								<div class="col-md-4" id="rentOwnerDiv" style="display:none">
                                    <div class="form-group row">
										<label class="col-form-label col-lg-12">Rent Owner</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control rent_owner_id select2" id="rent_owner_id" name="rent_owner_id">
                                                <option value="">Select Rent Owner</option>
                                                @foreach( $rent_owner as $val )
                                                    <option class="{{ $val->id }}-head" value="{{ $val->id }}"  >{{ $val->owner_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
										
                                    </div>
                                </div>
								
								
								<div class="col-md-4" id="directorDiv" style="display:none">
                                    <div class="form-group row">
										<label class="col-form-label col-lg-12">Director</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control director_id select2" id="director_id" name="director_id">
                                                <option value="">Select Director</option>
                                                @foreach( $director as $val )
                                                    <option class="{{ $val->id }}-head" value="{{ $val->id }}"  >{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
								
								<div class="col-md-4" id="shareHolderDiv" style="display:none">
                                    <div class="form-group row">
										<label class="col-form-label col-lg-12">Share Holder</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control share_holder_id select2" id="share_holder_id" name="share_holder_id">
                                                <option value="">Select Share Holder</option>
                                                @foreach( $share_holder as $val )
                                                    <option class="{{ $val->id }}-head" value="{{ $val->id }}"  >{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
								
								
								<div class="col-md-4" id="vendorDiv" style="display:none">
                                    <div class="form-group row">
										<label class="col-form-label col-lg-12">Vendors</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control vendor_id select2" id="vendor_id" name="vendor_id">
                                                <option value="">Select Vendors</option>
                                                @foreach( $vendors as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
								
								
								<div class="col-md-4" id="customerDiv" style="display:none">
                                    <div class="form-group row">
										<label class="col-form-label col-lg-12">Customer</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control customer_id select2" id="customer_id" name="customer_id">
                                                <option value="">Select Customer</option>
                                                @foreach( $customers as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
								
                                
                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm1()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm1()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>

            <div class="col-md-12" id="ledgerListingTableDiv" style="display:none">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Ledger Listing</h6>
                        <div class="">
                        <button type="button" class="btn bg-dark export-2 ml-2" data-extension="0"
                            style="float: right;">Export xslx</button>
                    </div>
                    </div>

                    <div class="">
                        <table id="ledger_listing_records" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th class="text-center">S/N</th> 
                                    <th class="text-center">Created Date</th>
                                    <th class="text-center">Company</th>
                                    <th class="text-center">Branch Name</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Payment Mode</th>
									<th class="text-center">Description</th>
                                    <th class="text-center">Debit</th>
                                    <th class="text-center">Credit</th>
                                    <th class="text-center">Balance</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>

                </div>
            </div>
			
			
			<div class="col-md-12" id="ledgerMembersListingTableDiv" style="display:none">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Ledger Listing</h6>
                        <div class="">
                        <!-- <button type="button" class="btn bg-dark export_legder_record ml-2" data-extension="0"
                            style="float: right;">Export xslx</button> -->
                    </div>
                    </div>

                    <div class="">
                        <table id="ledger_member_listing_records" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th class="text-center">S/N</th> 
                                    <th class="text-center">Created Date</th>
                                    <th class="text-center">Branch Name</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Payment Mode</th>
									<th class="text-center">Description</th>
									<th class="text-center">Payment Type</th>
                                    <th class="text-center">Balance</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>

                </div>
            </div>

        </div>

    </div>

@include('templates.admin.ledger_listing.script_list')

@stop