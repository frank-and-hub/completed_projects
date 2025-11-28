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
                                @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true,'branchShow'=>true])
                                    <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Name<sup class="required">*</sup> </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="bank_id" name="bank_id">
                                                <option value="">Select Bank</option>          
                                                @foreach($bank as $key => $value )
                                                <option value="{{$value->id}}">{{$value->bank_name}}</option> 
                                                @endforeach()                                     
                                            </select>
                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                        </div>
                                    </div>
                                </div>                           
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
                                                 <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off" >
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="1">New</option>                                                
                                                <option value="3">Cleared</option>                                                
                                                <option value="0">Deleted</option> 
                                            </select>
                                        </div>
                                    </div>                                   
                                </div>   
                               
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Account Number<sup class="required">*</sup> </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="account_id" name="account_id">
                                                <option value="">Select Account Number</option>
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cheque Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="cheque_no" id="cheque_no" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>

                                                             
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="cheque_export" id="cheque_export" value="">
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
                        <h6 class="card-title font-weight-semibold">Cheque List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            
                        </div>
                    </div>
                    <div class="">
                        <table id="cheque_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Cheque Date</th>                                    
                                    <th>Bank Name</th>
                                    <th>Account No.</th>
                                    <th>Cheque No</th> 
                                    <th>Is Used</th>
                                    <th>Status</th>
                                    <th>Delete Date</th> 
                                    <th>Cancel Date</th>                                
                                    <th>Cancel Remark</th> 
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.cheque_management.partials.listing_script')
@stop