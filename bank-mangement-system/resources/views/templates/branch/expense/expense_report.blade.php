@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                    <h3 class=""> Expense Detail Report</h3>                    
                </div>
                </div>
            </div>
        </div>
        <form id="filter" action="#" method="post" enctype="multipart/form-data" name="filter">
                @csrf
            <input type="hidden" name="expense_export" id="filter_report">
             <input type="hidden" name="bill_no" id="bill_no" value="{{$bill_no}}">
             <input type="hidden" name="branch_id" id="branch_id" value="{{$bill_status->branch_id}}">
              <input type="hidden" name="created_at" id="created_at" value="{{$bill_status->created_at}}">
        </form>

     
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark"> Expense List</h3>
                            </div>
                            <div class="col-md-4 text-right">
                              <!--   <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button> -->
                            </div>
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                       <table id="expense_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Bill Date</th> 
                                    <th>Approve Date</th> 
                                    <th>Account Head</th> 
                                    <th>Sub Head1</th> 
                                    <th>Sub Head2</th>  
                                    <th>Particulars</th>
                                    <th>Receipt</th>
                                    <th>Amount</th>
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
    @include('templates.branch.expense.partial.script')
@stop