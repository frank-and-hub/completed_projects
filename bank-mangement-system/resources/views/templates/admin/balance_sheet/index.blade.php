@extends('templates.admin.master')

@section('content')
<style type="text/css">
  .text_upper{ text-transform: uppercase; }
</style>

@php
$finacialYear=getFinacialYear();
$startDatee=date("Y-m-d", strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$endDatee = date("d/m/Y", strtotime(convertDate($globalDate1)));
$endYear = date('Y',strtotime($finacialYear['dateEnd']));
$finacialCurrentYear =  date('Y',strtotime($finacialYear['dateStart'])).' - '. $endYear;
$startDate=date("d/m/Y", strtotime($finacialYear['dateStart']));
$endDate=headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);;
@endphp



    <div class="content">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    {{Form::open(['url'=>'#','method'=>'post','class'=>'','id'=>'filter','name'=>'filter','enctype'=>'multipart/form-data']) }}
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
                            'apply_col_md'=>true,
                            'multiselect'=>false,
                            'placeHolder1'=>'Please Select Company',
                            'placeHolder2'=>'Please Select Branch'
                            ])
                        <div class="col-md-4">
                            <div class="form-group row">
                            <label class="col-form-label col-lg-12">Financial Year </label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="financial_year" name="financial_year">
                                    @foreach( getFinancialYear() as $key => $value )
                                        <option value="{{ $value }}" @if( $value == $finacialCurrentYear) selected @endif >{{ $value }} </option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">From Date </label>
                                  <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            {{Form::text('start_date',$startDate,['id'=>'start_date','class'=>'form-control','readonly'=>true])}}
                                        </div>
                                  </div>
                              </div>
                          </div>

                            <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">To Date </label>
                                  <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                        {{Form::text('create_application_date',$endDatee,['id'=>'create_application_date','class'=>'form-control create_application_date','readonly'=>true])}}
                                        </div>
                                  </div>
                              </div>
                          </div>                       

                          <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right" >
                                        {{Form::hidden('export','',['id'=>'export','class'=>'form-control'])}}
                                        {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                                        {{Form::hidden('default_date',$startDate,['id'=>'default_date','class'=>'form-control'])}}
                                        {{Form::hidden('default_end_date',$endDatee,['id'=>'default_end_date','class'=>'form-control'])}}       
                                        {{Form::hidden('fund_transfer_export','',['id'=>'fund_transfer_export','class'=>'form-control'])}}       
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{Form::close()}}
                </div>
                </div>
            </div>

            <div class="data container-fluid">
                <div class="container-fluid">
                    {{-- <button type="button" class="btn bg-dark legitRipple settlebalancesheet d-none" style="float:left;">Close Financial Year</button> --}}
                    <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float:right;">Export xslx</button>
                    {{-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1" style="float:right;">Export PDF</button> --}}
                </div>
            </div> <br/><br/>
            <div id="filter_data">
        </div>
    </div>
@include($script)

@stop
