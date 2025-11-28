@extends('templates.admin.master')

@section('content')

    <div class="content">
        <div class="row">

       
       
            <div class="col-md-12"> 
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Loan Account Branch Transfer Log</h6>
                    </div> 
                    <div class="card-body">
                        <form action="{{route('admin.loan.branch_save')}}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                              
                            
                                <div class="col-md-12">
								
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Account Number </label>
                                        <div class="col-lg-5 error-msg">
                                            <input type="text" autocomplete="off" name="account_number" id="account_number" class="form-control"  >
                                        </div>
                                    </div>
									
									
                                </div>
				           
								
							
								
		
                                <div class="col-md-12" id="loan_branch_transferdetail">
                                     
                                </div>
                                
							
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.log.partials.loanlogbranchtransfer_js')
@stop