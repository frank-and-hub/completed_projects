@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{$detail->sub_head}}  - Ledger</h6>
                        <div class="">
                         <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf

                        <input type="hidden" name="head" id="head" value="{{$head}}">
                        <input type="hidden" name="export" id="export" >
                          <input type="hidden" name="label" id="label" value="{{$label}}"> 
                        </form>
                            <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <!-- <h6>TotalAmount :{{$total}}</h6> -->
                            
                        </div>
                    </div>
                    <input type="hidden" name="date" value="{{$date}}" id="date">
                    <input type="hidden" name="branch" value="{{$branch}}" id="branch">
                    <table class="table datatable-show-all" id="account_head_ledger_report">
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
                                        <th>Voucher Date</th>  
                                        <th >Cheque No.</th>   
                                        <th >Cheque Date</th>
                                        <th >Transaction Number</th>
                                        <th>Transaction Date</th>  
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
