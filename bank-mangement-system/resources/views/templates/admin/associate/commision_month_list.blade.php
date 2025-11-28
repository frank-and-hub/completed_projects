@extends('templates.admin.master')

@section('content')

    @if ($errors->any())
        @foreach($errors->all() as $err)
        <div>{{ $error }}</div>
        @endforeach
    @endif

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Commission Month End Listing</h6>
                    <div class="">
                        <a title="Create Month End Commission" style="padding-right: 4px;" href="{{route('admin.associate.month')}}"> <i style="font-size: 24px;" class="fa">&#xf067;</i></a>
                    </div>
                </div>
                <div class="">
                    <table id="commision_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Created by</th>
                                <th class="sorting">User Name</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.associate.partials.commision_month_script_list') 
@stop
