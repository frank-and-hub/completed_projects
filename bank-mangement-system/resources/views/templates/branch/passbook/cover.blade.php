@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
    td{vertical-align: top;}
</style>

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                    
                        <h3 class="">Passbook Cover Page  For : {{ $passbook->account_number  }}</h3></h3> 
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
                                <div class="col-lg-8  passbook-style " style="display: inline-block;  "> 
                                                                        
                                    <div style="text-align: left">
                                    <table  class="cover" border="0" cellspacing="0" cellpadding="0" align="center" id="passbok_cover_print">
                                       
                                     

                                        <tr>
                                        
                                        <td colspan="2"  style="padding-bottom: 25px;">
                                            <div  id=showDuplicate class=" passbook_content"  @if($passbook->is_passbook_print ==1) style="visibility: visible; width: 38%;float: left; font-weight: bold;" @else style="visibility: hidden; width: 38%;float: left; font-weight: bold;" @endif >  Duplicate  Copy</div>

                                            <div class="col-lg-12 passbook_content cover-city" style="text-align: left;width: 60%;float: left; padding-top:0px;">{{ $passbook['branch']->name  }}</div>
                                        </td> 
                                      </tr> 
                                      <tr>
                                        <td class="td_lable"><label class=" col-lg-12 passbook_lable">Passbook No. = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                @if($passbook['plan']->plan_code != 703)
                                                {{ $passbook->passbook_no  }} 
                                                @else
                                                {{ $passbook['ssb']->passbook_no }}
                                                @endif
                                            </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="td_lable"><label class=" col-lg-12 passbook_lable">Member ID = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook['member']->member_id  }}
                                            </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Member Name = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook['member']->first_name  }} {{ $passbook['member']->last_name  }}
                                            </div>
                                        </td>
                                      </tr>
                                      
                                      
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Father/ Husband's Name = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook['member']->father_husband  }}
                                            </div>
                                        </td>
                                      </tr>
                                      
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Plan = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook['plan']->name  }}
                                            </div>
                                        </td>
                                      </tr>
                                      
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Account Number = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook->account_number  }} 
                                            </div>
                                        </td>
                                      </tr>
                                      
                                @if($passbook['plan']->plan_code == 709)
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Daughter Name  = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook->daughter_name  }} 
                                            </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Daughter DOB = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{  date("d/m/Y", strtotime($passbook->dob))  }} 
                                            </div>
                                        </td>
                                      </tr>
                                @endif
                                      
                                      
                                    @if($passbook['plan']->plan_code != 703 && $passbook['plan']->plan_code != 721)
                                    
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Tenure = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                @if($passbook['plan']->plan_code == 709) {{ $passbook->tenure  }} Year @else {{ $passbook->tenure*12  }} Months @endif
                                            </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Deno = </label></td>
                                        <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook->deposite_amount  }} <img src="{{url('/')}}/asset/images/rs.png" width="7">
                                            </div>
                                        </td>
                                      </tr>
                                         
                                    @endif
                                    @if(in_array($passbook['plan']->plan_code,[721]))
                                    <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Child Name = </label></td>
                                            <td ><div class="col-lg-12 passbook_content">
                                                {{ $passbook->re_name  }} 
                                            </div>
                                        </td>
                                    </tr>  
                                    <tr> 
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Child Dob = </label></td>
                                            <td ><div class="col-lg-12 passbook_content">
                                                {{ date('d/m/Y',strtotime($passbook->re_dob))  }} 
                                            </div>
                                        </td>
                                    </tr>    
                                            @endif
                                        
                                    <tr>
                                        <td  class="td_lable"><label class=" col-lg-12 passbook_lable">Address = </label></td>
                                        <td ><div class="col-lg-12 passbook_content" style="word-break: break-all;">
                                                {{ $passbook['member']->address}},
                                                <br>
                                                {{ $passbook['member']->village }},{{ getDistrictName($passbook['member']->district_id) }},<br>{{ getStateName($passbook['member']->state_id) }},{{$passbook['member']->pin_code}} 

                                            </div>
                                        </td>
                          	            </tr>
                                      
                                      <tr>
                                        @php
                                            $stateid = getBranchState(Auth::user()->username);
                                        @endphp
                                        <td class="td_lable" style="padding:30px 0px;"><label class=" col-lg-12 passbook_lable">{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }} </label></td>
                                        <td style="padding:30px 0px;"><div class="col-lg-7 passbook_content">
                                               
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
                @if($passbook->is_passbook_print !=1)
                <div class="card bg-white shadow" id="printButton"> 
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            @if( in_array('Passbook Cover Print', auth()->user()->getPermissionNames()->toArray() ) )
                                <button type="submit" class="btn btn-primary" onclick="printDiv('passbok_cover_print','{{$passbook->id}}');" >Print<i class="icon-paperplane ml-2"></i></button>
                            @endif
                        </div>
                    </div> 

                </div> 
                @endif
            @if( in_array('Cover Print And Pay', auth()->user()->getPermissionNames()->toArray() ) )
                <div class="card bg-white shadow" id="printButtonPay" @if($passbook->is_passbook_print ==1) style="display: block" @else style="display: none" @endif> 
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                                 <div class="col-lg-3" style="display: inline-block;">
                                        <div class="form-group row">
                                            <div class="col-lg-12 error-msg">
                                                <div class="rupee-img"></div>
                                               <input type="text" class="form-control  rupee-txt" name="amount" id="amount"  readonly value="50"> 
                                            </div>
                                        </div>
                                        @if($gstAmount > 0 )

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
                                                <input type="text" class="form-control  igst" name="igst" id="igst"  readonly value="{{$gstAmount}}"> 
                                              </div>
                                              
                                          </div>
                                          @endif
                                        @endif  
                                    </div>
                                <button type="submit" class="btn btn-primary" onclick="pay_print('passbok_cover_print','{{$passbook->id}}');" >Pay&Print<i class="icon-paperplane ml-2" ></i></button>

                                @if($correctionStatus != '0')
                                <a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal" data-target="#correction-form">Correction Request For Free</a>
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
@include('templates.branch.passbook.partials.script')
@stop