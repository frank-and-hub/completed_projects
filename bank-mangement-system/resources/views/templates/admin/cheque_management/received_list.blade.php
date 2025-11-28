@extends('templates.admin.master')
@php
$dropDown = $company;
$filedTitle  = 'Company';
$name = 'company_id';
@endphp
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
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off" > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  autocomplete="off">
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                
                              
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option selected value="">Select Status</option>
                                                <option value="1">Pending</option>
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

                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >


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
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Cheque/UTR  List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            
                        </div>
                    </div>
                    <div class="">
                        <table id="cheque_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>COMPANY NAME</th>
                                    {{-- <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> --}}
                                    <th>Cheque/UTR  Date </th>
                                    <th>Cheque/UTR  Number</th>
                                    <th>Cheque/UTR  Bank Name</th>
                                    <th>Cheque/UTR  Branch Name</th>
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
@include('templates.admin.cheque_management.partials.listing_received')
@stop