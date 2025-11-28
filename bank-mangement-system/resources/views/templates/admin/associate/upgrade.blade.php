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
                        <h6 class="card-title font-weight-semibold">Upgrade(Promote) Associate</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.associate.upgrade_save') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
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

                                <div class="col-md-12">
                                    <h6 class="card-title font-weight-semibold ">Upgrade To </h6>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Carder </label>
                                        <div class="col-lg-5 error-msg">
                                            <select class="form-control select" name="upgrade_carder" id="upgrade_carder" >  
                                                <option value="">Select Carder</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 text-center">
                                     <button type="Submit" class=" btn bg-dark legitRipple" >Upgrade</button>
                                     <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.associate.partials.upgrage_js')
@stop