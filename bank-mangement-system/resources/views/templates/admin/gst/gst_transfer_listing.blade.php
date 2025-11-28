@extends('templates.admin.master')

@section('content')
    <div class="content">
       <style>
        .hideTableData{
            display:none;
        }
       </style>
        <div class="row">
            @include('templates.admin.gst.gst_transafer_filter')
            <div class="col-md-12">
           
                <div class="col-md-12 table-section hideTableData">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold ">GST Transaction List</h6>
                            <div class="col-md-8">
                                <button type="button" class="btn bg-dark legitRipple export_gst_transafer ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            </div>
                        </div>
                        <div class="">
                            <table id="gst_transfer_list" class="table datatable-show-all ">
                                <thead>
                                    <tr>
                                        <th >S/No</th>
                                        <th >Transfer Date</th>
                                        <th >Date Range</th>
                                        <th >State</th>
                                        <th >IGST Amount</th>
                                        <th >CGST Amount</th>
                                        <th >SGST Amount</th>
                                        <th >Set Off IGST Amount</th>
                                        <th >Set Off CGST Amount</th>
                                        <th >Set Off SGST Amount</th>
                                        <th >Final IGST Amount</th>
                                        <th >Final CGST Amount</th>
                                        <th >Final SGST Amount</th>
                                        <th >Transfer Request Amount</th>
                                        <th >GST Payable Amount</th>
                                        <th >NEFT Charges</th>
                                        <th >Late Penalty Amount</th>
                                        <th >Total Payable Amount</th>
                                        <th >Payment Date</th>
                                        <th >Is Paid</th>
                                        <th >Company Name</th>
                                        <th >Challan Slip</th>
                                        <th >Action</th>
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
  @include('templates.admin.gst.partials.gst_payable_script')
@stop