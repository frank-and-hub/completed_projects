@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }.required{
        color:red;
    }
</style>
@endsection
@php
$dropDown = $company;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
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
                            {{-- @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch']) --}}
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                         
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Party Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="party_name" id="party_name" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Status: </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select Status</option>
                                            <option value="1">Approved</option>
                                            <option value="0">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                        <input type="hidden" name="expense_export" id="filter_report">
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
                    <h6 class="card-title font-weight-semibold">Bill Expense Report</h6>
                    <div class="">

                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                        <!-- <a type="button" data-extension="1" class="btn bg-dark legitRipple export ml-2" style="float: right;">Export Pdf</a> -->

                    </div>
                </div>
                <div class="">
                    <table id="bill_expense_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S.N</th>
                                <th>Company</th>
                                <th>Branch Name</th>
                                <th>Branch Code</th>
                                <th>Created At</th>
                                <th>Bill Date</th>
                                <th>Bill Number</th>
                                <th>Party Name</th>
                                <th>Party BanK Name</th>
                                <th>Party BanK A/C No.</th>
                                <th>Party Bank Ifsc.</th>
                                <th>Payment Mode</th>
                                <th>Cheque No.</th>
                                <th>Utr No.</th>
                                <th>Neft Charge</th>
                                <th>Total Expense</th>
                                <!--<th>Account Heads</th>-->
                                <th>Bill Amount</th>
                                <th>Status</th>
                                <th>Action</th>
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
@include('templates.admin.expense.partial.script')
@stop