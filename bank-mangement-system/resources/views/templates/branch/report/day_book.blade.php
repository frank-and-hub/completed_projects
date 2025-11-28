@extends('layouts/branch.dashboard')

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
     .loader {
              position: fixed;
              left: 0px;
              top: 0px;
              width: 100%;
              height: 100%;
              z-index: 9999;
              background: url('{{url('/')}}/asset/images/loader.gif') 50% 50% no-repeat rgb(249,249,249,0);
          }
</style>
<?php

$getBranchId=getUserBranchId(Auth::user()->id);
 $branch_id=$getBranchId->id;
?>
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
<div class="content-wrapper">
    <div class="row">

            <div class="col-lg-12">

                <div class="card bg-white">

                <div class="card-body page-title"> 
                        <h3 class="">Daybook Listing</h3> 
                </div>

                </div>

            </div>

        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-white">

                <div class="card-header header-elements-inline">

                    <h3 class="card-title font-weight-semibold">Search Filter</h3>

                </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                            
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                              
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                           
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                              
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($company))
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Company</label>
                                        <div class="col-lg-12 error-msg">
                                        <select class="form-control" name="company" id="company_id">
                                          <option value="">--Please Select Company -- </option>
                                         
                                            @foreach($company as $key=>$com)
                                                <option value="{{$key}}">{{$com}}</option>
                                           @endforeach
                                        </select>
                                        </div>
                                    </div>
                                </div>
                                @endif
                               <input type="hidden" class="form-control  " name="branch_id" id="branch_id"  value="{{$branch_id}}" >

                                
                                
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-secondary legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
           
        </div>
         <div class="data">
              
            </div>
    </div>
</div>
    @stop
@section('script')
 @include('templates.branch.report.partials.new_day_book_script')
@stop