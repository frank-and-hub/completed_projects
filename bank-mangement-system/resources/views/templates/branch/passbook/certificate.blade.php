@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
  .remove_lable{
    font-size:16px; color:#333333; font-weight:bold;
  }
  .table_remove td{border: 1px solid #333333;}

  .table_remove td.td_remove { color:#333; font-weight:bold; }


</style>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="">Certificate  For : {{ $certificate->account_number  }}</h3>
                        <a href="{!! route('branch.passbook') !!}" style="float:right" class="btn btn-secondary">Back</a>
                    </div>
                </div>
                </div> 
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">

                <div class="card bg-white shadow">
                    <div class="card-body" style='text-align: center;'>
                       <div class="col-lg-12" id="certificatePrint">
                      <table width="870" align="center" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif; font-size:16px;" >
  <tbody>
   <tr>
    <td>

     <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tbody>
        <tr>
        <td  style="padding:7px 0px 23px 0px;">
          <table style="width: 100%">
            <tr>
              <td  class="remove_lable" style="text-align: right;  width: 75%; font-weight: bold;">Certificate No</td>
              <td style="text-align: center;">{{ $certificate->certificate_no  }}</td>
            </tr>
          </table>

        </td>
      </tr>
      <tr>
      <td  style="padding:11px 0px 9px 0px;">
          <table style="width: 100%">
            <tr>
              <td style="text-align: left;  width: 62%; ">
               <div class="remove_lable"  style="width: 30%;float: left; font-weight: bold; ">Account No.</div>
               <div style="width: 69%;float: left;">{{ $certificate->account_number  }}</div>
              </td>
              <td>
                <div  class="remove_lable" style="width: 55%;float: left; font-weight: bold; ">Appl. Form No.</div>
               <div style="width: 44%;float: left; ">{{ $certificate->form_number  }}</div>
              </td>
            </tr>
          </table>

        </td>
      </tr>
      <tr>
      <td  style="padding:10px 0px;">
          <table style="width: 100%">
            <tr>
              <td style="text-align: left;  width: 35%; ">
               <div class="remove_lable" style="width: 30%;float: left; font-weight: bold; ">Branch .</div>
               <div style="width: 69%;float: left;">{{ $certificate['branch']->name  }}</div>
              </td>
              <td style="text-align: left;  width:40%; ">
                <div  class="remove_lable" style="width: 45%;float: left; font-weight: bold; ">Sector/Region</div>
                <div style="width: 54%;float: left; ">{{ $certificate['branch']->sector  }} / {{ $certificate['branch']->regan  }}</div>
              </td>
              <td>
                <div  class="remove_lable" style="width: 30%;float: left; font-weight: bold; ">H.Q. </div>
               <div style="width: 70%;float: left; ">Jaipur</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
      <td  style="padding:10px 0px;">
          <table style="width: 100%">
            <tr>
              <td style="text-align: left;  width: 100%; ">
               <div  class="remove_lable" style="width: 28%;float: left; font-weight: bold; ">Received Form Shri/Smt./Km.</div>
               <div style="width: 64%;float: left;"> {{ $certificate['member']->first_name  }} {{ $certificate['member']->last_name  }}</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
      <td  style="padding:10px 0px;">
          <table style="width: 100%">
            <tr>
              <td style="text-align: left;  width: 100%; ">
               <div  class="remove_lable" style="width: 35%;float: left; font-weight: bold; ">a sum of Rupees(in figures)</div>
               <div style="width: 64%;float: left;"> {{ $certificate->deposite_amount  }} ₹</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
      <td  style="padding:8px 0px;">
          <table style="width: 100%">
            <tr>
              <td style="text-align: left;  width: 100%; ">
               <div  class="remove_lable" style="width: 15%;float: left; font-weight: bold; ">(in words) </div>
               <div style="width: 84%;float: left; "> {{amountINWord($certificate->deposite_amount)}} only</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>

       <tr>
      <td  style="padding:13px 0px 10px 0px; " class="tdpadding">
          <table width="100%"   cellspacing="0" cellpadding="0" class="table_remove certificate_table" style=" padding:0px 20px;" >
      <tr class="tr_remove">
        <td class="td_remove" style="width:113px;">Deposit Date</td>
        <td class="td_remove" style="width:113px;">Scheme Name</td>
        <td class="td_remove" style="width:113px;">Deposit Amount (Rs.)</td>
        <td class="td_remove" style="width:113px;">Period(Month)</td>
        <td class="td_remove" style="width:113px;">Rate of Interest</td>
        <td class="td_remove" style="width:113px;">Maturity Date</td>
        <td class="td_remove" style="width:113px;">Maturity Value<br> With Int. Rs.</td>
      </tr>
      <tr>
        <td align="center" style="padding:15px;"> @if($certificate->created_at  !=null){{ date("d/m/Y ", strtotime($certificate->created_at ))}}@endif</td>
        <td align="center" style="padding:15px;">{{ $certificate['plan']->name  }}</td>
        <?php $month = $certificate->tenure*12;?>
        <td align="center" style="padding:15px;">{{ $certificate->deposite_amount  }}</td>
        <td align="center" style="padding:15px;">{{ $month }}</td>
        <td align="center" style="padding:15px;">{{ isset($certificate->plan->plantenure->display_roi ) ? $certificate->plan->plantenure->display_roi : ($certificate->interest_rate)  }}%</td>
        <td align="center" style="padding:15px;">@if($certificate->maturity_date!=null){{ date("d/m/Y ", strtotime($certificate->maturity_date))}} @else {{  date
        ("d/m/Y",strtotime('+ '.($certificate->tenure*12).'months', strtotime($certificate->created_at)) )  }} @endif</td>
        <td align="right" style="padding:15px;">{{ ($certificate->plan->plan_sub_category_code != 'I') ?   $certificate->maturity_amount :  $certificate->deposite_amount  }}</td>
      </tr>
    </table>
        </td>
      </tr>


    </tbody>
   </table>






    </td>
  </tr>
</tbody>
<tr>
@if(isset($certificate->plan->plan_sub_category_code) && $certificate->plan->plan_sub_category_code == 'I') 
<td style="float:left";>Monthly Interest Amount:  {{round($certificate->deposite_amount*$certificate->interest_rate/1200,0) }}

</td>@endif</tr>


<td style="float:left";>Print Date:{{date('d/m/Y',strtotime($softdate))}}</td>
</table>
  </div>


                    </div>
                </div>
            </div>
        </div>

        <?php
                    $correctio_request_type = 0;
                    $is_request=0;
                    $status='';
                    
                    if($getCorrectionDetail)
                    {
                        $correctio_request_type = $getCorrectionDetail->print_type;
                        $is_request=1;
                        $status= $getCorrectionDetail->status;
                    }

                ?>


          @if($certificate->is_certificate_print ==0 &&  $is_request==0 )
          
                <div class="card bg-white shadow" id="printButton">
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            @if( in_array('Print Certificate', auth()->user()->getPermissionNames()->toArray() ) )
                            <button type="submit" class="btn btn-primary" onclick="printDivcer('certificatePrint','{{$certificate->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
           @endif
           @if($certificate->is_certificate_print ==0 &&  $is_request==1 && $correctio_request_type==1 )
                <div class="card bg-white shadow" id="printButton">
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            @if( in_array('Print Certificate', auth()->user()->getPermissionNames()->toArray() ) )
                            <button type="submit" class="btn btn-primary" onclick="printDivcer('certificatePrint','{{$certificate->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                            @endif
                        </div>
                    </div>
                </div>     
           @endif
      

           @if( in_array('Cover Print And Pay', auth()->user()->getPermissionNames()->toArray() ) )
                <div class="card bg-white shadow" id="printButtonPay"  >
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                                  @if($correctio_request_type==2 && $certificate->is_certificate_print ==0 ) 
                                    <div class="col-lg-6" style="display: inline-block;">
                                            <div class="form-group row">
                                              <div class="col-lg-12 error-msg">
                                                <div class="rupee-img"></div>
                                                <input type="text" class="form-control  rupee-txt" name="amount" id="amount"  readonly value="50">
                                              </div>
                                            </div>
                                            @if($gstAmount > 0)
                                              @if($IntraState)
                                              <div class="form-group row">
                                                <label  class="col-form-label col-lg-12 text-left">CGST {{$gst_percentage/2}} % Charge</label>
                                                    <div class="col-lg-12 error-msg">
                                                        <div class="rupee-img"></div>
                                                        <input type="text" class="form-control  rupee-txt" name="cgst" id="amount"  readonly value="{{$gstAmount}}">
                                                      
                                                    </div>

                                              </div>
                                              <div class="form-group row">
                                                  <label  class="col-form-label col-lg-12 text-left">SGST  {{$gst_percentage/2}} % Charge</label>
                                                  <div class="col-lg-12 error-msg">
                                                      <div class="rupee-img"></div>
                                                      <input type="text" class="form-control  rupee-txt" name="sgst" id="sgst"  readonly value="{{$gstAmount}}">
                                                  
                                                  </div>

                                              </div>
                                            @else
                                                <div class="form-group row">
                                                  <label  class="col-form-label col-lg-12 text-left">IGST  {{$gst_percentage}} % Charge</label>
                                                  <div class="col-lg-12 error-msg">
                                                    <div class="rupee-img"></div>
                                                      <input type="text" class="form-control rupee-txt igst" name="igst" id="igst"  readonly value="{{$gstAmount}}">
                                                    

                                                  </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-lg-12" >
                                        <button type="submit" class="btn btn-primary" onclick="certificate_pay_print('passbok_cover_print','{{$certificate->id}}');" >Pay&Print<i class="icon-paperplane ml-2" ></i></button>
                                    </div>
                                  @endif

                                     
                                    
                                    @if( $certificate->is_certificate_print ==1 && $is_request ==0 )
                                        <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Duplicate Certificate Request</a>
                                    @endif  
                                    @if( $certificate->is_certificate_print ==1 && $is_request ==1 &&  $status ==1)
                                        <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Duplicate Certificate Request</a>
                                    @endif
                                    @if( $certificate->is_certificate_print ==1 && $is_request ==1 &&  $status ==2)
                                        <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Duplicate Certificate Request</a>
                                    @endif

                        </div>
                    </div>
                </div>
            @endif
            </div>
        </div>

    </div>

    <div class="modal fade" id="correction-form" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
          <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
            <div class="modal-content">
              <div class="modal-body p-0">
                <div class="card bg-white border-0 mb-0">
                  <div class="card-header bg-transparent pb-2ß">
                    <div class="text-dark text-center mt-2 mb-3">Correction Request</div>
                  </div>
                  <div class="card-body px-lg-5 py-lg-5">
                    <form action="{{route('correction.request')}}" method="post" id="member-correction-form" name="member-correction-form">
                      @csrf
                      <input type="hidden" name="correction_type_id" id="correction_type_id" value="{{ $id }}">
                      <input type="hidden" name="correction_type" id="correction_type" value="6">
                      <input type="hidden" name="companyid" id="company_id" value="{{$certificate->company_id}}">
                      <div class="form-group row">
                        <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
                        <div class="col-lg-12">
                          <textarea name="corrections" name="corrections" id="corrections" rows="6" cols="50" class="form-control" placeholder="Corrections"></textarea>
                        </div>
                      </div>

                      <div class="text-right">
                        <input type="submit" name="submitform" value="Submit" class="btn btn-primary">
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


    </div>

</div>
@stop

@section('script')
@include('templates.branch.passbook.partials.script')
@stop
