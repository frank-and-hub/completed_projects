@extends('templates.admin.master')

@section('content')
<?php
$end_date='';
$to_date='';

if(isset($_GET['date']))
{
    $date=trim($_GET['date']);
    if($date!=''){
        $end_date=$date;
    }
  
}
if(isset($_GET['to_date']))
{
    $to_date=trim($_GET['to_date']);
    if($to_date!=''){
        $to_date=$to_date;
    }
  
}
$finacialYear=getFinacialYear();
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$startDate = date("d/m/Y", strtotime($finacialYear['dateStart']));
$endDatee = date("d/m/Y", strtotime(convertDate($globalDate1)));    
   
?>

    <div class="content">

        <div class="row">
               <div class="col-md-12">
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

                          <div class="col-md-4">

                              <div class="form-group row">

                                  <label class="col-form-label col-lg-12">From Date </label>

                                  <div class="col-lg-12 error-msg">

                                       <div class="input-group">

                                           <input type="text" class="form-control  " name="date" id="date" value="{{$end_date}}">  

                                         </div>

                                  </div>

                              </div>

                          </div>
                             <div class="col-md-4">

                              <div class="form-group row">

                                  <label class="col-form-label col-lg-12">To Date </label>

                                  <div class="col-lg-12 error-msg">

                                       <div class="input-group">

                                           <input type="text" class="form-control  " name="to_date" id="to_date" value="{{$to_date}}">  

                                         </div>

                                  </div>

                              </div>

                          </div>
                          <div class="col-md-12">

                                <div class="form-group row"> 

                                    <div class="col-lg-12 text-right" >

                                        <input type="hidden" name="is_search" id="is_search" value="no">

                                        <input type="hidden" name="export" id="export" value="">
                                        <input type="hidden" name="head_id" id="head_id" value="{{$head_id}}">


                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchdepreciationForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetdepreciationForm()" >Reset </button>

                                        

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
                <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                <button type="button" class="btn bg-dark legitRipple export" data-extension="1"  style="float: right;">Export PDF</button>
              </div>
            </div> <br/><br/><br/>
            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Depreciation</h6>
                        
                    </div>
                       
                    <div class="">

                        <table id="depreciation_list" class="table datatable-show-all">

                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Date</th>
                                    <th>Party Name</th>
                                    <!-- <th>Amount</th> -->
                                    
                                   
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

@include('templates.admin.profit_loss.partials.depreciation_script')

@stop

  