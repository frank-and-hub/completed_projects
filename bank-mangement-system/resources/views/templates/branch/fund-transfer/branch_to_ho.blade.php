@extends('layouts/branch.dashboard')

@section('content')

<style>
    .table-section, .hide-table{
        display: none;
    }
    .show-table{
        display: block;
    }
</style>

    <div class="loader" style="display: none;"></div>

    <div class="container-fluid mt--6">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <h3 class="">{{ $title }}</h3>

                            @if (in_array(
                                    'Branch to Bank Fund Transfer',
                                    auth()->user()->getPermissionNames()->toArray()))
                                <a href="{!! route('branch.fundtransfer.createbranchtoho') !!}" style="float:right" class="btn btn-secondary">Transfer
                                    Fund</a>
                            @endif

                            <!-- Validate error messages -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <!-- Validate error messages -->
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
                            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                               @csrf
                                <div class="row">

                                    @include('templates.GlobalTempletes.role_type',[
                                'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                'name'=>'company_id',
                                'apply_col_md'=>false,
                                'filedTitle' => 'Company'
                                ])

                                    <div class="col-md-12">
                                        <div class="form-group text-right">
                                            <div class="col-lg-12 page">
                                                <input type="hidden" name="is_search" id="is_search" value="no">
                                                <input type="hidden" name="report_export" id="report_export"
                                                    value="">
                                                <button type="button"
                                                    class=" btn btn-primary legitRipple investment_filters"
                                                    onclick="searchBranchToHo()">Submit</button>
                                                <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                                    onclick="resetFormHo()">Reset </button>
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
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body page-title">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="branch_to_ho_listing" class="table table-flush">
                                        <thead class="">
                                            <tr>
                                                <th>S/N</th>
                                                <th>Date</th>
                                                {{-- <th>Company Name</th> --}}
                                                <th>Branch (Name/Code)</th>
                                                {{-- <th>Branch Code</th> --}}
                                                <th>Created At</th>
                                                <th>Transfer Mode</th>
                                                <th>Transfer Amount</th>
                                                <th>Bank</th>
                                                <th>Bank A/C</th>
                                                <th>Bank Slip</th>
                                                <th>Status</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @include('templates.branch.fund-transfer.partials.script')
@stop
