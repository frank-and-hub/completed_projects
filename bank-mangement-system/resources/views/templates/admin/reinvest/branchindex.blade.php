@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Branch</h6>
                            <div class="header-elements">
                            </div>
                    </div>
                    <table class="table datatable-show-all" id="reinvest-member">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Branch Name</th>
                                <th width="5%">Branch Code</th>
                                <th width="5%">State</th>
                                <th width="5%">City</th>
                                <th width="10%">Phone Number</th>
                                <th width="30%">Address</th>
                                <th width="10%">Created Date</th>
                                <th width="10%">Status</th>
                                <th class="text-center" width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.reinvest.partials.branch-script')
@stop
