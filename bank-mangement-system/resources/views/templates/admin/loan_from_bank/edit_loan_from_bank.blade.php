@extends('templates.admin.master')

@section('content')

<div class="loader" style="display: none;"></div>
<div class="content">

    <div class="row">
        <div class="col-md-12">
            <!-- Basic layout-->
            <div class="card">
                <div class="card-header header-elements-inline">
                    <div class="card-body">
                        <form method="post" action="{!! route('admin.loan_from_bank.update') !!}" id="loan_from_bank">
                            @csrf
                            <input type="hidden" name="head_id" value={{$data->account_head_id}}>
                            <input type="hidden" name="id" value={{$data->id}}>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Company Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="company" class="form-control" readonly value="{{$data->company->name}}">
                                </div>
                                <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="branch_name" name="branch_name" class="form-control " readonly value="{{$data->branch_name}}">
                                </div>
                            </div>
                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="bank_name" class="form-control" value="{{$data->bank_name}}">
                                </div>
                                <label class="col-form-label col-lg-2">Address<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="address" class="form-control" value="{{$data->address}}">

                                </div>



                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Start Date<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="start_date" name="start_date" class="form-control" value="{{date("d/m/Y", strtotime(convertDate($data->emi_start_date)))}}" readonly>
                                    <input type="hidden" id="created_at" name="created_at" class="form-control created_at ">
                                    <input type="hidden" id="create_application_date" name="create_application_date" class="form-control create_application_date ">

                                </div>
                                <label class="col-form-label col-lg-2">End Date<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="end_date" id="end_date" class="form-control" value="{{date("d/m/Y", strtotime(convertDate($data->emi_end_date)))}}" readonly>

                                </div>


                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Emi Amount<sup>*</sup></label>
    
                                <div class="col-lg-4 error-msg">
    
                                    <input type="text" id="emi_amount" name="emi_amount" class="form-control " value="{{$data->emi_amount}}">
    
                                </div>
                                <label class="col-form-label col-lg-2">Loan Amount</label>
                                <div class="col-lg-4  error-msg">
                                    <input type="text" id="loan_amount" name="loan_amount" class="form-control " value="{{number_format((float) $data->loan_amount, 2, '.', '')}}" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Loan Account Number<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="loan_account_number" name="loan_account_number" class="form-control @error('loan_account_number') is-invalid @enderror" value="{{$data->loan_account_number}}">
                                    @error('loan_account_number')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror


                                </div>
                                <label class="col-form-label col-lg-2">Loan Interest Rate<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="loan_interest_rate" name="loan_interest_rate" class="form-control " value="{{number_format((float) $data->loan_interest_rate, 2, '.', '')}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Number of Emi</label>
                                <div class="col-lg-4">
                                    <input type="text" id="no_of_emi" name="no_of_emi" class="form-control " value="{{$data->number_of_emi}}">
                                </div>

                                <label class="col-form-label col-lg-2"> Type<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    @if($data->loan_type==0)
                                    <input type="hidden" class="form-control" value="{{$data->loan_type}}" name="head_type" readonly>
                                    <input type="text" class="form-control" value="Secure Loan" name="loan_type" readonly>
                                    @elseif($data->loan_type == 1)
                                    <input type="hidden" class="form-control" value="{{$data->loan_type}}" name="head_type" readonly>
                                    <input type="text" class="form-control" value="In Secure Loan" name="loan_type" readonly>
                                    @endif
                                </div>


                            </div>
                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Remark<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="remark" name="remark" class="form-control " value="{{$data->remark}}">
                                </div>


                            </div>

                            <h3 class="card-title">Received Bank</h3>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="received_bank_name" id="received_bank_name">
                                        <!-- <option value=''>--- Please Select Bank ---</option> -->
                                        @foreach($banks as $bank)

                                        <option value="{{ $bank->id }}" {{$data->received_bank== $bank->id ? 'selected' : ''}}>{{ $bank->bank_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Bank Account<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">

                                    <select class="form-control" name="received_bank_account" id="received_bank_account">
                                        @foreach($banks as $value)
                                        @if($value['bankAccount'])
                                        <option class="{{ $value->id }}-received-account received-account" @if($data->received_bank_account === $value['bankAccount']['account_no']) selected @else style="display: none;" @endif>{{ $value['bankAccount']['account_no'] }}</option>
                                        @endif
                                        @endforeach
                                    </select>
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
@stop
@section('script')
@include('templates.admin.loan_from_bank.partials.script')
@stop