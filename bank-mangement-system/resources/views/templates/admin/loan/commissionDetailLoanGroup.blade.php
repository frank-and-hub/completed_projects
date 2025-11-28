@extends('templates.admin.master')

@section('content')

<?php 
$end='';
$start='';
$search='yes';
if(isset($_GET['start']))
{
$start=$_GET['start'];
$search='yes';
}
if(isset($_GET['end']))
{
$end=$_GET['end'];
$search='yes';
}

?>
<div class="content">
    <div class="row">  
        <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                         <form action="#" method="post" enctype="multipart/form-data" id="commissionFilterDetail" name="commissionFilterDetail">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Start  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" readonly class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">End  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" readonly class="form-control  " name="end_date" id="end_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div> 

                                <div class="col-lg-12 text-right">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="commission_export" id="commission_export" value="">
                                            <input type="hidden" name="id" id="id" value="{{$loan->id}}">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchCommissionDetailForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCommissionDetailForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <div class="col-lg-12">                
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline"> 
                    <h5 class="mb-0 text-dark">Loans - {{ $loan->account_number}}( Group Loan )</h5>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple exportcommissionDetail ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <button type="button" class="btn bg-dark legitRipple exportcommissionDetail" data-extension="1">Export PDF</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="associate-commission-detail" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th>
                                <th>Date</th>
                                <th>Associate Name</th>
                                <th>Associate Code</th>
                                <th>Total Amount</th>
                                <th>Commission Amount</th>                         
                                <th>Percentage</th>
                                <th>Carder Name</th> 
                                <th>Commission Type</th>  
                                <th>Payment Type</th>
                                <th>Payment Distribute</th> 
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
@include('templates.admin.loan.partials.listing_loan_group_script') 
@stop
