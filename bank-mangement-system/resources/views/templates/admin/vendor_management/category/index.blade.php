@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <!--<div class="col-md-12">
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
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select Branch</option>
                                                <option value="1">Active</option>  
                                                <option value="0">Inactive</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row"> 
                                        <label class="col-form-label col-lg-12">  </label>
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">-->
                                            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                                                @csrf
                                                    <input type="hidden" name="vendor_category" id="vendor_category" value="">
                                            </form>
                                            <!--<button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>-->
              
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Vendor Category Lists</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button> -->
                        </div>
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                    </div>
                    <div class="">
                        <table id="vendor_category_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Name</th>
                                    <th>Status</th>                                     
                                    <th>Created</th>
                                    <th>Updated</th>
									<?php if(check_my_permission( Auth::user()->id,"193") == "1"  || check_my_permission( Auth::user()->id,"194") == "1" ) {?>
                                    <th class="text-center">Action</th> 
									<?php } ?>
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.vendor_management.category.partials.script_list')
@stop