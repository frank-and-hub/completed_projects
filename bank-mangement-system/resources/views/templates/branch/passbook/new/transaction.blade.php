@extends('layouts/branch.dashboard')
@section('content')
    <div class="container-fluid mt--6">
        <div class="content-wrapper">
            @if ($accountDetail)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card bg-white">
                            <div class="card-body page-title">
                                <h3 class="">Transaction For :
                                    @if ($code == 'S')
                                        {{ $accountDetail->account_no }}
                                    @else
                                        {{ $accountDetail->account_number }} - {{ $accountDetail->plan->name }}
                                    @endif
                                </h3>
                                @if ($button_show == 1)
                                    <a href="javascript:void(0);" style="float:right" class="btn btn-secondary corrections " id='corrections' data-toggle="modal" data-target="#correction-form">Corrections</a>
                                @endif
                                <a href="{!! route('branch.passbook') !!}" style="float:right" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white shadow">
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data" action="{!! route('branch.transaction_start_new') !!}" id="fillter" name="fillter">
                                @csrf
                                <h3 class="card-title mb-3">Print Fillter</h3>
                                <div class="row">
                                    <div class="col-lg-5">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-5">Transaction ID From<sup class="required">*</sup></label>
                                            <div class="col-lg-7 error-msg ">
                                                <input type="text" name="transaction_id_from" id="transaction_id_from" class="form-control  ">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-5">Transaction ID To<sup class="required">*</sup></label>
                                            <div class="col-lg-7 error-msg">
                                                <input type="text" name="transaction_id_to" id="transaction_id_to" class="form-control  ">
                                                <input type="hidden" name="id" id="id" class="form-control  " value="{{ $accountDetail->id }} ">
                                                <input type="hidden" name="code" id="code" class="form-control  " value="{{ $code }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 text-center">
                                        <div class=" ">
                                            <button type="submit" class="btn btn-primary">Submit<i class="icon-paperplane ml-2"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12" id="print_passbook">
                    <div class="card bg-white shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-flush" style="width: 100%" id="listtansaction">
                                    <thead class="">
                                        <tr>
                                            <th style="width: 10%"> S.No</th>
                                            <th style="width: 10%"> Transaction ID</th>
                                            <th style="width: 10%"> Transaction By</th>
                                            <th style="width: 10%"> Date</th>
                                            <th>Particulars</th>
                                            <th>Cheque No</th>
                                            <th>Withdrawal</th>
                                            <th>Amt. Deposited</th>
                                            <th>Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                    <div class="card-body">
                        <div class="text-center">
                            <h3 class="">Transaction not found</h3>
                            <a href="{!! route('branch.passbook') !!}" style="float:right" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    <div class="modal fade" id="correction-form" tabindex="-1" role="dialog" aria-labelledby="modal-form"
        aria-hidden="true">
        <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document"
            style="max-width: 600px !important; ">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="card bg-white border-0 mb-0">
                        <div class="card-header bg-transparent pb-2ß">
                            <div class="text-dark text-center mt-2 mb-3">Correction Request</div>
                        </div>
                        <div class="card-body px-lg-5 py-lg-5">
                            {{ Form::open(['url' => route('correction.request'), 'method' => 'POST', 'id' => 'renew-correction-form', 'class' => '', 'name' => 'renew-correction-form']) }}
                            {{ Form::hidden('correction_type_id', $iId ?? '', ['id' => 'correction_type_id', 'class' => '']) }}
                            {{ Form::hidden('correction_type', '3', ['id' => 'correction_type', 'class' => '']) }}
                            {{ Form::hidden('companyid', $accountDetail ? $accountDetail->company_id : '', ['id' => 'companyid', 'class' => '']) }}
                            {{ Form::hidden('account_id', $accountDetail ? $accountDetail->id : '', ['id' => 'account_id', 'class' => '']) }}
                            {{ Form::hidden('plan_category_code', $code, ['id' => 'plan_category_code', 'class' => '']) }}
                            {{ Form::hidden('request_date', '', ['id' => 'created_at', 'class' => 'created_at']) }}
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Corrections</label>
                                <div class="col-lg-12">
                                    {{ Form::textarea('corrections', '', ['id' => '', 'class' => 'form-control', 'placeholder' => 'Corrections', 'rows' => '6', 'cols' => '50', 'required' => true]) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <input type="submit" id="submitform" value="Submit" class="btn btn-primary">
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    @include('templates.branch.passbook.partials.tran_listing_script')
@stop
