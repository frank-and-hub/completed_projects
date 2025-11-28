@extends('templates.admin.master')

@section('content')
@section('css')
    <style>
        .hideTableData {
            display: none;
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
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.both_company_filter',['all'=>true,'branchShow'=>true])
                            {{--
                            @include('templates.GlobalTempletes.role_type', [
                                'dropDown' => $dropDown,
                                'filedTitle' => $filedTitle,
                                'name' => $name,
                                'value' => '',
                                'multiselect' => 'false',
                                'apply_col_md' => true,
                            ])--}}
                            @if (Auth::user()->branch_id < 1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_name" name="branch_name">
                                                <option value="">Select Branch</option>                                                
                                                @foreach($branches as $key => $value)
                                                    <option value="{{$value->id}}">{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="branch_code" id="filter_branch_code"
                                                class="form-control" readonly="">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="branch_name" id="branch_name" value="{{ Auth::user()->branch_id }}">
                            @endif
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select Status</option>
                                            <option value="1">Approved</option>
                                            <option value="0">Pending</option>
                                            <option value="3">Deleted</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Transaction Type </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="transfer_type" name="transfer_type">
                                            <option value="">Select Status</option>
                                            <option value="0">Branch To Head Office</option>
                                            <option value="1">Bank To Bank</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="yes">
                                        <input type="hidden" name="report_export" id="report_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
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
                    <h6 class="card-title font-weight-semibold">Report List</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                        {{-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button> --}}
                    </div>
                </div>
                <div class="">
                    <table id="report_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Request Type</th>
                                <th>Company Name</th>
                                <th>Branch Name</th>
                                <th>Branch Code</th>
                                <!-- <th>Loan Daybook Amount</th>-->
                                <th>Cash in Hand Amount</th>
                                <th>Transfer Amount</th>
                                <th>Transfer Date</th>
                                <th>From Bank</th>
                                <th>To Bank</th>
                                <th>From Bank A/C No.</th>
                                <th>Transfer Mode</th>
                                <th>Transfer Cheque No/UTR No</th>
                                <th>RTGS/NEFT Charge </th>
                                <!-- <th>Receive Bank Name </th> -->
                                <th>Receive Bank A/c </th>
                                <th>Receive Cheque No/UTR No</th>
                                <th>Receive Amount</th>
                                <th>Request Date</th>
                                <th>Bank Slip</th>
                                <!--   <th> Approve/Reject Date</th> -->
                                <th>Remark </th>
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
@include('templates.admin.payment-management.fund-transfer.partials.script')
<script>
    $(document).ready(function() {
        $('#company_id').change(function(e) {
            $('#filter_branch_code').val('');
            e.preventDefault();
            var companyId = $(this).val();
            $.ajax({
                type: "POST",
                url: "{{ route('admin.fetchbranchbycompanyid') }}",
                data: {
                    'company_id': companyId,
                    'branch': 'true',
                    'bank': 'false',
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let myObj = JSON.parse(response);
                    console.log(myObj);
                    if (myObj.branch) {
                        var optionBranch =`<option value="">----Please Select----</option>`;
                        myObj.branch.forEach(e => {
                            if(e.companies_branch[0]){
                                optionBranch += `<option value="${e.id}" data-value="${e.branch_code}">${e.name}</option>`;
                            }
                        });
                        $('#branch_name').html(optionBranch);
                    }
                    if (myObj.bank) {
                        var optionBank = `<option value="">----Please Select----</option>`;
                        myObj.bank.forEach(e => {
                            optionBank +=`<option value="${e.id}">${e.bank_name}</option>`;
                        });
                        $('#bank').html(optionBank);
                    }
                }
            });
        });
    });
</script>

@stop
