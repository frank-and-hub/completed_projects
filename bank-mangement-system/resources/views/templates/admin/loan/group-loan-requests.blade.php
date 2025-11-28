@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="group-loan-filter" name="group-loan-filter">
                    @csrf
                        <div class="row">

                            @php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp

                           @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                           <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type <span class="required">*</span></label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control group_loan_type" id="group_loan_type" name="group_loan_type">
                                                    <option value=""  >----Select Loan Type----</option> 
                                                    <option value="L">Loan</option>
                                                    <option value="G">Group Loan</option>
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan</label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control group_loan_plan" id="group_loan_plan" name="group_loan_plan">
                                                    <option value=""  >----Select Loan Plan----</option> 
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" readonly class="form-control from_date" name="date_from" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" readonly class="form-control to_date" name="date_to" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <!-- @if(Auth::user()->branch_id<1)
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Branch </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value=""  >----Select----</option> 
                                                @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                @endforeach
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            @else
                                  <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">                         
                            @endif -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="application_number" id="application_number"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="member_name" id="member_name"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="member_id" id="member_id" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <option value="0">Pending</option>
                                            <option value="1">Approved</option>
                                            <option value="3">Clear</option>
                                            <!-- <option value="4">Due</option> -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="group_loan_details_export" id="group_loan_details_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="groupLoanSearchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="groupLoanResetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 d-none">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Group Loan Registration Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-group-loan-details ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple export-group-loan-details" data-extension="1">Export PDF</button>
                    </div>
                </div>
                <div class="">
                    <table id="group_loan_request_table" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Applicant/Group Leader Id</th>
                                <th>Application Number</th>
                                <th>A/C No.</th>
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <!--<th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th>-->
                                <th>Member Id</th>
                                <th>Member Name</th>
                                <th>Last Recovery Date</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Loan Type</th>
                                <th>Transfer Amount</th>
                                <th>Loan Amount</th>
                                <th>File Charge Amount</th>
                                <th>Bank Name</th>
                                <th>Bank Account Number</th>
                                <th>IFSC Code</th>
                                <th>Status</th>
                                <th>Approved Date</th>
                                <th>Group Loan Common ID</th>
                                <th>Application Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>                    
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.loan.partials.script')
@endsection