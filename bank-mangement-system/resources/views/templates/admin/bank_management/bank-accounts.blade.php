@extends('templates.admin.master')

@section('content')

@php
    $dropDown = $company;
    $bankDropdown = $bank;
    $Title ="Company Name";
    $bankTitle = "Bank Name";
    $name = "company_id";
    $bankName = "bank_id";
    $apply_col_md = "1";
@endphp
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                        <a href="" data-toggle="modal" data-target="#bankAccountModel" id="add_bank"><i class="fa fa-plus"></i> Add Account</a>
                    </div> 
                    <div class="card-body">
                        {{ Form::open(['url'=>'','method'=>'POST','name'=>'searchForm','id'=>'searchForm','class'=>'searchForm']) }}
                            <div class="form-group row">
                                <div class="col-lg-6 dropDown">
                                    @include('templates.GlobalTempletes.role_type',['dropDown'=>$dropDown,'filedTitle'=>$Title,'name'=>'searchCompanyId','id'=>'searchCompanyId'])
                                </div>
                                <div class="col-lg-6 dropDown">
                                    @include('templates.GlobalTempletes.role_type',['dropDown'=>$bankDropdown,'filedTitle'=>$bankTitle,'name'=>'searchBankId','id'=>'searchBankId'])           
                                </div>
                            </div>
                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary mb-3 float-right','type'=>'submit','id'=>'search','style'=>'width:auto;']) }}
                        {{ Form::close() }}
                    </div>
                </div>

                <!--Model Start-->
                <div class="modal fade" id="bankAccountModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title mb-3" id="bankAccountModelLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <!--<div class="modal-body">-->
                                {{ Form::open(['url'=>'','method'=>'POST','name'=>'bankAccountForm','id'=>'bankAccountForm','class'=>'bankAccountForm']) }}
									<div class="card mb-0">
										<div class="card-body">
											<div class="form-group row">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        {{Form::hidden("account_id",'',['class'=>'account_id'])}}
                                                        {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                                        <div class=" col-lg-12  error-msg">
                                                            @include('templates.GlobalTempletes.role_type',['dropDown'=>$dropDown,'filedTitle'=>$Title,'name'=>'accountCompanyId','id'=>'accountCompanyId','class'=>$apply_col_md  ])
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                    @include('templates.GlobalTempletes.role_type',['dropDown'=>$bankDropdown,'filedTitle'=>$bankTitle,'name'=>'accountBankId','id'=>'accountBankId','class'=>$apply_col_md  ])           
                                                </div>
											</div>

                                            <div class="form-group row">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <div class=" col-lg-12  error-msg">
                                                        {{ Form::label('account_no','Account No <span style="color: red;">*</span>','',false) }}
                                                        {{ Form::text("account_no",'',["class"=>"form-control account_no"]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                        {{ Form::label('ifsc_code','IFSC Code <span style="color: red;">*</span>','',false) }}
                                                        {{ Form::text("ifsc_code","",["class"=>"form-control ifsc_code"]) }}
                                                </div>
											</div>

                                            <div class="form-group row">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <div class=" col-lg-12  error-msg">
                                                        {{ Form::label("branch_name","Branch Name <span style='color: red;'>*</span>",'',false) }}
                                                        {{ Form::text("branch_name",'',["class"=>"form-control branch_name"]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                        {{ Form::label("address",'Address <span style="color: red;">*</span>','',false) }}
                                                        {{ Form::textarea("address","",["class"=>"form-control address","style"=>"height: 100px;"]) }}
                                                </div>
											</div>
                                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary w-25 float-right','type'=>'submit','id'=>'create']) }}
										</div>
									</div>
								{{Form::close()}}                
                        </div>
                    </div>
                </div>
                <!--Model End-->

                <!--Edit Model Start-->
                <div class="modal fade" id="editBankAccountModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title mb-3" id="editBankAccountModelLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <!--<div class="modal-body">-->
                                {{ Form::open(['url'=>'','method'=>'POST','name'=>'editBankAccountForm','id'=>'editBankAccountForm','class'=>'editBankAccountForm']) }}
									<div class="card mb-0">
										<div class="card-body">
											<div class="form-group row">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        {{Form::hidden("account_id",'',['class'=>'accountId'])}}
                                                        {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                                        <div class=" col-lg-12  error-msg">
                                                            {{ Form::label("companyId","Company Name") }}
                                                            {{ Form::text("companyId",'',['class'=>'form-control companyId','readonly'=>true]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                {{Form::label("bankName","Bank Name")}}
                                                {{Form::text("bankName",'',['class'=>'form-control bankName','readonly'=>true])}}
                                                </div>
											</div>

                                            <div class="form-group row">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <div class=" col-lg-12  error-msg">
                                                        {{ Form::label("account_no","Account No <span style='color: red;'>*</span>","",false) }}
                                                        {{ Form::text("account_no",'',["class"=>"form-control accountNo"]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                        {{ Form::label("ifsc_code",'IFSC Code <span style="color: red;">*</span>','',false) }}
                                                        {{ Form::text("ifsc_code","",["class"=>"form-control ifscCode"]) }}
                                                </div>
											</div>

                                            <div class="form-group row">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <div class=" col-lg-12  error-msg">
                                                        {{ Form::label("branch_name","Branch Name <span style='color: red;'>*</span>","",false) }}
                                                        {{ Form::text("branch_name",'',["class"=>"form-control branchName"]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                        {{ Form::label("address",'Address <span style="color: red;">*</span>','',false) }}
                                                        {{ Form::textarea("address","",["class"=>"form-control Address","style"=>"height: 100px;"]) }}
                                                </div>
											</div>
                                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary w-25 float-right','type'=>'submit','id'=>'update']) }}
										</div>
									</div>
								{{Form::close()}}                
                        </div>
                    </div>
                </div>
                <!--Edit Model End-->

                <div class="card data_div d-none">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Bank Accounts</h6>
                    </div>
                     
                    <div class="">
                        <table class="table datatable-show-all account_table">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Bank Name</th>
                                    <th>Company Name</th>
                                    <th>Account No.</th>
                                    <th>IFSC Code</th>
                                    <th>Branch Name</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.bank_management.partials.bank-account_script')
@stop