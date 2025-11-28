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
                        <a href="" data-toggle="modal" data-target="#bankModel" id="add_bank"><i class="fa fa-plus"></i> Add Bank</a>
                    </div> 
                    <div class="card-body">
                        {{ Form::open(['url'=>'','method'=>'POST','name'=>'searchForm','id'=>'searchForm','class'=>'searchForm']) }}
                            <div class="form-group row">
                                <div class=" col-lg-12 error-msg getcompany_id">
                                        <!-- @include('templates.GlobalTempletes.role_type',['dropDown'=>$AllCompany,'filedTitle'=>'Company','name'=>'searchCompanyId','apply_col_md'=>true]) -->
                                    @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true,'branchShow'=>true])
                                </div>
                            </div>
                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary mb-3 float-right','type'=>'submit','id'=>'search','style'=>'width:auto;']) }}
                        {{ Form::close() }}
                    </div>
                </div>
               

                <div class="card data_div d-none">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Banks</h6>
                        
                    </div>
                    <div class="">
                        <table class="table datatable-show-all bank_table">
                            <thead>
                                <tr>
                                    <th>S. No.</th>                                    
                                    <th>Company Name</th>
                                    <th>Bank Name</th>
                                    <th>Status</th>
                                    <th>Created By</th>
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
     <!--Model Start-->
     <div class="modal fade" id="bankModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title mb-3" id="bankModelLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <!--<div class="modal-body">-->
                                {{ Form::open(['url'=>'','method'=>'POST','name'=>'bankform','id'=>'bankform','class'=>'bankform']) }}
									<div class="card mb-0">
										<div class="card-body">
											<div class="form-group row">
                                            <div class="col-lg-6">
                                                        {{--Form::label("Company_id",'Company Name')--}}
                                                        @include('templates.GlobalTempletes.role_type',['dropDown'=>$dropDown,'filedTitle'=>$Title,'name'=>'addBankCompanyId','class'=>$apply_col_md])
                                                </div>
												<div class="col-lg-6">
                                                    <div>
                                                        <div class="form-group row">
                                                            {{Form::hidden("bank_id",'',['class'=>'bank_id'])}}
                                                            {{Form::label("bank_name",'Bank Name <span style="color: red;">*</span>',["class"=>"col-form-label  col-lg-12"],'',false,)}}
                                                            
                                                            <div class=" col-lg-12  error-msg">
                                                                {{Form::text("bank_name","",["class"=>"form-control bank_name","id"=>"bank_name"])}}    
                                                            </div>
                                                        </div>
                                                    </div>
												</div>

												
													
											</div>
                                            <div class="form-group row">
                                                <div class="col-lg-6">
                                                    <div>
                                                        <div class="form-group row">
                                                            <div class=" col-lg-12  error-msg">
                                                                {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary w-25 float-right','type'=>'submit','id'=>'create']) }}
                                            {{Form::button('<i class="fa fa-submit"></i> UPDATE', ['class' => 'btn btn-block btn-primary w-25 float-right d-none','type'=>'submit','id'=>'edit']) }}
										</div>
									</div>
								{{Form::close()}}                
                        </div>
                    </div>
                </div>
                <!--Model End-->

                <!-- Account Add Model Start-->
                <div class="modal fade" id="bankAccountModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="bankAccountModelLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                                {{ Form::open(['url'=>'','method'=>'POST','name'=>'bankAccountForm','id'=>'bankAccountForm','class'=>'bankAccountForm']) }}
									<div class="card mb-0">
										<div class="card-body">
											<div class="form-group row col-sm-12 col-lg-12">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        {{Form::hidden("account_id",'',['class'=>'account_id','id'=>'account_id'])}}
                                                        
                                                        <div class=" col-lg-12  error-msg">
                                                            @include('templates.GlobalTempletes.role_type',['dropDown'=>$dropDown,'filedTitle'=>$Title,'name'=>'bankAccountCompanyId','class'=>$apply_col_md  ])
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                    @include('templates.GlobalTempletes.role_type',['dropDown'=>$bankDropdown,'filedTitle'=>$bankTitle,'name'=>$bankName,'id'=>"bank_dropDown",'disabled' => 'disabled','class'=>$apply_col_md])           
                                                </div>
											</div>

                                            <div class="form-group row col-sm-12 col-lg-12">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <div class=" col-lg-12  error-msg">
                                                        {{ Form::label("account_no","Account No") }}
                                                        {{ Form::text("account_no",'',["class"=>"form-control account_no"]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                        {{ Form::label("ifsc_code",'IFSC Code') }}
                                                        {{ Form::text("ifsc_code","",["class"=>"form-control ifsc_code"]) }}
                                                </div>
											</div>

                                            <div class="form-group row col-sm-12 col-lg-12">
												<div class="col-lg-6">
                                                    <div class="form-group row">
                                                        <div class=" col-lg-12  error-msg">
                                                        {{ Form::label("branch_name","Branch Name") }}
                                                        {{ Form::text("branch_name",'',["class"=>"form-control branch_name"]) }}
                                                        </div>
                                                    </div>
												</div>

												<div class="col-lg-6">
                                                        {{ Form::label("address",'Address') }}
                                                        {{ Form::textarea("address","",["class"=>"form-control address","style"=>"height: 100px;"]) }}
                                                </div>
											</div>
                                            {{Form::button('<i class="fa fa-submit"></i> SUBMIT', ['class' => 'btn btn-block btn-primary w-25 float-right','type'=>'submit','id'=>'insert']) }}
										</div>
									</div>
								{{Form::close()}}                
                        </div>
                    </div>
                </div>
@include('templates.admin.bank_management.partials.script')
@stop