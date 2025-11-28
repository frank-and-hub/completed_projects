@extends('templates.admin.master')
<?php

$sub_head='';
?>
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
                            <div class="row">
                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Month <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="month" name="month">
                                                    <option value=""  >----Select Month----</option> 
                                                 <!--   @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor -->

                                                    {{ $last_month= 12}}
                                                        {{ $now_month = 1}}

                                                        @for ($i = $now_month; $i <= $last_month; $i++)
                                                        <?php
                                                        $monthNum  = $i;
                                                        $monthName = date('F', mktime(0, 0, 0, $monthNum, 10));
                                                        ?>
                                                            <option value="{{ $i }}">{{ $monthName }}</option>
                                                        @endfor

                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Year <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="year" name="year">
                                                    <option value=""  >----Select Year----</option> 
                                                  <!--  @for($i = 2000; $i <= 2050; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor-->

                                                    {{ $last= date('Y')-100 }}
                                                        {{ $now = date('Y') }}

                                                        @for ($i = $now; $i >= $last; $i--)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor

                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->branch_id<1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="branch_id" name="branch_id">
                                                    <option value=""  >----Select Branch----</option> @foreach($branch as $val)
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}-{{ $val->branch_code}}</option> 
                                                    @endforeach
                                                 

                                                </select>

                                               </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                  <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">                         
                                @endif
                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Rent Type </label>
                                        
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_type" name="rent_type">
                                                    <option value=""  >----Select----</option> 
                                                    
                                                    @foreach( $accountHeadLibilities as  $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->sub_head }}</option> 
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="status" name="status">
                                                    <option value=""  >----Select Status----</option> 
                                                  
                                                    <option value="0">Pending</option>
                                                    <option value="1">Transferred</option> 

                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="ledger_export" id="ledger_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple filter-report" onClick="searchFormReport()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple filter-report" id="reset_form" onClick="resetFormReport()" >Reset </button>
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
                       <h6 class="card-title font-weight-semibold">Rent Payment List</h6>
                        <div class="">
                        
                            <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>
                             
                        </div>
                    </div>
                    <table class="table datatable-show-all" id="rent-ledger-table-report">
                        <thead>
                            <tr>                                    
                                <th >S.No</th> 
                                <th >BR Name</th> 
                                <th >BR Code</th>
                                <th >SO Name</th>
                                <th >RO Name</th>
                                <th >ZO Name</th>
                                <th >Month</th>
                                <th >Year</th>
                                <th >Rent Type</th>
                                <th >Period From </th>
                                <th >Period To</th>
                                <th >Address</th>
                                <th >Owner Name</th>
                                <th  >Owner Mobile Number</th>
                                <th >Owner Pan Card</th>
                                <th >Owner Aadhar Card </th>
                                <th >Owner SSB account </th> 
                                <th > Owner Bank Name</th>
                                <th  >Owner Bank A/c No.</th>
                                <th  >Owner IFSC code </th>
                                <th >Security amount</th>                                   
                                <th >Yearly Increment</th>
                                <th >Office Square feet area</th>
                                <th >Rent</th>
                                <th >Actual Transfer Amount</th>  
                                <th >TDS Amount</th>   
 
                                <th >Transfer Amount</th>
                                <th >Advance Payment</th>  
                                <th >Settle Amount</th>  
                                <th >Transfer Status</th>
                                <th >Transfer Date</th>                                      
                                <th >Transfer Mode</th>  
                                <th >V No.</th>  
                                <th >V Date</th>  
                                <th >Bank Name</th>  
                                <th >Bank A/No.</th> 
                                <th >Payment Mode</th>   
                                <th >Cheque No. </th>  
                                <th >Online Transaction No.</th>  
                                <th >NEFT Charge</th> 
                                <th >Employee Code</th>
                                <th >Employee Name</th>
                                <th >Employee Designation</th>
                                <th >Employee Mobile No.</th> 

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
@include('templates.admin.rent-management.partials.payment_ledger_script')
@endsection
