@extends('templates.admin.master')

@section('content')
@php
$dropDown = $company;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
<style>
    .table-section,
    .hide-table {
        display: none;
    }

    .show-table {
        display: block;
    }
</style>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="header-elements">
                          @if($BankToBankTransfer == "1")
                            <a class="font-weight-semibold" href="{!! route('admin.fund.transfer.bankTobank') !!}"><i class="icon-file-plus mr-2"></i>Transfer Fund</a>
                           @endif
                        </div>
                    </div>
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            <input type="hidden" name="_token" value="LlxGEeWlSBf2MDw7vPj3KmhJ2LS40HwOLxtfhLMa">
                            <div class="row">                              
                                @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true,'branchShow'=>true])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input name="start_date" type="text" readonly id="start_date" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input name="end_date" type="text" readonly id="end_date" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 ">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12"> Bank </label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="bank" id="bank" class="form-control">
                                                <option value="">---- Select Bank ----</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 ">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12"> Account No.</label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="bank_ac" id="bank_ac" class="form-control">
                                                <option value="">---- Select Account no. ----</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4 ">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status</label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="status" id="status" class="form-control">
                                                <option value="">---- Select Status. ----</option>
                                                <option value="0">Pending</option>
                                                <option value="1">Approved</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="report_export" id="report_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple investment_filters" onclick="searchBranchToBank()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onclick="resetFormBank()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row   table-section hide-table">
            <div class="col-md-12">
                <div class="card">
                    <div class="">
                        <table id="fund_transfer_listing" class="table datatable-show-all">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Company Name</th>
                                <th>From Bank</th>
                                <th>From Bank A/C</th>
                                <th>To Bank</th>
                                <th>To Bank A/C</th>
                                <th>Transfer Amount</th>
                                <th>Transfer Mode</th>
                                <th>Cheque No</th>
                                <th>UTR No</th>
                                <th>RTGS/NEFT Charges</th>
                                <th>Remark</th>
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
	<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
    @include('templates.admin.payment-management.fund-transfer.partials.script')
	@endsection

