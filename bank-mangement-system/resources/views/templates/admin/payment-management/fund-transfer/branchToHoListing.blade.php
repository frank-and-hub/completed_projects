@extends('templates.admin.master')

@section('content')
    <style>
        .table-section,
        .hide-table {
            display: none;
        }

        .show-table {
            display: block;
        }
    </style>

    <div class="loader" style="display: none;"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="header-elements">
                            @if ($BranchToHoTransfer == '1')
                                <a class="font-weight-semibold" href="{!! route('admin.fund-transfer.branchToHo.create') !!}"><i
                                        class="icon-file-plus mr-2"></i>Transfer Fund</a>
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

                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
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
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="status" id="status" class="form-control">
                                                <option value="">---- Select Status. ----</option>
                                                <option value="0">Pending</option>
                                                <option value="1">Approved</option>                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="report_export" id="report_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple investment_filters" onclick="searchBranchToHo()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onclick="resetFormHo()">Reset </button>
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
                    <table id="branch_to_ho_listing" class="table table-flush">
                        <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Branch</th>
                                <th>Created At</th>
                                <th>Transfer Amount</th>
                                <th>Bank</th>
                                <th>Bank A/C</th>
                                <th>Bank Slip</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{ url('/') }}/asset/js/sweetalert.min.js"></script>
    @include('templates.admin.payment-management.fund-transfer.partials.script')
    <script>
        $(document).ready(function () {
            $('#company_id').closest('.col-md-4').removeClass('col-md-4').addClass('col-md-4');
            $('#branch').closest('.col-md-4').removeClass('col-md-4').addClass('col-md-4');
            $('#branch').change(function(){
                return false;
            })
        });
    </script>
@stop
{{-- admin  --}}
