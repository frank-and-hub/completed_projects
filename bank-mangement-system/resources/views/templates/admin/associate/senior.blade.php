@extends('templates.admin.master')

@section('content')

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
                        <h6 class="card-title font-weight-semibold">Associate Senior Change</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.associate.senior_save') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
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

                                <div class="col-md-12 associate_changes" style="display: none;">
                                    <h6 class="card-title font-weight-semibold ">Senior Change To </h6>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">New Senior Code </label>
                                        <div class="col-lg-4 error-msg">
                                            <input type="text" name="new_associate_senior" id="new_associate_senior" class="form-control"  >
                                            <input type="hidden" name="new_senior_chk" id="new_senior_chk" class="form-control"  >
                                        </div>
                                        <div class="col-lg-4 error-msg">
                                            <span id="old_code"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12" id="new_associate_detail">
                                     
                                </div>
                                <div class="col-md-12 text-center">
                                     <button type="Submit" class=" btn bg-dark legitRipple" >Submit</button>
                                     <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.associate.partials.senior_js')
@stop