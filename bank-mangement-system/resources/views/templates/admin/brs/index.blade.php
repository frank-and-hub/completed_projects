@extends('templates.admin.master')
@section('content')
@php
    $dropDown = $company;
    $filedTitle = 'Company';
    $name = 'company_id';
@endphp
<?php 
$re_month1='';
$re_year1=''; 
if(old('rent_month') )
{
    $re_month1=old('rent_month') ;
}
if(old('rent_year'))
{
    $re_year1=old('rent_year') ;
} 

if(isset($re_month))
{
    $re_month1=$re_month;
}
if(isset($re_year))
{
    $re_year1=$re_year;
} 
?>
<style>
form#filterPerticuler {padding: 0 16px;}
.notSave td{color:red;cursor:pointer;}
.table th, .table td {padding: 0.25rem;vertical-align: top;border-top: 1px solid #ddd;}
.btnDiv{text-align: left;padding: 30px 0 30px 12px;}
.totalFInals{padding:20px !important;}
.notSave{height: 80px;}
</style>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                        
                    <div class="card-body">
                        {{Form::open(['url'=>'#','method'=>'POST','idf'=>'filter','name'=>'filter','enctype'=>'multipart/form-data'])}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Year </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
												 <select class="form-control" name="year" id="year">
                                                    <option value="">---Please Select Year---</option>
                                                    {{ $last= date('Y')-3 }}
                                                    {{ $now = date('Y') }}

                                                    @for ($i = $now; $i >= $last; $i--)
                                                        <option value="{{ $i }}" @if($i==$re_year1) selected @endif >{{ $i }}</option>
                                                    @endfor
                                                 </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                     <label class="col-form-label col-lg-12">Month</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
												 <select class="form-control" name="month" id="month">
                                                     <option value="">---Please Select Month---</option>
													 @for ($i = 1; $i <= 12; $i++)
                                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('M', mktime(0, 0, 0, $i, 1)) }}</option>
                                                    @endfor
                                                 </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @include('templates.GlobalTempletes.role_type', [
                                'dropDown' => $dropDown,
                                'filedTitle' => $filedTitle,
                                'name' => $name,
                                'value' => '',
                                'multiselect' => 'false',
                                'apply_col_md' => true,
                                'classes' => 'findBranh',
                                ])    
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="bank" name="bank">
                                                <option value="">Select Bank</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Account</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="bank_account" name="bank_account">
                                                <option value="">Select Bank Account</option>
                                            </select>
                                            <span id="msg" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <button type="button" class=" btn bg-dark legitRipple" name="submitGetData" id="submitGetData">Get Data</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
						{{Form::close()}}	

						<!--Statement Closing Balance   -->
                        <div class="modal fade" id="Statement_balance" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"  data-dismiss="modal">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">BRS- Bank Daybook closing amount</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Closing Balance</label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="closing_balance" id="closing_balance"  readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="Statement_balance_submit">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daybook Balance -->

                        <div class="modal fade" id="daybook_balance" tabindex="-2" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">BRS- Bank Statement closing amount </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Closing Balance</label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" class="form-control  " name="daybook_closing_balance" id="daybook_closing_balance">
                                                    <input type="hidden" name="openingbalance" id="openingbalance" val="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" name="submitFinalData" id="submitFinalData">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 d-none" id="BrsTableDiv">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">BRS List</h6>
                       <!--  <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>
                        </div> -->
                    </div>

					<form action="#" method="post" enctype="multipart/form-data" id="filterPerticuler" name="filterPerticuler">
					    <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <thead style="font-size:9px;">  
                                        <tr>
                                            <th>S/N</th>
                                            <th>Case Depo</th>
                                            <th>Particulars</th>
                                            <th>BR Name</th>
                                            <th>Chq No./UTR No.</th> 
                                            <th>CR-Amt </th>
                                            <th>DR-Amt </th>
                                        </tr>
                                    </thead>
                                    <tbody id="BrsDataBody" style="font-size:9px;"> 

                                    </tbody>
                                </table>
                            </div>

					        <div class="col-md-6">
							    <table class="table">
                                    <thead style="font-size:9px;">
                                        <tr>
                                            <th>Date</th>
                                            <th>Case Depo</th>
                                            <th>BR Name</th>
                                            <th>Chq No./UTR No.</th>  
                                            <th>CR-Amt </th>
                                            <th>DR-Amt </th>
                                        </tr>
                                    </thead>
								    <tbody id="EntryNewBrsDataBody" style="font-size:9px;">


								    </tbody>
							    </table>
					        </div>
					    </div>
                        <div class="row totalRecords" style="display:none">
                                    
                            <input type="hidden" name="company_id" id="company_id" readonly/>
                            <input type="hidden" name="bank_id" id="bank_id" readonly/>
                            <input type="hidden" name="bank_account" id="bank_account_detail" readonly/>
                            <input type="hidden" name="year" id="year_detail" readonly/>
                            <input type="hidden" name="month" id="month_detail" readonly/>
                            @php 
                            $startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
                            $startDatee = $endDatee = date('d/m/Y',strtotime($startDatee));
                            @endphp 

                            <input type="hidden" name="created_at" id="created_at" readonly value="{{$startDatee}}">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td style="padding:20px;">Daybook Closing <span class="closingDate"></span></td>
                                        <td style="padding:20px;">Statement Closing</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:20px;"><input type="text" name="finalClosingBalance" id="finalClosingBalance" readonly/></td>
                                        <td style="padding:20px;"><input type="text" name="statementClosingBalance" id="statementClosingBalance" readonly/></td>
                                    </tr>
                                </thead>
                            </table>
                        </div>    
					    
                        <div class="btnDiv" style="display:none">
                            <button type="button" class="btn btn-primary" name="saveUserRecordsData" id="saveUserRecordsData">Save</button>
                            <button type="button" class="btn btn-primary" name="clearUserRecordsData" id="clearUserRecordsData">Cancel</button>
                            <button type="button" class="btn btn-primary" name="printUserRecordsData" id="printUserRecordsData">Print</button>
                        </div>
					</form>
                </div>
            </div>
        </div>
    </div>

@include('templates.admin.brs.partials.script')

@stop