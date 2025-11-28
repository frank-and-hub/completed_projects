@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Account Heads</h6>
                            <div class="header-elements">
                                <a class="font-weight-semibold" href="{{ route('branch.create') }}"><i class="icon-file-plus mr-2"></i>Create Branch</a>
                            </div>
                    </div>
                    <table class="table datatable-show-all" id="account-head">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Branch Name</th>
                                <th width="5%">Branch Code</th>
                                <th width="5%">account_number</th>
                                <th width="10%">Status</th>
                                <th class="text-center" width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.category.partials.script')
@stop
