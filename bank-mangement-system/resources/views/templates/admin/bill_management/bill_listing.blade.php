@extends('templates.admin.master')

@section('content')
@section('css')
    <style>
        .hideTableData {
            display: none;
        }
    </style>
@endsection
<style type="text/css">
    @media print {
        .detailtable th {
            background: red !important;
        }
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="bill_form" name="bill_form">
                        @csrf
                        <div class="row">
                        @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Vendor</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" name="vendor" id="vendor">
                                                <option value="">---Please Select Vendor---</option>
                                                {{--
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                                --}}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date <span>*</span></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off" readonly title="Please select the date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date <span>*</span></label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off" readonly title="Please select the date">
                                            <input type="hidden" class="create_application_date" id="system_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Status</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">

                                            <select class="form-control" name="status" id="status">
                                                <option value="">---Please Select Status---</option>
                                                <option value="0">UnPaid</option>
                                                <option value="1">Paid</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="bill_report_export" id="bill_report_export"
                                            value="">
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                            onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Bill List</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                            style="float: right;">Export xslx</button>
                    </div>
                </div>
                <table id="bill_listing" class="table datatable-show-all">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Date</th>
                            <th>Company Name</th>
                            <th>Branch Name</th>
                            <th>Bill Number</th>
                            <th>Vendor Name</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Paid Amount</th>
                            <th>Balance Due</th>
                            <th>Bill Amount</th>
                            <th>
                                @if (check_my_permission(Auth::user()->id, '208') == '1' ||
                                        check_my_permission(Auth::user()->id, '209') == '1' ||
                                        check_my_permission(Auth::user()->id, '210') == '1' ||
                                        check_my_permission(Auth::user()->id, '211') == '1')
                                    Action
                                @endif
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog  modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bill Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card bg-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="border rounded d-flex shadow  bg-light" style="height:30px;">
                                            <div class="border-right px-2 py-1" onclick="printDiv('detail');"><i
                                                    class="fas fa-print"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card bg-white">
                            <div class="card-body ">
                                <div class="row" id="detail">
                                    <div class="d-flex justify-content-between" style="width:100%;">
                                        <label class="card-title col-md-6 ">
                                            <span class="font-weight-bold companyName">Samraddh
                                                Bestwin</span><br>{{ $set->title }}
                                            <br /> India</label>
                                        <h1></h1>
                                        <div style="padding:5px 10px 5px 0px;font-size:10pt;" class="text-right">
                                            <h1 class="card-title display-4">BILL</h1>
                                            <label class="font-weight-bold">Bill# <span class="bill_number">
                                                    5456</span></label><br>
                                            <label class="font-weight-normal mt-2">Balance Due</label><br>
                                            <label class="font-weight-bold" style="font-size: 25px;">Rs. <span
                                                    class="bill_balance_due">30000</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <table style="width:100%;margin-top:30px;margin-bottom:80px;">
                                            <tbody>
                                                <tr>
                                                    <td style="width:60%;vertical-align:bottom;word-wrap: break-word;">
                                                        <div style="padding-bottom: 5px;">
                                                            <label style="font-size: 10pt;" id="notes_label"
                                                                class="">Notes</label>
                                                            <br>
                                                            <span style="white-space: pre-wrap;" id="notes"
                                                                class="notes">hbvhbvnv</span>
                                                        </div>
                                                    </td>
                                                    <td align="right" style="vertical-align:bottom;width: 40%;">
                                                        <table
                                                            style="float:right;width: 100%;table-layout: fixed;word-wrap: break-word;"
                                                            border="0" cellspacing="0" cellpadding="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="padding:5px 10px 5px 0px;font-size:10pt;"
                                                                        class="text-right">
                                                                        <span class="">Date:</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <span class="date">13/08/2021</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:5px 10px 5px 0px;font-size:10pt;"
                                                                        class="text-right">
                                                                        <span class="">Amount:</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <span class="amount">Rs.50,000.00</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="padding:5px 10px 5px 0px;font-size: 10pt;"
                                                                        class="text-right">
                                                                        <span class="">Branch:</span>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <span class="branch">Jaipur</span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <table class="table detailtable">
                                            <thead>
                                                <tr>
                                                    <th style="word-wrap: break-word;width:10%;" class="">
                                                        #
                                                    </th>
                                                    <th style="word-wrap: break-word;width:40%;" class="">
                                                        Item & Description
                                                    </th>
                                                    <th style="word-wrap: break-word;width:10%;" class="">
                                                        Qty
                                                    </th>
                                                    <th style="word-wrap: break-word;width:10%;" class="">
                                                        Rate
                                                    </th>
                                                    <th style="word-wrap: break-word;width:10%;" class="">
                                                        Amount
                                                    </th>



                                                </tr>
                                            </thead>
                                            <tbody id="bodyData">

                                            </tbody>
                                        </table>
                                        <div style="width: 100%;margin-top: 1px;">
                                            <div class="">
                                                <table class="" cellspacing="0" border="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 120px;" class=" text-right">

                                                            </td>
                                                            <td style="width: 120px;" class=" text-right">

                                                            </td>
                                                            <td style="width: 120px;" class=" text-right">

                                                            </td>
                                                            <td style="width: 120px;" class=" text-right">

                                                            </td>
                                                            <td style="width: 120px;" class=" text-right">

                                                            </td>
                                                            <td style="width: 120px;" class=" text-right">

                                                            </td>
                                                            <td class=" text-right"style="width: 120px;">Sub Total
                                                            </td>

                                                            <td class=" text-right sub_amount"style="">50,000.00
                                                            </td>
                                                        </tr>
                                                        <tr style="height:40px;" id="transferAmount">
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td class="text-right" style=""><b>Total</b></td>

                                                            <td class=" text-right "style=""><b
                                                                    class="transfer_amount">Rs.50,000.00</b></td>
                                                        </tr>
                                                        </tr>
                                                        <tr style="height:40px;">
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>
                                                            <td style="" class=" text-right">

                                                            </td>

                                                            <th class="shadow-lg p-3 mb-5 bg-white rounded"
                                                                colspan='2' style=""><b
                                                                    class="d-flex justify-content-between">Balance Due
                                                                    <span class="text-right bill_balance_due">
                                                                        Rs.50,000.00</span></b></th>



                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div style="clear: both;">Authorized Signature _______________________
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@include('templates.admin.bill_management.partials.listing_script')
@stop
