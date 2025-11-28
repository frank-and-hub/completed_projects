@extends('templates.admin.master')

@section('content')

<?php
$date_filter='';
$date_filter1='';
$branch_filter='';
$end_date_filter = '';
$end_date_filter1 = '';

if(isset($_GET['date']))
{
    $date_filter=$_GET['date'];
    if($date_filter!=''){
        $date_filter1=date("d/m/Y", strtotime(convertDate($date_filter)));
    }
  
}
if(isset($_GET['branch']))
{
  $branch_filter=$_GET['branch'];
}      

if(Auth::user()->branch_id>0)					
 $branch_filter=Auth::user()->branch_id;


 if(isset($_GET['end_date']))
{
    $end_date_filter=$_GET['end_date'];
    if($end_date_filter!=''){
        $end_date_filter1= $end_date = date("d/m/Y", strtotime(convertDate($end_date_filter)));
    } 
}

$finacialYear=getFinacialYear();
$startDatee=date("Y-m-d", strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$endDatee = date("Y-m-d", strtotime(convertDate($globalDate1)));
?>



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
                      <div class="row">
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">From Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="start_date" id="start_date"  value="{{$date_filter1}}" autocomplete="off"> 
                                         </div>
                                  </div>
                              </div>
                          </div>
						  <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">To Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control ends_date" name="ends_date" id="ends_date" value="{{$end_date_filter1}}" autocomplete="off"> 
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="fund_transfer_export" id="fund_transfer_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple submit" onClick="searchtransactionForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resettransactionForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="head" id="head" value="{{$head}}">
                        <input type="hidden" name="export" id="export" >
                          <input type="hidden" name="label" id="label" value="{{$label}}"> 
                    </form>
                </div>
                </div>
            </div>  
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{$detail->sub_head}}  - Ledger</h6>
                        <div class="">
						<!--
                         <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf

                        <input type="hidden" name="head" id="head" value="{{$head}}">
                        <input type="hidden" name="export" id="export" >
                          <input type="hidden" name="label" id="label" value="{{$label}}"> 
                        </form> -->
							<button type="button" class="btn bg-dark legitRipple export_report_trans ml-2" data-extension="1" style="float: right;">Export pdf</button>
                            <button type="button" class="btn bg-dark legitRipple export_report_trans ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <!-- <h6>TotalAmount :{{$total}}</h6> -->
                            
                        </div>
                    </div>
                    <input type="hidden" name="date" value="{{$date_filter}}" id="date">
                    <input type="hidden" name="branch" value="{{$branch}}" id="branch">
                    <table class="table datatable-show-all" id="transaction_report">
                        <thead>
                            <tr>                                    
                                        <th >S.No</th> 
                                        <th >BR Name</th> 
                                        <th >BR Code</th>
                                        <th >SO Name</th>
                                        <th >RO Name</th>
                                        <th >ZO Name</th>
                                        <th >Type</th> 
                                        <th >Description</th>
                                        <th >Amount</th>
                                        <th >Account No</th>
                                        <th >Member Name</th>  
                                        <!--<th >Associate Name</th>   -->
                                        <th >Payment Type</th>
                                        <th >Payment Mode</th>
                                        <th >Voucher No.</th>
                                       <!--  <th>Voucher Date</th>   -->
                                        <th >Cheque No.</th>   
                                        <th >Cheque Date</th>
                                        <th >Transaction Number</th>
                                        <th>Receive Bank</th>
                                        <th>Receive Bank Account</th>
                                        <th>Created Date</th>
                                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.account_head_report.ledger_script')
@endsection
