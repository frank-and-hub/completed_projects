@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
            <div class="col-md-12">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">Search Filter </h6>
                        </div>
                        <div class="card-body">
                            {{Form::open(['url'=>'#','method'=>'post','id'=>'filter','name'=>'filter','enctype'=>'multipart/form-data'])}}                             
                                <div class="row">
                                    {{--
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Branch</label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="">
                                                    <select class="form-control" name="branch_id" id="branch" title="Please Select Branch" aria-invalid="false">
                                                        <option value="">---Please Select Branch---</option>
                                                    @foreach ($branches as $key => $item)
                                                            <option value="{{$key}}">{{$item}}</option>
                                                    @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    --}}
                                    @include('templates.GlobalTempletes.new_role_type', [
                                        'dropDown' => $AllCompany,
                                        'filedTitle' => 'Company',
                                        'name' => 'company_id',
                                        'value' => '',
                                        'selectedCompany' => $company_id,
                                        'design_type' => 4,
                                        'branchShow' => true,
                                        'branchName' => 'branch_id',
                                        'selectedBranch' => $branch_id,
                                        'apply_col_md' => true,
                                        'multiselect' => false,
                                        'placeHolder1' => 'Please Select Company',
                                        'placeHolder2' => 'Please Select Branch',
                                    ])
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">From Date </label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    {{Form::text('date',$start_date,['class'=>'form-control','id'=>'date','required'=>true,'title'=>'Please select the From date','readonly'=>true])}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">To Date </label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    {{Form::text('to_date',$end_date,['class'=>'form-control','id'=>'to_date','required'=>true,'title'=>'Please select the To date','readonly'=>true])}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-lg-12 text-right">
                                                {{Form::hidden('head_id',$head_id,['class'=>'','id'=>'head_id'])}}
                                                {{Form::hidden('title',$title,['class'=>'','id'=>'title'])}}
                                                {{Form::hidden('is_search','no',['class'=>'','id'=>'is_search'])}}
                                                {{Form::hidden('export','',['class'=>'','id'=>'export'])}}
                                                <button type="button" class=" btn bg-dark legitRipple" id="submit_form">Submit</button>
                                                <button type="button" class="btn btn-gray legitRipple" id="reset_form">Reset </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{Form::close()}}
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="profit_and_loss_table" style="display:none;">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h6 class="card-title font-weight-semibold">{{ getAcountHead($head_id) }}</h6>
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                        <div class="">
                            <table id="depreciation_list" class="table datatable-show-all">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Date</th>
                                        <th>V.NO. / AC.NO. / M.ID</th>
                                        <th>Name</th>
                                        <th>Asscoaite No.</th>

                                        <th>Account No</th>
                                        <th>CR</th>
                                        <th>DR</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @stop
    @section('script')
        @include('templates.admin.profit_loss.partials.depreciation_script_new')
    @stop
