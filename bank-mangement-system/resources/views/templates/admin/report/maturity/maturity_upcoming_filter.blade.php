<?php
$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
$startDatee = $endDatee = date('d/m/Y', strtotime($startDatee));
?>
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
                <div class="col-md-4">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-12">Plan</label>
                        <div class="col-lg-12 error-msg">
                            <select class="form-control" id="plan" name="plan">
                                <option value="">Select Plan</option>
                                @foreach($plans as $key => $val)
                                    <option value="{{$key}}">{{$val}}</option>  
                                @endforeach 
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="plancategory" class="plancategory">
                <input type="hidden" name="plansubcategory" class="plansubcategory">

                <div class="col-md-4">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-12">Start Date </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="text" class="form-control" name="maturity_start_date" id="maturity_start_date" value="{{$startDatee}}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-12">End Date </label>
                        <div class="col-lg-12 error-msg">
                            <div class="input-group">
                                <input type="text" class="form-control" name="maturity_end_date" id="maturity_end_date" value="{{$endDatee}}">

                                <span id="warning-msg" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Status </label>
                            <div class="col-lg-12 error-msg">
                                <select class="form-control" id="status" name="status">
                                    <option value="">Select Status</option>
                                    <option value="0">pending</option>
                                    <option  value="1">processed</option>
                                </select>
                            </div>
                        </div>
                    </div> -->

                    

                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Member Name </label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" class="form-control" name="member_name" id="member_name">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Member ID </label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" name="member_id" id="member_id" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Account No </label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" name="account_no" id="account_no" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-12">Associate Code</label>
                            <div class="col-lg-12 error-msg">
                                <input type="text" name="associate_code" id="associate_code" class="form-control">
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group row">
                            <div class="col-lg-12 text-right">
                                <input type="hidden" name="is_search" id="is_search" value="no">

                                 <input type="hidden" name="adm_report_currentdate" id="adm_report_currentdate" class="create_application_date adm_report_currentdate" value="{{$startDatee}}">

                                <input type="hidden" name="export" id="export" value="">
                                <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
</div>