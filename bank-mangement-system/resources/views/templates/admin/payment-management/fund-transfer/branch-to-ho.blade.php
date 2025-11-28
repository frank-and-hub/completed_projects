@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Fund Transfer Branch to Head Office</h6>
                    </div>
{{--@dd( $data )--}}
                    <div class="card-body">
                        <form action="{{url('admin/profile-update')}}" method="post">
                            @csrf

                            {{--<input type="hidden" name="branch_id" id="branch_id" value="{{ $data->branch_id }}">--}}

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Branch</label>
                                <div class="col-lg-4">
                                    <select name="branch_id" id="branch_id" class="form-control">
                                        @foreach( $branches as $branch)
                                            <option value="{{ $branch['id'] }}" data-branch-code="{{ $branch['branch_code'] }}" {{$data->branch_id == $branch['id'] ? 'selected' : ''}}>{{ $branch['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Select Date<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" id="date" name="date" class="form-control" value="{{ $data->transfer_date_time }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Branch Code</label>
                                <div class="col-lg-4">
                                    <input type="text" name="branch_code" id="branch_code" class="form-control" placeholder="Branch Code" value="{{ $data->branch_code }}" readonly="">
                                </div>
                                <label class="col-form-label col-lg-2">	Loan Daybook Amount </label>
                                <div class="col-lg-4">
                                    <input type="text" name="loan_daybook_amount" id="loan_daybook_amount" class="form-control" value="{{ $data->loan_day_book_amount }}" placeholder="0.00" >
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Transfer Mode <sup>*</sup></label>
                                <div class="col-lg-4">
                                    <select name="transfer_mode" id="transfer_mode" class="form-control">
                                        <option value="">----Select Transfer Mode----</option>
                                        <option value="0" {{ $data->transfer_mode == 0 ? 'selected' : '' }}>Loan</option>
                                        <option value="1" {{ $data->transfer_mode == 1 ? 'selected' : '' }}>Micro</option>
                                    </select>
                                </div>

                                <label class="col-form-label col-lg-2">Micro Daybook Amount <sup>*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="micro_daybook_amount" id="micro_daybook_amount" class="form-control" value="{{ $data->micro_day_book_amount }}" placeholder="0
                                    .00" >
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">	Transfer Amount </label>
                                <div class="col-lg-4">
                                    <input type="text" name="transfer_amount" id="transfer_amount" class="form-control" value="{{ $data->transfer_amount }}" placeholder="0.00" >
                                </div>
                                <label class="col-form-label col-lg-2">	Bank</label>
                                <div class="col-lg-4">
                                    <select name="bank" id="bank" class="form-control">
                                        @foreach( $data->banks as $bank)
                                            <option value="{{ $bank['id'] }}" data-account="{{ $bank['account_number'] }}" {{ $data->head_office_bank_id == $bank['id'] ?
                                            'selected' : ''}}>{{ $bank['title'] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="bank_to_head_office" name="bank_to_head_office" value="{{ $data->head_office_bank_account_number }}"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Upload Bank Slip </label>
                                <div class="col-lg-4">
                                    @if( $data->file )
                                        <img src="{{ config('app.url') }}/{{$data->file->file_path}}" style="width:200px;height:200px;"/>
                                    @endif
                                    <span class="signature"><input type="file" class="" name="bank_slip"/></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn bg-dark">Update<i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.payment-management.fund-transfer.partials.script')
@stop
