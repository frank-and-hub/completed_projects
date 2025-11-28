@extends('templates.admin.master')



@section('content')


<style>
    sup{
        color:red;
    }
</style>
<div class="loader" style="display: none;"></div>



<div class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Basic layout-->
            <div class="card">
                <div class="card-header header-elements-inline">
                    <div class="card-body">
                        <form action="{!! route('admin.asset.asset_save') !!}" method="post"
                            enctype="multipart/form-data" id="edit_asset" name="edit_asset">
                            @csrf

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Company Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                <input type="textarea" id="company_name" name="company_name" class="form-control "
                                        value="{{$company_name}}" readonly="true">
                                <input type="hidden" id="company_id" name="company_id" class="form-control "
                                        value="{{$company_id}}" readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="textarea" id="branch_name" name="branch_name" class="form-control "
                                        value="{{$branch_name}}" readonly="true">
                                    <input type="hidden" name="branch_id" value="{{$branch_id}}" id="branch_id">
                                </div>

                            </div>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Demand Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="textarea" id="demand_date" name="demand_date" class="form-control "
                                        value="{{$demand_date}}" readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Advice Date<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="advice_date" name="advice_date" class="form-control "
                                        value={{$advice_date}} readonly="true">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Account Head<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="hidden" name="head_id" value="{{$account_head_id}}" id="head_id">
                                    <input type="hidden" name="asset_id" value="{{$asset_id}}" id="asset_id">
                                    <input type="hidden" name="demand_id" value="{{$demand_id}}" id="demand_id">

                                    <input type="hidden" name="created_at" id="created_at" class="created_at">
                                    <input type="hidden" name="create_application_date" id="create_application_date"
                                        class="create_application_date">
                                    <input type="text" name="account_head_name" class="form-control"
                                        value="{{getAcountHeadNameHeadId($account_head_id)}}" readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Sub Account Head<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="hidden" id="sub_account_head_id" name="sub_account_head_id"
                                        class="form-control " value="{{$sub_account_head_id}}">
                                    <input type="textarea" id="sub_account_head_name" name="sub_account_head_name"
                                        class="form-control " value="{{getAcountHeadNameHeadId($sub_account_head_id)}}"
                                        readonly="true">
                                </div>


                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Party name<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="party_name" name="party_name" class="form-control "
                                        value={{$party_name}} readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Mobile no.<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="mobile_no" name="mobile_no" class="form-control "
                                        value={{$mobile_no}} readonly="true">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Bill no.<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="bill_no" name="bill_no" class="form-control "
                                        value="{{$bill_no}}" readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Bill copy<sup>*<sup></label>
                                <div class="col-lg-4">

                                    <a href="{{$bill_copy_path}}" target="_blank">{{$bill_copy}}</a>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Old Status<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="old_status" name="old_status" class="form-control "
                                        value={{$old_status}} readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Status <sup>*</sup></label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="new_status">
                                        <option value="">---Please Select Status --- </option>
                                        <option value="1">Damage</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Amount<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="amount" name="amount" class="form-control"
                                        value={{number_format((float) $amount, 2, '.','')}} readonly="true">
                                </div>

                                <label class="col-form-label col-lg-2">Current Amount<sup>*<sup></label>
                                <div class="col-lg-4">
                                    <input type="textarea" id="current_balance" name="current_balance"
                                        class="form-control " value={{$current_balance}} readonly="true">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Remark<sup>*<sup></label>
                                <div class="col-lg-10">
                                    <textarea class="form-control " name="remark" id="remark"></textarea>
                                </div>

                            </div>

                            <div class="text-right">
                                <input type="submit" name="submitform" value="Update" class="btn btn-primary submit">
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
@include('templates.admin.asset_new.partials.script')
@stop