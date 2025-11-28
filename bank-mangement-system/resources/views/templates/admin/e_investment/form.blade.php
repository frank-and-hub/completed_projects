@extends('templates.admin.master')







@section('content')



    <div class="loader" style="display: none;"></div>



    <div class="content">



        <div class="row">



            <div class="col-md-12">



                <!-- Basic layout-->



                <div class="card">



                    <div class="card-header header-elements-inline">



                        <div class="card-body" >

                            <form method="post" action="{{route('admin.getReinvestDetail')}}" name="filter" id="filter">

                                 @csrf

                                <div class="form-group row">



                                    <label class="col-form-label col-lg-2">Enter Account No.<sup>*</sup></label>



                                    <div class="col-lg-10 error-msg">



                                        <input type="text" id="account_no" name="account_no" class="form-control " @if($record) value="{{$record->account_number}}" @endif>



										<p class="text-danger msg"></p>



                                    </div>



                                </div>



								<div class="text-right">



                                     <button type="button" class=" btn btn-primary legitRipple submit" >Submit</button>



                                </div>

							</form>	



							<form method="post" action="{{route('admin.savereinvestmbdata')}}" name="filter2" id="filter2">

                                @csrf

                                <input type="hidden" name="investmentId" id="investmentId">

                                <input type="hidden" name="accountNumber" id="accountNumber">

                                <input type="hidden" name="fdamount" id="fdamount">
                                <input type="hidden" name="totaldeposit" id="totaldeposit">

    							<div class=""  id="detail_form">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-2">A/C Open Date<sup>*</sup></label>

                                            <div class="col-lg-4 error-msg">

                                                <input type="text" id="ac_opening_date" name="ac_opening_date" class="form-control " autocomplete="off">

                                            </div>



                                            <label class="col-form-label col-lg-2">Money Back Date<sup>*</sup></label>

                                             <div class="col-lg-4 error-msg">

                                                <input type="text" id="mb_date" name="mb_date" class="form-control " autocomplete="off" readonly="">

                                            </div>

                                        </div>





    									<div class="form-group row">

                                           <label class="col-form-label col-lg-2">ELI Amt<sup>*</sup></label>

                                            <div class="col-lg-4  error-msg">

                                                <input type="text" id="eli_amount" name="eli_amount" class="form-control " readonly>

                                            </div>



                                            <label class="col-form-label col-lg-2">Deposit AMT<sup>*</sup></label>

                                            <div class="col-lg-4 error-msg">

                                                <input type="text" id="deposite_amount" name="deposite_amount" class="form-control " readonly="">

                                            </div>

                                        </div>



                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-2">A/C. Deno<sup>*</sup></label>

                                             <div class="col-lg-4 error-msg">

                                                <input type="text" id="ac_deno" name="ac_deno" class="form-control " readonly>

                                            </div>



                                            <label class="col-form-label col-lg-2">Total Amount<sup>*</sup></label>

                                            <div class="col-lg-4">

                                                <input type="text" id="total_amount" name="total_amount" class="form-control " readonly>

                                            </div>

                                        </div>



                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-1">Customer Status<sup class="required">*</sup> </label>

                                            <div class="col-lg-5 error-msg">

                                                <div class="row">

                                                  <div class="col-lg-3">

                                                    <div class="custom-control custom-radio mb-3 ">

                                                      <input type="radio" id="regular" name="customer_status" class="custom-control-input customer-status" value="1">

                                                      <label class="custom-control-label" for="regular">Regular</label>

                                                    </div>

                                                  </div>

                                                  <div class="col-lg-3">

                                                    <div class="custom-control custom-radio mb-3  ">

                                                      <input type="radio" id="default" name="customer_status" class="custom-control-input customer-status" value="0">

                                                      <label class="custom-control-label" for="default">Default</label>

                                                    </div>

                                                  </div>

                                                </div>

                                            </div>



                                            <label class="col-form-label col-lg-2">Money Back Amount<sup>*</sup></label>

                                            <div class="col-lg-3">

                                                <input type="text" id="mb_amount" name="mb_amount" class="form-control " readonly="">

                                            </div>



                                            

                                        </div>



                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-2">Money Back Transfer<sup>*</sup></label>

                                            <div class="col-lg-4 error-msg">

                                                <input type="text" id="mb_transfer" name="mb_transfer" class="form-control " readonly>

                                            </div>



                                            <label class="col-form-label col-lg-2">Money Back Interest<sup>*</sup></label>

                                            <div class="col-lg-4 error-msg">

                                                <input type="text" id="mb_inst" name="mb_inst" class="form-control " >

                                            </div>

                                        </div>



        								<div class="form-group row">

                                            <label class="col-form-label col-lg-2">Money Back FD Amount<sup>*</sup></label>

                                            <div class="col-lg-4 error-msg">

                                                <input type="text" id="mbfd_amount" name="mbfd_amount" class="form-control " readonly="">

                                            </div>

                                            <label class="col-form-label col-lg-2">Carry Forward Amount</label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" id="carryforwardamount" name="carryforwardamount" class="form-control" readonly>
                                            </div>
                                        </div>



                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Balance</label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" id="balance" name="balance"   value="@if($record){{$record->current_balance}}@endif" class="form-control " required readonly>
                                            </div>

                                            <label class="col-form-label col-lg-2">SSB A/C No.</label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" id="ssb_ac" name="ssb_ac"  class="form-control " value="@if($record){{$record->ssb_account_number}}@endif" required readonly>
                                            </div>
                                        </div>



                                        <div class="text-right">



                                            <input type="submit" class=" btn btn-primary legitRipple formsubmit" value="Submit">



        									<button type="button" class=" btn btn-gray legitRipple reset" onClick="resetForm()">Reset

                                            </button>



                                        </div>

    							</div>

                            </form>

                        </div>

                    </div>



                    <!-- /basic layout -->



					<div id="transaction_table" style="display:none;">



						<div class="card-header header-elements-inline">



							<h6 class="card-title font-weight-semibold">Transaction List</h6>



							<div class="">



								<button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>



								<button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>



							</div> 



						</div>



						<form method="Post" id="export_form">



						@csrf



							<input type="hidden" name="export_value" id="export_value">



							<input type="hidden" name="export_account_no" id="export_account_no">



						</form>



                        <table id="transaction_list" class="table datatable-show-all" >



                            <thead>



                                <tr>



                                    <th>S/N</th>



                                    <th>Transaction ID.</th>



									<th>Date</th>



									<th>Description</th>



                                    <th>Cheque/Reference Number</th>



                                    <th>Withdrawal</th>



                                    <th>Deposit</th>



                                    <th>Balance</th>                              



                                </tr>







                            </thead> 



    						<tbody>



    						</tbody>



                        </table>



					</div>



                </div>



            </div>



        </div>



    </div>



@stop







@section('script')



    @include('templates.admin.e_investment.partials.script')



@stop