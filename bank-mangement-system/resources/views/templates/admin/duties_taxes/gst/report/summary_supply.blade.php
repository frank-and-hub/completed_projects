@extends('templates.admin.master')
@section('css')
<style>
thead th{
    
    vertical-align:top;
}
th {
    background:#00B0F0;
}
tr + tr th{
    background:#DAEEF3;
}
tr + tr, tbody {
    text-align:left
}
table, th, td {
    border:groove .5px;
    border-collapse:collapse;
    table-layout:fixed;
}
</style>
@endsection
@section('content')
<div class="content">
    
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">{{$title}}</h6>
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="export_summary_extension" id="export_summary_extension">

                    </form>    
                    <div class="">
                        <!-- <button type="button" class="btn bg-dark legitRipple export_summary ml-2" data-extension="0" style="float: right;">Export xslx</button> -->
                        <!-- <button type="button" class="btn bg-dark legitRipple export-group-loan" data-extension="1">Export PDF</button> -->
                    </div>
                </div>
                <div class="">
                    <table id="gstsummary" class="table datatable-show-all">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2">S/N</th>
                                <th  rowspan=2>Nature of Document </th>
                                <th colspan="2">Sr.No </th>                                
                                <th  rowspan=2>Total Number</th>
                                <th  rowspan=2>Cancelled</th>
                                <th  rowspan=2>Net Issued</th>                               
                            </tr>
                            <tr>
                                <th>From</th>
                                <th>To</th>
                            </tr>
                        </thead>                    
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.duties_taxes.gst.report.partials.summary_supply_script')
@stop