@extends('templates.admin.master')
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
                    @include('templates.admin.tds_payable.filter')
                </div>
            </div>
            <div class="col-md-12 table-section hideTableData">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">TDS Payable List</h6>
                        <div class="col-md-8">
                            <button type="button" class="btn bg-dark legitRipple export_tds_payable ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>
                    <div class="">
                        <table id="tds_payable_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Created Date</th>
                                    <th>Company</th>
                                    <th>Branch</th>
                                    <th>TDS Head Name</th>
                                    <th>Vendor Name</th>
                                    <th>PAN Number</th>
                                    <th>DR</th>
                                    <th>CR</th>
                                    <th>Balance</th>   
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.tds_payable.partials.tds_payable_script')
@stop