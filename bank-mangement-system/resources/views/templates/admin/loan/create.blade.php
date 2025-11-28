@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Create</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        <form action="{{route('admin.loan.store')}}" method="post" name="loanform" id="loanform">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="id" class="id" value='0'>
                            <input type="hidden" name="create_application_date" class="create_application_date" id="create_application_date">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Loan Type </label>
                                <div class="col-lg-10 error-msg">
                                    <div class="input-group">
                                        <select class="form-control" id="loan_type_plan" name="loan_type_plan"> 
                                            <option value=""  >----Select----</option> 
                                            <option value="L">Loan</option> 
                                            <option value="G">Group Loan</option> 
                                        </select>
                                        </div>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Loan Plan:</label>
                                <div class="col-lg-10">
                                    <select class="form-control loan_id" name="loan_id" id="loan_id">
                                        <option value="">--Please Select Plan -- </option>
                                         
                                    </select>
                                </div>
                            </div>
                            

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Emi Option:</label>
                                <div class="col-lg-10">
                                    <select class="form-control select" name="emi_option" id="emi_option">
                                    <option value="">--Please Select Emi Option  -- </option>
                                        <option value="1">Monthly
                                        </option>
                                        <option value="2">Weekly
                                        </option>
                                        <option value="3">Daily
                                        </option>
                                    </select>
                                </div>
                            </div>   


                             <div class="form-group row">
                                <label class="col-form-label col-lg-2">Tenure:</label>
                                <div class="col-lg-10">
                                    <input type="text" name="tenure" id="tenure" class="form-control" autocomplete="off" reqiured>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">ROI:</label>
                                <div class="col-lg-10">
                                    <input type="text" name="roi" id="roi" min="0" autocomplete="off" class="form-control">
                                </div>
                            </div> 
                            

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Effective From</label>
                                <div class="col-lg-10">
                                    <input type="text" name="effective_from" id="effective_from" autocomplete="off" class="form-control">
                                </div>
                            </div>
                            <!--<div class="form-group row">
                                <label class="col-form-label col-lg-2">Status:</label>
                                <div class="col-lg-10">
                                    <select class="form-control select" name="status">
                                        <option value="1">Active
                                        </option>
                                        <option value="0">Disable
                                        </option>
                                    </select>
                                </div>
                            </div>-->
                            <div class="text-right">
                                <button type="button" class="btn bg-dark" id="save_tenure">Submit<i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.loan.partials.settingscript')
@stop
