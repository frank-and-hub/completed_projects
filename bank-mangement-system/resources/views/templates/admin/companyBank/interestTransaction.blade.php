@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                
            <div class="col-md-12">
                <div class="card">
                    <form method="POST" id="filter">
                        @csrf
                        <input type="hidden" name="company_bond_transaction" id="company_bond_transaction" value="">

<!--                             <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>
 -->                      <input type="hidden" name="bound_id" id="bound_id"   value="{{$id}}">
                    </form>
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Transaction List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                        </div>
                    </div>
                    <div class="">
                        <table id="transaction_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Transaction Date</th>
                                    <th>Bank Name</th>
                                    <th>Transaction Type</th>

                                    <th>Fd No.</th>
                                    <th>Received Interest</th>

                                    <th>Tds Amount</th> 
                                    <th>Total Amount</th> 
                                    <th>Withdrawal Amount</th>
                                    <th>Remark</th>
                                    <th>Receive Bank</th>                                
                                    <th>Receive Bank Account</th> 
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.companyBank.partials.interest_script')
@stop