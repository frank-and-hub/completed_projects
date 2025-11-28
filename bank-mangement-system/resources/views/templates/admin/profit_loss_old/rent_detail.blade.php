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

    $branch = App\Models\Branch::where('id',$branch_id)->first();
   
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
                                          <input type="hidden" name="branch_id" id="branch_id" value="{{$branch_id}}">
                                          <input type="hidden" name="head_id" id="head_id" value="{{$head_id}}">


                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchrentForm()" >Submit</button>


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
                        <h6 class="card-title font-weight-semibold">{{$branch->name}}</h6>
                        
                    </div>
                       
                    <div class="">

                        <table id="rent" class="table datatable-show-all">

                            <thead>
                                <tr>
                                     <th>S/N</th>

                                     <th>Date</th>

                                    <th>Owner Name</th>

                                    <th>Rent Type</th>

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
    @include('templates.admin.profit_loss.partials.rent_script')

@stop

