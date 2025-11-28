@extends('templates.admin.master')







@section('content')





<?php
$date_filter='';

$date_filter1='';

$branch_filter='';



$end_date_filter = '';
$end_date_filter1 = '';

$head = '';

if(isset($_GET['date']))

{

    $date_filter=$_GET['date'];

    if($date_filter!=''){

        $date_filter1=date("d/m/Y", strtotime(convertDate($date_filter)));

    }

  

}

if(isset($_GET['branch_id']))

{

    $branch_filter = $_GET['branch_id'];

} 







 $info = 'head'.$label;

if(isset($_GET['end_date']))
{
    $end_date_filter=$_GET['end_date'];
    if($end_date_filter!=''){
        $end_date_filter1= $end_date = date("d/m/Y", strtotime(convertDate($end_date_filter)));
    } 
}



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

                          <div class="col-md-4">

                              <div class="form-group row">

                                  <label class="col-form-label col-lg-12"> Date </label>

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
                          <div class="col-md-12">

                                <div class="form-group row"> 

                                    <div class="col-lg-12 text-right" >

                                        <input type="hidden" name="is_search" id="is_search" value="no">

                                        <input type="hidden" name="export" id="export" value="">

                                        <input type="hidden" name="branch_filter" id="branch_filter" value="<?php echo $branch_filter; ?>"/>
                                        <input type="hidden" name="head_id" id="head_id" value="<?php echo $head_id; ?>"/>


                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchRentCreditorsForm()" >Submit</button>

                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetRentCreditorsForm()" >Reset </button>

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

                        <h6 class="card-title font-weight-semibold"></h6>

                         <div class="">
                            <button type="button" class="btn bg-dark legitRipple export_report_rent ml-2" data-extension="1" style="float: right;">Export pdf</button>
                            <button type="button" class="btn bg-dark legitRipple export_report_rent ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        </div>

                    </div>

                    <div class="">

                        <table id="rentCreditorsList" class="table datatable-show-all">



                            <thead>



                                <tr>

                                    <th>S/N</th>
                                    <th>Date</th>

                                    <th>Owner Name</th>

                                    <th>Rent Type</th>

                                    <!--<th>Amount</th>-->
                                    
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



    @include('templates.admin.balance_sheet.partials.branch_wise_list_script')



@stop



