@extends('templates.admin.master')

@section('content')

<div class="content">
    <div class="row">  
        <div class="col-lg-12">   
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Account Number : {{ $investment->account_number }}</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="investmentCommissionFilter" name="investmentCommissionFilter">
                        @csrf
                        <input type="hidden" name="investment_id" id="investment_id" value="">
                        <input type="hidden" name="investmentcommission_export" id="investmentcommission_export" value="">            
                    </form>
                </div>
            </div>             
        </div>
    </div>

    <div class="row" > 
        <div class="col-lg-12" id="print_passbook">                
            <div class="card bg-white shadow">
                <div class="card-body">
                    <div class="card-header bg-transparent header-elements-inline">
                        <h3 class="mb-0 text-dark">Investment Commissions</h3>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple investcommissionexport ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple investcommissionexport" data-extension="1">Export PDF</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table   class="table table-flush" style="width: 100%" id="commission_listing">
                            <thead class=""> 
                                <tr>
                                    <th style="width: 10%"> S.No</th>
                                    <th style="width: 10%"> Date</th>
                                    <th style="width: 10%"> Member ID</th>
                                    <th> Member Name</th> 
                                    <th> Total Amount</th>
                                    <th> Commission Amount</th>
                                    <th> Percentage</th>
                                    <th> Carder Name</th>
                                    <th>EMI No</th>
                                <th>Commission Type</th> 
                                <th>Associate Exists</th>
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
</div>

@stop

@section('script')
@include('templates.admin.investment_management.partials.commission_script')
@stop