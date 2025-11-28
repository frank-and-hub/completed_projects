@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
    td{vertical-align: top;}
    td .cover_new_td_left{ text-align: left; word-wrap: break-word; vertical-align: top!important;}
    .border-bottom{ border-bottom: dotted 1px black !important; padding-bottom: 30px }
    .hide_td{padding-bottom: 10px}

</style>
<?php 
        if($maturity){
            $cheque_no = \App\Models\AllHeadTransaction::where('type',13)->where('sub_type',133)->where('type_id',$maturity->id)->where('payment_mode',1)->first();
            $data = getMemberInvestment($maturity->investment_id);
        }

 ?>

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    
                        <h3 class="">Print Maturity For : {{ $passbook->account_number  }}</h3></h3> 
                        <a href="{!! route('branch.passbook') !!}" style="float:right" class="btn btn-secondary">Back</a>
                    
                </div>
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
                                                <div  class="border-bottom" @if($loan_record) style="width: 70%;float: left;padding-bottom:7px;" @else style="width: 70%;float: left;"@endif>@if($loan_record){{ date("d/m/Y", strtotime($loan_record->entry_date)) }} @endif </div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td"  style="width: 35%;float: left;font-weight: bold;"> Payment Voucher No</div>
                                                <div  class="border-bottom" style="width: 65%;float: left;"></div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td"  style="width: 30%;float: left;font-weight: bold;"> Rate Of Interest</div>
                                                <div class="border-bottom" style="width: 70%;float: left;"></div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td" style="width: 45%;float: left;font-weight: bold;"> Received a sum of Rupees</div>
                                                <div class="border-bottom"  @if($loan_record) style="width: 55%;float: left;padding-bottom:7px;" @else style="width: 55%;float: left;"@endif>@if($loan_record){{number_format((float)$loan_record->amount, 2, '.', '')}}@endif </div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;"> 
                                             <div class="border-bottom" @if($loan_record) style="width: 98%;padding-bottom:7px;" @else style= "width: 98% ;text-align:center;"@endif>
                                               @if($loan_record)
                                                @if($loan_record->payment_mode == 0)
                                                    Cash
                                                @elseif($loan_record->payment_mode == 1 || $loan_record->payment_mode == 2)
                                                    Bank 
                                                    @if($bank_detail->bank_name)({{$bank_detail->bank_name}}) @endif
                                                    @if($bank_detail->bank_account_number) ({{$bank_detail->bank_account_number}}) @endif
                                                    @if($bank_detail->ifsc_code) ({{$bank_detail->ifsc_code}}) @endif
                                              
                                                @elseif($loan_record->payment_mode == 3)
                                                   Saving Account ({{getMemberSsbAccountDetail($loan_record->member_id)->account_no}})    
                                                @endif 
                                                @endif    
                                              </div> 
                                                
                                             </div>
                                         </td>
                                         <td style="width: 50% !important">
                                            <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 10px !important;"> 
                                                <div  class="hide_td" style="width: 27%;float: left;font-weight: bold;"> Payment Date</div>
                                                <div  class="border-bottom" @if($maturity) style="width: 70%;float: left;text-align:left;padding-bottom:7px;" @else style="width: 70%;float: left;text-align:left"@endif > @if($maturity){{ date("d/m/Y", strtotime($maturity->date)) }} @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class="hide_td"  style="width: 35%;float: left;font-weight: bold;"> Payment Voucher No</div>
                                                <div  class="border-bottom" @if($maturity) style="width: 65%;float: left;text-align:left;padding-bottom:27px;" @else style="width: 65%;float: left;text-align:left"@endif  > @if($maturity){{ $maturity->voucher_number }} @endif</div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 10px !important;">  
                                                <div class="hide_td"  style="width: 30%;float: left;font-weight: bold;"> Rate Of Interest</div>
                                                <div class="border-bottom" @if($maturity) style="width: 70%;float: left;text-align:left;padding-bottom:7px;" @else style="width: 70%;float: left;text-align:left"@endif > @if($maturity){{ $passbook->interest_rate }} %  @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;">  
                                                <div class="hide_td" style="width: 45%;float: left;font-weight: bold;"> Received a sum of Rupees</div>
                                                <div class="border-bottom"  @if($maturity) style="width: 55%;float: left;text-align:left;padding-bottom:7px;" @else style="width: 55%;float: left;text-align:left"@endif > 
                                         @if($maturity){{ $maturity->maturity_amount_payable }}/- @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  35px !important;padding-top: 20px !important;clear: both;text-align: left;padding-right: 30px !important;"> 
                                             <div class="border-bottom" @if($maturity) style="width: 98%;float: left;padding-bottom:7px;" @else style="width: 98%;float: left;"@endif > 
                                        @if($maturity)         
                                             @if($maturity->payment_mode == 0)
                                           Cash
                                        @elseif($maturity->payment_mode == 1)
                                           Cheque ({{$maturity->bank_name}}) ({{$maturity->bank_account_number}}) ({{$maturity->bank_ifsc}})
                                        @elseif($maturity->payment_mode == 2)
                                           Online Transaction 
                                           @if($maturity->bank_name) 
                                           ({{$maturity->bank_name}})@endif 
                                            @if($maturity->bank_account_number) 
                                            ({{$maturity->bank_account_number}}) 
                                            @endif
                                                     @if($maturity->bank_ifsc)
                                                    ({{$maturity->bank_ifsc}})
                                                    @endif
                                                @elseif($maturity->payment_mode == 3)
                                                   Saving Account ({{getSavingAccountMemberId($data->member_id)->account_no}})    
                                                @endif 
                                                @endif
                                                </div> 
                                                
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
                @if( in_array('Maturity Print', auth()->user()->getPermissionNames()->toArray() ) )
                <div class="card bg-white shadow" id="printButton"> 
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            
                                <button type="submit" class="btn btn-primary" onclick="printDivMaturity('passbok_maturity_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                            
                        </div>
                    </div> 
                </div> 
                @endif
            
            </div> 
        </div>
         
    </div>
@stop

@section('script')
@include('templates.branch.passbook.new.script')
@stop