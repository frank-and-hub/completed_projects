@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        @if($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif
        <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Commission Ledger create   -- Monthly </h6>
                    </div>
                    <div class="card-body">
                        <form action="{!! route('admin.associate.commission.commissionTransfer') !!}" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Start  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  value="@isset($start_date) {{ $start_date }} @endisset"> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">End  Date</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <input readonly="" type="text" class="form-control  " name="end_date" id="end_date"  value="@isset($end_date) {{ $end_date}} @endisset"> 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Company</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control valid" name="company_id" id="company_id"
                                                title="Please Select Company" required="" aria-invalid="false">
                                                <option value=""> Select Company </option>
                                                    @foreach ($companies as $item)
                                                    <option value="{{$item->id}}" @isset($company_id) @if($company_id==$item->id)  selected @endif @endisset>{{$item->name}}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                               

                                <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">

                                            <button type="submit" class=" btn bg-dark legitRipple"   >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        @isset($code)
        <div class="col-md-12" id='hide_div'>
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate Listing (Total Commission Amount)  -- Monthly</h6>
                    </div>
                    <form action="{!! route('admin.associate.commission.commissionLedgerCreate') !!}" method="post" enctype="multipart/form-data" id="transfer" name="transfer">
                        @csrf 
                        <input type="hidden" name="created_at" class="created_at">
                        <input type="hidden" name="companyId" class="companyId" value="{{$company_id}}">
                    <div class="card-body">
                        <div class="row">
                           
                            <div class="col-md-12">
                                <div class="table-responsive">
                                <table id="member_listing" class="table table-flush">
                                    <thead>
                                        <tr>                                    
                                        <th>S.No</th>
                                        <th>Associate code</th> 
                                        <th>Associate Name</th> 
                                        <!--<th>Associate Carder </th>-->
                                        <th>PAN No </th>
                                      <!--<th>SSB Account No </th>-->
                                        <th>PM TDS Deduction</th>
                                        <th>Total Amount</th>
                                        <th>Total TDS</th>
                                        <th>Final Payable Amount</th>
                                        <th>Collection Amount</th>
                                        <th>Fuel Amount</th>
                                        <th class="text-center"> Action </th>   
                                        </tr>
                                    </thead> 
                                    <tbody>
                                    @if(count($total_commission)>0)
                                    <?php 
                                        $total=0;
                                        $totalFinalAmount=0;
                                        $totalCollection=0;
                                        $totalFuleAmount=0;
                                    ?>
                                         @foreach($total_commission as $index =>  $val) 
                                         <?php //print_r($val['tds_member']);die; 

                                          $total=round($total+$val->commission_amount); 
                                          $getPan ='';
                                          if($val['comm_member']['memberIdProof']->first_id_type_id==5)
                                          {
                                            $getPan =$val['comm_member']['memberIdProof']->first_id_no;
                                          }
                                          if($val['comm_member']['memberIdProof']->second_id_type_id==5)
                                          {
                                            $getPan =$val['comm_member']['memberIdProof']->second_id_no;
                                          } 
                                          $isTdsDeduct=0;
                                          $is_TDS_Deduct='NO';
                                          if($val['tds_member'])
                                          {
                                                $isTdsDeduct=1;
                                                $is_TDS_Deduct='YES';
                                          }


                                                $perAmount=0; 
                                                $amount=round($val->commission_amount);
                                                 $tdsAmount=TotalCommissionCurrentYear($val->member_id, $start_date_time,$company_id,$val->commission_amount,$isTdsDeduct,$getPan);
                                                 //print_r($tdsAmount->perAmount);
                                                $perAmount=$tdsAmount['perAmount'];
                                          ?> 
                                         <tr>
                                            <td>{{ $index+1 }}</td>
                                            <td>{{ $val['comm_member']->associate_no }}
                                            
                                            </td>
                                            <td>{{ $val['comm_member']->first_name }} {{$val['comm_member']->last_name  }}</td>
                                            <!--<td> </td>-->
                                            <td>{{ $getPan }}</td>
                                            <!-- <td>{{  $val->saccount_no }}</td>-->
                                            <?php $amounttotalComm=round($val->commission_amount); ?>
                                            <td>{{ $is_TDS_Deduct}} ({{$tdsAmount['per']}}%)<br> @if($isTdsDeduct==0) ({{$tdsAmount['total_amount']}}) @endif </td>
                                            <td>{{ $amounttotalComm}}  </td>
                                           
                                            <td><?php $perAmount=round($perAmount);?>{{ $perAmount }}</td>
                                            <td>
                                                <?php 
                                                $finalAmount=($amount-$perAmount);
                                                if($finalAmount>0)
                                                {
                                                    $$finalAmount=$finalAmount;
                                                }
                                                else
                                                {
                                                    $finalAmount=0;
                                                }

                                                  $totalFinalAmount=$totalFinalAmount+$finalAmount; 
                                                ?>
                                                 <?php $finalAmount=round($finalAmount);?> {{ $finalAmount }}   
                                            </td>
                                             <td>
                                                <?php 
                                                    $reCollection=$val->collection_amount;
                                                    
                                                
                                                $reCollection =round($reCollection);
                                                ?>
                                                 {{ $reCollection }} 
                                             </td>
                                             <td>
                                                
                                                <?php
                                                    $fule = round($val->fule_amount);                                              
                                                    
                                                    $fixdate = strtotime(date('2023-08-01'));
                                                    $joindate = strtotime(date($val['comm_member']->associate_join_date ));
                                                    if($joindate < $fixdate){
                                                        $fule = $fule;
                                                    }
                                                    else
                                                    {
                                                        $fule = 0;
                                                    }
                                                ?>

                                                {{ $fule }} 
                                                <?php
                                                    $totalCollection=$totalCollection+$reCollection;                        
                                                    $totalFuleAmount=$totalFuleAmount+$fule;
                                                
                                                ?>
                                             </td>

                                             <td><a target="_blank" href='{{ URL::to("admin/commission/investment-detail/".$val->member_id."?start=$start_date&end=$end_date")}}' title="Commission Detail"><i class="fa fa-percent  mr-2"></i></a>

                                                <a target="_blank" href='{{ URL::to("admin/commission/loan-detail/".$val->member_id."?start=$start_date&end=$end_date")}}' title="Commission Detail"><i class="fa fa-percent  mr-2"></i></a>


                                             </td>
                                             <input type="hidden" name="id[]" id="id" value="{{ $val->id }}">

                                             <input type="hidden" name="start_date_time" id="start_date_time" value="{{ $start_date_time }}">
                                             <input type="hidden" name="end_date_time" id="end_date_time" value="{{ $end_date_time }}">
                                             
                                             <input type="hidden" name="amount[]" id="amount" value="{{ $amount }}">
                                             <input type="hidden" name="tds[]" id="tds" value="{{ $perAmount }}">
                                             <input type="hidden" name="fule[]" id="fule" value="{{ $fule }}">
                                             <input type="hidden" name="collection[]" id="collection" value="{{ $reCollection }}">

                                            
                                             

                                             

                                         </tr>                                      
                                         @endforeach
                                         </tbody>
                                         <tfoot>
                                            <input type="hidden" name="total" id="total" value="{{ $total }}">
                                            <input type="hidden" name="totalFinalAmount" id="totalFinalAmount" value="{{ $totalFinalAmount }}">
                                             <input type="hidden" name="totalCollection" id="totalCollection" value="{{ $totalCollection }}">
                                             <input type="hidden" name="totalFuleAmount" id="totalFuleAmount" value="{{ $totalFuleAmount }}">
                                             
                                             <td  colspan="2" align="center"><strong>Total Commission</strong> </td>
                                             <td   align="left"><strong>{{ $total }}</strong></td>
                                            
                                             <td  colspan="2" align="center"><strong> Total Final Payable Amount</strong> </td>
                                             <td   align="left"><strong>{{ $totalFinalAmount }}</strong></td>
                                          
                                             <td  colspan="2" align="center"><strong>Total Collection</strong> </td>
                                             <td   align="left"><strong>{{ $totalCollection }}</strong></td>
                                          
                                             <td  colspan="2"  align="center"><strong>Total Fule Amount</strong> </td>
                                             <td   align="left"><strong>{{ $totalFuleAmount }}</strong></td>
                                            
                                         </tfoot>
                                    @else

                                        <tr>
                                            <td colspan="6" align="center">No record </td>
                                        </tr>
                                        </tbody>
                                    @endif
                                    
                                </table>
                            </div>
                            </div>
                             
                 
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                @if(count($total_commission)>0)
                                <button type="button" class=" btn bg-dark legitRipple"  id="submit_transfer">Create Ledger</button>
                                @endif
                             </div>
                         </div>
                     </div>
                </form> 
                </div>           
        </div>
        @endisset
    </div>
</div>
@stop

@section('script')
@include('templates.admin.associate.commission_monthly.create_comm_script')
@stop
