@extends('templates.admin.master')



@section('content')



    <div class="loader" style="display: none;"></div>



    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <!-- Basic layout-->

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <div class="card-body" >

                            <form method="post" action="{!! route('admin.update.bank_account') !!}" id="bank_account">

                                 @csrf

                                <input type="hidden" name="head_id" value="{{$bank_account->account_head_id}}">
                                <input type="hidden" name="id" value="{{$bank_account->id}}">

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                       <input type="text" name="bank_name" class="form-control" value="{{getSamraddhBank($bank_account->bank_id)->bank_name}}">

                                    </div>

                                  <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="branch_name" name="branch_name" class="form-control " value="{{$bank_account->branch_name}}">

                                    </div>

                                </div>

                                

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2"> Account Number<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="account_number" name="account_number" class="form-control " value="{{$bank_account->account_no}}">

                                    </div>

                                    <label class="col-form-label col-lg-2">Ifsc Code<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                        <input type="text" id="ifsc" name="ifsc" class="form-control " value="{{$bank_account->ifsc_code}}">

                                    </div>

                                </div>

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Address</label>

                                    <div class="col-lg-4">

                                        <input type="text" id="address" name="address" class="form-control " value="{{$bank_account->address}}">

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

    @include('templates.admin.bank_account.partials.script')

@stop