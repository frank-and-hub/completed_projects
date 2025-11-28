@extends('templates.admin.master')

@section('content')
@section('css')
<style>
.hideTableData {
    display: none;
}
sup{
    color:red;
}
</style>
@endsection
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data' ,'id'=>'filter' ,'name'=>'filter'])}}
                        <div class="row">
                        @if(Auth::user()->branch_id < 1)
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])                            
                        @else
                            {{Form::hidden('branch_name',Auth::user()->branch_id,['id'=>'branch','class'=>'form-control'])}}
                        @endif
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Fixed Assets</label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="category" name="category">
                                        <option value="">Select categories</option>
                                        @foreach($head as $val)
                                        <option value="{{$val->head_id}}">{{$val->sub_head}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-lg-12 text-right">
                                    {{Form::hidden('export_de','',['id'=>'export_de','class'=>'form-control'])}}
                                    {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                                    <button type="button" class=" btn bg-dark legitRipple submit" onClick="searchForm1()">Submit</button>
                                    <button type="button" class="btn btn-gray legitRipple reset" id="reset_form" onClick="resetForm1()">Reset </button>
                                </div>
                            </div>
                        </div>
                    </div>
                {{Form::close()}}
            </div>
        </div>
    </div>

    <div class="col-md-12 table-section hideTableData">

        <div class="card">
            <div class="card-header bg-transparent header-elements-inline">
                <h3 class="mb-0 text-dark">Depreciation</h3>
                <div class="">
                    <button type="button" class="btn bg-dark legitRipple export_de ml-2" data-extension="0"
                        style="float: right;">Export xslx</button>
                </div>
            </div>
            <div class="">
                <table id="depreciation_list" class="table datatable-show-all">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Branch Name</th>
                            <th>Account Head</th>
                            <th>Sub-Account Head Name</th>
                            <th>Assets Purchase Date</th>
                            <th>Party Name</th>
                            <th>Mobile no.</th>
                            <th>Bill no.</th>
                            <th>Total Value of Asset</th>
                            <th>Current Assets value</th>
                            <th>Depreciation % </th>
                            <th>Bill copy</th>
                            <th>Action</th>

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

@include('templates.admin.asset_new.partials.script_list')

@stop