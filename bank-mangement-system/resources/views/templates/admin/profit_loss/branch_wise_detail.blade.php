@extends('templates.admin.master')



@section('content')
<?php

$date_filter='';
$date_filter1='';
$branch_filter='';
$end_date_filter = '';
$end_date_filter1= '';

$end_date='';
if(isset($_GET['date']))
{
    $date=trim($_GET['date']);
    if($date!=''){
        $end_date=$date;
    }
	$date_filter=$_GET['date'];
  
}
if(isset($_GET['to_date']))
{
    $to_date=trim($_GET['to_date']);
    if($to_date!=''){
        $to_date=$to_date;
    }
	if($to_date!=''){
        $end_date_filter1= $end_date = date("d/m/Y", strtotime(convertDate($to_date)));
    } 
  
}

if(isset($_GET['branch_id']))
{
    $branch_filter=$_GET['branch_id'];
}

$finacialYear=getFinacialYear();
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$startDate = date("d/m/Y", strtotime($finacialYear['dateStart']));
$endDatee = date("d/m/Y", strtotime(convertDate($globalDate1)));

$currentYear = date('Y');
$endYear = date('Y') +1;

if(isset($_GET['financial_year']))
{
    $financial_year=$_GET['financial_year'];
}
else{
    $financial_year = date('Y').' - '. $endYear;

}

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
                    <input type="hidden" class="form-control  " name="default_date" id="default_date"  value="{{ $startDate }}" > 
                         <input type="hidden" class="form-control " name="default_end_date" id="default_end_date" value="{{$endDatee}}"> 
                      <div class="row">
                        <div class="col-md-3">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-12">Financial Year </label>
                              <div class="col-lg-12 error-msg">
                                <select class="form-control" id="financial_year" name="financial_year">
                                  @foreach( getFinancialYear() as $key => $value )
                                  <option value="{{ $value }}" @if( $value == $financial_year) selected @endif >{{ $value }} </option>
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
                                           <input type="text" class="form-control  " name="date" id="date" value="{{$date_filter}}">  
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">To Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="to_date" id="to_date" value="{{$end_date_filter1}}">  
                                         </div>
                                  </div>
                              </div>
                          </div>
                     
                          <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="export" id="export" value="">
                                         <input type="hidden" name="head" value="{{$head}}" id="head"> 
                                        <input type="hidden" name="label" value="{{$label}}" id="label"> 
                                          <input type="hidden" name="branch" value="{{$branch}}" id="branch"> 
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
            <div class="data container-fluid">
                
              <div class="container">
                <button type="button" class="btn bg-dark legitRipple export_report ml-2" data-extension="0" style="float: right;">Export xslx</button>

                <button type="button" class="btn bg-dark legitRipple export_report" data-extension="1" style="float: right;">Export PDF</button>
              </div>
            </div> <br/><br/><br/>
            <div class="col-md-12 mt-2">

                <div class="card">

                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">{{$headDetail->sub_head}}</h6>
                        <h6 class=" font-weight-semibold ">Total- &#X20B9;{{ number_format((float)headTotalNew($head,$date_filter,$end_date_filter1,$branch_filter), 2, '.', '')}}</h6>
                    </div>
                        <div class="">
                          <input type="hidden" name="head" value="{{$head}}" id="head"> 
                          <input type="hidden" name="label" value="{{$label}}" id="label"> 
                           <input type="hidden" name="date" value="{{$end_date}}" id="date"> 
                            <input type="hidden" name="to_date" value="{{$to_date}}" id="to_date"> 
                            <input type="hidden" name="financial_year" value="{{$financial_year}}" id="financial_year">
                        </div>
                    <div class="">

                        <table id="detailList" class="table datatable-show-all">

                            <thead>

                                <tr>

                                    <th>S/N</th>
                                    <th>Branch Code</th>
                                    <th>Branch Name</th>
                                <!--     <th>Total No.</th> -->
                                    <th>Total Amount</th>
                                    
                                   
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

@include('templates.admin.profit_loss.partials.branch_wise_list_script')

@stop


