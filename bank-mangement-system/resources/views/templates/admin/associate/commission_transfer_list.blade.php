@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        <div class="col-lg-12">   
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Commission Transfer </h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="leaserFilter" name="leaserFilter">
                        @csrf 
                        <input type="hidden" name="leaser_export" id="leaser_export" value="">            
                    </form>
                </div>
            </div>             
        </div>
    </div>
    <div class="row">  
        
        <div class="col-lg-12">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Ledger List</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple leaser ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple leaser" data-extension="1">Export PDF</button>
                    </div>
                </div>

               <div class="table-responsive">
                        <table   class="table table-flush" style="width: 100%"  id="transfer-listing" >
                       <thead class="">
                            <tr>
                                <th>S/N</th>
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
    @include('templates.admin.associate.partials.leaser_script')
@stop
