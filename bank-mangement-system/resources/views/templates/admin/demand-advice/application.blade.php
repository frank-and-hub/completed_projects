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
                        <form action="#" method="post" enctype="multipart/form-data" id="application_filter_report" name="application_filter_report">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                           <input type="hidden" class="form-control create_application_date" name="default_date" id="default_date"  autocomplete="off">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date From</label>
                                        <div class="col-lg-12 error-msg">
                                                <input type="text" name="date_from" id="fdate" class="form-control date-from " value="01/06/2020">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date To</label>
                                        <div class="col-lg-12 error-msg">
                                                <input type="text" name="date_to" id="tdate" class="form-control date-to create_application_date">
                                        </div>
                                    </div>
                                </div>
                           <!-- @if(Auth::user()->branch_id>0)
                              <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="filter_branch" name="filter_branch">
                                                    <option value=""  >----Select----</option>
                                                    @foreach( App\Models\Branch::where('id','=',Auth::user()->branch_id)->pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                              @else
                              <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="filter_branch" name="filter_branch">
                                                    <option value=""  >----Select----</option>
                                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                              @endif -->
                              @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$company,'filedTitle'=>"Company",'name'=>'company_id','value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>false,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch' ,'selectedCompany'=>1])

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Advice Type </label>
                                        <div class="col-lg-12 error-msg">
                                                <select class="form-control" id="advice_type" name="advice_type">
                                                    <option value=""  >----Select----</option>
                                                    {{-- <option value="0" data-type="expense-type" >Expenses</option> --}}
                                                    <option value="1" data-type="maturity-type" selected >Maturity</option>
                                                    <option value="2" data-type="prematurity-type" >Prematurity</option>
                                                    <option value="3" data-type="death-help-type" >Death Help </option>
                                                    <option value="4" data-type="death-claim-type" >Death Claim </option>
                                                </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Account Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control" name="account_number" id="account_number"  >
                                        </div>
                                    </div>
                                </div> 

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Requested Payment Mode </label>
                                        <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="requested_payment_mode" name="requested_payment_mode">
                                                    <option value=""  >----Please Select Requested Payment Mode----</option>
                                                    
                                                    <option value="BANK"   >BANK</option>
                                                    <option value="SSB" >SSB</option>
                                                 
                                                </select>
                                          
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-4 expense-type advice-type" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Expense Type </label>
                                        <div class="col-lg-12 error-msg">
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
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="demand_advice_report_export" id="demand_advice_report_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchApplicationForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetApplicationForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 " id="appdaTatable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Demand Advice Application</h6>
                        <div class="">
                            <form action="{{route('admin.demand.deletemultiple')}}" method="post" name="delete-demand-application" id="delete-demand-application">
                                @csrf
                                <input type="hidden" name="select_deleted_records" id="select_deleted_records">
                                <button type="submit" class="btn bg-dark delete-demand-application" style="float: right;margin-left: 564px;">Delete<i class="fas fa-trash-alt ml-2"></i></button>
                            </form>
                        </div>
                        <div class="">
                            <form action="{{route('admin.demandadvice.approve')}}" method="post" name="transfer-rent-payable" id="transfer-rent-payable">
                                @csrf
                                <input type="hidden" name="selected_records" id="selected_records">
                                <input type="hidden" name="pending_records" id="pending_records">
                                <button type="submit approve-application" class="btn bg-dark">Approve<i class="icon-paperplane ml-2"></i></button>
                            </form>
                        </div>
                    </div>
                    <table class="table datatable-show-all" id="demand-advice-application-table">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="5%"><input type="checkbox" name="select_all" id="select_all"></th>
                                <th width="10%">Company Name</th>
                                <th width="10%">BR Name</th>
                                <th width="10%">Requested Payment Mode</th>
                                <!-- <th width="10%">SO Code</th> -->
                                  <th width="10%">Account Number</th>
                                <th width="10%">Member Name</th>
								<th width="10%">Associate Name</th>
                                <th width="10%">Is Loan</th>
                                <th width="10%">Total Amount</th>
                                <th width="10%">TDS Amount</th>
                                <th width="10%">Interest Amount</th>
                                <!-- <th width="10%">Total Payable Amount</th> -->
                                <th width="10%">Final Amount</th>
                                <th width="5%">Demand Date</th>
                                <th width="5%">Created Date</th>
                                <th width="5%">Advice Type</th>
                                {{-- <th width="5%">Expense Type</th> --}}
                                <th width="5%">Voucher No</th>
                                <!-- <th width="5%">Advice No</th>
                                <th width="10%">Owner Name</th>
                                <th width="10%">Particular</th>
                                <th width="10%">Mobile</th> -->
                                <th width="10%">Passbook / Bond Photo</th>
                                <th width="10%">Status</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.modal.index')
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.demand-advice.partials.demand_maturity_script')
@endsection