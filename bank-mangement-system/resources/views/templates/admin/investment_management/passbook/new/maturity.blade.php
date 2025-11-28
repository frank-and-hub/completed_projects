@extends('templates.admin.master')

@section('content')
<style type="text/css">
    td{vertical-align: top;}
    td .cover_new_td_left{ text-align: left; word-wrap: break-word; vertical-align: top!important;}
    .border-bottom{ border-bottom: dotted 1px black !important; padding-bottom: 10px }
    .hide_td{padding-bottom: 10px}

</style>

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                
                </div>
            </div>
        </div>
        <div class="row">  
            <div class="col-lg-12">                

                <div class="card bg-white shadow"> 
                    <div class="card-body"> 
                       
                        <div >

                            <!--- start  html-->
                                    <!-- html add here -->
                            <!--- end html-->
							<div class="row "  style="text-align: center">
                            <div class="col-lg-3 ">
                            </div> 
                                <div class="col-lg-12  passbook-style " style="display: inline-block;  "> 
                                                                        
                                    <div style=" ">
                                    <table  class="cover1" border="0" cellspacing="0" cellpadding="0" align="center" id="passbok_cover_print1" style="width: 100%; ">
                                       
                                     
                                     <tr>
                                         <td style="width: 50%;font-weight: bold; "> LONE PAYMENT </td>
                                         <td style="width: 50%;font-weight: bold;"> MATURITY PAYMENT </td>
                                     </tr>
                                      
                                    </table>
                                    <table  class="cover_new top-margin-100" border="0" cellspacing="0" cellpadding="0"  id="passbok_maturity_print" style="width: 97%;margin-top:30px; ">
                                       
                                     
                                     <tr>
                                         <td style="width: 50% !important">
                                            <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;"> 
                                                <div  class="hide_td" style="width: 27%;float: left;font-weight: bold;"> Payment Date</div>
                                                <div  class="border-bottom" style="width: 70%;float: left;"> </div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td"  style="width: 35%;float: left;font-weight: bold;"> Payment Voucher No</div>
                                                <div  class="border-bottom" style="width: 65%;float: left;"> </div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td"  style="width: 30%;float: left;font-weight: bold;"> Rate Of Interest</div>
                                                <div class="border-bottom" style="width: 70%;float: left;"></div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td" style="width: 45%;float: left;font-weight: bold;"> Received a sum of Rupees</div>
                                                <div class="border-bottom" style="width: 55%;float: left;"> </div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;"> 
                                             <div class="border-bottom" style="width: 98% ;text-align:center"> </div> 
                                                
                                             </div>
                                         </td>
                                         <td style="width: 50% !important">
                                            <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 10px !important;"> 
                                                <div  class="hide_td" style="width: 27%;float: left;font-weight: bold;"> Payment Date</div>
                                                <div  class="border-bottom" style="width: 70%;float: left;text-align:left"> @if($maturity){{ date("d/m/Y", strtotime($maturity->date)) }} @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class="hide_td"  style="width: 35%;float: left;font-weight: bold;"> Payment Voucher No</div>
                                                <div  class="border-bottom" style="width: 65%;float: left;text-align:left"> @if($maturity){{ $maturity->voucher_number }} @endif</div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class="hide_td"  style="width: 30%;float: left;font-weight: bold;"> Rate Of Interest</div>
                                                <div class="border-bottom" style="width: 70%;float: left;text-align:left"> @if($maturity){{ $passbook->interest_rate }} %  @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td" style="width: 45%;float: left;font-weight: bold;"> Received a sum of Rupees</div>
                                                <div class="border-bottom" style="width: 55%;float: left;text-align:left"> @if($maturity){{ $maturity->maturity_amount_payable }}/- @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;"> 
                                             <div class="border-bottom" style="width: 98% ;text-align:left"> @if($maturity){{amountINWord($maturity->maturity_amount_payable) }} only @endif</div> 
                                                
                                             </div>
                                         </td>
                                     </tr>
                                      
                                    </table>
                                </div>

                                  
                                         
                                    </div>
<div class="col-lg-1 ">
                            </div> 
                                </div>
								
                            </div>
								</div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-white shadow" id="printButton"> 
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            
                                <button type="submit" class="btn btn-primary" onclick="printDivMaturity('passbok_maturity_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                            
                        </div>
                    </div> 
                </div> 
                
            
            </div> 
        </div>
         
    </div>
@stop

@section('script')
@include('templates.admin.investment_management.passbook.new.script')
@stop