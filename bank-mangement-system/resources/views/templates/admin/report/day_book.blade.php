@extends('templates.admin.master')

@section('content')
<style type="text/css">
    #expense{
            margin:4px, 4px;
                padding:4px;
               
                height: 37rem;
                overflow-x: hidden;
                overflow-y: auto;
                text-align:justify;
    }
</style>
<div class="content">
     <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="mx-2">Daybook Listing</h3> 
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                            
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  autocomplete="off"> 
                                              
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                           
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  autocomplete="off">
                                              
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">Select Branch</option>
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }} ({{$val->branch_code}})</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="data container-fluid">              
            </div>
        </div>

    </div>

 @include('templates.admin.report.partials.new_day_book_script')
@stop