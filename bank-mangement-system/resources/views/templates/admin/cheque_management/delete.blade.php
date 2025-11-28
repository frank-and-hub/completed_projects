@extends('templates.admin.master')

@section('content')
<?php
$bankId='';
$accId='';
$cheId='';
$company_id='';
if(isset($chequeDetail))
{

    $bankId=$chequeDetail->bank_id;
    $accId=$chequeDetail->account_id;
    $cheId=$chequeDetail->id;
    // $company_id=$companyDetail->company_id;
}
?>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Delete Cheque</h6>
                    </div>
                    <div class="card-body">
                        <form action="{!! route('admin.delete_cheque') !!}" method="post" enctype="multipart/form-data" id="delete_cheque" name="delete_cheque">
                        @csrf
                            <div class="row">
                                <!--<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12"> Date </label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                <input type="text" class="form-control  " name="cheque_date" id="cheque_date"  readonly value="{{ date('d/m/Y') }}"> 
                                               </div>
                                        </div>
                                    </div>
                                </div>-->
                                    @include('templates.GlobalTempletes.role_type',[
                                    'dropDown'=> $company,
                                    'name'=>'company_id',
                                    'apply_col_md'=>true,
                                                'filedTitle' => 'Company'
                                    ])
                                <input type="hidden" class="form-control  create_application_date" name="cheque_date" id="cheque_date"  readonly > 
                                <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="bank_id" name="bank_id">
                                                <option value="">Select Bank</option>
                                                {{-- @foreach( $bank as $val )
                                                    <option value="{{ $val->id }}"  @if($val->id==$bankId) selected @endif >{{ $val->bank_name }}</option> 
                                                @endforeach --}}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Account Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="account_id" name="account_id">
                                                <option value="">Select Account Number</option>
                                            </select>
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cheque Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="cheque_no" name="cheque_no">
                                                <option value="">Select Cheque Number</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-center" > 
                                            <button type="button" class="btn btn-primary"onClick="deleteCheque()" >Delete</button> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> 
        </div>
    </div>
@include('templates.admin.cheque_management.partials.delete')
@stop