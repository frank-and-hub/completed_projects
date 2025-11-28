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

                        <form action="{!! route('admin.brs.bank_charge_save') !!}" method="post" enctype="multipart/form-data" name="brs_bank_charge" id="brs_bank_charge">

                            @csrf

                            <input type="hidden" name="created_at" id="created_at" class="created_at">
                            <input type="hidden" name="create_application_date" id="create_application_date" class="create_application_date">

                            <div class="form-group row">
                            @include('templates.GlobalTempletes.role_type',[
                                    'dropDown'=> $company,
                                    'name'=>'company_id',
                                    'apply_col_md'=>true,
                                    'filedTitle' => 'Company Name'
                                    ]) 
                                <div class="col-lg-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="bank" id="bank" class="form-control">
                                                <option value=''>-- Please Select Bank --</option>                                              

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Account<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" name="bank_account" id="bank_account">
                                                <option value=''>--- Please Select Bank Account ---</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Date<sup class="required">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="date" id="date" class="form-control " readonly >
                                            <input type="hidden" name="company_register_date" id="company_register_date" class="form-control company_register_date " readonly >
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                        </div>
                                    </div>
                                </div>
                              
                                <div class="col-lg-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Available Balance<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="bank_balance" id="bank_balance" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Title<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" name="bank_charge" id="bank_charge">
                                                <option value=''>--- Please Select Bank Charge ---</option>
                                             {{-- @foreach($head as $val)
                                                <option value="{{$val->head_id}}">{{$val->sub_head}}</option>
                                                @endforeach --}}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            
                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Particular<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="textarea" name="description" id="description" class="form-control">

                                </div>

                                <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="amount" id="amount" class="form-control">

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

@include('templates.admin.brs.partials.script_bank')

@stop