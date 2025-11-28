@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Associate Commission Listing</h3>
                    
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
                    <form action="#" method="post" enctype="multipart/form-data" id="commissionFilter" name="commissionFilter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <!--<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
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
                                        <label class="col-form-label col-lg-12">Associate Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Name  </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_name" id="associate_name" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                            <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="commission_export" id="commission_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchCommissionForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCommissionForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Associates</h3>
                            </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class=" btn btn-primary legitRipple exportcommission ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class=" btn btn-primary legitRipple exportcommission" data-extension="1">Export PDF</button>
                        </div>
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                        <table id="associate-commission-listing" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th> 
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th>
                                <th>Associate Name</th>
                                <th>Associate Code</th>
                                <th>Associate Carder</th>                         
                                <th>Total Commission Amount</th>
                                <!--<th>Total Collection Amount</th>-->
                                <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
@include('templates.branch.associate_management.partials.listing_script')
@stop