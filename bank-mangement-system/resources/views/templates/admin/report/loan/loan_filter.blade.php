<?php
$startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
$startDatee = $endDatee = date('d/m/Y',strtotime($startDatee));
?>
 @php
 $dropDown = $company;
 $filedTitle = 'Company';
 $name = 'company_id';
 @endphp
<div class="col-md-12">
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Search Filter</h6>
        </div>
        @php
        $reportName = Request::segment(3);
        @endphp
        <div class="card-body">
            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
            @csrf
                <div class="row">
                    @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Loan Type <sup class="required">*</sup></label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <select class="form-control" id="loan_category" name="loan_category">
                                    <option value="">----Select----</option>
                                    <option value="G">Group loan</option> 
                                    <option value="L">Loan</option> 
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12"> Loan Plan </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <select class="form-control" id="plan" name="plan">
                                    <option value="">----Select Plan----</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @if($reportName == 'loan_application')                        
                           <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12"> Date From </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="text" class="form-control  create_application_date" name="application_start_date" id="application_start_date"  value="{{$startDatee}}" >
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Date To </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="text" class="form-control  create_application_date" name="application_end_date" id="application_end_date" value="{{$endDatee}}" >
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reportName == 'loan_issued')
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Date From </label>
                        <div class="col-lg-12 error-msg">
                                <div class="input-group">
                                    <input type="text" class="form-control  create_application_date" name="loanpayment_start_date" id="loanpayment_start_date"  value="{{$startDatee}}" >
                                </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Date To</label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="text" class="form-control  create_application_date" name="loanpayment_end_date" id="loanpayment_end_date" value="{{$endDatee}}" >
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reportName == 'loan_closed')
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Date From</label>
                        <div class="col-lg-12 error-msg">
                                <div class="input-group">
                                    <input type="text" class="form-control  create_application_date" name="closure_start_date" id="closure_start_date"  value="{{$startDatee}}" >
                                </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Date To </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="text" class="form-control  create_application_date" name="closure_end_date" id="closure_end_date" value="{{$endDatee}}" >
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reportName == 'loan_issued')
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Loan Status </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <select class="form-control" id="loan_status" name="loan_status">
                                    <option value="">----Select----</option>
                                    <option value="4">Due</option> 
                                    <option value="3">Clear</option> 
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reportName == 'loan_application')                   
                        <div class="form-group col-md-4">
                            <label class="col-form-label col-lg-12">Status </label>
                            <div class="col-lg-12 error-msg">
                                <select class="form-control" id="status" name="status">
                                    <option value="">Select status</option>
                                    <option value="0">Pending</option>
                                    <option value="1">Approved</option>
                                    <option value="2">Rejected</option>
                                </select>
                            </div>
                        </div>
                        @endif
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Member ID </label>
                        <div class="col-lg-12 error-msg">
                            <input type="text" name="member_id" id="member_id" class="form-control"  >
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Member Name </label>
                        <div class="col-lg-12 error-msg">
                            <input type="text" class="form-control  " name="member_name" id="member_name"  >
                        </div>
                    </div>
                    @if($reportName == 'loan_issued' || $reportName == 'loan_closed')
                    <div class="form-group col-md-4">
                        <label class="col-form-label col-lg-12">Account no </label>
                        <div class="col-lg-12 error-msg">
                            <input type="text" name="account_number" id="account_number" class="form-control"  >
                        </div>
                    </div>
                    @endif
                    <div class="form-group col-md-12">
                        <div class="col-lg-12 text-right" >
                            <input type="hidden" name="adm_report_currentdate" id="adm_report_currentdate" class="create_application_date" value="{{$startDatee}}">
                            <input type="hidden" name="is_search" id="is_search" value="no">
                            <input type="hidden" name="export" id="export" value="">
                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>