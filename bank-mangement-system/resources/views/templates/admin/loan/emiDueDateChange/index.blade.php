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
                    <h6 class="card-title font-weight-semibold">Emi Due Date & Emi Amount Correction</h6>
                </div> 
                <div class="card-body">
                    <form action="{!! route('admin.loan.emiDueDate.correction') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-3">Account Number </label>
                                    <div class="col-lg-5 error-msg">
                                        <input type="text" name="account_no" id="account_no" class="form-control"  >
                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" id="investment_detail">
                            </div>
                            <div class="col-md-12 associate_changes" style="display: none;">
                                <h6 class="card-title font-weight-semibold ">Change Emi Due Date / Emi Amount </h6>
                                <div class="form-group row">
									<div class="col-lg-12 row">
										<label class="col-form-label col-lg-3" for="change_type">Change Type  <span class="red">*</span></label>
										<div class="col-lg-3 error-msg">
											<select name="change_type" id="change_type" class="form-control" >
                                                <option value="">-- Please select type --</option>
                                                <option value="1">Emi Due Date </option>
                                                <option value="2">Emi Amount</option>
                                            </select>
										</div>
										<label class="col-form-label col-lg-3 emiDueDate" for="emiDueDate">Emi Due Date <span class="red">*</span></label>
										<div class="col-lg-3 error-msg emiDueDate">
											<input type="text"  name="emiDueDate" id="emiDueDate" value="" class="form-control removeSpaceInput emiDueDate" readonly />
										</div>
                                        <label class="col-form-label col-lg-3 emi" for="emi">Emi Amount <span class="red">*</span></label>
										<div class="col-lg-3 error-msg emi">
											<input type="text"  name="emi" id="emi" value="" class="form-control removeSpaceInput emi" />
										</div>
										<label class="col-form-label col-lg-3" for="remark">Remark <span class="red">*</span></label>
										<div class="col-lg-3 error-msg">
											<input type="text"  name="remark" id="remark" value=""  class="form-control" />
										</div>
									</div>
                                </div>
                            </div>
							<input type="hidden" id="type" value="" name="type" />
							<input type="hidden" id="etype" value="" name="etype" />
							<input type="hidden" id="sanction_date" value="" name="sanction_date" />
							<input type="hidden" id="emi_due_date" value="" name="emi_due_date" />
							<input type="hidden" id="emi_amount" value="" name="emi_amount" />
                            <div class="col-md-12" id="new_associate_detail">
                            </div>
                            <div class="col-md-12 text-center">
                                    <button type="Submit" class=" btn bg-dark legitRipple btncollector" >Submit</button>
                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.loan.emiDueDateChange.partials.emiDueDateChange_js')
@stop