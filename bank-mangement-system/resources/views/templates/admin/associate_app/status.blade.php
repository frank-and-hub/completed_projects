@extends('templates.admin.master')

@section('content') 
{{-- <style>
    .table-section, .hide-table{
        display: none;
    }
    .show-table{
        display: block;
    }
</style> --}}
    <div class="content">
        <div class="row">
             @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold"> Associate App Active/Inactive</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.associate.status_save_app') !!}" method="post" enctype="multipart/form-data" id="filter1" name="filter1">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                            <div class="row">
                                
                                
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Associate Code </label>
                                        <div class="col-lg-5 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" id="associate_detail">
                                     
                                </div>

                               
                                
                                <div class="col-md-12 text-center">
                                     <button type="Submit" class=" btn bg-dark legitRipple" >Update</button>
                                     <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div> 
                        </form>
                        <form action="{!! route('admin.associate.status_save_app') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <input type="hidden" name="export" id="export" value="" />
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 table-section">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">App Inactive Associate List</h6>
                        <div class=""> 
                            
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                            {{-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button> --}}
                        </div>
                    </div>
                    <div class="">
                        <table id="member_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Join Date</th>
                                    <th>BR Name</th>
                                    <th>Customber ID</th>   
                                    <th>Associate ID</th>
                                    <th>Associate Name</th>
                                    <th>Email ID</th>
                                    <th>Mobile No</th> 
                                    <th>Senior Code</th>  
                                    <th>Senior Name</th>                              
                                    <th>Status</th> 
                                    <th>App Status</th>   
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.associate_app.partials.status_js')
@stop