@extends('templates.admin.master')
@section('content')
<div class="content">
    <div class="row">
        <form id="company_form" action="#" method="post" enctype="multipart/form-data" name="company_form">
            @csrf
            <input type="hidden" name="export" id="export">
        </form>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn bg-dark CompanyExposrt export ml-2" data-extension="0"
                        style="float: right;display:none;">Export xslx</button>
                </div>
                <div class="">
                    <table id="companies_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S.N</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Short Name</th>
                                <th>Mobile No</th>
                                <th>FA Code From</th>
                                <th>FA Code To</th>
                                <th>TAN No</th>
                                <th>PAN No</th>
                                <th>CIN No</th>
                                <th>Created by</th>
                                <th>Created at</th>
                                <th>Status</th>                       
                                <th>Action </th>
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