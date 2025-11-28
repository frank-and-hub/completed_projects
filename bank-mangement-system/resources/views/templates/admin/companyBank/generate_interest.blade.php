@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Basic layout-->
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
            <div class="card">
                <div class="card-header header-elements-inline">
                    <div class="card-body">
                        <form method="post" action="{!! route('admin.save.interest') !!}" id="company_fd_interest">
                            @csrf
                            <input type="hidden" id="create_application_date" name="create_application_date"
                                class="form-control create_application_date" readonly="true" autocomplete="off">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Company Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="company_id" id="company_id" title="Please
                                Select Company" required="">
                                        <option value="{{ ($detail->company_id) }}"> {{ $detail->companies->name }}
                                        </option>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">FD No.<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="fd_no" class="form-control" value="{{$detail->fd_no}}"
                                        readonly>
                                </div>

                                
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2"> Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="date" name="date" class="form-control  "
                                        value="{{ old('date') }}" autocomplete="off" readonly>
                                        <input type="hidden" id="create_application_date" name="create_application_date"
                                         class="form-control create_application_date" readonly="true" autocomplete="off">
                                        <input type="hidden" id="fd_create_date" name="fd_create_date"
                                        class="form-control" value="{{ date('d-m-Y', strtotime($detail->date)) }}">
                                </div>
                                <label class="col-form-label col-lg-2">Branch <sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="hidden" name="branch_id" class="form-control" value="{{$branch->id}}"
                                        readonly>
                                    <input type="text" name="branch" class="form-control" value="{{$branch->name}}"
                                        readonly>
                                        <input type="hidden" name="bond_id" class="form-control" value="{{$detail->id}}"
                                        readonly>
                                </div>
                                
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2"> Tds Amount<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="tds_amount" name="tds_amount" class="form-control "
                                        value="{{ old('tds_amount') }}">
                                </div>
                                
                                <label class="col-form-label col-lg-2">Current Balance<sup>*</sup></label>
                                @php
                                $amount = isset($detail->getCompanyBoundTransaction->tds_amount) ?
                                $detail->getCompanyBoundTransaction->tds_amount : 0;
                                @endphp
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="current_balance" name="current_balance" class="form-control "
                                        value="{{$detail->current_balance  - $amount}}" readonly>
                                </div>



                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Remark</label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="remark" name="remark" class="form-control "
                                        value="{{ old('remark') }}">
                                </div>
                                <label class="col-form-label col-lg-2">Interest Amount<sup>*</sup></label>
                                <div class="col-lg-4  error-msg">
                                    <input type="text" id="interest_amount" name="interest_amount" class="form-control "
                                        value="{{ old('interest_amount') }}">
                                </div>

                            </div>

                            <div class="form-group row">
                            <label class="col-form-label col-lg-2">Select TDS Receivable Year<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="tds_receive_year" id="tds_receive_year">
                                        <option value=''>--- Please Select TDS RECEIVABLE ---</option>
                                        @foreach($tds_heads as $tds)
                                        <option value="{{$tds->head_id}}">{{$tds->sub_head}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Select Interest Type<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="interest_type" id="interest_type">
                                        <option value=''>--- Please Select Interest Type ---</option>
                                        <option value="0">Bank Account</option>
                                        <option value="1">FDR Bank</option>

                                    </select>
                                </div>
                            </div>

                            <div class="bank_details" style="display:none;">

                                <h3 class="card-title">Received Bank</h3>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select class="form-control" name="received_bank_name" id="received_bank_name">
                                            <option value=''>--- Please Select Bank ---</option>
                                            @foreach($banks as $bank)
                                            <option value="{{$bank->id}}">{{$bank->bank_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Bank Account<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select class="form-control" name="received_bank_account"
                                            id="received_bank_account">
                                            <option value=''>--- Please Select Bank Account ---</option>
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <div class="text-right">
                                <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /basic layout -->
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@include('templates.admin.companyBank.partials.interest_script')
@endsection