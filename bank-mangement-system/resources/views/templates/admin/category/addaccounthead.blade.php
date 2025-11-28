@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Create Account Head</h6>
                    </div>
                    <form action="{{url('admin/save-account-head')}}" method="post" id="accounthead-create">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                        <div class="modal-body">
                            <div class="form-group row">
                                <!-- <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select account type</label>
                                    <div class="col-lg-10">
                                        <select name="accounttype" id="accounttype" class="form-control">
                                            <option value="0">Expenses</option>
                                            <option value="1">Liability</option>
                                            <option value="2">Bank</option>
                                            <option value="3">Inflow</option>
                                        </select>
                                    </div>
                                </div> -->

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Main Head</label>
                                    <div class="col-lg-10">
                                        <select name="accounttype" id="accounttype" class="form-control">
                                            <option value="2" selected="">Assets</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select Sub Head</label>
                                    <div class="col-lg-10">
                                        <select name="subhead" id="subhead" class="form-control">
                                            <option value="27" selected="">BANK ACCOUNTS</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Account Head Name</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="account_head_name" id="account_head_name" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6 account-number">
                                    <label class="col-form-label col-lg-12">Account Number</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="account_number" id="account_number" class="form-control">
                                        <span class="error" id="account-number-error"></span>
                                        <input type="hidden" name="account-exist" id="account-exist" value="">
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.category.partials.script')
@stop
