@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
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
                                                <input type="text" name="date_from" id="date" class="form-control date-from">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date To</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="">
                                                <input type="text" name="date_to" id="date_to" class="form-control date-to">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               @if(Auth::user()->branch_id<1)
                               @php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp

                           @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                               @else
                                  <input type="hidden" name="filter_branch" id="filter_branch" value="{{Auth::user()->branch_id}}">                         
                               @endif
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
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Account Number </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" class="form-control  " name="account_number" id="account_number"  >

                                        </div>

                                    </div>

                                </div> 
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="demand_advice_report_export" id="demand_advice_report_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchReportForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetReportForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Demand Advices</h6>
                        <div class="">
                            {{-- <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button> --}}
                            <button type="button" class="btn bg-dark legitRipple export_new ml-2" data-extension="0" style="float: right;">New Export xslx</button>
                            <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button> -->
                        </div>
                    </div>
                    <table class="table datatable-show-all" id="demand-advice-report-table">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Company Name</th>
                                <th width="10%">BR Name</th>
                                <!-- <th width="10%">BR Code</th>
                                <th width="10%">SO Name</th>
                                <th width="10%">RO Name</th>
                                <th width="10%">ZO Name</th> -->
                                <th width="10%">Customer Id</th>
                                <th width="10%">Member Id</th>
                                <th width="10%">Member Name</th>
                                <th width="10%">Nominee Name</th>
                                <th width="10%">Associate Code</th>
                                <th width="10%">Associate Name</th>
                                <th width="10%">A/C Opening Date</th>  
                                <th width="10%">Advice Type</th>
                                <th width="10%">Expense Type</th>
                                <th width="5%">Date</th>
                                <th width="5%">Voucher No</th>
                                <th width="5%">Payment Mode</th>
                                <!-- <th width="5%">Advice No</th>
                                <th width="10%">Owner Name</th>
                                <th width="10%">Particular</th>
                                <th width="10%">Mobile</th> -->
                                <th width="10%">Is Loan</th>
                                <th width="10%">Total Deposit Amount</th>
                                <th width="10%">Payment Trf. Amt.</th>
                                <th width="10%">TDS Amount</th>
                                <th width="10%">Interest Amount</th>
                                <th width="10%">Total Amount With Interest</th>
                                <th width="10%">RTGS Charge</th>
                                <th width="10%">Account Number</th>
                                <th width="10%">SSB Account Number</th>                                
                                <th width="10%">Bank Account Number</th>                                
                                <th width="10%">IFSC Code</th>
                                <th width="10%">Print</th>
								<th width="10%">Requested Payment Mode</th>
                                <th width="10%">Status</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.demand-advice.partials.script')
@endsection
