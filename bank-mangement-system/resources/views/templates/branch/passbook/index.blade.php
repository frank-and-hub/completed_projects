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
                <div class="card-body">
                    <div class="">
                        <h3 class="">Passbook</h3> 
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Filter</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12"> 
                            <form action="#" method="post" enctype="multipart/form-data" id="fillter" name="fillter">
                        @csrf
                                <div class="row">
                                    @include('templates.GlobalTempletes.role_type',[
                                        'dropDown'=> $branchCompany[Auth::user()->branches->id],
                                        'name'=>'company_id',
                                        'apply_col_md'=>false,
                                        'filedTitle' => 'Company'
                                        ])
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Start Date</label>
                                            <div class="col-lg-12 error-msg">
                                              <div class="input-group">
                                                <span class="input-group-prepend">
                                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                </span>
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                      <div class="form-group row">
                                          <label class="col-form-label col-lg-12">End Date</label>
                                            <div class="col-lg-12 error-msg">
                                              <div class="input-group">
                                                <span class="input-group-prepend">
                                                  <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                                </span>
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                               </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--<div class="col-lg-3">
                                      <div class="form-group row">
                                          <label class="col-form-label col-lg-12">Branch</label>
                                            <div class="col-lg-12 error-msg">

                                                <select class="form-control select" name="branch_id" id="branch_id" >  
                                                    <option value="">Select Branch</option>
                                                    @foreach( $branch as $val )
                                                        <option value="{{ $val->id }}" @if(getUserBranchId(Auth::user()->id)->id==$val->id)  selected @endif >{{ $val->name }}</option> 
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>-->
                                    
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                          <label class="col-form-label col-lg-12">Account Number</label>
                                            <div class="col-lg-12 error-msg">
                                               <input type="text" class="form-control  " name="member_id" id="member_id"  >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                          <label class="col-form-label col-lg-12">Member Name</label>
                                            <div class="col-lg-12 error-msg">
                                               <input type="text" class="form-control  " name="member_name" id="member_name"  >
                                               <input type="hidden" class="form-control  " name="is_search" id="is_search"  value="no">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 text-right">
                                        <div class=" " style="margin-top: 45px"> 
                                            <button type="button" class="btn btn-primary" onClick="searchForm()" >Submit<i class="icon-paperplane ml-2"></i></button>

                                            <button type="button" class="btn btn-secondary" id="reset_form" onClick="resetForm()" >Reset </button>
                                             
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card bg-white shadow table-section">
                    <div class="card-header bg-transparent">
                        <h3 class="mb-0 text-dark">Account Listing</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12"> 
                            <div class="table-responsive">
                                <table id="passbook" class="table table-flush">
                                    <thead class="">
                                      <tr>
                                        <th>S/N</th>
                                        <th>Account No</th>
                                        <th>Created Date</th>
                                        <th>Plan</th>
                                        <th>Name</th>
                                        <th>Customer ID</th> 
                                        <th>Member Id</th>  
                                        <th>Transaction</th>
                                        <th>Cover Page</th>    
                                        <th>Print Passbook</th>    
                                        <th>Certificate</th>                                         
                                        <th>Maturity Print</th>                                       
                                        <th>BR Name</th>                                                                             
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
@stop

@section('script')
@include('templates.branch.passbook.partials.listing_script')
@stop