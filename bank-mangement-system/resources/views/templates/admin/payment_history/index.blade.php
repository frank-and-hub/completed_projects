@extends('templates.admin.master')
@section('content')
<style type="text/css">
    @media print {
body {-webkit-print-color-adjust: exact;}
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
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Start  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off"   > 
                                               </div>
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">End  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Vendor Type </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="type" name="type">
											<?php if(check_my_permission(Auth::user()->id,"195") == "1"){ ?>
                                                <option value="0" selected >Vendor</option> 
											<?php } ?>
											<?php if(check_my_permission(Auth::user()->id,"196") == "1"){ ?>
                                                <option value="3"  >Customer</option> 
											<?php } ?>
                                                <option value="1">Rent</option>  
                                                <option value="2">Salary</option> 
                                                <option value="4">Associate</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
												<option value=""  >---Select Branch---</option>
												@foreach($branches as $branch)
												<option value="{{$branch->id}}">{{$branch->name}} ({{$branch->branch_code}})</option>
												@endforeach 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <label class="col-form-label col-lg-12">  </label>
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="vendorExport" id="vendorExport" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button><input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                                <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                    
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
             
            <div class="col-md-12"  id='vendor_div'>
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Payment List</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>
                        <!--<a href="{{URL::to('/')}}/admin/vendor/print" target="_blank"><button type="button" class="btn bg-dark legitRipple ml-2" data-extension="2" style="float: right;">Print</button></a>-->

                        </div>
                    </div>
                    <div class="table-responsive"   >
                        <table id="payment_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Date</th>
                                    <th>Payment#</th>                                     
                                    <th>Reference#</th>
                                    <th>Vendor Name</th>
                                    <th>Bill#</th>
                                    <th>Mode</th>
                                    <th>Amount</th>
                                    <th class="text-center">Action</th> 
								
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                   
                    </div>
                </div>
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
      <div class="">
       
        <div class="col-lg-12">
          <div class="card bg-white" >
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div class="border rounded d-flex shadow  bg-light" style="height:30px;">
                 <div class="border-right px-2 py-1"><i class="fas fa-print"onclick="printDiv('detail');" ></i></div>
               </div>
              </div>
             
              </div>
            </div>
          </div>
        </div>
       <div class="col-lg-12" >
          <div class="card bg-white" > 
            <div class="card-body ">
             
              <div class="row"  id="detail">
                <div class="" style="width:100%;">
                    <label class="card-title col-md-6 "><span class="font-weight-bold">Samraddh Bestwin</span><br>Rajasthan 
                       <br/> India</label>
                       <hr ></hr>

                </div>
                <div class="col-lg-12">
                 <h1 class="text-center">Payment Made</h1>
                 <div style="margin-top:30px;display: flex;">
                 <table style="width:100%;margin-top:30px;margin-bottom:20px;">
                    <tbody>
                      <tr>
                       
                        <td align="right" style="vertical-align:bottom;width: 40%;">
                            <table style="float:right;width: 100%;table-layout: fixed;word-wrap: break-word;" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                        <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Payment
                                            </th>
                                            <td>
                                                <input type="text" id="payment_id"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Payment Date
                                            </th>
                                            <td>
                                                <input type="text" id="paymentDate"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                         <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Reference Number
                                            </th>
                                            <td>
                                                <input type="text" id="reference_number"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                         <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Paid To
                                            </th>
                                            <td>
                                                <input type="text" id="paidToVendor"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                         <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Payment Mode
                                            </th>
                                            <td>
                                                <input type="text" id="payment_mode"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                         <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Paid Through
                                            </th>
                                            <td>
                                                <input type="text" id="paymentThrough"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                         <tr>
                                            <th style="padding:5px 10px 5px 0px;font-size:10pt;" class="">
                                             Amount Paid In Words
                                            </th>
                                            <td>
                                                <input type="text" id="amountInWord"  class="form-control" readonly>
                                            </td>
                                        </tr>
                                        
                                </tbody>
                            </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                
                      <div style="width:50%;margin-top:60px;margin-left:15px;">
                        <div style="text-align:center;color:#ffffff;background:#78ae54;width: 50%; padding: 34px 5px;margin-left: inherit;">
                            <span> Amount Paid</span><br>
                            <span class="pcs-total">
                            </span>
                        </div>
                      </div>
                    </div>
                     <hr ></hr>
					 <h4 style="text-align:right;" class="over_payment_div" style="display:none">Over Payment <span class="over_payment"></span></h4>
                     <h1 class="payment_for_div">Payment For</h1>
                  <table  class="table detailtable">
                      <thead  id="">
                          <tr >
                              <th style="" class="">
                                Bill Number
                              </th>
                              <th style="word-wrap: break-word;width:40%;" class="">
                                Bill Date
                              </th>
                              <th style="" class="">
                                Bill Amount
                              </th>
                              <th style="" class="">
                                 Payment Amount
                              </th>
                              
                          </tr>
                      </thead>
                      <tbody>
                              <tr style="border-bottom:1px solid #ededed" class="">
                                      <td class="bill_number"> 
                                      </td>
                                      <td class="bill_date">
                                      </td>
                                      <td >Rs.<span class="bill_amount"></span>   
                                      </td>
                                      <td>Rs.<span class="payable_amount"></span> 
                                      </td>
                                      
                                    
                              </tr>
                              
                      </tbody>
                  </table>
                  
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
@include('templates.admin.payment_history.partials.script')
@stop
