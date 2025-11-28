@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        <div class="col-lg-12">   
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Commission Transfer -Ledger Date ({{ date("d/m/Y", strtotime($detail->start_date)) }} - {{ date("d/m/Y", strtotime($detail->end_date)) }}) </h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="leaserDetailFilter" name="leaserDetailFilter">
                        @csrf 
                        <input type="hidden" name="leaserDetail_export" id="leaserDetail_export" value="">  
                        <input type="hidden" name="id" id="id" value="{{ $detail->id }}">           
                    </form>
                </div>
            </div>             
        </div>
    </div>
    <div class="row">  
        
        <div class="col-lg-12">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="mb-0 text-dark">Ledger Detail List</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple leaserDetail ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple leaserDetail" data-extension="1">Export PDF</button>
                    </div>
                </div>

               <div class="table-responsive">
                        <table   class="table table-flush" style="width: 100%"  id="transfer-listing-detail" >
                       <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Associate Carder</th>
                                <th>PAN No </th> 
                                <th>Total Amount </th>
                                <th>TDS Amount </th>
                                <th>Final Payable Amount</th>
                                <th>Total Collection </th>
                                <th>Fuel Amount </th>
                                <th>SSB Account No</th>
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
    @include('templates.admin.associate.partials.transfer_detail_script')
@stop
