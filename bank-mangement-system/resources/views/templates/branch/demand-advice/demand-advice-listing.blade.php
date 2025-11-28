@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
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

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date From</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="date_from" id="date" class="form-control date-from">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date To</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="date_to" id="date" class="form-control date-to">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="filter_branch" name="filter_branch">
                                                    <option value=""  >----Select----</option> 
                                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Advice Type </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="advice_type" name="advice_type">
                                                    <option value=""  >----Select----</option> 
                                                    <option value="0"  >Expenses</option> 
                                                    <option value="1"  >Liability</option> 
                                                    <option value="2"  >Maturity</option> 
                                                    <option value="3"  >Prematurity</option> 
                                                    <option value="4"  >Death help/Death clam </option>
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="loan_recovery_export" id="loan_recovery_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Demand Advices</h3>
                        <!-- <div class="">
                        
                            <a type="button" class="btn btn-primary legitRipple export ml-2" href="{{url('admin/exportrentliabilities')}}" style="float: right;">Export xslx</a>
                            
                        </div> -->
                    </div>
                    <table class="table datatable-show-all" id="demand-advice-table">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Payment Type</th>
                                <th width="10%">Sub Payment Type</th>
                                <th width="10%">Branch</th>
                                <th width="10%">Status</th>
                                <th width="5%">Created at</th>
                                <th width="10%">Action</th>
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
@include('templates.branch.demand-advice.partials.script')
@stop
