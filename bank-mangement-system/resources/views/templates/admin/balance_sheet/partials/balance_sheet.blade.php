<?php

 $libility = array(1);
 $subHeadsIDS = App\Models\AccountHeads::where('head_id',1)->where('status',0)->pluck('head_id')->toArray();
  if( count($subHeadsIDS) > 0 ){
    $head_ids=  array_merge($libility,$subHeadsIDS);
      $return_array= get_change_sub_account_head($libility,$subHeadsIDS,true);
    }
    foreach ($return_array as $key => $value) {
     $ids[] = $value;
    }
$asset = array(2);
 $subHeadsIDS = App\Models\AccountHeads::where('head_id',2)->where('status',0)->pluck('head_id')->toArray();
  if( count($subHeadsIDS) > 0 ){
    $head_ids=  array_merge($asset,$subHeadsIDS);
      $return_array= get_change_sub_account_head($asset,$subHeadsIDS,true);
    }
    foreach ($return_array as $key => $value) {
     $Assetids[] = $value;
    }    
 ?>
        
            <div class="row" >
              <div class="col-md-6">
                <div class="d-flex justify-content-between mx-2 mb-2">
                  <span class="font-weight-bold text_upper">LIABILITIES & OWNERS' EQUITY</span>
                  <span class="font-weight-bold text_upper">Amount</span>
                </div>
                <div class="card">
                  <div class="">
                   <table id="capital" class="table  datatable-show-all">
                      @foreach ($libalityHead as $lib) 
                        <?php  
                          $countHead = getHead($lib->head_id,3);
                         ?>
                        <thead >
                         <!-- main Head  -->
                            <tr>
                              @if(count($countHead)>0)
                                <th class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail/'.$lib->head_id.'/'.$lib->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$lib->sub_head}} </a> </th>
                              @else
                                <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$lib->head_id.'/'.$lib->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$lib->sub_head}}</a></td>

                             @endif 
                                <th style="padding-right:150px"></th>
                                @if($lib->head_id == 6)
                                 <th>&#X20B9;{{ number_format((float)$profit_loss,2,'.','')}}</th>
                                @else
                                  <th>&#X20B9;{{ number_format((float)headTotalNew($lib->head_id,$start_date,
                                          $end_date,$branch_id), 2, '.', '')}}</th>
                                @endif           
                          </tr>  



                          
                          <!-- Sub Head -->
                        </thead> 
                         <?php  $libalityHeadThree = getHead($lib->head_id,3);?>
                        @if(count($libalityHeadThree)>0)
                          @foreach ($libalityHeadThree as $valLib) 
                           <?php  $countChildLia = getHead($valLib->head_id,4);?> 
                            <tbody>
                              <tr @if($valLib->status==1) class="child_inactive" @endif style="background-color: #dff9fb;">
                                @if(count($countChildLia) > 0)
                                   <td class="text_upper"><a href="{{ URL::to('admin/balance-sheet/detail/'.$valLib->head_id.'/'.$valLib->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$valLib->sub_head}}</td>
                                @elseif($valLib->head_id == 18)    
                                    <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail_ledger'.'/?head='.$valLib->head_id.'&date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$valLib->sub_head}}</a></td>
                                @elseif($valLib->head_id == 16)
                                    <td class="text_upper"> {{$valLib->sub_head}}</td>    
                                @else    
                                    <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$valLib->head_id.'/'.$valLib->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$valLib->sub_head}}</a></td>
                                @endif
                                 <th style="padding-right:150px"></th>
                                @if($valLib->head_id == 6)
                                 <th>&#X20B9;{{ number_format((float)$profit_loss,2,'.','')}}</th>
                                @else
                                  <th>&#X20B9;{{ number_format((float)headTotalNew($valLib->head_id,$start_date,
                                          $end_date,$branch_id), 2, '.', '')}}</th>
                                @endif

                              <!--   @if($valLib->head_id == 16)
                                    <td class="text_upper"> {{$valLib->sub_head}}</td>
                                @else
                                  @if(count( $countChildLia)>0)
                                  <td class="text_upper"><a href="{{ URL::to('admin/balance-sheet/detail/'.$valLib->head_id.'/'.$valLib->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$valLib->sub_head}}</td>
                                  @else
                                    <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$valLib->head_id.'/'.$valLib->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$valLib->sub_head}}</a></td>
                                  @endif
                                @endif  
                                <td style="padding-right:150px"></td>
                                @if($valLib->head_id==17)
                                    <td>&#X20B9;{{ number_format((float)$profit_loss, 2, '.', '')}}</td>
                                @else 
                                    <td>&#X20B9;{{ number_format((float)headTotalNew($valLib->head_id,$start_date,
                                      $end_date,''), 2, '.', '')}}</td>
                               @endif  -->
                              </tr>    
                            </tbody> 
                          @endforeach
                        @endif
                      @endforeach 
                                                  
                    </table>
                  </div>
                </div>
              </div> 
                <div class="col-md-6">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="font-weight-bold text_upper">ASSETS</span>
                    <span class="font-weight-bold text_upper">Amount</span>
                  </div>
                  <div class="card">
                    <div class="">
                      <table id="current_asset" class="table  datatable-show-all">
                        @foreach ($assestHead as $assest) 
                          <?php 
                            $assetCount = getHead($assest->head_id,3)

                          ?>
                        <thead >
                          <tr >
                            @if(count($assetCount) > 0)
                              <th class="text_upper">  <a href="{{ URL::to('admin/balance-sheet/detail/'.$assest->head_id.'/'.$assest->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$assest->sub_head}} </a></th>
                            @else
                             <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$assest->head_id.'/'.$assest->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$assest->sub_head}}</a></td>
                            @endif

                              <th style="padding-right:150px"></th>
                            <th>&#X20B9;{{ number_format((float)headTotalNew($assest->head_id,$start_date,
                                      $end_date,$branch_id), 2, '.', '')}}</th>
                          </tr>  
                        </thead>
                        <?php  $assestHeadThree = getHead($assest->head_id,3);?>
                        @if(count($assestHeadThree)>0)
                          @foreach ($assestHeadThree as $val1)  
                             <?php  $countChild = getHead($val1->head_id,4);?>

                            <tbody>
                             <tr @if($val1->status==1) class="child_inactive" @endif style="background-color: #dff9fb;">
                              
                              @if(count($countChild) == 0 )
                              @if(in_array(2,$Assetids) )
                                
                                  <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/current_liability/branch_wise/'.$val1->head_id.'/'.$val1->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$val1->sub_head}}</a></td>

                               
                              @else
                                <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail/'.$val1->head_id.'/'.$val1->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$val1->sub_head}}</a></td>
                              @endif  
                               @else
                                  <td class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail/'.$val1->head_id.'/'.$val1->labels.'/?date='.$start_date.'&end_date='.$end_date)}}" target="_blank">{{$val1->sub_head}}</a></td>
                                @endif  
                                <td style="padding-right:150px;"></td>
                                <td>&#X20B9;{{ number_format((float)headtotalNew($val1->head_id,$start_date,$end_date,$branch_id), 2, '.', '')}}</td>
                              </tr>    
                            </tbody> 
                          @endforeach
                        @endif
                      @endforeach   
                      </table>
                    </div>
                  </div>
                </div> 
            </div> 
            <div class="col-md-6 d-flex justify-content-between ">
                   <h4 class="text_upper">Interbranch transactions </h4>
                    <span class="mr-1 text_upper">Total Amount:0000</span>
                  </div>
              <div class="row  d-flex align-items-center">
                <div class="col-md-6">
                    <div class="card">
                      <div class="">
                        <table id="total_liability" class="table datatable-show-all">
                            <thead>
                               <tr>
                                <th class="text_upper">Total Liabilities</th>
                                <th></th>
                                <th>&#X20B9;{{ number_format((float)headTotalNew(1,$start_date,
                                        $end_date,$branch_id) + $profit_loss, 2, '.', '')}}</th>
                               </tr>  
                            </thead> 
                        </table>
                      </div>
                    </div>
                 </div> 
                 <div class="col-md-6">
                    <div class="card">
                      <div class="">
                        <table id="total_assets" class="table datatable-show-all">
                          <thead>
                            <tr>
                            <th class="text_upper">Total Assets[E]</th>
                            <th></th>
                            <th>&#X20B9;{{ number_format((float)headTotalNew(2,$start_date,
                                        $end_date,$branch_id), 2, '.', '')}}</th>
                           </tr>  
                          </thead> 
                        </table>
                      </div>
                    </div>
                  </div> 
            </div>



     
