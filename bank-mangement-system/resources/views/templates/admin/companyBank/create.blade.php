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
                        <form method="post" action="{!! route('admin.save.company_fd') !!}" id="company_fd"
                            enctype="multipart/form-data" name="company_fd">
                            @csrf
                            <input type="hidden" id="create_application_date" name="create_application_date"
                                class="form-control create_application_date" readonly="true" autocomplete="off">


                            <div class="form-group row">
                                @php
                                $dropDown = $company;
                                $filedTitle = 'Company';
                                $name = 'company_id';
                                @endphp

                                @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please
                                Select Company','placeHolder2'=>'Please Select Branch'])
                            </div>

                            <div class="form-group row">
                                 
                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="bank_name" id="bank_name" class="form-control"
                                        value="{{ old('bank_name') }}">
                                </div>

                                <input type="hidden" name="company_create_date" id="company_create_date"
                                        class="form-control" value=" " readonly>
                                        <label class="col-form-label col-lg-2">FD No.<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="fd_no" id="fd_no" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">

                                
                                <label class="col-form-label col-lg-2"> Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="date" name="date" class="form-control  " autocomplete="off"
                                        readonly value="">
                                    <input type="hidden" id="bill_reate_application_date"
                                        name="bill_reate_application_date" class="form-control created_at ">
                                </div>

                                <label class="col-form-label col-lg-2">Maturity Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="maturity_date" id="maturity_date" class="form-control"
                                        value="" autocomplete="off" readonly>
                                </div>

                            </div>
                            <div class="form-group row">
                                
                                <label class="col-form-label col-lg-2"> Amount<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="amount" name="amount" class="form-control "
                                        value="{{ old('amount') }}">
                                </div>
                                <label class="col-form-label col-lg-2">Rate Of Interest<sup>*</sup></label>
                                <div class="col-lg-4  error-msg">
                                    <input type="text" id="roi" name="roi" class="form-control "
                                        value="{{ old('roi') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                               
                                <label class="col-form-label col-lg-2">Remark<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="remark" name="remark" class="form-control "
                                        value="{{ old('remark') }}">
                                </div>

                             

                                <label class="col-form-label col-lg-2">File Upload</label>
                                <div class="col-lg-4 error-msg">
                                    <input type='file' id="file_upload" name="file_upload" class="form-control"
                                        value="{{ old('file_upload') }}">

                                </div>
                            </div>


                            <h3 class="card-title">Received Bank</h3>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="received_bank_name" id="received_bank_name">
                                        <option value=''>--- Please Select Bank ---</option>

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

                            <div class="form-group row">
                            <label class="col-form-label col-lg-2">Bank Account Balance</label>
                                <div class="col-lg-4 error-msg">
                                <input type="text" id="bank_account_balance" name="bank_account_balance" class="form-control "
                                        value="0.00" readonly>
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
@endsection
@section('script')
@include('templates.admin.companyBank.partials.script')
@endsection