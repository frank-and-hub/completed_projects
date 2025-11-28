@extends('templates.admin.master')

@section('content')

<div class="content">
    <div class="row">  
        <div class="col-lg-12">   
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Filter </h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="leaserFilter" name="leaserFilter">
                        @csrf 
                        <input type="hidden" name="leaser_export" id="leaser_export" value="">   
                       
                        @include('templates.GlobalTempletes.both_company_filter',[
                                   'branchShow' => 'no'
                                    ])   
                                    
                            <div class="col-md-12">
                            <div class="form-group row"> 
                                <div class="col-lg-12 text-right" >                                         
                                    <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>             
        </div>
    </div>
    <div class="row">  
        
        <div class="col-lg-12  table-section hideTableData">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Ledger List -- Monthly</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple leaser ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        
                    </div>
                </div>

               <div class="table-responsive">
                        <table   class="table table-flush" style="width: 100%"  id="transfer-listing" >
                       <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Start Date Time</th>
                                <th>End Date Time</th>
                                <th>Total Amt. </th>
                                <th>Total Transfer Amt. </th>
                                <th>Total Refund Amt. </th>
                                <th>Total Fuel Transfer Amt. </th> 
                                <th>Total Fuel Refund </th>
                                <th>Status</th>
                                <th>Created</th> 
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
    @include('templates.admin.associate.commission_monthly.leaser_script')
@stop
