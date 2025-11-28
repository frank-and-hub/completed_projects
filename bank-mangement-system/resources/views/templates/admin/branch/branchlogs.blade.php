@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        
                            <div class="header-elements"></div>
                    </div>
                    <table class="table datatable-show-all" id="branchLogs">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="5%">Date</th>
                                <th width="20%">Branch Name</th>
                                <th width="10%">Branch Code</th>
                                <th width="10%">IP Address</th>
                                <th width="50%">Message</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.branch.partials.branch-script')
@stop
