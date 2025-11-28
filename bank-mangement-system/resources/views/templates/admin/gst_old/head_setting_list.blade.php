
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
                    <!-- <div class="card-header">         
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <a type="button" data-extension="1" class="btn bg-dark legitRipple export ml-2" style="float: right;">Export Pdf</a>    
                        <a type="button" href="{!! route('admin.create.tds_deposit') !!}" class="btn bg-dark legitRipple export " style="float: right;">Create TDS Setting</a>
                    </div> -->
                    <div class="card-header d-flex justify-content-between">
                        <h1>Gst Head Setting</h1> 
                        @if( check_my_permission( Auth::user()->id,"274") == "1")       
                        <a type="button" href="{!! route('admin.gst.head.setting') !!}" ><i class="fa fa-plus m-1" aria-hidden="true"></i>Generate Gst Head Setting</a>
                        @endif
                    </div>
                    <div class="">
                        <table id="head_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                     <th>Head Name</th>
                                    <th>Gst Percentage</th>
                                    <th>Action</th>
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
    @include('templates.admin.gst.partials.scripts')
@stop