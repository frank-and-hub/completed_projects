@extends('templates.admin.master')

@section('content')

    <div class="content">
        <div class="row">

             
       
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate Branch Transfer</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{!! route('admin.associate.branch_save') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                              
                               
								
								 <input type="hidden" name="created_at" class="created_at">
								
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Associate Code </label>
                                        <div class="col-lg-5 error-msg">
                                            <input type="text" autocomplete="off" name="associate_code" id="associate_code" class="form-control"  >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" id="associate_branch_transferdetail">
                                     
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
@include('templates.admin.associate.partials.branchtransfer_js')
@stop