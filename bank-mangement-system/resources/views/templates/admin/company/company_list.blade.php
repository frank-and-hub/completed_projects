@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
               <form id="filter" action="#" method="post" enctype="multipart/form-data" name="filter">
                @csrf
            <input type="hidden" name="export" id="export">
        </form>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <a type="button" data-extension="1" class="btn bg-dark legitRipple export ml-2" style="float: right; display: none;">Export Pdf</a>    
                        <a type="button" href="{!! route('admin.create.tds_deposit') !!}" class="btn bg-dark legitRipple ml-2 " style="float: right;">Create TDS Setting</a>
                    </div>
                    <div class="">
                        <table id="tds_deposite_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Date</th>
									<th>Effective From Date</th>
									<th>Effective To Date</th>
									<th>Income Type</th>
									<th>Section</th>
									<th>Financial Year</th>
									<th>Beneficiary Type</th>
									<th>Min limit</th>
									<th>Max limit</th>
									<th>Min limit Multiple Invoice</th>
									<th>Max limit Multiple Invoice</th>
									<th>Tds Pan Percentage</th>
									<th>Tds No Pan Percentage</th>
									<th>15G Limit</th>
									<th>15H Limit</th>
									<th>15H Super Senior Limit</th>
									<th>Status</th>
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
    @include('templates.admin.company.partials.script')
@stop