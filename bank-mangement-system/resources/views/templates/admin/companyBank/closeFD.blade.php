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
                        <form method="post" action="{!! route('admin.fd.close.permanent') !!}" id="company_fd_close" name="company_fd_close">
                            @csrf
                            <input type="hidden" name="branch_id" class="form-control" value="{{$branch->id}}" readonly>
                            <input type="hidden" name="currentdate" class="create_application_date" id="currentdate">
                            <input type="hidden" id="id" name="id" class="form-control " value="{{$detail->id}}"
                                readonly="true" autocomplete="off">
                            <input type="hidden" name="company_id" class="form-control"
                                value="{{ ($detail->company_id) }}" readonly>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Company Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="company_id" id="company_id" title="Please
                                Select Company" required="">
                                        <option value="{{ ($detail->company_id) }}"> {{ $detail->companies->name }} </option>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="bank_name" class="form-control"
                                        value="{{ ($detail->bank_name) }}" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">FD No.<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="fd_no" class="form-control" value="{{ ($detail->fd_no) }}"
                                        readonly>
                                </div>

                            </div>
                            <div class="form-group row">

                                <label class="col-form-label col-lg-2"> Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="date" name="date" class="form-control  minvalDate"
                                        value="{{date('d/m/Y', strtotime($detail->date))}}" readonly>
                                    <input type="hidden" id="created_at" name="created_at"
                                        class="form-control created_at ">
                                </div>
                                <label class="col-form-label col-lg-2">Current Balance<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="current_balance" name="current_balance" class="form-control "
                                        value="{{$totalAmount}}" readonly>
                                </div>

                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Maturity Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="maturity_date1" id="maturity_date1" class="form-control"
                                        value="{{date('d/m/Y', strtotime($detail->maturity_date))}}" readonly>
                                    <p class="text-danger msg"></p>
                                </div>

                                <label class="col-form-label col-lg-2"> Amount<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="amount" name="amount" class="form-control "
                                        value="{{ ($detail->amount) }}" readonly>
                                </div>

                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Rate Of Interest<sup>*</sup></label>
                                <div class="col-lg-4  error-msg">
                                    <input type="text" id="roi" name="roi" class="form-control "
                                        value="{{ ($detail->roi) }}" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Remark</label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="remark" name="remark" class="form-control "
                                        value="{{ ($detail->remark) }}">
                                </div>

                            </div>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">File Upload</label>
                                <div class="col-lg-4 error-msg">
                                    <input type="file" id="file_upload" name="file_upload" class="form-control"
                                        value="{{($detail->file) }}" readonly>

                                </div>
                            </div>



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
                            @if($detail->status == 0)

                            <div class="text-right">
                                <input type="submit" name="submitform" value="Close FD" class="btn btn-success submit">
                            </div>
                            @endif
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
@include('templates.admin.companyBank.partials.script')
@endsection