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
                    <h6 class="card-title font-weight-semibold">Emi outstanding update</h6>
                </div> 
                <div class="card-body">
                    <form action="{{route('admin.outstanding.emi.update') }}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row ml-2  ">
                                    <label for="loan_type" class="col-form-label ">Account Number<sup
                                        class="required">*</sup></label>
                                    <div class="col-md-2 ml-2">
                                    <input type="text" name="account_number" id="account_number" class="form-control"
                                            placeholder="Enter Loan Account Number">
                                    </div>
                                    <label for="errDate" class="col-form-label ml-4">Error Date<sup
                                        class="required">*</sup></label>
                                    <div class="col-md-3 ml-2">
                                        <input type="text" name="errDate" id="errDate" class="form-control"
                                            placeholder="Please Select Error Date" readonly>
                                    </div>
                                    <label for="date" class="col-form-label ml-4 ">Date<sup
                                        class="required">*</sup></label>
                                    <div class="col-md-3 ml-2">
                                        <input type="text" name="date" id="date" class="form-control"
                                            placeholder="Please Select Date" readonly>
                                    </div>
                                    
                                </div>
                            </div>
                            </div>
                            <div class="col-md-12" id="investment_detail">
                            </div>
                            <input type="hidden" id="type" value="" name="type" />
							<input type="hidden" id="etype" value="" name="etype" />
							<input type="hidden" id="sanction_date" value="" name="sanction_date" />
							<input type="hidden" id="emi_due_date" value="" name="emi_due_date" />
							<input type="hidden" id="emi_amount" value="" name="emi_amount" />
							<input type="hidden" id="create_application_date" value="" name="create_application_date" class="create_application_date" />
                            <div class="col-md-12 text-center mt-3">
                            <button type="Submit" id="submitBtn" class=" btn bg-dark legitRipple btncollector" >Submit</button>
                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.loan.emi_update_cron.partial')
@stop