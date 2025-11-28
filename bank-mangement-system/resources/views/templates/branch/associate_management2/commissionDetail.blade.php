@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Associate Commission Details</h3>
                    
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
                    
                    <form action="#" method="post" enctype="multipart/form-data" id="commissionFilterDetail" name="commissionFilterDetail">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Start  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">End  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Investment Plans </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="plan_id" name="plan_id">
                                                <option value="">Select Plan</option>
                                                @foreach( App\Models\Plans::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 text-right">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="commission_export" id="commission_export" value="">
                                            <input type="hidden" name="id" id="id" value="{{$member->id}}">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchCommissionDetailForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCommissionDetailForm()" >Reset </button>
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
                                <h3 class="mb-0 text-dark">Associates - {{ $member->associate_no}}({{$member->first_name}} {{$member->last_name}}) - {{ getCarderName($member->current_carder_id) }}</h3>
                            </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class=" btn btn-primary legitRipple exportcommissionDetail ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class=" btn btn-primary legitRipple exportcommissionDetail" data-extension="1">Export PDF</button>
                        </div>
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                        <table id="associate-commission-detail" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Investment Account</th>
                                <th>Plan Name</th>
                                <th>Total Amount</th>
                                <th>Commission Amount</th>                         
                                <th>Percentage</th>
                                <th>Carder Name</th>
                                <th>EMI No</th>
                                <th>Commission Type</th> 
                                <th>Associate Exists</th>
                                <th>Payment Type</th>
                                <th>Payment Distribute</th> 
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