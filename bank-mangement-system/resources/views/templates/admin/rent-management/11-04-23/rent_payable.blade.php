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
                        <form action="#" method="post" enctype="multipart/form-data" id="rent_payable_filter" name="rent_payable_filter">
                        @csrf
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Month <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="rent_month" name="rent_month">
                                                    <option value=""  >----Select Month----</option> 
                                                 <!--   @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor -->

                                                    {{ $last_month= 12}}
                                                        {{ $now_month = 1}}

                                                        @for ($i = $now_month; $i <= $last_month; $i++)
                                                        <?php
                                                        $monthNum  = $i;
                                                        $monthName = date('F', mktime(0, 0, 0, $monthNum, 10));
                                                        ?>
                                                            <option value="{{ $i }}">{{ $monthName }}</option>
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
                                                    <option value=""  >----Select Year----</option> 
                                                  <!--  @for($i = 2000; $i <= 2050; $i++)
                                                    <option value="{{ $i }}"  >{{ $i }}</option> 
                                                    @endfor-->

                                                    {{ $last= date('Y')-100 }}
                                                        {{ $now = date('Y') }}

                                                        @for ($i = $now; $i >= $last; $i--)
                                                            <option value="{{ $i }}">{{ $i }}</option>
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
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchRentPayableForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetRentPayableForm()" >Reset </button>
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
                        <h6 class="card-title font-weight-semibold">Rent Payable</h6>
                        <div class="">
                            <form action="{{route('admin.rentpayable.transfer')}}" method="post" name="transfer-rent-payable" id="transfer-rent-payable">
                                @csrf
                                <input type="hidden" name="selected_records" id="selected_records">
                                <input type="hidden" name="pending_records" id="pending_records">
                                <button type="submit" class="btn bg-dark">Transfer<i class="icon-paperplane ml-2"></i></button>
                            </form>
                        </div>
                    </div>
                    <table class="table datatable-show-all" id="rent-payable-table">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="5%"><input type="checkbox" name="select_all" id="select_all"></th>
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
                                <th width="10%">Action</th>
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
