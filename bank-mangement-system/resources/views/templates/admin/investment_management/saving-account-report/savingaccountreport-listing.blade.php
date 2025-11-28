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
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
								
								{{--@include('templates.GlobalTempletes.new_role_type',['dropDown'=>$AllCompany,'filedTitle'=>'Company','name'=>'company_id','value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])--}}
								@include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Amount  <sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="amount" name="amount" required>
                                                <option value="" >Please select Amount Type</option>
                                                <option value="deposit">Deposit</option>
                                                <option value="withdrawal">Payment</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="investments_export" id="investments_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <div class="col-lg-12 table-section hideTableData">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Saving Listing</h3>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-dark legitRipple  export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="SavingAccountReport-listing" class="table table-flush">
                       <thead class="">
                            <tr>
                            <th>S/N</th>
                            <th>Created Date</th>
                            <th>Transaction By</th>
							<th>Company</th>
                            <th>BR Name</th>
                            <!-- <th>BR Code</th> -->
                            <!-- <th>SO Name</th>
                            <th>RO Name</th>  
                            <th>ZO Name</th> -->
                            <th>Customer ID</th>
                            <th>Member ID</th>
                            <th>Account Number</th>
                            <th>Member(Account Holder Name)</th> 
                            <!--<th>Plan</th>
                            <th>Tenure</th>-->
                            <th>Amount</th>
                            <th>Associate Code</th>
                            <th>Associate Name</th>
                            <th>Payment Mode</th>
                            
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
@include('templates.admin.investment_management.partials.savingaccountreport_script')
@stop