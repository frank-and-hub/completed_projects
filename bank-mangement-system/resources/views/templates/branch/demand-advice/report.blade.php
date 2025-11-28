@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Demand Advices</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h3 class="card-title font-weight-semibold">Search Filter</h3>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter_report" name="filter_report">
                    @csrf
                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" name="date_from" id="date" class="form-control date-from" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" name="date_to" id="date" class="form-control date-to" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('templates.GlobalTempletes.role_type',[
							'dropDown'=> $branchCompany[Auth::user()->branches->id],
							'name'=>'company_id',
							'apply_col_md'=>false,
                            'filedTitle' => 'Company'
							])


                            <!-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Branch </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="filter_branch" name="filter_branch">
                                                <option value=""  >----Select----</option> 
                                                @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    @if($branch_id == $key)
                                                        <option selected="" value="{{ $key }}"  >{{ $val }}</option> 
                                                    @endif
                                                @endforeach
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div> -->

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Advice Type </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="advice_type" name="advice_type">
                                                <option value=""  >----Select----</option> 
                                                {{-- <option value="0" data-type="expense-type">Expenses</option>  --}}
                                                <option value="1" data-type="maturity-type">Maturity</option> 
                                                <option value="2" data-type="prematurity-type">Prematurity</option>
                                                <option value="3" data-type="deathhelp-type">Death Help </option>
                                                <option value="4" data-type="deathclaim-type">Death Claim </option>
                                                                                                     <option value="5" data-type="emergency-maturity">Emergency Maturity</option>

                                            </select>
                                            
                                           </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 expense-type advice-type" style="display: none;">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Expense Type </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="expense_type" name="expense_type">
                                                <option value=""  >----Select----</option> 
                                                <option value="0"  >Fresh Expense</option> 
                                                <option value="1"  >TA advance and Imprest</option> 
                                                <option value="2"  >Advance Salary</option> 
                                                <option value="3"  >Advance Rent </option>
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Voucher Number</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" name="voucher_number" id="voucher_number" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="status" name="status">
                                                <option value=""  >----Select----</option> 
                                                <option value="1"  >Approve</option> 
                                                <option value="0"  >Pending</option> 
                                                <option value="2"  >Rejected</option>
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Account Number </label>
									<div class="col-lg-12 error-msg">
										<input type="text" class="form-control" name="account_number" id="account_number">
									</div>
								</div>
							</div> 
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="demand_advice_report_export" id="demand_advice_report_export" value="">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="searchReportForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetReportForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                            <h3 class="mb-0 text-dark">Demand Advices</h3>
                        </div>
                            <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            <!-- <button type="button" class="btn btn-primary legitRipple export" data-extension="1" style="float: right;">Export PDF</button> -->
                        </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table datatable-show-all" id="demand-advice-report-table">
                            <thead>
                                <tr>
                                    <th width="5%">S/N</th>
                                    <th width="10%">Company Name</th>
                                    <th width="10%">BR Name</th>
                                    <th width="10%">BR Code</th>
                                    <!-- <th width="10%">SO Name</th>
                                    <th width="10%">RO Name</th>
                                    <th width="10%">ZO Name</th> -->
                                    <th width="10%">Customer Id</th>
                                    <th width="10%">Member Id</th>
                                    <th width="10%">Member Name</th>
                                    <th width="10%">Associate Code</th>
                                    <th width="10%">Associate Name</th>
                                    <th width="10%">A/C Opening Date</th>  
                                    <th width="10%">Advice Type</th>
                                    <th width="10%">Expense Type</th>
                                    <th width="5%">Date</th>
                                    <th width="5%">Voucher No</th>
                                    <th width="5%">Payment Mode</th>
                                    <th width="5%">Is Loan</th>
                                    <!-- <th width="5%">Advice No</th>
                                    <th width="10%">Owner Name</th>
                                    <th width="10%">Particular</th>
                                    <th width="10%">Mobile</th> -->
                                    <th width="10%">Account Number</th>
                                    <th width="10%">SSB Account Number</th>
                                    <th width="10%">Bank Account Number</th>
                                    <th width="10%">IFSC Code</th>
                                    <th width="10%">Total Deposit Amount</th>
                                    <th width="10%">Payment Trf Amt.</th>
                                    <th width="10%">Total Amount With Interest</th>
                                    <th width="10%">TDS Amount</th>
                                    <th width="10%">RTGS Charge</th>
                                    <th width="10%">Print</th>
                                    <th width="10%">Status</th>
									<th width="10%">Requested Payment Mode</th>
                                    <th width="10%">Reason</th>
                                    <th width="10%">Action</th>
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
@include('templates.branch.demand-advice.partials.script')
@stop
