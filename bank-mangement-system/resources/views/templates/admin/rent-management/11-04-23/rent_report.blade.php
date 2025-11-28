@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="rent_report_filter" name="rent_payable_filter">
                        @csrf
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_branch" name="rent_branch">
                                                    <option value=""  >----Select----</option> 
                                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Month <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_month" name="rent_month">
                                                    <option value=""  >----Select----</option> 
                                                    @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Year <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_year" name="rent_year">
                                                    <option value=""  >----Select----</option> 
                                                    @for($i = 2000; $i <= 2050; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Rent Type <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_type" name="rent_type">
                                                    <option value=""  >----Select----</option> 
                                                    @foreach( App\Models\SubAccountHeads::pluck('title', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="loan_recovery_export" id="loan_recovery_export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple filter-report" onClick="searchRentReportForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple filter-report" id="reset_form" onClick="resetRentReportForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Transfer Amount</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.rentpayable.transferamount')}}" method="post" id="transferr_rent_amount" name="transferr_rent_amount">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="rentIds" id="rentIds" value="{{ $report }}">
                            <div class="row">
                               <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Section Amount Mode<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="0">SSB</option>
                                                    <option value="1">Bank</option> 
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Owner SSB account<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="owner_ssb_account" id="owner_ssb_account" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="col-md-4 bank-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Bank <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="bank" name="bank">
                                                    <option value=""  >----Select----</option> 
                                                   {{-- @foreach($acountheads as $key => $value)
                                                    <option data-title="{{ $value->title }}" data-account-number="{{ $value->account_number }}" value="{{ $value->id }}">{{ $value->title }}</option>
                                                    @endforeach --}}
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 bank-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Name<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="bank_name" id="bank_name" class="form-control" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 bank-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Account Number<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="bank_account_number" id="bank_account_number" class="form-control" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 bank-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Mode <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="mode" name="mode">
                                                    <option value=""  >----Select----</option> 
                                                    <option value="2"  >Cheque</option> 
                                                    <option value="3"  >Online</option> 
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 cheque-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cheque Number<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="cheque_number" id="cheque_number" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">UTR number / Transaction Number<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="utr_number" id="utr_number" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Amount<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="amount" id="amount" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">RTGS/NEFT Charge <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="neft_charge" id="neft_charge" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-section" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Total amount  <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="total_amount" id="total_amount" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <button type="submit" class=" btn bg-dark legitRipple" >Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Rent Report</h6>
                        <div class="">
                        
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            
                        </div>
                    </div>
                    <table class="table datatable-show-all" id="rent-report-table">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Branch</th>
                                <th width="10%">Rent Type</th>
                                <th width="10%">Period From</th>
                                <th width="10%">Period To</th>
                                <th width="5%">Address</th>
                                <th width="5%">Owner Name</th>
                                <th width="5%">Owner Mobile Number </th>
                                <th width="5%">Owner Pan Card</th>
                                <th width="10%">Owner Aadhar Card</th>
                                <th width="10%">Owner SSB account</th>
                                <th width="10%">Bank name</th>
                                <th width="10%">Bank account Number</th>
                                <th width="10%">IFSC code</th>
                                <th width="10%">Security amount </th>
                                <th width="10%">Rent</th>
                                <th width="10%">Yearly Increment</th>
                                <th width="10%">Office Square feet area</th>
                                <th width="10%">Employee Code</th>
                                <th width="10%">Authorized Employee name</th>
                                <th width="10%">Authorized Employee Designation</th>
                                <th width="10%">Mobile Number</th>
                                <th width="10%">Rent Agreement</th>
                                <th width="10%">Agreement Status</th>
                                <!-- <th width="10%">Action</th> -->
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
@include('templates.admin.rent-management.partials.script')
@endsection
