@extends('templates.admin.master')



@section('content')

    <style>

        .required {

            color: red;

        }

    </style>

    
    <div class="content">

        <div class="row">

            <div class="col-lg-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Associate Commission ( Company ) Filter</h6>

                    </div>

                    <div class="card-body">

                        <form action="#" method="post" enctype="multipart/form-data" id="associateCommissionDetailFilter"

                            name="associateCommissionDetailFilter">

                            @csrf

                            <div class="form-group row">

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Company <sup

                                                class="required">*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            <select class="form-control valid" name="company_id" id="company_id"

                                                title="Please Select Company" required="" aria-invalid="false">

                                                <option value="0"> All Company </option>

                                                @foreach ($AllCompany as $key => $item)

                                                    <option value="{{ $key }}">{{ $item }}</option>

                                                @endforeach

                                            </select>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Associate Code </label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" name="associate_code" id="associate_code"

                                                class="form-control">

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Ledger Month<sup

                                                class="required">*</sup></label>

                                        <div class="col-lg-12 error-msg">



                                            <select class="form-control" name="ledger_month" id="ledger_month"

                                                title="Please Select Month" required="">

                                                <option value="">--Please Select Month -- </option>

                                                @foreach ($detailMonths as $item)

                                                    <option value="{{ $item->id }}" data-month="{{ $item->month }}"

                                                        data-year="{{ $item->year }}" data-company="{{$item->company_id}}">{{ getMonthName($item->month) }}

                                                        {{ $item->year }}</option>

                                                @endforeach

                                            </select>

                                            <div class="input-group">

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <input type="hidden" name="month" id="month">

                                <input type="hidden" name="year" id="year">



                                <div class="col-lg-12 text-right page">

                                    <label class="col-form-label col-lg-12"> </label>

                                    <input type="hidden" name="companyComission_export" id="companyComission_export"

                                        value="">

                                    <input type="hidden" name="is_search" id="is_search" value="yes">

                                    <button type="button" class=" btn bg-dark legitRipple"

                                        onClick="searchCommissionDetailForm()">Submit</button>

                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form"

                                        onClick="resetCommissionDetailForm()">Reset </button>

                                </div>

                            </div>





                        </form>

                    </div>

                </div>

            </div>

        </div>

        <div class="row">



            <div class="col-lg-12" id="hidecommisiontabledata" style="display:none;">

                <div class="card bg-white shadow">

                    <div class="card-header bg-transparent header-elements-inline">

                        <h3 class="mb-0 text-dark">Associate Commission ( Company )</h3>

                        <div class="">

                            <button type="button" class="btn bg-dark legitRipple leaserDetail ml-2" data-extension="0"

                                style="float: right;">Export xslx</button>

                        </div>

                    </div>



                    <div class="table-responsive">

                        <table class="table table-flush" style="width: 100%" id="transfer-listing-detail">

                            <thead class="">

                                <tr>

                                    <th>S/N</th>

                                    <th>Company Name</th>

                                    <th>Ledger Month</th>

                                    <th>Associate Code</th>

                                    <th>Associate Name</th>

                                    <th>Associate Carder</th>

                                    <th>PAN No </th>

                                    <th>Total Amount </th>

                                    <th>TDS Amount </th>

                                    <th>Final Payable Amount</th>

                                    <th>Total Collection </th>

                                    <th>Fuel Amount </th>

                                    <th>SSB Account No</th>

                                    <th>Status</th>

                                    <th>Created</th>

                                    <th>Action</th>

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

    @include('templates.admin.associate.partials.associate_comission_company_script')

@stop

{{-- uat --}}

