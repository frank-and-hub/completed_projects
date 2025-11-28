@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline" id="company_register">
                    <div class="card-body">
                        <div class="company_register">
                            @include('templates.admin.company.associate_setting.form')
                        </div>
                    </div>
                </div>
            </div>
            <div class="card CompanyAssociatesListing_table" id="">
                <div class="">
                    <table id="CompanyAssociatesListing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company ID</th>
                                <th>Company Name</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Status</th>
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
@include('templates.admin.company.partials.index_script')
@stop