@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }.required{
        color:red;
    }
    #start_date-error,#end_date-error
    {
        margin-top: 40px;
        position: absolute;
    }

</style>
@endsection
<div class="content">
    <div class="row">
        @include('templates.admin.cron.mbat_cron_filter')
        {{--
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Money Back Account Transfer Listing</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="mbat_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S. No.</th>
                                <th>Cron Name</th>
                                <th>Start Date/Time</th>
                                <th>End Date/Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>--}}
    </div>
</div>
@stop
@section('script')
@include('templates.admin.cron.partial.script')
@stop