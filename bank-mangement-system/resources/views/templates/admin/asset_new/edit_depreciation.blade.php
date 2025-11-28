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

                        <form action="{!! route('admin.asset.depreciation_save') !!}" method="post"
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
                                <label class="col-form-label col-lg-2">Assets Purchase Date<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="textarea" id="asset_purchase_date" name="asset_purchase_date"
                                        class="form-control " value="{{$asset_purchase_date}}" readonly="true">

                                </div>

                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Assets Name <sup>*</sup></label>

                                <div class="col-lg-4 error-msg">
                                    <input type="hidden" name="head_id" value="{{$account_head_id}}" id="head_id">
                                    <input type="hidden" name="sub_account_head_id" value="{{$sub_account_head_id}}"
                                        id="sub_account_head_id">
                                    <input type="hidden" name="branch_id" value="{{$branch_id}}" id="branch_id">
                                    <input type="hidden" name="asset_id" value="{{$asset_id}}" id="asset_id">
                                    <input type="hidden" name="demand_id" value="{{$demand_id}}" id="demand_id">
                                    <input type="hidden" name="created_at" id="created_at" class="created_at">
                                    <input type="hidden" name="create_application_date" id="create_application_date"
                                        class="create_application_date">
                                    <input type="textarea" name="asset_name" class="form-control"
                                        value="{{$asset_name}}" readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Assets Category<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="textarea" id="asset_category" name="asset_category"
                                        class="form-control " value="{{$asset_category}}" readonly="true">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Total Value of Asset<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="textarea" id="total_asset" name="total_asset" class="form-control "
                                        value="{{$total_asset}}" readonly="true">

                                </div>
                                <label class="col-form-label col-lg-2">Current Assets value<sup>*</sup></label>

                                <div class="col-lg-4">

                                    <input type="textarea" id="current_asset_value" name="current_asset_value"
                                        class="form-control " value={{$current_asset_value}} readonly="true">

                                </div>



                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Depreciation %<sup>*</sup></label>

                                <div class="col-lg-4">

                                    <input type="textarea" id="depreciation_percentage" name="depreciation_percentage"
                                        class="form-control ">

                                </div>
                                <label class="col-form-label col-lg-2">After Depreciation Assets value<sup>*</sup></label>

                                <div class="col-lg-4">

                                    <input type="textarea" id="after_depreciation_asset_value"
                                        name="after_depreciation_asset_value" class="form-control " readonly>

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