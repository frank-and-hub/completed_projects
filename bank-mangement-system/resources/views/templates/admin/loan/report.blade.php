@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                    <input type="hidden" name="loan_id" id="loan_id">
                    <input type="hidden" id="report_title" value="{{$title}}" name="title" />
                    <input type="hidden" id="create_application_date" name="create_application_date" class="form-control create_application_date" value="">

                        <div class="row">
                        @php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp
                           @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch','allOptionShow' =>true])
                            <!-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Outstanding As On Date</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control outstanding_as_on_date" name="outstanding_as_on_date" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control from_date" name="from_date" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control to_date" name="to_date" autocomplete="off"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="loan_account_number" id="loan_account_number"  >
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
                                    <label class="col-form-label col-lg-12">Customer ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="customer_id" id="customer_id" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                          
                            <!------------------ loan Plan Dynamic  --->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type <sup>*</sup> </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="loan_type" name="loan_type"> 
                                                <option value=""  >----Select----</option> 
                                                <option value="L">Loan</option> 
                                                <option value="G">Group Loan</option> 
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="loan_plan" name="loan_plan">
                                                <option value=""  >----Select----</option> 
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
							<div class="col-md-4 group_loan_common">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Group Leader ID </label>
									<div class="col-lg-12 error-msg">
										<input type="text" name="group_loan_common_id" id="group_loan_common_id" class="form-control"  > 
									</div>
								</div>
							</div>


                            <!------------------ loan Plan Dynamic  --->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Emi Type</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="emi_option" name="emi_option">
                                                <option value=""  >----Select----</option> 
                                                <option value="1"  >Monthly</option>
                                                <option value="2"  >Week</option> 
                                                <option value="3"  >Daily</option>
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="export" id="export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">{{$title}}</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-outstanding ml-2" data-extension="0" style="float: right;">Export xslx</button>
                       <!--  <button type="button" class="btn bg-dark legitRipple export-group-loan" data-extension="1">Export PDF</button> -->
                    </div>
                </div>
                <div class="">
                <table  id="loan_report_outstanding" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th> Company Name </th>
                                <th> Emi date </th>
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <th>A/C Number</th> 
                                <th>Applicant/Group Leader Id</th> 
                                <th>Customer Id</th> 
                                <th>Member Name</th>
                                <th>Member ID</th>
                                <th>Loan Type</th>
                                <th>Emi Type</th>
                                <th>Emi Period</th>
                                <!-- <th>ROI</th> -->
                                <th>Total Payment</th>
                                <th>Loan Amount</th>
                                <th>Closure Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>                    
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Repayment Chart</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <form action="#" method="post" enctype="multipart/form-data" id="filter_repayment" name="filter_repayment">
              @csrf
              <input type="hidden" name="export_repayment" id="export_repayment" value="">
          </form>
          <input type="hidden" id="inputid">
      <button type="button" class="btn bg-dark legitRipple export-repayment ml-2" data-extension="0" style="float: right;">Export xslx</button>
      <a  href="javascript:void(0)" id="prnt" class="btn bg-dark legitRipple  ml-2 prnt_modal" data-extension="0" style="float: right;" >Print</a>
        <table id="" class="table datatable-show-all pageee" >
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Emi Date</th>
                   
                    <th>Emi Amount </th>
                    <th>Interest Amount </th>
                    <th>Principal Amount </th>
                    <th>Outstanding  Amount </th>
                </tr>
            </thead>
           <tbody id="datarow" ></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.loan.partials.loan_report_script')
@endsection