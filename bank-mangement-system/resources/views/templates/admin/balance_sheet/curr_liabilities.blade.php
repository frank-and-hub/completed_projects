@extends('templates.admin.master')

@section('content')
<style type="text/css">
  .text_upper{ text-transform: uppercase; }
</style>
<?php
$date_filter='';
$date_filter1='';
$branch_filter='';
$end_date_filter = '';
$end_date_filter1 = '';

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

if(Auth::user()->branch_id>0)                   
 $branch_filter=Auth::user()->branch_id;


 if(isset($_GET['end_date']))
{
    $end_date_filter=$_GET['end_date'];
    if($end_date_filter!=''){
        $end_date_filter1= $end_date = date("d/m/Y", strtotime(convertDate($end_date_filter)));
    } 
}

$profit_loss=(headTotalNew(3,$date_filter,$end_date_filter,$branch_filter)-headTotalNew(4,$date_filter,$end_date_filter,$branch_filter));
?>

<?php

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
                      <div class="row">
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">From Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="start_date" id="start_date"  value="{{$date_filter1}}"> 
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
                                              <option value="">Select Branch</option>
                                            @if(is_null(Auth::user()->branch_ids))
                                                @foreach( $branches as $branch)
                                                    <option value="{{ $branch->id }}" @if($branch_filter==$branch->id) selected @endif>{{ $branch->name }}</option>
                                                @endforeach
                                            @else
                                                <?php $an_array = explode(",", Auth::user()->branch_ids); ?>
                                                @foreach( $branches as $branch)
                                                    @if (in_array($branch->id, $an_array))
                                                        <option value="{{ $branch->id }}" @if($branch_filter==$branch->id) selected @endif>{{ $branch->name }}</option>
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
                                        <input type="hidden" name="export" id="export" class="export"/>
                                        <input type="hidden" name="head_id" id="head_id" value="{{$head}}"/>
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
                <style type="text/css">
                    #expense{
                            height: 44.5rem;
                            overflow-x: hidden;
                            overflow-y: auto;
                            text-align:justify;
                    }
                </style>
                <div class="container">
                  <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                    <button type="button" class="btn bg-dark legitRipple export" data-extension="1" style="float: right;">Export PDF</button>
                </div>
            </div> <br/><br/>
            
            <div class="col-md-12 mt-2">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">
                              @if($headDetail->head_id==5)
                          <th class="text_upper"><a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/report'.'/?head_id='.$headDetail->head_id.'&date='.$date_filter.'&branch_id='.$branch_filter.'&end_date='.$end_date)}}" target="_blank">{{$headDetail->sub_head}}</a></th></h6>
                          @elseif(in_array(18,$ids) || in_array(15,$ids) || in_array(19,$ids))
                          <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail_ledger'.'/?head='.$headDetail->head_id.'&date='.$date_filter.'&end_date='.$end_date)}}" target="_blank">{{$headDetail->sub_head}}</a></td></h6>
                           @elseif(in_array(17,$ids) || in_array(6,$ids))
                          <td class="text_upper"> <a href="{{ URL::to('admin/profit-loss'.'/?head='.$headDetail->head_id.'&date='.$date_filter.'&end_date='.$end_date)}}" target="_blank">{{$headDetail->sub_head}}</a></td></h6>
                            @elseif($headDetail->head_id==27)
                              <th class="text_upper"><a href="{{ URL::to('admin/balance-sheet/current_liability/bank_wise/'.$headDetail->head_id.'/'.$headDetail->labels.'/?date='.$date_filter.'&branch_id='.$branch_filter.'&end_date='.$end_date)}}" target="_blank">{{$headDetail->sub_head}}</a></th></h6>
                           @elseif($headDetail->head_id == 16)
                                    <td class="text_upper"> {{$headDetail->sub_head}}</td></h6>
                          @else
                             <th class="text_upper"><a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$headDetail->head_id.'/'.$headDetail->labels.'/?date='.$date_filter.'&branch='.$branch_filter.'&end_date='.$end_date)}}" target="_blank">{{$headDetail->sub_head}}</a></th></h6> 
                          @endif
                       @if(in_array(17,$ids) || in_array(6,$ids) )
                        <th>&#X20B9;{{ number_format((float)$profit_loss, 2, '.', '')}}</th> 
                       @else
                         <th>&#X20B9;{{ number_format((float)headTotalNew($headDetail->head_id,$date_filter,$end_date_filter,$branch_filter), 2, '.', '')}}</th> 
                       @endif
                       
                    </div>
                    <table class="table  datatable-show-all">
                @if(count($childHead)>0)

                    @foreach ($childHead as $val1) 
                        <thead>
                          <tr @if($val1->status==1) class="child_inactive" @endif >
                                
                
              <?php  $head4= getHead($val1->head_id,5);?>
              
                        @if(count($head4)>0)
                            <th class="text_upper 22"><a href="{!!route('admin.balance-sheet.curr_liability_detail',$val1->head_id.'/?date='.$date_filter.'&branch='.$branch_filter.'&end_date='.$end_date_filter)!!}" target="_blank">{{$val1->sub_head}}</a></th>
                        @elseif($val1->head_id == 16)
                            <td class="text_upper"> {{$val1->sub_head}}</td>

                        @elseif( $val1->head_id==89 )
                             <th class="text_upper 33">{{$val1->sub_head}} </th>

                        @elseif(in_array(18,$ids) || in_array(15,$ids)  || in_array(19,$ids))

                          <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail_ledger'.'/?head='.$val1->head_id.'&date='.$date_filter.'&end_date='.$end_date)}}" target="_blank">{{$val1->sub_head}}</a></td>

                        @elseif(in_array(17,$ids) || in_array(6,$ids))
                          <td class="text_upper"> <a href="{{ URL::to('admin/profit-loss'.'/?head='.$headDetail->head_id.'&date='.$date_filter.'&end_date='.$end_date)}}" target="_blank">{{$val1->sub_head}}</a></td>

                        @elseif(in_array(27,$ids))
                              <th class="text_upper"><a href="{{ URL::to('admin/balance-sheet/current_liability/bank_wise/'.$val1->head_id.'/'.$val1->labels.'/?date='.$date_filter.'&branch_id='.$branch_filter.'&end_date='.$end_date)}}" target="_blank">{{$val1->sub_head}}</a></th></h6>
                        @else
                              <th class="text_upper 44"><a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$val1->head_id.'/'.$val1->labels.'/?date='.$date_filter.'&branch='.$branch_filter.'&end_date='.$end_date_filter)}}" target="_blank">
                              {{$val1->sub_head}}</a></th>
                        @endif

                                <th></th>
                                <th></th>

                                @if($val1->head_id==17)
                    <th>&#X20B9;{{ number_format((float)$profit_loss, 2, '.', '')}}</th>
                @else
                    <th>&#X20B9;{{ number_format((float)headTotalNew($val1->head_id,$date_filter,$end_date_filter,$branch_filter), 2, '.', '')}}</th>
                @endif

                            </tr>
                        </thead>
                        <?php  $head4= getHead($val1->head_id,5);?>
                    @if(count($head4)>0)
                        @foreach ($head4 as $val4)  
                        <tbody>
                <tr @if($val4->status==1) class="child_inactive" @endif >
                <td> </td>
                @if($val1->head_id==57 || $val1->head_id == 59 )
                  <td class="text_upper  55"><a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$val4->head_id.'/'.$val4->labels.'/?date='.$date_filter.'&branch='.$branch_filter.'&end_date='.$end_date_filter)}}" target="_blank">   {{$val4->sub_head}}</a></td> 

                @else
                <td class="text_upper  55"><a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$val4->head_id.'/'.$val4->labels.'/?date='.$date_filter.'&branch='.$branch_filter.'&end_date='.$end_date_filter)}}" target="_blank">  {{$val4->sub_head}}</a></td>
                @endif
                <td> &#X20B9;{{ number_format((float)headTotalNew($val4->head_id,$date_filter,$end_date_filter,$branch_filter), 2, '.', '')}}</td> 
                <td> </td>
              </tr> 
              
            </tbody> 
                    @endforeach
                @endif
            @endforeach
             @elseif(count($subchildHead)>0)
                    @foreach ($subchildHead as $val5)
                    <tbody>
                        <tr @if($val5->status==1) class="child_inactive" @endif >
                     
                      <td class="text_upper  66"><a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$val5->head_id.'/'.$val5->labels.'/?date='.$date_filter.'&branch='.$branch_filter.'&end_date='.$end_date_filter)}}" target="_blank"> {{$val5->sub_head}}</a></td>

                      <td align="center"> &#X20B9;{{ number_format((float)headTotalNew($val5->head_id,$date_filter,$end_date_filter,$branch_filter), 2, '.', '')}}</td> 
               
                    </tr>
                    </tbody>
                    @endforeach
                @endif
                    </table>    
                </div>
            </div>  
        </div>
    </div>

@include('templates.admin.balance_sheet.partials.script_search_lib')

@stop