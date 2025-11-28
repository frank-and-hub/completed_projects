@extends('templates.admin.master')

@section('content')

<div class="content">
    @if($loanDetails)
        <div class="row">  
            <div class="col-lg-12">   
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">EMI Transaction For :{{ $loanDetails->account_number }} - {{ $loanTitle }}</h6>
                    </div>
                </div>             
            
            </div>
        </div>
		<!--<input name="total" id="total" class="total" value="" />-->
        <div class="row" > 
            <div class="col-lg-12" id="print_passbook">                
                <div class="card bg-white shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                          <input type="hidden" name="loanId" id="loanId" value="{{ $id }}">
                          <input type="hidden" name="loanType" id="loanType" value="{{ $type }}">
                            <table class="table table-flush" style="width: 100%" id="listtansactiondeposit">
                                <thead class=""> 
                                    <tr>
                                        <th style="width: 10%"> S.No</th>
                                        <!-- <th style="width: 10%"> Transaction ID</th> -->
                                        <th style="width: 10%"> Transaction Date</th>
                                        <th>Payment Mode</th> 
                                        <th>Description</th> 
                                        <th>Sanction Amount</th> 
                                        {{-- <th>Penalty</th>  --}}
                                        <th>Deposit</th>
                                        <th>JV Amount</th>
                                        <th>IGST Charge</th>

                                        <th>CGST Charge</th>
                                        <th>SGST Charge</th>  
										<th>Balance</th>

                                        {{-- <th>Opening Balance</th> --}}
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                            </table>
                        </div>                   
                    </div>
                </div> 
            </div> 
        </div>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="">EMI not found</h3>
                        <a href="#" style="float:right" class="btn btn-secondary">Back</a>
                    </div>
                </div>
                </div>
            </div> 
        </div>
    @endif
</div>
@stop

@section('script')
@include('templates.admin.loan.partials.script')
@endsection