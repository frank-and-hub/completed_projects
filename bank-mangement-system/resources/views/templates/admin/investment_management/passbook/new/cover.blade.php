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
                <!-- <div class="card-body page-title">
                    
                        <h3 class="">New Passbook Cover Page  For : {{ $passbook->account_number  }}</h3></h3> 
                        <a href="{!! route('branch.passbook') !!}" style="float:right" class="btn btn-secondary">Back</a>
                    
                </div> -->
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
                                                                        
                                    <div style=" "  id="passbok_cover_print" >
                                    <table  class="cover1" border="0" cellspacing="0" cellpadding="0" align="center" id="passbok_cover_print1" style="width: 100%; ">
                                       
                                     
                                     <tr>
                                         <td style="width: 100%;font-weight: bold; "> <div  id=showDuplicate class="  "  @if($passbook->is_passbook_print ==1) style="vvisibility: visible;width: 37%;float: left;text-align: right;font-weight: bold;padding-right: 12px;" @else style="visibility: hidden;width: 37%;float: left;text-align: right;font-weight: bold;padding-right: 12px;" @endif >  Duplicate  Copy</div>

                                            <div class="col-lg-12  " style="text-align: left;width: 60%; padding-top:0px;">{{ $passbook['branch']->name  }}</div> </td> 
                                     </tr>
                                      
                                    </table>
                                    <table  class="cover_new top-margin-100" border="0" cellspacing="0" cellpadding="0" style=" margin-right: 10px;width: 99%;margin-top: 30px">
                                       
                                     
                                     <tr>
                                         <td style="width: 45% !important">
                                            <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;"> 
                                                <div  class="" style="width: 47%;float: left;"> Passbook No. = </div>
                                                <div  class="" style="width: 53%;float: left;">@if($passbook['plan']->plan_code != 703)
                                                {{ $passbook->passbook_no  }} 
                                                @else
                                                {{ $passbook['ssb']->passbook_no }}
                                                @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class=""  style="width: 47%;float: left;"> Member ID = </div>
                                                <div  class="" style="width: 53%;float: left;">{{ $passbook['member']->member_id  }}</div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class=""  style="width: 47%;float: left;"> Member Name = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $passbook['member']->first_name  }} {{ $passbook['member']->last_name  }}</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="" style="width: 47%;float: left;">Father/ Husband's Name = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $passbook['member']->father_husband  }} </div>
                                             </div>
                                             @if($passbook['plan']->plan_code == 709)
                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="" style="width: 47%;float: left;">Daughter Name  = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $passbook->daughter_name  }}</div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="" style="width:47%;float: left;">Daughter DOB =  </div>
                                                <div class="" style="width: 53%;float: left;">{{  date("d/m/Y", strtotime($passbook->dob))  }}  </div>
                                             </div>
                                            @endif

                                           <!--  -->
                                              
                                         </td>
                                         <td style="width: 55% !important">
                                            <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 10px !important;"> 
                                                <div  class="" style="width: 30%;float: left;">Plan = </div>
                                                <div  class="" style="width: 69%;float: left;">{{ $passbook['plan']->name  }}</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class=""  style="width: 30%;float: left;"> Account Number = </div>
                                                <div  class="" style="width: 69%;float: left;"> {{ $passbook->account_number  }}</div>
                                             </div>
                                        @if($passbook['plan']->plan_code != 703)
                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class=""  style="width: 30%;float: left;"> Tenure = </div>
                                                <div class="" style="width: 69%;float: left;"> @if($passbook['plan']->plan_code == 709) {{ $passbook->tenure  }} Year @else {{ $passbook->tenure*12  }} Months @endif </div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class=""  style="width: 30%;float: left;">Deno = </div>
                                                <div class="" style="width: 69%;float: left;">  {{ $passbook->deposite_amount  }} <img src="{{url('/')}}/asset/images/rs.png" width="7"> </div>
                                             </div>
                                         @endif
                                         
                                             <div style="width: 100%;padding-bottom:  17px !important;padding-top: 12px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="" style="width: 30%;float: left;"> Address = </div>
                                                <div class="" style="width: 69%;float: left;"> {{ $passbook['member']->address}},
                                                <br>
                                                {{ $passbook['member']->village }},{{ getDistrictName($passbook['member']->district_id) }}, <br>{{ getStateName($passbook['member']->state_id) }},{{$passbook['member']->pin_code}} </div>
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
                            
                                <button type="submit" class="btn btn-primary" onclick="printDiv('passbok_cover_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                           
                        </div>
                    </div> 
                </div> 
              
            
                <div class="card bg-white shadow" id="printButtonPay" > 
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                                 <div class="col-lg-3" style="display: inline-block;">
                                        <div class="form-group row">
                                            <div class="col-lg-12 error-msg">
                                                <div class="rupee-img"></div>
                                               <input type="text" class="form-control  rupee-txt" name="amount" id="amount"  readonly value="50"> 
                                            </div>
                                        </div>
                                    </div>
                                <button type="submit" class="btn btn-primary" onclick="pay_print('passbok_cover_print','{{$passbook->id}}');" >Pay&Print<i class="icon-paperplane ml-2" ></i></button>
                                
                               <!-- <button type="submit" class="btn btn-primary" onclick="printDiv('passbok_cover_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>-->
                        </div>
                    </div> 
                </div>
           
            </div> 
        </div>
         
    </div>
@stop

@section('script')
@include('templates.admin.investment_management.passbook.partials.script')
@stop