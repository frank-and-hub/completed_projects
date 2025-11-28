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
                        <a type="button" href="{!! route('admin.duties_taxes.tds.setting.add_tds_settings') !!}" class="btn bg-dark legitRipple  " style="float: right;">Create TDS Setting</a>
                    </div>
                    <div class="">
                        <table id="tds_deposite_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                     <th>Date</th>
                                    <th>Start Date</th>
                                     <th>End Date</th>
                                    <th>TDS Percentage</th>
									<th>TDS Amount</th> 
									<th>Type</th> 
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
    @include('templates.admin.duties_taxes.tds.partials.script')
@stop