@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Trial Balance Sheet</h6>
                </div>
                <?php
                // $aaaaa = DB::select('call headSum(?,?,?,?,?,?,?,?,?)',["55","CR","01","01","2022","1","02","01","2022"]);
                //   echo $aaaaa[0]->headAmount;die;
                
                //  $aaaaa = DB::select('call getAllHead(?)',["1"]);
                //    echo $aaaaa[0]->headVal;die;
                ?>
                <div class="card-body">
                    {{Form::open(['url'=>'#','method'=>'post','id'=>'getHeadList','name'=>'getHeadList','enctype'=>'multipart/form-data'])}}
                    <div class="row">

                        @include('templates.GlobalTempletes.new_role_type',[
                            'dropDown'=>$AllCompany,
                            'filedTitle'=>'Company',
                            'name'=>'company_id',
                            'value'=>'',
                            'multiselect'=>'false',
                            'design_type'=>4,
                            'branchShow'=>true,
                            'branchName'=>'branch_id',
                            'apply_col_md'=>false,
                            'multiselect'=>false,
                            'placeHolder1'=>'Please Select Company',
                            'placeHolder2'=>'Please Select Branch',
                            'selectedCompany'=>$company_id??1,
                            'selectedBranch'=>$branch_id??'',
                            'allBranch' => 1
                            ])

                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Financial Year <sup
                                        class="required">*</sup></label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="financial_year" name="financial_year">
                                        <option value="">Select Financial Year </option>
                                        @foreach( getFinancialYear() as $key => $value )
                                        <option value="{{ $value }}" {{isset($financial_year) ? (($financial_year == $value) ? 'selected' : '') : ''}}>{{ $value }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-lg-12 text-right">
                                    <button type="button" class=" btn bg-dark legitRipple" id="formgethead">Submit</button>
                                    {{Form::hidden('head_id',$head_id??'',['id'=>'head_id'])}}
                                    {{Form::hidden('name',$name??'',['id'=>'name'])}}
                                    {{Form::hidden('export','',['id'=>'export'])}}
                                    <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>


        <div class="col-md-12" id="head_closing_value_show">

        </div>

    </div>

</div>

@stop

@section('script')
@include('templates.admin.trailbalance.partials.script')

@stop