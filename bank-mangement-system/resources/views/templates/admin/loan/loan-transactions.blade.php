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
                    <form action="#" method="post" enctype="multipart/form-data" id="transaction-loan-filter" name="loan-filter">
                    @csrf
                        <div class="row">

                            @php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp

                           @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                           <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type <span class="required">*</span></label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control transaction_loan_type loan_typee" id="transaction_loan_type" name="transaction_loan_type">
                                                    <option value=""  >----Select Loan Type----</option> 
                                                    <option value="L">Loan</option>
                                                    <option value="G">Group Loan</option>
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan</label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control transaction_loan_plan" id="transaction_loan_plan" name="transaction_loan_plan">
                                                    <option value=""  >----Select Loan Plan----</option> 
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan</label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control transaction_loan_plan loan_plann" id="transaction_loan_plan" name="transaction_loan_plan">
                                                    <option value=""  >----Select Loan Plan----</option> 
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" readonly class="form-control from_date" name="date_from" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" readonly class="form-control to_date" name="date_to" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="application_number" id="application_number"  >
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
                                    <label class="col-form-label col-lg-12">Member ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="member_id" id="member_id" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Customer ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="customer_id" id="customer_id" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Payment Mode</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="payment_mode" name="payment_mode">
                                                <option value=""  >----Select----</option> 
                                                 <option value="0">Cash</option> 
                                                 <option value="1">cheque </option> 
                                                 <option value="2">dd</option> 
                                                 <option value="3">online_transaction </option> 
                                                 <option value="4">SSb</option>                                                  
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="loan_transaction_export" id="loan_transaction_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="loanTransactionSearchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="loanTransactionResetForm()" >Reset </button>
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
                    <h6 class="card-title font-weight-semibold">Loan Transaction Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-loan-transaction ml-2" data-extension="0" style="float: right;">Export Excel</button>
                        {{-- <button type="button" class="btn bg-dark legitRipple export-loan-transaction" data-extension="1">Export PDF</button> --}}
                    </div>
                </div>
                <div class="">
                    <table id="loan_transaction_table" class="table datatable-show-all">
                        <thead>
                            <tr>
								<th>S/N</th>
								<th>Created Date</th>
								<th>Company</th>
								<th>BR Name</th>
								<th>Customer Id</th>
								<!--<th>SO Name</th>
								<th>RO Name</th>
								<th>ZO Name</th>-->
								<th>Member Id</th>
								<th>Account No.</th>
								<th>Member(Account Holder Name)</th>
								<th>Loan Type </th>
								<th>Tenure</th>
								<th>Emi Amount </th>
								<th>Transaction Type</th>
								<th>Associate Code</th>
								<th>Associate Name</th>
								<th>Payment Mode</th>
								<!--<th>Action</th>-->
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
@include('templates.admin.loan.partials.script')
@endsection