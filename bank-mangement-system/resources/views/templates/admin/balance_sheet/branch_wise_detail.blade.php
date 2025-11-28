@extends('templates.admin.master')
@section('content')


<?php
$date_filter='';
$date_filter1='';
$branch_filter='';
$end_date_filter = '';
$end_date_filter1= '';
if(isset($_GET['date']))
{
    $date_filter=$_GET['date'];
    if($date_filter!=''){
        $date_filter1=date("d/m/Y", strtotime(convertDate($date_filter)));
    }

}
if(isset($_GET['branch']))
{
    $branch_filter=$_GET['branch'];
}

if(isset($_GET['branch_id']))
{
    $branch_filter=$_GET['branch_id'];
}

if(isset($_GET['end_date']))
{
    $end_date_filter=$_GET['end_date'];
    if($end_date_filter!=''){
        $end_date_filter1= $end_date = date("d/m/Y", strtotime(convertDate($end_date_filter)));
    }
}
$endYear = date('Y') +1;

if(isset($_GET['financial_year']))
{
    $financial_year=$_GET['financial_year'];
}
else{
    $financial_year = date('Y').' - '. $endYear;

}

 $info = 'head'.$label;

$finacialYear=getFinacialYear();
$startDatee=date("Y-m-d", strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$endDatee = date("Y-m-d", strtotime(convertDate($globalDate1)));


?>



    <div class="content">

        <div class="row">

            <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                        <input type="hidden" class="form-control  " name="default_date" id="default_date"  value="{{ $startDatee }}" >
                         <input type="hidden" class="form-control " name="default_end_date" id="default_end_date" value="{{$endDatee}}">
                      <div class="row">
                      <div class="col-md-3">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Financial Year </label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="financial_year" name="financial_year">
                                    @foreach( getFinancialYear() as $key => $value )
                                    <option value="{{ $value }}" @if( $value ==  $financial_year ) selected @endif >{{ $value }} </option>
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
                                           <input type="text" class="form-control  " name="start_date" id="start_date" value="{{$date_filter1}}">
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">To Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control ends_date" name="ends_date" id="ends_date" value="{{$end_date_filter1}}">
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4">
                                  <div class="form-group row">

                                      <label class="col-form-label col-lg-12">Branch</label>
                                      <div class="col-lg-12 error-msg">
                                           <select class="form-control" id="branch" name="branch_name">
                                              @if(is_null(Auth::user()->branch_ids))
                                                <option value="">Select Branch</option>
                                                @foreach( $branches as $val )
                                                    <option value="{{ $val->id }}" @if($branch_filter==$val->id) selected @endif >{{ $val->name }}</option>
                                                @endforeach
                                             @else
                                                  <?php $an_array = explode(",", Auth::user()->branch_ids); ?>
                                                    <option value=""  >Select Branch</option>
                                                    @foreach( $branches as $k =>$val )
                                                         @if (in_array($val->id, $an_array))
                                                            <option value="{{ $val->id }}"   @if($branch_filter==$val->id) selected @endif>{{ $val->name }}</option>
                                                        @endif
                                                    @endforeach
                                             @endif
                                          </select>

                                      </div>

                                  </div>
                              </div>
                          <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="fund_transfer_export" id="fund_transfer_export" value="">
                                        <input type="hidden" name="head" value="{{$head}}" id="head">
                                          <input type="hidden" name="label" value="{{$label}}" id="label">
                                           <input type="hidden" name="date" value="{{$date_filter}}" id="date_filter">
                                           <input type="hidden" name="ends_date_filter" value="{{$end_date_filter1}}" id="ends_date_filter">
                                          <input type="hidden" name="branch" value="{{$branch_filter}}" id="branch_filter">
                                          <input type="hidden" name="export" id="export" class="export">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div>

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">{{$headDetail->sub_head}}</h6>

                        @if($financial_year != '')
                        <h6 class=" font-weight-semibold ">Closing Amount- &#X20B9;{{ number_format((float)getHeadClosing($head,$date_filter), 2, '.', '')}}</h6>@endif

                         <h6 class=" font-weight-semibold ">Total- &#X20B9;{{ number_format((float)headTotalNew($head,$date_filter,$end_date_filter1,$branch_filter), 2, '.', '')}}</h6>

                    </div>

                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold"></h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="1" style="float: right;">Export pdf</button>
                            <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>
                    </div>

                        <div class="">
                          <input type="hidden" name="head" value="{{$head}}" id="head">
                          <input type="hidden" name="label" value="{{$label}}" id="label">
                           <input type="hidden" name="date" value="{{$date_filter}}" id="date_filter">
                           <input type="hidden" name="ends_date_filter" value="{{$end_date_filter}}" id="ends_date_filter">
                          <input type="hidden" name="branch" value="{{$branch_filter}}" id="branch_filter">
                        </div>
                    <div class="">

                        <table id="detailList" class="table datatable-show-all">

                            <thead>

                                <tr>

                                    <th>S/N</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                 <!--    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                                 <th>Total Member</th>
                                    <th>Amount</th>
                                    <th>Action</th>


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

    @include('templates.admin.balance_sheet.partials.branch_wise_list_script')
@stop



