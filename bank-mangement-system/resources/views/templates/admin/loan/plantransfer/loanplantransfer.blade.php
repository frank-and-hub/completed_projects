@extends('templates.admin.master')

@section('content')


    <div class="content">
        <div class="row">

       
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Loan Plan Transfer</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{{route('admin.loan.plantransfer.plan_save')}}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                              
                            
                                <div class="col-md-12">
								
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Account No </label>
                                        <div class="col-lg-5 error-msg">
                                            <input type="text" autocomplete="off" name="account_number" id="account_number" class="form-control"  >
                                        </div>
                                    </div>
									
									
                                </div>
				           
								<!--<div class="col-md-12">
								
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Loan </label>
                                        <div class="col-lg-5 error-msg">
                                            <select class="form-control" id="loan_id" name="loan_id">
											  <option value=""  >Select Loan</option>
												 <option value="loan" >loan  </option  >
											<option value="Group loan" >Group loan  </option  >
										   </select>
                                        </div>
                                    </div>
									
									
                                </div>--> 
							
								
		
                            <div class="col-md-12" id="loan_plan_transferdetail">
                                    
                            </div>
                                
							 <input type="hidden" name="created_at" class="created_at">
                            
                                <div class="col-md-12 text-center">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group  text-center">
                                                <button type="Submit" class=" btn bg-dark legitRipple" >Submit</button>
                                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                            </div>
                                        </div>
                                    </div>
									
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.loan.plantransfer.partials.loanplantransfer_js')
@stop