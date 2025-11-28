@extends('templates.admin.master')

@section('content')

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Transaction Search</h6>
                    </div>
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','enctype'=>'multipart/form-data','id'=>'filter','name'=>'filter'])}}
                            <div class="row">
                                <div class="col-md-4 error-msg">
                                    <label class="col-form-label col-lg-12">Select Type </label>
                                    <select class="form-control" id="select_type" name="plan_type">
                                        <option value="" disable>---Please Select Type ---</option>
                                        <option value="1">SSB Transcation</option>                                    
                                        <option value="2" >Investment Transaction</option>  
                                        <option value="3">Loan Transaction</option>    										
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Transaction  id</label>
										<div class="col-lg-9 error-msg">
                                            {{Form::text('transcation_id','',['id'=>'transcation_id','class'=>'form-control'])}}
										</div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
											<button type="submit" class="btn btn-primary">Submit</button>
											<button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
            <div class="col-md-12" id="ssbac_detail"></div>
			<div class="col-md-12" id="no-results" style="display:none"><ul><li id='hover'>No matches 
 found</li></ul></div>
        </div>
    </div>
@include('templates.admin.transcation_search.partials.transcationsearch_script')
@stop
