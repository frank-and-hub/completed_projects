@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
               <form id="filter" action="#" method="post" enctype="multipart/form-data" name="filter">
                @csrf
            <input type="hidden" name="expense_export" id="filter_report">
            <input type="hidden" name="bill_no" id="bill_no" value="{{$bill_no}}">
             <input type="hidden" name="branch_id" id="branch_id" value="{{$bill_status->branch_id}}">
              <input type="hidden" name="created_at" id="created_at" value="{{$bill_status->bill_date}}">
        </form>


        @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
        
            <div class="col-md-12">



                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Expense Report</h6>
                        <div class="">

                            @if($bill_status->status == '0')
                             <button type="button" class="btn bg-success legitRipple  ml-2 approve_expense" data-row-id="{{$bill_no}}">Approve</button>

                            @endif
<!--                              <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
 -->
                            <!-- <a type="button" data-extension="1" class="btn bg-dark legitRipple export ml-2" style="float: right;">Export Pdf</a> -->
                            
                        </div> 
                    </div>
                    <div class="">
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
    @include('templates.admin.expense.partial.script')
@stop