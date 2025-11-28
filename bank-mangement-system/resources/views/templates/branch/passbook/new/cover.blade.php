@extends('layouts/branch.dashboard')

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
                <div class="card-body page-title">

                        <h3 class="">New Passbook Cover Page  For : {{ $passbook->account_number  }}</h3></h3>
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

                                    <div style=" "  id="passbok_cover_print" >
                                    <table  class="cover1" border="0" cellspacing="0" cellpadding="0" align="center" id="passbok_cover_print1" style="width: 100%; ">


                                     <tr>
                                         <td style="width: 100%;font-weight: bold; "> <div  id=showDuplicate class="  "  @if($passbook->is_passbook_print ==1) style="visibility: visible;width: 20%;float: left;text-align: right;font-weight: bold;padding-right: 10px;" @else style="visibility: hidden;width: 20%;float: left;text-align: right;font-weight: bold;padding-right: 10px;" @endif >  Duplicate  Copy</div>

                                            <div class=" " style="text-align: center;width: 80%; padding-top:0px;"> {{ $passbook['company']->name  }}</div>
                                            <center>
                                                <small class=" " style="text-align: center;width: 80%; padding-top:0px;">{{$passbook['company']->address}}</small>
                                            </center>
                                        </td>
                                     </tr>
                                     <tr>
                                         <td style="width: 100%;font-weight: bold; ">

                                            <div class="col-lg-12  " style="text-align: center;width: 97%; padding-top:0px;"> <span style='padding-right:15px; text-decoration: underline;' > {{ $passbook['branch']->name  }} </span> शाखा / Branch</div> </td>
                                     </tr>

                                    </table>
                                    <table  class="cover_new top-margin-100" border="0" cellspacing="0" cellpadding="0" style=" margin-right: 10px;width: 99%;margin-top: 5px">


                                     <tr>
                                         <td style="width: 45% !important">
                                            <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div  class="" style="width: 47%;float: left;"> Passbook No. = </div>
                                                <div  class="" style="width: 53%;float: left;">@if($passbook['plan']->plan_category_code != "S")
                                                {{ $passbook->passbook_no  }}
                                                @else
                                                {{ $passbook['ssb_detail'][0]['passbook_no'] }}
                                                @endif</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class=""  style="width: 47%;float: left;"> Customer ID = </div>
                                                <div  class="" style="width: 53%;float: left;">{{ $passbook['member']->member_id  }}</div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class=""  style="width: 47%;float: left;"> Customer Name = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $passbook['member']->first_name  }} {{ $passbook['member']->last_name  }}</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class="" style="width: 47%;float: left;">Father/ Husband's Name = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $passbook['member']->father_husband  }} </div>
                                             </div>
                                             @if($passbook['plan']->plan_sub_category_code != 'K')
                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class="" style="width: 47%;float: left;">Nominee Name = </div>
                                                <div class="" style="width: 53%;float: left;">@if(isset($passbook['investmentNomiees'][0])) {{ $passbook['investmentNomiees'][0]->name}}@endif </div>
                                            </div>
                                            <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class="" style="width: 47%;float: left;">Relation = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $relation ? $relation['investmentRelation'] ? $relation['investmentRelation']->name : '' : ''}} </div>
                                            </div>
                                            @else
                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class="" style="width: 47%;float: left;">Daughter Name  = </div>
                                                <div class="" style="width: 53%;float: left;">{{ $passbook->daughter_name  }}</div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class="" style="width:47%;float: left;">Daughter DOB =  </div>
                                                <div class="" style="width: 53%;float: left;">{{  date("d/m/Y", strtotime($passbook->dob))  }}  </div>
                                             </div>
                                            @endif
                                             @if(in_array($passbook['plan']->plan_code,[721]))
                                                <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                    <div class="" style="width: 47%;float: left;">Child Name  = </div>
                                                    <div class="" style="width: 53%;float: left;">{{ $passbook->re_name  }}</div>
                                                 </div>
                                                 <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                    <div class="" style="width: 47%;float: left;">Child DOB  = </div>
                                                    <div class="" style="width: 53%;float: left;">{{date("d/m/Y", strtotime($passbook->re_dob)) }}</div>
                                                 </div>

                                            @endif
                                            <div style="width: 100%;padding-bottom:  20px !important;padding-top: 45px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                                <div class="" style="width: 99%;float: left;">
                                                    @php
                                                        $stateid = getBranchState(Auth::user()->username);
                                                    @endphp

                                                    {{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}

                                                </div>

                                             </div>

                                         </td>
                                         <td style="width: 55% !important">
                                            <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 10px !important;">
                                                <div  class="" style="width: 30%;float: left;">Plan = </div>
                                                <div  class="" style="width: 69%;float: left;">{{ $passbook['plan']->name  }}</div>
                                             </div>
                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 10px !important;">
                                                <div class=""  style="width: 30%;float: left;"> Account No. = </div>
                                                <div  class="" style="width: 69%;float: left;"> {{ $passbook->account_number  }}</div>
                                             </div>
                                        @if(!in_array($passbook['plan']->plan_category_code ,['S']))
                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 10px !important;">
                                                <div class=""  style="width: 30%;float: left;"> Tenure = </div>
                                                <div class="" style="width: 69%;float: left;">  {{ $passbook->tenure*12  }} Months </div>
                                             </div>

                                             <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 10px !important;">
                                                <div class=""  style="width: 30%;float: left;">Denomination = </div>
                                                <div class="" style="width: 69%;float: left;">  {{ $passbook->deposite_amount  }} <img src="{{url('/')}}/asset/images/rs.png" width="7"> </div>
                                             </div>
                                         @endif
                                         @if($passbook['plan']->plan_sub_category_code != 'K')
                                         <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 10px !important;">
                                            <div class="" style="width: 30%;float: left;">Percentage = </div>
                                            <div class="" style="width: 69%;float: left;">@if(isset($passbook['investmentNomiees'][0])) {{ $passbook['investmentNomiees'][0]->percentage.'%'}} @endif</div>
                                        </div>
                                        @endif
                                        <div style="width: 100%;padding-bottom:  10px !important;padding-top: 10px !important;clear: both;text-align: left;padding-right: 30px !important;">
                                            <div class="" style="width: 31%;float: left;"> Address = </div>
                                            <div class="" style="width: 69%;float: left; text-transform: capitalize;"> {{ strtolower($passbook['member']->address) }},
                                            <br>
                                            {{ strtolower($passbook['member']->village) }},{{ strtolower(getDistrictName($passbook['member']->district_id)) }},
                                            {{ getStateCode($passbook['member']->state_id) }},{{ strtolower($passbook['member']->pin_code)}} </div>
                                        </div>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td tyle="width: 45% !important">
                                        <div class="" style="width: 99%;float: left;text-align: left; padding-top:5px;" >
                                                जारी करने का तारीख / Date of issue
                                                </div>
                                        </td>
                                        <td tyle="width: 55% !important">
                                        <div class="" style="margin-left:17rem;"> प्रबंधक / Manager
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

                {{-- {{dd($passbook,$is_request,$status,$correctio_request_type,($passbook->is_passbook_print ==0 &&  $is_request==0 && $passbook->is_mature != 0 ),(auth()->user()->getPermissionNames()->toArray()),($passbook->is_passbook_print ==0 &&  $is_request==1 && $correctio_request_type==1))}} --}}

                @if($passbook->is_passbook_print ==0 &&  $is_request==0 && $passbook->is_mature != 0 )


                <div class="card bg-white shadow" id="printButton">
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            @if( in_array('New Passbook Cover Print', auth()->user()->getPermissionNames()->toArray() ) )
                                <button type="submit" class="btn btn-primary" onclick="printDiv('passbok_cover_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @if($passbook->is_passbook_print ==0 &&  $is_request==1 && $correctio_request_type==1 )
                    <div class="card bg-white shadow" >
                        <div class="card-body">
                            <div class="col-lg-12 text-center ">
                                @if( in_array('New Passbook Cover Print', auth()->user()->getPermissionNames()->toArray() ) )
                                    <button type="submit" class="btn btn-primary" onclick="printDiv('passbok_cover_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @if( in_array('New Cover Print And Pay', auth()->user()->getPermissionNames()->toArray() )  && $passbook->is_mature != 0)
                <div class="card bg-white shadow" id="printButtonPay" >
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                                @if($correctio_request_type==2 && $passbook->is_passbook_print ==0 )
                                    <div class="col-lg-6" style="display: inline-block;">
                                        <div class="form-group row">
                                            <div class="col-lg-12 error-msg">
                                                <div class="rupee-img"></div>
                                               <input type="text" class="form-control rupee-txt" name="amount" id="amount"  readonly value="50">
                                            </div>
                                        </div>
                                        @if($gstAmount  > 0)
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

                                        <button type="submit" class="btn btn-primary" onclick="pay_print('passbok_cover_print','{{$passbook->id}}');" >Pay&Print<i class="icon-paperplane ml-2" ></i></button>
                                    </div>
                                @endif

                                @if( $passbook->is_passbook_print ==1 && $is_request ==0 )
                                    <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Duplicate Passbook Request </a>
                                @endif
                                @if( $passbook->is_passbook_print ==1 && $is_request ==1 &&  $status ==1)
                                    <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Duplicate Passbook Request</a>
                                @endif
                                @if( $passbook->is_passbook_print ==1 && $is_request ==1 &&  $status ==2)
                                    <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Duplicate Passbook Request</a>
                                @endif


                               <!-- <button type="submit" class="btn btn-primary" onclick="printDiv('passbok_cover_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>-->
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
                      <input type="hidden" name="correction_type" id="correction_type" value="5">
                      <input type="hidden" name="companyid" id="company_id" value="{{$passbook->company_id}}">
                      <div class="form-group row">
                        <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
                        <div class="col-lg-12">
                          <textarea name="corrections" name="corrections" rows="6" cols="50" class="form-control" placeholder="Corrections"></textarea>
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
@stop

@section('script')
@include('templates.branch.passbook.new.script')
@stop
