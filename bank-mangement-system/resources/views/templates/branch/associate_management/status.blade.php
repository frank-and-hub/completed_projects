@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Associate Active/Inactive</h3>
                    
                </div>
                </div>
            </div>
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
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                                <h3 class="mb-0 text-dark">Associate Active/Inactive</h3>
                    </div>
                    <div class="card-body">
                        <form action="{!! route('branch.associate.status_save') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
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
                                </div>
                             <div class="row" id="hide_associate">
                                <div class="col-md-12 text-center">
                                     <button type="Submit" class=" btn btn-primary " >Update</button>
                                     <button type="button" class="btn btn-gray " id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
@include('templates.branch.associate_management.partials.status_js')
@stop