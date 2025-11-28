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

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Received Cheque Management</h3>                    
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
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
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off" readonly  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  autocomplete="off" readonly >
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">Select Branch</option>
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>-->

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="" selected >Select Status</option>
                                                <option value="1" >Pending</option>
                                                <option value="2">Apporve</option>
                                                <option value="3">Cleared</option> 
                                                <option value="0">Deleted</option>     
                                            </select>
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="cheque_export" id="cheque_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple "  onClick="searchForm()"  id="submit-button">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
            </div>
            </div>
        </div>
        <div class="row table-section">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Cheque/UTR  List</h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class=" btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            {{-- <button type="button" class="btn btn-primary legitRipple export" data-extension="1">Export PDF</button> --}}
                            </div>
                            </div>
                        </div>
                    
                    
                    <div class="table-responsive">
                        <table id="cheque_listing" class="table table-flush datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>                                    
                                    <th>Company Name</th>
                                    <!-- <th>BR Name</th> -->
                                    <!-- <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                                    <th>Cheque/UTR  Date </th>
                                    <th>Cheque/UTR  Number</th>
                                    <th>Cheque/UTR  Bank Name</th>                                    
                                    <th>Cheque/UTR  Account Holder Name</th>
                                    <th>Cheque/UTR  Account No.</th>                                    
                                    <th>Amount</th> 
                                    <th>Deposit  Date </th> 
                                    <th>Deposit Bank Name</th>
                                    <th>Deposit Account No.</th>
                                    <th>Used Date</th>
                                    <th>Clearing Date</th>
                                    <th>Status</th>
                                    <th>Remark</th> 
                                    <th class="text-center">Action</th>    
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
@include('templates.branch.cheque_management.partials.listing_received')
@stop