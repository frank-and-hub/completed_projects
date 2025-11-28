@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Edit Account Head</h6>
                    </div>
                    <form action="{{route('admin.accounthead.update')}}" method="post" id="editaccounthead-create">
                        @csrf
                        <input type="hidden" name="accountheadid" id="accountheadid" value="{{ $accounthead->id }}">
                        <div class="modal-body">

                            <div class="form-group row">
                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Select account type</label>
                                    <div class="col-lg-10">
                                        <select name="accounttype" id="accounttype" class="form-control">
                                            <option @if($accounthead->account_type == 0) selected @endif value="0">Expenses</option>
                                            <option @if($accounthead->account_type == 1) selected @endif value="1">Liability</option>
                                            <option @if($accounthead->account_type == 2) selected @endif value="2">Bank</option>
                                            <option @if($accounthead->account_type == 3) selected @endif value="3">Inflow</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 account-number"  @if($accounthead->account_type != 2) style="display:none;" @endif>
                                    <label class="col-form-label col-lg-12">Account Number</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="account_number" id="edit_account_number" class="form-control" value="{{ $accounthead->account_number }}">
                                        <span class="error" id="account-number-error"></span>
                                        <input type="hidden" name="account-exist" id="account-exist" value="0">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label class="col-form-label col-lg-12">Account Head Name</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="account_head_name" id="account_head_name" class="form-control" value="{{ $accounthead->title }}">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark">Update<i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@include('templates.admin.category.partials.script')
@stop
