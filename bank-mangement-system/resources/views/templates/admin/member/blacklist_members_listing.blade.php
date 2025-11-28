@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
			<!--
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="name" id="name" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="member_id" id="member_id" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate code  </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                        </div>
                                    </div>
                                </div> 
								<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12  error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
												@if(is_null(Auth::user()->branch_ids))
													<option value=""  >All</option>
													@foreach( $branch as $k =>$val )
														<option value="{{ $val->id }}"   @if($k==0) selected @endif>{{ $val->name }}</option> 
													@endforeach
												@else
													<?php $an_array = explode(",", Auth::user()->branch_ids); ?>
													<option value=""  >All</option>
													@foreach( $branch as $k =>$val )
														 @if (in_array($val->id, $an_array))
															<option value="{{ $val->id }}"   @if($k==0) selected @endif>{{ $val->name }}</option> 
														@endif
													@endforeach
												@endif	
                                            </select>
                                        </div>
                                    </div>
                                </div>
                               
                               
                               
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="member_export" id="member_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> -->
			<form action="#" method="post" enctype="multipart/form-data" id="member_filter" name="member_filter">
				@csrf
				<input type="hidden" name="is_search" id="is_search" value="yes">
                <input type="hidden" name="member_export" id="member_export" value="">
				<input type="hidden" name="urls" id="urls" value="{{URL::to('/')}}">
			</form>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Blacklist Members List For Loan</h6>
                        <div class="">
                            @if(check_my_permission(Auth::user()->id,'180') == 1)
                            <button type="button" class="btn bg-dark legitRipple export_blacklist_member ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            @endif
                             @if(check_my_permission(Auth::user()->id,'179 ') == 1)
                            <button type="button" class="btn bg-dark legitRipple export_blacklist_member ml-2" data-extension="1" style="float: right;">Export PDF</button>
                            @endif
                             @if(check_my_permission(Auth::user()->id,'178') == 1)
							<a href="{{URL::to('/')}}/admin/print-blacklist-member-on-loan" target="_blank"><button type="button" class="btn bg-dark legitRipple ml-2" data-extension="2" style="float: right;">Print</button></a>
                            @endif
                             @if(check_my_permission(Auth::user()->id,'177') == 1)
							<a href="{{URL::to('/')}}/admin/add-blacklist-member-on-loan"><button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="2" style="float: right;">Add Blacklist Member For Loan</button></a>
                            @endif
                        </div>
                    </div>
                    <div class="">
                        <table id="member_blacklist_on_loan_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Join Date</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Member ID</th>
                                    <th>Name</th> 
									<th>Member DOB</th>
                                    <th>Account No</th>
                                    <th>Mobile No</th> 
                                    <th>Associate Code</th>  
                                    <th>Associate Name</th>
									<th>Nominee Name</th>
									<th>Relation</th>
									<th>Nominee Age</th>										
                                    <th>Status</th> 
									<th>Member Status For Loan</th>
                                    <th>Is Uploaded</th>  

                                    <th>Address</th>
                                    <th>State</th>
                                    <th>District</th> 
                                    <th>City</th>
                                    <th>Village Name</th> 
                                    <th>Pin Code</th>  
                                    <th>First ID Proof</th>                               
                                    <th>Second ID Proof</th> 

                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.member.partials.blacklist_listing_script')
@stop